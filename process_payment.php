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
$subtotal = floatval($_POST['total'] ?? 0);
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

// Calculate tax and total
$vat_rate = 0.12;
$vat_amount = $subtotal * $vat_rate;
$total_with_tax = $subtotal + $vat_amount;

if (!$user || $user['balance'] < $total_with_tax) {
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
            'price' => $product['price'],
            'name' => $product['name'],
            'image' => $product['image']
        ];
    }

} else {
    // Full cart
    $stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.price, p.stock, p.name, p.image
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
            'price' => $row['price'],
            'name' => $row['name'],
            'image' => $row['image']
        ];
    }
}

// ✅ Deduct balance from user
$stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
$stmt->bind_param("di", $total_with_tax, $user_id);
$stmt->execute();

// ✅ Store order ID for display
$order_ids = [];

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
    
    $order_ids[] = $conn->insert_id;
}

// ✅ Clear cart if full checkout
if (!$buy_now) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

// ✅ Get the first order ID for display
$order_id = $order_ids[0] ?? 0;

// Generate order number (using transaction ID with padding)
$order_number = str_pad($order_id, 6, '0', STR_PAD_LEFT);

// Get current date and time
$order_date = date('M d, Y');
$order_time = date('h:i A');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Pawganic Supplies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f8f5f0 0%, #f1e6d0 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            padding: 30px 20px;
            min-height: 100vh;
            color: #333;
        }

        .confirmation-container {
            max-width: 850px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .confirmation-header {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
        }

        .success-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            animation: scaleIn 0.6s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .confirmation-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .confirmation-header p {
            font-size: 1.1rem;
            opacity: 0.95;
            margin: 0;
        }

        .confirmation-body {
            padding: 40px 30px;
        }

        .info-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0e8d8;
        }

        .info-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-item {
            background: #f8f5f0;
            padding: 15px;
            border-radius: 10px;
        }

        .info-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.1rem;
            color: #2c2416;
            font-weight: 600;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c2416;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #8a5d2f;
            display: inline-block;
        }

        .order-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f5f0;
            border-radius: 10px;
        }

        .order-item-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 2px solid #e0d0b0;
        }

        .order-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .order-item-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .order-item-name {
            font-weight: 600;
            color: #2c2416;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .order-item-quantity {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .order-item-price {
            font-weight: 700;
            color: #8a5d2f;
            font-size: 1.1rem;
        }

        .pricing-table {
            background: #f8f5f0;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .pricing-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            color: #666;
        }

        .pricing-row.total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c2416;
            border-top: 2px solid #e0d0b0;
            padding-top: 12px;
            margin-bottom: 0;
        }

        .info-highlight {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            border-left: 4px solid #27ae60;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .info-highlight h4 {
            color: #229954;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .info-highlight ul {
            margin: 0;
            padding-left: 20px;
            color: #555;
        }

        .info-highlight li {
            margin-bottom: 6px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-action {
            flex: 1;
            min-width: 160px;
            padding: 14px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary-action {
            background: linear-gradient(135deg, #8a5d2f 0%, #6b4723 100%);
            color: white;
        }

        .btn-primary-action:hover {
            background: linear-gradient(135deg, #6b4723 0%, #5a3919 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(138, 93, 47, 0.3);
        }

        .btn-secondary-action {
            background: white;
            color: #8a5d2f;
            border: 2px solid #8a5d2f;
        }

        .btn-secondary-action:hover {
            background: #f8f5f0;
            transform: translateY(-2px);
        }

        .btn-print {
            background: #f0e8d8;
            color: #8a5d2f;
            border: 2px solid #ddd;
        }

        .btn-print:hover {
            background: white;
        }

        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #f0e8d8;
            flex-wrap: wrap;
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
        }

        .badge i {
            font-size: 1.3rem;
            color: #27ae60;
        }

        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            .confirmation-container {
                margin: 0 10px;
                border-radius: 15px;
            }

            .confirmation-header {
                padding: 35px 20px;
            }

            .confirmation-header h1 {
                font-size: 1.6rem;
            }

            .confirmation-header p {
                font-size: 1rem;
            }

            .success-icon {
                font-size: 2.5rem;
                margin-bottom: 15px;
            }

            .confirmation-body {
                padding: 25px 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .info-item {
                padding: 12px;
            }

            .section-title {
                font-size: 1.1rem;
            }

            .product-info {
                gap: 12px;
                margin-bottom: 15px;
                padding-bottom: 15px;
            }

            .product-image {
                width: 70px;
                height: 70px;
            }

            .product-name {
                font-size: 0.95rem;
            }

            .product-price {
                font-size: 1rem;
            }

            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .btn-action {
                min-width: 100%;
                padding: 12px;
                font-size: 0.95rem;
            }

            .trust-badges {
                flex-direction: column;
                gap: 12px;
            }

            .delivery-info {
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px 10px;
            }

            .confirmation-container {
                margin: 0;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }

            .confirmation-header {
                padding: 25px 15px;
                border-radius: 12px 12px 0 0;
            }

            .confirmation-header h1 {
                font-size: 1.3rem;
                margin-bottom: 8px;
            }

            .confirmation-header p {
                font-size: 0.9rem;
            }

            .success-icon {
                font-size: 2rem;
                margin-bottom: 10px;
            }

            .confirmation-body {
                padding: 15px;
            }

            .info-section {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .info-item {
                padding: 10px;
                border-radius: 8px;
            }

            .info-label {
                font-size: 0.7rem;
                margin-bottom: 4px;
            }

            .info-value {
                font-size: 0.95rem;
            }

            .section-title {
                font-size: 1rem;
                margin-bottom: 15px;
                padding-bottom: 10px;
            }

            .product-info {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 10px;
                margin-bottom: 12px;
                padding-bottom: 12px;
            }

            .product-image {
                width: 60px;
                height: 60px;
                border: 1px solid #e0d0b0;
            }

            .product-name {
                font-size: 0.9rem;
                margin-bottom: 4px;
            }

            .product-quantity {
                font-size: 0.8rem;
                margin-bottom: 4px;
            }

            .product-price {
                font-size: 0.95rem;
            }

            .order-details-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .detail-item {
                padding: 8px;
                border-radius: 6px;
            }

            .detail-label {
                font-size: 0.7rem;
            }

            .detail-value {
                font-size: 0.85rem;
            }

            .pricing-table {
                padding: 12px;
                margin: 12px 0;
            }

            .pricing-row {
                font-size: 0.85rem;
                margin-bottom: 8px;
            }

            .pricing-row.total {
                font-size: 0.95rem;
                margin-bottom: 0;
            }

            .delivery-info {
                padding: 10px;
                border-left: 3px solid #27ae60;
            }

            .delivery-label {
                font-size: 0.8rem;
                margin-bottom: 4px;
            }

            .delivery-address {
                font-size: 0.85rem;
            }

            .action-buttons {
                flex-direction: column;
                gap: 8px;
                margin-top: 20px;
            }

            .btn-action {
                min-width: 100%;
                padding: 10px;
                font-size: 0.85rem;
                gap: 8px;
            }

            .btn-action i {
                display: none;
            }

            .trust-badges {
                flex-direction: column;
                gap: 8px;
                margin-top: 15px;
            }

            .badge {
                font-size: 0.8rem;
                justify-content: center;
            }
        }

        @media print {
            body {
                background: white;
            }
            .confirmation-container {
                box-shadow: none;
            }
            .action-buttons {
                display: none;
            }
            .trust-badges {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="confirmation-container">
    <!-- Header -->
    <div class="confirmation-header">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Order Confirmed!</h1>
        <p>Your order has been successfully placed and payment processed</p>
    </div>

    <!-- Body -->
    <div class="confirmation-body">
        <!-- Order Details -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Order Number</div>
                    <div class="info-value">#<?= $order_number ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Order Date</div>
                    <div class="info-value"><?= $order_date ?> • <?= $order_time ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value"><?= htmlspecialchars($payment_method) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Paid</div>
                    <div class="info-value" style="color: #27ae60;">₱<?= number_format($total_with_tax, 2) ?></div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Items Ordered</div>
                <div class="info-value"><?= count($items_to_purchase) ?> <?= count($items_to_purchase) > 1 ? 'items' : 'item' ?></div>
            </div>
        </div>

        <!-- Items Ordered -->
        <div class="info-section">
            <div class="section-title">
                <i class="fas fa-shopping-bag me-2"></i>Items Ordered
            </div>

            <?php foreach ($items_to_purchase as $item): ?>
            <div class="order-item">
                <div class="order-item-image">
                    <?php if (!empty($item['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <?php else: ?>
                        <i class="fas fa-box" style="font-size: 2rem; color: #ccc;"></i>
                    <?php endif; ?>
                </div>
                <div class="order-item-details">
                    <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="order-item-quantity">Quantity: <?= $item['quantity'] ?></div>
                    <div class="order-item-price">₱<?= number_format($item['price'], 2) ?></div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Pricing Breakdown -->
            <div class="pricing-table">
                <div class="pricing-row">
                    <span>Subtotal:</span>
                    <span>₱<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="pricing-row">
                    <span>VAT (12%):</span>
                    <span>₱<?= number_format($vat_amount, 2) ?></span>
                </div>
                <div class="pricing-row">
                    <span>Shipping:</span>
                    <span style="color: #27ae60;">FREE</span>
                </div>
                <div class="pricing-row total">
                    <span>Total Paid:</span>
                    <span>₱<?= number_format($total_with_tax, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Delivery Address -->
        <div class="info-section">
            <div class="section-title">
                <i class="fas fa-map-marker-alt me-2"></i>Delivery Address
            </div>
            <div class="info-item">
                <div class="info-label">Address</div>
                <div class="info-value"><?= htmlspecialchars($delivery_location) ?></div>
            </div>
            <div class="info-highlight">
                <i class="fas fa-truck" style="margin-right: 10px; color: #229954;"></i>
                <strong>Expected delivery: 3-5 business days</strong>
            </div>
        </div>

        <!-- What's Next -->
        <div class="info-section">
            <div class="section-title">
                <i class="fas fa-arrow-right me-2"></i>What's Next?
            </div>
            <div class="info-highlight">
                <h4><i class="fas fa-tasks me-2"></i>Order Process</h4>
                <ul>
                    <li>We'll process your order immediately</li>
                    <li>You'll receive a tracking number via email</li>
                    <li>Monitor your delivery status in Purchase History</li>
                    <li>Contact us if you have any questions</li>
                </ul>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="purchase_history.php" class="btn-action btn-primary-action">
                <i class="fas fa-history"></i>View Purchase History
            </a>
            <a href="shop.php" class="btn-action btn-secondary-action">
                <i class="fas fa-shopping-bag"></i>Continue Shopping
            </a>
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fas fa-print"></i>Print Receipt
            </button>
        </div>

        <!-- Trust Badges -->
        <div class="trust-badges">
            <div class="badge">
                <i class="fas fa-lock"></i>
                <span>Secure Transaction</span>
            </div>
            <div class="badge">
                <i class="fas fa-truck"></i>
                <span>Fast Delivery</span>
            </div>
            <div class="badge">
                <i class="fas fa-undo"></i>
                <span>Easy Returns</span>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0e8d8; color: #666;">
            <p><strong>Thank you for choosing Pawganic Supplies!</strong></p>
            <p style="margin: 0; font-size: 0.9rem;">For support, visit our website or contact customer service</p>
        </div>
    </div>
</div>

</body>
</html>
