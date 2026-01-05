<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const REGULAR_USER_REFERENCE = 'regular-user';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Admin user
        $admin = new User();
        $admin->setEmail('admin@ecogarden.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setCityName('Paris');
        $admin->setPostalCode('75001');
        $manager->persist($admin);
        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);

        // Regular user
        $user1 = new User();
        $user1->setEmail('user@ecogarden.fr');
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'user123'));
        $user1->setCityName('Lyon');
        $user1->setPostalCode('69001');
        $manager->persist($user1);
        $this->addReference(self::REGULAR_USER_REFERENCE, $user1);

        // Another user with only city name
        $user2 = new User();
        $user2->setEmail('gardener@ecogarden.fr');
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'garden123'));
        $user2->setCityName('Marseille');
        $manager->persist($user2);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['users', 'all'];
    }
}
