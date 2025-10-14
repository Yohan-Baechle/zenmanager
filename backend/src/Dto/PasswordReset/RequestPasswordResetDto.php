<?php

namespace App\Dto\PasswordReset;

use Symfony\Component\Validator\Constraints as Assert;

class RequestPasswordResetDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Invalid email format')]
        public readonly string $email
    ) {}
}
