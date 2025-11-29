<?php

namespace App\DataFixtures;

use App\Entity\ClockRequest;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ClockRequestFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->loadTestClockRequests($manager);
        $manager->flush();
    }

    private function loadTestClockRequests(ObjectManager $manager): void
    {
        $theJoker = $this->getReference('user-3', User::class);
        $alice = $this->getReference('user-4', User::class);
        $bob = $this->getReference('user-5', User::class);

        $request1 = new ClockRequest();
        $request1->setUser($theJoker)
            ->setType('CREATE')
            ->setRequestedTime(new \DateTimeImmutable('today 08:30'))
            ->setRequestedStatus(true)
            ->setStatus('PENDING')
            ->setReason("J'ai oublié de badger en arrivant ce matin, j'étais en retard à cause des transports.");
        $manager->persist($request1);
        $this->addReference('clock-request-1', $request1);

        $request2 = new ClockRequest();
        $request2->setUser($alice)
            ->setType('CREATE')
            ->setRequestedTime(new \DateTimeImmutable('yesterday 18:00'))
            ->setRequestedStatus(false)
            ->setStatus('PENDING')
            ->setReason("J'ai oublié de badger hier soir en partant. J'étais partie à 18h mais j'étais pressée pour récupérer mes enfants.");
        $manager->persist($request2);
        $this->addReference('clock-request-2', $request2);

        $request3 = new ClockRequest();
        $request3->setUser($bob)
            ->setType('CREATE')
            ->setRequestedTime(new \DateTimeImmutable('today 10:00'))
            ->setRequestedStatus(true)
            ->setStatus('PENDING')
            ->setReason("Rendez-vous médical ce matin, je suis arrivé au bureau à 10h. J'ai oublié de badger en arrivant.");
        $manager->persist($request3);
        $this->addReference('clock-request-3', $request3);

        $request4 = new ClockRequest();
        $request4->setUser($theJoker)
            ->setType('CREATE')
            ->setRequestedTime(new \DateTimeImmutable('-3 days 09:00'))
            ->setRequestedStatus(true)
            ->setStatus('APPROVED')
            ->setReason("Oubli de badgeage en arrivant ce jour-là. J'étais en réunion dès mon arrivée.");
        $manager->persist($request4);
        $this->addReference('clock-request-4', $request4);

        $request5 = new ClockRequest();
        $request5->setUser($alice)
            ->setType('CREATE')
            ->setRequestedTime(new \DateTimeImmutable('-5 days 07:00'))
            ->setRequestedStatus(true)
            ->setStatus('REJECTED')
            ->setReason("Demande de badgeage à 7h du matin.");
        $manager->persist($request5);
        $this->addReference('clock-request-5', $request5);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
