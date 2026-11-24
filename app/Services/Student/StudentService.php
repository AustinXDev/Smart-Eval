<?php

namespace App\Services\Student;

use App\Models\Student;
use App\Repositories\StudentRepo\StudentRepository;
use PDOException;
use RuntimeException;

class StudentService
{

    public function __construct(
        private StudentRepository $studentRepo,
        private ?\App\Services\ForgotPasswordServices\ForgotPasswordServices $passwordRecovery = null
    ) {
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


    /**
     * Year levels offered per department (same options as the add-student form).
     *
     * @return list<string>
     */
    private static function yearLevelsFor(string $department): array
    {
        return strtolower($department) === 'shs' ? ['11', '12'] : ['1', '2', '3', '4'];
    }

    public function getStudentList(string $department, array $query): array
    {
        $department = trim($department);
        if ($department === '') {
            throw new RuntimeException('Department is required.');
        }
        $counts = $this->studentRepo->getCountByDepartment($department);
        $yearLevels = self::yearLevelsFor($department);
        if (($query['counts_only'] ?? '') === '1') {
            // Every year level of the department is listed, including empty ones.
            $byYear = $this->studentRepo->getYearLevelCountsByDepartment($department);
            return [
                'counts' => $counts,
                'year_levels' => array_map(
                    static fn (string $level): array => ['year_level' => $level, 'total' => $byYear[$level] ?? 0],
                    $yearLevels
                ),
            ];
        }
        $search = $query['search'] ?? [];
        $search = is_array($search) ? ($search['value'] ?? '') : $search;
        $status = $query['status'] ?? 'All';
        $program = $query['program'] ?? 'All';
        $yearLevel = $query['year_level'] ?? '';
        if (
            !is_string($search) || !is_string($program) || !is_string($yearLevel)
            || !in_array($status, ['All', 'Active', 'Inactive'], true)
            || ($yearLevel !== '' && !in_array($yearLevel, $yearLevels, true))
        ) {
            throw new RuntimeException('Invalid student filters.');
        }
        $page = $this->studentRepo->findPageByDepartment(
            $department,
            max(0, (int) ($query['start'] ?? 0)),
            max(1, min(100, (int) ($query['length'] ?? 5))),
            trim($search), $status, $program, $yearLevel
        );
        return [
            'draw' => max(0, (int) ($query['draw'] ?? 0)),
            'recordsTotal' => (int) $counts['active'],
            'recordsFiltered' => $page['filtered'],
            'data' => $page['students'],
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
        if (
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


        if ($exist) {

            if ((int) $exist['is_active'] === 0) {
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


        if (!$success) {

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

        if ($studentId === '') {
            throw new RuntimeException(
                "Student ID not defined."
            );
        }

        $success = $this->studentRepo->reactive(
            $studentId
        );

        if (!$success) {
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

        if ($oldId === "" ||
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

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            throw new RuntimeException((
                "Invalid email format."
            ));

        }

        if ($newID !== $oldId) {

            $exist = $this->studentRepo->findById($newID);

            if ($exist) {

                throw new RuntimeException(
                    "The new Student ID is already taken."
                );

            }

        }


        //check if email already exist
        $emailExist = $this->studentRepo->findByEmail($email);

        if ($emailExist) {

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

        if (!$update) {
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

        if ($studentId === '') {

            throw new RuntimeException("Student id is missing.");

        }

        $student = $this->studentRepo->findById($studentId);
        if (!$student || !$student->hasPassword()) {
            throw new RuntimeException('Student does not have a registered password.');
        }
        if (!$this->passwordRecovery) {
            throw new RuntimeException('Password recovery service is unavailable.');
        }
        $this->passwordRecovery->sendResetLink($student->email, $_SERVER['REMOTE_ADDR'] ?? '');
        return ['status' => 'success', 'message' => 'A password reset link has been requested for the registered email.'];
    }


    /**
     * Delete Student
     */
    public function delete(
        string $studentId,
        bool $confirmed
    ): array {

        if ($studentId === '') {

            throw new RuntimeException(
                "Student id is missing."
            );

        }

        $student = $this->studentRepo->findById($studentId);

        if (!$student) {

            throw new RuntimeException(
                "Student not found."
            );

        }

        //Check if student have evaluation responses
        $countResponses = $this->studentRepo->countEvaluationResponse($studentId);

        if ($countResponses == 0) {
            $delete = $this->studentRepo->delete($studentId, true);

            if (!$delete) {
                throw new RuntimeException(
                    "Unable to delete student."
                );
            }

            return [
              'status' => 'success',
              'message' => 'Student permanently deleted.'
            ];
        }

        if ($countResponses > 0 && !$confirmed) {
            return [
              'status' => 'warning',
              'message' => "This student has $countResponses evaluation record(s).",
              'requiresConfirm' => true
            ];
        }

        $inactive = $this->studentRepo->delete($studentId, false);

        if (!$inactive) {
            throw new RuntimeException(
                "Unable to inactive student'."
            );
        }

        return [
          'status' => 'success',
          'message' => 'Student has been set to inactive.'
        ];

    }


    /** Columns of the student CSV template, in order. */
    public const CSV_HEADERS = ['student_id', 'full_name', 'email', 'program_id', 'year_level'];

    /**
     * Imports students from CSV rows (header already removed). Valid rows are
     * saved even when other rows fail; each failed row is reported with its
     * row number, Student ID, name and reason for the import summary.
     */
    public function importCsv(
        array $rows,
        string $department
    ): array {

        if ($department === '') {
            throw new RuntimeException('Department is required');
        }

        $columns = count(self::CSV_HEADERS);
        $firstRow = ['student_id' => [], 'email' => []];
        $labels = ['student_id' => 'Student ID', 'email' => 'Email'];
        $success = 0;
        $errors = [];

        foreach ($rows as $index => $row) {

            $line = $index + 2; // row 1 is the header

            $cells = array_map(static fn ($value): string => trim((string) $value), $row);

            // Spreadsheet apps often save empty trailing cells ("a,b,c,d,e,,").
            while (count($cells) > $columns && end($cells) === '') {
                array_pop($cells);
            }

            [$studentId, $fullName, $email, $programId, $yearLevel] = array_pad($cells, $columns, '');
            $fullName = preg_replace('/\s+/u', ' ', $fullName) ?? $fullName;

            $fail = static function (string $message) use (&$errors, $line, $studentId, $fullName): void {
                $errors[] = [
                    'row'        => $line,
                    'student_id' => mb_substr($studentId, 0, 40),
                    'full_name'  => mb_substr($fullName, 0, 100),
                    'message'    => $message,
                ];
            };

            if (count($cells) !== $columns) {
                $fail("Expected {$columns} columns (" . implode(', ', self::CSV_HEADERS) . ') but found ' . count($cells) . '.');
                continue;
            }

            // Repeated inside the file (case-insensitive, like the database).
            $repeated = [];
            foreach (['student_id' => $studentId, 'email' => $email] as $field => $value) {
                $key = mb_strtolower($value);
                if ($key === '') {
                    continue;
                }
                if (isset($firstRow[$field][$key])) {
                    $repeated[] = "{$labels[$field]} is repeated (first used on row {$firstRow[$field][$key]})";
                } else {
                    $firstRow[$field][$key] = $line;
                }
            }

            $missing = array_keys(array_filter([
                'Student ID' => $studentId,
                'Name'       => $fullName,
                'Email'      => $email,
                'Program'    => $programId,
                'Year Level' => $yearLevel,
            ], static fn (string $value): bool => $value === ''));

            if ($missing) {
                $fail('Missing ' . implode(', ', $missing) . '.');
                continue;
            }

            if ($repeated) {
                $fail(implode('; ', $repeated) . '.');
                continue;
            }

            if (mb_strlen($studentId) > 20) {
                $fail('Student ID must not exceed 20 characters.');
                continue;
            }

            if (mb_strlen($fullName) > 100) {
                $fail('Name must not exceed 100 characters.');
                continue;
            }

            if (mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $fail('Invalid email format.');
                continue;
            }

            if (!ctype_digit($programId)) {
                $fail('Program must be the program number from the Program Guide.');
                continue;
            }

            if (!ctype_digit($yearLevel)) {
                $fail('Year Level must be a whole number.');
                continue;
            }

            if (!$this->studentRepo->programExist($programId, $department)) {
                $fail('Program number does not exist in this department.');
                continue;
            }

            if ($this->studentRepo->existsByStudentIdOrEmail($studentId, $email)) {
                $fail('Student ID or email already exists.');
                continue;
            }

            try {

                if (!$this->studentRepo->create($studentId, $fullName, $email, $programId, $yearLevel)) {
                    $fail('Unable to create student.');
                    continue;
                }

                $success++;

            } catch (PDOException $e) {

                $fail($e->getCode() === '23000'
                    ? 'Student ID or email already exists.'
                    : 'Unable to save this student.');

            }

        }

        return [
          'total'   => count($rows),
          'success' => $success,
          'failed'  => count($errors),
          'errors'  => $errors
        ];

    }




}
