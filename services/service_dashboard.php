<?php
// Capture users response
ob_start();
include 'service_users.php';
$usersJson = ob_get_clean();
$users = json_decode($usersJson, true);

// Capture products response
ob_start();
include 'service_products.php';
$productsJson = ob_get_clean();
$products = json_decode($productsJson, true);

// Aggregate and respond
echo json_encode([
    'users' => $users,
    'products' => $products
]);
?>
