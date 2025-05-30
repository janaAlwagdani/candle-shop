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

try {
    $conn->begin_transaction();

    // Fetch all products from the cart and checkout tables
    $fetchSql = "SELECT product_id, product_name, product_price, product_quantity, product_image FROM cart WHERE guest_id = ?
                 UNION
                 SELECT productID AS product_id, productName AS product_name, productPrice AS product_price, productQuantity AS product_quantity, productImage AS product_image FROM checkout WHERE guest_id = ?";
    $fetchStmt = $conn->prepare($fetchSql);
    if (!$fetchStmt) {
        throw new Exception("Failed to prepare fetch query: " . $conn->error);
    }
    $fetchStmt->bind_param("ss", $visitorID, $visitorID);
    $fetchStmt->execute();
    $result = $fetchStmt->get_result();

    // Move products to the purchases table and update product stock
    $insertSql = "INSERT INTO purchases (guest_id, product_id, product_name, product_price, product_quantity, product_image, purchase_date)
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        throw new Exception("Failed to prepare insert query: " . $conn->error);
    }

    $updateStockSql = "UPDATE product SET productStock = productStock - ? WHERE productID = ?";
    $updateStockStmt = $conn->prepare($updateStockSql);
    if (!$updateStockStmt) {
        throw new Exception("Failed to prepare stock update query: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        // Insert into purchases table
        $insertStmt->bind_param(
            "sisdss",
            $visitorID,
            $row['product_id'],
            $row['product_name'],
            $row['product_price'],
            $row['product_quantity'],
            $row['product_image']
        );

        if (!$insertStmt->execute()) {
            throw new Exception("Failed to insert product into purchases: " . $conn->error);
        }

        // Update product stock
        $updateStockStmt->bind_param("ii", $row['product_quantity'], $row['product_id']);
        if (!$updateStockStmt->execute()) {
            throw new Exception("Failed to update product stock: " . $conn->error);
        }
    }

    // Clear the cart and checkout tables
    $deleteCartSql = "DELETE FROM cart WHERE guest_id = ?";
    $deleteCartStmt = $conn->prepare($deleteCartSql);
    if (!$deleteCartStmt) {
        throw new Exception("Failed to prepare cart delete query: " . $conn->error);
    }
    $deleteCartStmt->bind_param("s", $visitorID);
    if (!$deleteCartStmt->execute()) {
        throw new Exception("Failed to clear cart: " . $conn->error);
    }

    $deleteCheckoutSql = "DELETE FROM checkout WHERE guest_id = ?";
    $deleteCheckoutStmt = $conn->prepare($deleteCheckoutSql);
    if (!$deleteCheckoutStmt) {
        throw new Exception("Failed to prepare checkout delete query: " . $conn->error);
    }
    $deleteCheckoutStmt->bind_param("s", $visitorID);
    if (!$deleteCheckoutStmt->execute()) {
        throw new Exception("Failed to clear checkout: " . $conn->error);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Thank you for your purchase!']);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Transaction failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $fetchStmt->close();
    $insertStmt->close();
    $updateStockStmt->close();
    $deleteCartStmt->close();
    $deleteCheckoutStmt->close();
    $conn->close();
}
?>
