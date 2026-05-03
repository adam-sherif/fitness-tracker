<?php
// ============================================================
//  controllers/ProgressController.php — Progress CRUD Logic
// ============================================================
//  Implements full Create, Read, Update, Delete for `progress`.
//
//  Request Routing (inside handle()):
//    GET  ?action=delete&id=N  → deleteEntry(N) → redirect
//    GET  ?action=edit&id=N    → loads entry into $data['editEntry']
//    POST action=add           → addEntry()     → redirect or errors
//    POST action=update        → updateEntry()  → redirect or errors
//    GET  (default)            → listEntries()  → return data
//
//  Ownership rule: EVERY query that touches a row by ID also
//  filters by `user_id = :uid` to prevent unauthorized access.
// ============================================================

require_once __DIR__ . '/../config.php';

class ProgressController
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
     * Main router — detects action and delegates to the right method.
     *
     * @return array  Data for views/progress.view.php
     */
    public function handle(): array
    {
        $action = $_GET['action'] ?? ($_POST['action'] ?? '');

        // ── DELETE ────────────────────────────────────────────
        if ($action === 'delete' && isset($_GET['id'])) {
            $this->deleteEntry((int)$_GET['id']);
            // Always redirects, code below unreachable
        }

        // ── POST: Add or Update ───────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'add') {
                $result = $this->addEntry();
                if (isset($result['errors'])) {
                    // Validation failed — show form with errors
                    return array_merge($this->listEntries(), $result);
                }
            }
            if ($action === 'update') {
                $result = $this->updateEntry();
                if (isset($result['errors'])) {
                    return array_merge($this->listEntries(), $result);
                }
            }
        }

        // ── GET: Load Entry for Editing ───────────────────────
        $editEntry = null;
        if ($action === 'edit' && isset($_GET['id'])) {
            $editEntry = $this->getEntryById((int)$_GET['id']);
            if (!$editEntry) {
                $_SESSION['flash_error'] = 'Entry not found.';
                header('Location: progress.php');
                exit();
            }
        }

        $data = $this->listEntries();
        $data['editEntry'] = $editEntry;
        return $data;
    }

    // ── Private: CREATE ───────────────────────────────────────

    /**
     * Validates and inserts a new progress entry.
     * On success: sets flash + redirect. On failure: returns errors.
     */
    private function addEntry(): array
    {
        $errors  = [];
        $weight  = $_POST['weight']   !== '' ? (float)$_POST['weight']   : null;
        $bodyFat = $_POST['body_fat'] !== '' ? (float)$_POST['body_fat'] : null;
        $notes   = trim($_POST['notes']    ?? '');
        $logDate = $this->sanitizeDate($_POST['log_date'] ?? date('Y-m-d'));

        $errors = $this->validate($weight, $bodyFat);

        if (!empty($errors)) {
            return ['errors' => $errors, 'editEntry' => null];
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO progress (user_id, weight, body_fat, notes, log_date)
             VALUES (:uid, :w, :bf, :notes, :date)'
        );
        $stmt->execute([
            ':uid'   => $this->userId,
            ':w'     => $weight,
            ':bf'    => $bodyFat,
            ':notes' => $notes ?: null,
            ':date'  => $logDate,
        ]);

        $_SESSION['flash_success'] = '✅ Progress entry added!';
        header('Location: progress.php');
        exit();
    }

    // ── Private: UPDATE ───────────────────────────────────────

    /**
     * Validates and updates an existing progress entry.
     * Ownership check: `WHERE id = :id AND user_id = :uid`
     */
    private function updateEntry(): array
    {
        $entryId = (int)($_POST['entry_id'] ?? 0);
        $weight  = $_POST['weight']   !== '' ? (float)$_POST['weight']   : null;
        $bodyFat = $_POST['body_fat'] !== '' ? (float)$_POST['body_fat'] : null;
        $notes   = trim($_POST['notes']    ?? '');
        $logDate = $this->sanitizeDate($_POST['log_date'] ?? date('Y-m-d'));

        $errors = $this->validate($weight, $bodyFat);

        if (!empty($errors)) {
            // Re-load the entry for the edit form
            return ['errors' => $errors, 'editEntry' => $this->getEntryById($entryId)];
        }

        $stmt = $this->pdo->prepare(
            'UPDATE progress
             SET    weight = :w, body_fat = :bf, notes = :notes, log_date = :date
             WHERE  id = :id AND user_id = :uid'
        );
        $stmt->execute([
            ':w'    => $weight,
            ':bf'   => $bodyFat,
            ':notes'=> $notes ?: null,
            ':date' => $logDate,
            ':id'   => $entryId,
            ':uid'  => $this->userId,
        ]);

        $_SESSION['flash_success'] = '✅ Progress entry updated!';
        header('Location: progress.php');
        exit();
    }

    // ── Private: DELETE ───────────────────────────────────────

    /**
     * Deletes an entry — only if it belongs to this user.
     */
    private function deleteEntry(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM progress WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([':id' => $id, ':uid' => $this->userId]);

        $_SESSION['flash_success'] = '🗑️ Entry deleted.';
        header('Location: progress.php');
        exit();
    }

    // ── Private: READ (list) ──────────────────────────────────

    /**
     * Fetches all progress entries for this user and computes stats.
     */
    private function listEntries(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM progress
             WHERE  user_id = :uid
             ORDER  BY log_date DESC'
        );
        $stmt->execute([':uid' => $this->userId]);
        $entries = $stmt->fetchAll();

        // Calculate summary stats from weight entries
        $weightEntries = array_values(
            array_filter($entries, fn($e) => $e['weight'] !== null)
        );
        $stats = $this->calculateStats($weightEntries);

        // Grab and clear flash messages
        $flash      = $_SESSION['flash_success'] ?? '';
        $flashError = $_SESSION['flash_error']   ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        return [
            'pageTitle'     => 'Progress Tracker',
            'entries'       => $entries,
            'weightEntries' => $weightEntries,
            'stats'         => $stats,
            'flash'         => $flash,
            'flashError'    => $flashError,
            'errors'        => [],
            'editEntry'     => null,
        ];
    }

    // ── Private: READ (single) ────────────────────────────────

    /**
     * Fetches a single entry by ID — with ownership check.
     *
     * @return array|false
     */
    private function getEntryById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM progress WHERE id = :id AND user_id = :uid LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':uid' => $this->userId]);
        return $stmt->fetch();
    }

    // ── Private: Helpers ──────────────────────────────────────

    /**
     * Validates weight and body fat values.
     *
     * @return array  Empty if valid, list of error strings if not
     */
    private function validate(?float $weight, ?float $bodyFat): array
    {
        $errors = [];

        if ($weight === null && $bodyFat === null) {
            $errors[] = 'Enter at least weight or body fat percentage.';
        }
        if ($weight !== null && ($weight < 20 || $weight > 500)) {
            $errors[] = 'Weight must be between 20 and 500 kg.';
        }
        if ($bodyFat !== null && ($bodyFat < 1 || $bodyFat > 70)) {
            $errors[] = 'Body fat % must be between 1 and 70.';
        }

        return $errors;
    }

    /**
     * Computes min/max weight and total change from first to last entry.
     *
     * @param array $weightEntries  Entries that have a non-null weight
     */
    private function calculateStats(array $weightEntries): array
    {
        if (empty($weightEntries)) {
            return ['min' => null, 'max' => null, 'change' => null, 'latest' => null];
        }

        $weights = array_column($weightEntries, 'weight');

        // Sort by date ascending to find first/last weight
        $byDate = $weightEntries;
        usort($byDate, fn($a, $b) => strcmp($a['log_date'], $b['log_date']));
        $firstWeight  = (float)$byDate[0]['weight'];
        $latestWeight = (float)end($byDate)['weight'];

        return [
            'min'    => min($weights),
            'max'    => max($weights),
            'latest' => $latestWeight,
            'change' => round($latestWeight - $firstWeight, 1),
        ];
    }

    /**
     * Validates a date string — falls back to today if malformed.
     */
    private function sanitizeDate(string $date): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d');
    }
}
