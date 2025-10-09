<?php

namespace App\Mapper;

use App\Dto\WorkingTime\WorkingTimeInputDto;
use App\Dto\WorkingTime\WorkingTimeOutputDto;
use App\Dto\WorkingTime\WorkingTimeUpdateDto;
use App\Entity\WorkingTime;
use App\Entity\User;

class WorkingTimeMapper
{
    public function __construct(
        private readonly UserMapper $userMapper
    ) {}

    public function toOutputDto(WorkingTime $workingTime): WorkingTimeOutputDto
    {
        // Calcul de la durée en minutes
        $interval = $workingTime->getStartTime()->diff($workingTime->getEndTime());
        $durationMinutes = ($interval->h * 60) + $interval->i;

        return new WorkingTimeOutputDto(
            id: $workingTime->getId(),
            startTime: $workingTime->getStartTime(),
            endTime: $workingTime->getEndTime(),
            user: $this->userMapper->toOutputDto($workingTime->getUser()),
            durationMinutes: $durationMinutes,
            createdAt: $workingTime->getCreatedAt(),
            updatedAt: $workingTime->getUpdatedAt(),
        );
    }

    /**
     * @param WorkingTime[] $workingTimes
     * @return WorkingTimeOutputDto[]
     */
    public function toOutputDtoCollection(array $workingTimes): array
    {
        return array_map(fn(WorkingTime $wt) => $this->toOutputDto($wt), $workingTimes);
    }

    public function toEntity(WorkingTimeInputDto $dto, User $user): WorkingTime
    {
        $workingTime = new WorkingTime();
        $workingTime->setStartTime(\DateTimeImmutable::createFromInterface($dto->startTime));
        $workingTime->setEndTime(\DateTimeImmutable::createFromInterface($dto->endTime));
        $workingTime->setUser($user);

        return $workingTime;
    }

    public function updateEntity(WorkingTime $workingTime, WorkingTimeUpdateDto $dto): void
    {
        if ($dto->startTime !== null) {
            $workingTime->setStartTime(\DateTimeImmutable::createFromInterface($dto->startTime));
        }

        if ($dto->endTime !== null) {
            $workingTime->setEndTime(\DateTimeImmutable::createFromInterface($dto->endTime));
        }
    }
}
