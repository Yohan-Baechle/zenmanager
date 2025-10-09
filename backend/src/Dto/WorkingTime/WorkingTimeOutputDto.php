<?php

namespace App\Dto\WorkingTime;

use App\Dto\User\UserOutputDto;

class WorkingTimeOutputDto
{
    public function __construct(
        public readonly int $id,
        public readonly \DateTimeImmutable $startTime,
        public readonly \DateTimeImmutable $endTime,
        public readonly UserOutputDto $user,
        public readonly int $durationMinutes,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
