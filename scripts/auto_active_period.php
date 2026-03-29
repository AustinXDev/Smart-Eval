<?php 
require_once __DIR__ . '/../app/config/database.php';

date_default_timezone_set('Asia/Manila');
$now = date('Y-m-d H:i:s');

try{
  // Activate ONLY ONE per department (latest start_date)
  $activate = $pdo->prepare("
    UPDATE evaluation_periods ep
    JOIN (
      SELECT target_dept, MAX(start_date) as latest_start
      FROM evaluation_periods
      WHERE start_date <= ? AND end_date >= ?
      GROUP BY target_dept
    ) latest
    ON ep.target_dept = latest.target_dept
    AND ep.start_date = latest.latest_start
    SET ep.is_active = 1
  ");
  $activate->execute([$now, $now]);

  // Deactivate all others
  $deactivate = $pdo->prepare("
    UPDATE evaluation_periods
    SET is_active = 0
    WHERE NOT (start_date <= ? AND end_date >= ?)
  ");
  $deactivate->execute([$now, $now]);

  echo "Updated at: " . $now;
} catch (PDOException $e){
  echo "Error updating periods: " . $e->getMessage() . "\n";
}
?>