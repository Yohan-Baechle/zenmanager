<?php

namespace App\Dto\Clock;

use Symfony\Component\Validator\Constraints as Assert;

class ClockInputDto
{
    public ?\DateTimeImmutable $time = null;

    public ?bool $status = null;
}
