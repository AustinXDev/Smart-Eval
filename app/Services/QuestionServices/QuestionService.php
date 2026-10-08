<?php

namespace App\Services\QuestionServices;

use App\Models\QuestionModel;
use App\Repositories\QuestionRepo\QuestionRepositories;
use RuntimeException;
use PDO;

class QuestionService
{
    public function __construct(
        private PDO $pdo,
        private QuestionRepositories $questionRepo
    ) {
    }


    public function create(
        array $data
    ): QuestionModel {

        $setName = trim($data['set_name'] ?? '');


        /**
         * Validate
         */
        if ($setName === '') {

            throw new RuntimeException(
                "Question set name is required."
            );

        }

        /**
         * Optional length validation
         */
        if (mb_strlen($setName) > 100) {

            throw new RuntimeException(
                "Question set name must not exceed 100 characters."
            );

        }


        /**
         * Check duplicate
         */
        $existingSet = $this->questionRepo->findByName($setName);


        if ($existingSet) {


            if ($existingSet->isActive) {

                throw new RuntimeException(
                    "Question set already exists and is currently active."
                );

            }

        }


        /**
         * Create model
         */
        $questionSet = new QuestionModel();

        $questionSet->setName = $setName;
        $questionSet->isActive = true;

        try {

            $this->pdo->beginTransaction();

            $questionSet->setId = $this->questionRepo->create(
                $questionSet
            );

            $this->pdo->commit();

            return $questionSet;

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {

                $this->pdo->rollBack();

            }

            throw $e;

        }

    }


    /**
     * Update Question Set
     */
    public function updateSet(
        array $data
    ): QuestionModel {

        $setId = (int) ($data['set_id'] ?? 0);
        $newName = trim($data['set_name'] ?? '');

        //Validate
        if ($newName === '') {

            throw new RuntimeException(
                "Set name is required."
            );

        }


        if (mb_strlen($newName) < 3) {

            throw new RuntimeException(
                "Set must be at least 3 characters"
            );

        }


        if (mb_strlen($newName) > 100) {
            throw new RuntimeException(
                "Set name must not exceed 100 characters."
            );
        }


        $questionSet = $this->questionRepo->findById($setId);

        if (!$questionSet) {

            throw new RuntimeException(
                "Question set not found."
            );

        }


        //Check if no changes
        if (
            strcasecmp(
                trim($questionSet->setName),
                $newName
            ) === 0
        ) {

            throw new RuntimeException(
                "No changes were made to set name."
            );

        }


        //Duplicate name check
        if (
            $this->questionRepo->existByNameExceptId(
                $newName,
                $setId
            )
        ) {

            if ($questionSet->isActive) {

                throw new RuntimeException(
                    "Another question set already uses this name."
                );

            }

        }


        try {

            $this->pdo->beginTransaction();

            $this->questionRepo->updateSet(
                $setId,
                $newName
            );

            $this->pdo->commit();

            $questionSet->setName = $newName;

            return $questionSet;

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }


    }


    public function deleteSet(
        array $data
    ): array {

        $setId = (int) ($data['set_id'] ?? 0);

        if ($setId <= 0) {
            throw new RuntimeException(
                "Invalid question set ID. $setId"
            );
        }


        $questionSet = $this->questionRepo->findById($setId);

        if (!$questionSet) {
            throw new RuntimeException(
                "Set not found."
            );
        }


        if (
            $this->questionRepo->isUsedInActiveEvaluation($setId)
        ) {
            throw new RuntimeException(
                "This set is currently used in an active evaluation and cannot be deleted."
            );
        }


        $hasAnswers = $this->questionRepo->hasAnswers($setId);


        try {

            $this->pdo->beginTransaction();

            /**
             * Archived if answer exist
             */
            if ($hasAnswers) {

                $this->questionRepo->archive($setId);

                $this->pdo->commit();

                return [
                    'action' => 'archived',
                    'set_id' => $questionSet->setId,
                    'set_name' => $questionSet->setName
                ];
            }


            /**
             * Permanently Deleted
             */
            $this->questionRepo->deleteQuestions($setId);

            $this->questionRepo->deleteSet($setId);

            $this->pdo->commit();

            return [
                'action' => 'deleted',
                'set_id' => $questionSet->setId,
                'set_name' => $questionSet->setName
            ];

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }

    }


    public function getAllActive(): array
    {

        return $this->questionRepo->findAllActiveWithStats();

    }


    public function getQuestionById(
        array $data
    ): array {

        $setId = (int) ($data['set_id'] ?? 0);


        if ($setId <= 0) {

            throw new RuntimeException(
                "Invalid or missing question set ID."
            );

        }


        $questionSet = $this->questionRepo->findById(
            $setId
        );

        if (!$questionSet) {

            throw new RuntimeException(
                "Question set not found."
            );

        }

        return $this->questionRepo->getQuestionsById(
            $setId
        );

    }


    public function createQuestion(
        array $data
    ): array {

        $setId = (int) ($data['set_id'] ?? 0);
        $questionName = trim(
            $data['question_name'] ?? ''
        );
        $category = trim(
            $data['categories'] ?? ''
        );


        if ($setId <= 0) {
            throw new RuntimeException(
                "Invalid question set ID."
            );
        }


        if ($questionName === '') {
            throw new RuntimeException(
                "Question is required."
            );
        }


        if ($category === '') {
            throw new RuntimeException(
                "Category is required."
            );
        }

        $normalized = strtolower(
            preg_replace(
                '/[^a-zA-Z0-9]/',
                '',
                $questionName
            )
        );

        $existing = $this->questionRepo->findDuplicateQuestion(
            $setId,
            $normalized
        );

        if ($existing) {

            if ((int) $existing['is_active'] === 1) {

                throw new RuntimeException(
                    "This question already exists."
                );
            }


            return [
                'status' => 'warning',
                'question_id' => (int) $existing['question_id'],
                'message' => 'This question exists but is inactive. Activate it?'
            ];
        }

        try {

            $this->pdo->beginTransaction();

            $questionId = $this->questionRepo->createQuestion(
                $setId,
                $questionName,
                $category
            );

            $this->pdo->commit();


            return [
                'status' => 'success',
                'question_id' => $questionId,
                'message' => 'Question added successfully.'
            ];

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }

    }


    public function updateQuestion(
        array $data
    ): array {

        $questionId = (int) ($data['question_id'] ?? 0);
        $setId = (int) ($data['set_id'] ?? 0);

        $newText = trim(
            $data['question_text'] ?? ''
        );

        $newCategory = trim(
            $data['category'] ?? ''
        );

        if ($questionId <= 0) {
            throw new RuntimeException(
                "Invalid question ID."
            );
        }

        if ($setId <= 0) {
            throw new RuntimeException(
                "Invalid question set ID."
            );
        }

        if ($newText === '') {
            throw new RuntimeException(
                "Question text is required."
            );
        }

        if ($newCategory === '') {
            throw new RuntimeException(
                "Category is required."
            );
        }

        $validCategories = [
            'Punctuality',
            'Communication Skills',
            'Subject Mastery',
            'Teaching Effectiveness',
            'Professionalism',
            'Classroom Management',
            'Assessment & Feedback',
            'Student Engagement',
            'Fairness & Inclusivity'
        ];

        if (!in_array(
            $newCategory,
            $validCategories,
            true
        )) {
            throw new RuntimeException(
                "Invalid category."
            );
        }


        $question = $this->questionRepo->findQuestionById(
            $questionId
        );

        if (!$question) {
            throw new RuntimeException(
                "Question not found."
            );
        }

        if ((int) $question['set_id'] !== $setId) {
            throw new RuntimeException(
                "The question does not belong to the selected question set."
            );
        }

        if (!$this->questionRepo->findById($setId)) {
            throw new RuntimeException(
                "The selected question set does not exist."
            );
        }

        $evaluationCount =
            $this->questionRepo->countQuestionEvaluationPeriods(
                $questionId
            );

        if ($evaluationCount >= 2) {
            throw new RuntimeException(
                "This question has already been used in multiple evaluations and can no longer be edited."
            );
        }


        if (
            $question['question_text'] === $newText
            &&
            $question['category'] === $newCategory
        ) {
            return [
                'status' => 'warning',
                'message' => 'No changes detected.'
            ];
        }


        if (
            $this->questionRepo->existsDuplicateQuestion(
                $setId,
                $newText,
                $questionId
            )
        ) {
            throw new RuntimeException(
                "Another question in this set already uses this exact text."
            );
        }


        try {

            $this->pdo->beginTransaction();

            $this->questionRepo->updateQuestion(
                $questionId,
                $newText,
                $newCategory
            );

            $this->pdo->commit();

            return [
                'status' => 'success',
                'question_id' => $questionId,
                'set_id' => $setId,
                'question_text' => $newText,
                'category' => $newCategory,
                'message' => 'Question updated successfully.'
            ];

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }


    /**
     * Activate an inactive question.
     */
    public function activateQuestion(
        array $data
    ): array {

        $questionId = (int) ($data['question_id'] ?? 0);

        if ($questionId <= 0) {
            throw new RuntimeException(
                "Invalid question."
            );
        }


        // Find question
        $question = $this->questionRepo->findQuestionById(
            $questionId
        );

        if (!$question) {
            throw new RuntimeException(
                "Question not found."
            );
        }


        // Already active
        if ((int) $question['is_active'] === 1) {
            return [
                'status' => 'warning',
                'question_id' => $questionId,
                'message' => 'Question is already active.'
            ];
        }


        try {

            $this->pdo->beginTransaction();

            $this->questionRepo->activateQuestion(
                $questionId
            );

            $this->pdo->commit();

            return [
                'status' => 'success',
                'question_id' => $questionId,
                'message' => 'Question activated successfully.'
            ];

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }


    /**
 * Delete or deactivate a question.
 */
    public function deleteQuestion(
        array $data
    ): array {

        $questionId = (int) ($data['question_id'] ?? 0);


        if ($questionId <= 0) {
            throw new RuntimeException(
                "Question ID is required."
            );
        }


        $question = $this->questionRepo->findQuestionById(
            $questionId
        );

        if (!$question) {
            throw new RuntimeException(
                "Question not found."
            );
        }


        $setId = (int) $question['set_id'];


        $answerCount =
            $this->questionRepo->countQuestionAnswers(
                $questionId
            );


        if ($answerCount > 0) {

            try {

                $this->pdo->beginTransaction();

                $this->questionRepo->deactivateQuestion(
                    $questionId
                );

                $this->pdo->commit();

                return [
                    'status' => 'warning',
                    'question_id' => $questionId,
                    'message' =>
                        "Question has {$answerCount} student answers. " .
                        "It was deactivated instead of deleted."
                ];

            } catch (\Throwable $e) {

                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }

                throw $e;
            }
        }


        $activeQuestions =
            $this->questionRepo->countActiveQuestionsBySet(
                $setId
            );

        $minimumRequired = 1;


        if ($activeQuestions <= $minimumRequired) {

            throw new RuntimeException(
                "Cannot delete. Minimum {$minimumRequired} " .
                "active question(s) required in this set."
            );
        }


        try {

            $this->pdo->beginTransaction();

            $this->questionRepo->deleteQuestion(
                $questionId
            );

            $this->pdo->commit();

            return [
                'status' => 'success',
                'question_id' => $questionId,
                'message' => 'Question deleted successfully.'
            ];

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function getActiveSet(): array
    {

        return $this->questionRepo->getActiveQuestionSets();

    }

}
