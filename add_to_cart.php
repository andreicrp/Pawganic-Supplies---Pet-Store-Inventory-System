<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please login to add to cart.";
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

// Check if product already exists in cart
$check = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Update quantity
    $update = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
    $update->bind_param("ii", $user_id, $product_id);
    $update->execute();
} else {
    // Insert new cart item
    $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
    $insert->bind_param("ii", $user_id, $product_id);
    $insert->execute();
}

echo "Item added to cart!";
