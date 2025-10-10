<?php

namespace App\Controller\Api;

use App\Repository\ClockRepository;
use App\Repository\WorkingTimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Reports')]
class ReportsController extends AbstractController
{
    public function __construct(
        private readonly ClockRepository $clockRepository,
        private readonly WorkingTimeRepository $workingTimeRepository
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
                                new OA\Property(property: 'user_id', type: 'integer', nullable: true)
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'period',
                            properties: [
                                new OA\Property(property: 'total_days', type: 'integer', example: 365),
                                new OA\Property(property: 'working_days', type: 'integer', example: 261),
                                new OA\Property(property: 'weekend_days', type: 'integer', example: 104)
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
                                new OA\Property(property: 'standard_hours_per_day', type: 'integer', example: 8)
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'kpis',
                            properties: [
                                new OA\Property(property: 'total_working_hours', type: 'number', format: 'float', example: 1848.5),
                                new OA\Property(property: 'late_arrivals_count', type: 'integer', example: 12),
                                new OA\Property(property: 'early_departures_count', type: 'integer', example: 5),
                                new OA\Property(property: 'present_days_count', type: 'integer', example: 235),
                                new OA\Property(property: 'absent_days_count', type: 'integer', example: 26),
                                new OA\Property(property: 'incomplete_days_count', type: 'integer', example: 3),
                                new OA\Property(property: 'total_exits_count', type: 'integer', example: 470)
                            ],
                            type: 'object'
                        )
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid date format'
    )]
    #[OA\Response(
        response: 500,
        description: 'Error calculating KPIs'
    )]
    public function getReports(Request $request): JsonResponse
    {
        $startDateStr = $request->query->get('start_date');
        $endDateStr = $request->query->get('end_date');
        $teamId = $request->query->get('team_id');
        $userId = $request->query->get('user_id') ? (int)$request->query->get('user_id') : null;

        try {
            $startDate = null;
            $endDate = null;

            if ($startDateStr) {
                $startDate = new \DateTimeImmutable($startDateStr);
            }

            if ($endDateStr) {
                $endDate = new \DateTimeImmutable($endDateStr . ' 23:59:59');
            }

            $totalWorkingHours = $this->workingTimeRepository->calculateTotalWorkingHours(
                $startDate, 
                $endDate, 
                $userId
            );

            $lateArrivals = $this->clockRepository->countLateArrivals(
                $startDate, 
                $endDate, 
                $userId
            );

            $earlyDepartures = $this->clockRepository->countEarlyDepartures(
                $startDate, 
                $endDate, 
                $userId
            );

            $presentDays = $this->workingTimeRepository->countPresentDays(
                $startDate, 
                $endDate, 
                $userId
            );

            $absentDays = $this->calculateAbsentDays(
                $startDateStr, 
                $endDateStr, 
                $userId, 
                $presentDays
            );

            $incompleteDays = $this->clockRepository->countIncompleteDays(
                $startDate, 
                $endDate, 
                $userId
            );

            $totalExits = $this->clockRepository->countTotalExits(
                $startDate, 
                $endDate, 
                $userId
            );

            $periodInfo = $this->getPeriodInfo($startDateStr, $endDateStr);

            return $this->json([
                'success' => true,
                'message' => 'Dashboard KPIs retrieved successfully',
                'data' => [
                    'filters' => [
                        'start_date' => $startDateStr,
                        'end_date' => $endDateStr,
                        'team_id' => $teamId ? (int)$teamId : null,
                        'user_id' => $userId
                    ],
                    'period' => $periodInfo,
                    'work_schedule' => [
                        'start_time' => '08:00',
                        'end_time' => '17:00',
                        'tolerance_late' => 30,
                        'tolerance_early_departure' => 30,
                        'standard_hours_per_day' => 8
                    ],
                    'kpis' => [
                        'total_working_hours' => $totalWorkingHours,
                        'late_arrivals_count' => $lateArrivals,
                        'early_departures_count' => $earlyDepartures,
                        'present_days_count' => $presentDays,
                        'absent_days_count' => $absentDays,
                        'incomplete_days_count' => $incompleteDays,
                        'total_exits_count' => $totalExits
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error calculating KPIs: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function calculateAbsentDays(
        ?string $startDate, 
        ?string $endDate, 
        ?int $userId, 
        int $presentDays
    ): int {
        if (!$startDate || !$endDate) {
            return 0;
        }

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        $workingDays = 0;
        $current = clone $start;
        
        while ($current <= $end) {
            $dayOfWeek = (int)$current->format('N');
            
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $workingDays++;
            }
            
            $current->modify('+1 day');
        }

        if ($userId) {
            return max(0, $workingDays - $presentDays);
        }

        return 0;
    }

    private function getPeriodInfo(?string $startDate, ?string $endDate): array
    {
        if (!$startDate || !$endDate) {
            return [
                'total_days' => null,
                'working_days' => null,
                'weekend_days' => null
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
            $dayOfWeek = (int)$current->format('N');
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $workingDays++;
            } else {
                $weekendDays++;
            }
            $current->modify('+1 day');
        }

        return [
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'weekend_days' => $weekendDays
        ];
    }
}
