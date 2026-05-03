<?php
// ============================================================
//  controllers/AuthController.php — Authentication Logic
// ============================================================
//  Handles all login and signup business logic.
//  No HTML is produced here — returns $data arrays for views.
//
//  Public methods:
//    handleLogin()  → used by login.php
//    handleSignup() → used by signup.php
// ============================================================

require_once __DIR__ . '/../config.php';

class AuthController
{
    private PDO $pdo;

    public function __construct()
    {
        // If the user is already logged in, no need to be on auth pages
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit();
        }

        $this->pdo = getDB();
    }

    // ── LOGIN ─────────────────────────────────────────────────

    /**
     * Processes GET and POST for the login page.
     *
     * POST flow:
     *  1. Sanitize inputs
     *  2. Fetch user from DB by username or email
     *  3. Verify password with password_verify()
     *  4. On success: populate session, regenerate ID, redirect
     *  5. On failure: return error data to login view
     *
     * @return array  Data for login.view.php
     */
    public function handleLogin(): array
    {
        $data = [
            'error'   => '',
            'flash'   => '',
            'oldUser' => '',
        ];

        // Grab flash message set by signup redirect
        $data['flash'] = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_success']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $data; // Just render the form on GET
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $data['oldUser'] = htmlspecialchars($username);

        // Basic presence check
        if (empty($username) || empty($password)) {
            $data['error'] = 'Please fill in both fields.';
            return $data;
        }

        // Fetch user — allow login via username OR email
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, password, goal
             FROM   users
             WHERE  username = :u OR email = :u
             LIMIT  1'
        );
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch(); // Returns array or false

        // password_verify() compares plain-text against bcrypt hash
        if ($user && password_verify($password, $user['password'])) {

            // Populate session
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['goal']     = $user['goal'];

            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);

            header('Location: ' . $redirect);
            exit();
        }

        $data['error'] = 'Invalid username or password. Please try again.';
        return $data;
    }

    // ── SIGNUP ────────────────────────────────────────────────

    /**
     * Processes GET and POST for the signup page.
     *
     * POST flow:
     *  1. Sanitize and validate all fields
     *  2. Check for duplicate username/email in DB
     *  3. Hash password with PASSWORD_BCRYPT
     *  4. INSERT new user row
     *  5. Redirect to login with a flash message
     *
     * @return array  Data for signup.view.php
     */
    public function handleSignup(): array
    {
        $data = [
            'errors' => [],
            'old'    => [],
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $data;
        }

        // ── Step 1: Sanitize Inputs ───────────────────────────
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm']  ?? '');
        $age      = (int)($_POST['age']     ?? 0);
        $weight   = (float)($_POST['weight'] ?? 0);
        $height   = (float)($_POST['height'] ?? 0);
        $goal     = $_POST['goal'] ?? 'maintain';

        // Preserve old values to re-populate the form on error
        $data['old'] = compact('username', 'email', 'age', 'weight', 'height', 'goal');

        // ── Step 2: Validate ──────────────────────────────────
        if (strlen($username) < 3) {
            $data['errors'][] = 'Username must be at least 3 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data['errors'][] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 6) {
            $data['errors'][] = 'Password must be at least 6 characters.';
        }
        if ($password !== $confirm) {
            $data['errors'][] = 'Passwords do not match.';
        }
        if (!in_array($goal, ['lose_weight', 'gain_muscle', 'maintain'])) {
            $goal = 'maintain';
        }

        // ── Step 3: Check for duplicates ──────────────────────
        if (empty($data['errors'])) {
            $stmt = $this->pdo->prepare(
                'SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1'
            );
            $stmt->execute([':u' => $username, ':e' => $email]);

            if ($stmt->fetch()) {
                $data['errors'][] = 'That username or email is already registered.';
            }
        }

        // ── Step 4: Hash & Insert ─────────────────────────────
        if (empty($data['errors'])) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->pdo->prepare(
                'INSERT INTO users (username, email, password, age, weight, height, goal)
                 VALUES (:username, :email, :password, :age, :weight, :height, :goal)'
            );
            $stmt->execute([
                ':username' => $username,
                ':email'    => $email,
                ':password' => $hashed,
                ':age'      => $age    ?: null,
                ':weight'   => $weight ?: null,
                ':height'   => $height ?: null,
                ':goal'     => $goal,
            ]);

            $_SESSION['flash_success'] = 'Account created! Please log in.';
            header('Location: login.php');
            exit();
        }

        return $data;
    }
}
