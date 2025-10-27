<?php

namespace App\Dto\Export;

use Symfony\Component\Validator\Constraints as Assert;

class ExportFilterInputDto
{
    #[Assert\Date(message: 'start_date must be a valid date (format: YYYY-MM-DD)')]
    public ?string $start_date = null;

    #[Assert\Date(message: 'end_date must be a valid date (format: YYYY-MM-DD)')]
    public ?string $end_date = null;

    #[Assert\Positive(message: 'team_id must be a positive integer')]
    public ?int $team_id = null;

    #[Assert\Positive(message: 'user_id must be a positive integer')]
    public ?int $user_id = null;

    #[Assert\Choice(
        choices: ['pdf', 'xlsx'],
        message: 'Format must be either "pdf" or "xlsx"'
    )]
    public ?string $format = null;

    /**
     * Get start date as DateTimeInterface
     */
    public function getStartDateAsDateTime(): ?\DateTimeInterface
    {
        if ($this->start_date) {
            try {
                return new \DateTimeImmutable($this->start_date);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get end date as DateTimeInterface
     */
    public function getEndDateAsDateTime(): ?\DateTimeInterface
    {
        if ($this->end_date) {
            try {
                return new \DateTimeImmutable($this->end_date);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Validate that end date is after start date
     */
    #[Assert\IsTrue(message: 'end_date must be after start_date')]
    public function isEndDateValid(): bool
    {
        $start = $this->getStartDateAsDateTime();
        $end = $this->getEndDateAsDateTime();

        if ($start && $end) {
            return $end >= $start;
        }

        return true;
    }

    /**
     * Validate that date range is not too large (max 1 year)
     */
    #[Assert\IsTrue(message: 'Date range cannot exceed 1 year')]
    public function isDateRangeValid(): bool
    {
        $start = $this->getStartDateAsDateTime();
        $end = $this->getEndDateAsDateTime();

        if ($start && $end) {
            $interval = $start->diff($end);
            $days = $interval->days;

            // Max 366 days (1 year including leap year)
            return $days <= 366;
        }

        return true;
    }

    /**
     * Validate that dates are not in the future
     */
    #[Assert\IsTrue(message: 'Dates cannot be in the future')]
    public function isDatesNotInFuture(): bool
    {
        $now = new \DateTimeImmutable();

        $start = $this->getStartDateAsDateTime();
        if ($start && $start > $now) {
            return false;
        }

        $end = $this->getEndDateAsDateTime();
        if ($end && $end > $now) {
            return false;
        }

        return true;
    }
}
