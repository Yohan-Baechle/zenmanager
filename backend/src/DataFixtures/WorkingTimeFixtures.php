<?php

namespace App\DataFixtures;

use App\Entity\WorkingTime;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class WorkingTimeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Generate working time data for the last 30 days for each user
        for ($userIndex = 1; $userIndex <= 30; $userIndex++) {
            $user = $this->getReference('user-' . $userIndex, User::class);

            // Generate 20-25 work days for each user (not all days)
            $workDays = $faker->numberBetween(20, 25);

            for ($day = 0; $day < $workDays; $day++) {
                // Random day in the last 30 days
                $daysAgo = $faker->numberBetween(0, 29);
                $date = new \DateTimeImmutable("-{$daysAgo} days");

                $scenario = $faker->numberBetween(1, 100);

                if ($scenario <= 60) {
                    // Scenario 1 (60%): Normal day - 2 WorkingTime (morning + afternoon with lunch break)

                    // Morning session (arrival between 7:00-9:30, departure for lunch 11:30-13:00)
                    $morningStart = $date->setTime(
                        $faker->numberBetween(7, 9),
                        $faker->numberBetween(0, 59)
                    );
                    $lunchStart = $date->setTime(
                        $faker->numberBetween(11, 12),
                        $faker->numberBetween(30, 59)
                    );

                    $morningSession = new WorkingTime();
                    $morningSession->setStartTime($morningStart)
                        ->setEndTime($lunchStart)
                        ->setOwner($user);
                    $manager->persist($morningSession);

                    // Afternoon session (return from lunch 13:00-14:30, departure 16:30-20:00)
                    $afternoonStart = $date->setTime(
                        $faker->numberBetween(13, 14),
                        $faker->numberBetween(0, 59)
                    );
                    $afternoonEnd = $date->setTime(
                        $faker->numberBetween(17, 19),
                        $faker->numberBetween(0, 59)
                    );

                    $afternoonSession = new WorkingTime();
                    $afternoonSession->setStartTime($afternoonStart)
                        ->setEndTime($afternoonEnd)
                        ->setOwner($user);
                    $manager->persist($afternoonSession);

                } elseif ($scenario <= 80) {
                    // Scenario 2 (20%): Forgot to clock out for lunch - 1 full day WorkingTime
                    $dayStart = $date->setTime(
                        $faker->numberBetween(7, 9),
                        $faker->numberBetween(0, 59)
                    );
                    $dayEnd = $date->setTime(
                        $faker->numberBetween(17, 19),
                        $faker->numberBetween(0, 59)
                    );

                    $fullDaySession = new WorkingTime();
                    $fullDaySession->setStartTime($dayStart)
                        ->setEndTime($dayEnd)
                        ->setOwner($user);
                    $manager->persist($fullDaySession);

                } elseif ($scenario <= 90) {
                    // Scenario 3 (10%): Only morning session (forgot afternoon or left early)
                    $morningStart = $date->setTime(
                        $faker->numberBetween(7, 9),
                        $faker->numberBetween(0, 59)
                    );
                    $morningEnd = $date->setTime(
                        $faker->numberBetween(11, 13),
                        $faker->numberBetween(0, 59)
                    );

                    $morningOnly = new WorkingTime();
                    $morningOnly->setStartTime($morningStart)
                        ->setEndTime($morningEnd)
                        ->setOwner($user);
                    $manager->persist($morningOnly);

                } else {
                    // Scenario 4 (10%): Only afternoon session (arrived late or forgot morning)
                    $afternoonStart = $date->setTime(
                        $faker->numberBetween(13, 15),
                        $faker->numberBetween(0, 59)
                    );
                    $afternoonEnd = $date->setTime(
                        $faker->numberBetween(17, 19),
                        $faker->numberBetween(0, 59)
                    );

                    $afternoonOnly = new WorkingTime();
                    $afternoonOnly->setStartTime($afternoonStart)
                        ->setEndTime($afternoonEnd)
                        ->setOwner($user);
                    $manager->persist($afternoonOnly);
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