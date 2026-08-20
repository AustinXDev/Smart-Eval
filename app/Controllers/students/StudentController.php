<?php 

namespace App\Controllers\students;

use App\Models\Student;
use App\Services\Student\StudentService;

class StudentController{

    public function __construct(
        private StudentService $service
    )
    {
    }

    /**
     * Get student by Id
     */
    public function getById(
        string $studentId
    ): Student {

        return $this->service->getById(
            $studentId
        );

    }


    /**
     * Get all student by department
     */
    public function getAllByDepartment(
    string $department
    ): array {

        return $this->service->getAllByDepartment(
            $department
        );
    }


    /**
     * Create new student
     */
    public function create(
        string $studentId,
        string $fullName,
        string $email,
        int $programId,
        int $yearLevel
    ): array {

        return $this->service->createStudent(
            $studentId,
            $fullName,
            $email,
            $programId,
            $yearLevel
        );

    }


    /**
     * Reactivate Student
     */
    public function reactivate(
        string $studentId
    ): array {

        return $this->service->reactivateStudent(
            $studentId
        );

    }


    /**
     * Update Student Information
     */
    public function update(
        string $oldId,
        string $newId, 
        string $fullName,
        string $email,
        int $programId,
        int $yearLevel
    ): array {

        return $this->service->update(
            $oldId,
            $newId,
            $fullName,
            $email,
            $yearLevel,
            $programId
        );

    }


    /**
     * Reset student password
     */
    public function resetPassword(
        string $studentId
    ): array {

        return $this->service->resetPassword(
            $studentId
        );

    }


    /**
     * Delete student record
     */
    public function delete(
        string $studentId,
        bool $confirmed
    ): array {

        return $this->service->delete(
            $studentId,
            $confirmed
        );

    }


    /**
     * Import student csv
     */
    public function importCsv(
        array $rows,
        string $department
    ): array {

        return $this->service->importCsv(
            $rows,
            $department
        );

    }



}

?>