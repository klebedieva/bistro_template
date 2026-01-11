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
        // Separate group to avoid touching other data when loading
        return ['users'];
    }

    public function load(ObjectManager $manager): void
    {
        $repo = $manager->getRepository(User::class);

        // Admin with secure password: Admin13005!@#Secure
        // Requirements: 12+ chars, uppercase, lowercase, digit, special char
        $adminEmail = 'admin@bistro.com';
        $admin = $repo->findOneBy(['email' => $adminEmail]);
        if (!$admin) {
            $admin = new User();
            $admin->setEmail($adminEmail)
                  ->setName('Admin')
                  ->setRole(UserRole::ADMIN)
                  ->setIsActive(true);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin13005!@#Secure'));
            $manager->persist($admin);
        }

        // Moderator with secure password: Moder13005!@#Secure
        // Requirements: 12+ chars, uppercase, lowercase, digit, special char
        $moderatorEmail = 'moderator@bistro.com';
        $moderator = $repo->findOneBy(['email' => $moderatorEmail]);
        if (!$moderator) {
            $moderator = new User();
            $moderator->setEmail($moderatorEmail)
                      ->setName('Moderator')
                      ->setRole(UserRole::MODERATOR)
                      ->setIsActive(true);
            $moderator->setPassword($this->passwordHasher->hashPassword($moderator, 'Moder13005!@#Secure'));
            $manager->persist($moderator);
        }

        $manager->flush();
    }
}


