<?php 

namespace App\Repositories\EvaluationRepo;

use PDO;

class EvaluationRepository
{

  public function __construct(
    private PDO $pdo
  )
  {
  }

  /**
   * Find active evaluation period
   * by department
   */
  public function findActiveByDepartment(
    string $department
  ): ?array {

    $stmt = $this->pdo->prepare("
      SELECT
          period_id,
          academic_year,
          semester,
          start_date,
          end_date
      FROM evaluation_periods
      WHERE target_dept = ?
        AND is_active = 1
      LIMIT 1
    ");

    $stmt->execute([
      $department
    ]);

    $period = $stmt->fetch(PDO::FETCH_ASSOC);

    return $period ?: null;
  }


  /**
   * Find evaluation period by
   * id
   */
  public function findById(
    int $periodId
  ): ?array {

    $stmt = $this->pdo->prepare("
      SELECT
          period_id,
          academic_year,
          semester,
          start_date,
          end_date
      FROM evaluation_periods
      WHERE period_id = ?
      LIMIT 1
    ");

    $stmt->execute([$periodId]);

    $period = $stmt->fetch(PDO::FETCH_ASSOC);

    return $period ?: null;

  }


  /**
   * Find previous evaluation by department
   */
  public function findPreviousInactive(
    string $department
  ): ?array {

    $stmt = $this->pdo->prepare("
      SELECT period_id
      FROM evaluation_periods
      WHERE target_dept = ?
        AND is_active = 0
      ORDER BY end_date DESC
      LIMIT 1
    ");

    $stmt->execute([$department]);

    $period = $stmt->fetch(PDO::FETCH_ASSOC);

    return $period ?: null;

  }

}

?>