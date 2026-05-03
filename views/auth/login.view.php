<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FitTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">

<div class="auth-container auth-container--narrow">

    <!-- Brand -->
    <div class="auth-brand">
        <span class="auth-logo">💪</span>
        <h1>FitTrack</h1>
        <p>Log in to your account</p>
    </div>

    <!-- Flash success (set by signup redirect) -->
    <?php if ($data['flash']): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($data['flash']) ?></div>
    <?php endif; ?>

    <!-- Error message -->
    <?php if ($data['error']): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($data['error']) ?></div>
    <?php endif; ?>

    <!-- Login Form -->
    <form class="auth-form" method="POST" action="login.php">

        <div class="form-group">
            <label for="username">Username or Email</label>
            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter your username or email"
                value="<?= htmlspecialchars($data['oldUser']) ?>"
                required
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >
        </div>

        <button type="submit" class="btn-primary btn-full">Login →</button>

    </form>

    <p class="auth-switch">
        Don't have an account? <a href="signup.php">Sign up for free</a>
    </p>

</div>

</body>
</html>
