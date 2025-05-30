<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../config/database.php';


// Get JSON data
$productID = $_POST['productID'] ?? null;
$productName = $_POST['productName'] ?? null;
$productStock = $_POST['productStock'] ?? null;
$productPrice = $_POST['productPrice'] ?? null;
$productDescription = $_POST['productDescription'] ?? null;
$productImage = null; // Initialize as null

// Validate required fields
if (!$productID || !$productName || !$productStock || !$productPrice || !$productDescription) {
    echo json_encode(['success' => false, 'error' => 'All fields except productImage are required.']);
    exit;
}

// Handle image upload
if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
    $targetDir = "../images/products/"; // Folder where images will be saved
    $imageFileType = strtolower(pathinfo($_FILES['productImage']['name'], PATHINFO_EXTENSION));
    $targetFile = $targetDir . uniqid() . '.' . $imageFileType;

    // Check if the uploaded file is an actual image
    $check = getimagesize($_FILES['productImage']['tmp_name']);
    if ($check === false) {
        echo json_encode(['success' => false, 'error' => 'Uploaded file is not an image.']);
        exit;
    }

    // Try to move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['productImage']['tmp_name'], $targetFile)) {
        // Successfully uploaded the image
        $productImage = "images/products/" . basename($targetFile); // Store relative path for database
    } else {
        echo json_encode(['success' => false, 'error' => 'Sorry, there was an error uploading your image.']);
        exit;
    }
} else {
    // If no new image is uploaded, keep the existing image
    $sql = "SELECT productImage FROM product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $productImage = $row['productImage'];
    } else {
        echo json_encode(['success' => false, 'error' => 'Product not found.']);
        exit;
    }
    $stmt->close();
}

// Prepare update SQL query
$sql = "UPDATE product 
        SET productName = ?, productStock = ?, productPrice = ?, productDescription = ?, productImage = ?
        WHERE productID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

// Bind parameters (s = string, i = integer, d = double)
$stmt->bind_param("sidssi", $productName, $productStock, $productPrice, $productDescription, $productImage, $productID);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Redirect to admin_dashboard.html after success
        header('Location: ../HTML/admin_dashboard.html');
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Product not found or no changes made.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
