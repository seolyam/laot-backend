<?php
// La-ot Login API
// Unified login endpoint with enhanced security and JWT token generation
// Supports both simple and enhanced login modes

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
    
    // Required fields for login
    $required_fields = ['username', 'password'];
    if (!validate_required_fields($input, $required_fields)) {
        api_response(false, "Username and password are required", null, 400);
    }
    
    $username = sanitize_input($input["username"]);
    $password = $input["password"];
    
    // Basic validation
    if (empty($username) || empty($password)) {
        api_response(false, "Username and password cannot be empty", null, 400);
    }
    
    // Check if username exists and is active
    $sql = "SELECT id, username, first_name, last_name, email, password, user_role, university, is_active FROM users WHERE username=?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        api_response(false, "Database error", null, 500);
    }
    
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        api_response(false, "Database error", null, 500);
    }
    
    $user = mysqli_fetch_assoc($result);
    
    if (!$user) {
        api_response(false, "Invalid username or password", null, 401);
    }
    
    // Check if user is active
    if (!$user['is_active']) {
        api_response(false, "Account is deactivated. Please contact administrator.", null, 403);
    }
    
    // Verify password
    if (!password_verify($password, $user["password"])) {
        api_response(false, "Invalid username or password", null, 401);
    }
    
    // Generate JWT token
    $token = generate_jwt($user['id'], $user['username'], $user['user_role']);
    
    // Get additional profile data for athletes
    $profile_data = null;
    if ($user['user_role'] === 'athlete') {
        $profile_sql = "SELECT sport, position, team, fitness_level FROM athlete_profiles WHERE user_id=?";
        $profile_stmt = mysqli_prepare($conn, $profile_sql);
        if ($profile_stmt) {
            mysqli_stmt_bind_param($profile_stmt, "i", $user['id']);
            mysqli_stmt_execute($profile_stmt);
            $profile_result = mysqli_stmt_get_result($profile_stmt);
            $profile_data = mysqli_fetch_assoc($profile_result);
        }
    }
    
    // Get coach's athletes if user is a coach
    $coach_data = null;
    if ($user['user_role'] === 'coach') {
        $athletes_sql = "SELECT u.id, u.username, u.first_name, u.last_name, u.email, ap.sport, ap.team 
                        FROM users u 
                        JOIN athlete_profiles ap ON u.id = ap.user_id 
                        JOIN coach_athlete_relationships car ON u.id = car.athlete_id 
                        WHERE car.coach_id = ? AND car.relationship_status = 'active'";
        $athletes_stmt = mysqli_prepare($conn, $athletes_sql);
        if ($athletes_stmt) {
            mysqli_stmt_bind_param($athletes_stmt, "i", $user['id']);
            mysqli_stmt_execute($athletes_stmt);
            $athletes_result = mysqli_stmt_get_result($athletes_stmt);
            $coach_data = [];
            while ($athlete = mysqli_fetch_assoc($athletes_result)) {
                $coach_data[] = $athlete;
            }
        }
    }
    
    // Update last login timestamp
    $update_sql = "UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
        mysqli_stmt_execute($update_stmt);
    }
    
    // Return success with user data and token
    $user_data = [
        "user_id" => $user['id'],
        "username" => $user['username'],
        "first_name" => $user['first_name'],
        "last_name" => $user['last_name'],
        "email" => $user['email'],
        "university" => $user['university'],
        "user_role" => $user['user_role'],
        "token" => $token,
        "profile" => $profile_data,
        "coach_data" => $coach_data,
        "login_timestamp" => date('Y-m-d H:i:s')
    ];
    
    api_response(true, "Login successful", $user_data, 200);
    
} else {
    api_response(false, "Only POST method allowed", null, 405);
}
?>