<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/api_bootstrap.php';

// Teacher import template. Photos are not part of the CSV.
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="teacher_template.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

if ($output === false) {
    http_response_code(500);
    exit('Unable to generate CSV template.');
}

fputcsv($output, ['employee_id', 'full_name', 'email'], ',', '"', '');
fputcsv($output, ['EMP-0001', 'Maria Santos', 'maria.santos@example.com'], ',', '"', '');

fclose($output);
exit;
