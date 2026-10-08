<?php

class ReportValidator
{
    public static function validate($type, $params)
    {
        $required = [
            'faculty' => ['period_id'],
            'teacher' => ['teacher_id', 'period_id'],
            'period'  => ['period_id']
        ];

        if (!isset($required[$type])) {
            die("Invalid report type");
        }

        foreach ($required[$type] as $field) {
            if (empty($params[$field])) {
                die("Missing parameter: $field");
            }
        }
    }
}