<?php

namespace App\Controller\Api;

use App\Dto\User\UserInputDto;
use App\Dto\User\UserUpdateDto;
use App\Entity\Clock;
use App\Entity\Team;
use App\Entity\User;
use App\Mapper\ClockMapper;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use App\Service\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserMapper $userMapper,
        private readonly ClockMapper $clockMapper,
        private readonly Paginator $paginator
    ) {}

    #[Route('/users', name: 'api_users_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        summary: 'Get all users',
        tags: ['Users']
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 20, minimum: 1, maximum: 100)
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
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
                                    new OA\Property(property: 'name', type: 'string', example: 'Development Team')
                                ],
                                type: 'object',
                                nullable: true
                            ),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                        ]
                    )
                ),
                new OA\Property(
                    property: 'meta',
                    properties: [
                        new OA\Property(property: 'currentPage', type: 'integer', example: 1),
                        new OA\Property(property: 'itemsPerPage', type: 'integer', example: 20),
                        new OA\Property(property: 'totalItems', type: 'integer', example: 50),
                        new OA\Property(property: 'totalPages', type: 'integer', example: 3)
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    public function index(Request $request, UserRepository $userRepository): JsonResponse
    {
        $page = $request->query->getInt('page', Paginator::DEFAULT_PAGE);
        $limit = $request->query->getInt('limit', Paginator::DEFAULT_LIMIT);

        $queryBuilder = $userRepository->createQueryBuilder('u');
        $paginatedResult = $this->paginator->paginate($queryBuilder, $page, $limit);

        $dtos = $this->userMapper->toOutputDtoCollection($paginatedResult['items']);

        return $this->json([
            'data' => $dtos,
            'meta' => $paginatedResult['meta']
        ]);
    }

    #[Route('/users/{id}', name: 'api_users_show', methods: ['GET'])]
    #[IsGranted('USER_VIEW', 'user')]
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
                        new OA\Property(property: 'name', type: 'string', example: 'Development Team')
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
        response: 404,
        description: 'User not found'
    )]
    public function show(User $user): JsonResponse
    {
        $dto = $this->userMapper->toOutputDto($user);
        return $this->json($dto);
    }

    #[Route('/users', name: 'api_users_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users',
        summary: 'Create a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'email', 'password', 'firstName', 'lastName', 'role'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'jsmith'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'newuser@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'SecurePass123!'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Smith'),
                    new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                    new OA\Property(property: 'role', description: 'Must be either "employee" or "manager"', type: 'string', example: 'employee'),
                    new OA\Property(property: 'teamId', description: 'Team ID', type: 'integer', example: 1, nullable: true)
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
                        new OA\Property(property: 'username', type: 'string', example: 'jsmith'),
                        new OA\Property(property: 'email', type: 'string', example: 'newuser@example.com'),
                        new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Smith'),
                        new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                        new OA\Property(property: 'role', type: 'string', example: 'employee'),
                        new OA\Property(
                            property: 'team',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Development Team')
                            ],
                            type: 'object',
                            nullable: true
                        ),
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
    public function create(
        #[MapRequestPayload] UserInputDto $dto
    ): JsonResponse {
        $team = null;
        if ($dto->teamId !== null) {
            $team = $this->em->getRepository(Team::class)->find($dto->teamId);
            if (!$team) {
                return $this->json(['error' => 'Team not found'], Response::HTTP_NOT_FOUND);
            }
        }

        $user = $this->userMapper->toEntity($dto, $team);

        $this->em->persist($user);
        $this->em->flush();

        $outputDto = $this->userMapper->toOutputDto($user);
        return $this->json($outputDto, Response::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'api_users_update', methods: ['PUT'])]
    #[IsGranted('USER_EDIT', 'user')]
    #[OA\Put(
        path: '/api/users/{id}',
        summary: 'Update an existing user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'jsmith_updated'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'updated@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'NewSecurePass123!'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Smith'),
                    new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                    new OA\Property(property: 'role', description: 'Must be either "employee" or "manager"', type: 'string', example: 'manager'),
                    new OA\Property(property: 'teamId', description: 'Team ID', type: 'integer', example: 1, nullable: true)
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
                        new OA\Property(property: 'username', type: 'string', example: 'jsmith_updated'),
                        new OA\Property(property: 'email', type: 'string', example: 'updated@example.com'),
                        new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Smith'),
                        new OA\Property(property: 'phoneNumber', type: 'string', example: '+33612345678', nullable: true),
                        new OA\Property(property: 'role', type: 'string', example: 'manager'),
                        new OA\Property(
                            property: 'team',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Development Team')
                            ],
                            type: 'object',
                            nullable: true
                        ),
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
    public function update(
        User $user,
        #[MapRequestPayload] UserUpdateDto $dto
    ): JsonResponse {
        $team = $user->getTeam();
        if ($dto->teamId !== null) {
            $team = $this->em->getRepository(Team::class)->find($dto->teamId);
            if (!$team) {
                return $this->json(['error' => 'Team not found'], Response::HTTP_NOT_FOUND);
            }
        }

        $this->userMapper->updateEntity($user, $dto, $team);

        $this->em->flush();

        $outputDto = $this->userMapper->toOutputDto($user);
        return $this->json($outputDto);
    }

    #[Route('/users/{id}', name: 'api_users_delete', methods: ['DELETE'])]
    #[IsGranted('USER_DELETE', 'user')]
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
    #[IsGranted('USER_VIEW_CLOCKS', 'user')]
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
                        description: 'true for clock-in (arrival), false for clock-out (departure)',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'owner',
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
                                    new OA\Property(property: 'name', type: 'string', example: 'Development Team')
                                ],
                                type: 'object',
                                nullable: true
                            ),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                        ],
                        type: 'object'
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

        $clockDtos = $this->clockMapper->toOutputDtoCollection($clocks);

        return $this->json($clockDtos);
    }
}
