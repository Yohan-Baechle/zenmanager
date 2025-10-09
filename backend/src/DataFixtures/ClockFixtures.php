<?php

namespace App\DataFixtures;

use App\Entity\Clock;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ClockFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Generate clock data for the last 30 days for each user
        for ($userIndex = 1; $userIndex <= 30; $userIndex++) {
            $user = $this->getReference('user-' . $userIndex, User::class);

            // Generate 20-25 work days for each user (not all days)
            $workDays = $faker->numberBetween(20, 25);

            for ($day = 0; $day < $workDays; $day++) {
                // Random day in the last 30 days
                $daysAgo = $faker->numberBetween(0, 29);
                $date = new \DateTimeImmutable("-{$daysAgo} days");

                // Clock in - morning (between 7:00 and 10:00)
                $clockInHour = $faker->numberBetween(7, 9);
                $clockInMinute = $faker->numberBetween(0, 59);
                $clockInTime = $date->setTime($clockInHour, $clockInMinute);

                $clockIn = new Clock();
                $clockIn->setTime($clockInTime)
                    ->setStatus(true)
                    ->setOwner($user);

                $manager->persist($clockIn);

                // 80% chance to have a clock out (some days user might forget to clock out)
                if ($faker->boolean(80)) {
                    // Clock out - evening (between 16:00 and 20:00)
                    $clockOutHour = $faker->numberBetween(16, 19);
                    $clockOutMinute = $faker->numberBetween(0, 59);
                    $clockOutTime = $date->setTime($clockOutHour, $clockOutMinute);

                    $clockOut = new Clock();
                    $clockOut->setTime($clockOutTime)
                        ->setStatus(false)
                        ->setOwner($user);

                    $manager->persist($clockOut);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}