<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';

$period_id = $_POST['period_id'] ?? null;

if (!$period_id) {
    echo json_encode(['status' => 'error', 'message' => 'Period ID not found.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get period info
    $stmt = $pdo->prepare("
        SELECT period_id, target_dept, is_active
        FROM evaluation_periods
        WHERE period_id = ?
    ");
    $stmt->execute([$period_id]);
    $period = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$period) {
        throw new Exception("Period not found.");
    }

    $dept = $period['target_dept'];
    $pid = $period['period_id'];

    // Count total expected evaluations (based on total students)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total_expected
        FROM students s
        INNER JOIN programs p
            ON s.program_id = p.program_id
        WHERE s.is_active = 1
          AND p.department = ?
    ");
    $stmt->execute([$dept]);
    $totalExpected = (int) ($stmt->fetchColumn() ?? 0);

    if ($totalExpected === 0) {
        throw new Exception("No expected evaluations found for this period.");
    }

    // Count total finished evaluations (students with is_finished_all = 1)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total_finished
        FROM students s
        INNER JOIN programs p
            ON s.program_id = p.program_id
        WHERE s.is_active = 1
          AND s.is_finished_all = 1
          AND p.department = ?
    ");
    $stmt->execute([$dept]);
    $totalFinished = (int) ($stmt->fetchColumn() ?? 0);

    // Calculate participation rate safely
    $participationRate = $totalExpected > 0
        ? ($totalFinished / $totalExpected) * 100
        : 0;

    // Check if participation is 100%
    if ($participationRate < 100) {
        throw new Exception("Cannot force close. Participation is only " . number_format($participationRate, 2) . "%.");
    }

    //archived student participation
    $archiveStudents = $pdo->prepare("
        INSERT INTO participation_history (
            period_id, student_id, full_name_at_time,
            year_level_at_time, dept_at_time, status
        )
        SELECT 
            ?, 
            s.student_id, 
            s.full_name, 
            s.year_level, 
            p.department,
            CASE 
                WHEN s.is_finished_all = 1 THEN 'Completed'
                WHEN EXISTS (
                    SELECT 1 FROM evaluation_status es 
                    WHERE es.student_id = s.student_id AND es.period_id = ?
                ) THEN 'Abandoned'
                ELSE 'Never Started'
            END
        FROM students s
        INNER JOIN programs p ON s.program_id = p.program_id
        WHERE p.department = ? AND s.is_active = 1
    ");
    $archiveStudents->execute([$pid, $pid, $dept]);

    //Queue Teacher Notification
    $queueTeachers = $pdo->prepare("
        INSERT INTO teacher_notification_queue (teacher_id, period_id, status)
        SELECT DISTINCT t.teacher_id, ?, 'pending'
        FROM teachers t
        INNER JOIN teacher_load tl ON t.teacher_id = tl.teacher_id
        WHERE t.department = ? 
          AND tl.is_active = 1 
          AND t.is_active = 1 
          AND EXISTS (
              SELECT 1 FROM evaluation_status es 
              WHERE es.load_id = tl.load_id 
                AND es.period_id = ? 
                AND es.is_submitted = 1
          )
    ");
    $queueTeachers->execute([$pid, $dept, $pid]);

    //update evaluation periods with final stats
    $snapshotStmt = $pdo->prepare("
        UPDATE evaluation_periods ep
        SET
            ep.final_average = (
                SELECT ROUND(SUM(ea.score) / NULLIF(COUNT(ea.answer_id), 0), 2)
                FROM evaluation_answers ea
                INNER JOIN evaluation_status es ON ea.eval_id = es.eval_id
                WHERE es.period_id = ?
                AND es.student_id IN (
                    SELECT es_inner.student_id
                    FROM evaluation_status es_inner
                    WHERE es_inner.period_id = ?
                    GROUP BY es_inner.student_id
                    HAVING SUM(es_inner.is_submitted) = COUNT(es_inner.load_id)
                )
            ),
            ep.total_responses = (
                SELECT COUNT(*) FROM (
                    SELECT es_inner.student_id
                    FROM evaluation_status es_inner
                    WHERE es_inner.period_id = ? AND es_inner.is_submitted = 1
                    GROUP BY es_inner.student_id
                    HAVING COUNT(es_inner.load_id) = (
                        SELECT COUNT(*) FROM evaluation_status es2
                        WHERE es2.student_id = es_inner.student_id AND es2.period_id = ?
                    )
                ) AS temp_list
            ),
            ep.total_unresponsive_students = (
                SELECT COUNT(*) FROM students s
                INNER JOIN programs p ON s.program_id = p.program_id
                WHERE p.department = ep.target_dept AND s.is_active = 1
                AND NOT EXISTS (
                    SELECT 1 FROM evaluation_status es 
                    WHERE es.student_id = s.student_id AND es.period_id = ?
                )
            ),
            ep.total_incomplete_students = (
                SELECT COUNT(*) FROM students s
                INNER JOIN programs p ON s.program_id = p.program_id
                WHERE p.department = ep.target_dept AND s.is_active = 1
                AND s.is_finished_all = 0
                AND EXISTS (
                    SELECT 1 FROM evaluation_status es 
                    WHERE es.student_id = s.student_id AND es.period_id = ?
                )
            ),
            ep.participation_rate = (
                SELECT ROUND(
                    (COUNT(DISTINCT CASE WHEN s.is_finished_all = 1 THEN s.student_id END) * 100.0) /
                    NULLIF(COUNT(DISTINCT s.student_id), 0), 2
                )
                FROM students s
                INNER JOIN programs p ON s.program_id = p.program_id
                WHERE p.department = ep.target_dept AND s.is_active = 1
            ),
            ep.is_active = 0,
            ep.is_closed = 1
        WHERE ep.period_id = ?
    ");
    $snapshotStmt->execute([$pid, $pid, $pid, $pid, $pid, $pid, $pid]);

    //reset students enrollment_type, selected_loads, is_finished
    $resetStudents = $pdo->prepare("
        UPDATE students s
        INNER JOIN programs p ON s.program_id = p.program_id
        SET s.enrollment_type    = NULL,
            s.selected_load_ids  = NULL,
            s.is_finished_all    = 0
        WHERE p.department = ?
    ");
    $resetStudents->execute([$dept]);

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Evaluation period successfully closed. Participation: ' . number_format($participationRate, 2) . '%'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}