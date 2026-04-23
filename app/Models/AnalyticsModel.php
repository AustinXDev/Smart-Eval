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
      FROM students s 
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

    // Students Completed All assigend loads (Live Calculation)
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

  //Live calculation for mean score active period_id 
  public function getMeanScoreTrend($department, $periodId){

    //fetch the 4 most recent completed periods
    $stmt = $this->pdo->prepare("
      SELECT academic_year, final_average
      FROM evaluation_periods
      WHERE target_dept = ?
      AND is_active = 0
      ORDER BY academic_year DESC
      LIMIT 4
    ");
    $stmt->execute([$department]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtActive = $this->pdo->prepare("
      SELECT 
          p.academic_year,
          ROUND(SUM(ea.score) / COUNT(ea.score), 2) as current_avg
      FROM evaluation_periods p
      INNER JOIN evaluation_status es ON es.period_id = p.period_id
      INNER JOIN evaluation_answers ea ON ea.eval_id = es.eval_id
      WHERE p.target_dept = ? 
        AND p.is_active = 1
        AND es.is_submitted = 1
        AND es.student_id IN (
            SELECT es_done.student_id 
            FROM evaluation_status es_done
            WHERE es_done.period_id = ? AND es_done.is_submitted = 1
            GROUP BY es_done.student_id
            HAVING COUNT(es_done.load_id) = (
                SELECT COUNT(*) FROM evaluation_status es_total 
                WHERE es_total.student_id = es_done.student_id AND es_total.period_id = ?
            )
        )
      GROUP BY p.period_id
    ");
    $stmtActive->execute([$department, $periodId, $periodId]);
    $activeData = $stmtActive->fetch(PDO::FETCH_ASSOC);

    if ($activeData && $activeData['current_avg'] !== null) {
      array_unshift($history, [
        'academic_year' => $activeData['academic_year'] . ' (Live)',
        'final_average' => $activeData['current_avg']
      ]);
    }

    return $history;
  }

  public function getYearLevelAnalytics($periodId , $department){

    $check = $this->pdo->prepare("SELECT is_closed FROM evaluation_periods WHERE period_id = ?");
    $check->execute([$periodId]);
    $period = $check->fetch(PDO::FETCH_ASSOC);

    if ($period && $period['is_closed'] == 1) {
        // --- HISTORICAL SOURCE ---
        $sql = "SELECT 
                    year_level_at_time AS year_level,
                    COUNT(*) AS total_enrolled,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS total_finished,
                    SUM(CASE WHEN status != 'Completed' THEN 1 ELSE 0 END) AS total_not_finished,
                    ROUND((SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) * 100.0) / NULLIF(COUNT(*), 0), 2) AS completion_percentage
                FROM participation_history 
                WHERE period_id = ? 
                GROUP BY year_level_at_time 
                ORDER BY year_level_at_time ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$periodId]);
    } else {
        // --- LIVE SOURCE ---
        $sql = "SELECT 
                    s.year_level,
                    COUNT(s.student_id) AS total_enrolled,
                    SUM(CASE WHEN s.is_finished_all = 1 THEN 1 ELSE 0 END) AS total_finished,
                    SUM(CASE WHEN s.is_finished_all = 0 THEN 1 ELSE 0 END) AS total_not_finished,
                    ROUND((SUM(CASE WHEN s.is_finished_all = 1 THEN 1 ELSE 0 END) * 100.0) / NULLIF(COUNT(s.student_id), 0), 2) AS completion_percentage
                FROM students s
                INNER JOIN programs p ON s.program_id = p.program_id
                WHERE p.department = ? AND s.is_active = 1
                GROUP BY s.year_level 
                ORDER BY s.year_level ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$department]);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAnalyticsBundle($periodId, $department, $isActive) {
    
    $trendRaw = $this->getMeanScoreTrend($department, $periodId);
    
    $growthrate = $this->calculateGrowthRate($trendRaw);

    return [
      'funnel' => $isActive ? $this->getLiveFunnel($periodId, $department) : [],
      'trend' => [
          'trend' => array_reverse($trendRaw), 
          'growth' => $growthrate
      ],
      'year_participation' => $this->getYearLevelAnalytics($periodId, $department),
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

  //helpers to calculate growth rate between current and previous period
  private function calculateGrowthRate($trend) {
    if(count($trend) < 2) return 0;

    $current = (float)$trend[0]['final_average'];
    $previous = (float)$trend[1]['final_average'];

    if($previous == 0) return 0;
    
    $change = (($current - $previous) / $previous) * 100;

    return round($change, 2);
  }
}

?>