<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserUpdateDto
{
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\Length(min: 8)]
    public ?string $password = null;

    #[Assert\Length(min: 2, max: 100)]
    public ?string $firstName = null;

    #[Assert\Length(min: 2, max: 100)]
    public ?string $lastName = null;

    #[Assert\Regex(
        pattern: '/^\+?[1-9]\d{1,14}$/',
        message: 'Invalid phone number format'
    )]
    public ?string $phoneNumber = null;

    #[Assert\Choice(choices: ['employee', 'manager'], message: 'Role must be either employee or manager')]
    public ?string $role = null;

    public ?int $teamId = null;
}
