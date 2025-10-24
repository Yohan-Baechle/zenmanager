<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WorkingTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkingTime>
 */
class WorkingTimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkingTime::class);
    }

    public function findByUserAndPeriod(User $user, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('wt')
            ->where('wt.owner = :user')
            ->andWhere('wt.startTime >= :start')
            ->andWhere('wt.endTime <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('wt.startTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function calculateTotalWorkingHours(?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate, ?int $userId, ?int $teamId = null): float
    {
        $qb = $this->createQueryBuilder('wt');

        if ($startDate) {
            $qb->andWhere('wt.startTime >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('wt.endTime <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        if ($userId) {
            $qb->andWhere('wt.owner = :userId')
               ->setParameter('userId', $userId);
        }

        if ($teamId) {
            $qb->join('wt.owner', 'u')
               ->andWhere('u.team = :teamId')
               ->setParameter('teamId', $teamId);
        }

        $workingTimes = $qb->getQuery()->getResult();

        $totalHours = 0;
        foreach ($workingTimes as $workingTime) {
            $start = $workingTime->getStartTime();
            $end = $workingTime->getEndTime();

            if ($start && $end) {
                $interval = $start->diff($end);
                $hours = $interval->h + ($interval->days * 24);
                $hours += $interval->i / 60;
                $hours += $interval->s / 3600;
                $totalHours += $hours;
            }
        }

        return round($totalHours, 2);
    }

    public function countPresentDays(?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate, ?int $userId, ?int $teamId = null): int
    {
        $qb = $this->createQueryBuilder('wt');

        if ($startDate) {
            $qb->andWhere('wt.startTime >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('wt.endTime <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        if ($userId) {
            $qb->andWhere('wt.owner = :userId')
               ->setParameter('userId', $userId);
        }

        if ($teamId) {
            $qb->join('wt.owner', 'u')
               ->andWhere('u.team = :teamId')
               ->setParameter('teamId', $teamId);
        }

        $workingTimes = $qb->getQuery()->getResult();

        $distinctDates = [];
        foreach ($workingTimes as $workingTime) {
            $date = $workingTime->getStartTime()->format('Y-m-d');
            $distinctDates[$date] = true;
        }

        return count($distinctDates);
    }
}
