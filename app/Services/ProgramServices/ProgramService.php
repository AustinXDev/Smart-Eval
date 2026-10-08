<?php

namespace App\Services\ProgramServices;

use App\Models\Program;
use App\Repositories\ProgramRepo\ProgramRepository;
use PDO;
use RuntimeException;

class ProgramService
{
    public function __construct(
        private PDO $pdo,
        private ProgramRepository $programRepo
    ) {
    }

    public function getProgramByDepartment(
        string $department
    ): array {

        $department = trim($department);

        if ($department === '') {

            throw new RuntimeException(
                "Department is missing."
            );

        }

        return $this->programRepo->getProgramByDepartment($department);

    }


    public function getAllProgram(): array
    {
        $programs = $this->programRepo->getAllProgram();

        $count = $this->programRepo->getCount();

        return [
            'programs' => array_map(
                fn (Program $program): array => $program->toArray(),
                $programs
            ),
            'count' => $count
        ];
    }


    /**
     * Create Service
     */
    public function create(array $data): Program
    {
        $programCode = trim($data['program_code'] ?? '');
        $programName = trim($data['program_name'] ?? '');
        $department  = trim($data['department'] ?? '');

        // Required fields
        if (
            $programCode === '' ||
            $programName === '' ||
            $department === ''
        ) {
            throw new RuntimeException(
                'Missing required fields.'
            );
        }

        // Program code length
        if (strlen($programCode) < 2 || strlen($programCode) > 15) {
            throw new RuntimeException(
                'Program Code must be 2-15 characters long.'
            );
        }

        // Program name length
        if (strlen($programName) < 10 || strlen($programName) > 100) {
            throw new RuntimeException(
                'Program Name must be 10-100 characters long.'
            );
        }

        // Find existing program by code
        $existingProgram = $this->programRepo->findByCode(
            $programCode
        );

        // If code already exists and is active
        if ($existingProgram && $existingProgram->isActive()) {
            throw new RuntimeException(
                "Program Code '$programCode' is already registered."
            );
        }



        // Check if program name is already used by another active program
        $existingByName = $this->programRepo->findActiveByName(
            $programName
        );

        if (
            $existingByName &&
            $existingByName->getProgramId() !==
            $existingProgram?->getProgramId()
        ) {
            throw new RuntimeException(
                "Program Name '$programName' is already registered."
            );
        }

        try {

            $this->pdo->beginTransaction();

            /*
             * Existing inactive program
             * → Reactivate
             */
            if ($existingProgram && !$existingProgram->isActive()) {

                $updated = $this->programRepo->reactivate(
                    $existingProgram->getProgramId(),
                    $programCode,
                    $programName,
                    $department
                );

                if (!$updated) {
                    throw new RuntimeException(
                        'Failed to reactivate program.'
                    );
                }

                $program = $this->programRepo->findById(
                    $existingProgram->getProgramId()
                );

                if (!$program) {
                    throw new RuntimeException(
                        'Failed to retrieve reactivated program.'
                    );
                }

                $this->pdo->commit();

                return $program;
            }

            /*
             * No existing program
             * → Create
             */
            $program = Program::fromArray([
                'program_code' => $programCode,
                'program_name' => $programName,
                'department'   => $department,
                'is_active'    => true
            ]);

            $program = $this->programRepo->create($program);

            $this->pdo->commit();

            return $program;

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }


    public function update(
        array $data
    ): Program {

        $programId = (int) ($data['program_id'] ?? 0);

        $programCode = strtoupper(
            trim($data['program_code'] ?? '')
        );

        $programName = trim(
            $data['program_name'] ?? ''
        );

        $department = trim(
            $data['department'] ?? ''
        );


        if (
            $programId <= 0 ||
            $programCode === '' ||
            $programName === '' ||
            $department === ''
        ) {
            throw new RuntimeException(
                'Missing required fields.'
            );
        }

        if (
            strlen($programCode) < 2 ||
            strlen($programCode) > 15
        ) {
            throw new RuntimeException(
                'Program Code must be 2-15 characters long.'
            );
        }

        if (
            strlen($programName) < 10 ||
            strlen($programName) > 100
        ) {
            throw new RuntimeException(
                'Program Name must be 10-100 characters long.'
            );
        }


        $program = $this->programRepo->findById($programId);

        if (!$program) {
            throw new RuntimeException(
                'Program not found.'
            );
        }

        //Check if there changes made
        $hasChanges =
        $program->getProgramCode() !== $programCode ||
        $program->getProgramName() !== $programName ||
        $program->getDepartment() !== $department;


        if ($hasChanges) {

            $studentCount =
                $this->programRepo
                    ->countStudentsWithSubmittedEvaluations(
                        $programId
                    );

            if ($studentCount > 0) {
                throw new RuntimeException(
                    'This program cannot be modified because multiple students have already completed evaluations under this program.'
                );
            }
        }


        //check duplicate
        if (
            $this->programRepo->existsByCode(
                $programCode,
                $programId
            )
        ) {
            throw new RuntimeException(
                "The code '$programCode' is already assigned to another program."
            );
        }


        //check duplicate name
        if (
            $this->programRepo->existByName(
                $programName,
                $programId
            )
        ) {
            throw new RuntimeException(
                "The program name '$programName' is already in use."
            );
        }


        //check if program has evaluation responses
        if ($program->getDepartment() !== $department) {

            $studentCount =
               $this->programRepo
                   ->countStudentsWithSubmittedEvaluations(
                       $programId
                   );

            if ($studentCount > 0) {
                throw new RuntimeException(
                    'Cannot change department because students under this program have already submitted evaluations.'
                );
            }

        }


        $countStudentsAssociated = $this->programRepo->countStudents($programId);

        if ($countStudentsAssociated > 0) {
            throw new RuntimeException(
                "Warning: update request is not allowed. " .
                "There are {$countStudentsAssociated} student record(s) associated with this program."
            );
        }


        try {

            $this->pdo->beginTransaction();

            $updated = $this->programRepo->update(
                $programId,
                $programCode,
                $programName,
                $department
            );

            if (!$updated) {
                throw new RuntimeException(
                    'Failed to update program.'
                );
            }


            // Get updated record
            $updatedProgram = $this->programRepo->findById(
                $programId
            );

            if (!$updatedProgram) {
                throw new RuntimeException(
                    'Failed to retrieve updated program.'
                );
            }

            $this->pdo->commit();

            return $updatedProgram;

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }


    }


    public function delete(array $data): array
    {
        $programId = (int) ($data['program_id'] ?? 0);

        if ($programId <= 0) {
            throw new RuntimeException('Invalid program ID.');
        }

        try {

            $this->pdo->beginTransaction();

            $program = $this->programRepo->findById($programId);

            if (!$program) {
                throw new RuntimeException(
                    'Program not found.'
                );
            }

            /*
             *
             * Historical evaluation data must be preserved.
             */
            $submittedStudentCount =
                $this->programRepo
                    ->countStudentsWithSubmittedEvaluations($programId);

            if ($submittedStudentCount > 0) {

                $updated = $this->programRepo->deactivate(
                    $programId
                );

                if (!$updated) {
                    throw new RuntimeException(
                        'Failed to deactivate program.'
                    );
                }

                $this->pdo->commit();

                return [
                    'status' => 'warning',
                    'message' =>
                        'This program has students with submitted evaluations, so it was deactivated instead of permanently deleted.',
                    'type' => 'deactivated',
                    'program' => $this->programRepo
                        ->findById($programId)
                        ?->toArray(),
                    'submitted_students' => $submittedStudentCount
                ];
            }


            /*
             * Check other dependencies
             */
            $studentCount =
                $this->programRepo->countStudents($programId);

            $loadCount =
                $this->programRepo->countTeacherLoads($programId);


            if ($studentCount > 0 || $loadCount > 0) {

                $dependencies = [];

                if ($studentCount > 0) {
                    $dependencies[] =
                        "{$studentCount} student(s)";
                }

                if ($loadCount > 0) {
                    $dependencies[] =
                        "{$loadCount} teacher load(s)";
                }

                throw new RuntimeException(
                    'Cannot delete this program because it is associated with '
                    . implode(' and ', $dependencies)
                    . '. Please remove or reassign these records first.'
                );
            }


            /*
             *
             * Safe to permanently delete.
             */
            $deleted =
                $this->programRepo->delete($programId);

            if (!$deleted) {
                throw new RuntimeException(
                    'Failed to delete program.'
                );
            }

            $this->pdo->commit();

            return [
                'status' => 'success',
                'message' =>
                    'Program deleted successfully.',
                'type' => 'deleted',
                'program' => $program->toArray(),
                'submitted_students' => 0
            ];

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

}
