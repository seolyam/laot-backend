-- La-ot Database Upgrade Script
-- Run this to upgrade your current database to support the full app

-- First, let's see what columns currently exist in your users table
-- DESCRIBE users;

-- Add missing columns to users table
ALTER TABLE users 
ADD COLUMN first_name VARCHAR(100) DEFAULT NULL AFTER username,
ADD COLUMN last_name VARCHAR(100) DEFAULT NULL AFTER first_name,
ADD COLUMN email VARCHAR(255) DEFAULT NULL AFTER last_name,
ADD COLUMN university VARCHAR(255) DEFAULT NULL AFTER email,
ADD COLUMN age INT DEFAULT NULL AFTER university,
ADD COLUMN weight DECIMAL(5,2) DEFAULT NULL AFTER age,
ADD COLUMN height VARCHAR(10) DEFAULT NULL AFTER weight,
ADD COLUMN user_role ENUM('athlete', 'coach', 'admin') DEFAULT 'athlete' AFTER height,
ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER user_role,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add unique constraint to email (optional - remove if you don't want this)
-- ALTER TABLE users ADD UNIQUE KEY unique_email (email);

-- Create athlete_profiles table
CREATE TABLE IF NOT EXISTS athlete_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sport VARCHAR(100),
    position VARCHAR(100),
    team VARCHAR(100),
    coach_id INT,
    fitness_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    goals TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create workout_sessions table
CREATE TABLE IF NOT EXISTS workout_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    athlete_id INT NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    duration_minutes INT,
    workout_type VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (athlete_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create biometric_data table
CREATE TABLE IF NOT EXISTS biometric_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    athlete_id INT NOT NULL,
    session_id INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    heart_rate INT, -- BPM
    pace DECIMAL(5,2), -- minutes per km
    distance DECIMAL(8,2), -- in meters
    calories_burned INT,
    steps INT,
    FOREIGN KEY (athlete_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES workout_sessions(id) ON DELETE SET NULL
);


CREATE TABLE IF NOT EXISTS goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    athlete_id INT NOT NULL,
    goal_type ENUM('distance', 'time', 'weight', 'heart_rate', 'custom') NOT NULL,
    target_value DECIMAL(10,2),
    current_value DECIMAL(10,2) DEFAULT 0,
    target_date DATE,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (athlete_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS coach_athlete_relationships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coach_id INT NOT NULL,
    athlete_id INT NOT NULL,
    relationship_status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (athlete_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_coach_athlete (coach_id, athlete_id)
);


UPDATE users SET 
    first_name = username,
    last_name = '',
    email = CONCAT(username, '@example.com'),
    university = 'La-ot University',
    user_role = 'athlete'
WHERE first_name IS NULL;


INSERT IGNORE INTO athlete_profiles (user_id, sport, position, team, fitness_level)
SELECT id, 'General', 'Player', 'Team', 'beginner' FROM users WHERE user_role = 'athlete';

-- Show the updated structure
-- DESCRIBE users;
-- SHOW TABLES;
