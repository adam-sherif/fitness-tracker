<?php
// ============================================================
//  views/progress.view.php — Progress Tracker HTML Template
// ============================================================
//  Receives $data from ProgressController::handle().
//
//  Available $data keys:
//    entries, weightEntries, stats, editEntry,
//    flash, flashError, errors
// ============================================================

$stats     = $data['stats'];
$editEntry = $data['editEntry'];  // null (add mode) or array (edit mode)
?>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">📈 Progress Tracker</h1>
        <p class="page-subtitle">Track your weight and body fat over time. Every entry counts.</p>
    </div>
</div>

<!-- Alerts -->
<?php if ($data['flash']): ?>
    <div class="alert alert-success"><?= htmlspecialchars($data['flash']) ?></div>
<?php endif; ?>
<?php if ($data['flashError']): ?>
    <div class="alert alert-danger">⚠️ <?= htmlspecialchars($data['flashError']) ?></div>
<?php endif; ?>
<?php if (!empty($data['errors'])): ?>
    <div class="alert alert-danger">
        <?php foreach ($data['errors'] as $err): ?>
            <p>⚠️ <?= htmlspecialchars($err) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ===== STATS SUMMARY (only if weight data exists) ===== -->
<?php if (!empty($data['weightEntries'])): ?>
<div class="stats-grid stats-grid--3">

    <div class="stat-card">
        <div class="stat-icon">⚖️</div>
        <div class="stat-info">
            <span class="stat-value"><?= $stats['latest'] ?> kg</span>
            <span class="stat-label">Current Weight</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon"><?= $stats['change'] !== null && $stats['change'] <= 0 ? '📉' : '📈' ?></div>
        <div class="stat-info">
            <span class="stat-value <?= $stats['change'] < 0 ? 'text-success' : ($stats['change'] > 0 ? 'text-danger' : '') ?>">
                <?= $stats['change'] !== null
                    ? ($stats['change'] > 0 ? '+' : '') . $stats['change'] . ' kg'
                    : '—' ?>
            </span>
            <span class="stat-label">Total Change</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
            <span class="stat-value"><?= count($data['entries']) ?></span>
            <span class="stat-label">Total Entries</span>
        </div>
    </div>

</div>
<?php endif; ?>

<!-- ===== SPLIT LAYOUT ===== -->
<div class="page-split">

    <!-- LEFT: Add / Edit Form -->
    <div class="card form-card">
        <div class="card-header">
            <h2><?= $editEntry ? '✏️ Edit Entry' : '➕ Log Progress' ?></h2>
        </div>
        <div class="card-body">

            <form method="POST" action="progress.php">

                <!-- Hidden: tells controller whether this is add or update -->
                <input type="hidden" name="action" value="<?= $editEntry ? 'update' : 'add' ?>">
                <?php if ($editEntry): ?>
                    <input type="hidden" name="entry_id" value="<?= $editEntry['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="log_date">Date *</label>
                    <input
                        type="date"
                        id="log_date"
                        name="log_date"
                        value="<?= $editEntry ? $editEntry['log_date'] : date('Y-m-d') ?>"
                        max="<?= date('Y-m-d') ?>"
                        required
                    >
                </div>

                <div class="form-row two-col">
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input
                            type="number"
                            id="weight"
                            name="weight"
                            placeholder="e.g. 75.5"
                            step="0.1" min="20" max="500"
                            value="<?= $editEntry ? ($editEntry['weight'] ?? '') : '' ?>"
                        >
                        <small>Leave blank to skip</small>
                    </div>
                    <div class="form-group">
                        <label for="body_fat">Body Fat (%)</label>
                        <input
                            type="number"
                            id="body_fat"
                            name="body_fat"
                            placeholder="e.g. 18.5"
                            step="0.1" min="1" max="70"
                            value="<?= $editEntry ? ($editEntry['body_fat'] ?? '') : '' ?>"
                        >
                        <small>Leave blank to skip</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes (optional)</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        placeholder="How are you feeling? Any changes in routine?"
                    ><?= $editEntry ? htmlspecialchars($editEntry['notes'] ?? '') : '' ?></textarea>
                </div>

                <div class="form-row two-col">
                    <button type="submit" class="btn-primary">
                        <?= $editEntry ? '💾 Save Changes' : '📈 Log Progress' ?>
                    </button>
                    <?php if ($editEntry): ?>
                        <a href="progress.php" class="btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </div>

    <!-- RIGHT: Progress History Table -->
    <div class="card">
        <div class="card-header">
            <h2>📋 Progress History</h2>
            <span><?= count($data['entries']) ?> entries</span>
        </div>
        <div class="card-body">

            <?php if (empty($data['entries'])): ?>
                <p class="empty-state">
                    No progress logged yet. Use the form to record your first entry!
                </p>

            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Weight</th>
                                <th>Body Fat</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['entries'] as $entry): ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($entry['log_date'])) ?></td>
                                <td><?= $entry['weight']   ? $entry['weight']   . ' kg' : '—' ?></td>
                                <td><?= $entry['body_fat'] ? $entry['body_fat'] . '%'   : '—' ?></td>
                                <td class="notes-cell">
                                    <?php if ($entry['notes']): ?>
                                        <?= htmlspecialchars(mb_substr($entry['notes'], 0, 40)) ?>
                                        <?= mb_strlen($entry['notes']) > 40 ? '…' : '' ?>
                                    <?php else: ?>
                                        <span class="no-notes">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-cell">
                                    <!-- Edit: load entry into form -->
                                    <a href="progress.php?action=edit&id=<?= $entry['id'] ?>"
                                       class="btn-edit" title="Edit this entry">✏️</a>
                                    <!-- Delete: confirm first -->
                                    <a href="progress.php?action=delete&id=<?= $entry['id'] ?>"
                                       class="btn-delete"
                                       title="Delete this entry"
                                       onclick="return confirm('Delete this progress entry?')">🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /.page-split -->

<!-- ===== WEIGHT TREND CHART (CSS bar chart, shown if 2+ entries) ===== -->
<?php if (count($data['weightEntries']) >= 2):
    // Show last 10 weight entries, oldest first
    $chartData = array_reverse(array_slice(array_values($data['weightEntries']), 0, 10));
    $weights   = array_column($chartData, 'weight');
    $chartMin  = min($weights) - 2;
    $chartMax  = max($weights) + 2;
    $chartRange = $chartMax - $chartMin;
?>
<div class="card">
    <div class="card-header">
        <h2>⚖️ Weight Trend (Last <?= count($chartData) ?> Entries)</h2>
    </div>
    <div class="card-body">
        <div class="weight-chart">
            <?php foreach ($chartData as $entry):
                if ($entry['weight'] === null) continue;
                $barH = $chartRange > 0
                    ? round(($entry['weight'] - $chartMin) / $chartRange * 100)
                    : 50;
            ?>
                <div class="wc-bar-col"
                     title="<?= date('M j', strtotime($entry['log_date'])) ?>: <?= $entry['weight'] ?> kg">
                    <span class="wc-value"><?= $entry['weight'] ?></span>
                    <div class="wc-bar-wrap">
                        <div class="wc-bar" style="height: <?= $barH ?>%"></div>
                    </div>
                    <span class="wc-label"><?= date('M j', strtotime($entry['log_date'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
