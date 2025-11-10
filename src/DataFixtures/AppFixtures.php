<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Intentionnellement vide: utilisez BadgeFixtures, TagFixtures, MenuFixtures
        // Exemple d'exécution sans purge des autres tables:
        // php bin/console doctrine:fixtures:load --group=menu --append
    }
}
