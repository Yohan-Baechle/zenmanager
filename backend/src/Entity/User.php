<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_USERNAME', fields: ['username'])]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'This email is already used')]
#[UniqueEntity(fields: ['username'], message: 'This username is already used')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const USERNAME_MIN_LENGTH = 3;
    public const USERNAME_MAX_LENGTH = 50;

    public const FIRST_NAME_MIN_LENGTH = 2;
    public const FIRST_NAME_MAX_LENGTH = 100;

    public const LAST_NAME_MIN_LENGTH = 2;
    public const LAST_NAME_MAX_LENGTH = 100;

    public const EMAIL_MAX_LENGTH = 180;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: User::USERNAME_MAX_LENGTH, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: User::USERNAME_MIN_LENGTH, max: User::USERNAME_MAX_LENGTH)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_-]+$/',
        message: 'Username can only contain letters, numbers, underscores and hyphens'
    )]
    private ?string $username = null;

    #[ORM\Column(length: User::EMAIL_MAX_LENGTH, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: User::FIRST_NAME_MAX_LENGTH)]
    #[Assert\NotBlank]
    #[Assert\Length(min: User::FIRST_NAME_MIN_LENGTH, max: User::FIRST_NAME_MAX_LENGTH)]
    private ?string $firstName = null;

    #[ORM\Column(length: User::LAST_NAME_MAX_LENGTH)]
    #[Assert\NotBlank]
    #[Assert\Length(min: User::LAST_NAME_MIN_LENGTH, max: User::LAST_NAME_MAX_LENGTH)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^\+?[1-9]\d{1,14}$/',
        message: 'Invalid phone number format'
    )]
    private ?string $phoneNumber = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['user:read', 'user:write'])]
    private ?Team $team = null;

    #[ORM\OneToMany(targetEntity: Team::class, mappedBy: 'manager')]
    private Collection $managedTeams;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->managedTeams = new ArrayCollection();
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $length = strlen($username);

        if ($length < User::USERNAME_MIN_LENGTH || $length > User::USERNAME_MAX_LENGTH) {
            throw new \InvalidArgumentException('Username must be between '.User::USERNAME_MIN_LENGTH.' and '.User::USERNAME_MAX_LENGTH." characters. Got {$length}.");
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            throw new \InvalidArgumentException('Username can only contain letters, numbers, underscores and hyphens.');
        }

        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $length = mb_strlen($email); // jfyi : strlen doesn't work properly with multibyte chars that's why we use mb_strlen here

        if ($length > self::EMAIL_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('Email must not exceed %d characters. Got %d.', self::EMAIL_MAX_LENGTH, $length));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(sprintf('Invalid email format: "%s".', $email));
        }

        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        if (count($roles) > 1) {
            throw new \InvalidArgumentException('A user can only have one role.');
        }

        // Symfony expects an array of roles, so we keep it as an array
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $length = strlen($firstName);

        if ($length < User::FIRST_NAME_MIN_LENGTH || $length > User::FIRST_NAME_MAX_LENGTH) {
            throw new \InvalidArgumentException('First name must be between '.User::FIRST_NAME_MIN_LENGTH.' and '.User::FIRST_NAME_MAX_LENGTH." characters. Got {$length}.");
        }

        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $length = strlen($lastName);

        if ($length < User::LAST_NAME_MIN_LENGTH || $length > User::LAST_NAME_MAX_LENGTH) {
            throw new \InvalidArgumentException('Last name must be between '.User::LAST_NAME_MIN_LENGTH.' and '.User::LAST_NAME_MAX_LENGTH." characters. Got {$length}.");
        }

        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getRoleForDisplay(): string
    {
        $roles = $this->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            return 'admin';
        }

        if (in_array('ROLE_MANAGER', $roles)) {
            return 'manager';
        }

        return 'employee';
    }

    /**
     * Get the team this user is a member of (as an employee).
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get the teams this user manages.
     *
     * @return Collection<int, Team>
     */
    public function getManagedTeams(): Collection
    {
        return $this->managedTeams;
    }

    public function addManagedTeam(Team $team): static
    {
        if (!$this->managedTeams->contains($team)) {
            $this->managedTeams->add($team);
            $team->setManager($this);
        }

        return $this;
    }

    public function removeManagedTeam(Team $team): static
    {
        if ($this->managedTeams->removeElement($team)) {
            if ($team->getManager() === $this) {
                $team->setManager(null);
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
