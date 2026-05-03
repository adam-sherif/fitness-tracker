<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? SITE_NAME) ?> | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
// ── Determine active nav link ─────────────────────────────────
// basename() strips the directory path, leaving just "meals.php" etc.
$currentPage = basename($_SERVER['PHP_SELF']);

// ── Nav links definition ──────────────────────────────────────
// Easy to add/remove nav items here without touching each page.
$navLinks = [
    ['href' => 'index.php',    'icon' => '🏠', 'label' => 'Dashboard'],
    ['href' => 'meals.php',    'icon' => '🍽️', 'label' => 'Meals'],
    ['href' => 'macros.php',   'icon' => '📊', 'label' => 'Macros'],
    ['href' => 'workouts.php', 'icon' => '🏋️', 'label' => 'Workouts'],
    ['href' => 'progress.php', 'icon' => '📈', 'label' => 'Progress'],
];
?>

<!-- ===== TOP NAVIGATION BAR ===== -->
<nav class="navbar">

    <!-- Brand Logo -->
    <div class="nav-brand">
        <span class="nav-logo">💪</span>
        <span class="nav-title"><?= SITE_NAME ?></span>
    </div>

    <!-- Mobile Hamburger Toggle -->
    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu">
        &#9776;
    </button>

    <!-- Nav Links -->
    <ul class="nav-links" id="navLinks">
        <?php foreach ($navLinks as $link): ?>
            <li>
                <a href="<?= $link['href'] ?>"
                   class="<?= $currentPage === $link['href'] ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $link['icon'] ?></span>
                    <?= $link['label'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- User Info & Logout -->
    <div class="nav-user">
        <span class="user-greeting">
            👤 <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
        </span>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

</nav>

<!-- ===== MAIN CONTENT WRAPPER ===== -->
<main class="main-content">

<script>
    // Mobile nav toggle — runs immediately when header is included
    document.getElementById('navToggle').addEventListener('click', function () {
        document.getElementById('navLinks').classList.toggle('open');
    });
</script>
