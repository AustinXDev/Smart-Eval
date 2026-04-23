<?php 
require_once __DIR__ . '/../app/config/database.php';
date_default_timezone_set('Asia/Manila');

$now = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    // 1. Activate periods that are scheduled to start now
    $activate = $pdo->prepare("
        UPDATE evaluation_periods
        SET is_active = 1
        WHERE start_date <= ?
        AND end_date >= ?
        AND is_closed = 0
        AND is_forced = 0
    ");
    $activate->execute([$now, $now]);

    // 2. Fetch both ID and Dept for expired periods
    $findExpired = $pdo->prepare("
        SELECT period_id, target_dept FROM evaluation_periods
        WHERE end_date < ? AND is_active = 1 AND is_closed = 0
    ");
    $findExpired->execute([$now]);
    $expiredPeriods = $findExpired->fetchAll(PDO::FETCH_ASSOC);

    $deptsToReset = [];

    if (!empty($expiredPeriods)) {
        foreach ($expiredPeriods as $period) {
            $pid = $period['period_id'];
            $deptsToReset[] = $period['target_dept'];

            // --- NEW: ARCHIVE INDIVIDUAL STUDENT LISTS ---
            $archiveStudents = $pdo->prepare("
                INSERT INTO participation_history (
                    period_id, 
                    student_id, 
                    full_name_at_time, 
                    year_level_at_time, 
                    dept_at_time, 
                    status
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
                -- Target students from the correct department who didn't finish
                WHERE p.department = ? 
                AND s.is_active = 1
            ");
            $archiveStudents->execute([$pid, $pid, $period['target_dept']]);

            $snapshotStmt = $pdo->prepare("
                UPDATE evaluation_periods ep
                SET
                    -- Mean Score: Sum of scores / Total count of answer rows
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
                    -- Total Students who finished
                    ep.total_responses = (
                        SELECT COUNT(*) FROM (
                            SELECT es_inner.student_id
                            FROM evaluation_status es_inner
                            WHERE es_inner.period_id = ? 
                              AND es_inner.is_submitted = 1
                            GROUP BY es_inner.student_id
                            HAVING COUNT(es_inner.load_id) = (
                                SELECT COUNT(*) FROM evaluation_status es2
                                WHERE es2.student_id = es_inner.student_id 
                                  AND es2.period_id = ?
                            )
                        ) AS temp_list
                    ), 
                    -- Total UNRESPONSIVE
                    ep.total_unresponsive_students = (
                        SELECT COUNT(*) FROM students s
                        INNER JOIN programs p ON s.program_id = p.program_id
                        WHERE p.department = ep.target_dept AND s.is_active = 1
                        AND NOT EXISTS (
                            SELECT 1 FROM evaluation_status es 
                            WHERE es.student_id = s.student_id AND es.period_id = ? 
                        )
                    ),
                    -- Total INCOMPLETE
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
                    -- Participation Rate
                    ep.participation_rate = (
                        SELECT ROUND(
                            (COUNT(DISTINCT CASE WHEN s.is_finished_all = 1 THEN s.student_id END) * 100.0) / 
                            NULLIF(COUNT(DISTINCT s.student_id), 0), 2
                        )
                        FROM students s
                        INNER JOIN programs p ON s.program_id = p.program_id
                        WHERE p.department = ep.target_dept 
                        AND s.is_active = 1
                    ),
                    ep.is_active = 0,
                    ep.is_closed = 1 -- Mark closed
                WHERE ep.period_id = ?
            ");

            $snapshotStmt->execute([$pid, $pid, $pid, $pid, $pid, $pid, $pid]);
        }

        // 3. Reset students for the affected departments
        if (!empty($deptsToReset)) {
            $uniqueDepts = array_unique($deptsToReset);
            $placeholders = implode(',', array_fill(0, count($uniqueDepts), '?'));
            
            $resetStudents = $pdo->prepare("
                UPDATE students s
                INNER JOIN programs p ON s.program_id = p.program_id
                SET s.enrollment_type = NULL, 
                    s.selected_load_ids = NULL, 
                    s.is_finished_all = 0
                WHERE p.department IN ($placeholders)
            ");
            $resetStudents->execute($uniqueDepts);
        }
    }

    $pdo->commit();
    echo "Success: Managed " . count($expiredPeriods) . " expired periods at " . $now;

} catch(PDOException $e){
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Transaction Failed: " . $e->getMessage();
}
?>