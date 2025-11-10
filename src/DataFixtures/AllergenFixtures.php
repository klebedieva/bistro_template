<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AllergenFixtures extends Fixture implements FixtureGroupInterface
{
    public const REFERENCE_PREFIX = 'allergen_';

    public static function getGroups(): array
    {
        return ['allergens'];
    }

    public function load(ObjectManager $manager): void
    {
        // Derived from the static Restaurant project
        $rows = [
            ['code' => 'gluten',    'name' => 'Gluten'],
            ['code' => 'lactose',   'name' => 'Lactose'],
            ['code' => 'nuts',      'name' => 'Fruits à coque'],
            ['code' => 'eggs',      'name' => 'Œufs'],
            ['code' => 'fish',      'name' => 'Poisson'],
            ['code' => 'shellfish', 'name' => 'Crustacés'],
            ['code' => 'soy',       'name' => 'Soja'],
            ['code' => 'peanuts',   'name' => 'Arachides'],
        ];

        foreach ($rows as $r) {
            // Idempotent: find by code, create if missing
            $repo = $manager->getRepository(Allergen::class);
            $entity = $repo->findOneBy(['code' => $r['code']]);
            if (!$entity) {
                $entity = (new Allergen())
                    ->setCode($r['code'])
                    ->setName($r['name']);
                $manager->persist($entity);
            } else {
                // Ensure name is up to date if it changed
                if ($entity->getName() !== $r['name']) {
                    $entity->setName($r['name']);
                }
            }

            // Store reference for later linking if needed
            $this->addReference(self::REFERENCE_PREFIX . $r['code'], $entity);
        }

        $manager->flush();
    }
}


