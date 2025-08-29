<?php
// Simple test endpoint to verify server functionality
header('Content-Type: application/json');

$response = [
    "success" => true,
    "message" => "Server is working correctly",
    "timestamp" => date('Y-m-d H:i:s'),
    "server_info" => [
        "php_version" => PHP_VERSION,
        "server_software" => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        "document_root" => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        "script_name" => $_SERVER['SCRIPT_NAME'] ?? 'Unknown',
        "request_uri" => $_SERVER['REQUEST_URI'] ?? 'Unknown'
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
