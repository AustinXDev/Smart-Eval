<?php 

class AnalyticsModel
{
  private $pdo;

  public function __construct($pdo){
    $this->pdo = $pdo;
  }

  // Get the active evaluation period for a department
  public function getActivePeriod($department) {

    $stmt = $this->pdo->prepare("
      SELECT period_id, academic_year, semester
      FROM evaluation_periods
      WHERE target_dept = ? AND is_active = 1
      LIMIT 1
    ");
    $stmt->execute([$department]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  //get period by id - used for historical data
  public function getPeriodById($id) {

    $stmt = $this->pdo->prepare("SELECT * FROM evaluation_periods WHERE period_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);

  }

  // Get funnel Data for the period
  public function getLiveFunnel($periodId, $department) {

    //Total Enrolled
    $stmt = $this->pdo->prepare("
      SELECT COUNT(*) as total_enrolled
      FROM students s -- Added
      INNER JOIN programs p ON p.program_id = s.program_id
      WHERE p.department = ? AND s.is_active = 1
    ");
    $stmt->execute([$department]);
    $totalEnrolled = (int)$stmt->fetchColumn();

    //Students Never Started
    $stmt = $this->pdo->prepare("
      SELECT COUNT(s.student_id)
      FROM students s
      INNER JOIN programs p 
        On p.program_id = s.program_id
      WHERE p.department = ? AND s.is_active = 1
      AND NOT EXISTS (
        SELECT 1 FROM evaluation_status es
        WHERE es.student_id = s.student_id AND es.period_id = ?
      )
    ");
    $stmt->execute([$department, $periodId]);
    $neverStarted = (int)$stmt->fetchColumn();

    // Students Completed All assigend loads
    $stmt = $this->pdo->prepare("
      SELECT COUNT(*) FROM (
        SELECT es.student_id
        FROM evaluation_status es
        INNER JOIN students s ON s.student_id = es.student_id
        INNER JOIN programs p ON p.program_id = s.program_id
        WHERE es.period_id = ? AND p.department = ?
        GROUP BY es.student_id
        HAVING SUM(es.is_submitted) = COUNT(es.load_id)
      ) AS finished
    ");
    $stmt->execute([$periodId, $department]);
    $completed = (int)$stmt->fetchColumn();

    $abandoned = $totalEnrolled - $neverStarted - $completed;

    return $this->formatHistoricalFunnel($totalEnrolled, $neverStarted, $abandoned, $completed);
  }

  public function getAnalyticsBundle($periodId, $department, $isActive) {
    return [
      'funnel' => $isActive
                  ?$this->getLiveFunnel($periodId, $department)
                  : [], //future historical data
    ];
  }

  //helpers to keep formatting identical between live and historical data
  private function formatHistoricalFunnel($total, $never, $abandoned, $completed) {
    return [
      'total' => $total, 
      'never_started' => $never,
      'abandoned' => $abandoned,
      'completed' => $completed,
      'rates' => [
        'completion' => $total > 0 ? round(($completed / $total) * 100) : 0,
        'abadoned' => $total > 0 ? round(($abandoned / $total) * 100) : 0,
        'never_started' => $total > 0 ? round(($never / $total) * 100) : 0,
      ]
    ];
  }
}

?>