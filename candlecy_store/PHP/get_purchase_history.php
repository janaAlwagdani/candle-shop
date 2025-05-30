<?php
header('Content-Type: application/json');
session_start();

// Database connection
require_once __DIR__ . '/../config/database.php';


// Ensure guest_id is set
if (!isset($_SESSION['guest_id'])) {
    echo json_encode(['success' => false, 'error' => 'Guest ID not found.']);
    exit;
}

$guestID = $_SESSION['guest_id'];

// Fetch purchase history for the current guest
$sql = "SELECT productID, productName, productPrice, productImage, productQuantity, purchaseDate 
        FROM purchases WHERE guest_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $guestID);
$stmt->execute();
$result = $stmt->get_result();

$purchases = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $purchases[] = $row;
    }
}

echo json_encode(['success' => true, 'purchases' => $purchases]);

$stmt->close();
$conn->close();
?>