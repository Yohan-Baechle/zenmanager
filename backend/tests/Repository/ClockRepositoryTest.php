<?php

namespace App\Tests\Repository;

use App\Entity\Clock;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClockRepositoryTest extends KernelTestCase
{
    private \Doctrine\ORM\EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
    }

    /**
     * Ensures a Clock is correctly linked to its owner and saved to DB.
     */
    public function testPersistClockWithOwner(): void
    {
        $user = (new User())
            ->setUsername('ClockyMan')
            ->setEmail('clocky@example.com')
            ->setFirstName('Clocky')
            ->setLastName('Man')
            ->setPassword('hashhash')
            ->setRoles(['ROLE_EMPLOYEE']);

        $clock = (new Clock())
            ->setOwner($user)
            ->setStatus(true)
            ->setTime(new \DateTimeImmutable('2025-10-17 08:00:00'));

        $this->em->persist($user);
        $this->em->persist($clock);
        $this->em->flush();
        $this->em->clear();

        $foundClock = $this->em->getRepository(Clock::class)->findOneBy(['owner' => $user]);
        $this->assertNotNull($foundClock);
        $this->assertSame('ClockyMan', $foundClock->getOwner()->getUsername());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->em !== null) {
            $this->em->close();
        }
    }
}
