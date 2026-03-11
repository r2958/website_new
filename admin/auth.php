<?
/**
 * Admin Authentication Check
 * Include this file at the beginning of every admin page
 */

// Note: session_start() is already called in application.php

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Get current URL for redirect after login
    $current_page = basename($_SERVER['PHP_SELF']);
    $redirect = $current_page;

    // Preserve query string if any
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect .= '?' . $_SERVER['QUERY_STRING'];
    }

    // Redirect to login page
    header('Location: login.php?redirect=' . urlencode($redirect));
    exit;
}

// Optional: Check session timeout (30 minutes)
$session_timeout = 1800; // 30 minutes
if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > $session_timeout) {
    // Session expired
    session_destroy();
    header('Location: login.php?error=session_expired');
    exit;
}

// Update last activity time
$_SESSION['admin_login_time'] = time();
