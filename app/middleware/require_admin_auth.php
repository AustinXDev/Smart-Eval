<?php 
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/redirect.php';

if(is2FAPending()) {
  redirect('/Smart-Eval/views/admin/login.view.php');
}

if(!isAdminLoggedIn()){
  redirect('/Smart-Eval/views/admin/login.view.php');
}
?>