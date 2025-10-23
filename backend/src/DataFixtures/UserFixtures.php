<?php

namespace App\DataFixtures;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $isTest = ($_ENV['APP_ENV'] ?? 'dev') === 'test';

        $this->loadTestUsers($manager);

        if (!$isTest) {
            $this->loadGeneratedUsers($manager, $faker);
        }

        $manager->flush();
    }

    private function loadTestUsers(ObjectManager $manager): void
    {
        $teamDev = $this->getReference('team-1', Team::class);
        $teamMarketing = $this->getReference('team-2', Team::class);

        $admin = $this->createUser(
            username: 'admin',
            email: 'admin@test.com',
            firstName: 'Admin',
            lastName: 'System',
            roles: ['ROLE_ADMIN'],
            password: 'admin123'
        );
        $manager->persist($admin);
        $this->addReference('user-1', $admin);

        $theKing = $this->createUser(
            username: 'Theking',
            email: 'manager@test.com',
            firstName: 'Michel',
            lastName: 'MichMich',
            roles: ['ROLE_MANAGER'],
            password: 'password123',
            phoneNumber: '0800123123',
            team: $teamMarketing
        );
        $teamDev->setManager($theKing);
        $manager->persist($theKing);
        $this->addReference('user-2', $theKing);
        $this->addReference('user-manager', $theKing);

        $theJoker = $this->createUser(
            username: 'TheJoker',
            email: 'employee@test.com',
            firstName: 'Pol-Mattis',
            lastName: 'PM',
            roles: ['ROLE_EMPLOYEE'],
            password: 'password123',
            phoneNumber: '0345566667',
            team: $teamDev
        );
        $manager->persist($theJoker);
        $this->addReference('user-3', $theJoker);
        $this->addReference('user-employee', $theJoker);

        $employeeDev1 = $this->createUser(
            username: 'employee_dev1',
            email: 'emp1.dev@test.com',
            firstName: 'Alice',
            lastName: 'Developer',
            roles: ['ROLE_EMPLOYEE'],
            password: 'password',
            team: $teamDev
        );
        $manager->persist($employeeDev1);
        $this->addReference('user-4', $employeeDev1);

        $employeeDev2 = $this->createUser(
            username: 'employee_dev2',
            email: 'emp2.dev@test.com',
            firstName: 'Bob',
            lastName: 'Developer',
            roles: ['ROLE_EMPLOYEE'],
            password: 'password',
            team: $teamDev
        );
        $manager->persist($employeeDev2);
        $this->addReference('user-5', $employeeDev2);

        $managerMarketing = $this->createUser(
            username: 'manager_marketing',
            email: 'manager.marketing@test.com',
            firstName: 'Sarah',
            lastName: 'Marketing',
            roles: ['ROLE_MANAGER'],
            password: 'password'
        );
        $teamMarketing->setManager($managerMarketing);
        $manager->persist($managerMarketing);
        $this->addReference('user-6', $managerMarketing);
    }

    /**
     * @param \Faker\Generator $faker
     */
    private function loadGeneratedUsers(ObjectManager $manager, $faker): void
    {
        $maxUsers = 30;
        $maxTeams = 30;

        for ($i = 7; $i <= $maxUsers + 6; ++$i) {
            $role = $i <= 16 ? 'ROLE_MANAGER' : 'ROLE_EMPLOYEE';
            $team = null;

            if ($faker->boolean(80)) {
                $teamIndex = $faker->numberBetween(1, $maxTeams);
                $team = $this->getReference('team-'.$teamIndex, Team::class);
            }

            $user = $this->createUser(
                username: $faker->userName().$i,
                email: $faker->email().$i,
                firstName: $faker->firstName(),
                lastName: $faker->lastName(),
                roles: [$role],
                password: 'password123',
                phoneNumber: $faker->phoneNumber(),
                team: $team
            );

            $manager->persist($user);
            $this->addReference('user-'.$i, $user);
        }

        for ($i = 3; $i <= $maxTeams; ++$i) {
            if ($faker->boolean(70)) {
                $managerIndex = $faker->numberBetween(7, 16);
                $team = $this->getReference('team-'.$i, Team::class);
                $team->setManager($this->getReference('user-'.$managerIndex, User::class));
            }
        }
    }

    /**
     * @param array<string> $roles
     */
    private function createUser(
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        array $roles,
        string $password,
        ?string $phoneNumber = null,
        ?Team $team = null,
    ): User {
        $username = preg_replace('/[^a-zA-Z0-9_-]/', '_', $username);
        $user = new User();
        $user->setUsername($username)
            ->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRoles($roles)
            ->setPassword($this->passwordHasher->hashPassword($user, $password));

        if (null !== $phoneNumber) {
            $user->setPhoneNumber($phoneNumber);
        }

        if (null !== $team) {
            $user->setTeam($team);
        }

        return $user;
    }

    public function getDependencies(): array
    {
        return [
            TeamFixtures::class,
        ];
    }
}
