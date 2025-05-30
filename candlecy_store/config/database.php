<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "candlecy_store";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die(json_encode(['success' => false, 'error' => 'Database connection failed.']));
}
?>
