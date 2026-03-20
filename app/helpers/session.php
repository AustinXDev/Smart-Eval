<?php 
// app/helpers/session.php

// Start session if not already started
function startSession() {
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
}

// Check if student is logged in
function isStudentLoggedIn() {
  startSession();
  return isset($_SESSION['student_id']);
}

// Get current logged in student
function getStudent(){
  startSession();
  return $_SESSION['student_id'] ?? null;
}


// Check if admin is logged in
function isAdminLoggedIn() {
  startSession();
  return isset($_SESSION['admin_id']) && $_SESSION['is_admin'] === true && !isset($_SESSION['2fa_pending']);
  
}

//Get current logged in admin
function getAdmin(){
  startSession();
  return [
    'admin_id' => $_SESSION['admin_id'] ?? null,
    'username' => $_SESSION['username'] ?? null,
    'role' => $_SESSION['role'] ?? null,
  ];
}

//check if 2FA is pending
function is2FAPending() {
    startSession();
      return isset($_SESSION['2fa_pending']) 
        && $_SESSION['2fa_pending'] === true
        && isset($_SESSION['2fa_admin_id'])
        && $_SESSION['2fa_expires_at'] > time();
}

//get the admin id stored during 2FA pending state
function get2FAAdminId() {
    startSession();
    return $_SESSION['2fa_admin_id'] ?? null;
}

//clear 2FA session flags after successful verification
function clear2FASession() {
    startSession();
    unset($_SESSION['2fa_pending'], $_SESSION['2fa_admin_id']);
}

// Logout student
function logoutStudent() {
  startSession();
  unset($_SESSION['student_id']);
}

// Logout admin
function logoutAdmin() {
   startSession();
    unset($_SESSION['admin_id'], $_SESSION['is_admin'], $_SESSION['username'], $_SESSION['role']);
    session_destroy();
}

?>