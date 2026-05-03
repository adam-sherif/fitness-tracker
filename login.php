<?php
// ============================================================
//  login.php — Login Entry Point
// ============================================================
//  Wires AuthController::handleLogin() to the login view.
//  No logic here — the controller handles all GET/POST logic.
// ============================================================

require_once 'controllers/AuthController.php';

$controller = new AuthController();
$data       = $controller->handleLogin();   // Returns ['error', 'flash', 'oldUser']

require_once 'views/auth/login.view.php';   // Renders form using $data
