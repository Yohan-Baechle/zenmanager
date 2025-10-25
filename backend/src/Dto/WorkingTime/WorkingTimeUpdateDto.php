<?php

namespace App\Dto\WorkingTime;

use Symfony\Component\Validator\Constraints as Assert;

class WorkingTimeUpdateDto
{
    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeInterface $startTime = null;

    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeInterface $endTime = null;

    #[Assert\IsTrue(message: 'La date de fin doit être postérieure à la date de début')]
    public function isEndTimeAfterStartTime(): bool
    {
        if ($this->startTime && $this->endTime) {
            return $this->endTime > $this->startTime;
        }

        return true;
    }
}
