<?php
// filepath: /Applications/XAMPP/xamppfiles/htdocs/Project2web3/move_to_purchases.php
header('Content-Type: application/json');

try {
    // Connect to the database
    $pdo = new PDO('mysql:host=localhost;dbname=candlecy_store', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Start the transaction
    $pdo->beginTransaction();

    // Check if any product stock will go below zero after the purchase
    $checkStockQuery = "
        SELECT p.productID, p.productStock, c.productQuantity
        FROM product p
        JOIN checkout c ON p.productID = c.productID
        WHERE c.guest_id = :guest_id
    ";
    $stmtCheckStock = $pdo->prepare($checkStockQuery);
    $stmtCheckStock->execute(['guest_id' => $_COOKIE['visitorID']]);

    while ($row = $stmtCheckStock->fetch(PDO::FETCH_ASSOC)) {
        if ($row['productStock'] < $row['productQuantity']) {
            throw new Exception('Not enough stock for product ID: ' . $row['productID']);
        }
    }

    // Insert data into the purchases table
    $insertQuery = "
        INSERT INTO purchases (guest_id, product_id, product_name, product_image, product_description, product_price, purchase_date, product_quantity)
        SELECT 
            guest_id,
            productID AS product_id,
            productName AS product_name,
            productImage AS product_image,
            NULL AS product_description,
            productPrice AS product_price,
            NOW() AS purchase_date,
            productQuantity AS product_quantity
        FROM checkout
        FOR UPDATE
    ";
    $pdo->exec($insertQuery);

    // Update the stock in the products table
    $updateStockQuery = "
        UPDATE product p
        JOIN checkout c ON p.productID = c.productID
        SET p.productStock = p.productStock - c.productQuantity
        WHERE c.guest_id = :guest_id
    ";
    $stmtUpdateStock = $pdo->prepare($updateStockQuery);
    $stmtUpdateStock->execute(['guest_id' => $_COOKIE['visitorID']]);

    // Delete data from the checkout table
    $deleteQuery = "DELETE FROM checkout WHERE guest_id = :guest_id";
    $stmtDelete = $pdo->prepare($deleteQuery);
    $stmtDelete->execute(['guest_id' => $_COOKIE['visitorID']]);

    // Commit the transaction
    $pdo->commit();

    // Return success response
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Roll back the transaction if an error occurs
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Return error response
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
