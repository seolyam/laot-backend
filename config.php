<?php
$host = "sql112.infinityfree.com";
$user = "if0_39673054";
$pass = "ZruGDI9gdZb";
$db = "if0_39673054_laot";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>