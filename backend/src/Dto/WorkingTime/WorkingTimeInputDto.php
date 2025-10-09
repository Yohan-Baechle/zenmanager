<?php

namespace App\Dto\WorkingTime;

use Symfony\Component\Validator\Constraints as Assert;

class WorkingTimeInputDto
{
    #[Assert\NotBlank]
    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeInterface $startTime = null;

    #[Assert\NotBlank]
    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeInterface $endTime = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $userId = null;

    #[Assert\IsTrue(message: 'La date de fin doit être postérieure à la date de début')]
    public function isEndTimeAfterStartTime(): bool
    {
        if ($this->startTime && $this->endTime) {
            return $this->endTime > $this->startTime;
        }
        return true;
    }
}
