<?php
$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");
$db   = getenv("DB_NAME");

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("DB Connection failed");
}
?>
