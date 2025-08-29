<?php
// La-ot Registration API
// Unified registration endpoint that handles both simple and full registration
// Enhanced security and validation for mobile app integration

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include "../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        api_response(false, "Invalid JSON input", null, 400);
    }
    
    // Check if this is a simple registration (username + password only)
    $is_simple_registration = isset($input["username"]) && isset($input["password"]) && 
                              count($input) === 2;
    
    if ($is_simple_registration) {
        // Simple registration mode - only username and password required
        $username = sanitize_input($input["username"]);
        $password = $input["password"];
        
        // Basic validation for simple registration
        if (strlen($username) < 3 || strlen($username) > 50) {
            api_response(false, "Username must be between 3 and 50 characters", null, 400);
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            api_response(false, "Username can only contain letters, numbers, and underscores", null, 400);
        }
        
        if (strlen($password) < 6) {
            api_response(false, "Password must be at least 6 characters", null, 400);
        }
        
        // Set default values for simple registration
        $first_name = $username;
        $last_name = '';
        $email = $username . '@example.com';
        $university = 'La-ot University';
        $user_role = 'athlete';
        $age = null;
        $weight = null;
        $height = null;
        
    } else {
        // Full registration mode - all fields required
        $required_fields = ['username', 'first_name', 'last_name', 'email', 'password', 'university'];
        if (!validate_required_fields($input, $required_fields)) {
            api_response(false, "All required fields must be filled", null, 400);
        }
        
        // Sanitize and validate inputs
        $username = sanitize_input($input["username"]);
        $first_name = sanitize_input($input["first_name"]);
        $last_name = sanitize_input($input["last_name"]);
        $email = sanitize_input($input["email"]);
        $university = sanitize_input($input["university"]);
        $password = $input["password"];
        
        // Optional fields
        $age = isset($input["age"]) ? (int)$input["age"] : null;
        $weight = isset($input["weight"]) ? (float)$input["weight"] : null;
        $height = isset($input["height"]) ? sanitize_input($input["height"]) : null;
        $user_role = isset($input["user_role"]) ? sanitize_input($input["user_role"]) : 'athlete';
        
        // Enhanced validation for full registration
        if (strlen($username) < 3 || strlen($username) > 50) {
            api_response(false, "Username must be between 3 and 50 characters", null, 400);
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            api_response(false, "Username can only contain letters, numbers, and underscores", null, 400);
        }
        
        if (!is_valid_email($email)) {
            api_response(false, "Invalid email format", null, 400);
        }
        
        if (!is_strong_password($password)) {
            api_response(false, "Password must be at least 8 characters with uppercase, lowercase, and number", null, 400);
        }
        
        if ($age !== null && ($age < 13 || $age > 100)) {
            api_response(false, "Age must be between 13 and 100", null, 400);
        }
        
        if ($weight !== null && ($weight < 30 || $weight > 300)) {
            api_response(false, "Weight must be between 30 and 300 kg", null, 400);
        }
        
        if (!in_array($user_role, ['athlete', 'coach'])) {
            api_response(false, "Invalid user role", null, 400);
        }
    }
    
    // Check if username or email already exists
    $check_sql = "SELECT username, email FROM users WHERE username=? OR email=?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    if (!$check_stmt) {
        api_response(false, "Database error", null, 500);
    }
    
    mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_fetch_assoc($check_result)) {
        api_response(false, "Username or email already exists", null, 409);
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert new user
        $sql = "INSERT INTO users (username, first_name, last_name, email, university, age, weight, height, password, user_role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "sssssissss", $username, $first_name, $last_name, $email, $university, $age, $weight, $height, $hashed_password, $user_role);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Registration failed: " . mysqli_error($conn));
        }
        
        $user_id = mysqli_insert_id($conn);
        
        // Create athlete profile if user is an athlete
        if ($user_role === 'athlete') {
            $profile_sql = "INSERT INTO athlete_profiles (user_id, sport, position, team, fitness_level) VALUES (?, ?, ?, ?, ?)";
            $profile_stmt = mysqli_prepare($conn, $profile_sql);
            if ($profile_stmt) {
                $sport = isset($input["sport"]) ? sanitize_input($input["sport"]) : 'General';
                $position = isset($input["position"]) ? sanitize_input($input["position"]) : 'Player';
                $team = isset($input["team"]) ? sanitize_input($input["team"]) : 'Team';
                $fitness_level = isset($input["fitness_level"]) ? sanitize_input($input["fitness_level"]) : 'beginner';
                
                mysqli_stmt_bind_param($profile_stmt, "issss", $user_id, $sport, $position, $team, $fitness_level);
                mysqli_stmt_execute($profile_stmt);
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Generate JWT token
        $token = generate_jwt($user_id, $username, $user_role);
        
        // Return success with user data and token
        $user_data = [
            "user_id" => $user_id,
            "username" => $username,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "email" => $email,
            "university" => $university,
            "user_role" => $user_role,
            "token" => $token,
            "registration_mode" => $is_simple_registration ? "simple" : "full"
        ];
        
        api_response(true, "Registration successful", $user_data, 201);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        api_response(false, $e->getMessage(), null, 500);
    }
    
} else {
    api_response(false, "Only POST method allowed", null, 405);
}
?>