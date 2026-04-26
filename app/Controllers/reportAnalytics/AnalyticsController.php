<?php 
require_once __DIR__ . '/../../Models/AnalyticsModel.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Controllers/reports/utils/PdfRenderer.php'; // Ensure path to renderer is correct

$controller = new AnalyticsController();
$action = $_GET['action'] ?? 'index';

if ($action === 'downloadPDF') {
    $controller->downloadPDF();
} else {
    header('Content-Type: application/json');
    $controller->index();
}

class AnalyticsController
{
    public function index() {
        $data = $this->getSharedData();
        echo json_encode($data);
    }

    public function downloadPDF() {
        if (ob_get_length()) ob_end_clean();

        $data = $this->getSharedData();

        if (isset($data['error'])) {
            die($data['error']);
        }

        $renderer = new PdfRenderer();
        
        // Pass the bundle to your report view
        $renderer->render('analytics_report.php', [
            'data' => $data,
            'meta' => $data['meta']
        ]);
        exit;
    }

    private function getSharedData() {
        global $pdo;
        $model = new AnalyticsModel($pdo);

        $dept = $_GET['dept'] ?? null;
        $requestedPeriodId = $_GET['period_id'] ?? null;

        if (!$dept) return ['error' => 'Department is required.'];

        // Resolve Period
        if ($requestedPeriodId && $requestedPeriodId !== 'null') {
            $period = $model->getPeriodById($requestedPeriodId);
            $isActive = false;
        } else {
            $period = $model->getActivePeriod($dept);
            $isActive = true;
        }

        if (!$period) return ['error' => 'No period found'];

        $targetId = $period['period_id'];
        $data = $model->getAnalyticsBundle($targetId, $dept, $isActive) ?? [];

        $data['meta'] = [
            'academic_year' => $period['academic_year'],
            'semester' => $period['semester'],
            'department' => strtoupper($dept),
            'is_live' => $isActive,
        ];

        return $data;
    }
}