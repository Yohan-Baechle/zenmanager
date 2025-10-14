<?php

namespace App\Repository;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetToken>
 */
class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    /**
     * Find a valid token for a user
     */
    public function findValidTokenByUser(User $user): ?PasswordResetToken
    {
        return $this->createQueryBuilder('prt')
            ->where('prt.user = :user')
            ->andWhere('prt.isUsed = false')
            ->andWhere('prt.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('prt.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find a token by its hash value
     */
    public function findByToken(string $token): ?PasswordResetToken
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * Delete expired tokens (cleanup)
     */
    public function deleteExpiredTokens(): int
    {
        return $this->createQueryBuilder('prt')
            ->delete()
            ->where('prt.expiresAt < :now')
            ->orWhere('prt.isUsed = true')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    /**
     * Invalidate all existing tokens for a user
     */
    public function invalidateUserTokens(User $user): void
    {
        $this->createQueryBuilder('prt')
            ->update()
            ->set('prt.isUsed', 'true')
            ->where('prt.user = :user')
            ->andWhere('prt.isUsed = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
