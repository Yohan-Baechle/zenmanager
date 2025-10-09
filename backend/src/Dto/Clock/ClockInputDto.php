<?php

namespace App\Dto\Clock;

use Symfony\Component\Validator\Constraints as Assert;

class ClockInputDto
{
    #[Assert\NotNull(message: 'Time is required')]
    public ?\DateTimeImmutable $time = null;

    #[Assert\NotNull(message: 'Status is required')]
    public ?bool $status = null;

    #[Assert\NotBlank(message: 'User ID is required')]
    #[Assert\Positive]
    public ?int $userId = null;
}
