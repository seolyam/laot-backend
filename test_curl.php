<?php
// Unified API Test for La-ot APIs
// Tests both simple and full registration modes

echo "<h1>La-ot API Unified Test</h1>";

// Test server connectivity first
echo "<h2>Testing Server Connectivity</h2>";
$test_url = "https://laot.great-site.net/laot-api/test_simple.php";
echo "Testing: $test_url<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
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

// Test simple registration (username + password only)
echo "<h2>Testing Simple Registration API</h2>";
$test_username = "testuser" . time();
$test_data = json_encode([
    "username" => $test_username,
    "password" => "TestPass123"
]);

echo "Test data: " . $test_data . "<br><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://laot.great-site.net/laot-api/api/register.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $test_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "<br>";
if ($error) {
    echo "cURL Error: " . $error . "<br>";
}
echo "Response: <pre>" . htmlspecialchars($response) . "</pre><br>";

// Test full registration (all fields)
echo "<h2>Testing Full Registration API</h2>";
$full_username = "fulluser" . time();
$full_data = json_encode([
    "username" => $full_username,
    "first_name" => "Full",
    "last_name" => "User",
    "email" => $full_username . "@example.com",
    "password" => "SecurePass123",
    "university" => "La-ot University",
    "age" => 25,
    "weight" => 75.5,
    "height" => "180cm",
    "user_role" => "athlete",
    "sport" => "Football",
    "position" => "Forward",
    "team" => "Team Alpha",
    "fitness_level" => "intermediate"
]);

echo "Full registration data: " . $full_data . "<br><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://laot.great-site.net/laot-api/api/register.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $full_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "<br>";
if ($error) {
    echo "cURL Error: " . $error . "<br>";
}
echo "Response: <pre>" . htmlspecialchars($response) . "</pre><br>";

// Test login with the simple user
echo "<h2>Testing Login API</h2>";
$login_data = json_encode([
    "username" => $test_username,
    "password" => "TestPass123"
]);

echo "Login data: " . $login_data . "<br><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://laot.great-site.net/laot-api/api/login.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $login_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "<br>";
if ($error) {
    echo "cURL Error: " . $error . "<br>";
}
echo "Response: <pre>" . htmlspecialchars($response) . "</pre><br>";

echo "<h2>Manual cURL Commands:</h2>";

echo "<h3>Test Server:</h3>";
echo "<code>curl -X GET https://laot.great-site.net/laot-api/test_simple.php</code><br><br>";

echo "<h3>Simple Registration (Username + Password Only):</h3>";
echo "<code>curl -X POST https://laot.great-site.net/laot-api/api/register.php \\<br>";
echo "  -H \"Content-Type: application/json\" \\<br>";
echo "  -d '{\"username\": \"testuser123\", \"password\": \"TestPass123\"}'</code><br><br>";

echo "<h3>Full Registration (All Fields):</h3>";
echo "<code>curl -X POST https://laot.great-site.net/laot-api/api/register.php \\<br>";
echo "  -H \"Content-Type: application/json\" \\<br>";
echo "  -d '{<br>";
echo "    \"username\": \"johndoe\",<br>";
echo "    \"first_name\": \"John\",<br>";
echo "    \"last_name\": \"Doe\",<br>";
echo "    \"email\": \"john.doe@example.com\",<br>";
echo "    \"password\": \"SecurePass123\",<br>";
echo "    \"university\": \"La-ot University\",<br>";
echo "    \"age\": 25,<br>";
echo "    \"user_role\": \"athlete\"<br>";
echo "  }'</code><br><br>";

echo "<h3>Login:</h3>";
echo "<code>curl -X POST https://laot.great-site.net/laot-api/api/login.php \\<br>";
echo "  -H \"Content-Type: application/json\" \\<br>";
echo "  -d '{\"username\": \"testuser123\", \"password\": \"TestPass123\"}'</code><br><br>";

echo "<h2>Expected Results:</h2>";
echo "- Test server should return HTTP 200 with server info<br>";
echo "- Simple registration should return HTTP 201 with user data (registration_mode: simple)<br>";
echo "- Full registration should return HTTP 201 with user data (registration_mode: full)<br>";
echo "- Login should return HTTP 200 with user data and JWT token<br>";
echo "- All should return JSON responses, not redirects<br>";

echo "<h2>API Cleanup Summary:</h2>";
echo "✅ Removed duplicate files: register_simple.php, login_simple.php<br>";
echo "✅ Unified registration endpoint: /api/register.php (handles both modes)<br>";
echo "✅ Unified login endpoint: /api/login.php<br>";
echo "✅ Updated API documentation<br>";
echo "✅ Maintained backward compatibility for simple registration<br>";

echo "<h2>Next Steps:</h2>";
echo "1. Test the unified endpoints above<br>";
echo "2. Update your applications to use the new unified endpoints<br>";
echo "3. The API now supports both simple and full registration modes<br>";
echo "4. All endpoints return consistent JSON responses with JWT tokens<br>";
?>
