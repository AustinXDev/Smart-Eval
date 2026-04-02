<?php
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

date_default_timezone_set('Asia/Manila');
$now = date('Y-m-d H:i:s');

try {
  $stmt = $pdo->prepare("
    SELECT 
        ep.period_id, 
        ep.academic_year, 
        ep.semester, 
        ep.target_dept,
        COUNT(DISTINCT s.student_id) AS total_students,
        SUM(CASE WHEN es.is_submitted = 1 THEN 1 ELSE 0 END) AS submitted
    FROM evaluation_periods ep
    LEFT JOIN teacher_load tl 
        ON tl.program_id IN (
            SELECT program_id 
            FROM programs 
            WHERE department = ep.target_dept
        )
    LEFT JOIN teachers t
        ON tl.teacher_id = t.teacher_id
        AND t.is_active = 1
    LEFT JOIN students s 
        ON s.program_id = tl.program_id 
        AND s.year_level = tl.year_level 
        AND s.is_active = 1
    LEFT JOIN evaluation_status es 
        ON es.student_id = s.student_id 
        AND es.period_id = ep.period_id
    WHERE ep.is_active = 1
    GROUP BY ep.target_dept
  ");
  $stmt->execute();
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}