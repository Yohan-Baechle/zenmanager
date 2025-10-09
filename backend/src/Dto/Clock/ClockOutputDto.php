<?php

namespace App\Dto\Clock;

use App\Dto\User\UserOutputDto;

class ClockOutputDto
{
    public function __construct(
        public readonly int $id,
        public readonly \DateTimeImmutable $time,
        public readonly bool $status,
        public readonly UserOutputDto $owner,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}
