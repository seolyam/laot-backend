<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard -Capstone</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?> 🎉</h2>
    <p>You are logged in to Capstone Project 2.</p>
    <a href="logout.php">Logout</a>
</body>
</html>