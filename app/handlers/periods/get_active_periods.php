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
            ep.is_active,
            COUNT(DISTINCT s.student_id) AS total_students,
            SUM(CAST(s.is_finished_all AS UNSIGNED)) AS total_finished
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
        WHERE ep.is_active = 1
        GROUP BY ep.target_dept
    ");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}