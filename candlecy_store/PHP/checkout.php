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

// Fetch all items from the cart for the current visitor
$sql = "SELECT product_id, product_name, product_price, product_quantity, product_image FROM cart WHERE guest_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Failed to prepare fetch query: " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare fetch query.']);
    exit;
}
$stmt->bind_param("s", $visitorID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Your cart is empty.']);
    exit;
}

// Duplicate items into the checkout table
$insertSql = "INSERT INTO checkout (productID, productName, productPrice, productQuantity, productImage, guest_id)
              VALUES (?, ?, ?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertSql);
if (!$insertStmt) {
    error_log("Failed to prepare insert query: " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare insert query.']);
    exit;
}

while ($row = $result->fetch_assoc()) {
    $insertStmt->bind_param(
        "isdiss",
        $row['product_id'],
        $row['product_name'],
        $row['product_price'],
        $row['product_quantity'],
        $row['product_image'],
        $visitorID
    );

    if (!$insertStmt->execute()) {
        error_log("Failed to insert product into checkout: " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'Failed to add product to checkout.']);
        exit;
    }
}

echo json_encode(['success' => true, 'message' => 'Redirecting to checkout page...']);
$stmt->close();
$insertStmt->close();
$conn->close();
?>