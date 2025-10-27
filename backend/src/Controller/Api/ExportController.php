<?php

namespace App\Controller\Api;

use App\Dto\Export\ExportFilterInputDto;
use App\Entity\User;
use App\Security\Voter\ExportVoter;
use App\Service\ExportService;
use DateTime;
use DateTimeInterface as DateTimeInterfaceAlias;
use Exception as ExceptionAlias;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/exports')]
#[OA\Tag(name: 'Exports')]
#[IsGranted('ROLE_MANAGER')]
final class ExportController extends AbstractController
{
    public function __construct(
        private readonly ExportService $exportService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/clocking/pdf', name: 'api_export_clocking_pdf', methods: ['GET'])]
    #[OA\Get(
        path: '/api/exports/clocking/pdf',
        description: 'Generates a comprehensive PDF report with clocks, working times, and KPIs. Managers can only export their team data, admins can export all data.',
        summary: 'Export clocking data to PDF',
        tags: ['Exports']
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
        description: 'Filter by team ID (required for managers, optional for admins)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Parameter(
        name: 'user_id',
        description: 'Filter by specific user ID (optional)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'PDF file generated successfully',
        content: new OA\MediaType(
            mediaType: 'application/pdf',
            schema: new OA\Schema(type: 'string', format: 'binary')
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
        description: 'Access denied - Managers must specify a team they manage'
    )]
    #[OA\Response(
        response: 500,
        description: 'Error generating PDF'
    )]
    public function exportPdf(
        #[MapQueryString] ExportFilterInputDto $filters
    ): Response
    {
        error_log('DEBUG DTO: start_date=' . ($filters->start_date ?? 'null') .
                  ', end_date=' . ($filters->end_date ?? 'null') .
                  ', team_id=' . ($filters->team_id ?? 'null'));

        $errors = $this->validator->validate($filters);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json([
                'success' => false,
                'errors' => $errorMessages,
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->denyAccessUnlessGranted(ExportVoter::EXPORT_CLOCKING);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        [$teamId, $userId, $error] = $this->validateAndAdjustParameters(
            $currentUser,
            $filters->team_id,
            $filters->user_id
        );

        if ($error) {
            return $this->json($error['data'], $error['status']);
        }

        try {
            $startDate = $filters->getStartDateAsDateTime();
            $endDate = $filters->getEndDateAsDateTime();

            if ($endDate) {
                $endDate = (clone $endDate)->setTime(23, 59, 59);
            }

            $pdfContent = $this->exportService->generatePdfReport(
                $startDate,
                $endDate,
                $userId,
                $teamId
            );

            $filename = $this->generateFilename('pdf', $startDate, $endDate);

            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

            return $response;

        } catch (ExceptionAlias $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error generating PDF: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/clocking/xlsx', name: 'api_export_clocking_xlsx', methods: ['GET'])]
    #[OA\Get(
        path: '/api/exports/clocking/xlsx',
        description: 'Generates a comprehensive Excel report with multiple sheets (KPIs, Working Times, Clocks). Managers can only export their team data, admins can export all data.',
        summary: 'Export clocking data to XLSX',
        tags: ['Exports']
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
        description: 'Filter by team ID (required for managers, optional for admins)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Parameter(
        name: 'user_id',
        description: 'Filter by specific user ID (optional)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'XLSX file generated successfully',
        content: new OA\MediaType(
            mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            schema: new OA\Schema(type: 'string', format: 'binary')
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
        description: 'Access denied - Managers must specify a team they manage'
    )]
    #[OA\Response(
        response: 500,
        description: 'Error generating XLSX'
    )]
    public function exportXlsx(
        #[MapQueryString] ExportFilterInputDto $filters
    ): Response
    {
        $errors = $this->validator->validate($filters);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json([
                'success' => false,
                'errors' => $errorMessages,
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->denyAccessUnlessGranted(ExportVoter::EXPORT_CLOCKING);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        [$teamId, $userId, $error] = $this->validateAndAdjustParameters(
            $currentUser,
            $filters->team_id,
            $filters->user_id
        );

        if ($error) {
            return $this->json($error['data'], $error['status']);
        }

        try {
            $startDate = $filters->getStartDateAsDateTime();
            $endDate = $filters->getEndDateAsDateTime();

            if ($endDate) {
                $endDate = (clone $endDate)->setTime(23, 59, 59);
            }

            $xlsxContent = $this->exportService->generateXlsxReport(
                $startDate,
                $endDate,
                $userId,
                $teamId
            );

            $filename = $this->generateFilename('xlsx', $startDate, $endDate);

            $response = new Response($xlsxContent);
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

            return $response;

        } catch (ExceptionAlias $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error generating XLSX: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validate and adjust export parameters based on user permissions
     * Uses ExportVoter to check team access
     *
     * @return array [teamId, userId, error|null]
     */
    private function validateAndAdjustParameters(User $currentUser, ?int $teamId, ?int $userId): array
    {
        $userRoles = $currentUser->getRoles();
        $isAdmin = in_array('ROLE_ADMIN', $userRoles);
        $isManager = in_array('ROLE_MANAGER', $userRoles);

        if ($isManager && !$isAdmin) {
            $managedTeams = $currentUser->getManagedTeams();
            $managedTeamIds = array_map(fn ($team) => $team->getId(), $managedTeams->toArray());

            if (null === $teamId) {
                return [
                    null,
                    null,
                    [
                        'data' => [
                            'success' => false,
                            'error' => 'Managers must specify a team_id parameter',
                            'managed_team_ids' => $managedTeamIds,
                        ],
                        'status' => Response::HTTP_FORBIDDEN,
                    ]
                ];
            }

            if (!$this->isGranted(ExportVoter::EXPORT_CLOCKING, $teamId)) {
                return [
                    null,
                    null,
                    [
                        'data' => [
                            'success' => false,
                            'error' => 'Access denied. You can only export data for teams you manage.',
                            'managed_team_ids' => $managedTeamIds,
                        ],
                        'status' => Response::HTTP_FORBIDDEN,
                    ]
                ];
            }
        }


        return [$teamId, $userId, null];
    }

    /**
     * Generate standardized filename for exports
     */
    private function generateFilename(string $extension, DateTimeInterfaceAlias $startDate, DateTimeInterfaceAlias $endDate): string
    {
        $timestamp = (new DateTime())->format('Ymd_His');
        $dateRange = '';
        $startDate = $startDate->format('Ymd');
        $endDate = $endDate->format('Ymd');

        if ($startDate && $endDate) {
            $dateRange = '_' . $startDate . '-' . $endDate;
        } elseif ($startDate) {
            $dateRange = '_from_' . $startDate;
        } elseif ($endDate) {
            $dateRange = '_until_' . $endDate;
        }

        return "clocking_report.$dateRange._$timestamp.$extension";
    }
}
