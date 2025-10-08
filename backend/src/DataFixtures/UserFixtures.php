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
        // Create a manager
        $manager1 = new User();
        $manager1->setEmail('manager@example.com')
            ->setFirstName('John')
            ->setLastName('Manager')
            ->setPhoneNumber('0612345678')
            ->setRole('manager');

        $hashedPassword = $this->passwordHasher->hashPassword($manager1, 'password123');
        $manager1->setPassword($hashedPassword);

        $manager->persist($manager1);

        // Create employees
        $employee1 = new User();
        $employee1->setEmail('employee1@example.com')
            ->setFirstName('Alice')
            ->setLastName('Employee')
            ->setPhoneNumber('0623456789')
            ->setRole('employee');

        $hashedPassword = $this->passwordHasher->hashPassword($employee1, 'password123');
        $employee1->setPassword($hashedPassword);

        $manager->persist($employee1);

        $employee2 = new User();
        $employee2->setEmail('employee2@example.com')
            ->setFirstName('Bob')
            ->setLastName('Worker')
            ->setPhoneNumber('0634567890')
            ->setRole('employee');

        $hashedPassword = $this->passwordHasher->hashPassword($employee2, 'password123');
        $employee2->setPassword($hashedPassword);

        $manager->persist($employee2);

        $manager->flush();
    }
}
