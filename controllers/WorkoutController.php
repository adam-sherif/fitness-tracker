<?php
// ============================================================
//  controllers/WorkoutController.php — Workout Plans Logic
// ============================================================
//  Fetches pre-defined workout plans from the `workouts` table.
//  Supports filtering by category and difficulty via GET params.
//
//  Security note: filters are whitelisted against known ENUM
//  values — no raw user input is ever inserted into SQL.
// ============================================================

require_once __DIR__ . '/../config.php';

class WorkoutController
{
    private PDO $pdo;

    // Valid filter values (matches ENUM definitions in db.sql)
    private const VALID_CATEGORIES  = ['strength', 'cardio', 'hiit', 'flexibility'];
    private const VALID_DIFFICULTIES = ['beginner', 'intermediate', 'advanced'];

    public function __construct()
    {
        require_once __DIR__ . '/../includes/auth_check.php';
        $this->pdo = getDB();
    }

    /**
     * Builds all data needed by views/workouts.view.php.
     *
     * @return array
     */
    public function handle(): array
    {
        // Validate and sanitize GET filters against whitelists
        $filterCategory   = in_array($_GET['category']   ?? '', self::VALID_CATEGORIES)
                            ? $_GET['category'] : '';
        $filterDifficulty = in_array($_GET['difficulty']  ?? '', self::VALID_DIFFICULTIES)
                            ? $_GET['difficulty'] : '';

        return [
            'pageTitle'        => 'Workout Plans',
            'workouts'         => $this->getWorkouts($filterCategory, $filterDifficulty),
            'categoryCounts'   => $this->getCategoryCounts(),
            'filterCategory'   => $filterCategory,
            'filterDifficulty' => $filterDifficulty,
            'categoryMeta'     => $this->getCategoryMeta(),
            'difficultyMeta'   => $this->getDifficultyMeta(),
            'validCategories'  => self::VALID_CATEGORIES,
            'validDifficulties'=> self::VALID_DIFFICULTIES,
        ];
    }

    // ── Private Helpers ───────────────────────────────────────

    /**
     * Fetches workouts with optional category/difficulty filters.
     *
     * Builds the WHERE clause dynamically from validated inputs.
     * Parameters are bound via PDO prepared statements.
     *
     * @param string $category    Validated category filter or ''
     * @param string $difficulty  Validated difficulty filter or ''
     * @return array
     */
    private function getWorkouts(string $category, string $difficulty): array
    {
        $conditions = [];
        $params     = [];

        if ($category !== '') {
            $conditions[]       = 'category = :cat';
            $params[':cat']     = $category;
        }
        if ($difficulty !== '') {
            $conditions[]       = 'difficulty = :diff';
            $params[':diff']    = $difficulty;
        }

        $whereSQL = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $this->pdo->prepare(
            "SELECT * FROM workouts {$whereSQL} ORDER BY category, difficulty, name"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Returns workout count per category (used for filter badges).
     */
    private function getCategoryCounts(): array
    {
        $stmt = $this->pdo->query(
            'SELECT category, COUNT(*) AS cnt FROM workouts GROUP BY category'
        );
        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['category']] = $row['cnt'];
        }
        return $counts;
    }

    /**
     * UI metadata for each workout category.
     * Used by the view to render icons and CSS classes.
     */
    private function getCategoryMeta(): array
    {
        return [
            'strength'    => ['icon' => '🏋️', 'color' => 'cat-strength',    'label' => 'Strength'],
            'cardio'      => ['icon' => '🏃', 'color' => 'cat-cardio',       'label' => 'Cardio'],
            'hiit'        => ['icon' => '⚡', 'color' => 'cat-hiit',         'label' => 'HIIT'],
            'flexibility' => ['icon' => '🧘', 'color' => 'cat-flexibility',  'label' => 'Flexibility'],
        ];
    }

    /**
     * UI metadata for each difficulty level.
     */
    private function getDifficultyMeta(): array
    {
        return [
            'beginner'     => ['icon' => '🟢', 'label' => 'Beginner'],
            'intermediate' => ['icon' => '🟡', 'label' => 'Intermediate'],
            'advanced'     => ['icon' => '🔴', 'label' => 'Advanced'],
        ];
    }
}
