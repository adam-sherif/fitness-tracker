<?php
// ============================================================
//  index.php — Dashboard Entry Point
// ============================================================
//  This file has ONE job: wire the controller to the view.
//  All business logic lives in DashboardController.
//  All HTML lives in views/dashboard.view.php.
// ============================================================

require_once 'controllers/DashboardController.php';

$controller = new DashboardController(); // Auth check happens inside constructor
$data       = $controller->handle();     // Returns all data the view needs
$pageTitle  = $data['pageTitle'];

require_once 'includes/header.php';      // Outputs <nav> + opens <main>
require_once 'views/dashboard.view.php'; // Renders the page HTML using $data
require_once 'includes/footer.php';      // Closes <main>, outputs <footer>
