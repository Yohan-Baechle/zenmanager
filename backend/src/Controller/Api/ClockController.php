<?php

namespace App\Controller\Api;

use App\Dto\Clock\ClockInputDto;
use App\Dto\Clock\ClockUpdateDto;
use App\Entity\Clock;
use App\Entity\ClockRequest;
use App\Entity\User;
use App\Mapper\ClockMapper;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Clocks')]
class ClockController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClockMapper $clockMapper,
    ) {
    }

    private function getEffectiveLastStatus(User $user, \DateTimeImmutable $beforeTime): ?bool
    {
        $lastClock = $this->em->getRepository(Clock::class)
            ->findOneBy(['owner' => $user], ['time' => 'DESC']);

        $lastPendingRequest = $this->em->getRepository(ClockRequest::class)
            ->createQueryBuilder('cr')
            ->where('cr.user = :user')
            ->andWhere('cr.status = :status')
            ->andWhere('cr.type = :type')
            ->andWhere('cr.requestedTime < :beforeTime')
            ->setParameter('user', $user)
            ->setParameter('status', 'PENDING')
            ->setParameter('type', 'CREATE')
            ->setParameter('beforeTime', $beforeTime)
            ->orderBy('cr.requestedTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$lastClock && !$lastPendingRequest) {
            return null;
        }

        if (!$lastClock) {
            return $lastPendingRequest->getRequestedStatus();
        }

        if (!$lastPendingRequest) {
            return $lastClock->isStatus();
        }

        return $lastPendingRequest->getRequestedTime() > $lastClock->getTime()
            ? $lastPendingRequest->getRequestedStatus()
            : $lastClock->isStatus();
    }

    #[Route('/clocks', name: 'api_clocks_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/clocks',
        summary: 'Set the arrival/departure of the authenticated user',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'time',
                        description: 'Optional. Clock time. If not provided, the current server time will be used automatically.',
                        type: 'string',
                        format: 'date-time',
                        example: '2025-10-08T09:00:00+00:00',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'status',
                        description: 'Optional. true for clock-in, false for clock-out. If not provided, status is automatically alternated (clock-in after clock-out and vice versa). If last clock-in was on a previous day, an automatic clock-out at 23:59:59 is created first.',
                        type: 'boolean',
                        example: true,
                        nullable: true
                    ),
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
                                        new OA\Property(property: 'name', type: 'string', example: 'Development Team'),
                                    ],
                                    type: 'object',
                                    nullable: true
                                ),
                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input or badging too quickly (less than 1 minute since last badge)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Cannot badge twice within 1 minute'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Invalid or missing JWT token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Authentication required'),
                    ]
                )
            ),
        ]
    )]
    public function create(
        #[CurrentUser] ?User $user,
        #[MapRequestPayload] ?ClockInputDto $dto = null,
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        // Initialize DTO if null (empty body)
        if (null === $dto) {
            $dto = new ClockInputDto();
        }

        // Automatically generate timestamp if not provided
        if (null === $dto->time) {
            $dto->time = new \DateTimeImmutable();
        }

        $this->denyAccessUnlessGranted('USER_EDIT', $user);

        $lastClock = $this->em->getRepository(Clock::class)
            ->findOneBy(['owner' => $user], ['time' => 'DESC']);

        if (null === $dto->status) {
            if ($lastClock) {
                $timeDiff = $dto->time->getTimestamp() - $lastClock->getTime()->getTimestamp();
                if ($timeDiff < 60) {
                    return $this->json([
                        'error' => 'Cannot badge twice within 1 minute',
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $effectiveLastStatus = $this->getEffectiveLastStatus($user, $dto->time);

            if (null === $effectiveLastStatus) {
                $dto->status = true;
            } else {
                if (true === $effectiveLastStatus && $lastClock && true === $lastClock->isStatus()) {
                    $lastClockDate = $lastClock->getTime()->format('Y-m-d');
                    $currentDate = $dto->time->format('Y-m-d');

                    if ($lastClockDate !== $currentDate) {
                        $clockRequest = new ClockRequest();
                        $clockRequest->setUser($user);
                        $clockRequest->setType('UPDATE');
                        $clockRequest->setTargetClock($lastClock);
                        $clockRequest->setRequestedTime(
                            \DateTimeImmutable::createFromFormat(
                                'Y-m-d H:i:s',
                                $lastClockDate.' 23:59:59'
                            )
                        );
                        $clockRequest->setRequestedStatus(false);
                        $clockRequest->setReason('Automatic request: forgotten clock-out from previous day. The system detected an unclosed clock-in and created this request for manager review.');
                        $this->em->persist($clockRequest);

                        $dto->status = true;
                    } else {
                        $dto->status = !$effectiveLastStatus;
                    }
                } else {
                    $dto->status = !$effectiveLastStatus;
                }
            }
        }

        $clock = $this->clockMapper->toEntity($dto, $user);

        $this->em->persist($clock);
        $this->em->flush();

        $outputDto = $this->clockMapper->toOutputDto($clock);

        return $this->json($outputDto, Response::HTTP_CREATED);
    }

    #[Route('/clocks/{id}', name: 'api_clocks_show', methods: ['GET'])]
    #[IsGranted('CLOCK_VIEW', 'clock')]
    #[OA\Get(
        path: '/api/clocks/{id}',
        summary: 'Get a specific clock entry',
        tags: ['Clocks']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Clock ID',
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
                                new OA\Property(property: 'name', type: 'string', example: 'Development Team'),
                            ],
                            type: 'object',
                            nullable: true
                        ),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
                    ],
                    type: 'object'
                ),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Clock not found'
    )]
    public function show(Clock $clock): JsonResponse
    {
        $dto = $this->clockMapper->toOutputDto($clock);

        return $this->json($dto);
    }

    #[Route('/clocks/{id}', name: 'api_clocks_update', methods: ['PUT'])]
    #[IsGranted('CLOCK_EDIT', 'clock')]
    #[OA\Put(
        path: '/api/clocks/{id}',
        summary: 'Update a clock entry (managers only)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['time', 'status'],
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
                        description: 'true for clock-in, false for clock-out',
                        type: 'boolean',
                        example: true
                    ),
                ]
            )
        ),
        tags: ['Clocks']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Clock ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Clock updated successfully',
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
                                new OA\Property(property: 'name', type: 'string', example: 'Development Team'),
                            ],
                            type: 'object',
                            nullable: true
                        ),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
                    ],
                    type: 'object'
                ),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - Only managers of the team can edit clocks'
    )]
    #[OA\Response(
        response: 404,
        description: 'Clock not found'
    )]
    public function update(
        Clock $clock,
        #[MapRequestPayload] ClockUpdateDto $dto,
    ): JsonResponse {
        $clock->setTime($dto->time);
        $clock->setStatus($dto->status);

        $this->em->flush();

        $outputDto = $this->clockMapper->toOutputDto($clock);

        return $this->json($outputDto);
    }

    #[Route('/clocks/{id}', name: 'api_clocks_delete', methods: ['DELETE'])]
    #[IsGranted('CLOCK_DELETE', 'clock')]
    #[OA\Delete(
        path: '/api/clocks/{id}',
        summary: 'Delete a clock entry (managers only)',
        tags: ['Clocks']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Clock ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 204,
        description: 'Clock deleted successfully'
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - Only managers of the team can delete clocks'
    )]
    #[OA\Response(
        response: 404,
        description: 'Clock not found'
    )]
    public function delete(Clock $clock): JsonResponse
    {
        $this->em->remove($clock);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
