<?php

session_start();
header('Content-Type: text/html; charset=UTF-8');

// Database connection
require_once __DIR__ . '/../config/database.php';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../images/products/'; // Adjusted path
    $uploadFile = $uploadDir . basename($_FILES['image']['name']);

    // Check if the directory exists, if not, create it
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'error' => 'Failed to create directory.']);
            exit;
        }
    }

    // Check if the upload directory is writable
    if (!is_writable($uploadDir)) {
        echo json_encode(['success' => false, 'error' => 'Upload directory is not writable.']);
        exit;
    }

    // Attempt to move the uploaded file
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        echo json_encode(['success' => false, 'error' => 'Failed to upload image.']);
        exit;
    }

    $productImage = 'images/products/' . basename($_FILES['image']['name']); // Save the relative path
} else {
    echo json_encode(['success' => false, 'error' => 'Image upload failed.']);
    exit;
}

// Get other product data
$productID = $_POST['id'] ?? null;
$productName = $_POST['name'] ?? null;
$productStock = $_POST['stock'] ?? null;
$productPrice = $_POST['price'] ?? null;
$productDescription = $_POST['description'] ?? null;

// Validate input
if (!$productID || !$productName || !$productStock || !$productPrice || !$productDescription || !$productImage) {
    echo json_encode(['success' => false, 'error' => 'All fields, including Product ID, are required.']);
    exit;
}

// Insert the product into the database
$sql = "INSERT INTO product (productID, productName, productImage, productDescription, productPrice, productStock)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Failed to prepare statement.']);
    exit;
}

$stmt->bind_param("isssdi", $productID, $productName, $productImage, $productDescription, $productPrice, $productStock);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Product added successfully!']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to insert product.']);
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/AdminStyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../images/W_logo.png" alt="Candlecy Logo">
            <h1>Candlecy</h1>
        </div>
        <nav>
            <ul>
                <li><a href="Home.html">Home</a></li>
                <li><a href="Products.html">Products</a></li>
                <li><a href="cart.html">Cart</a></li>
                <li><a href="Admin.html">Admin</a></li>
                <li><a href="Contact.html">Contact</a></li>
                <li><a href="Help.html">Help</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Add Product</h2>
        <form id="addProductForm" enctype="multipart/form-data" method="POST">
            <input type="number" id="productId" name="id" placeholder="Product ID" required>
            <input type="text" id="productName" name="name" placeholder="Product Name" required>
            <input type="number" id="productStock" name="stock" placeholder="Stock" min="0" required>
            <input type="number" id="productPrice" name="price" placeholder="Price" min="0" step="0.01" required>
            <textarea id="productDescription" name="description" placeholder="Product Description" required></textarea>
            <input type="file" id="productImage" name="image" required accept="image/*">
            <button type="submit">Add Product</button>
        </form>
        <button onclick="location.href='admin_dashboard.html'">Back</button>
    </div>

    <script>
        document.getElementById('addProductForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('id', document.getElementById('productId').value);
            formData.append('name', document.getElementById('productName').value.trim());
            formData.append('stock', document.getElementById('productStock').value.trim());
            formData.append('price', document.getElementById('productPrice').value.trim());
            formData.append('description', document.getElementById('productDescription').value.trim());
            formData.append('image', document.getElementById('productImage').files[0]);

            fetch('add_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    window.location.href = 'Products.html'; // Redirect to the products page
                } else {
                    alert('Error: ' + result.error);
                }
            })
            .catch(error => {
                console.error('Error adding product:', error);
                alert('An error occurred while adding the product.');
            });
        });
    </script>

    <footer>
        <p>&copy; 2025 Candlecy, Candle Store. All rights reserved.</p>
        <div class="social-media" style="display: flex; gap: 1px; justify-content: center; flex-wrap: wrap;">
            <a href="https://www.SnapChat.com" target="_blank" style="background-color: white; padding: 1px; border-radius: 8px; display: inline-block;">
                <img src="../images/footer/Snap.png" alt="snap" style="width: 60px; height: auto; inline-size: 1.2cm;">
            </a>
            <a href="https://www.x.com" target="_blank" style="background-color: white; padding: 1px; border-radius: 8px; display: inline-block;">
                <img src="../images/footer/X.png" alt="X" style="width: 60px; height: auto; inline-size: 1.2cm;">
            </a>
            <a href="https://www.instagram.com" target="_blank" style="background-color: white; padding: 1px; border-radius: 8px; display: inline-block;">
                <img src="../images/footer/insta.png" alt="Instagram" style="width: 60px; height: auto; inline-size: 1.2cm;">
            </a>
            <a href="https://www.tiktok.com" target="_blank" style="background-color: white; padding: 1px; border-radius: 8px; display: inline-block;">
                <img src="../images/footer/tiktok.png" alt="TikTok" style="width: 60px; height: auto; inline-size: 1.2cm;">
            </a>
        </div>
    </footer>
</body>
</html>
