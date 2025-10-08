<?php

namespace App\Controller\Api;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Teams')]
class TeamController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer
    ) {
    }

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
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                ]
            )
        )
    )]
    public function index(TeamRepository $teamRepository): JsonResponse
    {
        $teams = $teamRepository->findAll();
        return $this->json($teams, Response::HTTP_OK, [], ['groups' => 'team:read']);
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
        return $this->json($team, Response::HTTP_OK, [], ['groups' => 'team:read']);
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
                    new OA\Property(property: 'description', type: 'string', example: 'Team responsible for marketing activities', nullable: true)
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
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $team = $this->serializer->deserialize(
                $request->getContent(),
                Team::class,
                'json'
            );

            $errors = $this->validator->validate($team);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $this->em->persist($team);
            $this->em->flush();

            return $this->json($team, Response::HTTP_CREATED, [], ['groups' => 'team:read']);
        } catch (ExceptionInterface $e) {
            return $this->json(['error' => 'Invalid data format'], Response::HTTP_BAD_REQUEST);
        }
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
                    new OA\Property(property: 'description', type: 'string', example: 'Updated description', nullable: true)
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
                description: 'Team not found'
            )
        ]
    )]
    public function update(Team $team, Request $request): JsonResponse
    {
        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Team::class,
                'json',
                ['object_to_populate' => $team]
            );

            $errors = $this->validator->validate($team);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $this->em->flush();

            return $this->json($team, Response::HTTP_OK, [], ['groups' => 'team:read']);
        } catch (ExceptionInterface $e) {
            return $this->json(['error' => 'Invalid data format'], Response::HTTP_BAD_REQUEST);
        }
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
