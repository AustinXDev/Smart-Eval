<?php
$filename = "students_format.csv";

// Force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen("php://output", "w");

// CSV headers (your format)
fputcsv($output, ['student_id', 'full_name', 'email', 'program_id', 'year_level']);

// Optional: sample row
fputcsv($output, ['2024-001', 'Juan Dela Cruz', 'juan@gmail.com', '1', '3']);
fputcsv($output, ['2024-001', 'Juan Dela Cruz', 'juan@gmail.com', '1', '11']);

fclose($output);
exit;
?>