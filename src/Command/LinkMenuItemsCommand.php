<?php

namespace App\Command;

use App\Entity\Badge;
use App\Entity\MenuItem;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:link-menu-items',
    description: 'Link menu items with badges, tags and allergens based on MenuFixtures data'
)]
class LinkMenuItemsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $badgeRepo = $this->em->getRepository(Badge::class);
        $tagRepo = $this->em->getRepository(Tag::class);
        $menuRepo = $this->em->getRepository(MenuItem::class);

        // Mapping from MenuFixtures
        $mappings = [
            'Asperges Printemps à la Ricotta' => [
                'badges' => ['Végétarien', 'Fait maison'],
                'tags' => ['vegetarian'],
            ],
            'Œuf Mollet au Safran et Petits Pois' => [
                'badges' => ['Végétarien', 'Fait maison'],
                'tags' => ['vegetarian'],
            ],
            'Seiches Sautées à la Chermoula' => [
                'badges' => ['Méditerranéen', 'Fait maison'],
                'tags' => [],
            ],
            "Boulette d'agneau" => [
                'badges' => ['Fait maison'],
                'tags' => [],
            ],
            "Galinette poêlée à l'ajo blanco" => [
                'badges' => ['Traditionnel', 'Spécialité'],
                'tags' => [],
            ],
            'Sashimi de ventrèche de thon fumé' => [
                'badges' => ['Fusion', 'Spécialité'],
                'tags' => [],
            ],
            'Magret de canard au fenouil confit' => [
                'badges' => ['Traditionnel', 'Spécialité'],
                'tags' => [],
            ],
            'Velouté de châtaignes aux pleurottes' => [
                'badges' => ['Traditionnel', 'Saison'],
                'tags' => [],
            ],
            "Spaghettis à l'ail noir et parmesan" => [
                'badges' => ['Traditionnel'],
                'tags' => [],
            ],
            'Loup de mer aux pois chiches' => [
                'badges' => ['Traditionnel', 'Méditerranéen'],
                'tags' => [],
            ],
            "Potimarron Rôti aux Saveurs d'Asie" => [
                'badges' => ['Végétarien', 'Fusion'],
                'tags' => ['vegetarian'],
            ],
            'Gaspacho Tomates et Melon' => [
                'badges' => ['Sans Gluten', 'Méditerranéen'],
                'tags' => ['vegetarian', 'glutenFree'],
            ],
            'Tartelette aux Marrons Suisses' => [
                'badges' => ['Fait maison', 'Saison'],
                'tags' => ['vegetarian'],
            ],
            'Tartelette Ricotta au Miel et Fraises' => [
                'badges' => ['Fait maison', 'Saison'],
                'tags' => ['vegetarian'],
            ],
            'Crémeux Yuzu aux Fruits Rouges' => [
                'badges' => ['Fait maison', 'Fusion'],
                'tags' => ['vegetarian'],
            ],
        ];

        $linked = 0;
        foreach ($mappings as $itemName => $data) {
            $item = $menuRepo->findOneBy(['name' => $itemName]);
            if (!$item) {
                $output->writeln("Item not found: {$itemName}");
                continue;
            }

            // Clear existing badges and tags
            foreach ($item->getBadges() as $badge) {
                $item->removeBadge($badge);
            }
            foreach ($item->getTags() as $tag) {
                $item->removeTag($tag);
            }

            // Add badges
            foreach ($data['badges'] ?? [] as $badgeName) {
                $badge = $badgeRepo->findOneBy(['name' => $badgeName]);
                if ($badge) {
                    $item->addBadge($badge);
                    $output->writeln("Linked badge '{$badgeName}' to '{$itemName}'");
                } else {
                    $output->writeln("Badge not found: {$badgeName}");
                }
            }

            // Add tags
            foreach ($data['tags'] ?? [] as $tagCode) {
                $tag = $tagRepo->findOneBy(['code' => $tagCode]);
                if ($tag) {
                    $item->addTag($tag);
                    $output->writeln("Linked tag '{$tagCode}' to '{$itemName}'");
                } else {
                    $output->writeln("Tag not found: {$tagCode}");
                }
            }

            $linked++;
        }

        $this->em->flush();
        $output->writeln("\nLinked {$linked} menu items with badges and tags.");

        return Command::SUCCESS;
    }
}


