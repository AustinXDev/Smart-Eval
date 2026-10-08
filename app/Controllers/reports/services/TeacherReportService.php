<?php

require_once __DIR__ . '/../repositories/EvaluationRepository.php';

class TeacherReportService
{
    public function generate($params)
    {
        $repo = new EvaluationRepository();

        $teacherId = $params['teacher_id'];
        $periodId  = $params['period_id'];

        return [
            'view' => 'teacher_report.php',
            'data' => [
                'teacher' => $repo->getTeacherEvaluation($teacherId, $periodId)
            ]
        ];
    }
}

?>