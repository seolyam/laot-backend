-- La-ot Database Schema
-- For student-athletes and coaches

-- Users table with role-based access
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    university VARCHAR(255) NOT NULL,
    age INT,
    weight DECIMAL(5,2), -- in kg
    height VARCHAR(10), -- in cm
    password VARCHAR(255) NOT NULL,
    user_role ENUM('athlete', 'coach', 'admin') DEFAULT 'athlete',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Athlete profiles with additional sports data
CREATE TABLE athlete_profiles (
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

-- Workout sessions
CREATE TABLE workout_sessions (
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

-- Biometric data from wearable devices
CREATE TABLE biometric_data (
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

-- Goals and progress tracking
CREATE TABLE goals (
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

-- Coach-athlete relationships
CREATE TABLE coach_athlete_relationships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coach_id INT NOT NULL,
    athlete_id INT NOT NULL,
    relationship_status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (athlete_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_coach_athlete (coach_id, athlete_id)
);

-- Insert default admin user
INSERT INTO users (username, first_name, last_name, email, university, user_role, password) 
VALUES ('admin', 'Admin', 'User', 'admin@laot.com', 'La-ot University', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Default password: password
