<?php 
require_once __DIR__ . '/../app/config/database.php';
date_default_timezone_set('Asia/Manila');

$now = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    // 1. Activate periods within schedule
    $activate = $pdo->prepare("
        UPDATE evaluation_periods
        SET is_active = 1
        WHERE start_date <= ?
        AND end_date >= ?
        AND is_closed = 0
        AND is_forced = 0
    ");
    $activate->execute([$now, $now]);

    // 2. Deactivate expired periods
    $deactivate = $pdo->prepare("
        UPDATE evaluation_periods
        SET is_active = 0
        WHERE end_date < ?
        AND is_forced = 0
    ");
    $deactivate->execute([$now]);

    $pdo->commit();

    echo "Auto-update successful at: " . $now;

} catch(PDOException $e){
    $pdo->rollBack();
    echo "Error updating periods: " . $e->getMessage();
}
?>