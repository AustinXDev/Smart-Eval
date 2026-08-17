<?php 

require_once __DIR__ . '/../../Models/AnalyticsModel.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../Controllers/reports/utils/PdfRenderer.php';

$controller = new AnalyticsController();
$action = $_GET['action'] ?? 'index';

if ($action === 'downloadPDF') {
  $controller->downloadPDF();
}

elseif ($action === 'getHistoryList') {
    $controller->getHistoryList();
}

elseif ($action === 'teacherReport') {
    $controller->downloadTeacherPDF();
} 
elseif ($action === 'exportExcel') {
  $controller->exportExcel();
} elseif ($action === 'teacherComments'){
    $controller->getTeacherComments();
} elseif ($action === 'exportComments') {
    $controller->exportComments();
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

        error_log("dept: $dept | period_id: $requestedPeriodId");

        if (!$dept) return ['error' => 'Department is required.'];

        if ($requestedPeriodId && $requestedPeriodId !== 'null') {
            $period = $model->getPeriodById($requestedPeriodId);
            $isActive = false;
        } else {
            $period = $model->getActivePeriod($dept);
            $isActive = true;

            if(!$period) {
                $history = $model->getEvaluationHistory($dept);
                $period = !empty($history) ? $history[0] : null;
                $isActive = false;
            }
        }

        if (!$period) return ['error' => 'System contains no evaluation data yet.'];

        $targetId = $period['period_id'];
        $data = $model->getAnalyticsBundle($targetId, $dept, $isActive) ?? [];
        
        error_log("teachers returned: " . count($data['teachers'] ?? []));

        $data['meta'] = [
            'academic_year' => $period['academic_year'],
            'semester' => $period['semester'],
            'department' => strtoupper($dept),
            'is_live' => $isActive,
        ];

        return $data;
    }

    public function getHistoryList() {
        header('Content-Type: application/json');
        global $pdo;
        $dept = $_GET['dept'] ?? '';
        $model = new AnalyticsModel($pdo);

        $history = $model->getEvaluationHistory($dept);
        
        echo json_encode([
            'status' => 'success',
            'data' => $history
        ]);
        exit;
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

  public function getTeacherComments(){
    header('Content-Type: application/json');
    global $pdo;
    $model = new AnalyticsModel($pdo);

    $teacherId = $_GET['teacher_id'] ?? null;
    $dept = $_GET['dept'] ?? null;
    $requestedPeriodId = $_GET['period_id'] ?? null;

    if (!$teacherId) {
        echo json_encode(['status' => 'error', 'message' => 'Teacher ID is required']);
        exit;
    }

    if ($requestedPeriodId && $requestedPeriodId !== 'null' && $requestedPeriodId !== '') {
        $periodId = $requestedPeriodId;
    } else {
        $period = $model->getActivePeriod($dept);
        if (!$period) {
            echo json_encode(['status' => 'error', 'message' => 'No active evaluation period found']);
            exit;
        }
        $periodId = $period['period_id'];
    }

    $data = $model->getVerifiedTeacherComments($periodId, $teacherId);

    if ($data === false || empty($data)) {
        echo json_encode(['status' => 'success', 'data' => [], 'message' => 'No verified comments found for this student load']);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'period_id' => $periodId,
        'data' => $data
    ]);
    exit;
  }

  public function exportComments(){
    global $pdo;
    $model = new AnalyticsModel($pdo);

    $teacherId = $_GET['teacher_id'] ?? null;
    $requestedPeriodId = $_GET['period_id'] ?? null;
    $dept = $_GET['dept'] ?? null;
    $teacherName = $_GET['name'] ?? 'Teacher';

    if (!$teacherId) {
        echo json_encode(['status' => 'error', 'message' => 'Teacher ID is required']);
        exit;
    }

    if ($requestedPeriodId && $requestedPeriodId !== 'null' && $requestedPeriodId !== '') {
        $periodId = $requestedPeriodId;
    } else {
        $period = $model->getActivePeriod($dept);
        if (!$period) {
            echo json_encode(['status' => 'error', 'message' => 'No active evaluation period found']);
            exit;
        }
        $periodId = $period['period_id'];
    }

    $comments = $model->getVerifiedTeacherComments($periodId, $teacherId);

    if (ob_get_length()) ob_end_clean();

    $filename = "Comments_" . str_replace(' ', '_', $teacherName) . "_" . date('Ymd') . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($output, ['#', 'Student Feedback/Comments']);

    if (!empty($comments)) {
        foreach ($comments as $index => $c) {
            fputcsv($output, [
                $index + 1,
                $c['comment'] ?? $c
            ]);
        }
    } else {
        fputcsv($output, ['-', 'No verified comments found for this period.']);
    }

    fclose($output);
    exit;
  }
}