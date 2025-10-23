<?php

namespace App\Dto\ClockRequest;

use App\Dto\Clock\ClockOutputDto;
use App\Dto\User\UserOutputDto;

class ClockRequestOutputDto
{
    public function __construct(
        public readonly int $id,
        public readonly UserOutputDto $user,
        public readonly string $type,
        public readonly \DateTimeImmutable $requestedTime,
        public readonly ?bool $requestedStatus,
        public readonly ?ClockOutputDto $targetClock,
        public readonly string $status,
        public readonly string $reason,
        public readonly ?UserOutputDto $reviewedBy,
        public readonly ?\DateTimeImmutable $reviewedAt,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {
    }
}
