<?php

namespace App\Dto\ClockRequest;

use Symfony\Component\Validator\Constraints as Assert;

class ApproveClockRequestDto
{
    public ?\DateTimeImmutable $approvedTime = null;

    public ?bool $approvedStatus = null;
}
