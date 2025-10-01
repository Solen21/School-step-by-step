<?php
// Load environment variables if not already done
if (file_exists(__DIR__ . '/../../.env')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

$servername = $_ENV['DB_HOST'] ?? "localhost";
$username   = $_ENV['DB_USER'] ?? "root";
$password   = $_ENV['DB_PASS'] ?? "";
$dbname     = $_ENV['DB_NAME'] ?? "ethio_school";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
