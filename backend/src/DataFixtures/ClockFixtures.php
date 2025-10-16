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
        $isTest = ($_ENV['APP_ENV'] ?? 'dev') === 'test';

        // Create hardcoded clocks for specific test users
        $this->createHardcodedClocks($manager);

        if (!$isTest) {
            $this->createRandomClocks($manager, $faker);
        }

        // Final flush
        $manager->flush();
        $manager->clear();
    }

    /**
     * Create hardcoded clocks for test users (Theking, TheJoker, etc.)
     */
    private function createHardcodedClocks(ObjectManager $manager): void
    {
        // Clocks for Theking (manager)
        $theKing = $this->getReference('user-manager', User::class);

        // Last 7 days of work for Theking
        for ($day = 0; $day < 7; $day++) {
            $date = new \DateTimeImmutable("-{$day} days");

            // Clock in at 8:00
            $clockIn = new Clock();
            $clockIn->setTime($date->setTime(8, 0))
                ->setStatus(true)
                ->setOwner($theKing);
            $manager->persist($clockIn);

            // Clock out at 17:00
            $clockOut = new Clock();
            $clockOut->setTime($date->setTime(17, 0))
                ->setStatus(false)
                ->setOwner($theKing);
            $manager->persist($clockOut);
        }

        // Clocks for TheJoker (employee)
        $theJoker = $this->getReference('user-employee', User::class);

        // Last 7 days of work for TheJoker
        for ($day = 0; $day < 7; $day++) {
            $date = new \DateTimeImmutable("-{$day} days");

            // Clock in at 9:00
            $clockIn = new Clock();
            $clockIn->setTime($date->setTime(9, 0))
                ->setStatus(true)
                ->setOwner($theJoker);
            $manager->persist($clockIn);

            // Clock out at 18:00
            $clockOut = new Clock();
            $clockOut->setTime($date->setTime(18, 0))
                ->setStatus(false)
                ->setOwner($theJoker);
            $manager->persist($clockOut);
        }

        // Clocks for standard test users (admin, Alice, Bob, Sarah)
        for ($userIndex = 1; $userIndex <= 6; $userIndex++) {
            $user = $this->getReference('user-' . $userIndex, User::class);

            for ($day = 0; $day < 5; $day++) {
                $date = new \DateTimeImmutable("-{$day} days");

                // Clock in
                $clockIn = new Clock();
                $clockIn->setTime($date->setTime(8 + $userIndex % 2, 30))
                    ->setStatus(true)
                    ->setOwner($user);
                $manager->persist($clockIn);

                // Clock out
                $clockOut = new Clock();
                $clockOut->setTime($date->setTime(17 + $userIndex % 2, 30))
                    ->setStatus(false)
                    ->setOwner($user);
                $manager->persist($clockOut);
            }
        }

        $manager->flush();
    }

    /**
     * Create random clocks for generated users
     */
    private function createRandomClocks(ObjectManager $manager, $faker): void
    {
        $maxUsers = 30;
        $batchSize = 20;
        $counter = 0;

        // Generated users start at index 7 now
        for ($userIndex = 7; $userIndex <= $maxUsers + 6; $userIndex++) {
            $workDays = $faker->numberBetween(20, 25);

            for ($day = 0; $day < $workDays; $day++) {
                $user = $this->getReference('user-' . $userIndex, User::class);

                $daysAgo = $faker->numberBetween(0, 29);
                $date = new \DateTimeImmutable("-{$daysAgo} days");

                // Clock in
                $clockIn = new Clock();
                $clockIn->setTime($date->setTime($faker->numberBetween(7, 9), $faker->numberBetween(0, 59)))
                    ->setStatus(true)
                    ->setOwner($user);
                $manager->persist($clockIn);
                $counter++;

                // Clock out (80% chance)
                if ($faker->boolean(80)) {
                    $clockOut = new Clock();
                    $clockOut->setTime($date->setTime($faker->numberBetween(16, 19), $faker->numberBetween(0, 59)))
                        ->setStatus(false)
                        ->setOwner($user);
                    $manager->persist($clockOut);
                    $counter++;
                }

                // Flush et clear plus fréquemment
                if ($counter % $batchSize === 0) {
                    $manager->flush();
                    $manager->clear();
                    gc_collect_cycles();
                }
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
