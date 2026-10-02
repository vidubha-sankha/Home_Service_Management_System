<?php
// config/db.php
// Shared database connection used by every page

require_once __DIR__ . '/../includes/env_loader.php';
loadEnv(__DIR__ . '/../.env');

$host = "localhost";
$dbname = "hsms_db";
$username = "root";
$password = "";   // default XAMPP password is blank

try {
    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Start session globally so every page can check login state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
