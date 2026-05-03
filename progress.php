<?php
// ============================================================
//  progress.php — Progress Tracker Entry Point
// ============================================================
//  Full CRUD entry point for body composition check-ins.
//
//  Supported requests:
//    GET  progress.php                    → list all entries + add form
//    GET  progress.php?action=edit&id=N   → load entry N into form
//    GET  progress.php?action=delete&id=N → delete entry N (redirect)
//    POST progress.php (action=add)       → create new entry (redirect)
//    POST progress.php (action=update)    → update entry (redirect)
// ============================================================

require_once 'controllers/ProgressController.php';

$controller = new ProgressController();
$data       = $controller->handle();
$pageTitle  = $data['pageTitle'];

require_once 'includes/header.php';
require_once 'views/progress.view.php';
require_once 'includes/footer.php';
