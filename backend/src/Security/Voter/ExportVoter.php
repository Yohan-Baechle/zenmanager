<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExportVoter extends Voter
{
    public const EXPORT_CLOCKING = 'EXPORT_CLOCKING';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EXPORT_CLOCKING;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::EXPORT_CLOCKING => $this->canExportClocking($subject, $currentUser),
            default => false,
        };
    }

    /**
     * Check if user can export clocking data
     *
     * Rules:
     * - ROLE_ADMIN: Can export all data (any team, any user)
     * - ROLE_MANAGER: Can only export data for teams they manage
     * - ROLE_USER: Cannot export (exports are manager+ only)
     *
     * @param mixed $subject Can be null, User entity, or integer (teamId)
     * @param User $currentUser The authenticated user
     * @return bool
     */
    private function canExportClocking(mixed $subject, User $currentUser): bool
    {
        $roles = $currentUser->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            return true;
        }

        if (!in_array('ROLE_MANAGER', $roles)) {
            return false;
        }

        $managedTeams = $currentUser->getManagedTeams();
        if ($managedTeams->isEmpty()) {
            return false;
        }

        if ($subject === null) {
            return true;
        }

        if (is_int($subject)) {
            $teamId = $subject;
            $managedTeamIds = array_map(fn($team) => $team->getId(), $managedTeams->toArray());
            return in_array($teamId, $managedTeamIds);
        }

        if ($subject instanceof User) {
            $targetTeam = $subject->getTeam();

            if (null === $targetTeam) {
                return false;
            }

            return $managedTeams->contains($targetTeam);
        }

        return false;
    }
}
