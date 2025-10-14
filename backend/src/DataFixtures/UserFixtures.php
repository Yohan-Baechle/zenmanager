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
        $isTest = ($_ENV['APP_ENV'] ?? 'dev') === 'test';

        if ($isTest) {
            $this->loadTestUsers($manager);
        } else {
            $this->loadTestUsers($manager);
            $this->loadGeneratedUsers($manager, $faker);
        }
    }

    private function loadTestUsers(ObjectManager $manager): void
    {
        $teamDev = $this->getReference('team-1', Team::class);
        $teamMarketing = $this->getReference('team-2', Team::class);

        $admin = new User();
        $admin->setUsername('admin')
            ->setEmail('admin@test.com')
            ->setFirstName('Admin')
            ->setLastName('System')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);
        $this->addReference('user-1', $admin);

        $managerDev = new User();
        $managerDev->setUsername('manager_dev')
            ->setEmail('manager.dev@test.com')
            ->setFirstName('John')
            ->setLastName('Manager')
            ->setRoles(['ROLE_MANAGER'])
            ->setPassword($this->passwordHasher->hashPassword($managerDev, 'password'));
        $manager->persist($managerDev);
        $this->addReference('user-2', $managerDev);

        $teamDev->setManager($managerDev);
        $manager->persist($teamDev);

        $employeeDev1 = new User();
        $employeeDev1->setUsername('employee_dev1')
            ->setEmail('emp1.dev@test.com')
            ->setFirstName('Alice')
            ->setLastName('Developer')
            ->setRoles(['ROLE_EMPLOYEE'])
            ->setPassword($this->passwordHasher->hashPassword($employeeDev1, 'password'))
            ->setTeam($teamDev);
        $manager->persist($employeeDev1);
        $this->addReference('user-3', $employeeDev1);

        $employeeDev2 = new User();
        $employeeDev2->setUsername('employee_dev2')
            ->setEmail('emp2.dev@test.com')
            ->setFirstName('Bob')
            ->setLastName('Developer')
            ->setRoles(['ROLE_EMPLOYEE'])
            ->setPassword($this->passwordHasher->hashPassword($employeeDev2, 'password'))
            ->setTeam($teamDev);
        $manager->persist($employeeDev2);
        $this->addReference('user-4', $employeeDev2);

        $managerMarketing = new User();
        $managerMarketing->setUsername('manager_marketing')
            ->setEmail('manager.marketing@test.com')
            ->setFirstName('Sarah')
            ->setLastName('Marketing')
            ->setRoles(['ROLE_MANAGER'])
            ->setPassword($this->passwordHasher->hashPassword($managerMarketing, 'password'));
        $manager->persist($managerMarketing);
        $this->addReference('user-5', $managerMarketing);

        $teamMarketing->setManager($managerMarketing);
        $manager->persist($teamMarketing);

        $manager->flush();
    }

    private function loadGeneratedUsers(ObjectManager $manager, $faker): void
    {
        $maxUsers = 30;
        $maxTeams = 30;

        $manager1 = new User();
        $manager1->setEmail('manager@test.com')
            ->setUsername('Theking')
            ->setFirstName('Michel')
            ->setLastName('MichMich')
            ->setPhoneNumber('0800123123')
            ->setRoles(['ROLE_MANAGER'])
            ->setPassword($this->passwordHasher->hashPassword($manager1, 'password123'));
        $manager->persist($manager1);
        $this->addReference('user-manager', $manager1);

        $employee1 = new User();
        $employee1->setEmail('employee@test.com')
            ->setUsername('TheJoker')
            ->setFirstName('Pol-Mattis')
            ->setLastName('PM')
            ->setPhoneNumber('0345566667')
            ->setRoles(['ROLE_EMPLOYEE'])
            ->setPassword($this->passwordHasher->hashPassword($employee1, 'password123'));
        $manager->persist($employee1);
        $this->addReference('user-employee', $employee1);

        for ($i = 6; $i <= $maxUsers + 5; $i++) {
            $user = new User();
            $role = $i <= 15 ? 'ROLE_MANAGER' : 'ROLE_EMPLOYEE';

            if ($faker->boolean(80)) {
                $teamIndex = $faker->numberBetween(1, $maxTeams);
                $user->setTeam($this->getReference('team-' . $teamIndex, Team::class));
            }

            $user->setEmail($faker->email() . $i)
                ->setUsername($faker->userName() . $i)
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPhoneNumber($faker->phoneNumber())
                ->setPassword($this->passwordHasher->hashPassword($user, 'password123'))
                ->setRoles([$role]);

            $manager->persist($user);
            $this->addReference('user-' . $i, $user);
        }

        $manager->flush();

        // Assigner des managers aux équipes
        for ($i = 3; $i <= $maxTeams; $i++) {
            if ($faker->boolean(70)) {
                $managerIndex = $faker->numberBetween(6, 15); // Managers (user-6 à user-15)
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
