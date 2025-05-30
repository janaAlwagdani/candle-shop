<?php
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../config/database.php';


// Get the product ID from the query string
$productID = intval($_GET['id'] ?? 0);

if ($productID <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Product ID.']);
    exit;
}

// Query the database for the product
$sql = "SELECT productID, productName, productImage, productDescription, productPrice, productStock FROM product WHERE productID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $product]);
} else {
    echo json_encode(['success' => false, 'error' => 'Product not found.']);
}

$stmt->close();
$conn->close();
?>