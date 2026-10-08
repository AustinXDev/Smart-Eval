<?php 
require_once '../config/database.php';

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);   // set 1 on HTTPS
ini_set('session.use_strict_mode', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>