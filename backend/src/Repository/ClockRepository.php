<?php

namespace App\Repository;

use App\Entity\Clock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Clock>
 */
class ClockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Clock::class);
    }

    public function countLateArrivals(?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate, ?int $userId): int
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.status = true');

        if ($startDate) {
            $qb->andWhere('c.time >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('c.time <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        if ($userId) {
            $qb->andWhere('c.owner = :userId')
               ->setParameter('userId', $userId);
        }

        $qb->orderBy('c.time', 'ASC');
        
        $clocks = $qb->getQuery()->getResult();
        
        $dailyFirstArrivals = [];
        foreach ($clocks as $clock) {
            $date = $clock->getTime()->format('Y-m-d');
            $ownerId = $clock->getOwner()->getId();
            $key = $date . '_' . $ownerId;
            
            if (!isset($dailyFirstArrivals[$key]) || 
                $clock->getTime() < $dailyFirstArrivals[$key]->getTime()) {
                $dailyFirstArrivals[$key] = $clock;
            }
        }
        
        $lateCount = 0;
        foreach ($dailyFirstArrivals as $clock) {
            $hour = (int)$clock->getTime()->format('H');
            $minute = (int)$clock->getTime()->format('i');
            
            if ($hour > 8 || ($hour === 8 && $minute >= 30)) {
                $lateCount++;
            }
        }
        
        return $lateCount;
    }

    public function countEarlyDepartures(?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate, ?int $userId): int
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.status = false');

        if ($startDate) {
            $qb->andWhere('c.time >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('c.time <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        if ($userId) {
            $qb->andWhere('c.owner = :userId')
               ->setParameter('userId', $userId);
        }

        $qb->orderBy('c.time', 'DESC');
        
        $clocks = $qb->getQuery()->getResult();
        
        $dailyLastDepartures = [];
        foreach ($clocks as $clock) {
            $date = $clock->getTime()->format('Y-m-d');
            $ownerId = $clock->getOwner()->getId();
            $key = $date . '_' . $ownerId;
            
            if (!isset($dailyLastDepartures[$key]) || 
                $clock->getTime() > $dailyLastDepartures[$key]->getTime()) {
                $dailyLastDepartures[$key] = $clock;
            }
        }
        
        $earlyCount = 0;
        foreach ($dailyLastDepartures as $clock) {
            $hour = (int)$clock->getTime()->format('H');
            $minute = (int)$clock->getTime()->format('i');
            
            if ($hour < 16 || ($hour === 16 && $minute < 30)) {
                $earlyCount++;
            }
        }
        
        return $earlyCount;
    }


    public function countIncompleteDays(?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate, ?int $userId): int
    {
        $qb = $this->createQueryBuilder('c');

        if ($startDate) {
            $qb->andWhere('c.time >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('c.time <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        if ($userId) {
            $qb->andWhere('c.owner = :userId')
               ->setParameter('userId', $userId);
        }

        $clocks = $qb->getQuery()->getResult();
        
        $dailyClockCounts = [];
        foreach ($clocks as $clock) {
            $date = $clock->getTime()->format('Y-m-d');
            $ownerId = $clock->getOwner()->getId();
            $key = $date . '_' . $ownerId;
            
            if (!isset($dailyClockCounts[$key])) {
                $dailyClockCounts[$key] = 0;
            }
            $dailyClockCounts[$key]++;
        }
        
        $incompleteCount = 0;
        foreach ($dailyClockCounts as $count) {
            if ($count % 2 === 1) {
                $incompleteCount++;
            }
        }
        
        return $incompleteCount;
    }

    
    public function countTotalExits(?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate, ?int $userId): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = false');

        if ($startDate) {
            $qb->andWhere('c.time >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('c.time <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        if ($userId) {
            $qb->andWhere('c.owner = :userId')
               ->setParameter('userId', $userId);
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
