<?php

namespace App\Tests\Entity;

use App\Entity\Clock;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ClockTest extends TestCase
{
    /**
     * This test ensures a brand new Clock should have predictable null values.
     * - Prevents subtle bugs where a date might be pre-initialized.
     */
    public function testInitialStateIsNull(): void
    {
        $clock = new Clock();

        $this->assertNull($clock->getId());
        $this->assertNull($clock->getTime());
        $this->assertNull($clock->getCreatedAt());
        $this->assertNull($clock->isStatus());
        $this->assertNull($clock->getOwner());
    }

    /**
     * This test ensures basic set/get checks are the foundation of entity tests.
     * Also makes sure values survive a round trip through setters and getters.
     */
    public function testSettersAndGettersWork(): void
    {
        $clock = new Clock();
        $user = new User();
        $time = new \DateTimeImmutable('2025-01-01 12:00:00');

        $clock->setTime($time);
        $clock->setStatus(true);
        $clock->setOwner($user);

        $this->assertSame($time, $clock->getTime());
        $this->assertTrue($clock->isStatus());
        $this->assertSame($user, $clock->getOwner());

        $clock->setStatus(false);
        $this->assertFalse($clock->isStatus());
    }

    /**
     * This test ensures:
     * - Doctrine calls PrePersist lifecycle hooks automatically.
     * - In unit tests, we simulate this manually to verify behavior.
     */
    public function testLifecycleCallbackSetsCreatedAt(): void
    {
        $clock = new Clock();

        $this->assertNull($clock->getCreatedAt());

        $clock->setCreatedAtValue();

        $this->assertInstanceOf(\DateTimeImmutable::class, $clock->getCreatedAt());
    }

    /**
     * We want to make sure setOwner() never accepts null, as per the entity definition.
     */
    public function testOwnerCannotBeNull(): void
    {
        $clock = new Clock();
        $this->expectException(\InvalidArgumentException::class);
        $clock->setOwner(null);
    }

    /**
     * This test ensures that:
     * - createdAt is only set once on persist.
     * - If the lifecycle method is called again, it shouldn't change it.
     */
    public function testCreatedAtNotOverwrittenIfAlreadySet(): void
    {
        $clock = new Clock();
        $clock->setCreatedAtValue();
        $firstTimestamp = $clock->getCreatedAt();

        sleep(1); // give time to ensure the timestamp would differ
        $clock->setCreatedAtValue();
        $secondTimestamp = $clock->getCreatedAt();

        $this->assertSame($firstTimestamp, $secondTimestamp);
    }
}
