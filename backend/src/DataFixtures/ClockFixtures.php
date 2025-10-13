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
        $batchSize = 20; // Réduit de 100 à 20 pour libérer la mémoire plus souvent
        $counter = 0;

        for ($userIndex = 1; $userIndex <= $maxUsers; $userIndex++) {
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
                    $manager->clear(); // Clear TOUT, pas juste Clock
                    gc_collect_cycles(); // Force le garbage collector PHP
                }
            }
        }

        // Final flush
        $manager->flush();
        $manager->clear();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
