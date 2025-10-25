<?php

namespace App\Tests\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\WorkingTime;
use App\Security\Voter\WorkingTimeVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class WorkingTimeVoterTest extends TestCase
{
    private WorkingTimeVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new WorkingTimeVoter();
    }

    public function testOwnerCanViewOwnWorkingTime(): void
    {
        $user = $this->createUser('employee');
        $workingTime = $this->createWorkingTime($user);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserCannotViewOtherUserWorkingTime(): void
    {
        $owner = $this->createUser('employee');
        $otherUser = $this->createUser('employee');
        $workingTime = $this->createWorkingTime($owner);
        $token = $this->createToken($otherUser);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCanViewTeamMemberWorkingTime(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $employee = $this->createUser('employee', $team);
        $workingTime = $this->createWorkingTime($employee);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCannotViewOtherTeamWorkingTime(): void
    {
        $team1 = $this->createTeam();
        $team2 = $this->createTeam();
        $manager = $this->createUser('manager', $team1, isManager: true);
        $employee = $this->createUser('employee', $team2);
        $workingTime = $this->createWorkingTime($employee);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testAdminCanViewAnyWorkingTime(): void
    {
        $admin = $this->createUser('admin');
        $otherUser = $this->createUser('employee');
        $workingTime = $this->createWorkingTime($otherUser);
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testOwnerCanEditOwnWorkingTime(): void
    {
        $user = $this->createUser('employee');
        $workingTime = $this->createWorkingTime($user);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserCannotEditOtherUserWorkingTime(): void
    {
        $owner = $this->createUser('employee');
        $otherUser = $this->createUser('employee');
        $workingTime = $this->createWorkingTime($owner);
        $token = $this->createToken($otherUser);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCanEditTeamMemberWorkingTime(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $employee = $this->createUser('employee', $team);
        $workingTime = $this->createWorkingTime($employee);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testAdminCanEditAnyWorkingTime(): void
    {
        $admin = $this->createUser('admin');
        $otherUser = $this->createUser('employee');
        $workingTime = $this->createWorkingTime($otherUser);
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testOwnerCanDeleteOwnWorkingTime(): void
    {
        $user = $this->createUser('employee');
        $workingTime = $this->createWorkingTime($user);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserCannotDeleteOtherUserWorkingTime(): void
    {
        $owner = $this->createUser('employee');
        $otherUser = $this->createUser('employee');
        $workingTime = $this->createWorkingTime($owner);
        $token = $this->createToken($otherUser);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCanDeleteTeamMemberWorkingTime(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $employee = $this->createUser('employee', $team);
        $workingTime = $this->createWorkingTime($employee);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testAdminCanDeleteAnyWorkingTime(): void
    {
        $admin = $this->createUser('admin');
        $otherUser = $this->createUser('employee');
        $workingTime = $this->createWorkingTime($otherUser);
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $workingTime, [WorkingTimeVoter::DELETE]);

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

    private function createWorkingTime(User $owner): WorkingTime
    {
        $workingTime = $this->createMock(WorkingTime::class);
        $workingTime->method('getOwner')->willReturn($owner);

        return $workingTime;
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
