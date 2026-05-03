<?php
// ============================================================
//  controllers/MacroController.php — Macros Calculation Logic
// ============================================================
//  Queries the meals table and calculates:
//    - Total macros for the selected date
//    - % of daily goals consumed
//    - Per-meal breakdown
//    - 7-day calorie trend
//    - Calorie split by macronutrient
//
//  Calorie conversion used:
//    Protein = 4 kcal/g
//    Carbs   = 4 kcal/g
//    Fats    = 9 kcal/g
// ============================================================

require_once __DIR__ . '/../config.php';

class MacroController
{
    private PDO $pdo;
    private int $userId;

    public function __construct()
    {
        require_once __DIR__ . '/../includes/auth_check.php';
        $this->pdo    = getDB();
        $this->userId = (int)$_SESSION['user_id'];
    }

    /**
     * Builds all data needed by views/macros.view.php.
     *
     * @return array
     */
    public function handle(): array
    {
        $selectedDate = $this->sanitizeDate($_GET['date'] ?? date('Y-m-d'));
        $goals        = $this->getDailyGoals();
        $totals       = $this->getDailyTotals($selectedDate);
        $meals        = $this->getMealsForDate($selectedDate);
        $trend        = $this->getSevenDayTrend();
        $calorieSplit = $this->calculateCalorieSplit($totals);

        return [
            'pageTitle'    => 'Macros Calculator',
            'selectedDate' => $selectedDate,
            'goals'        => $goals,
            'totals'       => $totals,
            'meals'        => $meals,
            'trend'        => $trend,
            'calorieSplit' => $calorieSplit,
        ];
    }

    // ── Private Helpers ───────────────────────────────────────

    /**
     * Returns recommended daily macro goals based on the user's
     * fitness goal stored in session (set at signup).
     */
    private function getDailyGoals(): array
    {
        return match($_SESSION['goal'] ?? 'maintain') {
            'lose_weight' => ['calories' => 1800, 'protein' => 150, 'carbs' => 150, 'fats' => 60],
            'gain_muscle' => ['calories' => 2800, 'protein' => 200, 'carbs' => 300, 'fats' => 80],
            default       => ['calories' => 2200, 'protein' => 165, 'carbs' => 220, 'fats' => 73],
        };
    }

    /**
     * Fetches the SUM of all macros for the selected date.
     * COALESCE(..., 0) ensures 0 instead of NULL for empty days.
     */
    private function getDailyTotals(string $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COALESCE(SUM(calories), 0) AS total_calories,
                COALESCE(SUM(protein),  0) AS total_protein,
                COALESCE(SUM(carbs),    0) AS total_carbs,
                COALESCE(SUM(fats),     0) AS total_fats,
                COUNT(*)                   AS meal_count
             FROM meals
             WHERE user_id = :uid AND meal_date = :date'
        );
        $stmt->execute([':uid' => $this->userId, ':date' => $date]);
        return $stmt->fetch();
    }

    /**
     * Fetches individual meal rows for the selected date.
     */
    private function getMealsForDate(string $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT food_name, calories, protein, carbs, fats
             FROM   meals
             WHERE  user_id = :uid AND meal_date = :date
             ORDER  BY created_at ASC'
        );
        $stmt->execute([':uid' => $this->userId, ':date' => $date]);
        return $stmt->fetchAll();
    }

    /**
     * Gets total calories per day for the last 7 days.
     * Used to draw the 7-day trend bar chart in the view.
     *
     * DATE_SUB(today, INTERVAL 6 DAY) = 7 days including today
     */
    private function getSevenDayTrend(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT meal_date, SUM(calories) AS daily_cal
             FROM   meals
             WHERE  user_id = :uid
               AND  meal_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP  BY meal_date
             ORDER  BY meal_date ASC'
        );
        $stmt->execute([':uid' => $this->userId]);
        return $stmt->fetchAll();
    }

    /**
     * Calculates what percentage of total calories came from each macro.
     *
     * Calorie density:
     *   Protein = 4 kcal per gram
     *   Carbs   = 4 kcal per gram
     *   Fats    = 9 kcal per gram
     *
     * @param array $totals  Row from getDailyTotals()
     * @return array  ['proteinKcal', 'carbsKcal', 'fatsKcal',
     *                 'proteinPct', 'carbsPct', 'fatsPct']
     */
    private function calculateCalorieSplit(array $totals): array
    {
        $totalCal   = (float)$totals['total_calories'];
        $proteinKcal = $totals['total_protein'] * 4;
        $carbsKcal   = $totals['total_carbs']   * 4;
        $fatsKcal    = $totals['total_fats']    * 9;

        return [
            'proteinKcal' => $proteinKcal,
            'carbsKcal'   => $carbsKcal,
            'fatsKcal'    => $fatsKcal,
            'proteinPct'  => $totalCal > 0 ? round($proteinKcal / $totalCal * 100) : 0,
            'carbsPct'    => $totalCal > 0 ? round($carbsKcal   / $totalCal * 100) : 0,
            'fatsPct'     => $totalCal > 0 ? round($fatsKcal    / $totalCal * 100) : 0,
        ];
    }

    /**
     * Validates a date string. Falls back to today if invalid.
     */
    private function sanitizeDate(string $date): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d');
    }
}
