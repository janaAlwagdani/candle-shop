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

// Fetch all items from the cart for the current visitor
$sql = "SELECT product_id, product_name, product_price, product_quantity, product_image FROM cart WHERE guest_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Failed to prepare query: " . $conn->error);
    echo json_encode([]);
    exit;
}

$stmt->bind_param("s", $visitorID);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

echo json_encode($cartItems);

$stmt->close();
$conn->close();
?>