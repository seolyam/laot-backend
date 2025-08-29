<?php
// La-ot Workouts API
// Manage workout sessions and biometric data

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
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
    // Get workout sessions
    $user_id = $user['id'];
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Validate pagination
    if ($limit > 50) $limit = 50;
    if ($offset < 0) $offset = 0;
    
    $sql = "SELECT id, session_date, start_time, end_time, duration_minutes, workout_type, notes, created_at 
            FROM workout_sessions 
            WHERE athlete_id = ? 
            ORDER BY session_date DESC, start_time DESC 
            LIMIT ? OFFSET ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $user_id, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $sessions = [];
    while ($session = mysqli_fetch_assoc($result)) {
        // Get biometric data for this session
        $biometric_sql = "SELECT heart_rate, pace, distance, calories_burned, steps, timestamp 
                         FROM biometric_data 
                         WHERE session_id = ? 
                         ORDER BY timestamp ASC";
        $biometric_stmt = mysqli_prepare($conn, $biometric_sql);
        mysqli_stmt_bind_param($biometric_stmt, "i", $session['id']);
        mysqli_stmt_execute($biometric_stmt);
        $biometric_result = mysqli_stmt_get_result($biometric_stmt);
        
        $biometric_data = [];
        while ($biometric = mysqli_fetch_assoc($biometric_result)) {
            $biometric_data[] = $biometric;
        }
        
        $session['biometric_data'] = $biometric_data;
        $sessions[] = $session;
    }
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM workout_sessions WHERE athlete_id = ?";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($count_stmt, "i", $user_id);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total = mysqli_fetch_assoc($count_result)['total'];
    
    $response_data = [
        "sessions" => $sessions,
        "pagination" => [
            "total" => $total,
            "limit" => $limit,
            "offset" => $offset,
            "has_more" => ($offset + $limit) < $total
        ]
    ];
    
    api_response(true, "Workout sessions retrieved successfully", $response_data, 200);
    
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create new workout session
    if ($user['user_role'] !== 'athlete') {
        api_response(false, "Only athletes can create workout sessions", null, 403);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        api_response(false, "Invalid JSON input", null, 400);
    }
    
    $required_fields = ['session_date', 'workout_type'];
    if (!validate_required_fields($input, $required_fields)) {
        api_response(false, "Session date and workout type are required", null, 400);
    }
    
    $user_id = $user['id'];
    $session_date = sanitize_input($input['session_date']);
    $start_time = isset($input['start_time']) ? sanitize_input($input['start_time']) : null;
    $end_time = isset($input['end_time']) ? sanitize_input($input['end_time']) : null;
    $workout_type = sanitize_input($input['workout_type']);
    $notes = isset($input['notes']) ? sanitize_input($input['notes']) : null;
    
    // Calculate duration if start and end time provided
    $duration_minutes = null;
    if ($start_time && $end_time) {
        $start = strtotime($start_time);
        $end = strtotime($end_time);
        if ($start && $end && $end > $start) {
            $duration_minutes = round(($end - $start) / 60);
        }
    }
    
    // Insert workout session
    $sql = "INSERT INTO workout_sessions (athlete_id, session_date, start_time, end_time, duration_minutes, workout_type, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issssss", $user_id, $session_date, $start_time, $end_time, $duration_minutes, $workout_type, $notes);
    
    if (mysqli_stmt_execute($stmt)) {
        $session_id = mysqli_insert_id($conn);
        
        // Insert biometric data if provided
        if (isset($input['biometric_data']) && is_array($input['biometric_data'])) {
            $biometric_sql = "INSERT INTO biometric_data (athlete_id, session_id, heart_rate, pace, distance, calories_burned, steps) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
            $biometric_stmt = mysqli_prepare($conn, $biometric_sql);
            
            foreach ($input['biometric_data'] as $biometric) {
                $heart_rate = isset($biometric['heart_rate']) ? (int)$biometric['heart_rate'] : null;
                $pace = isset($biometric['pace']) ? (float)$biometric['pace'] : null;
                $distance = isset($biometric['distance']) ? (float)$biometric['distance'] : null;
                $calories = isset($biometric['calories_burned']) ? (int)$biometric['calories_burned'] : null;
                $steps = isset($biometric['steps']) ? (int)$biometric['steps'] : null;
                
                mysqli_stmt_bind_param($biometric_stmt, "iiiddii", $user_id, $session_id, $heart_rate, $pace, $distance, $calories, $steps);
                mysqli_stmt_execute($biometric_stmt);
            }
        }
        
        $response_data = [
            "session_id" => $session_id,
            "message" => "Workout session created successfully"
        ];
        
        api_response(true, "Workout session created successfully", $response_data, 201);
    } else {
        api_response(false, "Failed to create workout session", null, 500);
    }
    
} elseif ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // Update workout session
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['session_id'])) {
        api_response(false, "Session ID is required", null, 400);
    }
    
    $session_id = (int)$input['session_id'];
    $user_id = $user['id'];
    
    // Check if session belongs to user
    $check_sql = "SELECT id FROM workout_sessions WHERE id = ? AND athlete_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ii", $session_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (!mysqli_fetch_assoc($check_result)) {
        api_response(false, "Session not found or access denied", null, 404);
    }
    
    // Update fields
    $updatable_fields = ['end_time', 'workout_type', 'notes'];
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
    
    // Recalculate duration if end_time is updated
    if (isset($input['end_time'])) {
        $update_data[] = "duration_minutes = ?";
        $types .= 'i';
        
        // Get start time
        $start_sql = "SELECT start_time FROM workout_sessions WHERE id = ?";
        $start_stmt = mysqli_prepare($conn, $start_sql);
        mysqli_stmt_bind_param($start_stmt, "i", $session_id);
        mysqli_stmt_execute($start_stmt);
        $start_result = mysqli_stmt_get_result($start_stmt);
        $start_data = mysqli_fetch_assoc($start_result);
        
        if ($start_data['start_time']) {
            $start = strtotime($start_data['start_time']);
            $end = strtotime($input['end_time']);
            if ($start && $end && $end > $start) {
                $duration = round(($end - $start) / 60);
            } else {
                $duration = null;
            }
        } else {
            $duration = null;
        }
        
        $values[] = $duration;
    }
    
    $values[] = $session_id;
    $types .= 'i';
    
    $sql = "UPDATE workout_sessions SET " . implode(', ', $update_data) . " WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    
    if (mysqli_stmt_execute($stmt)) {
        api_response(true, "Workout session updated successfully", null, 200);
    } else {
        api_response(false, "Failed to update workout session", null, 500);
    }
    
} else {
    api_response(false, "Method not allowed", null, 405);
}
?>
