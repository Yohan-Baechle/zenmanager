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
        // TheJoker (user-3) - employé de l'équipe Dev managée par TheKing
        $theJoker = $this->getReference('user-3', User::class);

        // Alice (user-4) - employée de l'équipe Dev managée par TheKing
        $alice = $this->getReference('user-4', User::class);

        // Bob (user-5) - employé de l'équipe Dev managée par TheKing
        $bob = $this->getReference('user-5', User::class);

        // Demande PENDING de TheJoker - oubli de badgeage d'entrée
        $request1 = new ClockRequest();
        $request1->setUser($theJoker)
            ->setType('CREATE')
            ->setRequestedTime(new \DateTimeImmutable('today 08:30'))
            ->setRequestedStatus(true) // true = entrée
            ->setStatus('PENDING')
            ->setReason("J'ai oublié de badger en arrivant ce matin, j'étais en retard à cause des transports.");
        $manager->persist($request1);
        $this->addReference('clock-request-1', $request1);

        // Demande PENDING d'Alice - oubli de badgeage de sortie
        $request2 = new ClockRequest();
        $request2->setUser($alice)
            ->setType('CREATE')
            ->setRequestedTime(new \DateTimeImmutable('yesterday 18:00'))
            ->setRequestedStatus(false) // false = sortie
            ->setStatus('PENDING')
            ->setReason("J'ai oublié de badger hier soir en partant. J'étais partie à 18h mais j'étais pressée pour récupérer mes enfants.");
        $manager->persist($request2);
        $this->addReference('clock-request-2', $request2);

        // Demande PENDING de Bob - oubli de badgeage d'entrée (rendez-vous médical)
        $request3 = new ClockRequest();
        $request3->setUser($bob)
            ->setType('CREATE')
            ->setRequestedTime(new \DateTimeImmutable('today 10:00'))
            ->setRequestedStatus(true) // true = entrée
            ->setStatus('PENDING')
            ->setReason("Rendez-vous médical ce matin, je suis arrivé au bureau à 10h. J'ai oublié de badger en arrivant.");
        $manager->persist($request3);
        $this->addReference('clock-request-3', $request3);

        // Demande APPROVED de TheJoker (déjà approuvée par le passé)
        $request4 = new ClockRequest();
        $request4->setUser($theJoker)
            ->setType('CREATE')
            ->setRequestedTime(new \DateTimeImmutable('-3 days 09:00'))
            ->setRequestedStatus(true)
            ->setStatus('APPROVED')
            ->setReason("Oubli de badgeage en arrivant ce jour-là. J'étais en réunion dès mon arrivée.");
        $manager->persist($request4);
        $this->addReference('clock-request-4', $request4);

        // Demande REJECTED d'Alice (rejetée car incohérente)
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
