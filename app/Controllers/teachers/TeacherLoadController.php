<?php 

namespace App\Controllers\teachers;

use App\Services\TeacherServices\TeacherLoadService;

class TeacherLoadController
{

  public function __construct(
    private TeacherLoadService $service
  )
  {
  }

  /**
   * create teacher load
   */
  public function create(
    array $input
  ): array {

    return $this->service->create($input);

  }


  /**
   * Delete teacher load
   */
  public function delete(
    array $input
  ): array {

      return $this->service->delete(
          $input
      );
  }

}

?>