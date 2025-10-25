<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\WorkingTime;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WorkingTimeVoter extends Voter
{
    public const VIEW = 'WORKING_TIME_VIEW';
    public const EDIT = 'WORKING_TIME_EDIT';
    public const DELETE = 'WORKING_TIME_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof WorkingTime;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var WorkingTime $workingTime */
        $workingTime = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($workingTime, $user),
            self::EDIT => $this->canEdit($workingTime, $user),
            self::DELETE => $this->canDelete($workingTime, $user),
            default => false,
        };
    }

    private function canView(WorkingTime $workingTime, User $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($workingTime->getOwner() === $user) {
            return true;
        }

        if (in_array('ROLE_MANAGER', $user->getRoles())) {
            $owner = $workingTime->getOwner();
            $ownerTeam = $owner?->getTeam();

            if (null !== $ownerTeam) {
                return $user->getManagedTeams()->contains($ownerTeam);
            }
        }

        return false;
    }

    private function canEdit(WorkingTime $workingTime, User $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($workingTime->getOwner() === $user) {
            return true;
        }

        if (in_array('ROLE_MANAGER', $user->getRoles())) {
            $owner = $workingTime->getOwner();
            $ownerTeam = $owner?->getTeam();

            if (null !== $ownerTeam) {
                return $user->getManagedTeams()->contains($ownerTeam);
            }
        }

        return false;
    }

    private function canDelete(WorkingTime $workingTime, User $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($workingTime->getOwner() === $user) {
            return true;
        }

        if (in_array('ROLE_MANAGER', $user->getRoles())) {
            $owner = $workingTime->getOwner();
            $ownerTeam = $owner?->getTeam();

            if (null !== $ownerTeam) {
                return $user->getManagedTeams()->contains($ownerTeam);
            }
        }

        return false;
    }
}
