<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['method'] ?? '';
$payment_credential = $_POST['credential'] ?? '';
$delivery_location = $_POST['location'] ?? '';
$total = floatval($_POST['total'] ?? 0);
$buy_now = isset($_POST['buy_now']) && $_POST['buy_now'] == '1';

if (!$payment_method || !$payment_credential || !$delivery_location) {
    echo "<script>alert('Please fill in all fields.'); window.location='checkout.php';</script>";
    exit;
}

// ✅ Check user balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['balance'] < $total) {
    echo "<script>alert('Insufficient balance!'); window.location='checkout.php';</script>";
    exit;
}

$items_to_purchase = [];

if ($buy_now) {
    // Buy Now
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($product = $result->fetch_assoc()) {
        if ($product['stock'] < $quantity) {
            echo "<script>alert('Not enough stock.'); window.location='shop.php';</script>";
            exit;
        }

        $items_to_purchase[] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $product['price']
        ];
    }

} else {
    // Full cart
    $stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.price, p.stock 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if ($row['stock'] < $row['quantity']) {
            echo "<script>alert('Not enough stock for one or more items.'); window.location='checkout.php';</script>";
            exit;
        }

        $items_to_purchase[] = [
            'product_id' => $row['product_id'],
            'quantity' => $row['quantity'],
            'price' => $row['price']
        ];
    }
}

// ✅ Deduct balance from user
$stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
$stmt->bind_param("di", $total, $user_id);
$stmt->execute();

// ✅ Process purchases
foreach ($items_to_purchase as $item) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    $price = $item['price'];
    $total_price = $price * $quantity;

    // Deduct stock
    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();

    // Insert into transactions with total_price, payment_credential, and delivery_location
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, product_id, quantity, payment_method, payment_credential, total_price, delivery_location) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissds", $user_id, $product_id, $quantity, $payment_method, $payment_credential, $total_price, $delivery_location);
    $stmt->execute();
}

// ✅ Clear cart if full checkout
if (!$buy_now) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

echo "<script>alert('Purchase successful!'); window.location='shop.php';</script>";
exit;
?>
