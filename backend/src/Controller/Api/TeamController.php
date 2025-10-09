<?php

namespace App\Controller\Api;

use App\Dto\Team\TeamInputDto;
use App\Dto\Team\TeamUpdateDto;
use App\Entity\Team;
use App\Entity\User;
use App\Mapper\TeamMapper;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Teams')]
class TeamController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TeamMapper $teamMapper
    ) {}

    #[Route('/teams', name: 'api_teams_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/teams',
        summary: 'Get all teams',
        tags: ['Teams']
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Development Team'),
                    new OA\Property(property: 'description', type: 'string', example: 'Main development team', nullable: true),
                    new OA\Property(
                        property: 'manager',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'username', type: 'string', example: 'jdoe'),
                            new OA\Property(property: 'email', type: 'string', example: 'manager@example.com'),
                            new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                            new OA\Property(property: 'lastName', type: 'string', example: 'Doe')
                        ],
                        type: 'object',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'employees',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 2),
                                new OA\Property(property: 'username', type: 'string', example: 'jsmith'),
                                new OA\Property(property: 'email', type: 'string', example: 'employee@example.com'),
                                new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                                new OA\Property(property: 'lastName', type: 'string', example: 'Smith'),
                                new OA\Property(property: 'role', type: 'string', example: 'employee')
                            ]
                        )
                    ),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                ]
            )
        )
    )]
    public function index(TeamRepository $teamRepository): JsonResponse
    {
        $teams = $teamRepository->findAll();
        $dtos = $this->teamMapper->toOutputDtoCollection($teams);

        return $this->json($dtos);
    }

    #[Route('/teams/{id}', name: 'api_teams_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/teams/{id}',
        summary: 'Get team by ID',
        tags: ['Teams']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Team ID',
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
                new OA\Property(property: 'name', type: 'string', example: 'Development Team'),
                new OA\Property(property: 'description', type: 'string', example: 'Main development team', nullable: true),
                new OA\Property(
                    property: 'manager',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'jdoe'),
                        new OA\Property(property: 'email', type: 'string', example: 'manager@example.com'),
                        new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Doe')
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'employees',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 2),
                            new OA\Property(property: 'username', type: 'string', example: 'jsmith'),
                            new OA\Property(property: 'email', type: 'string', example: 'employee@example.com'),
                            new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                            new OA\Property(property: 'lastName', type: 'string', example: 'Smith'),
                            new OA\Property(property: 'role', type: 'string', example: 'employee')
                        ]
                    )
                ),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Team not found'
    )]
    public function show(Team $team): JsonResponse
    {
        $dto = $this->teamMapper->toOutputDto($team);
        return $this->json($dto);
    }

    #[Route('/teams', name: 'api_teams_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/teams',
        summary: 'Create a new team',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Marketing Team'),
                    new OA\Property(property: 'description', type: 'string', example: 'Team responsible for marketing activities', nullable: true),
                    new OA\Property(property: 'managerId', description: 'Manager User ID', type: 'integer', example: 1, nullable: true)
                ]
            )
        ),
        tags: ['Teams'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Team created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Marketing Team'),
                        new OA\Property(property: 'description', type: 'string', example: 'Team responsible for marketing activities', nullable: true),
                        new OA\Property(
                            property: 'manager',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'username', type: 'string', example: 'jdoe'),
                                new OA\Property(property: 'email', type: 'string', example: 'manager@example.com'),
                                new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                                new OA\Property(property: 'lastName', type: 'string', example: 'Doe')
                            ],
                            type: 'object',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'employees',
                            type: 'array',
                            items: new OA\Items(type: 'object')
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
                description: 'Manager not found'
            )
        ]
    )]
    public function create(
        #[MapRequestPayload] TeamInputDto $dto
    ): JsonResponse {
        $manager = null;
        if ($dto->managerId !== null) {
            $manager = $this->em->getRepository(User::class)->find($dto->managerId);
            if (!$manager) {
                return $this->json(['error' => 'Manager not found'], Response::HTTP_NOT_FOUND);
            }
        }

        $team = $this->teamMapper->toEntity($dto, $manager);

        $this->em->persist($team);
        $this->em->flush();

        $outputDto = $this->teamMapper->toOutputDto($team);
        return $this->json($outputDto, Response::HTTP_CREATED);
    }

    #[Route('/teams/{id}', name: 'api_teams_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/teams/{id}',
        summary: 'Update an existing team',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Team Name'),
                    new OA\Property(property: 'description', type: 'string', example: 'Updated description', nullable: true),
                    new OA\Property(property: 'managerId', description: 'Manager User ID', type: 'integer', example: 2, nullable: true)
                ]
            )
        ),
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Team ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Team updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Updated Team Name'),
                        new OA\Property(property: 'description', type: 'string', example: 'Updated description', nullable: true),
                        new OA\Property(
                            property: 'manager',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 2),
                                new OA\Property(property: 'username', type: 'string', example: 'jsmith'),
                                new OA\Property(property: 'email', type: 'string', example: 'newmanager@example.com'),
                                new OA\Property(property: 'firstName', type: 'string', example: 'Jane'),
                                new OA\Property(property: 'lastName', type: 'string', example: 'Smith')
                            ],
                            type: 'object',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'employees',
                            type: 'array',
                            items: new OA\Items(type: 'object')
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
                description: 'Team or Manager not found'
            )
        ]
    )]
    public function update(
        Team $team,
        #[MapRequestPayload] TeamUpdateDto $dto
    ): JsonResponse {
        $manager = $team->getManager();

        if ($dto->managerId !== null) {
            $manager = $this->em->getRepository(User::class)->find($dto->managerId);
            if (!$manager) {
                return $this->json(['error' => 'Manager not found'], Response::HTTP_NOT_FOUND);
            }
        }

        $this->teamMapper->updateEntity($team, $dto, $manager);

        $this->em->flush();

        $outputDto = $this->teamMapper->toOutputDto($team);
        return $this->json($outputDto);
    }

    #[Route('/teams/{id}', name: 'api_teams_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/teams/{id}',
        summary: 'Delete a team',
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Team ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Team deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Team not found'
            )
        ]
    )]
    public function delete(Team $team): JsonResponse
    {
        $this->em->remove($team);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
