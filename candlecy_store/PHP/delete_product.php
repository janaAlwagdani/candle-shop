<?php
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../config/database.php';


// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);
$productID = $data['productID'] ?? null;

// Validate productID
if (!$productID || !is_numeric($productID)) {
    echo json_encode(['success' => false, 'error' => 'Invalid Product ID.']);
    exit;
}

// Delete the product from the database
$sql = "DELETE FROM product WHERE productID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $productID);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Product not found.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete product: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>