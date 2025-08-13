<?php

class SecurityManager {
    private $mysqli;
    private $maxAttempts = 5;
    private $lockoutTime = 900;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    /**
     * Checks rate limiting for both IP and username-based attempts
     * Implements dual-layer protection against brute force attacks
     */
    public function checkRateLimit($ipAddress, $username = null) {
        $timeThreshold = date('Y-m-d H:i:s', time() - $this->lockoutTime);
        
        $stmt = $this->mysqli->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > ? AND success = FALSE");
        $stmt->bind_param("ss", $ipAddress, $timeThreshold);
        $stmt->execute();
        $stmt->bind_result($ipAttempts);
        $stmt->fetch();
        $stmt->close();
        
        if ($ipAttempts >= $this->maxAttempts) {
            return false;
        }
        
        if ($username) {
            $stmt = $this->mysqli->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = ? AND attempt_time > ? AND success = FALSE");
            $stmt->bind_param("ss", $username, $timeThreshold);
            $stmt->execute();
            $stmt->bind_result($userAttempts);
            $stmt->fetch();
            $stmt->close();
            
            if ($userAttempts >= $this->maxAttempts) {
                return false;
            }
        }
        
        return true;
    }
    
    public function logLoginAttempt($ipAddress, $username, $success) {
        $stmt = $this->mysqli->prepare("INSERT INTO login_attempts (ip_address, username, success) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $ipAddress, $username, $success);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Sanitizes input based on type with specific validation rules
     * Handles username, email, and general input with appropriate filtering
     */
    public function sanitizeInput($input, $type = 'general') {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        switch ($type) {
            case 'username':
                $input = preg_replace('/[^a-zA-Z0-9_-]/', '', $input);
                $input = substr($input, 0, 50);
                break;
            case 'email':
                $input = filter_var($input, FILTER_SANITIZE_EMAIL);
                break;
            case 'general':
            default:
                $input = preg_replace('/[<>"\']/', '', $input);
                break;
        }
        
        return $input;
    }
    
    public function validateInput($input, $type = 'general') {
        switch ($type) {
            case 'username':
                return preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $input);
            case 'password':
                return strlen($input) >= 8 && strlen($input) <= 255;
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
            default:
                return !empty($input) && strlen($input) <= 255;
        }
    }
    
    public function setSecureHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none';");
        
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    /**
     * Detects client IP address handling various proxy scenarios
     * Validates IP addresses and handles comma-separated proxy chains
     */
    public function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
?>
