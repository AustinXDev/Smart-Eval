<?php 

namespace App\Services\ProgramServices;

use App\Repositories\ProgramRepo\ProgramRepository;
use RuntimeException;

class ProgramService
{ 

  public function __construct(
    private ProgramRepository $programRepo
  )
  {
  }

  public function getProgramByDepartment(
    string $department
  ): array {

    $department = trim($department);

    if($department === ''){

      throw new RuntimeException(
        "Department is missing."
      );

    }

    return $this->programRepo->getProgramByDepartment($department);

  }

}

?>