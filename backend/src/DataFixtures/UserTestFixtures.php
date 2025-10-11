<?php

namespace App\DataFixtures;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTestFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $teamDev = $this->getReference('team-1', Team::class);
        $teamMarketing = $this->getReference('team-2', Team::class);

        // 1. ADMIN - Accès total
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@test.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('System');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // 2. MANAGER de l'équipe Dev
        $managerDev = new User();
        $managerDev->setUsername('manager_dev');
        $managerDev->setEmail('manager.dev@test.com');
        $managerDev->setFirstName('John');
        $managerDev->setLastName('Manager');
        $managerDev->setRoles(['ROLE_MANAGER']);
        $managerDev->setBusinessRole('manager');
        $managerDev->setPassword($this->passwordHasher->hashPassword($managerDev, 'password'));
        $manager->persist($managerDev);

        $teamDev->setManager($managerDev);

        // 3. EMPLOYEE de l'équipe Dev
        $employeeDev1 = new User();
        $employeeDev1->setUsername('employee_dev1');
        $employeeDev1->setEmail('emp1.dev@test.com');
        $employeeDev1->setFirstName('Alice');
        $employeeDev1->setLastName('Developer');
        $employeeDev1->setRoles(['ROLE_USER']);
        $employeeDev1->setBusinessRole('employee');
        $employeeDev1->setPassword($this->passwordHasher->hashPassword($employeeDev1, 'password'));
        $employeeDev1->setTeam($teamDev);
        $manager->persist($employeeDev1);

        $employeeDev2 = new User();
        $employeeDev2->setUsername('employee_dev2');
        $employeeDev2->setEmail('emp2.dev@test.com');
        $employeeDev2->setFirstName('Bob');
        $employeeDev2->setLastName('Developer');
        $employeeDev2->setRoles(['ROLE_USER']);
        $employeeDev2->setBusinessRole('employee');
        $employeeDev2->setPassword($this->passwordHasher->hashPassword($employeeDev2, 'password'));
        $employeeDev2->setTeam($teamDev);
        $manager->persist($employeeDev2);

        // 4. MANAGER de l'équipe Marketing
        $managerMarketing = new User();
        $managerMarketing->setUsername('manager_marketing');
        $managerMarketing->setEmail('manager.marketing@test.com');
        $managerMarketing->setFirstName('Sarah');
        $managerMarketing->setLastName('Marketing');
        $managerMarketing->setRoles(['ROLE_MANAGER']);
        $managerMarketing->setBusinessRole('manager');
        $managerMarketing->setPassword($this->passwordHasher->hashPassword($managerMarketing, 'password'));
        $manager->persist($managerMarketing);

        $teamMarketing->setManager($managerMarketing);

        // 5. EMPLOYEE de l'équipe Marketing
        $employeeMarketing = new User();
        $employeeMarketing->setUsername('employee_marketing');
        $employeeMarketing->setEmail('emp.marketing@test.com');
        $employeeMarketing->setFirstName('Charlie');
        $employeeMarketing->setLastName('Marketeur');
        $employeeMarketing->setRoles(['ROLE_USER']);
        $employeeMarketing->setBusinessRole('employee');
        $employeeMarketing->setPassword($this->passwordHasher->hashPassword($employeeMarketing, 'password'));
        $employeeMarketing->setTeam($teamMarketing);
        $manager->persist($employeeMarketing);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TeamFixtures::class,
        ];
    }
}