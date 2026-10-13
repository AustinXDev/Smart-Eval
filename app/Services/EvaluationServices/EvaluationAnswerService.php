<?php

namespace App\Services\EvaluationServices;

use App\Repositories\EvaluationRepo\EvaluationAnswerRepository;
use App\Services\EvaluationServices\EvaluationStatusService;
use RuntimeException;
use PDO;

class EvaluationAnswerService
{
    public function __construct(
        private EvaluationAnswerRepository $evalAnswerRepo,
        private EvaluationStatusService $evalStatusService,
        private PDO $pdo
    ) {
    }

    public function submitAnswers(array $data, string $studentId): array
    {
        $evalId = filter_var($data['eval_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $teacherId = filter_var($data['teacher_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $answers = $data['answers'] ?? null;
        $comment = $data['comment'] ?? '';
        if (!$evalId || !$teacherId || $studentId === '') {
            throw new EvaluationRequestException('Invalid evaluation or teacher ID.');
        }
        if (!is_array($answers) || $answers === [] || count($answers) > 500) {
            throw new EvaluationRequestException('A valid answer object is required.');
        }
        if (!is_string($comment) || mb_strlen($comment) > 5000) {
            throw new EvaluationRequestException('Comment must not exceed 5000 characters.');
        }
        $comment = trim($comment);
        $normalized = [];
        foreach ($answers as $questionId => $score) {
            $id = filter_var($questionId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$id || !is_int($score) || $score < 1 || $score > 5) {
                throw new EvaluationRequestException('Answers must contain valid question IDs and integer scores from 1 to 5.');
            }
            $normalized[$id] = $score;
        }
        $answers = $normalized;

        try {

            $this->pdo->beginTransaction();

            $assignment = $this->evalAnswerRepo->lockOwnedEvaluation($evalId, $studentId, $teacherId);
            if (!$assignment) {
                throw new EvaluationRequestException('You cannot submit this evaluation.', 403);
            }
            if (!(bool) $assignment['is_open']) {
                throw new EvaluationRequestException('The evaluation period is closed.', 409);
            }
            $required = $this->evalAnswerRepo->getRequiredQuestionIds((int) $assignment['set_id']);
            $submitted = array_keys($answers);
            sort($required);
            sort($submitted);
            if ($required === [] || $required !== $submitted) {
                throw new EvaluationRequestException('Answer every question in the evaluation questionnaire.');
            }

            $existingAnswers =
                $this->evalAnswerRepo->getAnswersByEvalId($evalId);

            $existingComment =
                $this->evalAnswerRepo->getCommentByEvalId($evalId);

            // No existing answers = first submission
            $isFirstSubmission = empty($existingAnswers);

            if ($isFirstSubmission) {

                $this->evalAnswerRepo->saveAnswer(
                    $evalId,
                    $answers
                );

                $this->evalAnswerRepo->saveComment(
                    $evalId,
                    $comment
                );

                $action = 'created';

            } else {

                $answersChanged = $this->answersChanged(
                    $existingAnswers,
                    $answers
                );

                $commentChanged = $this->commentChanged(
                    $existingComment,
                    $comment
                );

                $hasChanges =
                    $answersChanged ||
                    $commentChanged;

                if ($hasChanges) {

                    $this->evalAnswerRepo->updateAnswers(
                        $evalId,
                        $answers
                    );

                    $this->evalAnswerRepo->saveComment(
                        $evalId,
                        $comment
                    );

                    $action = 'updated';

                } else {

                    $action = 'unchanged';
                }
            }

            $this->evalStatusService->markAsSubmitted($evalId);

            $this->pdo->commit();

            return [
                'status' => 'success',
                'eval_id' => $evalId,
                'teacher_id' => $teacherId,
                'action' => $action
            ];

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($e instanceof EvaluationRequestException) {
                throw $e;
            }

            error_log($e->getMessage());

            return [
                'status' => 'error',
                'message' => 'Unable to save evaluation.'
            ];
        }
    }


    private function answersChanged(
        array $existing,
        array $submitted
    ): bool {

        $existing = array_map('intval', $existing);
        $submitted = array_map('intval', $submitted);

        ksort($existing);
        ksort($submitted);

        return $existing !== $submitted;
    }


    private function commentChanged(
        string $existing,
        string $submitted
    ): bool {
        return trim($existing) !== trim($submitted);
    }

}
