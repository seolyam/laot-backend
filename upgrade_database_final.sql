-- La-ot Database Upgrade Script
-- Run this to ensure your database has the correct structure

-- Check if users table exists and has correct structure
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    university VARCHAR(255) NOT NULL,
    age INT,
    weight DECIMAL(5,2),
    height VARCHAR(10),
    password VARCHAR(255) NOT NULL,
    user_role ENUM('athlete', 'coach', 'admin') DEFAULT 'athlete',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add missing columns if they don't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS password VARCHAR(255) NOT NULL AFTER height,
ADD COLUMN IF NOT EXISTS user_role ENUM('athlete', 'coach', 'admin') DEFAULT 'athlete' AFTER password,
ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE AFTER user_role,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Check if athlete_profiles table exists
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

-- Check if workout_sessions table exists
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

-- Check if biometric_data table exists
CREATE TABLE IF NOT EXISTS biometric_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    athlete_id INT NOT NULL,
    session_id INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    heart_rate INT,
    pace DECIMAL(5,2),
    distance DECIMAL(8,2),
    calories_burned INT,
    steps INT,
    FOREIGN KEY (athlete_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES workout_sessions(id) ON DELETE SET NULL
);

-- Check if goals table exists
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

-- Check if coach_athlete_relationships table exists
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

-- Insert default admin user if not exists
INSERT IGNORE INTO users (username, first_name, last_name, email, university, user_role, password) 
VALUES ('admin', 'Admin', 'User', 'admin@laot.com', 'La-ot University', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Show current table structure
SHOW TABLES;
DESCRIBE users;
DESCRIBE athlete_profiles;
