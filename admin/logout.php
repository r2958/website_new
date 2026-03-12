<?php
// Start session before modifying it
session_start();

// Only clear admin-related session data, preserve user session
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_login_time']);

// Redirect to login page with logout parameter to prevent auto-redirect
header('Location: /admin/login.php?logout=1');
exit;
