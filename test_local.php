<?php
// Local API Test - Run this directly on your server
// This will test the API endpoints without external HTTP calls

echo "<h1>La-ot API Local Test</h1>";

// Test database connection first
echo "<h2>Testing Database Connection</h2>";
include "config.php";

if ($conn) {
    echo "✅ Database connection successful<br>";
    echo "Database: " . $db . "<br>";
    echo "Host: " . $host . "<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Test if API files exist and are accessible
echo "<h2>Testing API File Accessibility</h2>";

$api_files = [
    'api/register_simple.php',
    'api/login_simple.php'
];

foreach ($api_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file not found<br>";
    }
}

// Test direct API call simulation
echo "<h2>Testing Direct API Call Simulation</h2>";

// Simulate POST data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['username' => 'testuser' . time(), 'password' => 'TestPass123'];

// Capture output
ob_start();

// Test registration
echo "<h3>Testing Registration Logic</h3>";
try {
    // Include the registration file
    include "api/register_simple.php";
    $output = ob_get_clean();
    
    if (strpos($output, '"success":true') !== false) {
        echo "✅ Registration API working correctly<br>";
        echo "Response: <pre>" . htmlspecialchars($output) . "</pre>";
    } else {
        echo "❌ Registration API returned error<br>";
        echo "Response: <pre>" . htmlspecialchars($output) . "</pre>";
    }
} catch (Exception $e) {
    echo "❌ Registration API error: " . $e->getMessage() . "<br>";
}

// Test server info
echo "<h2>Server Information</h2>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "HTTP Host: " . $_SERVER['HTTP_HOST'] . "<br>";

// Test if .htaccess exists
echo "<h2>Checking .htaccess</h2>";
if (file_exists('.htaccess')) {
    echo "✅ .htaccess file exists<br>";
    echo "Content: <pre>" . htmlspecialchars(file_get_contents('.htaccess')) . "</pre>";
} else {
    echo "❌ No .htaccess file found<br>";
}

// Test directory permissions
echo "<h2>Directory Permissions</h2>";
$dirs = ['.', 'api'];
foreach ($dirs as $dir) {
    if (is_readable($dir)) {
        echo "✅ $dir is readable<br>";
    } else {
        echo "❌ $dir is not readable<br>";
    }
}
?>
