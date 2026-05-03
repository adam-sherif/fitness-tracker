# 📖 FitTrack — Technical Documentation

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [File-by-File Reference](#2-file-by-file-reference)
3. [Database Design](#3-database-design)
4. [Authentication Flow](#4-authentication-flow)
5. [Controller Pattern Explained](#5-controller-pattern-explained)
6. [How Views Work](#6-how-views-work)
7. [Data Flow Examples](#7-data-flow-examples)
8. [Security Practices](#8-security-practices)
9. [How to Extend the Project](#9-how-to-extend-the-project)

---

## 1. Architecture Overview

FitTrack uses an **MVC-inspired architecture** split into three layers:

```
Browser Request
      │
      ▼
 Entry Point (e.g. meals.php)
      │  — thin router, just wires controller + view
      │
      ├──► Controller (controllers/MealController.php)
      │         — all PHP logic, DB queries
      │         — returns $data array
      │
      └──► View (views/meals.view.php)
                — only HTML + echo $data[...]
                — zero DB queries
```

**Why this separation?**
- **Maintainability:** You can change the layout (view) without touching business logic (controller), and vice versa.
- **Readability:** Each file has one clear job.
- **Testing:** Controllers can be unit-tested without rendering HTML.

---

## 2. File-by-File Reference

### Entry Points (Root PHP files)
These are the URL-addressable pages. Each one is intentionally tiny:

```php
// Example: meals.php
<?php
require_once 'controllers/MealController.php';
$controller = new MealController(); // auth check happens inside constructor
$data       = $controller->handle(); // all logic runs here
$pageTitle  = $data['pageTitle'];
require_once 'includes/header.php'; // outputs nav HTML
require_once 'views/meals.view.php'; // outputs page HTML using $data
require_once 'includes/footer.php'; // outputs footer HTML
```

### `config.php`
- Defines database constants (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
- Provides `getDB(): PDO` — a singleton function that returns one PDO connection per request.
- Calls `session_start()` once so all included files share the session.

### `includes/auth_check.php`
- Must be the **first** thing included on any protected page.
- Checks if `$_SESSION['user_id']` exists.
- If not → `header('Location: login.php'); exit();`

### `includes/header.php`
- Outputs the `<!DOCTYPE html>` opening, `<head>`, and the `<nav>` bar.
- Uses `$currentPage = basename($_SERVER['PHP_SELF'])` to highlight the active nav link.
- Uses `$pageTitle` variable set by the entry point.

### `includes/footer.php`
- Closes the `<main>` tag, outputs `<footer>`, and closes `</body></html>`.

### `controllers/`
See Section 5 for details.

### `views/`
See Section 6 for details.

---

## 3. Database Design

### Entity-Relationship Summary

```
users (1) ──────< meals (N)      [one user has many meals]
users (1) ──────< progress (N)   [one user has many progress entries]
workouts                         [standalone, not user-specific]
```

### Table: `users`
| Column     | Type         | Notes                                |
|------------|--------------|--------------------------------------|
| id         | INT PK AI    | Auto-incremented primary key         |
| username   | VARCHAR(50)  | Unique login name                    |
| email      | VARCHAR(100) | Unique email address                 |
| password   | VARCHAR(255) | bcrypt hash via `password_hash()`    |
| age        | INT          | Optional, used for calorie estimates |
| weight     | FLOAT        | Initial weight in kg (optional)      |
| height     | FLOAT        | Height in cm (optional)              |
| goal       | ENUM         | `lose_weight`, `gain_muscle`, `maintain` |
| created_at | TIMESTAMP    | Auto-set on insert                   |

### Table: `meals`
| Column     | Type         | Notes                                |
|------------|--------------|--------------------------------------|
| id         | INT PK AI    |                                      |
| user_id    | INT FK       | References `users.id` (CASCADE DELETE) |
| food_name  | VARCHAR(150) | Name of the food/meal                |
| calories   | INT          | In kcal                              |
| protein    | FLOAT        | In grams                             |
| carbs      | FLOAT        | In grams                             |
| fats       | FLOAT        | In grams                             |
| meal_date  | DATE         | The date this meal was consumed      |
| created_at | TIMESTAMP    |                                      |

### Table: `workouts`
| Column      | Type    | Notes                                    |
|-------------|---------|------------------------------------------|
| id          | INT PK AI |                                        |
| name        | VARCHAR | Display name                             |
| category    | ENUM    | `strength`, `cardio`, `hiit`, `flexibility` |
| description | TEXT    | Short description                        |
| duration    | INT     | In minutes                               |
| difficulty  | ENUM    | `beginner`, `intermediate`, `advanced`   |
| exercises   | TEXT    | Newline-separated list of exercises      |

### Table: `progress`
| Column     | Type      | Notes                                  |
|------------|-----------|----------------------------------------|
| id         | INT PK AI |                                        |
| user_id    | INT FK    | References `users.id` (CASCADE DELETE) |
| weight     | FLOAT     | In kg (nullable)                       |
| body_fat   | FLOAT     | Percentage (nullable)                  |
| notes      | TEXT      | Optional user notes                    |
| log_date   | DATE      | The date of the check-in              |
| created_at | TIMESTAMP |                                        |

---

## 4. Authentication Flow

### Sign Up
```
1. User fills signup form (POST)
2. AuthController::handleSignup() runs:
   a. Sanitize & validate all inputs
   b. Check if username/email already exists in DB
   c. Hash password: $hash = password_hash($password, PASSWORD_BCRYPT)
   d. INSERT INTO users (...) VALUES (...)
   e. Store flash message in $_SESSION['flash_success']
   f. header('Location: login.php'); exit();
```

### Login
```
1. User fills login form (POST)
2. AuthController::handleLogin() runs:
   a. SELECT user WHERE username = ? OR email = ?
   b. password_verify($inputPassword, $storedHash)  ← bcrypt comparison
   c. If match:
      - $_SESSION['user_id']  = $user['id']
      - $_SESSION['username'] = $user['username']
      - session_regenerate_id(true)               ← prevents session fixation
      - header('Location: index.php'); exit();
   d. If no match: return error to view
```

### Protected Pages
Every protected page entry point includes `controllers/` which includes `auth_check.php`:
```
Request → MealController.php
              └── new MealController()
                      └── require_once 'includes/auth_check.php'
                              └── if (!$_SESSION['user_id']) → redirect to login
```

### Logout
```
logout.php:
  1. $_SESSION = []               ← clear all session data
  2. session_destroy()            ← destroy server-side session
  3. Delete session cookie        ← remove from browser
  4. header('Location: login.php')
```

---

## 5. Controller Pattern Explained

Each controller class follows this pattern:

```php
class MealController {

    private PDO $pdo;       // Database connection
    private int $userId;    // Logged-in user's ID (from session)

    public function __construct() {
        // 1. Auth check (redirects if not logged in)
        require_once __DIR__ . '/../includes/auth_check.php';
        // 2. Get DB connection
        $this->pdo    = getDB();
        // 3. Get user ID from session
        $this->userId = (int)$_SESSION['user_id'];
    }

    /**
     * Main entry point — called from the entry point PHP file.
     * Detects GET/POST and routes to the right private method.
     *
     * @return array  Data to be consumed by the view
     */
    public function handle(): array {
        if (isset($_GET['delete'])) {
            $this->deleteMeal((int)$_GET['delete']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->addMeal();
        }
        return $this->listMeals();
    }

    // Private methods handle individual operations
    private function addMeal(): array { ... }
    private function deleteMeal(int $id): void { ... redirect ... }
    private function listMeals(): array { ... return $data; }
}
```

**Key rule:** Controllers **never echo HTML**. They return a `$data` array.

---

## 6. How Views Work

Views are pure HTML templates that display data from `$data`:

```php
<!-- views/meals.view.php -->
<?php if (empty($data['meals'])): ?>
    <p class="empty-state">No meals logged yet.</p>
<?php else: ?>
    <?php foreach ($data['meals'] as $meal): ?>
        <tr>
            <td><?= htmlspecialchars($meal['food_name']) ?></td>
            <td><?= $meal['calories'] ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
```

**Key rules for views:**
- ✅ Use `$data['key']` to access values
- ✅ Use `htmlspecialchars()` on all user-generated text output
- ✅ Use `foreach`, `if`, `echo` for display logic
- ❌ No `new PDO()` or database queries
- ❌ No `require_once 'config.php'` (already done by the controller)
- ❌ No business logic (calculations should happen in the controller)

---

## 7. Data Flow Examples

### Example: Viewing Today's Meals

```
GET /meals.php
  │
  ├── MealController::__construct()
  │     ├── auth_check.php  → OK (user is logged in)
  │     ├── getDB()         → $this->pdo = PDO connection
  │     └── $_SESSION['user_id'] → $this->userId = 3
  │
  ├── MealController::handle()
  │     └── listMeals() called
  │           ├── SELECT * FROM meals WHERE user_id=3 AND meal_date='2026-05-03'
  │           ├── SELECT SUM(calories)... FROM meals WHERE user_id=3 AND meal_date=...
  │           └── returns $data = ['meals' => [...], 'totals' => [...], ...]
  │
  ├── includes/header.php   → Outputs <nav> HTML
  ├── views/meals.view.php  → Reads $data, outputs meal table HTML
  └── includes/footer.php   → Outputs <footer> HTML
```

### Example: Deleting a Progress Entry

```
GET /progress.php?action=delete&id=7
  │
  ├── ProgressController::handle()
  │     └── deleteEntry(7) called
  │           ├── DELETE FROM progress WHERE id=7 AND user_id=3
  │           │   (ownership check prevents deleting other users' data)
  │           ├── $_SESSION['flash_success'] = 'Entry deleted.'
  │           └── header('Location: progress.php'); exit();
  │
  └── (page reloads, flash message shown once then cleared)
```

---

## 8. Security Practices

### SQL Injection Prevention (PDO Prepared Statements)
```php
// ❌ VULNERABLE — never do this
$pdo->query("SELECT * FROM users WHERE username = '$username'");

// ✅ SAFE — always use prepared statements
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u');
$stmt->execute([':u' => $username]);
```

### Password Security (bcrypt)
```php
// Storing a password (signup):
$hash = password_hash($plainPassword, PASSWORD_BCRYPT);
// bcrypt automatically generates a salt and applies 10 rounds of hashing

// Verifying a password (login):
if (password_verify($plainPassword, $hashFromDB)) {
    // Login successful
}
```

### XSS Prevention (Output Escaping)
```php
// Always escape user data before displaying it in HTML
echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
// Converts <, >, ", ' to HTML entities so scripts can't execute
```

### Session Fixation Prevention
```php
// After successful login, regenerate the session ID
// This prevents an attacker who already has a session ID from hijacking it
session_regenerate_id(true);
```

### Ownership Checks on CRUD Operations
```php
// Always include user_id in WHERE clause to prevent IDOR attacks
// (Insecure Direct Object Reference — accessing other users' data by ID)
$stmt = $pdo->prepare(
    'DELETE FROM progress WHERE id = :id AND user_id = :uid'
);
```

---

## 9. How to Extend the Project

### Adding a New Page

1. **Create the Controller** in `controllers/MyFeatureController.php`
2. **Create the View** in `views/myfeature.view.php`
3. **Create the Entry Point** `myfeature.php` in the root
4. **Add a nav link** in `includes/header.php`

### Adding a New Database Table

1. Write the `CREATE TABLE` SQL in `db.sql`
2. Add any seed data you need with `INSERT INTO`
3. Re-import `db.sql` or run just the new statements in phpMyAdmin

### Adding a New Field to a Form

1. Add the `<input>` to the view (`.view.php`)
2. Add it to the controller's sanitization and validation block
3. Add it to the prepared statement's `execute()` parameter array
4. Add the column to the relevant table in `db.sql`

### Changing the Look and Feel

- All colors are CSS Custom Properties at the top of `css/style.css`
- Change `--accent`, `--bg-base`, `--bg-card` etc. to retheme the entire site instantly
