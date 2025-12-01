<?php

namespace App\Mapper;

use App\Dto\Clock\ClockInputDto;
use App\Dto\Clock\ClockOutputDto;
use App\Entity\Clock;
use App\Entity\User;

class ClockMapper
{
    public function __construct(
        private readonly UserMapper $userMapper,
    ) {
    }

    public function toOutputDto(Clock $clock): ClockOutputDto
    {
        return new ClockOutputDto(
            id: $clock->getId(),
            time: $clock->getTime(),
            status: $clock->isStatus(),
            owner: $this->userMapper->toOutputDto($clock->getOwner()),
            createdAt: $clock->getCreatedAt(),
        );
    }

    /**
     * @param Clock[] $clocks
     *
     * @return ClockOutputDto[]
     */
    public function toOutputDtoCollection(array $clocks): array
    {
        return array_map(fn (Clock $clock) => $this->toOutputDto($clock), $clocks);
    }

    public function toEntity(ClockInputDto $dto, User $user): Clock
    {
        $clock = new Clock();
        $clock->setTime($dto->time);
        $clock->setStatus($dto->status);
        $clock->setOwner($user);

        return $clock;
    }
}
