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

// Fetch purchases for the current visitor
$sql = "SELECT product_name, product_price, product_image FROM purchases WHERE guest_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Failed to prepare query: " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare query.']);
    exit;
}

$stmt->bind_param("s", $visitorID);
$stmt->execute();
$result = $stmt->get_result();

$purchases = [];
while ($row = $result->fetch_assoc()) {
    $purchases[] = $row;
}

echo json_encode(['success' => true, 'data' => $purchases]);

$stmt->close();
$conn->close();
?>
