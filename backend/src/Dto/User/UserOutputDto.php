<?php

namespace App\Dto\User;

use App\Dto\Team\TeamOutputDto;

class UserOutputDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $phoneNumber,
        public readonly string $role,
        public readonly ?TeamOutputDto $team,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {
    }
}
