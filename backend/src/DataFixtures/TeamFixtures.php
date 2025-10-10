<?php

namespace App\DataFixtures;

use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TeamFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $maxTeams = ($_ENV['APP_ENV'] ?? 'dev') === 'test' ? 5 : 30;
        // Create 30 teams (without managers for now)
        for ($i = 1; $i <= $maxTeams; $i++) {
            $team = new Team();
            $team->setName($faker->company() . ' Team')
                ->setDescription($faker->catchPhrase());

            $manager->persist($team);
            $this->addReference('team-' . $i, $team);
        }

        $manager->flush();
    }
}