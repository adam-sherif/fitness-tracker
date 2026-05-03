<?php
// ============================================================
//  views/meals.view.php — Meal Logger HTML Template
// ============================================================
//  Receives $data from MealController::handle().
//
//  Available $data keys:
//    meals, totals, filterDate, flash, errors, post
// ============================================================
?>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">🍽️ Meal Logger</h1>
        <p class="page-subtitle">Track everything you eat to meet your nutrition goals.</p>
    </div>
</div>

<!-- Alerts -->
<?php if ($data['flash']): ?>
    <div class="alert alert-success"><?= htmlspecialchars($data['flash']) ?></div>
<?php endif; ?>

<?php if (!empty($data['errors'])): ?>
    <div class="alert alert-danger">
        <?php foreach ($data['errors'] as $err): ?>
            <p>⚠️ <?= htmlspecialchars($err) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ===== SPLIT LAYOUT ===== -->
<div class="page-split">

    <!-- LEFT: Add Meal Form -->
    <div class="card form-card">
        <div class="card-header">
            <h2>➕ Log a New Meal</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="meals.php">

                <div class="form-group">
                    <label for="food_name">Food / Meal Name *</label>
                    <input
                        type="text"
                        id="food_name"
                        name="food_name"
                        placeholder="e.g. Grilled Chicken Breast"
                        value="<?= htmlspecialchars($data['post']['foodName'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="calories">Calories (kcal) *</label>
                    <input
                        type="number"
                        id="calories"
                        name="calories"
                        placeholder="e.g. 350"
                        min="0"
                        value="<?= htmlspecialchars($data['post']['calories'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-row three-col">
                    <div class="form-group">
                        <label for="protein">Protein (g)</label>
                        <input
                            type="number"
                            id="protein"
                            name="protein"
                            placeholder="35"
                            step="0.1" min="0"
                            value="<?= htmlspecialchars($data['post']['protein'] ?? '0') ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="carbs">Carbs (g)</label>
                        <input
                            type="number"
                            id="carbs"
                            name="carbs"
                            placeholder="20"
                            step="0.1" min="0"
                            value="<?= htmlspecialchars($data['post']['carbs'] ?? '0') ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="fats">Fats (g)</label>
                        <input
                            type="number"
                            id="fats"
                            name="fats"
                            placeholder="10"
                            step="0.1" min="0"
                            value="<?= htmlspecialchars($data['post']['fats'] ?? '0') ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="meal_date">Date</label>
                    <input
                        type="date"
                        id="meal_date"
                        name="meal_date"
                        value="<?= htmlspecialchars($data['post']['mealDate'] ?? $data['filterDate']) ?>"
                    >
                </div>

                <button type="submit" class="btn-primary btn-full">Log Meal 🍽️</button>
            </form>
        </div>
    </div>

    <!-- RIGHT: Meal List -->
    <div class="card">
        <div class="card-header">
            <h2>
                📋 Meals for
                <em><?= date('M j, Y', strtotime($data['filterDate'])) ?></em>
            </h2>
            <form method="GET" action="meals.php" class="inline-filter">
                <input type="date" name="date" value="<?= $data['filterDate'] ?>">
                <button type="submit" class="btn-sm">Filter</button>
            </form>
        </div>
        <div class="card-body">

            <?php if (empty($data['meals'])): ?>
                <p class="empty-state">
                    No meals logged for this date. Use the form to add one!
                </p>

            <?php else: ?>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Food</th>
                                <th>Cal</th>
                                <th>Pro</th>
                                <th>Carb</th>
                                <th>Fat</th>
                                <th>Del</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['meals'] as $meal): ?>
                                <tr>
                                    <td class="food-name-cell">
                                        <?= htmlspecialchars($meal['food_name']) ?>
                                    </td>
                                    <td><?= $meal['calories'] ?></td>
                                    <td><?= number_format($meal['protein'], 1) ?>g</td>
                                    <td><?= number_format($meal['carbs'],   1) ?>g</td>
                                    <td><?= number_format($meal['fats'],    1) ?>g</td>
                                    <td>
                                        <a href="meals.php?delete=<?= $meal['id'] ?>&date=<?= $data['filterDate'] ?>"
                                           class="btn-delete"
                                           onclick="return confirm('Delete this meal entry?')">
                                            🗑️
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="totals-row">
                                <td><strong>Daily Total</strong></td>
                                <td><strong><?= number_format($data['totals']['total_calories']) ?></strong></td>
                                <td><strong><?= number_format($data['totals']['total_protein'], 1) ?>g</strong></td>
                                <td><strong><?= number_format($data['totals']['total_carbs'],   1) ?>g</strong></td>
                                <td><strong><?= number_format($data['totals']['total_fats'],    1) ?>g</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Macro Summary Badges -->
                <div class="macro-badges">
                    <span class="macro-badge macro-badge--calories">
                        🔥 <?= number_format($data['totals']['total_calories']) ?> kcal
                    </span>
                    <span class="macro-badge macro-badge--protein">
                        🥩 <?= number_format($data['totals']['total_protein'], 1) ?>g protein
                    </span>
                    <span class="macro-badge macro-badge--carbs">
                        🌾 <?= number_format($data['totals']['total_carbs'], 1) ?>g carbs
                    </span>
                    <span class="macro-badge macro-badge--fats">
                        🥑 <?= number_format($data['totals']['total_fats'], 1) ?>g fats
                    </span>
                </div>

            <?php endif; ?>
        </div>
    </div>

</div><!-- /.page-split -->
