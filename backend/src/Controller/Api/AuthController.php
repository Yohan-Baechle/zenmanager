<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Mapper\UserMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Authentication')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserMapper $userMapper
    ) {}

    #[Route('/login_check', name: 'api_login_check', methods: ['POST'])]
    public function loginCheck(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->json([
                'message' => 'Missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'user' => $user,
            'message' => 'Login successful'
        ], Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    #[OA\Get(
        path: '/api/me',
        summary: 'Get current authenticated user information',
        security: [['Bearer' => []]],
        tags: ['Authentication']
    )]
    #[OA\Response(
        response: 200,
        description: 'User information retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'username', type: 'string', example: 'jdoe'),
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                new OA\Property(property: 'role', type: 'string', example: 'employee'),
                new OA\Property(
                    property: 'team',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Development Team'),
                        new OA\Property(property: 'description', type: 'string', example: 'Main development team', nullable: true),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized - Invalid or missing JWT token',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', type: 'integer', example: 401),
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found')
            ]
        )
    )]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication required',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userDto = $this->userMapper->toOutputDto($user);

        return $this->json($userDto);
    }
}
