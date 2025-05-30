<?php
header('Content-Type: application/json');

// Ensure visitorID is set
if (!isset($_COOKIE['visitorID'])) {
    $visitorID = 'visitor-' . bin2hex(random_bytes(5));
    setcookie('visitorID', $visitorID, time() + (86400 * 30), "/", null, true, true); // Secure & HttpOnly flags
    $_COOKIE['visitorID'] = $visitorID; // Set it for the current request
} else {
    $visitorID = $_COOKIE['visitorID'];
}

// Database connection
require_once __DIR__ . '/../config/database.php';


// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
        exit;
    }

    // Extract product details
    $productID = intval($data['product_id']);
    $productName = $conn->real_escape_string($data['product_name']);
    $productPrice = floatval($data['product_price']);
    $productQuantity = intval($data['product_quantity']);
    $productImage = $conn->real_escape_string($data['product_image']);

    // Check if the product already exists in the cart for the current visitor
    $checkSql = "SELECT id, product_quantity FROM cart WHERE product_id = ? AND guest_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("is", $productID, $visitorID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // If the product exists, update the quantity
        $row = $result->fetch_assoc();
        $newQuantity = $row['product_quantity'] + $productQuantity;

        $updateSql = "UPDATE cart SET product_quantity = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $newQuantity, $row['id']);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product quantity updated in cart.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update product quantity: ' . $conn->error]);
        }

        $updateStmt->close();
    } else {
        // If the product does not exist, insert it into the cart
        $insertSql = "INSERT INTO cart (product_id, product_name, product_price, product_quantity, product_image, guest_id)
                      VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("isdis", $productID, $productName, $productPrice, $productQuantity, $productImage, $visitorID);

        if ($insertStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product added to cart.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to insert product into cart: ' . $conn->error]);
        }

        $insertStmt->close();
    }

    $checkStmt->close();
    $conn->close();
    exit;
}

// If the request method is not supported
echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
$conn->close();
?>