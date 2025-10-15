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
     * createdAt should be set only once, calling the lifecycle method again shouldn't overwrite it
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

    /**
     * Ensures a manager cannot be added as an employee of their own team
     */
    public function testManagerCannotBeEmployeeOfOwnTeam(): void
    {
        $team = new Team();
        $manager = new User();

        $team->setManager($manager);

        $this->expectException(\LogicException::class);

        $team->addEmployee($manager);
    }

    /**
     * Ensures adding the same employee twice does not duplicate
     */
    public function testAddingSameEmployeeTwiceDoesNotDuplicate(): void
    {
        $team = new Team();
        $user = new User();

        $team->addEmployee($user);
        $team->addEmployee($user);

        $this->assertCount(1, $team->getEmployees());
    }

    /**
     * Ensures removing the manager does not touch what's in the team
     */
    public function testRemovingManagerLeavesEmployeesUntouched(): void
    {
        $team = new Team();
        $manager = new User();
        $employee = new User();

        $team->setManager($manager);
        $team->addEmployee($employee);

        $team->setManager(null);

        $this->assertNull($team->getManager());
        $this->assertCount(1, $team->getEmployees());
    }


    /**
     * Ensures an employee cannot belong to multiple teams
     */
    public function testEmployeeCantBelongToMultipleTeams(): void
    {
        $team1 = new Team();
        $team2 = new Team();
        $employee = new User();

        $team1->addEmployee($employee);

        $this->expectException(InvalidArgumentException::class);

        $team2->addEmployee($employee);
    }

    /**
     * This test verifies the name length constraints for a Team
     */
    public function testTeamNameLengthValidation(): void
    {
        $min = Team::MIN_NAME_LENGTH;
        $max = Team::MAX_NAME_LENGTH;

        $validCases = [
            str_repeat('a', $min),
            str_repeat('a', $max),
        ];

        foreach ($validCases as $teamName) {
            $team = new Team();
            $team->setName($teamName);
            $this->assertSame($teamName, $team->getName());
        }

       $invalidCases = [
            '',
            str_repeat('a', $min - 1),
            str_repeat('a', $max + 1),
        ];

        foreach ($invalidCases as $teamName) {
            $this->expectException(\InvalidArgumentException::class);
            $team = new Team();
            $team->setName($teamName);
        }
    }

    /**
     * This test verifies the description length constraints for a Team
     * watch out for the fact description can be empty
     */
    public function testTeamDescriptionLengthValidation(): void
    {
        $min = Team::MIN_DESCRIPTION_LENGTH;
        $max = Team::MAX_DESCRIPTION_LENGTH;

        $validCases = [
            '',
            str_repeat('a', $min),
            str_repeat('a', $max),
        ];

        foreach ($validCases as $teamDescription) {
            $team = new Team();
            $team->setDescription($teamDescription);
            $this->assertSame($teamDescription, $team->getDescription());
        }

       $invalidCases = [
            str_repeat('a', $max + 1),
        ];

        if ($min > 0) {
            $invalidCases[] = str_repeat('a', $min - 1);
        }

        foreach ($invalidCases as $teamDescription) {
            $this->expectException(\InvalidArgumentException::class);
            $team = new Team();
            $team->setDescription($teamDescription);
        }
    }

}
