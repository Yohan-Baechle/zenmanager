<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Create 30 users
        for ($i = 1; $i <= 30; $i++) {
            $user = new User();
            $role = $i <= 10 ? 'manager' : 'employee'; // 10 managers, 20 employees

            // Assign a random team (80% chance to have a team)
            if ($faker->boolean(80)) {
                $teamIndex = $faker->numberBetween(1, 30);
                $user->setTeam($this->getReference('team-' . $teamIndex, Team::class));
            }

            $user->setEmail($faker->unique()->email())
                ->setUsername($faker->unique()->userName())
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPhoneNumber($faker->phoneNumber())
                ->setRole($role);

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);

            $manager->persist($user);
            $this->addReference('user-' . $i, $user);
        }

        $manager->flush();

        // Assign managers to teams
        for ($i = 1; $i <= 30; $i++) {
            if ($faker->boolean(70)) { // 70% chance to have a manager
                $managerIndex = $faker->numberBetween(1, 10); // Pick from managers (users 1-10)
                $team = $this->getReference('team-' . $i, Team::class);
                $team->setManager($this->getReference('user-' . $managerIndex, User::class));
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TeamFixtures::class,
        ];
    }
}
