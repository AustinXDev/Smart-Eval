<?php

namespace App\Services\TeacherServices;

use App\Repositories\TeacherRepo\TeacherRepository;
use RuntimeException;

class TeacherLoadService
{
    public function __construct(
        private TeacherRepository $teacherRepo
    ) {
    }

    public function create(
        array $input
    ): array {

        $teacherId = (int) ($input['teacher_id'] ?? 0);

        $programId = (int) ($input['program'] ?? 0);

        $yearLevel = trim($input['level'] ?? '');


        //Validate
        if (
            $teacherId <= 0 ||
            $programId <= 0 ||
            $yearLevel === ''
        ) {

            throw new RuntimeException(
                'Select Teacher, Program, and Year Level.'
            );

        }


        //Check teacher status
        if (
            !$this->teacherRepo
                  ->isTeacherActive($teacherId)
        ) {

            throw new RuntimeException(
                "This teacher is inactive or does not exist."
            );

        }


        //Check Duplicate Load
        if (
            $this->teacherRepo
                ->existLoad($teacherId, $programId, $yearLevel)
        ) {

            throw new RuntimeException(
                "This teacher is already assigned to this program and year level."
            );

        }


        //Check Duplicate Load but inactive
        $inactiveLoadId = $this->teacherRepo->findInactiveLoadId(
            $teacherId,
            $programId,
            $yearLevel
        );

        if (
            $inactiveLoadId !== null
        ) {

            $updated = $this->update($inactiveLoadId);

            if (!$updated) {
                throw new RuntimeException(
                    "Unable to create this load."
                );
            }

            return [
                'load_id' => $inactiveLoadId,
                'message' => 'Handle reactivated successfully.'
            ];

        }


        /**
         * Create load
         */
        $loadId = $this->teacherRepo
                      ->createLoad(
                          $teacherId,
                          $programId,
                          $yearLevel
                      );

        return [
            'load_id' => $loadId,
            'message' => 'Handle added successfully.'
        ];


    }


    public function update(
        int $loadId
    ): int {

        return $this->teacherRepo->reactivateLoad(
            $loadId
        );

    }

    public function delete(
        array $input
    ): array {

        $teacherId = (int) ($input['teacher_id'] ?? 0);

        $loadId = (int) ($input['load_id'] ?? 0);

        //Validate
        if (
            $teacherId <= 0 ||
            $loadId <= 0
        ) {

            throw new RuntimeException(
                'Teacher ID and Load ID are required.'
            );

        }


        $load = $this->teacherRepo->findLoadById($teacherId, $loadId);

        if (!$load) {

            throw new RuntimeException(
                'Teacher load not found.'
            );

        }


        /**
         * No submitted evaluations.
         *
         * Safe to permanently delete.
         */
        $deleted =
            $this->teacherRepo
                ->deleteLoad($loadId);

        if ($deleted === 0) {

            throw new RuntimeException(
                'Failed to delete teacher load.'
            );

        }


        return [
            'action' => 'deleted',
            'load_id' => $loadId,
            'teacher_id' => $teacherId,
            'evaluation_count' => 0,
            'message' =>
                'Unused teacher load deleted successfully.'
        ];

    }
}
