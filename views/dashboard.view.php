<?php
// ============================================================
//  views/dashboard.view.php — Dashboard HTML Template
// ============================================================
//  Receives $data from DashboardController::handle().
//  Wrapped by header.php + footer.php in index.php.
//
//  Available $data keys:
//    greeting, username, today, todayMacros, recentMeals,
//    latestProgress, calorieGoal, goalLabel
// ============================================================

// Helper: calculate % of goal (capped at 100 for progress bar width)
$caloriePercent = $data['calorieGoal'] > 0
    ? min(100, round($data['todayMacros']['total_calories'] / $data['calorieGoal'] * 100))
    : 0;
?>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <?= $data['greeting'] ?>, <?= htmlspecialchars($data['username']) ?>! 👋
        </h1>
        <p class="page-subtitle">
            Overview for <strong><?= date('l, F j, Y') ?></strong>
        </p>
    </div>
    <span class="goal-badge"><?= $data['goalLabel'] ?></span>
</div>

<!-- ===== MACRO STATS CARDS ===== -->
<div class="stats-grid">

    <div class="stat-card stat-card--calories">
        <div class="stat-icon">🔥</div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format($data['todayMacros']['total_calories']) ?></span>
            <span class="stat-label">Calories Today</span>
            <span class="stat-sub">Goal: <?= number_format($data['calorieGoal']) ?> kcal</span>
        </div>
    </div>

    <div class="stat-card stat-card--protein">
        <div class="stat-icon">🥩</div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format($data['todayMacros']['total_protein'], 1) ?>g</span>
            <span class="stat-label">Protein Today</span>
            <span class="stat-sub"><?= $data['todayMacros']['meal_count'] ?> meals logged</span>
        </div>
    </div>

    <div class="stat-card stat-card--carbs">
        <div class="stat-icon">🌾</div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format($data['todayMacros']['total_carbs'], 1) ?>g</span>
            <span class="stat-label">Carbs Today</span>
        </div>
    </div>

    <div class="stat-card stat-card--fats">
        <div class="stat-icon">🥑</div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format($data['todayMacros']['total_fats'], 1) ?>g</span>
            <span class="stat-label">Fats Today</span>
        </div>
    </div>

</div>

<!-- ===== CALORIE PROGRESS BAR ===== -->
<div class="card">
    <div class="card-header">
        <h2>Daily Calorie Progress</h2>
        <span>
            <?= number_format($data['todayMacros']['total_calories']) ?> /
            <?= number_format($data['calorieGoal']) ?> kcal
        </span>
    </div>
    <div class="card-body">
        <div class="progress-bar-wrap">
            <div class="progress-bar <?= $caloriePercent >= 100 ? 'bar-over' : 'bar-good' ?>"
                 style="width: <?= $caloriePercent ?>%">
                <?= $caloriePercent ?>%
            </div>
        </div>
        <p class="progress-note">
            <?php if ($data['todayMacros']['total_calories'] >= $data['calorieGoal']): ?>
                ✅ You've reached your calorie goal for today!
            <?php else: ?>
                <?= number_format($data['calorieGoal'] - $data['todayMacros']['total_calories']) ?> kcal remaining today.
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- ===== BOTTOM ROW ===== -->
<div class="dashboard-row">

    <!-- Recent Meals -->
    <div class="card">
        <div class="card-header">
            <h2>🍽️ Recent Meals</h2>
            <a href="meals.php" class="card-link">Log a meal →</a>
        </div>
        <div class="card-body">
            <?php if (empty($data['recentMeals'])): ?>
                <p class="empty-state">
                    No meals logged yet. <a href="meals.php">Add your first meal</a>.
                </p>
            <?php else: ?>
                <ul class="recent-list">
                    <?php foreach ($data['recentMeals'] as $meal): ?>
                        <li class="recent-item">
                            <span class="recent-name">
                                <?= htmlspecialchars($meal['food_name']) ?>
                            </span>
                            <span class="recent-meta">
                                <?= $meal['calories'] ?> kcal &nbsp;·&nbsp; <?= $meal['meal_date'] ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Latest Progress -->
    <div class="card">
        <div class="card-header">
            <h2>📈 Latest Progress</h2>
            <a href="progress.php" class="card-link">Update →</a>
        </div>
        <div class="card-body">
            <?php if ($data['latestProgress']): ?>
                <div class="progress-snapshot">
                    <div class="snapshot-item">
                        <span class="snapshot-value">
                            <?= $data['latestProgress']['weight']
                                ? $data['latestProgress']['weight'] . ' kg' : '—' ?>
                        </span>
                        <span class="snapshot-label">Weight</span>
                    </div>
                    <div class="snapshot-item">
                        <span class="snapshot-value">
                            <?= $data['latestProgress']['body_fat']
                                ? $data['latestProgress']['body_fat'] . '%' : '—' ?>
                        </span>
                        <span class="snapshot-label">Body Fat</span>
                    </div>
                    <div class="snapshot-item">
                        <span class="snapshot-value">
                            <?= $data['latestProgress']['log_date'] ?>
                        </span>
                        <span class="snapshot-label">Last Entry</span>
                    </div>
                </div>
            <?php else: ?>
                <p class="empty-state">
                    No progress logged yet. <a href="progress.php">Track your first entry</a>.
                </p>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ===== QUICK LINKS ===== -->
<div class="quick-links">
    <a href="meals.php"    class="quick-card">🍽️<span>Log Meal</span></a>
    <a href="macros.php"   class="quick-card">📊<span>View Macros</span></a>
    <a href="workouts.php" class="quick-card">🏋️<span>Workouts</span></a>
    <a href="progress.php" class="quick-card">📈<span>Progress</span></a>
</div>
