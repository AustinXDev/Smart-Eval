<?php 

require_once __DIR__ . '/../../app/init.php';

use App\Repositories\StudentRepo\StudentRepository;
use App\Services\Student\StudentService;
use App\Controllers\students\StudentController;

header('Content-type: application/json');

try {

  $department = trim($_POST['department'] ?? '');

  if($department === '') {
    throw new RuntimeException(
      "Department is missing."
    );
  }


  /**
   * Check upload csv file
   */
  if(
    !isset($_FILES['csv']) ||
    $_FILES['csv']['error'] !== UPLOAD_ERR_OK
  ) {

    throw new RuntimeException(
      "No file uploaded or upload error."
    );

  }

  $file = $_FILES['csv']['tmp_name'];
  $filename = $_FILES['csv']['name'];

  /**
   * Validate file extension
   */
  if(
    strtolower(
      pathinfo($filename, PATHINFO_EXTENSION)
    ) !== 'csv'
  ) {

    throw new RuntimeException(
      "Invalid file type. Only CSV files are allowed."
    );

  }


  /**
   * Open CSV
   */
  $handle = fopen($file, 'r');

  if($handle === false) {
    throw new RuntimeException(
      "Unable to open CSV file."
    );
  }

  /**
   * Expected content
   */
  $expectedHeaders = [
    'student_id',
    'full_name',
    'email',
    'program_id',
    'year_level'
  ];


  /**
   * Read headers
   */
  $headers = fgetcsv($handle);

  if($headers === false) {
    fclose($handle);

    throw new RuntimeException(
      "CSV file is empty"
    );
  }


  /**
   * Normalize headers
   */
  $headers = array_map(
    fn($header) => trim($header),
    $headers
  );


  if($headers !== $expectedHeaders) {
    fclose($handle);

    throw new RuntimeException(
      "Invalid template. CSV columns must be " . 
      implode(', ', $expectedHeaders)
    );

  }


  /**
   * Read rows
   */
  $rows = [];

  while (($row = fgetcsv($handle)) !== false) {

    // Ignore the empty rows
    if(
      count($row) === 1 &&
      trim($row[0]) === ''
    ) {

      continue;

    }

    $rows[] = $row;

  }

  fclose($handle);

  /**
   * Call dependencies
   */
  require_once __DIR__ . '/../../app/config/database.php';

  $studentRepo = new StudentRepository($pdo);

  $service = new StudentService($studentRepo);

  $controller = new StudentController($service);


  $response = $controller->importCsv(
    $rows,
    $department
  );


  echo json_encode([
    'status' => 'success',
    ...$response
  ]);


} catch (Throwable $e) {

  http_response_code(400);

  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
  ]);

}

?>