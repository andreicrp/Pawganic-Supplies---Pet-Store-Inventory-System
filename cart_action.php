<?php
include 'db.php';

$action = $_POST['action'] ?? '';
$product_id = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? null;
$user_id = $_SESSION['user_id'];

if ($action === 'remove' && $product_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    echo "Product removed from cart.";
}

if ($action === 'update' && $product_id && $quantity) {
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE product_id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $product_id, $user_id);
    $stmt->execute();
    echo "Cart updated.";
}
?>
