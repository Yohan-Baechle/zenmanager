<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Team;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use DateTimeImmutable;

class UserTest extends TestCase
{
    /**
     * This test ensures the constructor initializes optional properties correctly
     * Prevents “undefined state” issues later
     */
    public function testConstructorInitializesValues(): void
    {
        $user = new User();

        $this->assertNull($user->getId());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getFirstName());
        $this->assertNull($user->getLastName());
        $this->assertNull($user->getPhoneNumber());
        $this->assertCount(0, $user->getManagedTeams());
    }


    /**
     * This test verifies that all getters and setters work as expected
     * - Each setter should assign the correct value
     * - Each getter should return exactly what was set
     * - Check for possible formatting issue
     */
    public function testSettersAndGetters(): void
    {
        $user = new User();

        $user->setUsername('JohnDoe');
        $user->setEmail('john@example.com');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setPhoneNumber('+33123456789');
        $user->setPassword('hashed_password');
        $user->setRoles(['ROLE_EMPLOYEE']);

        $this->assertSame('JohnDoe', $user->getUsername());
        $this->assertSame('john@example.com', $user->getEmail());
        $this->assertSame('John', $user->getFirstName());
        $this->assertSame('Doe', $user->getLastName());
        $this->assertSame('+33123456789', $user->getPhoneNumber());
        $this->assertSame('hashed_password', $user->getPassword());
        $this->assertContains('ROLE_EMPLOYEE', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles()); // must always be present
    }

    /**
     * This test ensures the username field works correctly
     * - The `getUserIdentifier()` method is what Symfony Security uses
     * - It must return the username, not email or anything else
     */
    public function testUserIdentifierReturnsUsername(): void
    {
        $user = new User();
        $user->setUsername('JohnDoe');

        $this->assertSame('JohnDoe', $user->getUserIdentifier());
    }

     /**
     * This test checks the business role logic
     * - `getBusinessRole()` converts Symfony security roles into business terms (manager/employee)
     * - `setBusinessRole()` must mirror this behavior.
     */
    public function testBusinessRoleMethods(): void
    {
        $user = new User();
        $user->setBusinessRole('manager');
        $this->assertContains('ROLE_MANAGER', $user->getRoles());
        $this->assertSame('manager', $user->getBusinessRole());

        $user->setBusinessRole('employee');
        $this->assertContains('ROLE_EMPLOYEE', $user->getRoles());
        $this->assertSame('employee', $user->getBusinessRole());
    }


    /**
     * This test ensures that setting an invalid business role throws an exception
     * - Prevents silent failures or inconsistent state
     * - It will help spots error if we decide to add a new role in the future
     */
    public function testSetBusinessRoleRejectsInvalidRole(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new User();
        $user->setBusinessRole('Blafeuh blafeuh blafeuh');
    }

    /**
     * This test ensures that getting a business role when none is set throws an exception
     */
    public function testGetBusinessRoleThrowsExceptionWhenNoRole(): void
    {
        $user = new User();
        $user->setRoles([]); // no business role

        $this->expectException(\LogicException::class);
        
        $user->getBusinessRole();
    }

    public function testGetBusinessRoleThrowsExceptionWhenNoValidRole(): void
    {
        $user = new User();
        $user->setRoles(["Epic Gamer"]); // no VALID business role

        $this->expectException(\LogicException::class);
        
        $user->getBusinessRole();
    }

    /**
     * This test verifies that lifecycle callbacks for timestamps work correctly
     * - Lifecycle callbacks are not automatically triggered in unit tests.
     * - But we can call them manually to verify behavior.
     */
    public function testLifecycleCallbacksSetTimestamps(): void
    {
        $user = new User();

        $this->assertNull($user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());

        $user->setCreatedAtValue();
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());

        $initialUpdatedAt = $user->getUpdatedAt();
        sleep(1);
        $user->setUpdatedAtValue();
        $this->assertNotEquals($initialUpdatedAt, $user->getUpdatedAt());
    }

    /**
     * This test ensures the password is hashed before serialization
     * - We want to ensure no real password leaks in session serialization.
     * - `__serialize()` must hash the password before storing.
     */
    public function testPasswordIsHashedInSerialize(): void
    {
        $user = new User();
        $user->setPassword('super_secret');

        $serialized = $user->__serialize();

        $this->assertArrayHasKey("\0App\Entity\User\0password", $serialized);
        $this->assertNotSame('super_secret', $serialized["\0App\Entity\User\0password"]);
        $this->assertSame(hash('crc32c', 'super_secret'), $serialized["\0App\Entity\User\0password"]);
    }

     /**
     * This test verifies the relationship between a manager and their teams
     * The relationship between a manager and their teams should be consistent
     * Adding/removing a team should modify both sides of the relation correctly
     * (even though we're not persisting anything here).
     */
    public function testManagedTeamsRelationship(): void
    {
        $user = new User();
        $team = new Team();

        $this->assertCount(0, $user->getManagedTeams());

        $user->addManagedTeam($team);
        $this->assertCount(1, $user->getManagedTeams());
        $this->assertSame($user, $team->getManager());

        $user->removeManagedTeam($team);
        $this->assertCount(0, $user->getManagedTeams());
        $this->assertNull($team->getManager());
    }

    /**
     * This test verifies the team assignment for employees
     * Ensures team assignment works as expected for employees (not managers)
     */
    public function testTeamSetterAndGetter(): void
    {
        $user = new User();
        $team = new Team();

        $user->setTeam($team);
        $this->assertSame($team, $user->getTeam());

        $user->setTeam(null);
        $this->assertNull($user->getTeam());
    }
}