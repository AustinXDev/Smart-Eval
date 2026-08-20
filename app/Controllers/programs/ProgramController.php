<?php 

namespace App\Controllers\programs;

use App\Services\ProgramServices\ProgramService;

class ProgramController
{

  public function __construct(
    private ProgramService $service
  )
  {
  }


  public function getProgramByDepartment(
    string $department
  ): array {

    return $this->service->getProgramByDepartment($department);

  }

}

?>