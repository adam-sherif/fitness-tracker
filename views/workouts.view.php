<?php
// ============================================================
//  views/workouts.view.php — Workout Plans HTML Template
// ============================================================
//  Receives $data from WorkoutController::handle().
//
//  Available $data keys:
//    workouts, categoryCounts, filterCategory, filterDifficulty,
//    categoryMeta, difficultyMeta, validCategories, validDifficulties
// ============================================================
?>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">🏋️ Workout Plans</h1>
        <p class="page-subtitle">
            <?= count($data['workouts']) ?> curated workout plans. Find your perfect training session.
        </p>
    </div>
</div>

<!-- ===== FILTER BAR ===== -->
<div class="filter-bar">

    <!-- Category Filter -->
    <div class="filter-group">
        <span class="filter-label">Category:</span>

        <a href="workouts.php<?= $data['filterDifficulty'] ? '?difficulty=' . $data['filterDifficulty'] : '' ?>"
           class="filter-btn <?= $data['filterCategory'] === '' ? 'active' : '' ?>">
            All
        </a>

        <?php foreach ($data['validCategories'] as $cat):
            $meta = $data['categoryMeta'][$cat];
            $href = 'workouts.php?category=' . $cat
                    . ($data['filterDifficulty'] ? '&difficulty=' . $data['filterDifficulty'] : '');
        ?>
            <a href="<?= $href ?>"
               class="filter-btn <?= $data['filterCategory'] === $cat ? 'active' : '' ?>">
                <?= $meta['icon'] ?> <?= $meta['label'] ?>
                <span class="filter-count"><?= $data['categoryCounts'][$cat] ?? 0 ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Difficulty Filter -->
    <div class="filter-group">
        <span class="filter-label">Difficulty:</span>

        <a href="workouts.php<?= $data['filterCategory'] ? '?category=' . $data['filterCategory'] : '' ?>"
           class="filter-btn <?= $data['filterDifficulty'] === '' ? 'active' : '' ?>">
            All
        </a>

        <?php foreach ($data['validDifficulties'] as $diff):
            $meta = $data['difficultyMeta'][$diff];
            $href = 'workouts.php?difficulty=' . $diff
                    . ($data['filterCategory'] ? '&category=' . $data['filterCategory'] : '');
        ?>
            <a href="<?= $href ?>"
               class="filter-btn <?= $data['filterDifficulty'] === $diff ? 'active' : '' ?>">
                <?= $meta['icon'] ?> <?= $meta['label'] ?>
            </a>
        <?php endforeach; ?>
    </div>

</div>

<!-- ===== WORKOUT CARDS ===== -->
<?php if (empty($data['workouts'])): ?>
    <div class="card">
        <div class="card-body">
            <p class="empty-state">
                No workouts match your filters. <a href="workouts.php">Clear filters</a>.
            </p>
        </div>
    </div>

<?php else: ?>
    <div class="workouts-grid">
        <?php foreach ($data['workouts'] as $w):
            $cat   = $data['categoryMeta'][$w['category']]    ?? ['icon' => '🏃', 'color' => 'cat-cardio', 'label' => $w['category']];
            $diff  = $data['difficultyMeta'][$w['difficulty']] ?? ['icon' => '🟢', 'label' => $w['difficulty']];

            // Parse exercises: newline-separated text → array of strings
            $exerciseLines = array_filter(
                array_map('trim', explode("\n", $w['exercises'] ?? '')),
                fn($line) => $line !== ''
            );
        ?>
        <div class="workout-card <?= $cat['color'] ?>">

            <!-- Category + Difficulty -->
            <div class="workout-card-header">
                <span class="workout-cat-icon"><?= $cat['icon'] ?></span>
                <div class="workout-meta-top">
                    <span class="workout-cat-label"><?= $cat['label'] ?></span>
                    <span class="workout-diff"><?= $diff['icon'] ?> <?= $diff['label'] ?></span>
                </div>
            </div>

            <!-- Name & Description -->
            <h3 class="workout-name"><?= htmlspecialchars($w['name']) ?></h3>
            <p class="workout-desc"><?= htmlspecialchars($w['description']) ?></p>

            <!-- Badges -->
            <div class="workout-badges">
                <span class="badge badge-duration">⏱️ <?= $w['duration'] ?> min</span>
                <span class="badge badge-exercises">📋 <?= count($exerciseLines) ?> exercises</span>
            </div>

            <!-- Collapsible Exercise List -->
            <details class="exercise-details">
                <summary class="exercise-summary">View Exercises ▾</summary>
                <ul class="exercise-list">
                    <?php foreach ($exerciseLines as $line): ?>
                        <li><?= htmlspecialchars($line) ?></li>
                    <?php endforeach; ?>
                </ul>
            </details>

            <!-- Start Button -->
            <button class="btn-workout" onclick="alert('Workout started! Good luck 💪')">
                Start Workout →
            </button>

        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ===== TIPS ===== -->
<div class="card tips-card">
    <div class="card-header"><h2>💡 General Training Tips</h2></div>
    <div class="card-body tips-grid">
        <div class="tip-item"><span>💧</span><p>Drink water before, during, and after every session.</p></div>
        <div class="tip-item"><span>🔥</span><p>Always warm up for 5–10 minutes before training.</p></div>
        <div class="tip-item"><span>😴</span><p>Aim for 7–9 hours of sleep for optimal recovery.</p></div>
        <div class="tip-item"><span>🍗</span><p>Consume protein within 30 minutes post-workout.</p></div>
    </div>
</div>
