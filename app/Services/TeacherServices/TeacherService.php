<?php

namespace App\Services\TeacherServices;

use PDO;
use App\Models\Teacher;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\helpers\FileUploader;
use RuntimeException;
use Throwable;

class TeacherService
{
    public function __construct(
        private PDO $pdo,
        private TeacherRepository $teacherRepo,
        private FileUploader $fileUploader
    ) {
    }


    public function create(
        array $data,
        ?array $photo = null
    ): Teacher {

        $employeeId = trim(
            $data['employee_id'] ?? ''
        );

        $fullName = trim(
            $data['full_name'] ?? ''
        );

        $email = trim(
            $data['email'] ?? ''
        );

        $department = trim(
            $data['department'] ?? ''
        );


        /**
         * Validate
         */
        if (
            $employeeId === '' ||
            $fullName === '' ||
            $email === '' ||
            $department === ''
        ) {
            throw new RuntimeException(
                'Missing fields.'
            );
        }

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'Invalid email format.'
            );
        }

        if (mb_strlen($fullName) > 100) {
            throw new RuntimeException(
                'Faculty name must not exceed 100 characters.'
            );
        }


        /**
         * Find existing teacher using
         * employee ID + department
         */
        $existingTeacher =
            $this->teacherRepo
                ->findByIdAndDepartment(
                    $employeeId,
                    $department
                );


        /**
         * Existing active teacher
         */
        if (
            $existingTeacher &&
            $existingTeacher->isActive
        ) {
            throw new RuntimeException(
                'Employee ID already exists in this department.'
            );
        }


        /**
         * Exclude existing inactive teacher
         * from duplicate checks.
         */
        $excludeTeacherId =
            $existingTeacher?->teacherId ?? 0;


        /**
         * Duplicate email
         */
        if (
            $this->teacherRepo
                ->existsByEmailAndDepartment(
                    $email,
                    $department,
                    $excludeTeacherId
                )
        ) {
            throw new RuntimeException(
                'Email already exists in this department.'
            );
        }


        /**
         * Duplicate name
         */
        if (
            $this->teacherRepo
                ->existsByNameAndDepartment(
                    $fullName,
                    $department,
                    $excludeTeacherId
                )
        ) {
            throw new RuntimeException(
                'A teacher with the same name already exists in this department.'
            );
        }


        try {

            $this->pdo->beginTransaction();


            /**
             * Existing image
             */
            $imagePath =
                $existingTeacher?->getImagePath()
                ?: 'default_teacher.png';


            /**
             * Upload new image
             */
            if (
                $photo &&
                !empty($photo['name'])
            ) {
                $imagePath =
                    $this->fileUploader->upload(
                        $photo
                    );
            }


            /**
             * Reactivate inactive teacher
             */
            if (
                $existingTeacher &&
                !$existingTeacher->isActive
            ) {

                $updated =
                    $this->teacherRepo->reactivate(
                        $existingTeacher->teacherId,
                        $employeeId,
                        $fullName,
                        $email,
                        $imagePath
                    );

                if (!$updated) {
                    throw new RuntimeException(
                        'Failed to reactivate teacher.'
                    );
                }

                $this->pdo->commit();

                $teacher =
                    $this->teacherRepo->findById(
                        $existingTeacher->teacherId
                    );

                if (!$teacher) {
                    throw new RuntimeException(
                        'Failed to retrieve reactivated teacher.'
                    );
                }

                return $teacher;
            }


            /**
             * Create new teacher
             */
            $teacher = new Teacher();

            $teacher->employeeId = $employeeId;
            $teacher->fullName = $fullName;
            $teacher->email = $email;
            $teacher->department = $department;
            $teacher->imagePath = $imagePath;
            $teacher->isActive = true;

            $teacher->teacherId =
                $this->teacherRepo->create(
                    $teacher
                );


            $this->pdo->commit();

            return $teacher;


        } catch (\Throwable $e) {

            if (
                $this->pdo->inTransaction()
            ) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }


    /** Columns of the teacher CSV template, in order (photos are added later per teacher). */
    public const CSV_HEADERS = ['employee_id', 'full_name', 'email'];

    /**
     * Imports teachers from CSV rows (header already removed). Every row goes
     * through create(), so the rules match the Add Teacher form: an Employee ID,
     * email or name already used by an active teacher in this department is
     * rejected, and a matching inactive teacher is reactivated. A value repeated
     * inside the file is rejected too (only its first row can be imported).
     * Valid rows are saved even when other rows fail.
     *
     * @param list<array<int, string|null>> $rows
     */
    public function importCsv(array $rows, string $department): array
    {
        $department = strtolower(trim($department));

        if (!in_array($department, ['college', 'shs'], true)) {
            throw new RuntimeException('Invalid department.');
        }

        $labels = [
            'employee_id' => 'Employee ID',
            'email'       => 'Email',
            'full_name'   => 'Name',
        ];
        $firstRow = ['employee_id' => [], 'email' => [], 'full_name' => []];
        $created = 0;
        $reactivated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // row 1 is the header

            $cells = array_map(static fn ($value): string => trim((string) $value), $row);

            // Spreadsheet apps often save empty trailing cells ("a,b,c,,").
            while (count($cells) > count(self::CSV_HEADERS) && end($cells) === '') {
                array_pop($cells);
            }

            $values = [
                'employee_id' => $cells[0] ?? '',
                'full_name'   => preg_replace('/\s+/u', ' ', $cells[1] ?? '') ?? '',
                'email'       => $cells[2] ?? '',
            ];

            $fail = static function (string $message) use (&$errors, $line, $values): void {
                $errors[] = [
                    'row'         => $line,
                    'employee_id' => mb_substr($values['employee_id'], 0, 40),
                    'full_name'   => mb_substr($values['full_name'], 0, 100),
                    'message'     => $message,
                ];
            };

            if (count($cells) !== count(self::CSV_HEADERS)) {
                $fail('Expected 3 columns (employee_id, full_name, email) but found ' . count($cells) . '.');
                continue;
            }

            // Repeated inside the file: compare case-insensitively, like the database does.
            $repeated = [];
            foreach ($firstRow as $field => $seen) {
                $key = mb_strtolower($values[$field]);
                if ($key === '') {
                    continue;
                }
                if (isset($seen[$key])) {
                    $repeated[] = "{$labels[$field]} is repeated (first used on row {$seen[$key]})";
                } else {
                    $firstRow[$field][$key] = $line;
                }
            }

            $missing = array_keys(array_filter($values, static fn (string $value): bool => $value === ''));
            if ($missing) {
                $fail('Missing ' . implode(', ', array_map(static fn ($f) => $labels[$f], $missing)) . '.');
                continue;
            }

            if ($repeated) {
                $fail(implode('; ', $repeated) . '.');
                continue;
            }

            if (mb_strlen($values['employee_id']) > 20) {
                $fail('Employee ID must not exceed 20 characters.');
                continue;
            }

            if (mb_strlen($values['email']) > 100) {
                $fail('Email must not exceed 100 characters.');
                continue;
            }

            try {
                $existing = $this->teacherRepo->findByIdAndDepartment($values['employee_id'], $department);

                $this->create($values + ['department' => $department]);

                if ($existing && !$existing->isActive) {
                    $reactivated++;
                } else {
                    $created++;
                }
            } catch (RuntimeException $e) {
                $fail($e->getMessage());
            } catch (\PDOException $e) {
                // e.g. a unique key hit by a teacher added at the same moment
                $fail($e->getCode() === '23000'
                    ? 'Employee ID or email already exists in this department.'
                    : 'Unable to save this teacher.');
            }
        }

        return [
            'total'       => count($rows),
            'success'     => $created + $reactivated,
            'created'     => $created,
            'reactivated' => $reactivated,
            'failed'      => count($errors),
            'errors'      => $errors,
        ];
    }


    /**
     * update teacher information
     */
    public function update(
        array $input,
        ?array $photo = null
    ): array {


        $teacherId = (int) ($input['teacher_id'] ?? 0);
        $employeeId = trim($input['employee_id'] ?? '');
        $fullName = trim($input['full_name'] ?? '');
        $department = trim($input['department'] ?? '');
        $email = trim($input['email'] ?? '');

        /**
         * Validate
         */
        if ($teacherId <= 0) {
            throw new RuntimeException(
                'Teacher ID is required.'
            );
        }

        if ($employeeId === '') {
            throw new RuntimeException(
                'Employee ID cannot be blank.'
            );
        }

        if ($fullName === '') {
            throw new RuntimeException(
                'Full name cannot be blank.'
            );
        }

        if (
            $email === ''
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new RuntimeException(
                'Please enter a valid email address.'
            );
        }


        /**
         * Find teacher
         */
        $teacher = $this->teacherRepo->findById($teacherId);

        if (!$teacher) {
            throw new RuntimeException(
                "Teacher not found."
            );
        }

        /**
         * Duplicate employee ID
         */
        if (
            $this->teacherRepo->existsByEmployeeIdAndDepartment(
                $employeeId,
                $department,
                $teacherId
            )
        ) {
            throw new RuntimeException(
                'Employee ID already exists for another teacher.'
            );
        }


        /**
         *Duplicate by email and department
         */
        if (
            $this->teacherRepo->existsByEmailAndDepartment(
                $email,
                $department,
                $teacherId
            )
        ) {

            throw new RuntimeException(
                'Email already exists for another teacher.'
            );

        }

        // Current image
        $imagePath = $teacher->getImagePath()
            ?: 'default_teacher.png';

        $newImage = null;

        if (
            $photo !== null
            && ($photo['error'] ?? UPLOAD_ERR_NO_FILE)
                === UPLOAD_ERR_OK
        ) {

            $newImage = $this->fileUploader->upload(
                $photo
            );

            $imagePath = $newImage;
        }

        /**
         * Update
         */
        $updated = $this->teacherRepo->update(
            $teacherId,
            $employeeId,
            $fullName,
            $email,
            $imagePath
        );

        if (!$updated) {

            if ($newImage !== null) {
                $this->fileUploader->delete(
                    $newImage
                );
            }

            throw new RuntimeException(
                'Failed to update teacher.'
            );

        }

        /**
         * Delete old image
         */
        if (
            $newImage !== null
            && $teacher->getImagePath()
            && $teacher->getImagePath()
              !== 'default_teacher.png'
        ) {

            $this->fileUploader->delete(
                $teacher->getImagePath()
            );

        }

        return [
          'teacher_id' => $teacherId,
          'employee_id' => $employeeId,
          'full_name' => $fullName,
          'email' => $email,
          'image_path' => $imagePath
        ];

    }


    /**
     * Delete or inactive teacher
     */
    public function delete(
        int $teacherId
    ): array {

        if ($teacherId <= 0) {
            throw new RuntimeException(
                'Invalid teacher ID'
            );
        }

        //Find teacher
        $teacher = $this->teacherRepo->findById($teacherId);

        if (!$teacher) {

            throw new RuntimeException(
                "Teacher not found"
            );

        }

        //Already Inactive
        if (!$teacher->isActive) {
            throw new RuntimeException(
                'This teacher is already deactivated.'
            );
        }

        //Active Evaluation protection
        if ($this->teacherRepo
                ->hasActiveEvaluation($teacherId)
        ) {

            throw new RuntimeException(
                'Cannot delete teacher because the teacher is currently part of an active evaluation.'
            );

        }


        //Check evaluation history
        $feedbackCount = $this->teacherRepo
                              ->countEvaluationHistory($teacherId);

        if ($feedbackCount >= 0) {

            $this->teacherRepo
                ->deactivate($teacherId);

            return[
              'action' => 'deactivated',
              'message' =>
                "Teacher has {$feedbackCount} evaluation records. "
                . "Teacher was deactivated instead of permanently deleted.",
              'feedback_count' => $feedbackCount
            ];

        }

        /**
         * No history
         * Permanently Deleted
         */
        $this->pdo->beginTransaction();

        try {

            // Delete database record first
            $this->teacherRepo->delete($teacherId);

            // Delete uploaded image
            $this->fileUploader->delete(
                $teacher->getImagePath()
            );

            $this->pdo->commit();

            return [
                'action' => 'deleted',
                'message' => 'Teacher and profile image deleted successfully.',
                'feedback_count' => 0
            ];

        } catch (Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }

    }



    /**
     * Get all teachers and count
     */
    public function getTeacherList(string $department, array $query): array
    {
        $department = trim($department);
        if ($department === '') {
            throw new RuntimeException('Department is required.');
        }
        $counts = $this->teacherRepo->getCountByDepartment($department);
        if (($query['counts_only'] ?? '') === '1') {
            return ['counts' => $counts];
        }
        $search = $query['search'] ?? '';
        $search = is_array($search) ? ($search['value'] ?? '') : $search;
        $status = $query['status'] ?? 'All';
        if (!is_string($search) || !in_array($status, ['All', 'Active', 'Inactive'], true)) {
            throw new RuntimeException('Invalid teacher filters.');
        }
        foreach (['draw', 'start', 'length'] as $key) {
            if (isset($query[$key]) && filter_var($query[$key], FILTER_VALIDATE_INT) === false) {
                throw new RuntimeException('Invalid pagination parameters.');
            }
        }
        $page = $this->teacherRepo->findPageByDepartment(
            $department,
            max(0, (int) ($query['start'] ?? 0)),
            max(1, min(100, (int) ($query['length'] ?? 5))),
            trim($search), $status
        );
        return [
            'draw' => max(0, (int) ($query['draw'] ?? 0)),
            'recordsTotal' => (int) $counts['total'],
            'recordsFiltered' => $page['filtered'],
            'data' => $page['teachers'],
            // Same counts as ?counts_only=1, so the KPI cards update with every list load.
            'counts' => $counts,
        ];
    }

    public function getAll(
        string $department
    ): array {

        $department = trim($department);

        if ($department === '') {
            throw new RuntimeException(
                'Department is required.'
            );
        }

        $teachers = $this->teacherRepo
                        ->findAllByDepartment($department);

        $counts = $this->teacherRepo
                      ->getCountByDepartment($department);

        return [
          'teachers' => array_map(
              fn (Teacher $teacher) => $teacher->toArray(),
              $teachers
          ),
          'counts' => $counts
        ];

    }


    /**
     * Get a single teacher
     */
    public function getById(
        int $teacherId
    ): array {

        if ($teacherId <= 0) {

            throw new RuntimeException(
                "Invalid teachers ID."
            );

        }

        $teacher = $this->teacherRepo
                        ->findById($teacherId);

        if (!$teacher) {
            throw new RuntimeException(
                "Teacher not found."
            );
        }

        $handles = $this->teacherRepo
                        ->findHandlesByTeacher($teacherId);

        $teacher->setHandles($handles);

        return $teacher->toArray();

    }


}
