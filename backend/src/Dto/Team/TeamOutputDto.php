<?php

namespace App\Dto\Team;

class TeamOutputDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}
}
