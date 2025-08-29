<?php
// Fix Password Column
// This will add the missing password column to the users table

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Fix Missing Password Column</h1>";

include "config.php";

if (!$conn) {
    echo "❌ Database connection failed<br>";
    exit;
}

echo "✅ Database connected successfully<br>";
echo "Database: $db<br>";
echo "Host: $host<br><br>";

// Check if password column exists
echo "<h2>Checking Current Users Table Structure</h2>";
$result = mysqli_query($conn, "DESCRIBE users");
if ($result) {
    $has_password = false;
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['Field'] === 'password') {
            $has_password = true;
            break;
        }
    }
    
    if ($has_password) {
        echo "✅ Password column already exists<br>";
    } else {
        echo "❌ Password column is missing - adding it now<br>";
        
        // Add the password column
        $sql = "ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL AFTER height";
        if (mysqli_query($conn, $sql)) {
            echo "✅ Password column added successfully<br>";
            
            // Update existing users with a default password
            $default_password = password_hash('changeme123', PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ? WHERE password IS NULL OR password = ''";
            $stmt = mysqli_prepare($conn, $update_sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $default_password);
                if (mysqli_stmt_execute($stmt)) {
                    echo "✅ Default passwords set for existing users<br>";
                    echo "Default password: changeme123<br>";
                } else {
                    echo "⚠️ Warning: Could not set default passwords: " . mysqli_error($conn) . "<br>";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            echo "❌ Error adding password column: " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "❌ Error describing users table: " . mysqli_error($conn) . "<br>";
}

echo "<br>";

// Show final table structure
echo "<h2>Final Users Table Structure</h2>";
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
}

echo "<br>";

// Test the API endpoints
echo "<h2>Next Steps</h2>";
echo "1. <a href='test_curl.php'>Test API Endpoints</a><br>";
echo "2. <a href='check_database.php'>Verify Database Structure</a><br>";

mysqli_close($conn);
?>
