<?php 
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/config/path.php';

require_once __DIR__ . '/../app/Controllers/reports/ReportController.php';

$type = $_GET['type'] ?? null;
$params = $_GET;

$controller = new ReportController();
$controller->generate($type, $params);
?>