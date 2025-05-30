<?php
session_start();
header('Content-Type: application/json');
ob_clean(); // Clear any previous output

// Database connection
require_once __DIR__ . '/../config/database.php';


// Ensure visitorID is set
if (!isset($_COOKIE['visitorID'])) {
    $visitorID = 'visitor-' . bin2hex(random_bytes(5));
    setcookie('visitorID', $visitorID, time() + (86400 * 30), "/", null, true, true); // Secure & HttpOnly flags
    $_COOKIE['visitorID'] = $visitorID; // Set it for the current request
} else {
    $visitorID = $_COOKIE['visitorID'];
}

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);
$productID = $data['productID'] ?? null;
$productName = $data['productName'] ?? null;
$productPrice = $data['productPrice'] ?? null;
$productQuantity = $data['product_quantity'] ?? null;
$productImage = $data['productImage'] ?? '';

// Validate data
if (!$productID || !$productName || !$productPrice || !$productQuantity) {
    echo json_encode(['success' => false, 'error' => 'Invalid product data.']);
    exit;
}

// Check available stock
$stmt = $conn->prepare("SELECT productStock FROM product WHERE productID = ?");
$stmt->bind_param("i", $productID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Product not found.']);
    exit;
}
$row = $result->fetch_assoc();
$availableStock = $row['productStock'];
if ($availableStock === null) {
    echo json_encode(['success' => false, 'error' => 'Stock information unavailable.']);
    exit;
}
if ($productQuantity > $availableStock) {
    echo json_encode(['success' => false, 'error' => 'Only ' . $availableStock . ' items available.']);
    exit;
}

// Check if product exists in cart
$checkStmt = $conn->prepare("SELECT product_quantity FROM cart WHERE guest_id = ? AND product_id = ?");
$checkStmt->bind_param("si", $visitorID, $productID);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity
    $row = $result->fetch_assoc();
    $newQuantity = $row['product_quantity'] + $productQuantity;
    if ($newQuantity > $availableStock) {
        echo json_encode(['success' => false, 'error' => 'Total exceeds available stock. Only ' . $availableStock . ' available.']);
        exit;
    }

    $updateStmt = $conn->prepare("UPDATE cart SET product_quantity = ? WHERE guest_id = ? AND product_id = ?");
    $updateStmt->bind_param("isi", $newQuantity, $visitorID, $productID);
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product quantity updated.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update cart.']);
    }
    $updateStmt->close();
} else {
    // Insert new product to cart
    $insertStmt = $conn->prepare("INSERT INTO cart (product_id, product_name, product_price, product_quantity, product_image, guest_id) VALUES (?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("isdiss", $productID, $productName, $productPrice, $productQuantity, $productImage, $visitorID);
    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product added to cart.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add product to cart.']);
    }
    $insertStmt->close();
}

$stmt->close();
$checkStmt->close();
$conn->close();

error_log("Cart update: $productID - $productName - $visitorID");
?>
