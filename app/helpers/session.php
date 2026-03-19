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
  return isset($_SESSION['admin_id']) && $_SESSION['is_admin'] === true;
}

//Get current logged in admin
function getAdmin(){
  startSession();
  return ($_SESSION['admin_id']) ?? null;
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