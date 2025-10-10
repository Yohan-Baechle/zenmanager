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


        // Create 2 fixed users for testing
        $manager1 = new User();
        $manager1->setEmail('manager@test.com')
            ->setUsername('Theking')
            ->setFirstName('Michel')
            ->setLastName('MichMich')
            ->setPhoneNumber('0800123123')
            ->setBusinessRole('manager');
        $hashedPassword = $this->passwordHasher->hashPassword($manager1, 'password123');
        $manager1->setPassword($hashedPassword);
        $manager->persist($manager1);
        $this->addReference('user-manager', $manager1);

        $employee1 = new User();
        $employee1->setEmail('employee@test.com')
            ->setUsername('TheJoker')
            ->setFirstName('Pol-Mattis')
            ->setLastName('PM')
            ->setPhoneNumber('0345566667')
            ->setBusinessRole('employee');
        $hashedPassword = $this->passwordHasher->hashPassword($employee1, 'password123');
        $employee1->setPassword($hashedPassword);
        $manager->persist($employee1);
        $this->addReference('user-employee', $employee1);

        $maxUsers = ($_ENV['APP_ENV'] ?? 'dev') === 'test' ? 5 : 30;
        $batchSize = 20; 
        $counter = 0;

        // Create users
        for ($i = 1; $i <= $maxUsers; $i++) {
            $user = new User();
            $role = $i <= 10 ? 'manager' : 'employee'; // 10 managers, 20 employees

            // Assign a random team (80% chance to have a team)
            if ($faker->boolean(80)) {
                $teamIndex = $faker->numberBetween(1, $maxUsers);
                $user->setTeam($this->getReference('team-' . $teamIndex, Team::class));
            }

            $user->setEmail($faker->email().$i) #unique() has been removed for test performance issues. Temporarily add it back if requiered or use $i to make it unique
                ->setUsername($faker->userName().$i) #unique() has been removed for test performance issues. Temporarily add it back if requiered or use $i to make it unique
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPhoneNumber($faker->phoneNumber())
                ->setPassword($this->passwordHasher->hashPassword($user, 'password123'))
                ->setBusinessRole($role);


            $manager->persist($user);
            $this->addReference('user-' . $i, $user);
            $counter++;

            if ($counter % $batchSize === 0) {
                $manager->flush();
            }
        }

        $manager->flush();

        // Assign managers to teams
        $maxTeams = ($_ENV['APP_ENV'] ?? 'dev') === 'test' ? 5 : 30;
        for ($i = 1; $i <= $maxTeams; $i++) {
            if ($faker->boolean(70)) { // 70% chance to have a manager
                $managerIndex = $faker->numberBetween(1, min(10, $maxUsers)); // Pick from managers (users 1-10)
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
