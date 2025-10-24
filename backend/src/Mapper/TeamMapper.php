<?php

namespace App\Mapper;

use App\Dto\Team\TeamInputDto;
use App\Dto\Team\TeamOutputDto;
use App\Dto\Team\TeamUpdateDto;
use App\Entity\Team;
use App\Entity\User;

class TeamMapper
{
    public function __construct(
        private readonly UserMapper $userMapper,
    ) {
    }

    public function toOutputDto(Team $team): TeamOutputDto
    {
        $employees = [];
        foreach ($team->getEmployees() as $employee) {
            $employees[] = $this->userMapper->toOutputDto($employee);
        }

        return new TeamOutputDto(
            id: $team->getId(),
            name: $team->getName(),
            description: $team->getDescription(),
            manager: $team->getManager()
                ? $this->userMapper->toOutputDto($team->getManager())
                : null,
            employees: $employees,
            createdAt: $team->getCreatedAt(),
            updatedAt: $team->getUpdatedAt(),
        );
    }

    /**
     * @param Team[] $teams
     *
     * @return TeamOutputDto[]
     */
    public function toOutputDtoCollection(array $teams): array
    {
        return array_map(fn (Team $team) => $this->toOutputDto($team), $teams);
    }

    public function toEntity(TeamInputDto $dto, ?User $manager = null): Team
    {
        $team = new Team();
        $team->setName($dto->name);
        $team->setDescription($dto->description);
        $team->setManager($manager);

        return $team;
    }

    public function updateEntity(Team $team, TeamUpdateDto $dto, ?User $manager = null): void
    {
        if (null !== $dto->name) {
            $team->setName($dto->name);
        }

        if (null !== $dto->description) {
            $team->setDescription($dto->description);
        }

        if (isset($dto->managerId) || null !== $manager) {
            $team->setManager($manager);
        }
    }
}
