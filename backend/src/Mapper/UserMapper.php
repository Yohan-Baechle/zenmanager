<?php

namespace App\Mapper;

use App\Dto\Team\TeamOutputDto;
use App\Dto\User\UserOutputDto;
use App\Dto\User\UserUpdateDto;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserMapper
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function toOutputDto(User $user): UserOutputDto
    {
        return new UserOutputDto(
            id: $user->getId(),
            username: $user->getUsername(),
            email: $user->getEmail(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            phoneNumber: $user->getPhoneNumber(),
            role: $user->getRoleForDisplay(),
            team: $user->getTeam() ? $this->teamToOutputDto($user->getTeam()) : null,
            createdAt: $user->getCreatedAt(),
            updatedAt: $user->getUpdatedAt(),
        );
    }

    /**
     * @param User[] $users
     *
     * @return UserOutputDto[]
     */
    public function toOutputDtoCollection(array $users): array
    {
        return array_map(fn (User $user) => $this->toOutputDto($user), $users);
    }

    public function updateEntity(User $user, UserUpdateDto $dto, ?Team $team = null): void
    {
        if (null !== $dto->username) {
            $user->setUsername($dto->username);
        }

        if (null !== $dto->email) {
            $user->setEmail($dto->email);
        }

        if (null !== $dto->firstName) {
            $user->setFirstName($dto->firstName);
        }

        if (null !== $dto->lastName) {
            $user->setLastName($dto->lastName);
        }

        if (null !== $dto->phoneNumber) {
            $user->setPhoneNumber($dto->phoneNumber);
        }

        if (null !== $dto->role) {
            if ('manager' === $dto->role) {
                $user->setRoles(['ROLE_MANAGER']);
            } elseif ('employee' === $dto->role) {
                $user->setRoles(['ROLE_EMPLOYEE']);
            }
        }

        if (null !== $dto->password) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
            $user->setPassword($hashedPassword);
        }

        $user->setTeam($team);
    }

    private function teamToOutputDto(Team $team): TeamOutputDto
    {
        return new TeamOutputDto(
            id: $team->getId(),
            name: $team->getName(),
            description: $team->getDescription(),
            manager: null,
            employees: [],
            createdAt: $team->getCreatedAt(),
            updatedAt: $team->getUpdatedAt(),
        );
    }
}
