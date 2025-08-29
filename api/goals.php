<?php
// La-ot Goals API
// Manage fitness goals and progress tracking

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
    // Get user goals
    $user_id = $user['id'];
    
    $sql = "SELECT id, goal_type, target_value, current_value, target_date, is_completed, created_at 
            FROM goals 
            WHERE athlete_id = ? 
            ORDER BY created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $goals = [];
    while ($goal = mysqli_fetch_assoc($result)) {
        // Calculate progress percentage
        if ($goal['target_value'] > 0) {
            $progress = min(100, round(($goal['current_value'] / $goal['target_value']) * 100, 2));
            $goal['progress_percentage'] = $progress;
        } else {
            $goal['progress_percentage'] = 0;
        }
        
        // Check if goal is overdue
        if ($goal['target_date'] && !$goal['is_completed']) {
            $target_timestamp = strtotime($goal['target_date']);
            $goal['is_overdue'] = $target_timestamp < time();
        } else {
            $goal['is_overdue'] = false;
        }
        
        $goals[] = $goal;
    }
    
    // Get goal statistics
    $stats_sql = "SELECT 
                    COUNT(*) as total_goals,
                    SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed_goals,
                    SUM(CASE WHEN is_completed = 0 THEN 1 ELSE 0 END) as active_goals
                   FROM goals 
                   WHERE athlete_id = ?";
    
    $stats_stmt = mysqli_prepare($conn, $stats_sql);
    mysqli_stmt_bind_param($stats_stmt, "i", $user_id);
    mysqli_stmt_execute($stats_stmt);
    $stats_result = mysqli_stmt_get_result($stats_stmt);
    $stats = mysqli_fetch_assoc($stats_result);
    
    $response_data = [
        "goals" => $goals,
        "statistics" => $stats
    ];
    
    api_response(true, "Goals retrieved successfully", $response_data, 200);
    
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create new goal
    if ($user['user_role'] !== 'athlete') {
        api_response(false, "Only athletes can create goals", null, 403);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        api_response(false, "Invalid JSON input", null, 400);
    }
    
    $required_fields = ['goal_type', 'target_value'];
    if (!validate_required_fields($input, $required_fields)) {
        api_response(false, "Goal type and target value are required", null, 400);
    }
    
    $user_id = $user['id'];
    $goal_type = sanitize_input($input['goal_type']);
    $target_value = (float)$input['target_value'];
    $target_date = isset($input['target_date']) ? sanitize_input($input['target_date']) : null;
    $current_value = isset($input['current_value']) ? (float)$input['current_value'] : 0;
    
    // Validate goal type
    $valid_types = ['distance', 'time', 'weight', 'heart_rate', 'custom'];
    if (!in_array($goal_type, $valid_types)) {
        api_response(false, "Invalid goal type", null, 400);
    }
    
    // Validate target value
    if ($target_value <= 0) {
        api_response(false, "Target value must be greater than 0", null, 400);
    }
    
    // Validate target date if provided
    if ($target_date && strtotime($target_date) < time()) {
        api_response(false, "Target date cannot be in the past", null, 400);
    }
    
    // Insert goal
    $sql = "INSERT INTO goals (athlete_id, goal_type, target_value, current_value, target_date) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isddss", $user_id, $goal_type, $target_value, $current_value, $target_date);
    
    if (mysqli_stmt_execute($stmt)) {
        $goal_id = mysqli_insert_id($conn);
        
        $response_data = [
            "goal_id" => $goal_id,
            "message" => "Goal created successfully"
        ];
        
        api_response(true, "Goal created successfully", $response_data, 201);
    } else {
        api_response(false, "Failed to create goal", null, 500);
    }
    
} elseif ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // Update goal
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['goal_id'])) {
        api_response(false, "Goal ID is required", null, 400);
    }
    
    $goal_id = (int)$input['goal_id'];
    $user_id = $user['id'];
    
    // Check if goal belongs to user
    $check_sql = "SELECT id FROM goals WHERE id = ? AND athlete_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ii", $goal_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (!mysqli_fetch_assoc($check_result)) {
        api_response(false, "Goal not found or access denied", null, 404);
    }
    
    // Update fields
    $updatable_fields = ['target_value', 'current_value', 'target_date', 'is_completed'];
    $update_data = [];
    $types = '';
    $values = [];
    
    foreach ($updatable_fields as $field) {
        if (isset($input[$field])) {
            $update_data[] = "$field = ?";
            if ($field === 'is_completed') {
                $types .= 'i';
                $values[] = (int)$input[$field];
            } elseif ($field === 'target_value' || $field === 'current_value') {
                $types .= 'd';
                $values[] = (float)$input[$field];
            } else {
                $types .= 's';
                $values[] = sanitize_input($input[$field]);
            }
        }
    }
    
    if (empty($update_data)) {
        api_response(false, "No fields to update", null, 400);
    }
    
    // Validate target date if being updated
    if (isset($input['target_date']) && strtotime($input['target_date']) < time()) {
        api_response(false, "Target date cannot be in the past", null, 400);
    }
    
    $values[] = $goal_id;
    $types .= 'i';
    
    $sql = "UPDATE goals SET " . implode(', ', $update_data) . " WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    
    if (mysqli_stmt_execute($stmt)) {
        api_response(true, "Goal updated successfully", null, 200);
    } else {
        api_response(false, "Failed to update goal", null, 500);
    }
    
} elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    // Delete goal
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['goal_id'])) {
        api_response(false, "Goal ID is required", null, 400);
    }
    
    $goal_id = (int)$input['goal_id'];
    $user_id = $user['id'];
    
    // Check if goal belongs to user
    $check_sql = "SELECT id FROM goals WHERE id = ? AND athlete_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ii", $goal_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (!mysqli_fetch_assoc($check_result)) {
        api_response(false, "Goal not found or access denied", null, 404);
    }
    
    // Delete goal
    $delete_sql = "DELETE FROM goals WHERE id = ? AND athlete_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "ii", $goal_id, $user_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        api_response(true, "Goal deleted successfully", null, 200);
    } else {
        api_response(false, "Failed to delete goal", null, 500);
    }
    
} else {
    api_response(false, "Method not allowed", null, 405);
}
?>
