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

  public function getDepartmentCategoryPerformance($periodId, $department) {
    $sql = "SELECT
             q.category,
             ROUND(AVG(ea.score), 2) as average_score
            FROM evaluation_answers ea
            INNER JOIN questions q ON ea.question_id = q.question_id
            INNER JOIN evaluation_status es ON ea.eval_id = es.eval_id
            INNER JOIN students s ON es.student_id = s.student_id
            INNER JOIN programs p ON s.program_id = p.program_id
            WHERE es.period_id = ?
             AND p.department = ?
             AND s.is_active = 1
            -- Only include students who have completed their entire load
            AND s.student_id IN (
              SELECT es_inner.student_id
              FROM evaluation_status es_inner
              WHERE es_inner.period_id = ?
              GROUP BY es_inner.student_id
              HAVING SUM(es_inner.is_submitted) = COUNT(es_inner.load_id)
            )
            GROUP BY q.category 
            ORDER BY q.category ASC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$periodId, $department, $periodId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getQuestionBreakDown($periodId, $department){
    $sql = "
      SELECT
        q.question_text,
        ROUND(AVG(ea.score), 2) as average_score
      FROM evaluation_answers ea
      INNER JOIN questions q ON ea.question_id = q.question_id
      INNER JOIN evaluation_status es ON ea.eval_id = es.eval_id
      INNER JOIN students s ON es.student_id = s.student_id
      INNER JOIN programs p ON s.program_id = p.program_id
      WHERE es.period_id = ?
        AND p.department = ?
        AND s.is_active = 1
      -- Only include students who have completed their entire load
      AND s.student_id IN (
        SELECT es_inner.student_id
        FROM evaluation_status es_inner
        WHERE es_inner.period_id = ?
        GROUP BY es_inner.student_id
        HAVING SUM(es_inner.is_submitted) = COUNT(es_inner.load_id)
      )
      GROUP BY q.question_text
      ORDER BY average_score DESC;
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$periodId, $department, $periodId]);
    return $stmt->fetchAll(PDO:: FETCH_ASSOC);
  }

  public function getTeacherRanking($periodId, $department){
    $sql = "
      SELECT 
        t.teacher_id,
        t.employee_id,
        t.full_name,
        ROUND(AVG(ea.score), 2) as mean_score,
        COUNT(DISTINCT es.student_id) as total_evaluated
      FROM teachers t
      INNER JOIN teacher_load tl ON t.teacher_id = tl.teacher_id
      INNER JOIN evaluation_status es ON tl.load_id = es.load_id
      INNER JOIN evaluation_answers ea ON es.eval_id = ea.eval_id
      INNER JOIN evaluation_periods ep ON es.period_id = ep.period_id
      INNER JOIN programs pr ON tl.program_id = pr.program_id
      WHERE ep.period_id = ?
        AND pr.department = ?
        AND tl.is_active = 1
        AND es.is_submitted = 1
        AND es.student_id IN (
          SELECT es_inner.student_id
          FROM evaluation_status es_inner
          WHERE es_inner.period_id = ?
          GROUP BY es_inner.student_id
          HAVING SUM(es_inner.is_submitted) = COUNT(es_inner.load_id)
        )
      GROUP BY t.teacher_id, t.employee_id, t.full_name
      ORDER BY mean_score DESC, total_evaluated DESC;;
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$periodId, $department, $periodId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($results as &$teacher){
      $ratingData = $this->getAdjectiveRating($teacher['mean_score']);
      $teacher['adjective_rating'] = $ratingData['rating'];
    }

    return $results;
  }

  public function getNotEvaluatedList($periodId, $department){
    $sql = '
      SELECT s.student_id, s.full_name, s.email, p.program_name
      FROM students s
      INNER JOIN programs p ON s.program_id = p.program_id
      WHERE p.department = ? 
        AND s.is_active = 1
        -- Gate: The student has NO entries at all for this period
        AND NOT EXISTS (
            SELECT 1 FROM evaluation_status es 
            WHERE es.student_id = s.student_id 
            AND es.period_id = ?
        )
    ';
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$department, $periodId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAbandonedList($periodId, $department) {
    $sql = '
      SELECT s.student_id, s.full_name, s.email, p.program_name
      FROM students s
      INNER JOIN programs p ON s.program_id = p.program_id
      WHERE p.department = ? 
        AND s.is_finished_all = 0 -- Logic: They aren\'t done yet
        AND s.is_active = 1
        -- Gate: They MUST have at least one entry (meaning they started)
        AND EXISTS (
            SELECT 1 FROM evaluation_status es 
            WHERE es.student_id = s.student_id 
            AND es.period_id = ?
        )
    ';
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$department, $periodId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAnalyticsBundle($periodId, $department, $isActive) {
    
    $trendRaw = $this->getMeanScoreTrend($department, $periodId);
    $growthrate = $this->calculateGrowthRate($trendRaw);
    $adjectiverate = $this->adjectiveRating($trendRaw);
    $categoryRaw = $this->getDepartmentCategoryPerformance($periodId, $department);
    $categoryPerformanceHiglights = $this->getDepartmentPerformanceHighlights($categoryRaw);
    $questionRaw = $this->getQuestionBreakDown($periodId, $department);
    $questionPerformanceHighlights = $this->getQuestionPerformanceHighlights($questionRaw);

    return [
      'funnel' => $isActive ? $this->getLiveFunnel($periodId, $department) : [],
      'trend' => [
          'trend' => array_reverse($trendRaw), 
          'growth' => $growthrate,
          'adjectiveRating' => $adjectiverate,
      ],
      'year_participation' => $this->getYearLevelAnalytics($periodId, $department),
      'category' => [
        'category_performance' => $categoryRaw,
        'performance_highlights' => $categoryPerformanceHiglights
      ],
      'questions' => $questionPerformanceHighlights, 
      'teachers' => $this->getTeacherRanking($periodId, $department),
      'not_evaluated' => $this->getNotEvaluatedList($periodId, $department),
      'abandoned' => $this->getAbandonedList($periodId, $department),
    ];
  }


  //get Individual teacher Bundle
  public function getIndividualTeacherBundle($periodId, $teacherId){
    $validStudentsSql = "
        SELECT es.student_id
        FROM evaluation_status es
        JOIN teacher_load tl ON es.load_id = tl.load_id
        WHERE es.period_id = ?
        GROUP BY es.student_id
        HAVING COUNT(CASE WHEN es.is_submitted = 1 THEN 1 END) = COUNT(tl.load_id)
    ";

    //Fetch Overall Stats (Filtering by Valid Students)
    $stmt = $this->pdo->prepare("
        SELECT 
            AVG(ea.score) as average_score,
            COUNT(DISTINCT es.student_id) as total_evaluated,
            t.full_name
        FROM evaluation_status es
        JOIN teacher_load tl ON es.load_id = tl.load_id
        JOIN teachers t ON tl.teacher_id = t.teacher_id
        JOIN evaluation_answers ea ON es.eval_id = ea.eval_id
        WHERE es.period_id = ? 
          AND tl.teacher_id = ?
          AND es.student_id IN ($validStudentsSql)
    ");
    $stmt->execute([$periodId, $teacherId, $periodId]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$summary || !$summary['full_name']) return null;

    $summary['adjective_rating'] = $this->getAdjectiveRating($summary['average_score']);

    // Categorical Breakdown (Filtering by Valid Students)
    $stmt = $this->pdo->prepare("
        SELECT 
            q.category,
            AVG(ea.score) as avg_score
        FROM evaluation_answers ea
        JOIN evaluation_status es ON ea.eval_id = es.eval_id
        JOIN teacher_load tl ON es.load_id = tl.load_id
        JOIN questions q ON ea.question_id = q.question_id
        WHERE es.period_id = ? 
          AND tl.teacher_id = ?
          AND es.student_id IN ($validStudentsSql)
        GROUP BY q.category
    ");
    $stmt->execute([$periodId, $teacherId, $periodId]);
    $breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $this->pdo->prepare("
        SELECT 
            q.question_text,
            AVG(ea.score) as q_avg,
            (AVG(ea.score) - ?) as gap
        FROM evaluation_answers ea
        JOIN evaluation_status es ON ea.eval_id = es.eval_id
        JOIN teacher_load tl ON es.load_id = tl.load_id
        JOIN questions q ON ea.question_id = q.question_id
        WHERE es.period_id = ? 
          AND tl.teacher_id = ?
          AND es.student_id IN ($validStudentsSql)
        GROUP BY q.question_id
        ORDER BY q_avg ASC
    ");
    $stmt->execute([$summary['average_score'], $periodId, $teacherId, $periodId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'info' => $summary,
        'breakdown' => $breakdown,
        'question_gaps' => [
            'weakest' => array_slice($questions, 0, 5),
            'strongest' => array_slice($questions, -5)
        ]
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

  private function adjectiveRating($trend){
    
    $mean = (float)$trend[0]['final_average'];

    if ($mean >= 4.50) {
      return "Outstanding";
    } 
    elseif ($mean >= 3.50) {
      return "Very Satisfactory";
    } 
    elseif ($mean >= 2.50) {
      return "Satisfactory";
    } 
    elseif ($mean >= 1.50) {
      return "Fair";
    } 
    else {
      return "Poor";
    }
  }

  //Highlight performance helpers(highest and lowest)
  private function getDepartmentPerformanceHighlights($data){
    if(empty($data)) {
      return ['highest' => null, 'lowest' => null];
    }

    $highest = $data[0];
    $lowest = $data[0];

    foreach ($data as $item){
      if ($item['average_score'] > $highest['average_score']){
        $highest = $item;
      }

      if ($item['average_score'] < $lowest['average_score']){
        $lowest = $item;
      }
    }

    return [
      'highest' => $highest,
      'lowest' => $lowest,
    ];
  }

  //Highlight question helpers(highest and lowest)
  private function getQuestionPerformanceHighlights($data){
    if(empty($data)) {
      return ['highest' => [], 'lowest' => []];
    }

    $highest = array_filter($data, function($q){
      return (float)$q['average_score'] >= 4.0;
    });

    $lowest = array_filter($data, function($q) {
      return (float)$q['average_score'] <= 3.9;
    });

    usort($highest, fn($a, $b) => $b['average_score'] <=> $a['average_score']);
    usort($lowest, fn($a, $b) => $b['average_score'] <=> $a['average_score']);

    return [
      'highest' => array_values($highest),
      'lowest' => array_values($lowest)
    ];
  }

  //get adjective Rating per teacher
  private function getAdjectiveRating($score) {
    if ($score >= 4.50) {
        return ['rating' => 'Outstanding'];
    } elseif ($score >= 3.50) {
        return ['rating' => 'Very Satisfactory'];
    } elseif ($score >= 2.50) {
        return ['rating' => 'Satisfactory'];
    } elseif ($score >= 1.50) {
        return ['rating' => 'Fair'];
    } else {
        return ['rating' => 'Poor'];
    }
  }
}

?>