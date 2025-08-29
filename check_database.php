<?php
// Database Schema Checker
// This will show the actual structure of your database tables

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Schema Checker</h1>";

include "config.php";

if (!$conn) {
    echo "❌ Database connection failed<br>";
    exit;
}

echo "✅ Database connected successfully<br>";
echo "Database: $db<br>";
echo "Host: $host<br><br>";

// Check users table structure
echo "<h2>Users Table Structure</h2>";
$result = mysqli_query($conn, "DESCRIBE users");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error describing users table: " . mysqli_error($conn) . "<br>";
}

echo "<br>";

// Check if users table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($result) == 0) {
    echo "❌ Users table does not exist!<br>";
    echo "Available tables:<br>";
    $result = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_array($result)) {
        echo "- " . $row[0] . "<br>";
    }
} else {
    echo "✅ Users table exists<br>";
}

echo "<br>";

// Check athlete_profiles table structure
echo "<h2>Athlete Profiles Table Structure</h2>";
$result = mysqli_query($conn, "DESCRIBE athlete_profiles");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error describing athlete_profiles table: " . mysqli_error($conn) . "<br>";
}

echo "<br>";

// Check sample data
echo "<h2>Sample Data Check</h2>";
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Total users: " . $row['count'] . "<br>";
    
    if ($row['count'] > 0) {
        echo "<br>Sample user data:<br>";
        $result = mysqli_query($conn, "SELECT * FROM users LIMIT 1");
        if ($result) {
            $user = mysqli_fetch_assoc($result);
            echo "<pre>" . print_r($user, true) . "</pre>";
        }
    }
} else {
    echo "❌ Error counting users: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
