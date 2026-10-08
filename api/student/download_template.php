<?php

declare(strict_types=1);

$filename = "student_template.csv";

header('Content-Type: text/csv; charset=utf-8');
header(
    'Content-Disposition: attachment; filename="' . $filename . '"'
);
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

if ($output === false) {
    http_response_code(500);
    exit('Unable to generate CSV template.');
}

// CSV headers
fputcsv($output, [
    'student_id',
    'full_name',
    'email',
    'program_id',
    'year_level'
]);

// Sample data
fputcsv($output, [
    '20-0101',
    'Juan Dela Cruz',
    'juan@gmail.com',
    '1',
    '3'
]);

fclose($output);
exit;