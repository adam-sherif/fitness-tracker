-- ============================================================
--  FitTrack — Fitness & Nutrition Tracker
--  db.sql  |  MySQL Database Schema + Seed Data
-- ============================================================
--  SETUP INSTRUCTIONS:
--    Option A (phpMyAdmin):
--      1. Create a database named `fitness_tracker`
--      2. Select it → click Import → choose this file → Go
--
--    Option B (command line):
--      mysql -u root -p < db.sql
-- ============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS fitness_tracker
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fitness_tracker;

-- ============================================================
-- TABLE: users
-- ============================================================
-- Stores all registered users.
-- Passwords are NEVER stored in plain text.
-- The `password` column holds a bcrypt hash produced by PHP's
-- password_hash($plain, PASSWORD_BCRYPT).
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    age        INT          DEFAULT NULL,
    weight     FLOAT        DEFAULT NULL,   -- kg (initial value at signup)
    height     FLOAT        DEFAULT NULL,   -- cm
    goal       ENUM('lose_weight', 'gain_muscle', 'maintain') DEFAULT 'maintain',
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: meals
-- ============================================================
-- Each row represents one food item logged by a user.
-- A single "meal" (e.g. lunch) may be split across multiple rows.
-- All macros stored in grams; calories in kcal.
--
-- FOREIGN KEY: If a user is deleted, all their meals are
-- automatically deleted too (ON DELETE CASCADE).
-- ============================================================
CREATE TABLE IF NOT EXISTS meals (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    food_name  VARCHAR(150) NOT NULL,
    calories   INT          NOT NULL DEFAULT 0,   -- kcal
    protein    FLOAT        NOT NULL DEFAULT 0,   -- grams
    carbs      FLOAT        NOT NULL DEFAULT 0,   -- grams
    fats       FLOAT        NOT NULL DEFAULT 0,   -- grams
    meal_date  DATE         NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Index for fast per-user, per-date lookups (used heavily by meals.php and macros.php)
CREATE INDEX IF NOT EXISTS idx_meals_user_date ON meals (user_id, meal_date);

-- ============================================================
-- TABLE: workouts
-- ============================================================
-- Pre-defined workout plans. These are read-only for users.
-- Seeded below with 8 plans covering all categories.
-- The `exercises` field holds a plain-text list, one exercise
-- per line. The view splits this into an array.
-- ============================================================
CREATE TABLE IF NOT EXISTS workouts (
    id          INT          AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    category    ENUM('strength', 'cardio', 'hiit', 'flexibility') NOT NULL,
    description TEXT,
    duration    INT          DEFAULT 30,  -- minutes
    difficulty  ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    exercises   TEXT                      -- newline-separated exercise list
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: progress
-- ============================================================
-- Daily body composition check-ins per user.
-- Both weight and body_fat are nullable — users can log one or both.
-- Full CRUD is supported in ProgressController.php.
-- ============================================================
CREATE TABLE IF NOT EXISTS progress (
    id         INT       AUTO_INCREMENT PRIMARY KEY,
    user_id    INT       NOT NULL,
    weight     FLOAT     DEFAULT NULL,  -- kg (nullable)
    body_fat   FLOAT     DEFAULT NULL,  -- percentage (nullable)
    notes      TEXT      DEFAULT NULL,
    log_date   DATE      NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Index for fast per-user lookups ordered by date
CREATE INDEX IF NOT EXISTS idx_progress_user_date ON progress (user_id, log_date);

-- ============================================================
-- SEED DATA: workouts
-- ============================================================
-- 8 pre-defined workout plans covering all 4 categories.
-- Exercises field uses \n (newline) as a separator — the PHP
-- view splits this with explode("\n", ...) for rendering.
-- ============================================================
INSERT INTO workouts (name, category, description, duration, difficulty, exercises) VALUES

('Full Body Blast',
 'strength',
 'A complete full-body strength session targeting every major muscle group. Rest 60 seconds between sets.',
 45, 'intermediate',
 'Barbell Squats — 4 x 12
Bench Press — 4 x 10
Bent-over Rows — 4 x 10
Overhead Press — 3 x 10
Romanian Deadlift — 3 x 12
Plank — 3 x 60 s'),

('Morning Cardio Run',
 'cardio',
 'Low-intensity steady-state cardio to boost metabolism and improve cardiovascular health.',
 35, 'beginner',
 'Warm-up walk — 5 min
Easy jog — 20 min
Moderate run — 5 min
Cool-down walk — 5 min'),

('HIIT Fat Burner',
 'hiit',
 'High-intensity intervals that maximize calorie burn in minimal time. 30 s work / 15 s rest per exercise.',
 25, 'advanced',
 'Burpees — 30 s
Jump Squats — 30 s
Mountain Climbers — 30 s
High Knees — 30 s
Push-up to T-Rotation — 30 s
(Repeat circuit x 5)'),

('Yoga & Flexibility Flow',
 'flexibility',
 'A gentle morning yoga flow to increase flexibility, reduce tension, and improve posture.',
 40, 'beginner',
 'Sun Salutation (A) — 3 rounds
Warrior I — 45 s each side
Warrior II — 45 s each side
Downward-Facing Dog — 60 s
Pigeon Pose — 60 s each side
Child''s Pose — 90 s'),

('Upper Body Power',
 'strength',
 'Chest, back, shoulders, and arms. Perfect for push/pull split routines. Rest 90 s between sets.',
 50, 'intermediate',
 'Incline Bench Press — 4 x 10
Pull-ups — 4 x 8
Dumbbell Shoulder Press — 3 x 12
Cable Rows — 3 x 12
Bicep Curls — 3 x 15
Tricep Pushdowns — 3 x 15'),

('Lower Body Strength',
 'strength',
 'Build powerful legs and glutes with compound and isolation movements.',
 50, 'intermediate',
 'Back Squats — 4 x 10
Romanian Deadlift — 4 x 10
Leg Press — 3 x 15
Walking Lunges — 3 x 12 each leg
Leg Curl — 3 x 12
Calf Raises — 4 x 20'),

('Core Crusher',
 'strength',
 'Intense ab and core workout to build a strong, stable midsection.',
 30, 'intermediate',
 'Weighted Crunches — 3 x 25
Hanging Leg Raises — 3 x 15
Russian Twists — 3 x 30
Ab Wheel Rollout — 3 x 12
Plank — 3 x 90 s
Side Plank — 3 x 45 s each side'),

('Beginner Full Body',
 'strength',
 'Bodyweight-only session. Perfect starting point for complete beginners — no equipment needed.',
 35, 'beginner',
 'Bodyweight Squats — 3 x 15
Knee Push-ups — 3 x 12
Glute Bridge — 3 x 15
Bird-Dog — 3 x 10 each side
Dead Bug — 3 x 10 each side
Wall Sit — 3 x 30 s');
