<?php

class EvaluationRepository
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getPeriod($periodId)
      {
          $stmt = $this->pdo->prepare("
              SELECT * FROM evaluation_periods WHERE period_id = ?
          ");
          $stmt->execute([$periodId]);
          return $stmt->fetch(PDO::FETCH_ASSOC);
      }

    public function getFacultyRankings($periodId)
      {
          $stmt = $this->pdo->prepare("
              SELECT 
                  t.full_name,
                  COUNT(DISTINCT es.student_id) AS evaluators,
                  ROUND(AVG(score_data.avg_score), 2) AS mean_score
              FROM teachers t
              JOIN teacher_load tl 
                  ON t.teacher_id = tl.teacher_id

              JOIN evaluation_status es 
                  ON tl.load_id = es.load_id

              JOIN (
                  SELECT 
                      eval_id,
                      AVG(score) AS avg_score
                  FROM evaluation_answers
                  GROUP BY eval_id
              ) score_data 
                  ON es.eval_id = score_data.eval_id

              WHERE es.period_id = ?
                AND es.is_submitted = 1

              GROUP BY t.teacher_id
              ORDER BY mean_score DESC
          ");

          $stmt->execute([$periodId]);
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

    /*
    |--------------------------------------------------------------------------
    | CATEGORY BREAKDOWN
    |--------------------------------------------------------------------------
    */
    public function getCategoryBreakdown($periodId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                q.category,
                ROUND(AVG(ea.score), 2) AS cat_avg
            FROM evaluation_answers ea
            JOIN questions q 
                ON ea.question_id = q.question_id

            JOIN evaluation_status es 
                ON ea.eval_id = es.eval_id

            WHERE es.period_id = ?
              AND es.is_submitted = 1

            GROUP BY q.category
        ");

        $stmt->execute([$periodId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPeriodSummary($periodId)
      {
          $stmt = $this->pdo->prepare("
              SELECT 
                  COUNT(DISTINCT student_id) AS total_evaluators,
                  ROUND(AVG(score), 2) AS final_average
              FROM evaluation_answers
              WHERE period_id = ?
          ");
          $stmt->execute([$periodId]);
          return $stmt->fetch(PDO::FETCH_ASSOC);
      }

    public function getTeacherEvaluation($teacherId, $periodId)
      {
          $stmt = $this->pdo->prepare("
              SELECT *
              FROM evaluation_answers
              WHERE faculty_id = ? AND period_id = ?
          ");
          $stmt->execute([$teacherId, $periodId]);
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }
}