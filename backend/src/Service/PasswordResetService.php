<?php

namespace App\Service;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PasswordResetTokenRepository $tokenRepository,
        private readonly UserRepository $userRepository,
        private readonly MailerInterface $mailer,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly string $resetPasswordUrl = 'http://localhost:5173/reset-password'
    ) {}

    /**
     * Generate and send a password reset token
     *
     * @throws \Exception
     */
    public function requestPasswordReset(string $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        // Always return true even if user doesn't exist (security best practice)
        // This prevents email enumeration attacks
        if (!$user) {
            return true;
        }

        // Invalidate any existing valid tokens for this user
        $this->tokenRepository->invalidateUserTokens($user);

        // Generate a secure random token
        $plainToken = bin2hex(random_bytes(32));

        // Hash the token before storing (security best practice)
        $hashedToken = hash('sha256', $plainToken);

        // Create the token entity (expires in 1 hour)
        $passwordResetToken = new PasswordResetToken($user, $hashedToken, 60);

        $this->entityManager->persist($passwordResetToken);
        $this->entityManager->flush();

        // Send email with plain token
        $this->sendResetEmail($user, $plainToken);

        return true;
    }

    /**
     * Reset password using a valid token
     *
     * @throws \InvalidArgumentException
     */
    public function resetPassword(string $plainToken, string $newPassword): void
    {
        // Hash the plain token to find it in database
        $hashedToken = hash('sha256', $plainToken);

        $passwordResetToken = $this->tokenRepository->findByToken($hashedToken);

        if (!$passwordResetToken) {
            throw new \InvalidArgumentException('Invalid or expired token');
        }

        if (!$passwordResetToken->isValid()) {
            throw new \InvalidArgumentException('Invalid or expired token');
        }

        $user = $passwordResetToken->getUser();

        // Hash and update password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        // Mark token as used
        $passwordResetToken->markAsUsed();

        $this->entityManager->flush();
    }

    /**
     * Send the password reset email
     */
    private function sendResetEmail(User $user, string $plainToken): void
    {
        $resetUrl = sprintf('%s?token=%s', $this->resetPasswordUrl, $plainToken);

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@timemanager.local', 'Time Manager'))
            ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
            ->subject('Password Reset Request')
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context([
                'user' => $user,
                'resetUrl' => $resetUrl,
                'expirationMinutes' => 60,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Clean up expired tokens (can be called via cron job)
     */
    public function cleanupExpiredTokens(): int
    {
        return $this->tokenRepository->deleteExpiredTokens();
    }
}
