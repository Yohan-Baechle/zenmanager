<?php

namespace App\Service;

use App\Dto\User\UserAdminCreateDto;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;


/**
 * Service for creating users by administrators.
 * Automatically generates a secure password and sends it via email.
 */
class UserCreationService
{
    public function __construct(
        private readonly PasswordGeneratorService $passwordGenerator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        #[Autowire('%env(MAILER_FROM)%')]
        private readonly string $mailerFrom
    ) {}

    /**
     * @return array{user: User, temporaryPassword: string}
     */
    public function createUser(UserAdminCreateDto $dto, ?Team $team = null): array
    {
        $temporaryPassword = $this->passwordGenerator->generate();

        $user = new User();
        $user->setUsername($dto->username);
        $user->setEmail($dto->email);
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setPhoneNumber($dto->phoneNumber);
        $user->setTeam($team);

        if ($dto->role === 'manager') {
            $user->setRoles(['ROLE_MANAGER']);
        } elseif ($dto->role === 'employee') {
            $user->setRoles(['ROLE_EMPLOYEE']);
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $temporaryPassword);
        $user->setPassword($hashedPassword);

        return [
            'user' => $user,
            'temporaryPassword' => $temporaryPassword
        ];
    }

    public function sendWelcomeEmail(User $user, string $temporaryPassword): void
    {
        $htmlContent = $this->twig->render('emails/account_created.html.twig', [
            'user' => $user,
            'temporaryPassword' => $temporaryPassword,
        ]);

        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Welcome to Time Manager - Your Account Details')
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    public function sendPasswordRegeneratedEmail(User $user, string $temporaryPassword): void
    {
        $htmlContent = $this->twig->render('emails/password_regenerated.html.twig', [
            'user' => $user,
            'temporaryPassword' => $temporaryPassword,
        ]);

        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Password Reset - Time Manager')
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}
