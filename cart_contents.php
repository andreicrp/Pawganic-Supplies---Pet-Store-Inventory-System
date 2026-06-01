<?php
include 'db.php';

$user_id = $_SESSION['user_id'];
$cart = [];

// Check if the user is logged in
if (!isset($user_id)) {
    die("User not logged in.");
}

// Fetch cart items from database
$stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
if (!$stmt) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Execute failed: " . htmlspecialchars($stmt->error));
}

$total = 0;
$cart_items = [];

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $item_total = $row['quantity'] * $row['price'];
    $total += $item_total;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-size: 14px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 960px;
        }
        .cart-header, .cart-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr;
            gap: 10px;
            align-items: center;
            padding: 10px 0;
        }
        .cart-header {
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }
        .cart-item {
            border-bottom: 1px solid #ddd;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item div {
            font-size: 14px;
        }
        .cart-item input {
            font-size: 14px;
            padding: 5px;
        }
        .cart-item button {
            padding: 2px 8px;
            font-size: 12px;
        }
        .total {
            font-size: 16px;
            color:rgb(0, 0, 0);
        }
        @media (max-width: 768px) {
            .cart-header, .cart-item {
                grid-template-columns: 1fr;
                text-align: left;
            }
            .cart-item div:not(:first-child) {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <?php if (empty($cart_items)): ?>
            <div>Your cart is empty.</div>
        <?php else: ?>
            <!-- Cart Headers -->
            <div class="cart-header">
                <div>Product</div>
                <div>Quantity</div>
                <div>Price</div>
                <div>Total</div>
                <div></div>
            </div>

            <?php foreach ($cart_items as $row):
                $item_total = $row['quantity'] * $row['price'];
            ?>
                <div class="cart-item">
                    <div><?= htmlspecialchars($row['name']) ?></div>
                    <div>
                        <input type="number" class="form-control form-control-sm" value="<?= $row['quantity'] ?>" min="1"
                               onchange="updateQuantity(<?= $row['product_id'] ?>, this.value)">
                    </div>
                    <div>₱<?= number_format($row['price'], 2) ?></div>
                    <div>₱<?= number_format($item_total, 2) ?></div>
                    <div>
                        <button class="btn btn-sm btn-danger" onclick="removeFromCart(<?= $row['product_id'] ?>)">×</button>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Total -->
            <div class="text-end fw-bold mt-3 total">
                Total: ₱<?= number_format($total, 2) ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        async function updateQuantity(productId, quantity) {
            const response = await fetch('cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update&product_id=${productId}&quantity=${quantity}`
            });
            const text = await response.text();
            if (text === 'success') {
                window.location.reload(); // Reload page to reflect updated cart
            } else {
                alert('Failed to update quantity. Please try again.');
            }
        }

        async function removeFromCart(productId) {
            const response = await fetch('cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=remove&product_id=${productId}`
            });
            const text = await response.text();
            if (text === 'success') {
                window.location.reload(); // Reload page to reflect updated cart
            } else {
                alert('Failed to remove item. Please try again.');
            }
        }
    </script>
</body>
</html>
