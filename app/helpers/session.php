<?php 
// app/helpers/session.php

// Start session if not already started
function startSession() {
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
}

// Check if user is logged in
function checklogin() {
  startSession();
  return isset($_SESSION['student_id']);
}

// Get current logged in user
function getUser(){
  startSession();
  return $_SESSION['student_id'] ?? null;
}

// Logout user
function logout() {
  startSession();
  session_unset();
  session_destroy();
}

?>