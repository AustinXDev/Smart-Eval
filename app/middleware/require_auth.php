<?php 
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/redirect.php';

// Check if user is logged in, if not redirect to Login page
if(!checkLogin()){
  redirect('../../views/auth/Login.view.php');
}

?>