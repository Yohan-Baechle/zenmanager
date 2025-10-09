<?php

namespace App\Controller\Api;

use App\Entity\Clock;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface          $validator,
        private readonly SerializerInterface         $serializer
    ) {
    }

    #[Route('/users', name: 'api_users_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        summary: 'Get all users',
        tags: ['Users']
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                    new OA\Property(property: 'role', type: 'string', example: 'employee'),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                ]
            )
        )
    )]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/users/{id}', name: 'api_users_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/{id}',
        summary: 'Get user by ID',
        tags: ['Users']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'User ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                new OA\Property(property: 'role', type: 'string', example: 'employee'),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found'
    )]
    public function show(User $user): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/users', name: 'api_users_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users',
        summary: 'Create a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'firstName', 'lastName', 'role'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'newuser@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'SecurePass123!'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Smith'),
                    new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                    new OA\Property(property: 'role', type: 'string', example: 'employee', description: 'Must be either "employee" or "manager"'),
                    new OA\Property(property: 'teamId', type: 'integer', example: 1, nullable: true, description: 'Team ID')
                ]
            )
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string', example: 'newuser@example.com'),
                        new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Smith'),
                        new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                        new OA\Property(property: 'role', type: 'string', example: 'employee'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Team not found'
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $user = $this->serializer->deserialize(
                $request->getContent(),
                User::class,
                'json'
            );

            return $this->handleUserData($user, $request, Response::HTTP_CREATED);
        } catch (ExceptionInterface $e) {
            return $this->json(['error' => 'Invalid data format'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/users/{id}', name: 'api_users_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users/{id}',
        summary: 'Update an existing user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'updated@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'NewSecurePass123!'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Smith'),
                    new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                    new OA\Property(property: 'role', type: 'string', example: 'manager', description: 'Must be either "employee" or "manager"'),
                    new OA\Property(property: 'teamId', type: 'integer', example: 1, nullable: true, description: 'Team ID')
                ]
            )
        ),
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string', example: 'updated@example.com'),
                        new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Smith'),
                        new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                        new OA\Property(property: 'role', type: 'string', example: 'manager'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input'
            ),
            new OA\Response(
                response: 404,
                description: 'User or Team not found'
            )
        ]
    )]
    public function update(User $user, Request $request): JsonResponse
    {
        try {
            $this->serializer->deserialize(
                $request->getContent(),
                User::class,
                'json',
                ['object_to_populate' => $user]
            );

            return $this->handleUserData($user, $request, Response::HTTP_OK);
        } catch (ExceptionInterface $e) {
            return $this->json(['error' => 'Invalid data format'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/users/{id}', name: 'api_users_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/users/{id}',
        summary: 'Delete a user',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'User deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            )
        ]
    )]
    public function delete(User $user): JsonResponse
    {
        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/users/{id}/clocks', name: 'api_users_clocks', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/{id}/clocks',
        summary: 'Get a summary of the arrivals and departures of an employee',
        tags: ['Users']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'User ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'start',
        description: 'Filter by start date (ISO 8601 format)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', format: 'date-time', example: '2025-10-01T00:00:00Z')
    )]
    #[OA\Parameter(
        name: 'end',
        description: 'Filter by end date (ISO 8601 format)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', format: 'date-time', example: '2025-10-31T23:59:59Z')
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'time', type: 'string', format: 'date-time'),
                    new OA\Property(
                        property: 'status',
                        type: 'boolean',
                        example: true,
                        description: 'true for clock-in (arrival), false for clock-out (departure)'
                    ),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time')
                ]
            )
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid date format'
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found'
    )]
    public function getClocks(User $user, Request $request): JsonResponse
    {
        $queryBuilder = $this->em->getRepository(Clock::class)
            ->createQueryBuilder('c')
            ->where('c.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('c.time', 'DESC');

        // Filter by date if provided
        if ($request->query->has('start')) {
            try {
                $startDate = new \DateTimeImmutable($request->query->get('start'));
                $queryBuilder->andWhere('c.time >= :start')
                    ->setParameter('start', $startDate);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid start date format'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->query->has('end')) {
            try {
                $endDate = new \DateTimeImmutable($request->query->get('end'));
                $queryBuilder->andWhere('c.time <= :end')
                    ->setParameter('end', $endDate);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid end date format'], Response::HTTP_BAD_REQUEST);
            }
        }

        $clocks = $queryBuilder->getQuery()->getResult();

        return $this->json($clocks, Response::HTTP_OK, [], ['groups' => 'clock:read']);
    }

    /**
     * Handle user data validation, password hashing, and persistence
     */
    private function handleUserData(User $user, Request $request, int $statusCode): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->hashPasswordIfProvided($user, $data);

        // Assign team if provided
        if (isset($data['teamId'])) {
            if ($data['teamId'] !== null) {
                $team = $this->em->getRepository(Team::class)->find($data['teamId']);
                if (!$team) {
                    return $this->json(['error' => 'Team not found'], Response::HTTP_NOT_FOUND);
                }
                $user->setTeam($team);
            } else {
                $user->setTeam(null);
            }
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        if ($statusCode === Response::HTTP_CREATED) {
            $this->em->persist($user);
        }

        $this->em->flush();

        return $this->json($user, $statusCode, [], ['groups' => ['user:read', 'team:read']]);
    }

    private function hashPasswordIfProvided(User $user, array $data): void
    {
        if (!empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
    }
}
