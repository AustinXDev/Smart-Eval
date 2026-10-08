<?php

declare(strict_types=1);

use App\Repositories\ProgramRepo\ProgramRepository;

require_once __DIR__ . '/../../app/init.php';
require_once __DIR__ . '/../../app/config/database.php';

$department = trim($_GET['department'] ?? '');

if ($department === '') {
    http_response_code(400);
    exit('Department is required.');
}

$programRepo = new ProgramRepository($pdo);

$programs = $programRepo->getByDepartment($department);

$filename = 'program_reference_guide.csv';

header('Content-Type: text/csv; charset=utf-8');
header(
    'Content-Disposition: attachment; filename="' . $filename . '"'
);
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

if ($output === false) {
    http_response_code(500);
    exit('Unable to generate CSV.');
}

fputcsv($output, [
    'program_id',
    'program_name'
]);

foreach ($programs as $program) {
    fputcsv($output, [
        $program['program_id'],
        $program['program_name']
    ]);
}

fclose($output);
exit;
?>