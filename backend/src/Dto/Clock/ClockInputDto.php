<?php

namespace App\Dto\Clock;

class ClockInputDto
{
    public ?\DateTimeImmutable $time = null;

    public ?bool $status = null;
}
