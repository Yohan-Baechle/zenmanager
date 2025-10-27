<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ClockRepository;
use App\Repository\WorkingTimeRepository;
use DateTime as DateTimeAlias;
use DateTimeInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Doctrine\ORM\EntityManagerInterface;

readonly class ExportService
{
    public function __construct(
        private ClockRepository        $clockRepository,
        private WorkingTimeRepository  $workingTimeRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Generate PDF report organized by employee with weekly breakdown
     */
    public function generatePdfReport(
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate,
        ?int               $userId = null,
        ?int               $teamId = null
    ): string {
        $employeesData = $this->collectEmployeesWeeklyData($startDate, $endDate, $userId, $teamId);

        $html = $this->generateHtmlForPdf($employeesData, $startDate, $endDate);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Generate XLSX report organized by employee with weekly breakdown
     */
    public function generateXlsxReport(
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate,
        ?int               $userId = null,
        ?int               $teamId = null
    ): string {
        $spreadsheet = new Spreadsheet();

        $employeesData = $this->collectEmployeesWeeklyData($startDate, $endDate, $userId, $teamId);

        $isFirstSheet = true;
        foreach ($employeesData as $employeeData) {
            if ($isFirstSheet) {
                $sheet = $spreadsheet->getActiveSheet();
                $isFirstSheet = false;
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            $this->createEmployeeWeeklySheet($sheet, $employeeData);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    /**
     * Collect data grouped by employee and organized by week
     */
    private function collectEmployeesWeeklyData(
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate,
        ?int               $userId,
        ?int               $teamId
    ): array {
        $usersQuery = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        if ($userId) {
            $usersQuery->where('u.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($teamId) {
            $usersQuery->andWhere('u.team = :teamId')
                ->setParameter('teamId', $teamId);
        }

        $usersQuery->orderBy('u.username', 'ASC');
        $users = $usersQuery->getQuery()->getResult();

        $employeesData = [];

        foreach ($users as $user) {
            $workingTimes = $this->collectWorkingTimesData($startDate, $endDate, $user->getId(), null);

            $clocks = $this->collectClocksData($startDate, $endDate, $user->getId(), null);

            $weeklyData = $this->groupByWeeks($workingTimes, $clocks, $startDate, $endDate);

            $totalHours = 0;
            foreach ($workingTimes as $wt) {
                $totalHours += ($wt->getEndTime()->getTimestamp() - $wt->getStartTime()->getTimestamp()) / 3600;
            }

            $anomalies = $this->detectAnomalies($workingTimes, $clocks, $user);

            $validWorkingTimes = array_filter($workingTimes, function($wt) use ($anomalies) {
                $dateKey = $wt->getStartTime()->format('Y-m-d');
                foreach ($anomalies as $anomaly) {
                    if ($anomaly['date'] === $dateKey) {
                        return false;
                    }
                }
                return true;
            });

            $validTotalHours = 0;
            foreach ($validWorkingTimes as $wt) {
                $validTotalHours += ($wt->getEndTime()->getTimestamp() - $wt->getStartTime()->getTimestamp()) / 3600;
            }

            $kpis = [
                'total_working_hours' => $validTotalHours,
                'late_arrivals' => $this->clockRepository->countLateArrivals($startDate, $endDate, $user->getId(), null),
                'early_departures' => $this->clockRepository->countEarlyDepartures($startDate, $endDate, $user->getId(), null),
                'present_days' => count($validWorkingTimes),
                'incomplete_days' => $this->clockRepository->countIncompleteDays($startDate, $endDate, $user->getId(), null),
                'total_exits' => $this->clockRepository->countTotalExits($startDate, $endDate, $user->getId(), null),
                'anomalies_count' => count($anomalies),
            ];

            $employeesData[] = [
                'user' => $user,
                'weeks' => $weeklyData,
                'total_hours' => $totalHours,
                'valid_total_hours' => $validTotalHours,
                'total_days' => count($workingTimes),
                'kpis' => $kpis,
                'anomalies' => $anomalies,
            ];
        }

        return $employeesData;
    }

    /**
     * Group working times and clocks by ISO weeks
     */
    private function groupByWeeks(
        array              $workingTimes,
        array              $clocks,
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate
    ): array {
        $weeks = [];

        foreach ($workingTimes as $wt) {
            $date = $wt->getStartTime();
            $weekKey = $date->format('o-W');
            $dayKey = $date->format('N');

            if (!isset($weeks[$weekKey])) {
                $weeks[$weekKey] = $this->initializeWeek($date);
            }

            $weeks[$weekKey]['days'][$dayKey]['working_times'][] = $wt;

            $duration = ($wt->getEndTime()->getTimestamp() - $wt->getStartTime()->getTimestamp()) / 3600;
            $weeks[$weekKey]['days'][$dayKey]['total_hours'] += $duration;

            if (!$weeks[$weekKey]['days'][$dayKey]['first_in']) {
                $weeks[$weekKey]['days'][$dayKey]['first_in'] = $wt->getStartTime();
            }
            $weeks[$weekKey]['days'][$dayKey]['last_out'] = $wt->getEndTime();
        }

        foreach ($clocks as $clock) {
            $date = $clock->getTime();
            $weekKey = $date->format('o-W');
            $dayKey = $date->format('N');

            if (!isset($weeks[$weekKey])) {
                $weeks[$weekKey] = $this->initializeWeek($date);
            }

            $weeks[$weekKey]['days'][$dayKey]['clocks'][] = $clock;
        }

        ksort($weeks);

        return $weeks;
    }

    /**
     * Initialize a week structure
     */
    private function initializeWeek(DateTimeInterface $date): array {
        $monday = (clone $date)->modify('monday this week');

        $week = [
            'week_number' => $monday->format('W'),
            'year' => $monday->format('o'),
            'monday_date' => $monday,
            'days' => [],
        ];

        for ($i = 1; $i <= 7; $i++) {
            $dayDate = (clone $monday)->modify('+' . ($i - 1) . ' days');
            $week['days'][$i] = [
                'date' => $dayDate,
                'day_name' => $this->getDayNameFr($i),
                'working_times' => [],
                'clocks' => [],
                'total_hours' => 0,
                'first_in' => null,
                'last_out' => null,
            ];
        }

        return $week;
    }

    /**
     * Get French day name
     */
    private function getDayNameFr(int $dayNumber): string {
        $days = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
        ];
        return $days[$dayNumber];
    }

    /**
     * Collect working times data
     */
    private function collectWorkingTimesData(
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate,
        ?int               $userId,
        ?int               $teamId
    ): array {
        $qb = $this->workingTimeRepository->createQueryBuilder('wt');

        if ($startDate) {
            $qb->andWhere('wt.startTime >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('wt.endTime <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        if ($userId) {
            $qb->andWhere('wt.owner = :userId')
               ->setParameter('userId', $userId);
        }

        if ($teamId) {
            $qb->join('wt.owner', 'u')
               ->andWhere('u.team = :teamId')
               ->setParameter('teamId', $teamId);
        }

        $qb->orderBy('wt.startTime', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Collect clocks data
     */
    private function collectClocksData(
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate,
        ?int               $userId,
        ?int               $teamId
    ): array {
        $qb = $this->clockRepository->createQueryBuilder('c');

        if ($startDate) {
            $qb->andWhere('c.time >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('c.time <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        if ($userId) {
            $qb->andWhere('c.owner = :userId')
               ->setParameter('userId', $userId);
        }

        if ($teamId) {
            $qb->join('c.owner', 'u')
               ->andWhere('u.team = :teamId')
               ->setParameter('teamId', $teamId);
        }

        $qb->orderBy('c.time', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Generate HTML for PDF with employee weekly breakdown
     */
    private function generateHtmlForPdf(
        array              $employeesData,
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate
    ): string {
        $periodStart = $startDate ? $startDate->format('d/m/Y') : 'N/A';
        $periodEnd = $endDate ? $endDate->format('d/m/Y') : 'N/A';
        $generatedAt = (new DateTimeAlias())->format('d/m/Y H:i:s');

        $html = "
        <!DOCTYPE html>
        <html lang=fr-FR>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; margin: 10px; }
                h1 { color: #333; text-align: center; font-size: 16pt; margin-bottom: 5px; }
                h2 { color: #2563eb; font-size: 13pt; margin-top: 25px; margin-bottom: 10px; border-bottom: 2px solid #2563eb; padding-bottom: 5px; }
                h3 { color: #555; font-size: 11pt; margin-top: 15px; margin-bottom: 8px; background-color: #f3f4f6; padding: 5px 10px; }
                .header { text-align: center; margin-bottom: 15px; }
                .header p { margin: 2px 0; color: #666; font-size: 9pt; }
                .employee-summary { background-color: #eff6ff; padding: 8px; margin-bottom: 10px; border-left: 4px solid #2563eb; }
                .employee-summary strong { color: #2563eb; }
                table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 8pt; }
                th { background-color: #2563eb; color: white; padding: 6px 4px; text-align: center; font-size: 8pt; }
                td { padding: 5px 4px; border: 1px solid #ddd; text-align: center; }
                .day-header { background-color: #f3f4f6; font-weight: bold; font-size: 8pt; }
                .weekend { background-color: #fef3c7; }
                .anomaly { background-color: #fee2e2; border-left: 3px solid #dc2626; }
                .anomaly-warning { color: #dc2626; font-weight: bold; font-size: 9pt; }
                .hours { color: #2563eb; font-weight: bold; }
                .time { color: #059669; font-size: 8pt; }
                .absent { color: #dc2626; }
                .page-break { page-break-after: always; }
                .footer { text-align: center; margin-top: 20px; font-size: 7pt; color: #999; }
                .kpi-section { margin-top: 20px; padding: 10px; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 5px; }
                .kpi-section h3 { margin-top: 0; color: #2563eb; font-size: 11pt; }
                .kpi-grid { display: table; width: 100%; margin-top: 10px; }
                .kpi-item { display: table-cell; width: 16.66%; text-align: center; padding: 8px; }
                .kpi-value { font-size: 16pt; font-weight: bold; color: #2563eb; }
                .kpi-label { font-size: 7pt; color: #666; margin-top: 3px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>📊 Rapport de Pointages par Salarié</h1>
                <p><strong>Période :</strong> $periodStart - $periodEnd</p>
                <p><strong>Généré le :</strong> $generatedAt</p>
            </div>";

        $employeeCount = 0;
        foreach ($employeesData as $employeeData) {
            $user = $employeeData['user'];
            $weeks = $employeeData['weeks'];
            $totalHours = $employeeData['total_hours'];
            $totalDays = $employeeData['total_days'];
            $kpis = $employeeData['kpis'];
            $anomalies = $employeeData['anomalies'];

            $anomaliesByDate = [];
            foreach ($anomalies as $anomaly) {
                $anomaliesByDate[$anomaly['date']][] = $anomaly;
            }

            $employeeCount++;
            if ($employeeCount > 1) {
                $html .= "<div class='page-break'></div>";
            }

            $fullName = $this->getUserFullName($user);
            $html .= "
            <h2>👤 $fullName</h2>
            <div class='employee-summary'>
                <strong>Email:</strong> {$user->getEmail()} |
                <strong>Heures totales:</strong> <span class='hours'>" . number_format($totalHours, 2) . "h</span> |
                <strong>Jours travaillés:</strong> {$totalDays}
            </div>";

            foreach ($weeks as $weekKey => $week) {
                $weekNumber = $week['week_number'];
                $year = $week['year'];
                $mondayDate = $week['monday_date']->format('d/m/Y');
                $sundayDate = (clone $week['monday_date'])->modify('+6 days')->format('d/m/Y');

                $weekTotalHours = 0;
                foreach ($week['days'] as $day) {
                    $weekTotalHours += $day['total_hours'];
                }

                $html .= "
                <h3>📅 Semaine $weekNumber ($year) : $mondayDate - $sundayDate | Total: <span class='hours'>" . number_format($weekTotalHours, 2) . "h</span></h3>
                <table>
                    <thead>
                        <tr>
                            <th style='width: 12%;'>Jour</th>
                            <th style='width: 10%;'>Date</th>
                            <th style='width: 15%;'>Arrivée</th>
                            <th style='width: 15%;'>Départ</th>
                            <th style='width: 12%;'>Heures</th>
                            <th style='width: 36%;'>Détails</th>
                        </tr>
                    </thead>
                    <tbody>";

                foreach ($week['days'] as $dayNum => $day) {
                    $isWeekend = ($dayNum == 6 || $dayNum == 7);
                    $dayFullDate = $day['date']->format('Y-m-d');
                    $hasAnomaly = isset($anomaliesByDate[$dayFullDate]);

                    $rowClass = $hasAnomaly ? 'anomaly' : ($isWeekend ? 'weekend' : '');

                    $dayName = $day['day_name'];
                    $date = $day['date']->format('d/m');
                    $firstIn = $day['first_in'] ? $day['first_in']->format('H:i') : '-';
                    $lastOut = $day['last_out'] ? $day['last_out']->format('H:i') : '-';
                    $hours = $day['total_hours'] > 0 ? number_format($day['total_hours'], 2) . 'h' : '-';

                    $details = '';
                    if (count($day['working_times']) > 0) {
                        $wtDetails = [];
                        foreach ($day['working_times'] as $wt) {
                            $wtDetails[] = $wt->getStartTime()->format('H:i') . '-' . $wt->getEndTime()->format('H:i');
                        }
                        $details = implode(', ', $wtDetails);

                        if ($hasAnomaly) {
                            $anomalyLabels = array_map(fn($a) => $a['type_label'], $anomaliesByDate[$dayFullDate]);
                            $details .= ' <span class="anomaly-warning">⚠ ' . implode(', ', $anomalyLabels) . '</span>';
                        }
                    } else {
                        $details = $isWeekend ? 'Weekend' : '<span class="absent">Absent</span>';
                    }

                    $html .= "
                        <tr class='$rowClass'>
                            <td class='day-header'>$dayName</td>
                            <td>$date</td>
                            <td class='time'>$firstIn</td>
                            <td class='time'>$lastOut</td>
                            <td class='hours'>$hours</td>
                            <td style='text-align: left; font-size: 7pt;'>$details</td>
                        </tr>";
                }

                $html .= "
                    </tbody>
                </table>";
            }

            $html .= "
            <div class='kpi-section'>
                <h3>📈 Indicateurs de Performance (KPIs)</h3>
                <div class='kpi-grid'>
                    <div class='kpi-item'>
                        <div class='kpi-value'>" . number_format($kpis['total_working_hours'], 1) . "h</div>
                        <div class='kpi-label'>Heures Travaillées</div>
                    </div>
                    <div class='kpi-item'>
                        <div class='kpi-value'>{$kpis['present_days']}</div>
                        <div class='kpi-label'>Jours Présents</div>
                    </div>
                    <div class='kpi-item'>
                        <div class='kpi-value'>{$kpis['late_arrivals']}</div>
                        <div class='kpi-label'>Retards</div>
                    </div>
                    <div class='kpi-item'>
                        <div class='kpi-value'>{$kpis['early_departures']}</div>
                        <div class='kpi-label'>Départs Anticipés</div>
                    </div>
                    <div class='kpi-item'>
                        <div class='kpi-value'>{$kpis['incomplete_days']}</div>
                        <div class='kpi-label'>Jours Incomplets</div>
                    </div>
                    <div class='kpi-item'>
                        <div class='kpi-value'>{$kpis['total_exits']}</div>
                        <div class='kpi-label'>Sorties Totales</div>
                    </div>
                </div>
            </div>";

            if (count($anomalies) > 0) {
                $html .= "
            <div class='kpi-section' style='margin-top: 15px; border-color: #fca5a5;'>
                <h3 style='color: #dc2626;'>⚠️ Anomalies Détectées ({$kpis['anomalies_count']})</h3>
                <p style='font-size: 8pt; color: #666; margin: 5px 0;'><em>Les jours anormaux sont exclus des totaux KPI et marqués en rouge dans les calendriers.</em></p>
                <table style='margin-top: 10px;'>
                    <thead>
                        <tr>
                            <th style='width: 15%;'>Date</th>
                            <th style='width: 25%;'>Type</th>
                            <th style='width: 45%;'>Description</th>
                            <th style='width: 15%;'>Gravité</th>
                        </tr>
                    </thead>
                    <tbody>";

                foreach ($anomalies as $anomaly) {
                    $dateFormatted = date('d/m/Y', strtotime($anomaly['date']));
                    $severityLabel = $anomaly['severity'] === 'high' ? '🔴 Élevée' : '🟠 Moyenne';
                    $html .= "
                        <tr style='background-color: " . ($anomaly['severity'] === 'high' ? '#fee2e2' : '#fed7aa') . ";'>
                            <td>$dateFormatted</td>
                            <td style='font-weight: bold;'>{$anomaly['type_label']}</td>
                            <td style='text-align: left;'>{$anomaly['description']}</td>
                            <td>$severityLabel</td>
                        </tr>";
                }

                $html .= "
                    </tbody>
                </table>
            </div>";
            }
        }

        $html .= "
            <div class='footer'>
                <p>Time Manager - Rapport généré automatiquement</p>
            </div>
        </body>
        </html>";

        return $html;
    }

    /**
     * Create employee weekly sheet for XLSX
     */
    private function createEmployeeWeeklySheet($sheet, array $employeeData): void {
        $user = $employeeData['user'];
        $weeks = $employeeData['weeks'];
        $totalHours = $employeeData['total_hours'];
        $kpis = $employeeData['kpis'];
        $anomalies = $employeeData['anomalies'];

        $anomaliesByDate = [];
        foreach ($anomalies as $anomaly) {
            $anomaliesByDate[$anomaly['date']][] = $anomaly;
        }

        $fullName = $this->getUserFullName($user);
        $sheetTitle = substr($fullName, 0, 31);
        $sheet->setTitle($sheetTitle);

        $sheet->setCellValue('A1', $fullName);
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563eb');
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        $sheet->setCellValue('A2', 'Email: ' . $user->getEmail());
        $sheet->setCellValue('D2', 'Total heures: ' . number_format($totalHours, 2) . 'h');

        $row = 4;

        foreach ($weeks as $weekKey => $week) {
            $weekNumber = $week['week_number'];
            $year = $week['year'];
            $mondayDate = $week['monday_date']->format('d/m/Y');

            $sheet->setCellValue('A' . $row, "Semaine $weekNumber ($year) - $mondayDate");
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('f3f4f6');
            $row++;

            $sheet->setCellValue('A' . $row, 'Jour');
            $sheet->setCellValue('B' . $row, 'Date');
            $sheet->setCellValue('C' . $row, 'Arrivée');
            $sheet->setCellValue('D' . $row, 'Départ');
            $sheet->setCellValue('E' . $row, 'Heures');
            $sheet->setCellValue('F' . $row, 'Détails');
            $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563eb');
            $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $row++;

            foreach ($week['days'] as $dayNum => $day) {
                $isWeekend = ($dayNum == 6 || $dayNum == 7);
                $dayFullDate = $day['date']->format('Y-m-d');
                $hasAnomaly = isset($anomaliesByDate[$dayFullDate]);

                $sheet->setCellValue('A' . $row, $day['day_name']);
                $sheet->setCellValue('B' . $row, $day['date']->format('d/m/Y'));
                $sheet->setCellValue('C' . $row, $day['first_in'] ? $day['first_in']->format('H:i') : '-');
                $sheet->setCellValue('D' . $row, $day['last_out'] ? $day['last_out']->format('H:i') : '-');
                $sheet->setCellValue('E' . $row, $day['total_hours'] > 0 ? number_format($day['total_hours'], 2) : '-');

                $details = '';
                if (count($day['working_times']) > 0) {
                    $wtDetails = [];
                    foreach ($day['working_times'] as $wt) {
                        $wtDetails[] = $wt->getStartTime()->format('H:i') . '-' . $wt->getEndTime()->format('H:i');
                    }
                    $details = implode(', ', $wtDetails);

                    if ($hasAnomaly) {
                        $anomalyLabels = array_map(fn($a) => $a['type_label'], $anomaliesByDate[$dayFullDate]);
                        $details .= ' ⚠ ' . implode(', ', $anomalyLabels);
                    }
                } else {
                    $details = $isWeekend ? 'Weekend' : 'Absent';
                }
                $sheet->setCellValue('F' . $row, $details);

                if ($hasAnomaly) {
                    $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fee2e2');
                    $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->getColor()->setRGB('dc2626');
                } elseif ($isWeekend) {
                    $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fef3c7');
                }

                $row++;
            }

            $row++;
        }

        $row++;
        $sheet->setCellValue('A' . $row, 'Indicateurs de Performance (KPIs)');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('f9fafb');
        $sheet->getStyle('A' . $row)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $row++;

        $sheet->setCellValue('A' . $row, 'Heures Travaillées');
        $sheet->setCellValue('B' . $row, 'Jours Présents');
        $sheet->setCellValue('C' . $row, 'Retards');
        $sheet->setCellValue('D' . $row, 'Départs Anticipés');
        $sheet->setCellValue('E' . $row, 'Jours Incomplets');
        $sheet->setCellValue('F' . $row, 'Sorties Totales');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563eb');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->setCellValue('A' . $row, number_format($kpis['total_working_hours'], 2) . ' h');
        $sheet->setCellValue('B' . $row, $kpis['present_days']);
        $sheet->setCellValue('C' . $row, $kpis['late_arrivals']);
        $sheet->setCellValue('D' . $row, $kpis['early_departures']);
        $sheet->setCellValue('E' . $row, $kpis['incomplete_days']);
        $sheet->setCellValue('F' . $row, $kpis['total_exits']);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('eff6ff');
        $row++;

        if (count($anomalies) > 0) {
            $row++;
            $sheet->setCellValue('A' . $row, '⚠️ Anomalies Détectées (' . count($anomalies) . ')');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('dc2626');
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fee2e2');
            $sheet->getStyle('A' . $row)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
            $row++;

            $sheet->setCellValue('A' . $row, 'Les jours anormaux sont exclus des totaux KPI et marqués en rouge dans les calendriers.');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('666666');
            $row++;

            $sheet->setCellValue('A' . $row, 'Date');
            $sheet->setCellValue('B' . $row, 'Type');
            $sheet->setCellValue('C' . $row, 'Description');
            $sheet->setCellValue('D' . $row, 'Gravité');
            $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('dc2626');
            $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;

            foreach ($anomalies as $anomaly) {
                $dateFormatted = date('d/m/Y', strtotime($anomaly['date']));
                $severityLabel = $anomaly['severity'] === 'high' ? '🔴 Élevée' : '🟠 Moyenne';
                $bgColor = $anomaly['severity'] === 'high' ? 'fee2e2' : 'fed7aa';

                $sheet->setCellValue('A' . $row, $dateFormatted);
                $sheet->setCellValue('B' . $row, $anomaly['type_label']);
                $sheet->setCellValue('C' . $row, $anomaly['description']);
                $sheet->setCellValue('D' . $row, $severityLabel);

                $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgColor);
                $sheet->getStyle('B' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row++;
            }
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A4:F' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    /**
     * Get user full name (FirstName LASTNAME)
     */
    private function getUserFullName(User $user): string {
        return trim($user->getFirstName() . ' ' . strtoupper($user->getLastName()));
    }

    /**
     * Detect anomalies in working times and clocks
     */
    private function detectAnomalies(array $workingTimes, array $clocks, User $user): array {
        $anomalies = [];
        $fullName = $this->getUserFullName($user);

        $wtByDate = [];
        foreach ($workingTimes as $wt) {
            $dateKey = $wt->getStartTime()->format('Y-m-d');
            if (!isset($wtByDate[$dateKey])) {
                $wtByDate[$dateKey] = [];
            }
            $wtByDate[$dateKey][] = $wt;
        }

        foreach ($wtByDate as $date => $dayWorkingTimes) {
            $totalDayHours = 0;
            $periods = [];

            foreach ($dayWorkingTimes as $wt) {
                $duration = ($wt->getEndTime()->getTimestamp() - $wt->getStartTime()->getTimestamp()) / 3600;
                $totalDayHours += $duration;
                $periods[] = [
                    'start' => $wt->getStartTime(),
                    'end' => $wt->getEndTime(),
                    'duration' => $duration,
                ];
            }

            if ($totalDayHours > 12) {
                $anomalies[] = [
                    'user' => $fullName,
                    'date' => $date,
                    'type' => 'DURATION_EXCESSIVE',
                    'type_label' => 'Durée excessive',
                    'severity' => 'high',
                    'description' => sprintf('Durée totale: %.2fh (>12h)', $totalDayHours),
                    'value' => $totalDayHours,
                ];
            }

            if ($totalDayHours > 0 && $totalDayHours < 2) {
                $anomalies[] = [
                    'user' => $fullName,
                    'date' => $date,
                    'type' => 'DURATION_TOO_SHORT',
                    'type_label' => 'Durée trop courte',
                    'severity' => 'medium',
                    'description' => sprintf('Durée totale: %.2fh (<2h)', $totalDayHours),
                    'value' => $totalDayHours,
                ];
            }

            for ($i = 0; $i < count($periods); $i++) {
                for ($j = $i + 1; $j < count($periods); $j++) {
                    $period1 = $periods[$i];
                    $period2 = $periods[$j];

                    if ($period1['start'] < $period2['end'] && $period2['start'] < $period1['end']) {
                        $anomalies[] = [
                            'user' => $fullName,
                            'date' => $date,
                            'type' => 'OVERLAPPING_PERIODS',
                            'type_label' => 'Périodes chevauchées',
                            'severity' => 'high',
                            'description' => sprintf(
                                'Chevauchement: %s-%s et %s-%s',
                                $period1['start']->format('H:i'),
                                $period1['end']->format('H:i'),
                                $period2['start']->format('H:i'),
                                $period2['end']->format('H:i')
                            ),
                            'value' => null,
                        ];
                    }
                }
            }
        }

        $clocksByDate = [];
        foreach ($clocks as $clock) {
            $dateKey = $clock->getTime()->format('Y-m-d');
            if (!isset($clocksByDate[$dateKey])) {
                $clocksByDate[$dateKey] = ['in' => 0, 'out' => 0];
            }
            if ($clock->isStatus()) {
                $clocksByDate[$dateKey]['in']++;
            } else {
                $clocksByDate[$dateKey]['out']++;
            }
        }

        foreach ($clocksByDate as $date => $counts) {
            if ($counts['in'] !== $counts['out']) {
                $anomalies[] = [
                    'user' => $fullName,
                    'date' => $date,
                    'type' => 'MISSING_CLOCKS',
                    'type_label' => 'Pointages manquants',
                    'severity' => 'high',
                    'description' => sprintf('%d IN, %d OUT', $counts['in'], $counts['out']),
                    'value' => abs($counts['in'] - $counts['out']),
                ];
            }
        }

        usort($anomalies, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $anomalies;
    }
}
