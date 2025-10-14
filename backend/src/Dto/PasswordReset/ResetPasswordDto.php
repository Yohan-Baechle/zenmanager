<?php

namespace App\Dto\PasswordReset;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Token is required')]
        public readonly string $token,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(
            min: 12,
            max: 255,
            minMessage: 'Password must be at least {{ limit }} characters long',
            maxMessage: 'Password cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&\-_+=.,:;#^~])/',
            message: 'Password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character (@$!%*?&-_+=.,:;#^~)'
        )]
        public readonly string $newPassword
    ) {}
}
