<?php  
require_once __DIR__ . '/../helpers/session.php';

// Only attempt logout if admin is logged in
if (isAdminLoggedIn() || is2FAPending()) {
    // Clear 2FA session if pending
    clear2FASession();

    // Logout the admin
    logoutAdmin();
}

// Redirect to login page after logout
header("Location:  admin/login.php");
exit;

?>