<?php
// ============================================================
//  signup.php — Signup Entry Point
// ============================================================
//  Wires AuthController::handleSignup() to the signup view.
//  On successful signup, the controller redirects to login.php.
// ============================================================

require_once 'controllers/AuthController.php';

$controller = new AuthController();
$data       = $controller->handleSignup();   // Returns ['errors', 'old']

require_once 'views/auth/signup.view.php';   // Renders form using $data
