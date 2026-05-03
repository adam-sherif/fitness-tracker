<?php
// ============================================================
//  logout.php — Session Destruction
// ============================================================
//  Destroys the session completely and redirects to login.
//  No controller or view needed — this is a pure action file.
//
//  Steps:
//    1. Start session (via config.php)
//    2. Clear all session variables
//    3. Delete the session cookie from the browser
//    4. Destroy the server-side session
//    5. Redirect to login page
// ============================================================

require_once 'config.php'; // Calls session_start() if not already started

// 1. Wipe all session data
$_SESSION = [];

// 2. Expire the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destroy the session file on the server
session_destroy();

// 4. Send user back to login
header('Location: login.php');
exit();
