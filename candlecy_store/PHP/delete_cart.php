<?php
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../config/database.php';


// Ensure visitorID is set
if (!isset($_COOKIE['visitorID'])) {
    error_log("Visitor ID not found.");
    echo json_encode(["error" => "Visitor ID not found."]);
    exit;
}

$guest_id = $_COOKIE['visitorID'];
error_log("Guest ID: " . $guest_id);

// Delete all products from the cart for the current guest_id
$sql = "DELETE FROM cart WHERE guest_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Failed to prepare statement: " . $conn->error);
    echo json_encode(["error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

$stmt->bind_param("s", $guest_id);

if ($stmt->execute()) {
    echo json_encode(["message" => "All products have been removed from your cart."]);
} else {
    error_log("Failed to delete cart items: " . $conn->error);
    echo json_encode(["error" => "Failed to delete cart items."]);
}

$stmt->close();
$conn->close();
?>