<?php 

namespace App\Controllers\teachers;

use App\Services\TeacherServices\TeacherService;

class TeacherController
{

  public function __construct(
    private TeacherService $service
  )
  {
  }

  public function create(
    array $data,
    ?array $photo = null
  ): array {

    $teacher = $this->service->create(
      $data,
      $photo
    );

    return [
      'teachers' => [
        'teacher_id' => $teacher->teacherId,
        'employee_id' => $teacher->employeeId,
        'full_name' => $teacher->fullName,
        'email' => $teacher->email,
        'department' => $teacher->department,
        'image_path' => $teacher->imagePath,
        'is_active' => $teacher->isActive
      ]
    ];

  }

  /**
   * Update Teacher
   */
  public function update(
    array $input,
    ?array $photo = null
  ): array {

    return $this->service->update(
      $input,
      $photo
    );

  }

  /**
   * Delete Teacher
   */
  public function delete(
    int $teacherId
  ): array  {


    return $this->service->delete($teacherId);

  }


  public function getAll(
    string $department
  ): array {

    return $this->service->getAll($department);

  }

  public function getById(
    int $teacherId
  ): array {

    return $this->service->getById($teacherId);

  }

}

?>