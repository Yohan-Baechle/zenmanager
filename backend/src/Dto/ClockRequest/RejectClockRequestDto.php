<?php

namespace App\Dto\ClockRequest;

use Symfony\Component\Validator\Constraints as Assert;

class RejectClockRequestDto
{
    #[Assert\NotBlank(message: 'Rejection reason is required')]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: 'Rejection reason must be at least 10 characters',
        maxMessage: 'Rejection reason cannot be longer than 1000 characters'
    )]
    public ?string $rejectionReason = null;
}
