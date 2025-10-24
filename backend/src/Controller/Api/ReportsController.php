<?php

namespace App\Controller\Api;

use App\Repository\ClockRepository;
use App\Repository\WorkingTimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Reports')]
class ReportsController extends AbstractController
{
    public function __construct(
        private readonly ClockRepository $clockRepository,
        private readonly WorkingTimeRepository $workingTimeRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/reports', name: 'api_reports', methods: ['GET'])]
    #[OA\Get(
        path: '/api/reports',
        summary: 'Get dashboard KPIs and statistics',
        tags: ['Reports']
    )]
    #[OA\Parameter(
        name: 'start_date',
        description: 'Start date (format: YYYY-MM-DD)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-01')
    )]
    #[OA\Parameter(
        name: 'end_date',
        description: 'End date (format: YYYY-MM-DD)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2025-12-31')
    )]
    #[OA\Parameter(
        name: 'team_id',
        description: 'Filter by team ID',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Parameter(
        name: 'user_id',
        description: 'Filter by user ID',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'KPIs retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Dashboard KPIs retrieved successfully'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'start_date', type: 'string', nullable: true),
                                new OA\Property(property: 'end_date', type: 'string', nullable: true),
                                new OA\Property(property: 'team_id', type: 'integer', nullable: true),
                                new OA\Property(property: 'user_id', type: 'integer', nullable: true),
                                new OA\Property(property: 'employee_count', type: 'integer', nullable: true, example: 5)
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'period',
                            properties: [
                                new OA\Property(property: 'total_days', type: 'integer', example: 365),
                                new OA\Property(property: 'working_days', type: 'integer', example: 261),
                                new OA\Property(property: 'weekend_days', type: 'integer', example: 104),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'work_schedule',
                            properties: [
                                new OA\Property(property: 'start_time', type: 'string', example: '08:00'),
                                new OA\Property(property: 'end_time', type: 'string', example: '17:00'),
                                new OA\Property(property: 'tolerance_late', type: 'integer', example: 30),
                                new OA\Property(property: 'tolerance_early_departure', type: 'integer', example: 30),
                                new OA\Property(property: 'standard_hours_per_day', type: 'integer', example: 8),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'kpis',
                            properties: [
                                new OA\Property(property: 'total_working_hours', type: 'number', format: 'float', example: 1848.5),
                                new OA\Property(property: 'late_arrivals_count', type: 'integer', example: 12),
                                new OA\Property(property: 'late_arrivals_rate', type: 'number', format: 'float', example: 5.1),
                                new OA\Property(property: 'early_departures_count', type: 'integer', example: 5),
                                new OA\Property(property: 'present_days_count', type: 'integer', example: 235),
                                new OA\Property(property: 'absent_days_count', type: 'integer', example: 26),
                                new OA\Property(property: 'incomplete_days_count', type: 'integer', example: 3),
                                new OA\Property(property: 'total_exits_count', type: 'integer', example: 470),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid date format'
    )]
    #[OA\Response(
        response: 401,
        description: 'User not authenticated'
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied - Manager can only view reports for their managed teams'
    )]
    #[OA\Response(
        response: 500,
        description: 'Error calculating KPIs'
    )]
    public function getReports(Request $request): JsonResponse
    {
        $startDateStr = $request->query->get('start_date');
        $endDateStr = $request->query->get('end_date');
        $teamId = $request->query->get('team_id') ? (int) $request->query->get('team_id') : null;
        $userId = $request->query->get('user_id') ? (int) $request->query->get('user_id') : null;

        $currentUser = $this->getUser();

        if (!$currentUser instanceof \App\Entity\User) {
            return $this->json([
                'success' => false,
                'error' => 'User not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userRoles = $currentUser->getRoles();
        $isAdmin = in_array('ROLE_ADMIN', $userRoles);
        $isManager = in_array('ROLE_MANAGER', $userRoles);

        if ($isManager && !$isAdmin) {
            $managedTeams = $currentUser->getManagedTeams();
            $managedTeamIds = array_map(fn ($team) => $team->getId(), $managedTeams->toArray());

            if (null === $teamId) {
                return $this->json([
                    'success' => false,
                    'error' => 'Managers must specify a team_id parameter',
                    'managed_team_ids' => $managedTeamIds,
                ], Response::HTTP_FORBIDDEN);
            }

            if (!in_array($teamId, $managedTeamIds)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Access denied. You can only view reports for teams you manage.',
                    'managed_team_ids' => $managedTeamIds,
                ], Response::HTTP_FORBIDDEN);
            }
        }

        if (!$isManager && !$isAdmin) {
            $userId = $currentUser->getId();
            $teamId = null;
        }

        try {
            $startDate = null;
            $endDate = null;

            if ($startDateStr) {
                $startDate = new \DateTimeImmutable($startDateStr);
            }

            if ($endDateStr) {
                $endDate = new \DateTimeImmutable($endDateStr.' 23:59:59');
            }

            $totalWorkingHours = $this->workingTimeRepository->calculateTotalWorkingHours(
                $startDate,
                $endDate,
                $userId,
                $teamId
            );

            $lateArrivals = $this->clockRepository->countLateArrivals(
                $startDate,
                $endDate,
                $userId,
                $teamId
            );

            $earlyDepartures = $this->clockRepository->countEarlyDepartures(
                $startDate,
                $endDate,
                $userId,
                $teamId
            );

            $presentDays = $this->workingTimeRepository->countPresentDays(
                $startDate,
                $endDate,
                $userId,
                $teamId
            );

            $incompleteDays = $this->workingTimeRepository->countIncompleteDays(
                $startDate,
                $endDate,
                $userId,
                $teamId
            );

            $totalExits = $this->clockRepository->countTotalExits(
                $startDate,
                $endDate,
                $userId,
                $teamId
            );

            $totalExitsExpected = $presentDays * 2;
            $lateArrivalsRate = $presentDays > 0
                ? round(($lateArrivals / $presentDays) * 100, 1)
                : 0;

            $absentDays = $this->calculateAbsentDays(
                $startDateStr,
                $endDateStr,
                $userId,
                $presentDays
            );

            $periodInfo = $this->getPeriodInfo($startDateStr, $endDateStr);

            $workSchedule = [
                'start_time' => '08:00',
                'end_time' => '17:00',
                'tolerance_late' => 30,
                'tolerance_early_departure' => 30,
                'standard_hours_per_day' => 8,
            ];

            $kpis = [
                'total_working_hours' => round($totalWorkingHours, 2),
                'late_arrivals_count' => $lateArrivals,
                'late_arrivals_rate' => $lateArrivalsRate,
                'early_departures_count' => $earlyDepartures,
                'present_days_count' => $presentDays,
                'absent_days_count' => $absentDays,
                'incomplete_days_count' => $incompleteDays,
                'total_exits_count' => $totalExits,
            ];

            return $this->json([
                'success' => true,
                'message' => 'Dashboard KPIs retrieved successfully',
                'data' => [
                    'filters' => [
                        'start_date' => $startDateStr,
                        'end_date' => $endDateStr,
                        'team_id' => $teamId,
                        'user_id' => $userId,
                    ],
                    'period' => $periodInfo,
                    'work_schedule' => $workSchedule,
                    'kpis' => $kpis,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while calculating KPIs: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/reports/teams', name: 'api_reports_teams', methods: ['GET'])]
    #[OA\Get(
        path: '/api/reports/teams',
        summary: 'Get list of teams available for the authenticated user',
        tags: ['Reports']
    )]
    #[OA\Response(
        response: 200,
        description: 'Teams retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'teams',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Development Team')
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'User not authenticated'
    )]
    public function getTeams(): JsonResponse
    {
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return $this->json([
                'success' => false,
                'error' => 'User not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userRoles = $currentUser->getRoles();
        $isAdmin = in_array('ROLE_ADMIN', $userRoles);
        $isManager = in_array('ROLE_MANAGER', $userRoles);

        $teams = [];

        if ($isAdmin) {
            $teamRepository = $this->entityManager->getRepository(\App\Entity\Team::class);
            $allTeams = $teamRepository->findAll();
            
            foreach ($allTeams as $team) {
                $teams[] = [
                    'id' => $team->getId(),
                    'name' => $team->getName()
                ];
            }
        } elseif ($isManager) {
            $managedTeams = $currentUser->getManagedTeams();
            
            foreach ($managedTeams as $team) {
                $teams[] = [
                    'id' => $team->getId(),
                    'name' => $team->getName()
                ];
            }
        } else {
            $userTeam = $currentUser->getTeam();
            if ($userTeam) {
                $teams[] = [
                    'id' => $userTeam->getId(),
                    'name' => $userTeam->getName()
                ];
            }
        }

        return $this->json([
            'success' => true,
            'teams' => $teams
        ]);
    }

    #[Route('/reports/team/{teamId}/employees', name: 'api_reports_team_employees', methods: ['GET'])]
    #[OA\Get(
        path: '/api/reports/team/{teamId}/employees',
        summary: 'Get employees of a specific team',
        tags: ['Reports']
    )]
    #[OA\Parameter(
        name: 'teamId',
        description: 'Team ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Employees retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'employees',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                            new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                            new OA\Property(property: 'fullName', type: 'string', example: 'John Doe')
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'User not authenticated'
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied'
    )]
    #[OA\Response(
        response: 404,
        description: 'Team not found'
    )]
    public function getTeamEmployees(int $teamId): JsonResponse
    {
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return $this->json([
                'success' => false,
                'error' => 'User not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userRoles = $currentUser->getRoles();
        $isAdmin = in_array('ROLE_ADMIN', $userRoles);
        $isManager = in_array('ROLE_MANAGER', $userRoles);

        if ($isManager && !$isAdmin) {
            $managedTeams = $currentUser->getManagedTeams();
            $managedTeamIds = array_map(fn($team) => $team->getId(), $managedTeams->toArray());

            if (!in_array($teamId, $managedTeamIds)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Access denied. You can only view employees for teams you manage.'
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $teamRepository = $this->entityManager->getRepository(\App\Entity\Team::class);
        $team = $teamRepository->find($teamId);

        if (!$team) {
            return $this->json([
                'success' => false,
                'error' => 'Team not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $employees = [];
        foreach ($team->getEmployees() as $employee) {
            $employees[] = [
                'id' => $employee->getId(),
                'firstName' => $employee->getFirstName(),
                'lastName' => $employee->getLastName(),
                'fullName' => $employee->getFirstName() . ' ' . $employee->getLastName()
            ];
        }

        return $this->json([
            'success' => true,
            'employees' => $employees
        ]);
    }

    private function calculateAbsentDays(
        ?string $startDate,
        ?string $endDate,
        ?int $userId,
        int $presentDays,
    ): int {
        if (!$startDate || !$endDate) {
            return 0;
        }

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        $workingDays = 0;
        $current = clone $start;

        while ($current <= $end) {
            $dayOfWeek = (int) $current->format('N');

            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                ++$workingDays;
            }

            $current->modify('+1 day');
        }

        if ($userId) {
            return max(0, $workingDays - $presentDays);
        }

        return 0;
    }

    /**
     * @return array{total_days: int|null, working_days: int|null, weekend_days: int|null}
     */
    private function getPeriodInfo(?string $startDate, ?string $endDate): array
    {
        if (!$startDate || !$endDate) {
            return [
                'total_days' => null,
                'working_days' => null,
                'weekend_days' => null,
            ];
        }

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = $start->diff($end);
        $totalDays = $interval->days + 1;

        $workingDays = 0;
        $weekendDays = 0;
        $current = clone $start;

        while ($current <= $end) {
            $dayOfWeek = (int) $current->format('N');
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                ++$workingDays;
            } else {
                ++$weekendDays;
            }
            $current->modify('+1 day');
        }

        return [
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'weekend_days' => $weekendDays,
        ];
    }
}