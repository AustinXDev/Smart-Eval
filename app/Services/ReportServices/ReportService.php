<?php

namespace App\Services\ReportServices;

use App\Models\Teacher;
use App\Services\AnalyticsServices\AnalyticsService;
use App\Services\TeacherServices\TeacherReportService;
use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;

define('BASE_PATH', dirname(__DIR__, 3));

final class ReportService
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private TeacherReportService $teacherReportService
    ) {
    }

    /**
     * Generate the department evaluation summary PDF.
     */
    public function generateFacultySummaryPDF(
        string $department,
        ?int $periodId = null
    ): string {

        # Get analytics data

        $data = $this->analyticsService->getAnalyticsBundle(
            $department,
            $periodId
        );

        if ($data === null) {
            throw new RuntimeException(
                'No evaluation data is available for this report.'
            );
        }


        # Render HTML template

        $html = $this->renderTemplate(
            'Evaluation/evaluation-summary',
            $data
        );


        # Configure Dompdf

        $options = new Options();

        $options->set([
            'isHtml5ParserEnabled'  => true,
            'isRemoteEnabled'       => false,
            'defaultFont'           => 'Arial',
            'defaultBackend'        => 'CPDF'
        ]);

        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();

        $registerEvaluationFooter = require BASE_PATH
            . DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'Views'
            . DIRECTORY_SEPARATOR
            . 'Reports'
            . DIRECTORY_SEPARATOR
            . 'Evaluation'
            . DIRECTORY_SEPARATOR
            . 'sections'
            . DIRECTORY_SEPARATOR
            . 'footer.php';

        $registerEvaluationFooter(
            $canvas,
            date('F j, Y \a\t h:i A')
        );


        # Lock Editing and Copying permission

        if (method_exists($canvas, 'get_cpdf')) {

            $canvas->get_cpdf()->setEncryption(
                '',
                $_ENV['PDF_PASS'],
                ['print']
            );

        } else {

            throw new RuntimeException(
                'Encryption failed because the active Dompdf canvas engine does not support CPDF.'
            );

        }


        return $dompdf->output();
    }


    /**
     * Generate individual teacher report pdf
     */
    public function generateIndiviudalTeacherReport(
        int $periodId,
        int $teacherId
    ): string {

        $data = $this->teacherReportService->getIndividualTeacherReport($periodId, $teacherId);

        if ($data === null) {
            throw new RuntimeException(
                "No report data is available for this teacher."
            );
        }


        //Render HTML Template

        $html = $this->renderTemplate(
            'Teacher/teacher-report',
            $data
        );


        //Configure Dompdf

        $options = new Options();

        $options->set([
            'isHtml5ParserEnabled'  => true,
            'isRemoteEnabled'       => false,
            'defaultFont'           => 'Arial',
            'defaultBackend'        => 'CPDF'
        ]);

        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();

        $registerTeacherFooter = require BASE_PATH
            . DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'Views'
            . DIRECTORY_SEPARATOR
            . 'Reports'
            . DIRECTORY_SEPARATOR
            . 'Teacher'
            . DIRECTORY_SEPARATOR
            . 'sections'
            . DIRECTORY_SEPARATOR
            . 'footer.php';

        $registerTeacherFooter(
            $canvas,
            date('F j, Y \a\t h:i A')
        );

        # Lock Editing and Copying permission

        if (method_exists($canvas, 'get_cpdf')) {

            $canvas->get_cpdf()->setEncryption(
                '',
                $_ENV['PDF_PASS'],
                ['print']
            );

        } else {

            throw new RuntimeException(
                'Encryption failed because the active Dompdf canvas engine does not support CPDF.'
            );

        }

        return $dompdf->output();

    }


    private function renderTemplate(
        string $template,
        array $data
    ): string {

        $templatePath = BASE_PATH
            . DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'Views'
            . DIRECTORY_SEPARATOR
            . 'Reports'
            . DIRECTORY_SEPARATOR
            . $template
            . '.php';

        if (!is_file($templatePath)) {
            throw new RuntimeException(
                "Template does not exist: {$templatePath}"
            );
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $templatePath;

            return ob_get_clean();

        } catch (\Throwable $e) {

            ob_end_clean();

            throw $e;
        }
    }
}
