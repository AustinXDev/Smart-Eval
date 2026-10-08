<?php

namespace App\Repositories\ProgramRepo;

use App\Models\Program;
use PDO;

class ProgramRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }


    public function getProgramChart(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
          SELECT
              p.program_name,

              COUNT(DISTINCT s.student_id)
                  AS total_students,

              COUNT(DISTINCT CASE
                  WHEN fin.student_id IS NOT NULL
                  THEN s.student_id
              END) AS finished,

              COUNT(DISTINCT CASE
                  WHEN fin.student_id IS NULL
                  THEN s.student_id
              END) AS not_finished

          FROM programs p

          INNER JOIN students s
              ON s.program_id = p.program_id
              AND s.is_active = 1

          LEFT JOIN evaluation_status es
              ON es.student_id = s.student_id
              AND es.period_id = ?

          LEFT JOIN (
              SELECT
                  student_id

              FROM evaluation_status

              WHERE period_id = ?
                AND is_submitted = 1

              GROUP BY student_id

              HAVING COUNT(teacher_id) = (
                  SELECT COUNT(*)
                  FROM evaluation_status es2
                  WHERE es2.student_id =
                        evaluation_status.student_id
                    AND es2.period_id = ?
              )
          ) AS fin
              ON fin.student_id = s.student_id

          WHERE p.department = ?
            AND p.is_active = 1

          GROUP BY
              p.program_id,
              p.program_name

          ORDER BY
              p.program_name ASC
      ");

        $stmt->execute([
            $periodId,
            $periodId,
            $periodId,
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    public function getAllProgram(): array
    {
        $stmt = $this->pdo->prepare("
        SELECT *
        FROM programs
        ORDER BY is_active DESC, program_name ASC, program_id ASC
    ");

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn (array $row): Program => Program::fromArray($row),
            $rows
        );
    }

    public function getCount(): array
    {

        $stmt = $this->pdo->prepare("
         SELECT
                COUNT(*) AS total,
                COALESCE(
                    SUM(CASE WHEN p.is_active = 1 THEN 1 ELSE 0 END),
                    0
                ) AS active,
                COALESCE(
                    SUM(CASE WHEN p.is_active = 0 THEN 1 ELSE 0 END),
                    0
                ) AS inactive
            FROM programs p
        ");

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0,
            'active' => 0,
            'inactive' => 0
        ];

    }

    public function getByDepartment(
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
            SELECT
                program_id,
                program_name
            FROM programs
            WHERE department = ?
            AND is_active = 1
            ORDER BY program_id ASC
        ");

        $stmt->execute([$department]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getProgramByDepartment(
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT * 
        FROM programs
            WHERE department = ?
        ORDER BY program_name ASC, is_active DESC
       ");

        $stmt->execute([
         $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    //Find program by ID
    public function findById(
        int $programId
    ): ?Program {

        $stmt = $this->pdo->prepare("
        SELECT *
        FROM programs
        WHERE program_id = ?
        LIMIT 1
    ");

        $stmt->execute([
            $programId
        ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data
            ? Program::fromArray($data)
            : null;
    }


    //Find program by code
    public function findByCode(string $programCode): ?Program
    {

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM programs
                WHERE program_code = ?
            LIMIT 1
        ");

        $stmt->execute([$programCode]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Program::fromArray($data) : null;

    }


    //Find duplicate active program
    public function findActiveByName(
        string $programName
    ): ?Program {

        $stmt = $this->pdo->prepare("
        SELECT *
        FROM programs
        WHERE LOWER(program_name) = LOWER(?)
          AND is_active = 1
        LIMIT 1
    ");

        $stmt->execute([
            $programName
        ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data
            ? Program::fromArray($data)
            : null;
    }


    //Find duplicate program
    public function findDuplicate(
        string $programCode,
        string $programName
    ): ?Program {

        $stmt = $this->pdo->prepare("
        SELECT *
        FROM programs
        WHERE program_code = ?
           OR (
                LOWER(program_name) = LOWER(?)
           )
        LIMIT 1
    ");

        $stmt->execute([
            $programCode,
            $programName
        ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data
            ? Program::fromArray($data)
            : null;
    }


    //Exist by program code and exclude program ID
    public function existsByCode(
        string $programCode,
        int $excludeProgramId
    ): bool {

        $stmt = $this->pdo->prepare("
        SELECT 1
        FROM programs
        WHERE program_code = ?
          AND program_id != ?
        LIMIT 1
    ");

        $stmt->execute([
            $programCode,
            $excludeProgramId
        ]);

        return (bool) $stmt->fetchColumn();

    }


    //Exist by name and exclude program ID
    public function existByName(
        string $programName,
        int $excludeProgramId
    ): bool {

        $stmt = $this->pdo->prepare("
        SELECT 1
        FROM programs
        WHERE LOWER(program_name) = LOWER(?)
          AND program_id != ?
        LIMIT 1
    ");

        $stmt->execute([
            $programName,
            $excludeProgramId
        ]);

        return (bool) $stmt->fetchColumn();

    }


    //Count student associated with program ID
    public function countStudents(int $programId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM students
            WHERE program_id = ?
        ");

        $stmt->execute([$programId]);

        return (int) $stmt->fetchColumn();
    }


    //Count Load associated with this program ID
    public function countTeacherLoads(int $programId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM teacher_load
            WHERE program_id = ?
        ");

        $stmt->execute([$programId]);

        return (int) $stmt->fetchColumn();
    }


    //Count submitted evaluation by this program
    public function countStudentsWithSubmittedEvaluations(
        int $programId
    ): int {

        $stmt = $this->pdo->prepare("
        SELECT COUNT(DISTINCT es.student_id)
        FROM students s
        INNER JOIN evaluation_status es
            ON es.student_id = s.student_id
        WHERE s.program_id = ?
          AND es.is_submitted = 1
    ");

        $stmt->execute([
            $programId
        ]);

        return (int) $stmt->fetchColumn();
    }


    //Create new program
    public function create(Program $program): Program
    {

        $stmt = $this->pdo->prepare("
            INSERT INTO programs
                (program_code, program_name, department, is_active)
            VALUES(?, ?, ?, ?)
        ");

        $stmt->execute([
            $program->getProgramCode(),
            $program->getProgramName(),
            $program->getDepartment(),
            $program->isActive() ? 1 : 0
        ]);

        return $program;

    }


    //Update program
    public function update(
        int $programId,
        string $programCode,
        string $programName,
        string $department
    ): bool {

        $stmt = $this->pdo->prepare("
        UPDATE programs
        SET
            program_code = ?,
            program_name = ?,
            department = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE program_id = ?
    ");

        return $stmt->execute([
            $programCode,
            $programName,
            $department,
            $programId
        ]);
    }


    //Reactivate archive program
    public function reactivate(
        int $programId,
        string $programCode,
        string $programName,
        string $department
    ): bool {

        $stmt = $this->pdo->prepare("
        UPDATE programs
        SET
            program_code = ?,
            program_name = ?,
            department = ?,
            is_active = 1
        WHERE program_id = ?
          AND is_active = 0
    ");

        return $stmt->execute([
            $programCode,
            $programName,
            $department,
            $programId
        ]);
    }


    public function deactivate(int $programId): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE programs
            SET is_active = 0
            WHERE program_id = ?
        ");

        $stmt->execute([$programId]);

        return $stmt->rowCount() > 0;
    }


    public function delete(int $programId): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM programs
            WHERE program_id = ?
        ");

        $stmt->execute([$programId]);

        return $stmt->rowCount() > 0;
    }
}
