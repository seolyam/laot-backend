<?php
require_once '../config/database.php';
require_once '../security/input_validator.php';
require_once '../security/rate_limiter.php';
require_once '../security/security_headers.php';
require_once 'csrf_protection.php';

class UserAuthentication {
    private $mysqli;
    private $rateLimiter;
    
    public function __construct($database) {
        $this->mysqli = $database;
        $this->rateLimiter = new RateLimiter($database);
    }
    
    /**
     * Registers a new user with comprehensive validation
     * Handles username uniqueness check, password hashing, and database insertion
     */
    public function registerUser($username, $password) {
        $usernameValidation = InputValidator::validateUsername($username);
        if (!$usernameValidation['valid']) {
            return ['success' => false, 'message' => $usernameValidation['error']];
        }
        
        $passwordValidation = InputValidator::validatePassword($password);
        if (!$passwordValidation['valid']) {
            return ['success' => false, 'message' => $passwordValidation['error']];
        }
        
        $stmt = $this->mysqli->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $usernameValidation['value']);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            return ['success' => false, 'message' => 'Username already exists'];
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $this->mysqli->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $insertStmt->bind_param("ss", $usernameValidation['value'], $passwordHash);
        
        if ($insertStmt->execute()) {
            return ['success' => true, 'message' => 'Registration successful'];
        } else {
            error_log('Registration failed: ' . $insertStmt->error);
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    /**
     * Authenticates user login with rate limiting and security measures
     * Implements brute force protection and secure session initialization
     */
    public function loginUser($username, $password) {
        $clientIP = RateLimiter::getClientIP();
        
        $lockoutStatus = $this->rateLimiter->isLockedOut($clientIP, $username);
        if ($lockoutStatus['locked']) {
            $minutes = ceil($lockoutStatus['remaining_time'] / 60);
            return [
                'success' => false, 
                'message' => "Too many failed attempts. Try again in {$minutes} minutes."
            ];
        }
        
        $usernameValidation = InputValidator::validateUsername($username);
        if (!$usernameValidation['valid']) {
            $this->rateLimiter->recordAttempt($clientIP, $username, false);
            return ['success' => false, 'message' => 'Invalid username format'];
        }
        
        $stmt = $this->mysqli->prepare("SELECT id, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $usernameValidation['value']);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($userId, $passwordHash);
            $stmt->fetch();
            
            if (password_verify($password, $passwordHash)) {
                $this->rateLimiter->recordAttempt($clientIP, $username, true);
                $this->startSecureSession($userId, $usernameValidation['value']);
                
                return [
                    'success' => true, 
                    'message' => 'Login successful',
                    'user_id' => $userId
                ];
            }
        }
        
        $this->rateLimiter->recordAttempt($clientIP, $username, false);
        return ['success' => false, 'message' => 'Invalid username or password'];
    }
    
    /**
     * Initializes secure session with anti-fixation measures
     * Sets secure cookie parameters and session variables
     */
    private function startSecureSession($userId, $username) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => 3600,
            'path' => $cookieParams['path'],
            'domain' => $cookieParams['domain'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    public function isValidSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return false;
        }
        
        $sessionTimeout = 1800;
        if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
            $this->logoutUser();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public function logoutUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
}
?>
