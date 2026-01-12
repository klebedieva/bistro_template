<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture implements FixtureGroupInterface
{
    public const REFERENCE_PREFIX = 'tag_';

    public static function getGroups(): array
    {
        return ['menu'];
    }

    public function load(ObjectManager $manager): void
    {
        $repo = $manager->getRepository(Tag::class);

        $defs = [
            ['name' => 'Vegetarian', 'code' => 'vegetarian'],
            ['name' => 'Vegan', 'code' => 'vegan'],
            ['name' => 'Sans gluten', 'code' => 'glutenFree'],
        ];

        foreach ($defs as $d) {
            // Idempotent: find by code, create if missing
            $tag = $repo->findOneBy(['code' => $d['code']]);
            if (!$tag) {
                $tag = (new Tag())
                    ->setName($d['name'])
                    ->setCode($d['code']);
                $manager->persist($tag);
            }
            // Always add reference, even if existing
            $this->addReference(self::REFERENCE_PREFIX . $d['code'], $tag);
        }

        $manager->flush();
    }
}



























































