<?php

require_once dirname(__DIR__, 2) . '/app/api_bootstrap.php';

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\teachers\TeacherController;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Services\TeacherServices\TeacherService;
use App\helpers\FileUploader;

header('Content-type: application/json; charset=utf-8');

/**
 * Import teachers from a CSV file (columns: employee_id, full_name, email).
 * Photos cannot travel in a CSV; imported teachers get the default photo and
 * an admin can upload one later from Edit Teacher.
 */
const TEACHER_CSV_MAX_ROWS = 1000;
const TEACHER_CSV_MAX_BYTES = 2 * 1024 * 1024;

try {

  $department = strtolower(trim((string) ($_POST['department'] ?? '')));

  if (!in_array($department, ['college', 'shs'], true)) {
    throw new RuntimeException('Department is missing or invalid.');
  }

  $upload = $_FILES['csv'] ?? null;

  if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    throw new RuntimeException('No file uploaded or upload error.');
  }

  if ((int) ($upload['size'] ?? 0) > TEACHER_CSV_MAX_BYTES) {
    throw new RuntimeException('CSV must be less than 2MB.');
  }

  if (strtolower(pathinfo((string) $upload['name'], PATHINFO_EXTENSION)) !== 'csv') {
    throw new RuntimeException('Invalid file type. Only CSV files are allowed.');
  }

  $handle = fopen($upload['tmp_name'], 'r');

  if ($handle === false) {
    throw new RuntimeException('Unable to open CSV file.');
  }

  try {

    // Excel saves "CSV UTF-8" with a byte-order mark and plain "CSV" in
    // Windows-1252, so names such as "Peña" arrive in either encoding.
    $toUtf8 = static function ($value): string {
      $value = (string) $value;
      return mb_check_encoding($value, 'UTF-8')
        ? $value
        : mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    };

    $headers = fgetcsv($handle, 0, ',', '"', '');

    if ($headers === false || $headers === [null]) {
      throw new RuntimeException('CSV file is empty.');
    }

    $headers = array_map(
      static fn ($header) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header))),
      $headers
    );

    // Ignore empty trailing header cells left by spreadsheet apps.
    while ($headers && end($headers) === '') {
      array_pop($headers);
    }

    if ($headers !== TeacherService::CSV_HEADERS) {
      throw new RuntimeException(
        'Invalid template. CSV columns must be ' . implode(', ', TeacherService::CSV_HEADERS)
        . '. Download the template and try again.'
      );
    }

    $rows = [];

    while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {

      // Skip blank lines (including ",,," rows from spreadsheets)
      if (implode('', array_map(static fn ($v) => trim((string) $v), $row)) === '') {
        continue;
      }

      if (count($rows) >= TEACHER_CSV_MAX_ROWS) {
        throw new RuntimeException('CSV must contain no more than ' . TEACHER_CSV_MAX_ROWS . ' teacher rows.');
      }

      $rows[] = array_map($toUtf8, $row);

    }

  } finally {
    fclose($handle);
  }

  if ($rows === []) {
    throw new RuntimeException('The CSV has no teacher rows below the header.');
  }

  require_once __DIR__ . '/../../app/config/database.php';

  $service = new TeacherService(
    $pdo,
    new TeacherRepository($pdo),
    new FileUploader(ROOT_PATH . '/public/uploads/teachers/')
  );

  $controller = new TeacherController($service);

  $result = $controller->importCsv($rows, $department);

  echo json_encode([
    'status' => 'success',
    'message' => $result['success'] > 0
      ? "{$result['success']} of {$result['total']} teachers imported."
      : 'No teachers were imported.',
    ...$result
  ]);

} catch (\Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status' => 'error',
    'message' => \App\middleware\ApiSecurity::publicMessage($e)
  ]);

}
