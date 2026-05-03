<?php
// ============================================================
//  config.php — Database Configuration & PDO Connection
// ============================================================
//  This file has ONE job: provide a working PDO connection.
//  Every other file gets the database through getDB().
//
//  ⚠️ Change the credentials below to match your local setup.
// ============================================================

// ── Database Credentials ─────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'fitness_tracker');
define('DB_USER',    'root');
define('DB_PASS',    '');            // Empty string for default XAMPP
define('DB_CHARSET', 'utf8mb4');     // Supports all Unicode characters

// ── Site Settings ─────────────────────────────────────────────
define('SITE_NAME', 'FitTrack');

// ── Start Session (once, globally) ───────────────────────────
// Calling session_start() here means every file that includes
// config.php automatically has access to $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── PDO Singleton Connection ──────────────────────────────────
/**
 * getDB() — Returns a shared PDO database connection.
 *
 * Uses a static variable so the connection is created ONCE
 * per HTTP request, no matter how many times getDB() is called.
 *
 * PDO was chosen because:
 *   • Supports prepared statements (prevents SQL injection)
 *   • Throws catchable exceptions on errors
 *   • Works with multiple DB drivers (MySQL, SQLite, PostgreSQL)
 *
 * @return PDO
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(
                '<div style="font-family:sans-serif;background:#0a0a14;color:#ff5252;
                padding:2rem;margin:2rem;border:1px solid #ff5252;border-radius:12px;">
                <h2>⚠️ Database Connection Error</h2>
                <p><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>
                <p>Check your credentials in <code>config.php</code> and make sure MySQL is running.</p>
                </div>'
            );
        }
    }

    return $pdo;
}
