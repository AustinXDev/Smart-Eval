<?php 

namespace App\Repositories\EvaluationRepo;

use PDO;

class EvaluationStatusRepository
{

  public function __construct(
    private PDO $pdo
  )
  {
  }

  /**
   * Count evaluation submitted
   */
  public function countTotalSubmitted(
    string $department,
    int $periodId
  ): int  {

    $stmt = $this->pdo->prepare("
        SELECT COUNT(*)

        FROM evaluation_status es

        INNER JOIN students s
            ON s.student_id = es.student_id

        INNER JOIN programs p
            ON p.program_id = s.program_id

        WHERE es.period_id = ?
          AND es.is_submitted = 1
          AND p.department = ?
          AND s.is_active = 1
    ");

    $stmt->execute([
        $periodId,
        $department
    ]);

    return (int) $stmt->fetchColumn();    

  }


  /**
   * Count completed students
   */
  public function countCompletedStudents(
    string $department,
    int $periodId
  ): int {

    $stmt = $this->pdo->prepare("
      SELECT COUNT(DISTINCT es.student_id)

      FROM evaluation_status es

      INNER JOIN students s
          ON s.student_id = es.student_id

      INNER JOIN programs p
          ON p.program_id = s.program_id

      INNER JOIN (
          SELECT student_id

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

      ) completed 
        ON completed.student_id = es.student_id

      WHERE es.period_id = ?
        AND p.department = ?
        AND s.is_active = 1
    ");

    $stmt->execute([
        $periodId,
        $periodId,
        $periodId,
        $department
    ]);

    return (int) $stmt->fetchColumn();

  }

  public function getParticipation(
    string $department,
    int $periodId
  ): array {

      $stmt = $this->pdo->prepare("
          SELECT

              COUNT(DISTINCT es.student_id)
                  AS total_evaluators,

              COUNT(DISTINCT
                  CASE
                      WHEN completed.student_id IS NOT NULL
                      THEN es.student_id
                  END
              ) AS finished,

              COUNT(DISTINCT
                  CASE
                      WHEN completed.student_id IS NULL
                      THEN es.student_id
                  END
              ) AS not_finished

          FROM evaluation_status es

          INNER JOIN students s
              ON s.student_id = es.student_id

          INNER JOIN programs p
              ON p.program_id = s.program_id

          LEFT JOIN (

              SELECT student_id

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

          ) completed

              ON completed.student_id = es.student_id

          WHERE es.period_id = ?
            AND p.department = ?
            AND s.is_active = 1
      ");

      $stmt->execute([
          $periodId,
          $periodId,
          $periodId,
          $department
      ]);

      return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
          'total_evaluators' => 0,
          'finished' => 0,
          'not_finished' => 0
      ];
  }

}

?>