<?php

namespace App\Controller\Api;

use App\Dto\PasswordReset\RequestPasswordResetDto;
use App\Dto\PasswordReset\ResetPasswordDto;
use App\Service\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;

#[Route('/password-reset')]
#[OA\Tag(name: 'Password Reset')]
class PasswordResetController extends AbstractController
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService,
        private readonly LoggerInterface $logger
    ) {}

    #[Route('/request', name: 'api_password_reset_request', methods: ['POST'])]
    #[OA\Post(
        path: '/api/password-reset/request',
        summary: 'Request a password reset token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'user@example.com'
                    )
                ]
            )
        ),
        tags: ['Password Reset'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset email sent (always returns 200 even if email not found for security)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'If this email exists, a password reset link has been sent'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid email format',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests - Rate limit exceeded'
            )
        ]
    )]
    public function requestPasswordReset(
        #[MapRequestPayload] RequestPasswordResetDto $dto,
        RateLimiterFactory $passwordResetLimiter
    ): JsonResponse {
        // Apply rate limiting to prevent abuse
        $limiter = $passwordResetLimiter->create($dto->email);

        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(
                ['error' => 'Too many password reset requests. Please try again later.'],
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        try {
            $this->passwordResetService->requestPasswordReset($dto->email);

            // Always return the same message for security (prevents email enumeration)
            return $this->json([
                'message' => 'If this email exists, a password reset link has been sent'
            ]);
        } catch (\Exception $e) {
            // Log error but don't expose details to user
            $this->logger->error('Password reset request failed', [
                'email' => $dto->email,
                'error' => $e->getMessage()
            ]);

            return $this->json([
                'message' => 'If this email exists, a password reset link has been sent'
            ]);
        }
    }

    #[Route('/reset', name: 'api_password_reset_reset', methods: ['POST'])]
    #[OA\Post(
        path: '/api/password-reset/reset',
        summary: 'Reset password using token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'newPassword'],
                properties: [
                    new OA\Property(
                        property: 'token',
                        type: 'string',
                        example: '1a2b3c4d5e6f7g8h9i0j1k2l3m4n5o6p7q8r9s0t1u2v3w4x5y6z7a8b9c0d1e2f'
                    ),
                    new OA\Property(
                        property: 'newPassword',
                        type: 'string',
                        format: 'password',
                        example: 'MySecureP@ssw0rd123',
                        description: 'Must be at least 12 characters long with uppercase, lowercase, digits, and special characters (ANSSI recommendation)'
                    )
                ]
            )
        ),
        tags: ['Password Reset'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Password has been reset successfully'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid or expired token, or password does not meet requirements',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests - Rate limit exceeded'
            )
        ]
    )]
    public function resetPassword(
        #[MapRequestPayload] ResetPasswordDto $dto,
        RateLimiterFactory $passwordResetLimiter
    ): JsonResponse {
        // Apply rate limiting
        $limiter = $passwordResetLimiter->create($dto->token);

        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(
                ['error' => 'Too many password reset attempts. Please try again later.'],
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        try {
            $this->passwordResetService->resetPassword($dto->token, $dto->newPassword);

            return $this->json([
                'message' => 'Password has been reset successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->logger->error('Password reset failed', [
                'error' => $e->getMessage()
            ]);

            return $this->json(
                ['error' => 'An error occurred while resetting your password'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
