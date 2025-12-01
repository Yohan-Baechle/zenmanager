<?php

namespace App\Dto\ClockRequest;

class ApproveClockRequestDto
{
    public ?\DateTimeImmutable $approvedTime = null;

    public ?bool $approvedStatus = null;
}
