<?php 

namespace App\Repositories\EvaluationRepo;

use PDO;

class EvaluationAnswerRepository
{

  public function __construct(
    private PDO $pdo
  )
  {
  }

  public function getScoreDistribution(
    string $department,
    int $periodId
  ): array {

    $stmt = $this->pdo->prepare("
        SELECT
            ea.score,
            COUNT(ea.score) AS total_count

        FROM evaluation_answers ea

        INNER JOIN evaluation_status es
            ON es.eval_id = ea.eval_id

        INNER JOIN teacher_load tl
            ON tl.load_id = es.load_id

        INNER JOIN programs p
            ON p.program_id = tl.program_id

        WHERE es.period_id = ?
          AND es.is_submitted = 1
          AND p.department = ?

        GROUP BY ea.score

        ORDER BY ea.score DESC
    ");

    $stmt->execute([
        $periodId,
        $department
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }


  public function getCategoricalBreakdown(
    string $department,
    int $periodId
  ): array {

    $stmt = $this->pdo->prepare("
        SELECT
            q.category,

            ROUND(
                SUM(ea.score) /
                NULLIF(COUNT(ea.answer_id), 0),
                2
            ) AS cat_avg

        FROM evaluation_answers ea

        INNER JOIN questions q
            ON q.question_id = ea.question_id

        INNER JOIN evaluation_status es
            ON es.eval_id = ea.eval_id

        INNER JOIN students s
            ON s.student_id = es.student_id

        INNER JOIN programs p
            ON p.program_id = s.program_id

        INNER JOIN (
            SELECT
                es_inner.student_id

            FROM evaluation_status es_inner

            WHERE es_inner.period_id = ?

            GROUP BY es_inner.student_id

            HAVING
                SUM(es_inner.is_submitted)
                =
                COUNT(es_inner.load_id)

        ) completed_students

            ON completed_students.student_id =
              es.student_id

        WHERE es.period_id = ?
          AND p.department = ?
          AND es.is_submitted = 1

        GROUP BY q.category

        ORDER BY cat_avg DESC
    ");

    $stmt->execute([
        $periodId,
        $periodId,
        $department
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

}


?>