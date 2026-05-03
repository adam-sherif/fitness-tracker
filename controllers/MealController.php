<?php
// ============================================================
//  controllers/MealController.php — Meal Logger Logic
// ============================================================
//  Handles Add, List, and Delete operations on the `meals` table.
//
//  Request Routing (inside handle()):
//    GET  ?delete=ID → deleteMeal(ID) → redirect
//    POST            → addMeal()      → redirect or return errors
//    GET             → listMeals()    → return data for view
// ============================================================

require_once __DIR__ . '/../config.php';

class MealController
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
     * Main router — detects the request type and delegates.
     *
     * @return array  Data for views/meals.view.php
     */
    public function handle(): array
    {
        // ── DELETE (GET with ?delete=ID) ──────────────────────
        if (isset($_GET['delete'])) {
            $this->deleteMeal((int)$_GET['delete']);
            // deleteMeal() always redirects, so code below never runs
        }

        // ── ADD (POST) ────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->addMeal();
            // If addMeal() returns data, there were validation errors
            if (isset($result['errors'])) {
                return array_merge($result, $this->listMeals($result['filterDate'] ?? date('Y-m-d')));
            }
        }

        // ── LIST (GET) ────────────────────────────────────────
        $filterDate = $this->sanitizeDate($_GET['date'] ?? date('Y-m-d'));
        return $this->listMeals($filterDate);
    }

    // ── Private: ADD ─────────────────────────────────────────

    /**
     * Validates POST data and inserts a new meal row.
     * On success: redirects. On failure: returns errors for view.
     */
    private function addMeal(): array
    {
        $errors     = [];
        $foodName   = trim($_POST['food_name']  ?? '');
        $calories   = (int)($_POST['calories']  ?? 0);
        $protein    = (float)($_POST['protein'] ?? 0);
        $carbs      = (float)($_POST['carbs']   ?? 0);
        $fats       = (float)($_POST['fats']    ?? 0);
        $mealDate   = $this->sanitizeDate($_POST['meal_date'] ?? date('Y-m-d'));

        // Validation
        if (empty($foodName))  $errors[] = 'Food name is required.';
        if ($calories < 0)     $errors[] = 'Calories cannot be negative.';
        if ($protein  < 0)     $errors[] = 'Protein cannot be negative.';
        if ($carbs    < 0)     $errors[] = 'Carbs cannot be negative.';
        if ($fats     < 0)     $errors[] = 'Fats cannot be negative.';

        if (!empty($errors)) {
            return [
                'errors'     => $errors,
                'filterDate' => $mealDate,
                'post'       => compact('foodName', 'calories', 'protein', 'carbs', 'fats', 'mealDate'),
            ];
        }

        // Insert into database using a prepared statement
        $stmt = $this->pdo->prepare(
            'INSERT INTO meals (user_id, food_name, calories, protein, carbs, fats, meal_date)
             VALUES (:uid, :food, :cal, :pro, :carb, :fat, :date)'
        );
        $stmt->execute([
            ':uid'  => $this->userId,
            ':food' => $foodName,
            ':cal'  => $calories,
            ':pro'  => $protein,
            ':carb' => $carbs,
            ':fat'  => $fats,
            ':date' => $mealDate,
        ]);

        $_SESSION['flash_success'] = "✅ \"{$foodName}\" logged successfully!";
        header('Location: meals.php?date=' . $mealDate);
        exit();
    }

    // ── Private: DELETE ───────────────────────────────────────

    /**
     * Deletes a meal entry — but only if it belongs to this user.
     * The `AND user_id = :uid` clause is the ownership check.
     */
    private function deleteMeal(int $id): void
    {
        $filterDate = $this->sanitizeDate($_GET['date'] ?? date('Y-m-d'));

        $stmt = $this->pdo->prepare(
            'DELETE FROM meals WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([':id' => $id, ':uid' => $this->userId]);

        $_SESSION['flash_success'] = '🗑️ Meal entry deleted.';
        header('Location: meals.php?date=' . $filterDate);
        exit();
    }

    // ── Private: LIST ─────────────────────────────────────────

    /**
     * Fetches all meals and totals for the given date.
     *
     * @param string $date  Date in 'Y-m-d' format
     * @return array
     */
    private function listMeals(string $date): array
    {
        // Fetch individual meals for this date
        $stmt = $this->pdo->prepare(
            'SELECT * FROM meals
             WHERE  user_id = :uid AND meal_date = :date
             ORDER  BY created_at ASC'
        );
        $stmt->execute([':uid' => $this->userId, ':date' => $date]);
        $meals = $stmt->fetchAll();

        // Fetch daily totals — COALESCE prevents NULL when no meals logged
        $stmt = $this->pdo->prepare(
            'SELECT
                COALESCE(SUM(calories), 0) AS total_calories,
                COALESCE(SUM(protein),  0) AS total_protein,
                COALESCE(SUM(carbs),    0) AS total_carbs,
                COALESCE(SUM(fats),     0) AS total_fats
             FROM meals
             WHERE user_id = :uid AND meal_date = :date'
        );
        $stmt->execute([':uid' => $this->userId, ':date' => $date]);
        $totals = $stmt->fetch();

        // Grab and clear flash message
        $flash = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_success']);

        return [
            'pageTitle'  => 'Meal Logger',
            'meals'      => $meals,
            'totals'     => $totals,
            'filterDate' => $date,
            'flash'      => $flash,
            'errors'     => [],
            'post'       => [],
        ];
    }

    // ── Private: Helpers ──────────────────────────────────────

    /**
     * Validates a date string. Returns today's date if invalid.
     */
    private function sanitizeDate(string $date): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d');
    }
}
