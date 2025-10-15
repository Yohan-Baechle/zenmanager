<?php

namespace App\Dto\ClockRequest;

use Symfony\Component\Validator\Constraints as Assert;

class CreateClockRequestDto
{
    #[Assert\NotBlank(message: 'Type is required')]
    #[Assert\Choice(choices: ['CREATE', 'UPDATE', 'DELETE'], message: 'Type must be CREATE, UPDATE or DELETE')]
    public ?string $type = null;

    #[Assert\NotNull(message: 'Requested time is required')]
    public ?\DateTimeImmutable $requestedTime = null;

    public ?bool $requestedStatus = null;

    #[Assert\Positive]
    public ?int $targetClockId = null;

    #[Assert\NotBlank(message: 'Reason is required')]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: 'Reason must be at least 10 characters',
        maxMessage: 'Reason cannot be longer than 1000 characters'
    )]
    public ?string $reason = null;
}
