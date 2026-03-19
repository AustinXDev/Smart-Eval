<?php 
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/redirect.php';

if(!isAdminLoggedIn()){
 redirect('/Smart-Eval/views/admin/login.view.php');
}
?>