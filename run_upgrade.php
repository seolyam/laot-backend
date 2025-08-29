<?php
// Database Upgrade Runner
// This will automatically upgrade your database to the correct structure

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Upgrade Runner</h1>";

include "config.php";

if (!$conn) {
    echo "❌ Database connection failed<br>";
    exit;
}

echo "✅ Database connected successfully<br>";
echo "Database: $db<br>";
echo "Host: $host<br><br>";

// Read and execute the upgrade script
echo "<h2>Running Database Upgrade</h2>";

$upgrade_sql = file_get_contents('upgrade_database_final.sql');
if (!$upgrade_sql) {
    echo "❌ Could not read upgrade script<br>";
    exit;
}

// Split the SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $upgrade_sql)));

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue; // Skip comments and empty lines
    }
    
    echo "Executing: " . substr($statement, 0, 50) . "...<br>";
    
    if (mysqli_query($conn, $statement)) {
        echo "✅ Success<br>";
        $success_count++;
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "<br>";
        $error_count++;
    }
}

echo "<br><h2>Upgrade Summary</h2>";
echo "Successful statements: $success_count<br>";
echo "Failed statements: $error_count<br>";

if ($error_count == 0) {
    echo "<br>🎉 Database upgrade completed successfully!<br>";
    echo "<a href='check_database.php'>Check Database Structure</a><br>";
    echo "<a href='test_curl.php'>Test API Endpoints</a><br>";
} else {
    echo "<br>⚠️ Some statements failed. Check the errors above.<br>";
}

mysqli_close($conn);
?>
