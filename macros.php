<?php
// ============================================================
//  macros.php — Macros Calculator Entry Point
// ============================================================
//  Queries totals for the selected date (GET ?date=YYYY-MM-DD),
//  calculates macro breakdowns, and renders the macros view.
// ============================================================

require_once 'controllers/MacroController.php';

$controller = new MacroController();
$data       = $controller->handle();
$pageTitle  = $data['pageTitle'];

require_once 'includes/header.php';
require_once 'views/macros.view.php';
require_once 'includes/footer.php';
