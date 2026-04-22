<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../Models/AnalyticsModel.php';
require_once __DIR__ . '/../../config/database.php';

$controller = new AnalyticsController();
$controller->index();

class AnalyticsController
{
  public function index(){
    global $pdo;
    $model = new AnalyticsModel($pdo);

    $dept = $_GET['dept'] ?? null;
    $requestedPeriodId = $_GET['period_id'] ?? null;

    if(!$dept) {
      echo json_encode(['error' => 'Department is requied.']);
      return;
    }

    if($requestedPeriodId && $requestedPeriodId !== 'null') {

      $isActive = false;

    } else {
      //This show if admin only opens page
      $period = $model->getActivePeriod($dept);
      $isActive = true;
    }

    if(!$period) {
      echo json_encode([]);
      return;
    }

    $targetId = $period['period_id'];

    //get Analytics Bundle Data
    $data = $model->getAnalyticsBundle($targetId, $dept, $isActive) ?? [];

    $data['meta'] = [
      'academic_year' => $period['academic_year'],
      'semester' => $period['semester'],
      'is_live' => $isActive,
    ];

    echo json_encode($data);

  }
}
?>