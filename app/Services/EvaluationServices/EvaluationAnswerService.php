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

    public function submitAnswers(array $data): array
    {
        $evalId = (int) ($data['eval_id'] ?? 0);
        $teacherId = (int) ($data['teacher_id'] ?? 0);
        $answers = $data['answers'];
        $comment = trim($data['comment'] ?? '');

        if ($evalId < 1 || $teacherId < 1) {
            throw new RuntimeException(
                'Invalid evaluation or teacher ID.'
            );
        }

        if (empty($answers)) {
            throw new RuntimeException(
                'No answers were provided.'
            );
        }

        try {

            $this->pdo->beginTransaction();

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
