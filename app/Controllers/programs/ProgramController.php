<?php

namespace App\Controllers\programs;

use App\Models\Program;
use App\Services\ProgramServices\ProgramService;

class ProgramController
{
    public function __construct(
        private ProgramService $service
    ) {
    }


    public function getProgramByDepartment(
        string $department
    ): array {

        return $this->service->getProgramByDepartment($department);

    }


    public function getAllProgram(): array
    {
        return $this->service->getAllProgram();
    }


    public function create(array $data)
    {

        return $this->service->create($data);

    }


    public function update(array $data): array
    {

        return $this->service
                ->update($data)
                ->toArray();

    }

    public function delete(array $data): array
    {

        return $this->service->delete($data);

    }

}
