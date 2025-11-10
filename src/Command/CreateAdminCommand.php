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
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Créer un utilisateur administrateur',
)]
class CreateAdminCommand extends Command
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
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email de l\'administrateur')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe de l\'administrateur')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Nom de l\'administrateur')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email') ?: $io->ask('Email de l\'administrateur');
        $password = $input->getOption('password') ?: $io->askHidden('Mot de passe de l\'administrateur');
        $name = $input->getOption('name') ?: $io->ask('Nom de l\'administrateur');

        if (!$email || !$password || !$name) {
            $io->error('Tous les champs sont requis.');
            return Command::FAILURE;
        }

        // Validate password strength
        $passwordValidation = $this->passwordValidator->validatePassword($password);
        if (!$passwordValidation['valid']) {
            $io->error('Le mot de passe ne respecte pas les exigences de sécurité:');
            foreach ($passwordValidation['errors'] as $error) {
                $io->writeln("  - $error");
            }
            $io->note($this->passwordValidator->getRequirements());
            return Command::FAILURE;
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà.');
            return Command::FAILURE;
        }

        // Create the user
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRole(UserRole::ADMIN);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Administrateur créé avec succès: %s (%s)', $name, $email));

        return Command::SUCCESS;
    }
}
