<?php
// ============================================================
//  views/macros.view.php — Macros Calculator HTML Template
// ============================================================
//  Receives $data from MacroController::handle().
//
//  Available $data keys:
//    selectedDate, goals, totals, meals, trend, calorieSplit
// ============================================================

// ── Local view helpers ────────────────────────────────────────

/**
 * Calculates % of goal, capped at 100 for bar width.
 */
function pct(float $actual, float $goal): float {
    return $goal > 0 ? min(100, round($actual / $goal * 100, 1)) : 0;
}

/**
 * Returns a CSS class for the progress bar based on % filled.
 */
function barClass(float $pct): string {
    if ($pct < 50)   return 'bar-low';
    if ($pct < 90)   return 'bar-medium';
    if ($pct <= 100) return 'bar-good';
    return 'bar-over';
}

$totals = $data['totals'];
$goals  = $data['goals'];
$split  = $data['calorieSplit'];
$trend  = $data['trend'];

$remaining = $goals['calories'] - $totals['total_calories'];
?>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">📊 Macros Calculator</h1>
        <p class="page-subtitle">Your daily macronutrient breakdown and progress toward your goals.</p>
    </div>
    <form method="GET" action="macros.php" class="date-picker-form">
        <label for="date-pick">View date:</label>
        <input
            type="date"
            id="date-pick"
            name="date"
            value="<?= $data['selectedDate'] ?>"
            max="<?= date('Y-m-d') ?>"
        >
        <button type="submit" class="btn-primary">Go</button>
    </form>
</div>

<!-- ===== CALORIE HERO ===== -->
<div class="macros-hero">
    <div class="calorie-ring">
        <div class="ring-value"><?= number_format($totals['total_calories']) ?></div>
        <div class="ring-label">kcal consumed</div>
    </div>
    <div class="calorie-meta">
        <div class="calorie-meta-item">
            <span class="meta-num"><?= number_format($goals['calories']) ?></span>
            <span class="meta-lbl">Goal</span>
        </div>
        <div class="calorie-meta-item">
            <span class="meta-num <?= $remaining < 0 ? 'text-danger' : 'text-success' ?>">
                <?= $remaining < 0 ? '+' . number_format(abs($remaining)) : number_format($remaining) ?>
            </span>
            <span class="meta-lbl"><?= $remaining < 0 ? 'Over' : 'Remaining' ?></span>
        </div>
        <div class="calorie-meta-item">
            <span class="meta-num"><?= $totals['meal_count'] ?></span>
            <span class="meta-lbl">Meals</span>
        </div>
    </div>
</div>

<!-- ===== MACRO PROGRESS BARS ===== -->
<div class="card">
    <div class="card-header"><h2>🎯 Macro Goals Progress</h2></div>
    <div class="card-body">

        <!-- Protein -->
        <?php $pctP = pct($totals['total_protein'], $goals['protein']); ?>
        <div class="macro-row">
            <div class="macro-label">
                <span>🥩 Protein</span>
                <span><?= number_format($totals['total_protein'], 1) ?>g / <?= $goals['protein'] ?>g</span>
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bar <?= barClass($pctP) ?>" style="width:<?= $pctP ?>%">
                    <?= $pctP ?>%
                </div>
            </div>
        </div>

        <!-- Carbs -->
        <?php $pctC = pct($totals['total_carbs'], $goals['carbs']); ?>
        <div class="macro-row">
            <div class="macro-label">
                <span>🌾 Carbs</span>
                <span><?= number_format($totals['total_carbs'], 1) ?>g / <?= $goals['carbs'] ?>g</span>
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bar <?= barClass($pctC) ?>" style="width:<?= $pctC ?>%">
                    <?= $pctC ?>%
                </div>
            </div>
        </div>

        <!-- Fats -->
        <?php $pctF = pct($totals['total_fats'], $goals['fats']); ?>
        <div class="macro-row">
            <div class="macro-label">
                <span>🥑 Fats</span>
                <span><?= number_format($totals['total_fats'], 1) ?>g / <?= $goals['fats'] ?>g</span>
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bar <?= barClass($pctF) ?>" style="width:<?= $pctF ?>%">
                    <?= $pctF ?>%
                </div>
            </div>
        </div>

        <!-- Calories -->
        <?php $pctCal = pct($totals['total_calories'], $goals['calories']); ?>
        <div class="macro-row">
            <div class="macro-label">
                <span>🔥 Calories</span>
                <span><?= number_format($totals['total_calories']) ?> / <?= number_format($goals['calories']) ?> kcal</span>
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bar <?= barClass($pctCal) ?>" style="width:<?= $pctCal ?>%">
                    <?= $pctCal ?>%
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ===== CALORIE SPLIT ===== -->
<?php if ($totals['total_calories'] > 0): ?>
<div class="card">
    <div class="card-header"><h2>🥧 Calorie Split by Macronutrient</h2></div>
    <div class="card-body">
        <p class="split-intro">How your calories are distributed (Protein=4kcal/g, Carbs=4kcal/g, Fats=9kcal/g):</p>
        <div class="calorie-split-bar">
            <div class="split-protein"
                 style="width:<?= $split['proteinPct'] ?>%"
                 title="Protein <?= $split['proteinPct'] ?>%">
                🥩 <?= $split['proteinPct'] ?>%
            </div>
            <div class="split-carbs"
                 style="width:<?= $split['carbsPct'] ?>%"
                 title="Carbs <?= $split['carbsPct'] ?>%">
                🌾 <?= $split['carbsPct'] ?>%
            </div>
            <div class="split-fats"
                 style="width:<?= $split['fatsPct'] ?>%"
                 title="Fats <?= $split['fatsPct'] ?>%">
                🥑 <?= $split['fatsPct'] ?>%
            </div>
        </div>
        <div class="split-legend">
            <span class="legend-protein">Protein — <?= $split['proteinPct'] ?>% (<?= number_format($split['proteinKcal']) ?> kcal)</span>
            <span class="legend-carbs">Carbs — <?= $split['carbsPct'] ?>% (<?= number_format($split['carbsKcal']) ?> kcal)</span>
            <span class="legend-fats">Fats — <?= $split['fatsPct'] ?>% (<?= number_format($split['fatsKcal']) ?> kcal)</span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ===== PER-MEAL BREAKDOWN ===== -->
<?php if (!empty($data['meals'])): ?>
<div class="card">
    <div class="card-header">
        <h2>🍽️ Per-Meal Breakdown</h2>
        <span><?= date('M j, Y', strtotime($data['selectedDate'])) ?></span>
    </div>
    <div class="card-body">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Food</th>
                        <th>Calories</th>
                        <th>Protein</th>
                        <th>Carbs</th>
                        <th>Fats</th>
                        <th>% of Day</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['meals'] as $i => $meal): ?>
                        <?php
                        $mealPct = $totals['total_calories'] > 0
                            ? round($meal['calories'] / $totals['total_calories'] * 100)
                            : 0;
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($meal['food_name']) ?></td>
                            <td><?= $meal['calories'] ?> kcal</td>
                            <td><?= number_format($meal['protein'], 1) ?>g</td>
                            <td><?= number_format($meal['carbs'],   1) ?>g</td>
                            <td><?= number_format($meal['fats'],    1) ?>g</td>
                            <td>
                                <div class="mini-bar-wrap">
                                    <div class="mini-bar" style="width:<?= $mealPct ?>%"></div>
                                    <span><?= $mealPct ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <p class="empty-state">
            No meals logged for <?= date('M j, Y', strtotime($data['selectedDate'])) ?>.
            <a href="meals.php">Log a meal</a>.
        </p>
    </div>
</div>
<?php endif; ?>

<!-- ===== 7-DAY CALORIE TREND ===== -->
<?php if (!empty($trend)): ?>
<div class="card">
    <div class="card-header"><h2>📅 7-Day Calorie Trend</h2></div>
    <div class="card-body">
        <div class="trend-chart">
            <?php
            $maxCal = max(array_merge(array_column($trend, 'daily_cal'), [$goals['calories']]));
            foreach ($trend as $day):
                $barH   = $maxCal > 0 ? round($day['daily_cal'] / $maxCal * 100) : 0;
                $isToday = ($day['meal_date'] === date('Y-m-d'));
            ?>
                <div class="trend-bar-col">
                    <div class="trend-bar-wrap"
                         title="<?= $day['meal_date'] ?>: <?= number_format($day['daily_cal']) ?> kcal">
                        <div class="trend-bar <?= $isToday ? 'trend-bar--today' : '' ?>"
                             style="height:<?= $barH ?>%">
                        </div>
                    </div>
                    <span class="trend-label"><?= date('D', strtotime($day['meal_date'])) ?></span>
                    <span class="trend-cal"><?= round($day['daily_cal'] / 1000, 1) ?>k</span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="trend-goal-line">Daily goal: <?= number_format($goals['calories']) ?> kcal</p>
    </div>
</div>
<?php endif; ?>
