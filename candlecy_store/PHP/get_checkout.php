<?php
session_start();
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../config/database.php';


// Ensure visitorID is set
if (!isset($_COOKIE['visitorID'])) {
    error_log("Visitor ID not found.");
    echo json_encode([]);
    exit;
}

$visitorID = $_COOKIE['visitorID'];

// Fetch all items from the checkout table for the current visitor
$sql = "SELECT productID, productName, productPrice, productQuantity, productImage FROM checkout WHERE guest_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Failed to prepare query: " . $conn->error);
    echo json_encode([]);
    exit;
}

$stmt->bind_param("s", $visitorID);
$stmt->execute();
$result = $stmt->get_result();

$checkoutItems = [];
while ($row = $result->fetch_assoc()) {
    $checkoutItems[] = $row;
}

echo json_encode($checkoutItems);

$stmt->close();
$conn->close();
?>