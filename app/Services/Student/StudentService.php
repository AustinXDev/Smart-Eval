<?php 

namespace App\Services\Student;

use App\Models\Student;
use App\Repositories\StudentRepo\StudentRepository;
use PDOException;
use RuntimeException;

class StudentService
{
  
  private const DEFAULT_PASSWORD = "password123";

  public function __construct(
    private StudentRepository $studentRepo
  )
  {
  }

  public function getById(
    string $studentId
  ): Student {

    $studentId = trim($studentId);

    if ($studentId === '') {
        throw new \RuntimeException(
            "Student ID is required."
        );
    }

    $student = $this->studentRepo->findById(
        $studentId
    );

    if (!$student) {
        throw new \RuntimeException(
            "Student not found."
        );
    }

    return $student;

  }


  public function getAllByDepartment(
    string $department
  ): array {

      $department = trim($department);

      if ($department === '') {
          throw new \RuntimeException(
              "Department is required."
          );
      }

      $students = $this->studentRepo
          ->findAllByDepartment($department);

      $counts = $this->studentRepo
          ->getCountByDepartment($department);

      return [
          'students' => $students,
          'counts' => $counts
      ];
  }
  

  public function createStudent(
    string $studentId,
    string $fullName,
    string $email,
    int $programId,
    int $yearLevel
  ): array {

    $studentId = trim($studentId);
    $fullName = trim($fullName);
    $email = trim($email);

    //Validate input
    if(
      $studentId === '' ||
      $fullName === '' ||
      $email === ''
    ) {

      throw new RuntimeException(
        "All fields are required."
      );

    }

    //Check if student ID or email exist
    $exist = $this->studentRepo->findByStudentIdOrEmail(
      $studentId,
      $email
    );


    if($exist) {
      
      if((int) $exist['is_active'] === 0) {
        return  [
          'status' => 'inactive',
          'message' => 'Student exists but inactive.',
          'student_id' => $exist['student_id']
        ];
      }

      throw new RuntimeException(
        "Student ID or Email already exists."
      );

    }


    //Create new student record
    $success = $this->studentRepo->create(
      $studentId,
      $fullName,
      $email,
      $programId,
      $yearLevel
    ); 


    if(!$success) {

      throw new RuntimeException(
        "Failed to add student."
      );

    }

    return[
      'status' => 'success',
      'message' => 'Student added successfully.'
    ]; 
    
  }

  public function reactivateStudent(
    string $studentId
  ): array {

    if($studentId === ''){
      throw new RuntimeException(
        "Student ID not defined."
      );
    }

    $success = $this->studentRepo->reactive(
      $studentId
    );

    if(!$success){
      throw new RuntimeException(
        "Failed to reactivate student."
      );
    }

    return [
      'status' => 'success',
      'message' => 'Student activated successfully.'
    ];

  }


  //Update Student
  public function update(
    string $oldId,
    string $newID,
    string $fullName,
    string $email,
    int $yearLevel,
    int $programId
  ): array {

    if($oldId === "" ||
      $newID === "" ||
      $fullName === "" ||
      $email === "" ||
      $yearLevel === "" ||
      $programId === ""
    ) {

      throw new RuntimeException(
        "All fields are required."
      );

    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      
      throw new RuntimeException((
        "Invalid email format."
      ));

    }

    if($newID !== $oldId) {
      
      $exist = $this->studentRepo->findById($newID);

      if($exist) {

        throw new RuntimeException(
          "The new Student ID is already taken."
        );

      }

    }


    //check if email already exist
    $emailExist = $this->studentRepo->findByEmail($email);

    if($emailExist) {

      throw new RuntimeException(
        "Email is already used by another student."
      );

    }

    
    $update = $this->studentRepo->edit(
      $newID,
      $oldId,
      $fullName,
      $email,
      $yearLevel,
      $programId,
    );

    if(!$update) {
      throw new RuntimeException(
        "Failed to edit student."
      );
    }

    return [
      'status' => 'success',
      'message' => 'Successfully update student information.'
    ];

  }


  /**
   * Reset password
   */
  public function resetPassword(
    string $studentId,
  ): array {

    if($studentId === '') 
    {

      throw new RuntimeException("Student id is missing.");

    }

    $default_password = self::DEFAULT_PASSWORD;
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

    $student = $this->studentRepo->findById($studentId);

    if(!$student){
      throw new RuntimeException(
        "Student not found."
      );
    }

    if(empty($student->passwordHash)){
      throw new RuntimeException(
        "Student does not have a registered password."
      );
    }

    $reset = $this->studentRepo->resetPassword(
      $studentId,
      $hashed_password
    );

    if(!$reset){
      throw new RuntimeException(
        "Failed to reset password."
      );
    }

    return [
      'status' => 'success',
      'message' => 'Password has been reset to default.'
    ];

  }


  /**
   * Delete Student
   */
  public function delete(
    string $studentId,
    bool $confirmed
  ): array {

    if($studentId === ''){
    
      throw new RuntimeException(
        "Student id is missing."
      );

    }

    $student = $this->studentRepo->findById($studentId);

    if(!$student){

      throw new RuntimeException(
        "Student not found."
      );

    }

    //Check if student have evaluation responses
    $countResponses = $this->studentRepo->countEvaluationResponse($studentId);

    if($countResponses == 0) {
      $delete = $this->studentRepo->delete($studentId, true);

      if(!$delete){
        throw new RuntimeException(
          "Unable to delete student."
        );
      }

      return [
        'status' => 'success',
        'message' => 'Student permanently deleted.'
      ];
    }

    if($countResponses > 0 && !$confirmed){
      return [
        'status' => 'warning',
        'message' => "This student has $countResponses evaluation record(s).",
        'requiresConfirm' => true
      ];
    }

    $inactive = $this->studentRepo->delete($studentId, false);

    if(!$inactive) {
      throw new RuntimeException(
        "Unable to inactive student'."
      );
    }

    return [
      'status' => 'success',
      'message' => 'Student has been set to inactive.'
    ];

  }


  /**
   * Import csv 
   */
  public function importCsv(
    array $rows,
    string $department
  ): array {

    if($department === ''){

      throw new RuntimeException(
        "Department is required"
      );

    }

    $total = count($rows);
    $success = 0;
    $errors = [];

    foreach ($rows as $index => $row) {

      $line = $index + 2;

      /**
       * Check row exactly 5 column
       */
      if(count($row) !== 5) {

        $errors[] = "Row {$line}: Invalid number of columns.";
        continue;

      }

      [
        $studentId,
        $fullName,
        $email,
        $programId,
        $yearLevel
      ] = array_map('trim', $row);


      /**
       * Required fields
       */
      if(
        $studentId === '' ||
        $fullName === '' ||
        $email === '' ||
        $programId === '' ||
        $yearLevel === ''
      ) {

        $errors[] = "Row {$line}: Missing field(s).";
        continue;

      }


      /**
       * validate numeric values
       */
      if(!ctype_digit($programId)) {
        $errors[] = "Row {$line}: Invalid Program ID.";
        continue;
      }

      if(!ctype_digit($yearLevel)) {
        $errors[] = "Row {$line}: Invalid Year Level.";
        continue;
      }


      /**
       * Check program
       */
      if(
        !$this->studentRepo->programExist(
          $programId,
          $department
        )
      ) {

        $errors[] = "Row {$line}: Invalid Program ID for this department.";
        continue;

      }


      /**
       * Check duplicate student
       */
      if(
        $this->studentRepo->existsByStudentIdOrEmail(
          $studentId,
          $email
        )
      ) {

        $errors[] = "Row {$line}: Duplicate Student ID or email";
        continue;

      }

      try {

        $created = $this->studentRepo->create(
          $studentId,
          $fullName,
          $email,
          $programId,
          $yearLevel
        );

        if(!$created) {

          $errors[] = "Row {$line}: Unable to create student.";
          continue;

        }

        $success++;

      } catch (PDOException $e) {

        $errors[] = "Row {$line}: Database error.";
        continue;

      }

    }

    return [
      'total' => $total,
      'success' => $success,
      'failed' => count($errors),
      'errors' => $errors
    ];

  }

}


?>