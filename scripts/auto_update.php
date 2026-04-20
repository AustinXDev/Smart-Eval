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

            $snapshotStmt = $pdo->prepare("
                UPDATE evaluation_periods ep
                SET
                    -- Mean Score
                    ep.final_average = (
                        SELECT ROUND(AVG(ea.score), 2)
                        FROM evaluation_answers ea
                        INNER JOIN evaluation_status es ON ea.eval_id = es.eval_id
                        WHERE es.period_id = ?
                    ),
                    -- Total Students who finished ALL their assigned loads
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
                    -- Participation Rate (Float math)
                    ep.participation_rate = (
                        SELECT ROUND(
                            (COUNT(DISTINCT CASE WHEN fin.is_complete = 1 THEN es_out.student_id END) * 100.0) / 
                            NULLIF(COUNT(DISTINCT es_out.student_id), 0), 2
                        )
                        FROM evaluation_status es_out
                        LEFT JOIN (
                            SELECT es_sub.student_id, 1 as is_complete
                            FROM evaluation_status es_sub
                            WHERE es_sub.period_id = ? AND es_sub.is_submitted = 1
                            GROUP BY es_sub.student_id
                            HAVING COUNT(es_sub.load_id) = (
                                SELECT COUNT(*) FROM evaluation_status es_check
                                WHERE es_check.student_id = es_sub.student_id 
                                  AND es_check.period_id = ?
                            ) 
                        ) fin ON fin.student_id = es_out.student_id
                        WHERE es_out.period_id = ?
                    ), 
                    ep.is_active = 0
                WHERE ep.period_id = ?
            ");

            // 7 placeholders for the specific Period ID
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