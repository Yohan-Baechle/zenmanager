<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Manager
        $manager1 = (new User())
            ->setEmail('manager@example.com')
            ->setUsername('manager')
            ->setFirstName('John')
            ->setLastName('Manager')
            ->setPhoneNumber('0612345678')
            ->setRole('manager');

        $manager1->setPassword(
            $this->passwordHasher->hashPassword($manager1, 'password123')
        );
        $manager->persist($manager1);

        // Employee
        $employee1 = (new User())
            ->setEmail('employee1@example.com')
            ->setUsername('alice')
            ->setFirstName('Alice')
            ->setLastName('Employee')
            ->setPhoneNumber('0623456789')
            ->setRole('employee');

        $employee1->setPassword(
            $this->passwordHasher->hashPassword($employee1, 'password123')
        );
        $manager->persist($employee1);

        $manager->flush();
    }
}
