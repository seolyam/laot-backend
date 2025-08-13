<?php
class SessionManager {
    private $timeout = 1800;
    
    public function __construct() {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        
        session_start();
        $this->checkTimeout();
    }
    
    private function checkTimeout() {
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > $this->timeout) {
                $this->destroySession();
                header("Location: enhanced_login.php?timeout=1");
                exit;
            }
            $_SESSION['login_time'] = time();
        }
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }
    
    public function destroySession() {
        session_unset();
        session_destroy();
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: enhanced_login.php");
            exit;
        }
    }
}
?>
