<?php

namespace App\Controller\Api;

use App\Dto\ClockRequest\CreateClockRequestDto;
use App\Dto\ClockRequest\ApproveClockRequestDto;
use App\Dto\ClockRequest\RejectClockRequestDto;
use App\Entity\Clock;
use App\Entity\ClockRequest;
use App\Entity\User;
use App\Mapper\ClockRequestMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Clock Requests')]
class ClockRequestController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClockRequestMapper $clockRequestMapper
    ) {}

    #[Route('/clock-requests', name: 'api_clock_requests_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/clock-requests',
        description: 'Allows employees to submit a request for manual clock entry. Supports three types: CREATE (new clock), UPDATE (modify existing clock), DELETE (remove clock).',
        summary: 'Create a manual clock request (employees)',
        tags: ['Clock Requests']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['type', 'requestedTime', 'reason'],
            properties: [
                new OA\Property(property: 'type', description: 'Type of request', type: 'string', enum: ['CREATE', 'UPDATE', 'DELETE'], example: 'CREATE'),
                new OA\Property(property: 'requestedTime', description: 'Time to be clocked (ISO 8601)', type: 'string', format: 'date-time', example: '2025-10-15T08:00:00+00:00'),
                new OA\Property(property: 'requestedStatus', description: 'Clock status (true=in, false=out). Required for CREATE type', type: 'boolean', example: true, nullable: true),
                new OA\Property(property: 'targetClockId', description: 'ID of clock to modify/delete. Required for UPDATE/DELETE types', type: 'integer', example: 42, nullable: true),
                new OA\Property(property: 'reason', description: 'Detailed justification (10-1000 chars)', type: 'string', maxLength: 1000, minLength: 10, example: 'I forgot to clock in this morning when I arrived.')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Clock request created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'type', type: 'string', example: 'CREATE'),
                new OA\Property(property: 'requestedTime', type: 'string', format: 'date-time'),
                new OA\Property(property: 'requestedStatus', type: 'boolean', nullable: true),
                new OA\Property(property: 'status', type: 'string', example: 'PENDING'),
                new OA\Property(property: 'reason', type: 'string'),
                new OA\Property(property: 'user', type: 'object'),
                new OA\Property(property: 'targetClock', type: 'object', nullable: true),
                new OA\Property(property: 'reviewedBy', type: 'object', nullable: true),
                new OA\Property(property: 'reviewedAt', type: 'string', format: 'date-time', nullable: true),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - validation errors or missing required fields',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'requestedStatus is required for CREATE type')
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - trying to modify another user\'s clock',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'You can only request changes to your own clocks')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Target clock not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Target clock not found')
            ]
        )
    )]
    public function create(
        #[MapRequestPayload] CreateClockRequestDto $dto,
        #[CurrentUser] User $currentUser
    ): JsonResponse {
        if ($dto->type === 'CREATE' && $dto->requestedStatus === null) {
            return $this->json([
                'error' => 'requestedStatus is required for CREATE type'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (in_array($dto->type, ['UPDATE', 'DELETE']) && $dto->targetClockId === null) {
            return $this->json([
                'error' => 'targetClockId is required for UPDATE and DELETE types'
            ], Response::HTTP_BAD_REQUEST);
        }

        $targetClock = null;
        if ($dto->targetClockId) {
            $targetClock = $this->em->getRepository(Clock::class)->find($dto->targetClockId);
            if (!$targetClock) {
                return $this->json(['error' => 'Target clock not found'], Response::HTTP_NOT_FOUND);
            }

            if ($targetClock->getOwner() !== $currentUser) {
                return $this->json(['error' => 'You can only request changes to your own clocks'], Response::HTTP_FORBIDDEN);
            }
        }

        $clockRequest = new ClockRequest();
        $clockRequest->setUser($currentUser);
        $clockRequest->setType($dto->type);
        $clockRequest->setRequestedTime($dto->requestedTime);
        $clockRequest->setRequestedStatus($dto->requestedStatus);
        $clockRequest->setTargetClock($targetClock);
        $clockRequest->setReason($dto->reason);

        $this->em->persist($clockRequest);
        $this->em->flush();

        $outputDto = $this->clockRequestMapper->toOutputDto($clockRequest);
        return $this->json($outputDto, Response::HTTP_CREATED);
    }

    #[Route('/clock-requests', name: 'api_clock_requests_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/clock-requests',
        description: 'Returns clock requests based on user role: ADMIN sees all, MANAGER sees their own requests + their team members\' requests, EMPLOYEE sees only their own requests.',
        summary: 'List clock requests (filtered by role)',
        tags: ['Clock Requests']
    )]
    #[OA\Parameter(
        name: 'status',
        description: 'Filter by request status',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['PENDING', 'APPROVED', 'REJECTED'])
    )]
    #[OA\Response(
        response: 200,
        description: 'List of clock requests',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'type', type: 'string', example: 'CREATE'),
                    new OA\Property(property: 'requestedTime', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'requestedStatus', type: 'boolean', nullable: true),
                    new OA\Property(property: 'status', type: 'string', example: 'PENDING'),
                    new OA\Property(property: 'reason', type: 'string'),
                    new OA\Property(property: 'user', type: 'object'),
                    new OA\Property(property: 'targetClock', type: 'object', nullable: true),
                    new OA\Property(property: 'reviewedBy', type: 'object', nullable: true),
                    new OA\Property(property: 'reviewedAt', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                ]
            )
        )
    )]
    public function list(
        Request $request,
        #[CurrentUser] User $currentUser
    ): JsonResponse {
        $qb = $this->em->getRepository(ClockRequest::class)->createQueryBuilder('cr');

        $status = $request->query->get('status');
        if ($status) {
            $qb->andWhere('cr.status = :status')
               ->setParameter('status', $status);
        }

        if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
        } elseif (in_array('ROLE_MANAGER', $currentUser->getRoles())) {
            $managedTeams = $currentUser->getManagedTeams();

            if ($managedTeams->isEmpty()) {
                $qb->andWhere('cr.user = :user')
                   ->setParameter('user', $currentUser);
            } else {
                $qb->join('cr.user', 'u')
                   ->andWhere('cr.user = :user OR u.team IN (:teams)')
                   ->setParameter('user', $currentUser)
                   ->setParameter('teams', $managedTeams);
            }
        } else {
            $qb->andWhere('cr.user = :user')
               ->setParameter('user', $currentUser);
        }

        $qb->orderBy('cr.createdAt', 'DESC');

        $clockRequests = $qb->getQuery()->getResult();
        $dtos = $this->clockRequestMapper->toOutputDtoCollection($clockRequests);

        return $this->json($dtos);
    }

    #[Route('/clock-requests/{id}', name: 'api_clock_requests_show', methods: ['GET'])]
    #[IsGranted('CLOCK_REQUEST_VIEW', 'clockRequest')]
    #[OA\Get(
        path: '/api/clock-requests/{id}',
        description: 'Returns details of a single clock request. Access control: users can see their own requests, managers can see their own requests + their team members\' requests, admins can see all.',
        summary: 'Get a specific clock request',
        tags: ['Clock Requests']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Clock request ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Clock request details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'type', type: 'string', example: 'CREATE'),
                new OA\Property(property: 'requestedTime', type: 'string', format: 'date-time'),
                new OA\Property(property: 'requestedStatus', type: 'boolean', nullable: true),
                new OA\Property(property: 'status', type: 'string', example: 'PENDING'),
                new OA\Property(property: 'reason', type: 'string'),
                new OA\Property(property: 'user', type: 'object'),
                new OA\Property(property: 'targetClock', type: 'object', nullable: true),
                new OA\Property(property: 'reviewedBy', type: 'object', nullable: true),
                new OA\Property(property: 'reviewedAt', type: 'string', format: 'date-time', nullable: true),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied - user cannot view this clock request',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Access Denied')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Clock request not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Not Found')
            ]
        )
    )]
    public function show(ClockRequest $clockRequest): JsonResponse
    {
        $dto = $this->clockRequestMapper->toOutputDto($clockRequest);
        return $this->json($dto);
    }

    #[Route('/clock-requests/{id}/approve', name: 'api_clock_requests_approve', methods: ['POST'])]
    #[IsGranted('CLOCK_REQUEST_REVIEW', 'clockRequest')]
    #[OA\Post(
        path: '/api/clock-requests/{id}/approve',
        description: 'Approves a pending clock request and executes the action (CREATE/UPDATE/DELETE clock). Managers can approve requests from their team, admins can approve any request. Optional override values can be provided.',
        summary: 'Approve a clock request (managers/admins only)',
        tags: ['Clock Requests']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Clock request ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        description: 'Optional: override the requested time and/or status',
        required: false,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'approvedTime', description: 'Override the requested time (optional)', type: 'string', format: 'date-time', example: '2025-10-15T08:30:00+00:00', nullable: true),
                new OA\Property(property: 'approvedStatus', description: 'Override the requested status (optional)', type: 'boolean', example: true, nullable: true)
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Clock request approved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'type', type: 'string', example: 'CREATE'),
                new OA\Property(property: 'requestedTime', type: 'string', format: 'date-time'),
                new OA\Property(property: 'requestedStatus', type: 'boolean', nullable: true),
                new OA\Property(property: 'status', type: 'string', example: 'APPROVED'),
                new OA\Property(property: 'reason', type: 'string'),
                new OA\Property(property: 'user', type: 'object'),
                new OA\Property(property: 'targetClock', type: 'object', nullable: true),
                new OA\Property(property: 'reviewedBy', type: 'object'),
                new OA\Property(property: 'reviewedAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - target clock not found for UPDATE/DELETE',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Target clock not found')
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied - user cannot review this clock request',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Access Denied')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Clock request not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Not Found')
            ]
        )
    )]
    public function approve(
        ClockRequest $clockRequest,
        #[MapRequestPayload] ApproveClockRequestDto $dto,
        #[CurrentUser] User $currentUser
    ): JsonResponse {
        $finalTime = $dto->approvedTime ?? $clockRequest->getRequestedTime();
        $finalStatus = $dto->approvedStatus ?? $clockRequest->getRequestedStatus();

        switch ($clockRequest->getType()) {
            case 'CREATE':
                $clock = new Clock();
                $clock->setOwner($clockRequest->getUser());
                $clock->setTime($finalTime);
                $clock->setStatus($finalStatus);
                $this->em->persist($clock);
                break;

            case 'UPDATE':
                $targetClock = $clockRequest->getTargetClock();
                if (!$targetClock) {
                    return $this->json(['error' => 'Target clock not found'], Response::HTTP_BAD_REQUEST);
                }
                $targetClock->setTime($finalTime);
                $targetClock->setStatus($finalStatus);
                break;

            case 'DELETE':
                $targetClock = $clockRequest->getTargetClock();
                if (!$targetClock) {
                    return $this->json(['error' => 'Target clock not found'], Response::HTTP_BAD_REQUEST);
                }
                $this->em->remove($targetClock);
                break;
        }

        $clockRequest->setStatus('APPROVED');
        $clockRequest->setReviewedBy($currentUser);
        $clockRequest->setReviewedAt(new \DateTimeImmutable());

        $this->em->flush();

        $outputDto = $this->clockRequestMapper->toOutputDto($clockRequest);
        return $this->json($outputDto);
    }

    #[Route('/clock-requests/{id}/reject', name: 'api_clock_requests_reject', methods: ['POST'])]
    #[IsGranted('CLOCK_REQUEST_REVIEW', 'clockRequest')]
    #[OA\Post(
        path: '/api/clock-requests/{id}/reject',
        description: 'Rejects a pending clock request with a mandatory rejection reason. Managers can reject requests from their team, admins can reject any request. The rejection reason is appended to the original request reason.',
        summary: 'Reject a clock request (managers/admins only)',
        tags: ['Clock Requests']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Clock request ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['rejectionReason'],
            properties: [
                new OA\Property(property: 'rejectionReason', type: 'string', minLength: 10, maxLength: 1000, example: 'The requested time conflicts with another team member\'s schedule.', description: 'Detailed reason for rejection (10-1000 chars)')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Clock request rejected successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'type', type: 'string', example: 'CREATE'),
                new OA\Property(property: 'requestedTime', type: 'string', format: 'date-time'),
                new OA\Property(property: 'requestedStatus', type: 'boolean', nullable: true),
                new OA\Property(property: 'status', type: 'string', example: 'REJECTED'),
                new OA\Property(property: 'reason', description: 'Original reason + rejection reason', type: 'string'),
                new OA\Property(property: 'user', type: 'object'),
                new OA\Property(property: 'targetClock', type: 'object', nullable: true),
                new OA\Property(property: 'reviewedBy', type: 'object'),
                new OA\Property(property: 'reviewedAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - validation errors on rejection reason',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Rejection reason must be at least 10 characters')
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied - user cannot review this clock request',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Access Denied')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Clock request not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Not Found')
            ]
        )
    )]
    public function reject(
        ClockRequest $clockRequest,
        #[MapRequestPayload] RejectClockRequestDto $dto,
        #[CurrentUser] User $currentUser
    ): JsonResponse {
        $clockRequest->setStatus('REJECTED');
        $clockRequest->setReviewedBy($currentUser);
        $clockRequest->setReviewedAt(new \DateTimeImmutable());
        $clockRequest->setReason(
            $clockRequest->getReason() . "\n\n[REJECTION REASON]: " . $dto->rejectionReason
        );

        $this->em->flush();

        $outputDto = $this->clockRequestMapper->toOutputDto($clockRequest);
        return $this->json($outputDto);
    }
}
