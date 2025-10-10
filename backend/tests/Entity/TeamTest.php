<?php

namespace App\Tests\Entity;

use App\Entity\Team;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
    /**
     * This ensures a new Team starts in a known clean state
     */
    public function testInitialStateIsNull(): void
    {
        $team = new Team();

        $this->assertNull($team->getId());
        $this->assertNull($team->getName());
        $this->assertNull($team->getDescription());
        $this->assertNull($team->getManager());
        $this->assertNull($team->getCreatedAt());
        $this->assertNull($team->getUpdatedAt());
        $this->assertInstanceOf(Collection::class, $team->getEmployees());
        $this->assertCount(0, $team->getEmployees());
    }

    /**
     * This test ensures that setters and getters work as expected
     */
    public function testSettersAndGettersWork(): void
    {
        $team = new Team();
        $manager = new User();

        $team->setName('The Stardust Crusaders');
        $team->setDescription('Lets go to Egypt!');
        $team->setManager($manager);

        $this->assertSame('The Stardust Crusaders', $team->getName());
        $this->assertSame('Lets go to Egypt!', $team->getDescription());
        $this->assertSame($manager, $team->getManager());
    }

    /**
     * Testing that adding employees does:
     * - add them to the employees collection
     * - set their team reference back to this team (bidirectional consistency)
     */
    public function testAddEmployeeSetsTeamReference(): void
    {
        $team = new Team();
        $employee = new User();

        $team->addEmployee($employee);

        $this->assertCount(1, $team->getEmployees());
        $this->assertTrue($team->getEmployees()->contains($employee));
        $this->assertSame($team, $employee->getTeam());
    }

    /**
     * Testing that removing employees does:
     * - remove them from the employees collection
     * - clear their team reference (bidirectional consistency)
     */
    public function testRemoveEmployeeClearsTeamReference(): void
    {
        $team = new Team();
        $employee = new User();

        $team->addEmployee($employee);
        $this->assertSame($team, $employee->getTeam());

        $team->removeEmployee($employee);

        $this->assertCount(0, $team->getEmployees());
        $this->assertNull($employee->getTeam());
    }

    /**
     * Lifecycle hook simulation:
     * - PrePersist sets createdAt and updatedAt
     * - PreUpdate only updates updatedAt
     */
    public function testLifecycleCallbacksSetTimestamps(): void
    {
        $team = new Team();

        $this->assertNull($team->getCreatedAt());
        $this->assertNull($team->getUpdatedAt());

        $team->setCreatedAtValue();

        $this->assertInstanceOf(DateTimeImmutable::class, $team->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $team->getUpdatedAt());

        $initialUpdatedAt = $team->getUpdatedAt();

        sleep(1); // make sure time actually changes
        $team->setUpdatedAtValue();

        $this->assertNotEquals($initialUpdatedAt, $team->getUpdatedAt());
    }

    /**
     * createdAt should be set only once, calling the lifecycle method again shouldn't overwrite it.
     */
    public function testCreatedAtNotOverwrittenOnSecondPersist(): void
    {
        $team = new Team();
        $team->setCreatedAtValue();
        $firstCreatedAt = $team->getCreatedAt();

        sleep(1);
        $team->setCreatedAtValue();
        $secondCreatedAt = $team->getCreatedAt();

        $this->assertSame($firstCreatedAt, $secondCreatedAt);
    }
}
