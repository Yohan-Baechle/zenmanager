<?php

namespace App\Security\Voter;

use App\Entity\Clock;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClockVoter extends Voter
{
    public const VIEW = 'CLOCK_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Clock;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Clock $clock */
        $clock = $subject;

        return $this->canView($clock, $user);
    }

    private function canView(Clock $clock, User $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($clock->getOwner() === $user) {
            return true;
        }

        if (in_array('ROLE_MANAGER', $user->getRoles())) {
            $clockOwner = $clock->getOwner();
            $clockOwnerTeam = $clockOwner?->getTeam();

            if ($clockOwnerTeam !== null) {
                return $user->getManagedTeams()->contains($clockOwnerTeam);
            }
        }

        return false;
    }
}