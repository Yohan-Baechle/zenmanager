<?php

namespace App\Dto\ClockRequest;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateClockRequestDto
{
    public ?\DateTimeImmutable $requestedTime = null;

    public ?bool $requestedStatus = null;

    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: 'Reason must be at least 10 characters',
        maxMessage: 'Reason cannot be longer than 1000 characters'
    )]
    public ?string $reason = null;
}
