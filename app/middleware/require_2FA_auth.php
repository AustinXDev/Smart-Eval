<?php 
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/redirect.php';


if(!is2FAPending()) {

  if(isset($_SESSION['2fa_expires_at']) && $_SESSION['2fa_expires_at'] < time()){
    unset($_SESSION['2fa_pending'], $_SESSION['2fa_admin_id'], $_SESSION['2fa_expires_at']);
    redirect('/Smart-Eval/views/admin/login.view.php?error=expired');
    exit;
  }

  redirect('/Smart-Eval/views/admin/login.view.php');
  exit;
}

?>