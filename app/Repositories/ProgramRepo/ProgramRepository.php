<?php 

namespace App\Repositories\ProgramRepo;

use PDO;

class ProgramRepository 
{

  public function __construct(
    private PDO $pdo
  )
  {
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

              HAVING COUNT(load_id) = (
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


    public function getByDepartment(
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
            SELECT
                program_id,
                program_name
            FROM programs
            WHERE department = ?
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
            AND is_active = 1
        ORDER BY program_name ASC
       ");
       
       $stmt->execute([
        $department
       ]);

       return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
}

?>