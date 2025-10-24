<?php

namespace App\EventSubscriber;

use App\Entity\Clock;
use App\Entity\WorkingTime;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

#[AsDoctrineListener(event: Events::postPersist)]
class ClockWorkingTimeSubscriber
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Clock) {
            return;
        }

        if (false !== $entity->isStatus()) {
            return;
        }

        $this->createWorkingTimeFromClockOut($entity);
    }

    private function createWorkingTimeFromClockOut(Clock $clockOut): void
    {
        $user = $clockOut->getOwner();
        $clockOutTime = $clockOut->getTime();

        $clockIn = $this->entityManager->getRepository(Clock::class)
            ->createQueryBuilder('c')
            ->where('c.owner = :user')
            ->andWhere('c.status = true')
            ->andWhere('c.time < :clockOutTime')
            ->setParameter('user', $user)
            ->setParameter('clockOutTime', $clockOutTime)
            ->orderBy('c.time', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$clockIn) {
            $this->logger->warning('No matching clock-in found for clock-out', [
                'user_id' => $user->getId(),
                'clock_out_time' => $clockOutTime->format('Y-m-d H:i:s'),
            ]);

            return;
        }

        $existingWorkingTime = $this->entityManager->getRepository(WorkingTime::class)
            ->createQueryBuilder('wt')
            ->where('wt.owner = :user')
            ->andWhere('wt.startTime = :startTime')
            ->andWhere('wt.endTime = :endTime')
            ->setParameter('user', $user)
            ->setParameter('startTime', $clockIn->getTime())
            ->setParameter('endTime', $clockOutTime)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingWorkingTime) {
            $this->logger->info('WorkingTime already exists for this period', [
                'user_id' => $user->getId(),
                'start_time' => $clockIn->getTime()->format('Y-m-d H:i:s'),
                'end_time' => $clockOutTime->format('Y-m-d H:i:s'),
            ]);

            return;
        }

        $workingTime = new WorkingTime();
        $workingTime->setOwner($user);
        $workingTime->setStartTime($clockIn->getTime());
        $workingTime->setEndTime($clockOutTime);

        $this->entityManager->persist($workingTime);
        $this->entityManager->flush();

        $this->logger->info('WorkingTime created from clock events', [
            'user_id' => $user->getId(),
            'start_time' => $clockIn->getTime()->format('Y-m-d H:i:s'),
            'end_time' => $clockOutTime->format('Y-m-d H:i:s'),
            'working_time_id' => $workingTime->getId(),
        ]);
    }
}
