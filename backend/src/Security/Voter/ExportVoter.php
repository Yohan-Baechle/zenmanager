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
        // This voter supports EXPORT_CLOCKING attribute
        // Subject can be null (when checking general export permission)
        // or a User (when checking export permission for a specific user)
        // or an integer (teamId)
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

        // Admins can export everything
        if (in_array('ROLE_ADMIN', $roles)) {
            return true;
        }

        // Only managers and admins can export
        if (!in_array('ROLE_MANAGER', $roles)) {
            return false;
        }

        // Managers must manage at least one team
        $managedTeams = $currentUser->getManagedTeams();
        if ($managedTeams->isEmpty()) {
            return false;
        }

        // If no subject specified, manager has general export permission
        // (the controller will ensure they provide a valid team_id)
        if ($subject === null) {
            return true;
        }

        // If subject is a team ID (integer), check if manager manages that team
        if (is_int($subject)) {
            $teamId = $subject;
            $managedTeamIds = array_map(fn($team) => $team->getId(), $managedTeams->toArray());
            return in_array($teamId, $managedTeamIds);
        }

        // If subject is a User, check if user belongs to a team the manager manages
        if ($subject instanceof User) {
            $targetTeam = $subject->getTeam();

            // If user has no team, manager cannot export their data
            if (null === $targetTeam) {
                return false;
            }

            return $managedTeams->contains($targetTeam);
        }

        // Unknown subject type
        return false;
    }
}
