<?php

namespace App\Mapper;

use App\Dto\ClockRequest\ClockRequestOutputDto;
use App\Entity\ClockRequest;

class ClockRequestMapper
{
    public function __construct(
        private readonly UserMapper $userMapper,
        private readonly ClockMapper $clockMapper,
    ) {
    }

    public function toOutputDto(ClockRequest $clockRequest): ClockRequestOutputDto
    {
        return new ClockRequestOutputDto(
            id: $clockRequest->getId(),
            user: $this->userMapper->toOutputDto($clockRequest->getUser()),
            type: $clockRequest->getType(),
            requestedTime: $clockRequest->getRequestedTime(),
            requestedStatus: $clockRequest->getRequestedStatus(),
            targetClock: $clockRequest->getTargetClock()
                ? $this->clockMapper->toOutputDto($clockRequest->getTargetClock())
                : null,
            status: $clockRequest->getStatus(),
            reason: $clockRequest->getReason(),
            reviewedBy: $clockRequest->getReviewedBy()
                ? $this->userMapper->toOutputDto($clockRequest->getReviewedBy())
                : null,
            reviewedAt: $clockRequest->getReviewedAt(),
            createdAt: $clockRequest->getCreatedAt(),
            updatedAt: $clockRequest->getUpdatedAt(),
        );
    }

    /**
     * @param ClockRequest[] $clockRequests
     *
     * @return ClockRequestOutputDto[]
     */
    public function toOutputDtoCollection(array $clockRequests): array
    {
        return array_map(
            fn (ClockRequest $clockRequest) => $this->toOutputDto($clockRequest),
            $clockRequests
        );
    }
}
