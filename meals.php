<?php
// ============================================================
//  meals.php — Meal Logger Entry Point
// ============================================================
//  Routes all GET/POST requests through MealController,
//  then renders the result using the meals view.
//
//  Supported requests:
//    GET  meals.php            → show today's meals + log form
//    GET  meals.php?date=DATE  → show meals for a specific date
//    GET  meals.php?delete=ID  → delete a meal (then redirect)
//    POST meals.php            → add a new meal (then redirect)
// ============================================================

require_once 'controllers/MealController.php';

$controller = new MealController();
$data       = $controller->handle();
$pageTitle  = $data['pageTitle'];

require_once 'includes/header.php';
require_once 'views/meals.view.php';
require_once 'includes/footer.php';
