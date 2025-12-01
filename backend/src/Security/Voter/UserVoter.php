<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const VIEW = 'USER_VIEW';
    public const EDIT = 'USER_EDIT';
    public const DELETE = 'USER_DELETE';
    public const VIEW_CLOCKS = 'USER_VIEW_CLOCKS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::VIEW_CLOCKS])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            return false;
        }

        /** @var User $targetUser */
        $targetUser = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($targetUser, $currentUser),
            self::EDIT => $this->canEdit($targetUser, $currentUser),
            self::DELETE => $this->canDelete($targetUser, $currentUser),
            self::VIEW_CLOCKS => $this->canViewClocks($targetUser, $currentUser),
            default => false,
        };
    }

    private function canView(User $targetUser, User $currentUser): bool
    {
        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return true;
        }

        if ($targetUser === $currentUser) {
            return true;
        }

        if (in_array('ROLE_MANAGER', $currentUser->getRoles())) {
            if ($currentUser->getManagedTeams()->isEmpty()) {
                return false;
            }

            $targetTeam = $targetUser->getTeam();
            if (null === $targetTeam) {
                return true;
            }
            return $currentUser->getManagedTeams()->contains($targetTeam);
        }

        return true;
    }

    private function canEdit(User $targetUser, User $currentUser): bool
    {
        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return true;
        }

        if ($targetUser === $currentUser) {
            return true;
        }

        if (in_array('ROLE_MANAGER', $currentUser->getRoles())) {
            if ($currentUser->getManagedTeams()->isEmpty()) {
                return false;
            }

            $targetTeam = $targetUser->getTeam();
            if (null === $targetTeam) {
                return true;
            }
            return $currentUser->getManagedTeams()->contains($targetTeam);
        }

        return false;
    }

    private function canDelete(User $targetUser, User $currentUser): bool
    {
        if (in_array('ROLE_ADMIN', $currentUser->getRoles()) && $targetUser !== $currentUser) {
            return true;
        }

        return false;
    }

    private function canViewClocks(User $targetUser, User $currentUser): bool
    {
        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return true;
        }

        if ($targetUser === $currentUser) {
            return true;
        }

        if (in_array('ROLE_MANAGER', $currentUser->getRoles())) {
            if ($currentUser->getManagedTeams()->isEmpty()) {
                return false;
            }

            $targetTeam = $targetUser->getTeam();
            if (null === $targetTeam) {
                return true;
            }
            return $currentUser->getManagedTeams()->contains($targetTeam);
        }

        return false;
    }
}
