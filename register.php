<?php
require_once 'config/database.php';
require_once 'auth/user_authentication.php';
require_once 'security/security_headers.php';

SecurityHeaders::setAuthHeaders();

$auth = new UserAuthentication($mysqli);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRFProtection::verifyToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security token validation failed';
    } else {
        $result = $auth->registerUser($_POST['username'] ?? '', $_POST['password'] ?? '');
        $message = $result['message'];
        
        if ($result['success']) {
            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #218838; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .requirements { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <h2>Register</h2>
    
    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'successful') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <?php echo CSRFProtection::getTokenField(); ?>
        
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <div class="requirements">3-50 characters, letters, numbers, and underscores only</div>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <div class="requirements">
                Minimum 8 characters with at least one uppercase letter, lowercase letter, and number
            </div>
        </div>
        
        <button type="submit">Register</button>
    </form>
    
    <p><a href="login.php">Already have an account? Login here</a></p>
</body>
</html>
