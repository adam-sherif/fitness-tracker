# 💪 FitTrack — Fitness & Nutrition Tracker

A full-featured web application for tracking daily nutrition, workouts, and body composition progress.  
Built with **PHP (PDO)**, **MySQL**, and **vanilla HTML5/CSS3** — no frameworks, no libraries.

---

## 📸 Pages Overview

| Page | Description |
|------|-------------|
| **Dashboard** | Daily calorie overview, macro summary, recent meals, latest progress |
| **Meal Logger** | Log meals with calories, protein, carbs, and fats. Filter by date |
| **Macros Calculator** | Visual breakdown of daily macros vs. goals. 7-day calorie trend chart |
| **Workout Plans** | Browse pre-defined workout plans filtered by category and difficulty |
| **Progress Tracker** | Full CRUD system for tracking weight and body fat percentage over time |

---

## 🗂️ Project Structure

```
fitness-tracker/
│
├── README.md                    ← This file
├── db.sql                       ← Database schema + seed data (run this first)
├── config.php                   ← DB credentials and PDO connection helper
├── .gitignore
│
├── docs/
│   └── DOCUMENTATION.md         ← Full technical documentation
│
│── css/
│   └── style.css                ← All styling (dark fitness theme, responsive)
│
├── includes/                    ← Shared PHP components
│   ├── auth_check.php           ← Session guard (redirects if not logged in)
│   ├── header.php               ← Top navigation bar (HTML)
│   └── footer.php               ← Page footer (HTML)
│
├── controllers/                 ← PHP business logic (NO HTML here)
│   ├── AuthController.php       ← Login & signup logic
│   ├── DashboardController.php  ← Dashboard data aggregation
│   ├── MealController.php       ← Meal CRUD logic
│   ├── MacroController.php      ← Macro calculation logic
│   ├── WorkoutController.php    ← Workout fetch & filter logic
│   └── ProgressController.php  ← Progress CRUD logic
│
├── views/                       ← Pure HTML templates (NO DB queries here)
│   ├── auth/
│   │   ├── login.view.php       ← Login form
│   │   └── signup.view.php      ← Registration form
│   ├── dashboard.view.php
│   ├── meals.view.php
│   ├── macros.view.php
│   ├── workouts.view.php
│   └── progress.view.php
│
└── (entry points — thin routers)
    ├── index.php
    ├── login.php
    ├── signup.php
    ├── logout.php
    ├── meals.php
    ├── macros.php
    ├── workouts.php
    └── progress.php
```

---

## ⚙️ Tech Stack

- **Frontend:** HTML5, CSS3 (Custom Properties, Grid, Flexbox)
- **Backend:** PHP 8.x (PDO, sessions, `password_hash`)
- **Database:** MySQL 5.7+ / MariaDB 10+
- **Architecture:** MVC-inspired — Controllers handle logic, Views handle display

---

## 🚀 Installation & Setup

### Prerequisites
- **XAMPP** (or any stack with Apache + PHP 8+ + MySQL)
- A web browser

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/YOUR_USERNAME/fitness-tracker.git
```

**2. Move to your web server root**
```bash
# XAMPP on Windows:
move fitness-tracker C:\xampp\htdocs\

# XAMPP on macOS:
mv fitness-tracker /Applications/XAMPP/htdocs/

# Ubuntu/Linux:
mv fitness-tracker /var/www/html/
```

**3. Create the database**

Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`) and:
- Click **"New"** to create a database (you can name it `fitness_tracker`)
- Select the new database → click **"Import"**
- Choose the file `db.sql` from this project → click **"Go"**

Or via MySQL command line:
```bash
mysql -u root -p < db.sql
```

**4. Configure the database connection**

Open `config.php` and edit the credentials to match your setup:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fitness_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');          // Empty for default XAMPP
```

**5. Open in your browser**
```
http://localhost/fitness-tracker/
```

**6. Create an account**

You'll be redirected to the login page. Click **"Sign up"** to create your first account.

---

## 🗄️ Database Schema

| Table | Purpose |
|-------|---------|
| `users` | Stores registered users (hashed passwords with bcrypt) |
| `meals` | Each food item logged by a user for a specific date |
| `workouts` | Pre-defined workout plans (seeded in `db.sql`) |
| `progress` | Daily weight and body fat check-ins per user |

---

## 🔐 Security Features

- **Passwords** are hashed using PHP's `password_hash()` with `PASSWORD_BCRYPT`
- **PDO Prepared Statements** prevent SQL injection on all queries
- **Session-based authentication** with `session_regenerate_id()` on login
- **Ownership checks** on all edit/delete operations (`WHERE id = ? AND user_id = ?`)
- All user output is escaped with `htmlspecialchars()` to prevent XSS

---

## 📚 Documentation

See [`docs/DOCUMENTATION.md`](docs/DOCUMENTATION.md) for full technical documentation including:
- Architecture explanation
- How each controller and view works
- Database relationships
- How to extend the project

---

## 👨‍💻 Author

Built as a university project for **Computer Science & IT** — Sinai University.

---

## 📄 License

MIT License — free to use, modify, and distribute.
