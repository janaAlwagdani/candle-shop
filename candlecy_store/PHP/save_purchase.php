<?php
ini_set('log_errors', 1);
ini_set("error_log", "/Applications/XAMPP/xamppfiles/logs/php_error_log");
session_start();
header('Content-Type: application/json');
ob_start(); // Start output buffering

// Database connection
require_once __DIR__ . '/../config/database.php';


// Get the visitor ID from the cookie or create one
if (!isset($_COOKIE['visitorID'])) {
    $visitor_id = 'visitor-' . bin2hex(random_bytes(5));
    setcookie('visitorID', $visitor_id, time() + (86400 * 365), "/", null, true, true); // Secure & HttpOnly flags
} else {
    $visitor_id = $_COOKIE['visitorID'];
}
error_log("Visitor ID: " . $visitor_id);

try {
    $conn->begin_transaction();

    // Fetch data from the checkout table for the visitor
    $fetchSql = "SELECT product_id, product_name, product_image, product_description, product_price FROM checkout WHERE visitor_id = ?";
    $fetchStmt = $conn->prepare($fetchSql);
    if (!$fetchStmt) {
        throw new Exception("Failed to prepare fetch statement: " . $conn->error);
    }
    $fetchStmt->bind_param("s", $visitor_id);
    $fetchStmt->execute();
    $result = $fetchStmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("No items found in the checkout table for this visitor.");
    }

    // Insert data into the purchases table
    $insertSql = "INSERT INTO purchases (visitor_id, product_id, product_name, product_image, product_description, product_price, purchase_date)
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        throw new Exception("Failed to prepare insert statement: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $product_id = $row['product_id'];
        $product_name = $row['product_name'];
        $product_image = $row['product_image'];
        $product_description = $row['product_description'];
        $product_price = floatval($row['product_price']);

        // Validate data before inserting
        if (empty($product_id) || empty($product_name) || empty($product_price)) {
            throw new Exception("Invalid product data: " . json_encode($row));
        }

        error_log("Inserting: " . json_encode([$visitor_id, $product_id, $product_name, $product_image, $product_description, $product_price]));

        $insertStmt->bind_param("sisssd", $visitor_id, $product_id, $product_name, $product_image, $product_description, $product_price);
        if (!$insertStmt->execute()) {
            throw new Exception("Failed to execute insert statement: " . $insertStmt->error);
        }
    }

    // Clear the checkout table for the visitor
    $deleteSql = "DELETE FROM checkout WHERE visitor_id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    if (!$deleteStmt) {
        throw new Exception("Failed to prepare delete statement: " . $conn->error);
    }
    $deleteStmt->bind_param("s", $visitor_id);
    if (!$deleteStmt->execute()) {
        throw new Exception("Failed to execute delete statement: " . $deleteStmt->error);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Purchase saved successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while saving the purchase.']);
} finally {
    $conn->close();
    ob_end_clean(); // Clear the output buffer
}
?>
