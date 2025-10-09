<?php

namespace App\Controller\Api;

use App\Dto\Clock\ClockInputDto;
use App\Entity\User;
use App\Mapper\ClockMapper;
use App\Repository\ClockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Clocks')]
class ClockController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClockMapper $clockMapper
    ) {}

    #[Route('/clocks', name: 'api_clocks_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/clocks',
        summary: 'Set the arrival/departure of the authenticated user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['time', 'status', 'userId'],
                properties: [
                    new OA\Property(
                        property: 'time',
                        description: 'Clock time',
                        type: 'string',
                        format: 'date-time',
                        example: '2025-10-08T09:00:00+00:00'
                    ),
                    new OA\Property(
                        property: 'status',
                        description: 'true for clock-in (arrival), false for clock-out (departure)',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'userId',
                        description: 'User ID',
                        type: 'integer',
                        example: 1
                    )
                ]
            )
        ),
        tags: ['Clocks'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Clock entry created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'time', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'owner',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
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
                description: 'User not found'
            )
        ]
    )]
    public function create(
        #[MapRequestPayload] ClockInputDto $dto
    ): JsonResponse {
        $user = $this->em->getRepository(User::class)->find($dto->userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $clock = $this->clockMapper->toEntity($dto, $user);

        $this->em->persist($clock);
        $this->em->flush();

        $outputDto = $this->clockMapper->toOutputDto($clock);
        return $this->json($outputDto, Response::HTTP_CREATED);
    }
}
