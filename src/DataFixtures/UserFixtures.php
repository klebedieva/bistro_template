<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getGroups(): array
    {
        // Отдельная группа, чтобы не трогать других данных при загрузке
        return ['users'];
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@letroisquarts.com')
              ->setName('Admin')
              ->setRole(UserRole::ADMIN)
              ->setIsActive(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Moderator
        $moderator = new User();
        $moderator->setEmail('moderator@letroisquarts.com')
                  ->setName('Moderator')
                  ->setRole(UserRole::MODERATOR)
                  ->setIsActive(true);
        $moderator->setPassword($this->passwordHasher->hashPassword($moderator, 'moder123'));
        $manager->persist($moderator);

        $manager->flush();
    }
}


