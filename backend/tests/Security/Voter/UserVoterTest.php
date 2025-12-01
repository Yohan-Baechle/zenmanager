<?php

namespace App\Tests\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use App\Security\Voter\UserVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class UserVoterTest extends TestCase
{
    private UserVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new UserVoter();
    }

    public function testAdminCanViewAnyUser(): void
    {
        $admin = $this->createUser('admin');
        $otherUser = $this->createUser('employee');
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $otherUser, [UserVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserCanViewSelf(): void
    {
        $user = $this->createUser('employee');
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $user, [UserVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCanViewTeamMember(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $employee = $this->createUser('employee', $team);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $employee, [UserVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testEveryoneCanViewUsers(): void
    {
        $user = $this->createUser('employee');
        $otherUser = $this->createUser('employee');
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $otherUser, [UserVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testAdminCanEditAnyUser(): void
    {
        $admin = $this->createUser('admin');
        $otherUser = $this->createUser('employee');
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $otherUser, [UserVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserCanEditSelf(): void
    {
        $user = $this->createUser('employee');
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $user, [UserVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCanEditTeamMember(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $employee = $this->createUser('employee', $team);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $employee, [UserVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserCannotEditOtherUser(): void
    {
        $user = $this->createUser('employee');
        $otherUser = $this->createUser('employee');
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $otherUser, [UserVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCannotEditOtherTeamMember(): void
    {
        $team1 = $this->createTeam();
        $team2 = $this->createTeam();
        $manager = $this->createUser('manager', $team1, isManager: true);
        $employee = $this->createUser('employee', $team2);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $employee, [UserVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testAdminCanDeleteUser(): void
    {
        $admin = $this->createUser('admin');
        $otherUser = $this->createUser('employee');
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $otherUser, [UserVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testAdminCannotDeleteSelf(): void
    {
        $admin = $this->createUser('admin');
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $admin, [UserVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testUserCannotDeleteSelf(): void
    {
        $user = $this->createUser('employee');
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $user, [UserVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCannotDeleteTeamMember(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $employee = $this->createUser('employee', $team);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $employee, [UserVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCannotDeleteOtherTeamMember(): void
    {
        $team1 = $this->createTeam();
        $team2 = $this->createTeam();
        $manager = $this->createUser('manager', $team1, isManager: true);
        $employee = $this->createUser('employee', $team2);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $employee, [UserVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testAdminCanViewAnyClock(): void
    {
        $admin = $this->createUser('admin');
        $otherUser = $this->createUser('employee');
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $otherUser, [UserVoter::VIEW_CLOCKS]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserCanViewOwnClocks(): void
    {
        $user = $this->createUser('employee');
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $user, [UserVoter::VIEW_CLOCKS]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCanViewTeamMemberClocks(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $employee = $this->createUser('employee', $team);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $employee, [UserVoter::VIEW_CLOCKS]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserCannotViewOtherUserClocks(): void
    {
        $user = $this->createUser('employee');
        $otherUser = $this->createUser('employee');
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $otherUser, [UserVoter::VIEW_CLOCKS]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCannotViewOtherTeamClocks(): void
    {
        $team1 = $this->createTeam();
        $team2 = $this->createTeam();
        $manager = $this->createUser('manager', $team1, isManager: true);
        $employee = $this->createUser('employee', $team2);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $employee, [UserVoter::VIEW_CLOCKS]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCanViewUserWithoutTeam(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $userWithoutTeam = $this->createUser('employee');
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $userWithoutTeam, [UserVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCanEditUserWithoutTeam(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $userWithoutTeam = $this->createUser('employee');
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $userWithoutTeam, [UserVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCannotDeleteUserWithoutTeam(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $userWithoutTeam = $this->createUser('employee');
        $token = $this->createToken($manager);
        $result = $this->voter->vote($token, $userWithoutTeam, [UserVoter::DELETE]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCanViewClocksOfUserWithoutTeam(): void
    {
        $team = $this->createTeam();
        $manager = $this->createUser('manager', $team, isManager: true);
        $userWithoutTeam = $this->createUser('employee');
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $userWithoutTeam, [UserVoter::VIEW_CLOCKS]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerWithoutTeamCannotViewOtherUsers(): void
    {
        $manager = $this->createUser('manager');
        $otherUser = $this->createUser('employee');
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $otherUser, [UserVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerWithoutTeamCannotEditOtherUsers(): void
    {
        $manager = $this->createUser('manager');
        $otherUser = $this->createUser('employee');
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $otherUser, [UserVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerWithoutTeamCannotViewOtherUserClocks(): void
    {
        $manager = $this->createUser('manager');
        $otherUser = $this->createUser('employee');
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $otherUser, [UserVoter::VIEW_CLOCKS]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerWithoutTeamCanViewSelf(): void
    {
        $manager = $this->createUser('manager');
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $manager, [UserVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerWithoutTeamCanEditSelf(): void
    {
        $manager = $this->createUser('manager');
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $manager, [UserVoter::EDIT]);

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
                $managedTeams->method('isEmpty')->willReturn(false);
                $user->method('getManagedTeams')->willReturn($managedTeams);
            }
        } else {
            if ($role === 'manager') {
                $managedTeams = $this->createMock(\Doctrine\Common\Collections\Collection::class);
                $managedTeams->method('isEmpty')->willReturn(true);
                $managedTeams->method('contains')->willReturn(false);
                $user->method('getManagedTeams')->willReturn($managedTeams);
            }
        }

        return $user;
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
