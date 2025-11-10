<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\UserRole;
use App\Service\PasswordValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:update-passwords',
    description: 'Mettre à jour les mots de passe des utilisateurs admin et modérateur existants avec des mots de passe sécurisés',
)]
class UpdatePasswordsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private PasswordValidator $passwordValidator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Email de l\'utilisateur à mettre à jour (si non spécifié, tous les admins et modérateurs seront mis à jour)')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Nouveau mot de passe (si non spécifié, sera demandé interactivement)')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Forcer la mise à jour sans confirmation')
            ->setHelp('Cette commande permet de mettre à jour les mots de passe des utilisateurs avec des exigences de sécurité renforcées (minimum 12 caractères, majuscules, minuscules, chiffres, caractères spéciaux)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        // Find users to update
        $email = $input->getOption('email');
        if ($email) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$user) {
                $io->error("Aucun utilisateur trouvé avec l'email: $email");
                return Command::FAILURE;
            }
            if (!in_array($user->getRole(), [UserRole::ADMIN, UserRole::MODERATOR])) {
                $io->error("Cet utilisateur n'est ni admin ni modérateur. Seuls les admins et modérateurs peuvent être mis à jour.");
                return Command::FAILURE;
            }
            $users = [$user];
        } else {
            // Find all admins and moderators
            $users = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')
                ->where('u.role = :admin OR u.role = :moderator')
                ->setParameter('admin', UserRole::ADMIN)
                ->setParameter('moderator', UserRole::MODERATOR)
                ->getQuery()
                ->getResult();

            if (empty($users)) {
                $io->warning('Aucun utilisateur admin ou modérateur trouvé.');
                return Command::SUCCESS;
            }

            $io->info(sprintf('Trouvé %d utilisateur(s) à mettre à jour.', count($users)));
            foreach ($users as $user) {
                $io->writeln(sprintf("  - %s (%s) - %s", $user->getName(), $user->getEmail(), $user->getRole()->value));
            }

            if (!$input->getOption('force')) {
                if (!$io->confirm('Voulez-vous continuer et mettre à jour tous ces utilisateurs?', false)) {
                    $io->info('Opération annulée.');
                    return Command::SUCCESS;
                }
            }
        }

        $updated = 0;
        $skipped = 0;
        $globalPassword = $input->getOption('password');

        foreach ($users as $user) {
            $io->section(sprintf('Mise à jour du mot de passe pour: %s (%s)', $user->getName(), $user->getEmail()));

            // Get password
            $password = $globalPassword;
            if (!$password) {
                $passwordQuestion = new Question('Entrez le nouveau mot de passe: ');
                $passwordQuestion->setHidden(true);
                $passwordQuestion->setHiddenFallback(false);
                
                $password = $helper->ask($input, $output, $passwordQuestion);
                
                if (!$password) {
                    $io->warning('Mot de passe vide, utilisateur ignoré.');
                    $skipped++;
                    continue;
                }

                // Confirm password
                $confirmQuestion = new Question('Confirmez le nouveau mot de passe: ');
                $confirmQuestion->setHidden(true);
                $confirmQuestion->setHiddenFallback(false);
                $confirmPassword = $helper->ask($input, $output, $confirmQuestion);

                if ($password !== $confirmPassword) {
                    $io->error('Les mots de passe ne correspondent pas. Utilisateur ignoré.');
                    $skipped++;
                    continue;
                }
            } elseif (count($users) > 1) {
                // If password is provided via option but multiple users, warn about reuse
                $io->warning('Le même mot de passe sera utilisé pour tous les utilisateurs. Assurez-vous que c\'est bien ce que vous voulez!');
            }

            // Validate password
            $passwordValidation = $this->passwordValidator->validatePassword($password);
            if (!$passwordValidation['valid']) {
                $io->error('Le mot de passe ne respecte pas les exigences de sécurité:');
                foreach ($passwordValidation['errors'] as $error) {
                    $io->writeln("  - $error");
                }
                $io->note($this->passwordValidator->getRequirements());
                $skipped++;
                continue;
            }

            // Update password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $updated++;

            $io->success(sprintf('Mot de passe mis à jour avec succès pour %s (%s)', $user->getName(), $user->getEmail()));
        }

        if ($updated > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('Mise à jour terminée: %d utilisateur(s) mis à jour, %d ignoré(s).', $updated, $skipped));
        } else {
            $io->warning('Aucun utilisateur n\'a été mis à jour.');
        }

        return Command::SUCCESS;
    }
}

