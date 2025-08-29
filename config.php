<?php
// La-ot Backend Configuration
// Enhanced security and JWT support for mobile app integration

// Database configuration
$host = "sql112.infinityfree.com";
$user = "if0_39673054";
$pass = "ZruGDI9gdZb";
$db = "if0_39673054_laot";

// JWT Configuration
define('JWT_SECRET', 'laot_super_secret_key_2024_change_in_production');
define('JWT_EXPIRY', 86400); // 24 hours

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Database connection with error handling
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    http_response_code(500);
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// Set charset to prevent SQL injection
mysqli_set_charset($conn, "utf8mb4");

// Enhanced input sanitization function
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// JWT token generation
function generate_jwt($user_id, $username, $user_role) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $user_id,
        'username' => $username,
        'user_role' => $user_role,
        'iat' => time(),
        'exp' => time() + JWT_EXPIRY
    ]);
    
    $base64_header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64_payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64_header . "." . $base64_payload, JWT_SECRET, true);
    $base64_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64_header . "." . $base64_payload . "." . $base64_signature;
}

// JWT token validation
function validate_jwt($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
    
    if (!$payload || $payload['exp'] < time()) {
        return false;
    }
    
    $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], JWT_SECRET, true);
    $expected_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return hash_equals($expected_signature, $parts[2]) ? $payload : false;
}

// Get user from JWT token
function get_user_from_token($token) {
    global $conn;
    
    $payload = validate_jwt($token);
    if (!$payload) {
        return false;
    }
    
    $user_id = $payload['user_id'];
    $sql = "SELECT id, username, first_name, last_name, email, user_role, university FROM users WHERE id = ? AND is_active = 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

// API response helper
function api_response($success, $message, $data = null, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data,
        "timestamp" => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Validate required fields
function validate_required_fields($input, $required_fields) {
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            return false;
        }
    }
    return true;
}

// Email validation
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Password strength validation
function is_strong_password($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}
?>