<?php
class RateLimiter {
    private $mysqli;
    private $maxAttempts = 5;
    private $lockoutDuration = 900;
    
    public function __construct($database) {
        $this->mysqli = $database;
    }
    
    /**
     * Checks if IP address or username is currently locked out
     * Implements dual-layer rate limiting with time-based calculations
     */
    public function isLockedOut($ipAddress, $username = null) {
        $currentTime = time();
        $lockoutTime = $currentTime - $this->lockoutDuration;
        
        $ipQuery = "SELECT COUNT(*) as attempt_count, MAX(UNIX_TIMESTAMP(attempt_time)) as last_attempt 
                   FROM login_attempts 
                   WHERE ip_address = ? AND success = FALSE AND attempt_time > FROM_UNIXTIME(?)";
        
        $stmt = $this->mysqli->prepare($ipQuery);
        $stmt->bind_param("si", $ipAddress, $lockoutTime);
        $stmt->execute();
        $result = $stmt->get_result();
        $ipData = $result->fetch_assoc();
        
        if ($ipData['attempt_count'] >= $this->maxAttempts) {
            $remainingTime = $this->lockoutDuration - ($currentTime - $ipData['last_attempt']);
            return [
                'locked' => true,
                'remaining_time' => max(0, $remainingTime)
            ];
        }
        
        if ($username) {
            $userQuery = "SELECT COUNT(*) as attempt_count, MAX(UNIX_TIMESTAMP(attempt_time)) as last_attempt 
                         FROM login_attempts 
                         WHERE username = ? AND success = FALSE AND attempt_time > FROM_UNIXTIME(?)";
            
            $stmt = $this->mysqli->prepare($userQuery);
            $stmt->bind_param("si", $username, $lockoutTime);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            
            if ($userData['attempt_count'] >= $this->maxAttempts) {
                $remainingTime = $this->lockoutDuration - ($currentTime - $userData['last_attempt']);
                return [
                    'locked' => true,
                    'remaining_time' => max(0, $remainingTime)
                ];
            }
        }
        
        return ['locked' => false, 'remaining_time' => 0];
    }
    
    public function recordAttempt($ipAddress, $username, $success) {
        $stmt = $this->mysqli->prepare(
            "INSERT INTO login_attempts (ip_address, username, success) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("ssi", $ipAddress, $username, $success);
        $stmt->execute();
        
        $cleanupTime = time() - 86400;
        $cleanupStmt = $this->mysqli->prepare(
            "DELETE FROM login_attempts WHERE attempt_time < FROM_UNIXTIME(?)"
        );
        $cleanupStmt->bind_param("i", $cleanupTime);
        $cleanupStmt->execute();
    }
    
    /**
     * Gets client IP address handling various proxy scenarios
     * Validates IP addresses and handles comma-separated proxy chains
     */
    public static function getClientIP() {
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
