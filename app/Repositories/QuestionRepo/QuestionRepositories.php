<?php

namespace App\Repositories\QuestionRepo;

use App\Models\QuestionModel;
use PDO;

class QuestionRepositories
{
    public function __construct(
        private PDO $pdo
    ) {
    }


    /**
     * Find question set by name.
     */
    public function findByName(
        string $setName
    ): ?QuestionModel {

        $stmt = $this->pdo->prepare("
      SELECT *
      FROM question_sets
      WHERE set_name = ?
      LIMIT 1
    ");

        $stmt->execute([
          $setName
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
              ? QuestionModel::fromArray($row)
              : null;

    }


    /**
     * Find question set by ID
     */
    public function findById(
        int $setId
    ): ?QuestionModel {

        $stmt = $this->pdo->prepare("
      SELECT *
      FROM question_sets
      WHERE set_id = ?
      LIMIT 1
    ");

        $stmt->execute([
          $setId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
              ? QuestionModel::fromArray($row)
              : null;

    }


    public function getActiveQuestionSets(): array
    {

        $stmt = $this->pdo->prepare("
            SELECT * 
            FROM question_sets
            WHERE is_active = 1
            ORDER BY set_id ASC
        ");

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return [];
        }

        return array_map(
            fn (array $row): QuestionModel => QuestionModel::fromArray($row),
            $rows
        );

    }


    /**
     * Find all active question sets withs stats
     */
    public function findAllActiveWithStats(): array
    {

        $stmt = $this->pdo->prepare("
        SELECT
            qs.set_id,
            qs.set_name,
            qs.is_active,
            qs.created_at,

            COUNT(DISTINCT q.question_id) AS total_questions,

            CASE
                WHEN COUNT(DISTINCT ep.period_id) > 0
                THEN 1
                ELSE 0
            END AS active_evaluation_using_set,

            (
                SELECT COUNT(*)
                FROM evaluation_periods ep2
                WHERE ep2.set_id = qs.set_id
            ) AS total_periods_using_set

        FROM question_sets qs

        LEFT JOIN questions q
            ON q.set_id = qs.set_id
            AND q.is_active = 1

        LEFT JOIN evaluation_periods ep
            ON ep.set_id = qs.set_id
            AND ep.is_active = 1

        WHERE qs.is_active = 1

        GROUP BY
            qs.set_id,
            qs.set_name,
            qs.is_active,
            qs.created_at

        ORDER BY qs.created_at DESC
    ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Get all questions by Id
     */
    public function getQuestionsById(
        int $setId
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT *
        FROM questions
          WHERE set_id = ?
          AND is_active = 1
        ORDER BY question_id ASC
      ");

        $stmt->execute([
          $setId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Get all questions by period id
     */
    public function getQuestionsByPeriodId(
        int $periodId
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            q.question_id,
            q.question_text
        FROM questions q

        INNER JOIN evaluation_periods ep
            ON q.set_id = ep.set_id

        WHERE ep.period_id = ?
          AND q.is_active = 1

        ORDER BY q.question_id ASC
    ");

        $stmt->execute([$periodId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    public function findDuplicateQuestion(
        int $setId,
        string $normalizedQuestion
    ): ?array {

        $stmt = $this->pdo->prepare("
        SELECT 
          question_id,
          is_active
        FROM questions
        WHERE set_id = ?
        AND LOWER(
          REPLACE(
            REPLACE(question_text, ' ', ''),
            '?',
            ''
          )
        ) = ?
        LIMIT 1
      ");

        $stmt->execute([
              $setId,
              $normalizedQuestion
          ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;

    }


    /**
     * Find question by ID
     */
    public function findQuestionById(
        int $questionId
    ): ?array {

        $stmt = $this->pdo->prepare("
            SELECT
                question_id,
                set_id,
                question_text,
                category,
                is_active
            FROM questions
            WHERE question_id = ?
            LIMIT 1
        ");

        $stmt->execute([$questionId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }


    /**
     * Check if question
     * already answered
     */
    public function hasQuestionAnswers(
        int $questionId
    ): bool {

        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM evaluation_answers
            WHERE question_id = ?
            LIMIT 1
        ");

        $stmt->execute([$questionId]);

        return (bool) $stmt->fetchColumn();
    }


    /**
   * Get the number of evaluation periods
   * that used this question.
   */
    public function countQuestionEvaluationPeriods(
        int $questionId
    ): int {

        $stmt = $this->pdo->prepare("
        SELECT COUNT(DISTINCT ep.period_id)
        FROM evaluation_periods ep
        INNER JOIN questions q
            ON q.set_id = ep.set_id
        WHERE q.question_id = ?
    ");

        $stmt->execute([$questionId]);

        return (int) $stmt->fetchColumn();
    }


    /**
     * Duplicate question name
     */
    public function existsDuplicateQuestion(
        int $setId,
        string $questionText,
        int $questionId
    ): bool {

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM questions
            WHERE set_id = ?
            AND LOWER(
                REPLACE(
                    REPLACE(question_text, ' ', ''),
                    '?',
                    ''
                )
            ) = LOWER(
                REPLACE(
                    REPLACE(?, ' ', ''),
                    '?',
                    ''
                )
            )
            AND question_id != ?
        ");

        $stmt->execute([
            $setId,
            $questionText,
            $questionId
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }


    /**
   * Create question set
   */
    public function create(
        QuestionModel $questionSet
    ): int {

        $stmt = $this->pdo->prepare("
      INSERT INTO question_sets
        (set_name, is_active)
      VALUES
        (?, ?)
    ");

        $stmt->execute([
          $questionSet->setName,
          $questionSet->isActive ? 1 : 0
        ]);

        return (int) $this->pdo->lastInsertId();

    }


    /**
     * Create Question
     */
    public function createQuestion(
        int $setId,
        string $questionText,
        string $category
    ): int {

        $stmt = $this->pdo->prepare("
            INSERT INTO questions (
                set_id,
                question_text,
                category,
                is_active
            )
            VALUES (?, ?, ?, 1)
        ");

        $stmt->execute([
            $setId,
            $questionText,
            $category
        ]);

        return (int) $this->pdo->lastInsertId();
    }


    /**
     * Update Question
     */
    public function updateQuestion(
        int $questionId,
        string $questionText,
        string $category
    ): bool {

        $stmt = $this->pdo->prepare("
            UPDATE questions
            SET
                question_text = ?,
                category = ?
            WHERE question_id = ?
        ");

        return $stmt->execute([
            $questionText,
            $category,
            $questionId
        ]);
    }


    /**
     * Activate question.
     */
    public function activateQuestion(
        int $questionId
    ): bool {

        $stmt = $this->pdo->prepare("
            UPDATE questions
            SET is_active = 1
            WHERE question_id = ?
        ");

        return $stmt->execute([$questionId]);
    }


    /**
     * Check if set used
     * in active evaluation period
     */
    public function isUsedInActiveEvaluation(
        int $setId
    ): bool {

        $stmt = $this->pdo->prepare("
          SELECT COUNT(*)
          FROM evaluation_periods
          WHERE set_id = ?
          AND is_active = 1
        ");

        $stmt->execute([
          $setId
        ]);

        return (int) $stmt->fetchColumn() > 0;

    }


    /**
     * Check if has answer in any evaluation
     */
    public function hasAnswers(
        int $setId
    ): bool {

        $stmt = $this->pdo->prepare("
        SELECT COUNT(*)
        FROM evaluation_answers ea
        INNER JOIN questions q
          ON ea.question_id = q.question_id
        WHERE q.set_id = ?
      ");

        $stmt->execute([
          $setId
        ]);

        return (int) $stmt->fetchColumn() > 0;

    }


    /**
     * update Set
     *
     */
    public function updateSet(
        int $setId,
        string $setName
    ): bool {

        $stmt = $this->pdo->prepare("
        UPDATE question_sets
        SET set_name = ?
        WHERE set_id = ?
      ");

        return $stmt->execute([
          $setName,
          $setId
        ]);

    }


    /**
     * Find By name except by ID
     */
    public function existByNameExceptId(
        string $setName,
        int $setId
    ): bool {

        $stmt = $this->pdo->prepare("
        SELECT COUNT(*)
        FROM question_sets
          WHERE LOWER(set_name) = LOWER(?)
          AND set_id != ?
      ");

        $stmt->execute([
          $setName,
          $setId
        ]);

        return (int) $stmt->fetchColumn() > 0;

    }


    /**
     * Archive Set
     */
    public function archive(
        int $setId
    ): bool {

        $stmt = $this->pdo->prepare("
        UPDATE question_sets
        SET is_active = 0
        WHERE set_id = ?
      ");

        return $stmt->execute([$setId]);

    }


    /**
     * Delete related set questions
     */
    public function deleteQuestions(
        int $setId
    ): bool {

        $stmt = $this->pdo->prepare("
            DELETE FROM questions
            WHERE set_id = ?
        ");

        return $stmt->execute([$setId]);
    }


    /**
     * Delete Set
     */
    public function deleteSet(
        int $setId
    ): bool {

        $stmt = $this->pdo->prepare("
        DELETE FROM question_sets
        WHERE set_id = ?
      ");

        return $stmt->execute([$setId]);

    }


    /**
   * Count student answers for a question.
   */
    public function countQuestionAnswers(
        int $questionId
    ): int {

        $stmt = $this->pdo->prepare("
        SELECT COUNT(*)
        FROM evaluation_answers
        WHERE question_id = ?
    ");

        $stmt->execute([
            $questionId
        ]);

        return (int) $stmt->fetchColumn();
    }


    /**
 * Count active questions in a set.
 */
    public function countActiveQuestionsBySet(
        int $setId
    ): int {

        $stmt = $this->pdo->prepare("
        SELECT COUNT(*)
        FROM questions
        WHERE set_id = ?
        AND is_active = 1
    ");

        $stmt->execute([
            $setId
        ]);

        return (int) $stmt->fetchColumn();
    }


    /**
     * Deactivate question.
     */
    public function deactivateQuestion(
        int $questionId
    ): bool {

        $stmt = $this->pdo->prepare("
        UPDATE questions
        SET is_active = 0
        WHERE question_id = ?
    ");

        return $stmt->execute([
            $questionId
        ]);
    }


    /**
     * Permanently delete question.
     */
    public function deleteQuestion(
        int $questionId
    ): bool {

        $stmt = $this->pdo->prepare("
        DELETE FROM questions
        WHERE question_id = ?
    ");

        return $stmt->execute([
            $questionId
        ]);
    }


}
