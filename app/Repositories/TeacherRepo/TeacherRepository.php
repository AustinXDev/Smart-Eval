<?php 

namespace App\Repositories\TeacherRepo;

use PDO;

class TeacherRepository
{

  public function __construct(
    private PDO $pdo
  )
  {
  }

  /**
   * Count active teacher by 
   * department
   */
  public function countActiveByDepartment(
    string $department
  ): int {

    $stmt = $this->pdo->prepare("
      SELECT COUNT(*)
      FROM teachers
      WHERE department = ?
        AND is_active = 1
    ");

    $stmt->execute([$department]);

     return (int) $stmt->fetchColumn();
  }


    /**
   * Get the teacher ranking 
   * based on period Id and department
   * 
   */
  public function getTeacherRanking(
    int $periodId,
    string $department
  ): array {

    $stmt = $this->pdo->prepare("
    SELECT
      t.teacher_id,
      t.full_name AS teacher_name,

      COUNT(DISTINCT es.student_id) AS total_evaluated_students,

      ROUND(AVG(ea.score), 2) AS overall_mean_score

      FROM teachers t

      INNER JOIN teacher_load tl
          ON tl.teacher_id = t.teacher_id

      INNER JOIN programs p
          ON p.program_id = tl.program_id

      INNER JOIN evaluation_status es
          ON es.load_id = tl.load_id
          AND es.period_id = ?
          AND es.is_submitted = 1

      INNER JOIN evaluation_answers ea
          ON ea.eval_id = es.eval_id

      WHERE p.department = ?

      GROUP BY
          t.teacher_id,
          t.full_name

      HAVING AVG(ea.score) IS NOT NULL

      ORDER BY overall_mean_score DESC

      LIMIT 5
    ");

    $stmt->execute([
      $periodId,
      $department
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }
}

?>