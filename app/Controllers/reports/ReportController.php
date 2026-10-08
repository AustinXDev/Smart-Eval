<?php

namespace App\Controllers\reports;

use App\Services\ReportServices\ReportService;
use Throwable;

final class ReportController
{
    public function __construct(
        private ReportService $service
    ) {
    }

    /**
     * Download faculty evaluation summary report
     */
    public function downloadEvaluationSummary(): void
    {

        try {

            $department = trim(
                $_GET['dept'] ?? ''
            );

            $periodId = isset($_GET['period_id']) && (int) $_GET['period_id'] > 0
            ? (int) $_GET['period_id']
            : null;

            if ($department === '') {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'Department is required.'
                ]);

                return;

            }


            /**
             * Generate PDF
             */
            $pdf = $this->service->generateFacultySummaryPDF($department, $periodId);


            /**
             * Download response
             */
            $filename = sprintf(
                'evaluation-summary-%s-%s.pdf',
                strtolower($department),
                date('Y-m-d')
            );

            header('Content-Type: application/pdf');

            header(
                'Content-Disposition: inline; filename="'
                . $filename
                . '"'
            );

            header(
                'Content-Length: ' . strlen($pdf)
            );

            header('Cache-Control: no-store');

            header('X-Content-Type-Options: nosniff');

            echo $pdf;
            exit;

        } catch (Throwable $e) {

            error_log(
                'Faculty report generation failed: '
                . $e->getMessage()
            );

            http_response_code(500);

            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'success' => false,
                'message' => 'Unable to generate evaluation report.',
            ]);

        }

    }


    public function downloadTeacherReport(): void
    {

        try {

            # Get teacher_id parameter and sanitize

            $teacherId = isset($_GET['teacher_id']) && (int) $_GET['teacher_id'] > 0
            ? (int) $_GET['teacher_id']
            : null;


            # Get teacher_id parameter and sanitize

            $periodId = isset($_GET['period_id']) && (int) $_GET['period_id'] > 0
            ? (int) $_GET['period_id']
            : null;


            # Get teacher name parameter and format

            $teacherName = trim($_GET['teacher_name'] ?? '');

            $formattedName = str_replace(' ', '-', $teacherName);


            if ($teacherId === null || $periodId === null) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' =>
                        'A valid teacher ID and evaluation period ID are required.'
                ]);

                return;
            }


            # Generate teacher report.
            $pdf = $this->service->generateIndiviudalTeacherReport(
                (int) $periodId,
                (int) $teacherId
            );


            # return PDF report

            $filename = sprintf(
                'evaluation-summary-%s-%s.pdf',
                $formattedName,
                date('Y-m-d')
            );

            header('Content-Type: application/pdf');

            header(
                'Content-Disposition: inline; filename="'
                . $filename
                . '"'
            );

            header('Content-Length: ' . strlen($pdf));

            header('Cache-Control: no-store');

            header('X-Content-Type-Options: nosniff');

            echo $pdf;
            exit;


        } catch (Throwable $e) {

            error_log(
                "Teacher report generation failed: "
                . $e->getMessage()
            );

            http_response_code(500);

            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'success' => false,
                'message' => 'Unable to generate teacher report.',
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'error-message' => $e->getMessage()
            ]);

        }

    }


}
