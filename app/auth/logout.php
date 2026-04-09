<?php 
require_once __DIR__ . '/../helpers/session.php';

// Logout student
logoutStudent();

// Redirect to login page
header('Location: /Smart-Eval/views/auth/login.view.php');
exit();
?>