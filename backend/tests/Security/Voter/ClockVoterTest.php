<?php

namespace App\Tests\Security\Voter;

use App\Entity\Clock;
use App\Entity\Team;
use App\Entity\User;
use App\Security\Voter\ClockVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ClockVoterTest extends TestCase
{
    private ClockVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new ClockVoter();
    }

    public function testOwnerCanViewOwnClock(): void
    {
        $user = $this->createUser('employee');
        $clock = $this->createClock($user);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $clock, [ClockVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserCannotViewOtherUserClock(): void
    {
        $owner = $this->createUser('employee');
        $otherUser = $this->createUser('employee');
        $clock = $this->createClock($owner);
        $token = $this->createToken($otherUser);

        $result = $this->voter->vote($token, $clock, [ClockVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCanViewTeamMemberClock(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $employee = $this->createUser('employee', $team);
        $clock = $this->createClock($employee);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $clock, [ClockVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCannotViewOtherTeamClock(): void
    {
        $team1 = $this->createTeam();
        $team2 = $this->createTeam();
        $manager = $this->createUser('manager', $team1, isManager: true);
        $employee = $this->createUser('employee', $team2);
        $clock = $this->createClock($employee);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $clock, [ClockVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testAdminCanViewAnyClock(): void
    {
        $admin = $this->createUser('admin');
        $otherUser = $this->createUser('employee');
        $clock = $this->createClock($otherUser);
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $clock, [ClockVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    private function createUser(string $role, ?Team $team = null, bool $isManager = false): User
    {
        $user = $this->createMock(User::class);

        $roles = match ($role) {
            'admin' => ['ROLE_ADMIN'],
            'manager' => ['ROLE_MANAGER'],
            default => ['ROLE_USER'],
        };

        $user->method('getRoles')->willReturn($roles);

        if (null !== $team) {
            $user->method('getTeam')->willReturn($team);

            if ($isManager) {
                $managedTeams = $this->createMock(\Doctrine\Common\Collections\Collection::class);
                $managedTeams->method('contains')->willReturnCallback(
                    fn ($checkTeam) => $checkTeam === $team
                );
                $user->method('getManagedTeams')->willReturn($managedTeams);
            }
        }

        return $user;
    }

    private function createClock(User $owner): Clock
    {
        $clock = $this->createMock(Clock::class);
        $clock->method('getOwner')->willReturn($owner);

        if (null !== $owner->getTeam()) {
            $clock->method('getOwner')->willReturn($owner);
        }

        return $clock;
    }

    private function createTeam(): Team
    {
        return $this->createMock(Team::class);
    }

    private function createToken(User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }
}
