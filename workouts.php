<?php
// ============================================================
//  workouts.php — Workout Plans Entry Point
// ============================================================
//  Fetches pre-defined workouts from the DB with optional
//  category/difficulty filtering via GET params.
//
//  Supported GET params:
//    ?category=strength|cardio|hiit|flexibility
//    ?difficulty=beginner|intermediate|advanced
//    (params can be combined)
// ============================================================

require_once 'controllers/WorkoutController.php';

$controller = new WorkoutController();
$data       = $controller->handle();
$pageTitle  = $data['pageTitle'];

require_once 'includes/header.php';
require_once 'views/workouts.view.php';
require_once 'includes/footer.php';
