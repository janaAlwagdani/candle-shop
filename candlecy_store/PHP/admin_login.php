<?php
// admin_login.php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
require_once __DIR__ . '/../config/database.php';


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminID = $_POST['adminID'];
    $password = $_POST['password'];

    // Debugging: Log the input values
    error_log("Admin ID: " . $adminID);
    error_log("Password: " . $password);

    // Prepare the SQL query
    $sql = "SELECT * FROM admin WHERE adminID = ? AND adminPassword = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("is", $adminID, $password); // "i" for adminID (int), "s" for adminPassword (string)
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the admin exists
    if ($result->num_rows > 0) {
        $_SESSION['adminID'] = $adminID;
        header("Location: ../HTML/admin_dashboard.html");
        exit();
    } else {
        echo "<p style='color: red;'>Invalid Admin ID or Password.</p>";
    }

    $stmt->close();
}

$conn->close();
?>
