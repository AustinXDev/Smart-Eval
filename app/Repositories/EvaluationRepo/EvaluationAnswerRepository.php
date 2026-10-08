<?php

namespace App\Repositories\EvaluationRepo;

use PDO;

class EvaluationAnswerRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Score Destribution
     *
     * Only includes answers from students who
     * fully completed all evaluations for the period.
     */
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
            AND es.period_id = ?
            AND es.is_submitted = 1

        INNER JOIN teachers t
            ON t.teacher_id = es.teacher_id

        INNER JOIN (
            SELECT
                student_id
            FROM evaluation_status
            WHERE period_id = ?
            GROUP BY student_id
            HAVING
                COUNT(*) > 0
                AND SUM(is_submitted = 1) = COUNT(*)
        ) completed_students
            ON completed_students.student_id = es.student_id

        WHERE t.department = ?
            AND t.is_active = 1

        GROUP BY ea.score

        ORDER BY ea.score DESC
    ");

        $stmt->execute([
            $periodId,
            $periodId,
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    /**
     * Categorical Breakdown
     */
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
                COUNT(es_inner.teacher_id)

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


    public function getStudentAnswersByPeriod(
        string $studentId,
        int $periodId
    ): array {
        $stmt = $this->pdo->prepare("
        SELECT
            es.teacher_id,
            ea.question_id,
            ea.score
        FROM evaluation_status es
        INNER JOIN evaluation_answers ea
            ON ea.eval_id = es.eval_id
        WHERE es.student_id = ?
          AND es.period_id = ?
        ORDER BY es.teacher_id, ea.question_id
    ");

        $stmt->execute([
            $studentId,
            $periodId
        ]);

        $result = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $teacherId = (int) $row['teacher_id'];
            $questionId = (int) $row['question_id'];

            $result[$teacherId][$questionId] = (int) $row['score'];
        }

        return $result;
    }


    public function saveAnswer(
        int $evalId,
        array $answers
    ): bool {

        $stmt = $this->pdo->prepare("
            INSERT INTO evaluation_answers
                (eval_id, question_id, score)
            VALUES
                (?, ?, ?) 
        ");

        foreach ($answers as $questionId => $rating) {

            $insert = $stmt->execute([
                $evalId,
                (int) $questionId,
                (int) $rating
            ]);

            if (!$insert) {
                return false;
            }
        }

        return true;

    }


    public function saveComment(
        int $evalId,
        string $comment
    ): bool {

        $stmt = $this->pdo->prepare("
            UPDATE evaluation_status
                SET comment = ?
            WHERE eval_id = ?
        ");

        return $stmt->execute([$comment, $evalId]);


    }


    public function updateAnswers(
        int $evalId,
        array $answers
    ): bool {

        $stmt = $this->pdo->prepare("
            UPDATE evaluation_answers
                SET score = ?
            WHERE eval_id = ?
                AND question_id = ?
        ");

        foreach ($answers as $questionId => $rating) {

            $insert = $stmt->execute([
                (int) $rating,
                (int) $evalId,
                (int) $questionId
            ]);

            if (!$insert) {
                return false;
            }
        }

        return true;


    }

    public function getAnswersByEvalId(int $evalId): array
    {
        $stmt = $this->pdo->prepare("
        SELECT question_id, score
        FROM evaluation_answers
        WHERE eval_id = ?
        ORDER BY question_id ASC
    ");

        $stmt->execute([$evalId]);

        $answers = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $answers[(int) $row['question_id']] = (int) $row['score'];
        }

        return $answers;
    }


    public function getCommentByEvalId(int $evalId): string
    {
        $stmt = $this->pdo->prepare("
        SELECT comment
        FROM evaluation_status
        WHERE eval_id = ?
    ");

        $stmt->execute([$evalId]);

        return (string) ($stmt->fetchColumn() ?? '');
    }

}
