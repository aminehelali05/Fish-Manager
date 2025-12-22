<?php
// import.php — Temporary DB import helper.
// SECURITY: This script must be removed after use.
// Usage (web): https://<your-service>/import.php?token=LONG_SECRET_TOKEN
// Usage (cli): php import.php

// Change this token or set via IMPORT_TOKEN env var
$TOKEN = getenv('IMPORT_TOKEN') ?: 's3cure-IMPORT-T0KEN-9f3c6a2b7d4e6f1a2b3c4d5e6f7a8b9c';

// If called via web, require & validate token
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN) {
        http_response_code(403);
        echo "Forbidden\n";
        exit;
    }
}

// Locate SQL file
$sqlFile = __DIR__ . '/database.sql';
if (!file_exists($sqlFile)) {
    echo "Error: database.sql not found in project root.\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Error reading database.sql\n";
    exit(1);
}

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
// Do NOT rely on DB_NAME; script will run CREATE DATABASE/USE from SQL

$conn = @mysqli_connect($host, $user, $pass);
if (!$conn) {
    echo "DB connect error: " . mysqli_connect_error() . "\n";
    exit(1);
}

// Run multi-statement import
if (!mysqli_multi_query($conn, $sql)) {
    echo "Import failed: " . mysqli_error($conn) . "\n";
    mysqli_close($conn);
    exit(1);
}

// Drain results
do {
    if ($res = mysqli_store_result($conn)) {
        mysqli_free_result($res);
    }
} while (mysqli_more_results($conn) && mysqli_next_result($conn));

mysqli_close($conn);

echo "Import OK\n";

// IMPORTANT: After verifying import, delete this file for security:
// rm import.php && git rm import.php && git commit -m "Remove import helper" && git push

?>