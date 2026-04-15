<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

try{
  $stmt = $pdo->prepare("
     SELECT 
        qs.set_id,
        qs.set_name,
        qs.is_active,
        qs.created_at,
        COUNT(q.question_id) AS total_questions,
        -- Boolean: true if any active evaluation is using this set
        CASE WHEN COUNT(ep.period_id) > 0 THEN 1 ELSE 0 END AS active_evaluation_using_set,
        -- Total number of periods (active or not) that used this set
        (SELECT COUNT(*) 
          FROM evaluation_periods ep2 
          WHERE ep2.set_id = qs.set_id) AS total_periods_using_set
    FROM question_sets qs
    LEFT JOIN questions q
        ON q.set_id = qs.set_id
        AND q.is_active = 1
    LEFT JOIN evaluation_periods ep
        ON ep.set_id = qs.set_id
        AND ep.is_active = 1
    WHERE qs.is_active = 1
    GROUP BY qs.set_id, qs.set_name, qs.is_active, qs.created_at
    ORDER BY qs.created_at DESC  
  ");

  $stmt->execute();
  $questionSets = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['status' => 'success', 'data' => $questionSets]);
  
} catch (Exception $e){
  echo json_encode(['status'=>'error', 'message'=>'Database error: '. $e->getMessage()]);
}

?>