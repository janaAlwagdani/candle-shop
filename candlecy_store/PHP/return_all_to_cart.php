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

    // Fetch all products from the checkout table
    $fetchSql = "SELECT productID, productName, productPrice, productQuantity, productImage FROM checkout WHERE guest_id = ?";
    $fetchStmt = $conn->prepare($fetchSql);
    if (!$fetchStmt) {
        throw new Exception("Failed to prepare fetch query: " . $conn->error);
    }
    $fetchStmt->bind_param("s", $visitorID);
    $fetchStmt->execute();
    $result = $fetchStmt->get_result();

    // Move products back to the cart
    $insertSql = "INSERT INTO cart (product_id, product_name, product_price, product_quantity, product_image, guest_id)
                  VALUES (?, ?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE product_quantity = product_quantity + VALUES(product_quantity)";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        throw new Exception("Failed to prepare insert query: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $insertStmt->bind_param(
            "isdiss",
            $row['productID'],
            $row['productName'],
            $row['productPrice'],
            $row['productQuantity'],
            $row['productImage'],
            $visitorID
        );

        if (!$insertStmt->execute()) {
            throw new Exception("Failed to insert product into cart: " . $conn->error);
        }
    }

    // Clear the checkout table
    $deleteSql = "DELETE FROM checkout WHERE guest_id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    if (!$deleteStmt) {
        throw new Exception("Failed to prepare delete query: " . $conn->error);
    }
    $deleteStmt->bind_param("s", $visitorID);
    if (!$deleteStmt->execute()) {
        throw new Exception("Failed to clear checkout table: " . $conn->error);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Products returned to cart successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Transaction failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $fetchStmt->close();
    $insertStmt->close();
    $deleteStmt->close();
    $conn->close();
}
?>