<?php

namespace App\Security\Voter;

use App\Entity\ClockRequest;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClockRequestVoter extends Voter
{
    public const VIEW = 'CLOCK_REQUEST_VIEW';
    public const REVIEW = 'CLOCK_REQUEST_REVIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::REVIEW]) && $subject instanceof ClockRequest;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var ClockRequest $clockRequest */
        $clockRequest = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($clockRequest, $user),
            self::REVIEW => $this->canReview($clockRequest, $user),
            default => false,
        };
    }

    private function canView(ClockRequest $clockRequest, User $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($clockRequest->getUser() === $user) {
            return true;
        }

        if (in_array('ROLE_MANAGER', $user->getRoles())) {
            $requestUser = $clockRequest->getUser();
            $requestUserTeam = $requestUser?->getTeam();

            if ($requestUserTeam !== null) {
                return $user->getManagedTeams()->contains($requestUserTeam);
            }
        }

        return false;
    }

    private function canReview(ClockRequest $clockRequest, User $user): bool
    {
        if ($clockRequest->getStatus() !== 'PENDING') {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if (in_array('ROLE_MANAGER', $user->getRoles())) {
            $requestUser = $clockRequest->getUser();
            $requestUserTeam = $requestUser?->getTeam();

            if ($requestUserTeam !== null) {
                return $user->getManagedTeams()->contains($requestUserTeam);
            }
        }

        return false;
    }
}
