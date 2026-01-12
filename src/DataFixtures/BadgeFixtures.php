<?php

namespace App\DataFixtures;

use App\Entity\Badge;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class BadgeFixtures extends Fixture implements FixtureGroupInterface
{
    public const REFERENCE_PREFIX = 'badge_';

    public static function getGroups(): array
    {
        // Groupe de chargement ciblé pour le menu
        return ['menu'];
    }

    public function load(ObjectManager $manager): void
    {
        $repo = $manager->getRepository(Badge::class);

        $badgeNames = [
            'Spécialité', 'Végétarien', 'Fait maison', 'Méditerranéen',
            'Traditionnel', 'Fusion', 'Saison', 'Sans Gluten'
        ];

        foreach ($badgeNames as $name) {
            // Idempotent: find by name, create if missing
            $badge = $repo->findOneBy(['name' => $name]);
            if (!$badge) {
                $badge = (new Badge())
                    ->setName($name)
                    ->setSlug(strtolower(str_replace(' ', '-', $name)));
                $manager->persist($badge);
            }
            // Mise en référence pour MenuFixtures (always add reference, even if existing)
            $this->addReference(self::REFERENCE_PREFIX . $name, $badge);
        }

        $manager->flush();
    }
}







