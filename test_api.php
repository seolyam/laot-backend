<?php
// La-ot API Test Script
// Use this to test your API endpoints

echo "<h1>La-ot API Test</h1>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
include "config.php";

if ($conn) {
    echo "✅ Database connected successfully<br>";
    
    // Check users table structure
    echo "<h3>Users Table Structure:</h3>";
    $result = mysqli_query($conn, "DESCRIBE users");
    if ($result) {
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Error describing table: " . mysqli_error($conn) . "<br>";
    }
    
    // Check existing users
    echo "<h3>Existing Users:</h3>";
    $result = mysqli_query($conn, "SELECT id, username, created_at FROM users LIMIT 5");
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Username</th><th>Created</th></tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No users found in database<br>";
        }
    } else {
        echo "❌ Error querying users: " . mysqli_error($conn) . "<br>";
    }
    
} else {
    echo "❌ Database connection failed<br>";
}

// Test API endpoints
echo "<h2>API Endpoint Tests</h2>";

// Test simple registration
echo "<h3>Testing Simple Registration API:</h3>";
$test_data = json_encode([
    "username" => "testuser" . time(),
    "password" => "TestPass123"
]);

echo "Test data: " . $test_data . "<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://laot.great-site.net/api/register_simple.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $test_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($test_data)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "<br>";
if ($error) {
    echo "cURL Error: " . $error . "<br>";
}
echo "Response: " . $response . "<br>";

// Test simple login
echo "<h3>Testing Simple Login API:</h3>";
$login_data = json_encode([
    "username" => "testuser" . (time() - 1),
    "password" => "TestPass123"
]);

echo "Login data: " . $login_data . "<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://laot.great-site.net/api/login_simple.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $login_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($login_data)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "<br>";
if ($error) {
    echo "cURL Error: " . $error . "<br>";
}
echo "Response: " . $response . "<br>";

echo "<h2>Next Steps:</h2>";
echo "1. If the simple APIs work, you can use them for now<br>";
echo "2. Run the upgrade_database.sql script to add missing columns<br>";
echo "3. Then use the full APIs (register.php, login.php, etc.)<br>";
echo "4. Test with cURL commands from the API documentation<br>";
?>
