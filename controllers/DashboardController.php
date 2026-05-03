<?php
// ============================================================
//  controllers/DashboardController.php — Dashboard Data
// ============================================================
//  Aggregates all data needed for the dashboard overview:
//    - Today's macro totals
//    - Recent meals
//    - Latest progress entry
//    - Calorie goal progress
// ============================================================

require_once __DIR__ . '/../config.php';

class DashboardController
{
    private PDO $pdo;
    private int $userId;

    public function __construct()
    {
        // Redirect to login if not authenticated
        require_once __DIR__ . '/../includes/auth_check.php';
        $this->pdo    = getDB();
        $this->userId = (int)$_SESSION['user_id'];
    }

    /**
     * Gathers all data needed by views/dashboard.view.php.
     *
     * @return array
     */
    public function handle(): array
    {
        $today = date('Y-m-d');

        return [
            'pageTitle'     => 'Dashboard',
            'greeting'      => $this->getGreeting(),
            'username'      => $_SESSION['username'],
            'today'         => $today,
            'todayMacros'   => $this->getTodayMacros($today),
            'recentMeals'   => $this->getRecentMeals(),
            'latestProgress'=> $this->getLatestProgress(),
            'calorieGoal'   => $this->getCalorieGoal(),
            'goalLabel'     => $this->getGoalLabel(),
        ];
    }

    // ── Private Helpers ───────────────────────────────────────

    /**
     * Returns greeting text based on the current hour.
     */
    private function getGreeting(): string
    {
        $hour = (int)date('H');
        if ($hour < 12) return 'Good Morning';
        if ($hour < 17) return 'Good Afternoon';
        return 'Good Evening';
    }

    /**
     * SUM all macros from meals logged today.
     * COALESCE returns 0 instead of NULL when no meals exist.
     */
    private function getTodayMacros(string $today): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COALESCE(SUM(calories), 0) AS total_calories,
                COALESCE(SUM(protein),  0) AS total_protein,
                COALESCE(SUM(carbs),    0) AS total_carbs,
                COALESCE(SUM(fats),     0) AS total_fats,
                COUNT(*)                   AS meal_count
             FROM meals
             WHERE user_id = :uid AND meal_date = :today'
        );
        $stmt->execute([':uid' => $this->userId, ':today' => $today]);
        return $stmt->fetch();
    }

    /**
     * Fetches the 3 most recently logged meals (any date).
     */
    private function getRecentMeals(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT food_name, calories, meal_date
             FROM   meals
             WHERE  user_id = :uid
             ORDER  BY created_at DESC
             LIMIT  3'
        );
        $stmt->execute([':uid' => $this->userId]);
        return $stmt->fetchAll();
    }

    /**
     * Fetches the most recent progress entry.
     */
    private function getLatestProgress(): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT weight, body_fat, log_date
             FROM   progress
             WHERE  user_id = :uid
             ORDER  BY log_date DESC
             LIMIT  1'
        );
        $stmt->execute([':uid' => $this->userId]);
        return $stmt->fetch();
    }

    /**
     * Returns a daily calorie goal based on the user's fitness goal.
     * These are rough general estimates suitable for display purposes.
     */
    private function getCalorieGoal(): int
    {
        return match($_SESSION['goal'] ?? 'maintain') {
            'lose_weight' => 1800,
            'gain_muscle' => 2800,
            default       => 2200,
        };
    }

    /**
     * Returns a human-readable label for the user's fitness goal.
     */
    private function getGoalLabel(): string
    {
        return match($_SESSION['goal'] ?? 'maintain') {
            'lose_weight' => '🔥 Lose Weight',
            'gain_muscle' => '💪 Gain Muscle',
            default       => '⚖️ Maintain',
        };
    }
}
