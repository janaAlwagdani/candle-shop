<?php
session_start();
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../config/database.php';


// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;
$productID = $data['productID'] ?? null;
$newQuantity = $data['newQuantity'] ?? null;

// Ensure visitorID is set
if (!isset($_COOKIE['visitorID'])) {
    error_log("Visitor ID not found.");
    echo json_encode(['success' => false, 'error' => 'Visitor ID not found.']);
    exit;
}

$guest_id = $_COOKIE['visitorID'];
error_log("Guest ID: " . $guest_id);

// Validate action
if (!$action || !in_array($action, ['delete', 'update'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    exit;
}

// Handle delete action
if ($action === 'delete') {
    // Validate productID
    if (!$productID || !is_numeric($productID)) {
        echo json_encode(['success' => false, 'error' => 'Invalid product ID.']);
        exit;
    }

    // Delete the product from the cart
    $sql = "DELETE FROM cart WHERE guest_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare delete statement: " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'Failed to prepare delete statement: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("si", $guest_id, $productID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
    } else {
        error_log("Failed to delete product: " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'Failed to delete product: ' . $conn->error]);
    }

    $stmt->close();
}

// Handle update action
if ($action === 'update') {
    // Validate productID and newQuantity
    if (!$productID || !is_numeric($productID) || !$newQuantity || !is_numeric($newQuantity)) {
        echo json_encode(['success' => false, 'error' => 'Invalid input.']);
        exit;
    }

    // Check if the new quantity exceeds the available stock
    $stockCheckSql = "SELECT productStock FROM product WHERE productID = ?";
    $stockCheckStmt = $conn->prepare($stockCheckSql);
    if (!$stockCheckStmt) {
        error_log("Failed to prepare stock check statement: " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'Failed to prepare stock check statement: ' . $conn->error]);
        exit;
    }

    $stockCheckStmt->bind_param("i", $productID);
    $stockCheckStmt->execute();
    $stockResult = $stockCheckStmt->get_result();

    if ($stockResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Product not found.']);
        exit;
    }

    $stockRow = $stockResult->fetch_assoc();
    $availableStock = $stockRow['productStock'];

    if ($newQuantity > $availableStock) {
        echo json_encode(['success' => false, 'error' => 'Requested quantity exceeds available stock.']);
        exit;
    }

    // Update the quantity in the cart
    $sql = "UPDATE cart SET product_quantity = ? WHERE guest_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare update statement: " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'Failed to prepare update statement: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("isi", $newQuantity, $guest_id, $productID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quantity updated successfully.']);
    } else {
        error_log("Failed to update quantity: " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'Failed to update quantity: ' . $conn->error]);
    }

    $stmt->close();
}

$conn->close();
?>