<?php
class InputValidator {
    
    public static function validateUsername($username) {
        $username = trim($username);
        
        if (strlen($username) < 3 || strlen($username) > 50) {
            return [
                'valid' => false,
                'value' => '',
                'error' => 'Username must be between 3 and 50 characters'
            ];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return [
                'valid' => false,
                'value' => '',
                'error' => 'Username can only contain letters, numbers, and underscores'
            ];
        }
        
        $sanitized = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        
        return [
            'valid' => true,
            'value' => $sanitized,
            'error' => ''
        ];
    }
    
    /**
     * Validates password strength with multiple criteria
     * Ensures minimum length and character variety requirements
     */
    public static function validatePassword($password) {
        if (strlen($password) < 8) {
            return [
                'valid' => false,
                'error' => 'Password must be at least 8 characters long'
            ];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return [
                'valid' => false,
                'error' => 'Password must contain at least one uppercase letter'
            ];
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return [
                'valid' => false,
                'error' => 'Password must contain at least one lowercase letter'
            ];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return [
                'valid' => false,
                'error' => 'Password must contain at least one number'
            ];
        }
        
        return [
            'valid' => true,
            'error' => ''
        ];
    }
    
    public static function sanitizeText($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
}
?>
