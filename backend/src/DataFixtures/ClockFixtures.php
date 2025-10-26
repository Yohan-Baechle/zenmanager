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

        $this->createHardcodedClocks($manager);

        if (!$isTest) {
            $this->createRandomClocks($manager, $faker);
        }

        $manager->flush();
        $manager->clear();
    }

    private function createHardcodedClocks(ObjectManager $manager): void
    {
        $theKing = $this->getReference('user-manager', User::class);

        for ($day = 1; $day <= 7; ++$day) {
            $date = new \DateTimeImmutable("-{$day} days");

            $clockIn = new Clock();
            $clockIn->setTime($date->setTime(8, 0))
                ->setStatus(true)
                ->setOwner($theKing);
            $manager->persist($clockIn);

            $clockOut = new Clock();
            $clockOut->setTime($date->setTime(17, 0))
                ->setStatus(false)
                ->setOwner($theKing);
            $manager->persist($clockOut);
        }

        $theJoker = $this->getReference('user-employee', User::class);

        for ($day = 1; $day <= 7; ++$day) {
            $date = new \DateTimeImmutable("-{$day} days");

            $clockIn = new Clock();
            $clockIn->setTime($date->setTime(9, 0))
                ->setStatus(true)
                ->setOwner($theJoker);
            $manager->persist($clockIn);

            $clockOut = new Clock();
            $clockOut->setTime($date->setTime(18, 0))
                ->setStatus(false)
                ->setOwner($theJoker);
            $manager->persist($clockOut);
        }

        for ($userIndex = 1; $userIndex <= 6; ++$userIndex) {
            $user = $this->getReference('user-'.$userIndex, User::class);

            for ($day = 1; $day <= 5; ++$day) {
                $date = new \DateTimeImmutable("-{$day} days");

                $clockIn = new Clock();
                $clockIn->setTime($date->setTime(8 + $userIndex % 2, 30))
                    ->setStatus(true)
                    ->setOwner($user);
                $manager->persist($clockIn);

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
     * @param \Faker\Generator $faker
     */
    private function createRandomClocks(ObjectManager $manager, $faker): void
    {
        $maxUsers = 30;
        $batchSize = 20;
        $counter = 0;

        for ($userIndex = 7; $userIndex <= $maxUsers + 6; ++$userIndex) {
            $workDays = $faker->numberBetween(20, 25);

            for ($day = 0; $day < $workDays; ++$day) {
                $user = $this->getReference('user-'.$userIndex, User::class);

                $daysAgo = $faker->numberBetween(1, 29);
                $date = new \DateTimeImmutable("-{$daysAgo} days");

                $clockIn = new Clock();
                $clockIn->setTime($date->setTime($faker->numberBetween(7, 9), $faker->numberBetween(0, 59)))
                    ->setStatus(true)
                    ->setOwner($user);
                $manager->persist($clockIn);
                ++$counter;

                if ($faker->boolean(80)) {
                    $clockOut = new Clock();
                    $clockOut->setTime($date->setTime($faker->numberBetween(16, 19), $faker->numberBetween(0, 59)))
                        ->setStatus(false)
                        ->setOwner($user);
                    $manager->persist($clockOut);
                    ++$counter;
                }

                if (0 === $counter % $batchSize) {
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
