<?php
// Test file to check if hosting provider is interfering with API responses
// Run this directly on your server to see what's happening

echo "<h1>Direct API Test</h1>";

// Test 1: Check if we can make a direct request to register.php
echo "<h2>Test 1: Direct Request to register.php</h2>";

$test_data = json_encode([
    "username" => "testuser" . time(),
    "first_name" => "Test",
    "last_name" => "User", 
    "email" => "test" . time() . "@example.com",
    "password" => "TestPass123",
    "university" => "University of St. La Salle",
    "user_role" => "athlete",
    "sport" => "General",
    "position" => "Player",
    "team" => "Team",
    "fitness_level" => "beginner"
]);

echo "Test data: <pre>" . htmlspecialchars($test_data) . "</pre><br>";

// Make internal request to register.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://laot.great-site.net/laot-api/api/register.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $test_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "<br>";
if ($error) {
    echo "cURL Error: " . $error . "<br>";
}
echo "Response: <pre>" . htmlspecialchars($response) . "</pre><br>";

// Test 2: Check if we can access the file directly
echo "<h2>Test 2: Direct File Access</h2>";
echo "Can we access register.php directly? <br>";

$file_content = file_get_contents("api/register.php");
if ($file_content !== false) {
    echo "✅ File accessible, first 200 chars: <pre>" . htmlspecialchars(substr($file_content, 0, 200)) . "</pre>";
} else {
    echo "❌ File not accessible";
}

// Test 3: Check server configuration
echo "<h2>Test 3: Server Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// Test 4: Check if .htaccess is interfering
echo "<h2>Test 4: .htaccess Check</h2>";
$htaccess_path = "../.htaccess";
if (file_exists($htaccess_path)) {
    echo "✅ .htaccess exists<br>";
    $htaccess_content = file_get_contents($htaccess_path);
    echo "Content: <pre>" . htmlspecialchars($htaccess_content) . "</pre>";
} else {
    echo "❌ No .htaccess file found<br>";
}
?>
