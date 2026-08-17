<?php 

namespace App\middleware;

class AdminAuthMiddleware
{

  public static function handle(): void
  {

    if(
      empty($_SESSION['admin_authenticated']) ||
      empty($_SESSION['admin_id'])
    ) {

      header('Location: /Smart-Eval/public/login');
      exit;

    }

  }

}

?>