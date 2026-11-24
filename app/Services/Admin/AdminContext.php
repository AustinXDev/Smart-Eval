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

    // Deactivated in Account Management: end the session and go to sign-in.
    // (Only page views use this class; APIs are blocked by ApiSecurity::admin.)
    if (!$admin->isActive()) {
      unset($_SESSION['admin_authenticated'], $_SESSION['admin_id']);
      if (!headers_sent()) {
        header('Location: ' . BASE_URL . 'admin-login');
      }
      exit;
    }

    return $admin;

  } 

  public function getRole(): string
  {

    $admin = $this->getCurrentAdmin();

    return $admin->roleKeyValue();

  }

}

?>