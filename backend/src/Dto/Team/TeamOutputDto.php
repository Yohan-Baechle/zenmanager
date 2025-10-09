<?php

namespace App\Dto\Team;

use App\Dto\User\UserOutputDto;

class TeamOutputDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?UserOutputDto $manager,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
