<?php
// La-ot Profile API
// Get and update user profile information

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include "../config.php";

// Get authorization header
$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
} elseif (isset($headers['authorization'])) {
    $token = str_replace('Bearer ', '', $headers['authorization']);
}

if (!$token) {
    api_response(false, "Authorization token required", null, 401);
}

// Validate token and get user
$user = get_user_from_token($token);
if (!$user) {
    api_response(false, "Invalid or expired token", null, 401);
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get user profile
    $user_id = $user['id'];
    
    // Get basic user info
    $sql = "SELECT id, username, first_name, last_name, email, university, age, weight, height, user_role, created_at FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($result);
    
    // Get athlete profile if applicable
    $profile_data = null;
    if ($user['user_role'] === 'athlete') {
        $profile_sql = "SELECT sport, position, team, fitness_level, goals FROM athlete_profiles WHERE user_id = ?";
        $profile_stmt = mysqli_prepare($conn, $profile_sql);
        mysqli_stmt_bind_param($profile_stmt, "i", $user_id);
        mysqli_stmt_execute($profile_stmt);
        $profile_result = mysqli_stmt_get_result($profile_stmt);
        $profile_data = mysqli_fetch_assoc($profile_result);
        
        // Get recent workout sessions
        $sessions_sql = "SELECT id, session_date, start_time, end_time, duration_minutes, workout_type FROM workout_sessions WHERE athlete_id = ? ORDER BY session_date DESC LIMIT 5";
        $sessions_stmt = mysqli_prepare($conn, $sessions_sql);
        mysqli_stmt_bind_param($sessions_stmt, "i", $user_id);
        mysqli_stmt_execute($sessions_stmt);
        $sessions_result = mysqli_stmt_get_result($sessions_stmt);
        $recent_sessions = [];
        while ($session = mysqli_fetch_assoc($sessions_result)) {
            $recent_sessions[] = $session;
        }
        
        $profile_data['recent_sessions'] = $recent_sessions;
    }
    
    // Get goals
    $goals_sql = "SELECT id, goal_type, target_value, current_value, target_date, is_completed FROM goals WHERE athlete_id = ? ORDER BY created_at DESC";
    $goals_stmt = mysqli_prepare($conn, $goals_sql);
    mysqli_stmt_bind_param($goals_stmt, "i", $user_id);
    mysqli_stmt_execute($goals_stmt);
    $goals_result = mysqli_stmt_get_result($goals_stmt);
    $goals = [];
    while ($goal = mysqli_fetch_assoc($goals_result)) {
        $goals[] = $goal;
    }
    
    $response_data = [
        "user" => $user_data,
        "profile" => $profile_data,
        "goals" => $goals
    ];
    
    api_response(true, "Profile retrieved successfully", $response_data, 200);
    
} elseif ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // Update user profile
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        api_response(false, "Invalid JSON input", null, 400);
    }
    
    $user_id = $user['id'];
    
    // Fields that can be updated
    $updatable_fields = ['first_name', 'last_name', 'email', 'university', 'age', 'weight', 'height'];
    $update_data = [];
    $types = '';
    $values = [];
    
    foreach ($updatable_fields as $field) {
        if (isset($input[$field])) {
            $update_data[] = "$field = ?";
            $types .= 's';
            $values[] = sanitize_input($input[$field]);
        }
    }
    
    if (empty($update_data)) {
        api_response(false, "No fields to update", null, 400);
    }
    
    // Validate email if being updated
    if (isset($input['email']) && !is_valid_email($input['email'])) {
        api_response(false, "Invalid email format", null, 400);
    }
    
    // Check if email already exists (if being updated)
    if (isset($input['email'])) {
        $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "si", $input['email'], $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        if (mysqli_fetch_assoc($check_result)) {
            api_response(false, "Email already exists", null, 409);
        }
    }
    
    // Update user table
    $sql = "UPDATE users SET " . implode(', ', $update_data) . " WHERE id = ?";
    $types .= 'i';
    $values[] = $user_id;
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    
    if (mysqli_stmt_execute($stmt)) {
        // Update athlete profile if applicable
        if ($user['user_role'] === 'athlete' && isset($input['sport'])) {
            $profile_sql = "UPDATE athlete_profiles SET sport = ?, position = ?, team = ?, fitness_level = ? WHERE user_id = ?";
            $profile_stmt = mysqli_prepare($conn, $profile_sql);
            $sport = sanitize_input($input['sport'] ?? '');
            $position = sanitize_input($input['position'] ?? '');
            $team = sanitize_input($input['team'] ?? '');
            $fitness_level = sanitize_input($input['fitness_level'] ?? 'beginner');
            
            mysqli_stmt_bind_param($profile_stmt, "ssssi", $sport, $position, $team, $fitness_level, $user_id);
            mysqli_stmt_execute($profile_stmt);
        }
        
        api_response(true, "Profile updated successfully", null, 200);
    } else {
        api_response(false, "Failed to update profile", null, 500);
    }
    
} else {
    api_response(false, "Method not allowed", null, 405);
}
?>
