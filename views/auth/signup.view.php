<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | FitTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">

<div class="auth-container">

    <!-- Brand -->
    <div class="auth-brand">
        <span class="auth-logo">💪</span>
        <h1>FitTrack</h1>
        <p>Start your fitness journey today</p>
    </div>

    <!-- Validation Errors -->
    <?php if (!empty($data['errors'])): ?>
        <div class="alert alert-danger">
            <?php foreach ($data['errors'] as $err): ?>
                <p>⚠️ <?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Sign-Up Form -->
    <form class="auth-form" method="POST" action="signup.php">

        <div class="form-row two-col">
            <div class="form-group">
                <label for="username">Username *</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="e.g. adam_fit"
                    value="<?= htmlspecialchars($data['old']['username'] ?? '') ?>"
                    required
                    minlength="3"
                >
            </div>
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    value="<?= htmlspecialchars($data['old']['email'] ?? '') ?>"
                    required
                >
            </div>
        </div>

        <div class="form-row two-col">
            <div class="form-group">
                <label for="password">Password *</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Min. 6 characters"
                    required
                    minlength="6"
                >
            </div>
            <div class="form-group">
                <label for="confirm">Confirm Password *</label>
                <input
                    type="password"
                    id="confirm"
                    name="confirm"
                    placeholder="Repeat password"
                    required
                >
            </div>
        </div>

        <div class="form-row three-col">
            <div class="form-group">
                <label for="age">Age (years)</label>
                <input
                    type="number"
                    id="age"
                    name="age"
                    placeholder="25"
                    min="10" max="100"
                    value="<?= htmlspecialchars($data['old']['age'] ?? '') ?>"
                >
            </div>
            <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input
                    type="number"
                    id="weight"
                    name="weight"
                    placeholder="70"
                    step="0.1" min="20"
                    value="<?= htmlspecialchars($data['old']['weight'] ?? '') ?>"
                >
            </div>
            <div class="form-group">
                <label for="height">Height (cm)</label>
                <input
                    type="number"
                    id="height"
                    name="height"
                    placeholder="175"
                    min="100" max="250"
                    value="<?= htmlspecialchars($data['old']['height'] ?? '') ?>"
                >
            </div>
        </div>

        <div class="form-group">
            <label for="goal">Fitness Goal</label>
            <select id="goal" name="goal">
                <option value="maintain"    <?= ($data['old']['goal'] ?? '') === 'maintain'    ? 'selected' : '' ?>>⚖️ Maintain Weight</option>
                <option value="lose_weight" <?= ($data['old']['goal'] ?? '') === 'lose_weight' ? 'selected' : '' ?>>🔥 Lose Weight</option>
                <option value="gain_muscle" <?= ($data['old']['goal'] ?? '') === 'gain_muscle' ? 'selected' : '' ?>>💪 Gain Muscle</option>
            </select>
        </div>

        <button type="submit" class="btn-primary btn-full">Create Account 🚀</button>

    </form>

    <p class="auth-switch">
        Already have an account? <a href="login.php">Log in here</a>
    </p>

</div>

</body>
</html>
