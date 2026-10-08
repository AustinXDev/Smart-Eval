<?php

namespace App\Controllers\students;

use App\Services\Student\EnrollmentSelectionService;

class EnrollmentSelectionController
{
    public function __construct(
        private EnrollmentSelectionService $service
    ) {
    }

    public function selectEnrollmentType(
        array $student,
        string $enrollmentType
    ): array {

        return $this->service->setEnrollmentType(
            $student,
            $enrollmentType
        );

    }

}
