<?php

namespace App\Entity;

use App\Repository\WorkingTimeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkingTimeRepository::class)]
#[ORM\Table(name: 'working_times')]
#[ORM\Index(columns: ['user_id'], name: 'idx_working_time_user')]
#[ORM\Index(columns: ['start_time', 'end_time'], name: 'idx_working_time_period')]
#[ORM\Index(columns: ['user_id', 'start_time', 'end_time'], name: 'idx_working_time_user_period')]
#[ORM\HasLifecycleCallbacks]
class WorkingTime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'workingTimes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // Lifecycle callbacks

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
