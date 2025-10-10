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

        $maxUsers = ($_ENV['APP_ENV'] ?? 'dev') === 'test' ? 5 : 30;
        $batchSize = 100;
        $counter = 0;

        // Generate clock data for the last 30 days for each user
        for ($userIndex = 1; $userIndex <= $maxUsers; $userIndex++) {
            $user = $this->getReference('user-' . $userIndex, User::class);

            // Generate 20-25 work days for each user (not all days)
            $workDays = $faker->numberBetween(20, 25);

            for ($day = 0; $day < $workDays; $day++) {
                // Random day in the last 30 days
                $daysAgo = $faker->numberBetween(0, 29);
                $date = new \DateTimeImmutable("-{$daysAgo} days");

                // Clock in - morning (between 7:00 and 10:00)
                $clockIn = new Clock();
                $clockIn->setTime($date->setTime($faker->numberBetween(7, 9), $faker->numberBetween(0, 59)))
                    ->setStatus(true)
                    ->setOwner($user);
                $manager->persist($clockIn);
                $counter++;

                // 80% chance to have a clock out (some days user might forget to clock out)
                if ($faker->boolean(80)) {
                    // Clock out - evening (between 16:00 and 20:00)
                    $clockOut = new Clock();
                    $clockOut->setTime($date->setTime($faker->numberBetween(16, 19), $faker->numberBetween(0, 59)))
                        ->setStatus(false)
                        ->setOwner($user);
                    $manager->persist($clockOut);
                    $counter++;
                }

                #cache clear, important for tests to avoid OOMKilled
                if ($counter % $batchSize === 0) {
                    $manager->flush();
                    $manager->clear(Clock::class);
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