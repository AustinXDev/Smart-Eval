<?php 
require_once __DIR__ . '/../../config/database.php';

class HistoryController {

  public function getHistoryList(){
    global $pdo;

    $stmt = $pdo->prepare("SELECT period_id, academic_year, semester, end_date, final_average 
                           FROM evaluation_periods 
                           WHERE is_active = 0 
                           ORDER BY end_date DESC");
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $history
    ]);
  }
}

?>