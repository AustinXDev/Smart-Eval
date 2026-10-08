<?php

namespace App\Controllers\students;

use App\Services\Student\TeacherSelectionService;

class TeacherSelectionController
{
    public function __construct(
        private TeacherSelectionService $service
    ) {
    }

    public function setTeacherSelection(
        array $student,
        array $teacherIds
    ): array {

        return $this->service->selectedTeachers(
            $student,
            $teacherIds
        );

    }

}
