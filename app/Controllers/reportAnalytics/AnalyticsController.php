<?php 
require_once __DIR__ . '/../../Models/AnalyticsModel.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Controllers/reports/utils/PdfRenderer.php'; // Ensure path to renderer is correct

$controller = new AnalyticsController();
$action = $_GET['action'] ?? 'index';

if ($action === 'downloadPDF') {
  $controller->downloadPDF();
} elseif ($action === 'teacherReport') {
    $controller->downloadTeacherPDF();
} elseif ($action === 'exportExcel') {
  $controller->exportExcel();
}
else {
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

  public function exportExcel() {
    $response = $this->getSharedData();
    
    if (isset($response['error'])) {
        die($response['error']);
    }

    if (ob_get_length()) ob_end_clean();

    $rankings = $response['teachers'] ?? [];
    $filename = "Teacher_Rankings_" . date('Ymd') . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($output, ['Rank', 'Teacher Name', 'Average Rating', 'Adjective Rating']);

    foreach ($rankings as $index => $teacher) {
        fputcsv($output, [
            $index + 1,
            $teacher['full_name'] ?? 'N/A',
            number_format($teacher['mean_score'] ?? 0, 2),
            $teacher['adjective_rating']
        ]);
    }

    fclose($output);
    exit;
  }

  public function downloadTeacherPDF() {
    if (ob_get_length()) ob_end_clean();

    global $pdo;
    $model = new AnalyticsModel($pdo);

    $teacherId = $_GET['teacher_id'] ?? null;
    $dept = $_GET['dept'] ?? null;
    $requestedPeriodId = $_GET['period_id'] ?? null;

    if (!$teacherId) {
        die("Teacher ID is required.");
    }

    if ($requestedPeriodId && $requestedPeriodId !== 'null' && $requestedPeriodId !== '') {
        $period = $model->getPeriodById($requestedPeriodId);
        $isActive = false;
    } else {

        $period = $model->getActivePeriod($dept);
        $isActive = true;
    }

    if (!$period) {
        die("No evaluation period found for this teacher.");
    }

    $targetId = $period['period_id'];
    $data = $model->getIndividualTeacherBundle($targetId, $teacherId);

    $renderer = new PdfRenderer();
    $renderer->render('teacher_individual_report.php', [
        'data' => $data,
        'meta' => [
            'academic_year' => $period['academic_year'],
            'semester' => $period['semester'],
            'is_live' => $isActive,
            'teacher_name' => $data['info']['full_name'] ?? 'Instructor',
            'generated_at' => date('F j, Y')
        ]
    ]);
    exit;
  }
}