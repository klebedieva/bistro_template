<?php

namespace App\DataFixtures;

use App\Entity\Drink;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class DrinkFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        // Can load together with menu
        return ['menu'];
    }

    public function load(ObjectManager $manager): void
    {
        $repo = $manager->getRepository(Drink::class);

        $data = [
            'vins' => [
                ['name' => 'Côtes du Rhône rouge', 'price' => '5.00'],
                ['name' => 'Rosé de Provence', 'price' => '4.00'],
                ['name' => 'Blanc de Cassis', 'price' => '5.00'],
            ],
            'bieres' => [
                ['name' => 'Pression 25cl', 'price' => '3.00'],
                ['name' => 'Pression 50cl', 'price' => '5.00'],
                ['name' => 'Bière artisanale', 'price' => '6.00'],
            ],
            'chaudes' => [
                ['name' => 'Café expresso', 'price' => '2.00'],
                ['name' => 'Cappuccino', 'price' => '3.00'],
                ['name' => 'Thé / Infusion', 'price' => '2.50'],
            ],
            'fraiches' => [
                ['name' => 'Jus de fruits frais', 'price' => '4.00'],
                ['name' => 'Sodas', 'price' => '3.00'],
                ['name' => 'Eau minérale', 'price' => '2.00'],
            ],
        ];

        foreach ($data as $type => $items) {
            foreach ($items as $row) {
                // Idempotent: find by name and type, create if missing
                $drink = $repo->findOneBy(['name' => $row['name'], 'type' => $type]);
                if (!$drink) {
                    $drink = new Drink();
                    $drink->setName($row['name']);
                    $drink->setPrice($row['price']);
                    $drink->setType($type);
                    $manager->persist($drink);
                }
            }
        }

        $manager->flush();
    }
}



























