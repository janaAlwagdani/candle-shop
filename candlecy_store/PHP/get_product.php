<?php
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../config/database.php';


$productID = intval($_GET['id'] ?? 0);

if ($productID <= 0) {
    echo json_encode(['error' => 'Invalid product ID.']);
    exit;
}

$sql = "SELECT productID, productName, productImage, productDescription, productPrice, productStock FROM product WHERE productID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $product['features'] = ['100% Organic: Made with natural waxes like soy and beeswax.', 'Non-Toxic: Free from harmful chemicals and synthetic fragrances.2', 'Eco-Friendly: Biodegradable and sustainable.']; // Example features
    echo json_encode($product);
} else {
    echo json_encode(['error' => 'Product not found.']);
}

$stmt->close();
$conn->close();
?>