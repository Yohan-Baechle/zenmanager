<?php

namespace App\Controller\Api;

use App\Dto\WorkingTime\WorkingTimeInputDto;
use App\Dto\WorkingTime\WorkingTimeUpdateDto;
use App\Entity\User;
use App\Entity\WorkingTime;
use App\Mapper\WorkingTimeMapper;
use App\Repository\WorkingTimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Working Times')]
class WorkingTimeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WorkingTimeMapper $workingTimeMapper,
    ) {
    }

    #[Route('/workingtimes/{userId}', name: 'api_workingtimes_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/workingtimes/{userId}',
        summary: 'Get working times for a user',
        tags: ['Working Times']
    )]
    #[OA\Parameter(
        name: 'userId',
        description: 'User ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'start',
        description: 'Start date (format: YYYY-MM-DD)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'end',
        description: 'End date (format: YYYY-MM-DD)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'startTime', type: 'string', format: 'date-time', example: '2025-01-15T09:00:00+00:00'),
                    new OA\Property(property: 'endTime', type: 'string', format: 'date-time', example: '2025-01-15T17:00:00+00:00'),
                    new OA\Property(
                        property: 'user',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'username', type: 'string', example: 'jdoe'),
                            new OA\Property(property: 'email', type: 'string', example: 'jdoe@example.com'),
                            new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                            new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                        ],
                        type: 'object'
                    ),
                    new OA\Property(property: 'durationMinutes', type: 'integer', example: 480),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found'
    )]
    public function index(int $userId, Request $request, WorkingTimeRepository $workingTimeRepository): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($userId);

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('USER_VIEW_CLOCKS', $user);

        $start = $request->query->get('start');
        $end = $request->query->get('end');

        if ($start && $end) {
            $workingTimes = $workingTimeRepository->findByUserAndPeriod(
                $user,
                new \DateTimeImmutable($start),
                new \DateTimeImmutable($end)
            );
        } else {
            $workingTimes = $workingTimeRepository->findBy(['owner' => $user], ['startTime' => 'DESC']);
        }

        $dtos = $this->workingTimeMapper->toOutputDtoCollection($workingTimes);

        return $this->json($dtos);
    }

    #[Route('/workingtimes/{userId}/{id}', name: 'api_workingtimes_show', methods: ['GET'])]
    #[IsGranted('WORKING_TIME_VIEW', 'workingTime')]
    #[OA\Get(
        path: '/api/workingtimes/{userId}/{id}',
        summary: 'Get a specific working time',
        tags: ['Working Times']
    )]
    #[OA\Parameter(
        name: 'userId',
        description: 'User ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Working Time ID',
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
                new OA\Property(property: 'startTime', type: 'string', format: 'date-time', example: '2025-01-15T09:00:00+00:00'),
                new OA\Property(property: 'endTime', type: 'string', format: 'date-time', example: '2025-01-15T17:00:00+00:00'),
                new OA\Property(
                    property: 'user',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'username', type: 'string', example: 'jdoe'),
                        new OA\Property(property: 'email', type: 'string', example: 'jdoe@example.com'),
                        new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                    ],
                    type: 'object'
                ),
                new OA\Property(property: 'durationMinutes', type: 'integer', example: 480),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - Working time does not belong to this user'
    )]
    #[OA\Response(
        response: 404,
        description: 'Working time not found'
    )]
    public function show(int $userId, WorkingTime $workingTime): JsonResponse
    {
        if ($workingTime->getOwner()->getId() !== $userId) {
            return $this->json(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $dto = $this->workingTimeMapper->toOutputDto($workingTime);

        return $this->json($dto);
    }

    #[Route('/workingtimes/{userId}', name: 'api_workingtimes_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/workingtimes/{userId}',
        summary: 'Create a new working time (manual correction)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['startTime', 'endTime'],
                properties: [
                    new OA\Property(property: 'startTime', type: 'string', format: 'date-time', example: '2025-01-15T09:00:00+00:00'),
                    new OA\Property(property: 'endTime', type: 'string', format: 'date-time', example: '2025-01-15T17:00:00+00:00'),
                ]
            )
        ),
        tags: ['Working Times'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Working time created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'startTime', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'endTime', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'user', type: 'object'),
                        new OA\Property(property: 'durationMinutes', type: 'integer'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            ),
        ]
    )]
    public function create(
        int $userId,
        #[MapRequestPayload] WorkingTimeInputDto $dto,
    ): JsonResponse {
        $user = $this->em->getRepository(User::class)->find($userId);

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('USER_EDIT', $user);

        $workingTime = $this->workingTimeMapper->toEntity($dto, $user);

        $this->em->persist($workingTime);
        $this->em->flush();

        $outputDto = $this->workingTimeMapper->toOutputDto($workingTime);

        return $this->json($outputDto, Response::HTTP_CREATED);
    }

    #[Route('/workingtimes/{id}', name: 'api_workingtimes_update', methods: ['PUT'])]
    #[IsGranted('WORKING_TIME_EDIT', 'workingTime')]
    #[OA\Put(
        path: '/api/workingtimes/{id}',
        summary: 'Update an existing working time',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'startTime', type: 'string', format: 'date-time', example: '2025-01-15T09:00:00+00:00'),
                    new OA\Property(property: 'endTime', type: 'string', format: 'date-time', example: '2025-01-15T17:30:00+00:00'),
                ]
            )
        ),
        tags: ['Working Times'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Working Time ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Working time updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'startTime', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'endTime', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'user', type: 'object'),
                        new OA\Property(property: 'durationMinutes', type: 'integer'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input'
            ),
            new OA\Response(
                response: 404,
                description: 'Working time not found'
            ),
        ]
    )]
    public function update(
        WorkingTime $workingTime,
        #[MapRequestPayload] WorkingTimeUpdateDto $dto,
    ): JsonResponse {
        $this->workingTimeMapper->updateEntity($workingTime, $dto);

        $this->em->flush();

        $outputDto = $this->workingTimeMapper->toOutputDto($workingTime);

        return $this->json($outputDto);
    }

    #[Route('/workingtimes/{id}', name: 'api_workingtimes_delete', methods: ['DELETE'])]
    #[IsGranted('WORKING_TIME_DELETE', 'workingTime')]
    #[OA\Delete(
        path: '/api/workingtimes/{id}',
        summary: 'Delete a working time',
        tags: ['Working Times'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Working Time ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Working time deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Working time not found'
            ),
        ]
    )]
    public function delete(WorkingTime $workingTime): JsonResponse
    {
        $this->em->remove($workingTime);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
