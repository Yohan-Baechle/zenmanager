<?php

namespace App\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TeamVoter extends Voter
{
    public const VIEW = 'TEAM_VIEW';
    public const EDIT = 'TEAM_EDIT';
    public const DELETE = 'TEAM_DELETE';
    public const MANAGE = 'TEAM_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::MANAGE])
            && $subject instanceof Team;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Team $team */
        $team = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($team, $user),
            self::EDIT => $this->canEdit($team, $user),
            self::DELETE => $this->canDelete($team, $user),
            self::MANAGE => $this->canManage($team, $user),
            default => false,
        };
    }

    private function canView(Team $team, User $user): bool
    {
        return true;
    }

    private function canEdit(Team $team, User $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return $team->getManager() === $user;
    }

    private function canDelete(Team $team, User $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return $team->getManager() === $user;
    }

    private function canManage(Team $team, User $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return $team->getManager() === $user;
    }
}
