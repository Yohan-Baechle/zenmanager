<?php

namespace App\Tests\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use App\Security\Voter\TeamVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TeamVoterTest extends TestCase
{
    private TeamVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new TeamVoter();
    }

    public function testEveryoneCanViewTeam(): void
    {
        $user = $this->createUser('employee');
        $team = $this->createTeam();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $team, [TeamVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testAdminCanEditTeam(): void
    {
        $admin = $this->createUser('admin');
        $team = $this->createTeam();
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $team, [TeamVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testTeamManagerCanEditTeam(): void
    {
        $manager = $this->createUser('manager');
        $team = $this->createTeam($manager);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $team, [TeamVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testNonManagerCannotEditTeam(): void
    {
        $user = $this->createUser('employee');
        $team = $this->createTeam();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $team, [TeamVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCannotEditOtherTeam(): void
    {
        $manager = $this->createUser('manager');
        $otherManager = $this->createUser('manager');
        $team = $this->createTeam($otherManager);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $team, [TeamVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testAdminCanDeleteTeam(): void
    {
        $admin = $this->createUser('admin');
        $team = $this->createTeam();
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $team, [TeamVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testTeamManagerCanDeleteTeam(): void
    {
        $manager = $this->createUser('manager');
        $team = $this->createTeam($manager);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $team, [TeamVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testNonManagerCannotDeleteTeam(): void
    {
        $user = $this->createUser('employee');
        $team = $this->createTeam();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $team, [TeamVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testAdminCanManageTeam(): void
    {
        $admin = $this->createUser('admin');
        $team = $this->createTeam();
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $team, [TeamVoter::MANAGE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testTeamManagerCanManageTeam(): void
    {
        $manager = $this->createUser('manager');
        $team = $this->createTeam($manager);
        $token = $this->createToken($manager);

        $result = $this->voter->vote($token, $team, [TeamVoter::MANAGE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testNonManagerCannotManageTeam(): void
    {
        $user = $this->createUser('employee');
        $team = $this->createTeam();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $team, [TeamVoter::MANAGE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    private function createUser(string $role): User
    {
        $user = $this->createMock(User::class);

        $roles = match ($role) {
            'admin' => ['ROLE_ADMIN'],
            'manager' => ['ROLE_MANAGER'],
            default => ['ROLE_USER'],
        };

        $user->method('getRoles')->willReturn($roles);

        return $user;
    }

    private function createTeam(?User $manager = null): Team
    {
        $team = $this->createMock(Team::class);

        if (null !== $manager) {
            $team->method('getManager')->willReturn($manager);
        }

        return $team;
    }

    private function createToken(User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }
}
