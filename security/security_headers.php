<?php
class SecurityHeaders {
    
    public static function setSecurityHeaders() {
        header('X-XSS-Protection: 1; mode=block');
        
        header('X-Content-Type-Options: nosniff');
        
        header('X-Frame-Options: DENY');
        
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none';");
        
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    public static function setAuthHeaders() {
        self::setSecurityHeaders();
        
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('X-Robots-Tag: noindex, nofollow');
    }
}
?>
