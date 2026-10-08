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
                -- Number of unique students who fully completed their eval for this teacher
                COUNT(DISTINCT es.student_id) AS evaluators,
                -- Total Sum of Scores / Total Number of Answers (The true mean)
                COALESCE(ROUND(SUM(ea.score) / NULLIF(COUNT(ea.answer_id), 0), 2), 0) AS mean_score
            FROM teachers t
            INNER JOIN teacher_load tl ON t.teacher_id = tl.teacher_id
            INNER JOIN evaluation_status es ON tl.load_id = es.load_id
            INNER JOIN evaluation_answers ea ON es.eval_id = ea.eval_id
            
            -- Filter for students who finished ALL their assigned loads
            INNER JOIN (
                SELECT es_inner.student_id
                FROM evaluation_status es_inner
                WHERE es_inner.period_id = ?
                GROUP BY es_inner.student_id
                HAVING SUM(es_inner.is_submitted) = COUNT(es_inner.load_id)
            ) finished_students ON es.student_id = finished_students.student_id

            WHERE es.period_id = ?
            AND es.is_submitted = 1
            GROUP BY t.teacher_id, t.full_name
            ORDER BY mean_score DESC
        ");

          $stmt->execute([$periodId, $periodId]);
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
            -- Total sum of scores in this category / total number of answers
            ROUND(SUM(ea.score) / NULLIF(COUNT(ea.answer_id), 0), 2) AS cat_avg
            FROM evaluation_answers ea
            INNER JOIN questions q ON ea.question_id = q.question_id
            INNER JOIN evaluation_status es ON ea.eval_id = es.eval_id
            
            -- Only include students who finished ALL their assigned loads
            INNER JOIN (
                SELECT es_inner.student_id
                FROM evaluation_status es_inner
                WHERE es_inner.period_id = ?
                GROUP BY es_inner.student_id
                HAVING SUM(es_inner.is_submitted) = COUNT(es_inner.load_id)
            ) finished_students ON es.student_id = finished_students.student_id

            WHERE es.period_id = ?
            AND es.is_submitted = 1
            GROUP BY q.category
            ORDER BY cat_avg DESC
        ");

        $stmt->execute([$periodId, $periodId]);
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