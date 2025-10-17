<?php

namespace App\Tests\Integration;

use App\Entity\Clock;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use DateTimeImmutable;

class ClockUserIntegrationTest extends KernelTestCase
{
    private $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->em !== null) {
            $this->em->close();
        }
    }

    /**
     * Ensure a Clock is correctly linked to its User owner and persisted.
     */
    public function testPersistClockWithOwner(): void
    {
        $user = (new User())
            ->setUsername('ClockHolder')
            ->setEmail('clock@example.com')
            ->setPassword('1234')
            ->setFirstName('Tick')
            ->setLastName('Tock')
            ->setRoles(['ROLE_EMPLOYEE']);

        $clock = (new Clock())
            ->setOwner($user)
            ->setStatus(true)
            ->setTime(new DateTimeImmutable());

        $this->em->persist($user);
        $this->em->persist($clock);
        $this->em->flush();
        $this->em->clear();

        $foundClock = $this->em->getRepository(Clock::class)->findOneBy(['status' => true]);
        $this->assertNotNull($foundClock);
        $this->assertSame('ClockHolder', $foundClock->getOwner()->getUsername());
    }
}
