<?php 

namespace App\Services\Admin;

use App\Repositories\AdminRepo\AdminRepository;
use RuntimeException;

class AdminContext 
{

  public function __construct(
    private AdminRepository $admins
  )
  {
  }

  public function getCurrentAdmin(){

    $adminId = $_SESSION['admin_id'] ?? '';

    if(!$adminId) {
      throw new RuntimeException(
          'Administrator session not found.'
      );
    }

    $admin = $this->admins->findById(
      (int) $adminId
    );

    if(!$admin) {
       throw new RuntimeException(
            'Administrator account not found.'
        );
    }

    return $admin;

  } 

  public function getRole(): string
  {

    $admin = $this->getCurrentAdmin();

    return strtolower(
        str_replace(' ', '_', $admin->role)
    );

  }

}

?>