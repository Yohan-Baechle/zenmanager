<?php

namespace App\Dto\Team;

use Symfony\Component\Validator\Constraints as Assert;

class TeamInputDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public ?string $name = null;

    public ?string $description = null;

    #[Assert\Positive]
    public ?int $managerId = null;
}
