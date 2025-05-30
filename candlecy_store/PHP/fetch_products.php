
<?php
//fetch_products.php
// filepath: /Applications/XAMPP/xamppfiles/htdocs/Project 2web 2/PHP/fetch_products.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';


$sql = "SELECT * FROM product";
$result = $conn->query($sql);

if (!$result) {
    die(json_encode(['error' => 'Query failed: ' . $conn->error]));
}

$essential = [];
$winter = [];

while ($row = $result->fetch_assoc()) {
    if (strlen($row['productID']) == 1) {
        $essential[] = $row;
    } elseif (strlen($row['productID']) == 2) {
        $winter[] = $row;
    }
}

echo json_encode(['essential' => $essential, 'winter' => $winter]);

$conn->close();
?>
