<?php
session_start();
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../config/database.php';


// Ensure visitorID is set
if (!isset($_COOKIE['visitorID'])) {
    error_log("Visitor ID not found.");
    echo json_encode(['success' => false, 'error' => 'Visitor ID not found.']);
    exit;
}

$visitorID = $_COOKIE['visitorID'];

// Clear the checkout table for the current visitor
$sql = "DELETE FROM checkout WHERE guest_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Failed to prepare delete query: " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare delete query.']);
    exit;
}

$stmt->bind_param("s", $visitorID);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Checkout table cleared successfully.']);
} else {
    error_log("Failed to clear checkout table: " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Failed to clear checkout table.']);
}

$stmt->close();
$conn->close();
?>