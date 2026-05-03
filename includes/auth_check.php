<?php
// ============================================================
//  includes/auth_check.php — Session Authentication Guard
// ============================================================
//  Included at the top of every Controller constructor.
//  Redirects to login.php if the user is not authenticated.
//
//  Usage (inside a controller constructor):
//      require_once __DIR__ . '/../includes/auth_check.php';
// ============================================================

require_once __DIR__ . '/../config.php'; // Ensures session is started

if (!isset($_SESSION['user_id'])) {
    // Save the attempted URL so we can redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}
