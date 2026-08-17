<?php 

namespace App\Models;

use DateTime;

class Student
{
  public string $studentId;
  public string $fullName;
  public string $email;
  public ?int $programId;
  public ?string $yearLevel;
  public ?string $enrollmentType;
  public ?string $accountStatus;
  public bool $isActive;
  public ?string $tokenExpires;
  public bool $isFinishedAll;
  public ?string $passwordHash;

  public static function fromArray(array $row): self 
  {
    $student = new self();

    $student->studentId      = 
      (string)($row['student_id'] ?? '');

    $student->fullName       = 
      (string)($row['full_name'] ?? '');

    $student->email          = 
      (string)($row['email'] ?? '');

    $student->programId      = 
      isset($row['program_id']) 
        ? (int)$row['program_id'] 
        : null;

    $student->yearLevel      = 
      $row['year_level'] ?? null;

    $student->enrollmentType = 
      $row['enrollment_type'] ?? null;

    $student->accountStatus  = 
      $row['account_status'] ?? null;

    $student->isActive       = 
      !empty($row['is_active']);

    $student->tokenExpires   = 
      $row['token_expires'] ?? null;

    $student->isFinishedAll  = 
      !empty($row['is_finished_all']);

    $student->passwordHash   = 
      $row['password_hash'] ?? null;

    return $student;

  }

  /**
   * Only the fields that are safe/useful to keep in the session.
   */
  
  public function toSessionArray(): array
  {
      return [
          'student_id'      => $this->studentId,
          'full_name'       => $this->fullName,
          'program_id'      => $this->programId,
          'year_level'      => $this->yearLevel,
          'enrollment_type' => $this->enrollmentType,
          'is_finished_all' => $this->isFinishedAll ? 1 : 0,
      ];
  }

  /**
   * Activate account
   */
  public function activate(): void {
    $this->isActive = true;
  }

}

?>