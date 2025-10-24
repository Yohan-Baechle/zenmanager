<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Team
{
    public const MIN_NAME_LENGTH = 2;
    public const MAX_NAME_LENGTH = 100;

    public const MIN_DESCRIPTION_LENGTH = 0;
    public const MAX_DESCRIPTION_LENGTH = 1000;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: Team::MAX_NAME_LENGTH)]
    #[Assert\NotBlank]
    #[Assert\Length(min: Team::MIN_NAME_LENGTH, max: Team::MAX_NAME_LENGTH)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(min: Team::MIN_DESCRIPTION_LENGTH, max: Team::MAX_DESCRIPTION_LENGTH)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'managedTeams')]
    private ?User $manager = null;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'team')]
    private Collection $employees;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
        $this->setUpdatedAtValue();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $length = strlen($name);

        if ($length < Team::MIN_NAME_LENGTH || $length > Team::MAX_NAME_LENGTH) {
            throw new \InvalidArgumentException('Name must be between '.Team::MIN_NAME_LENGTH.' and '.Team::MAX_NAME_LENGTH." characters. Got {$length}.");
        }

        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $length = strlen($description);

        if ($length < Team::MIN_DESCRIPTION_LENGTH || $length > Team::MAX_DESCRIPTION_LENGTH) {
            throw new \InvalidArgumentException('Description must be between '.Team::MIN_DESCRIPTION_LENGTH.' and '.Team::MAX_DESCRIPTION_LENGTH." characters. Got {$length}.");
        }

        $this->description = $description;

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(User $employee): static
    {
        if ($this->getManager() === $employee) {
            throw new \LogicException('A manager cannot be an employee of their own team.');
        }

        if (null !== $employee->getTeam() && $employee->getTeam() !== $this) {
            throw new \InvalidArgumentException('This employee already belongs to another team.');
        }

        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
            $employee->setTeam($this);
        }

        return $this;
    }

    public function removeEmployee(User $employee): static
    {
        if ($this->employees->removeElement($employee)) {
            if ($employee->getTeam() === $this) {
                $employee->setTeam(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
