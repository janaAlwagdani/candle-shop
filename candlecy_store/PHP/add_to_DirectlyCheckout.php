<?php
header('Content-Type: application/json');

// Ensure visitorID is set
if (!isset($_COOKIE['visitorID'])) {
    error_log("Visitor ID not found.");
    echo json_encode(['success' => false, 'error' => 'Visitor ID not found.']);
    exit;
}

// Retrieve the visitorID from the cookie
$visitorID = $_COOKIE['visitorID'];

// Database connection
require_once __DIR__ . '/../config/database.php';


// Handle POST request: Add a product to the checkout table
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
        exit;
    }

    // Extract product details
    $productID = intval($data['productID']);
    $productName = $conn->real_escape_string($data['productName']);
    $productPrice = floatval($data['productPrice']);
    $productImage = $conn->real_escape_string($data['productImage']);
    $productQuantity = intval($data['product_quantity']);

    // Check available stock
    $sql = "SELECT productStock FROM product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $availableStock = $row['productStock'];

        if ($availableStock === null) {
            echo json_encode(['success' => false, 'error' => 'Stock information is unavailable for this product.']);
            exit;
        }

        if ($productQuantity > $availableStock) {
            echo json_encode(['success' => false, 'error' => 'Requested quantity exceeds available stock. Only ' . $availableStock . ' items are available.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Product not found in the database.']);
        exit;
    }

    // Insert the product into the checkout table
    $sql = "INSERT INTO checkout (guest_id, productID, productName, productPrice, productImage, productQuantity)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisdsi", $visitorID, $productID, $productName, $productPrice, $productImage, $productQuantity);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to insert product into checkout table: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Handle GET request: Retrieve all products from the checkout table for the current visitor
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['view']) && $_GET['view'] === 'purchases') {
        // Retrieve all products from the purchases table for the current visitor
        $sql = "SELECT product_id, product_name, product_price, product_image, product_quantity 
                FROM purchases WHERE guest_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $visitorID);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        echo json_encode($products);
        $stmt->close();
        $conn->close();
        exit;
    } else {
        // Retrieve all products from the checkout table for the current visitor
        $sql = "SELECT productID, productName, productPrice, productImage, productQuantity 
                FROM checkout WHERE guest_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $visitorID);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        echo json_encode($products);
        $stmt->close();
        $conn->close();
        exit;
    }
}

// Handle DELETE request: Move products to purchases table and remove them from the checkout table
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Insert products from checkout table into purchases table, including the visitorID
        $sqlInsert = "INSERT INTO purchases (guest_id, product_id, product_name, product_price, product_image, product_quantity)
                      SELECT guest_id, productID, productName, productPrice, productImage, productQuantity 
                      FROM checkout WHERE guest_id = ?";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("s", $visitorID);
        if (!$stmtInsert->execute()) {
            throw new Exception('Failed to insert products into purchases table: ' . $stmtInsert->error);
        }

        // Delete products from checkout table for the current visitor
        $sqlDelete = "DELETE FROM checkout WHERE guest_id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param("s", $visitorID);
        if (!$stmtDelete->execute()) {
            throw new Exception('Failed to delete products from checkout table: ' . $stmtDelete->error);
        }

        // Commit the transaction
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    $stmtInsert->close();
    $stmtDelete->close();
    $conn->close();
    exit;
}

// If the request method is not supported
echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
$conn->close();
?>
