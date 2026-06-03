<?php
include 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's purchase history
$stmt = $conn->prepare("
    SELECT t.id, t.product_id, t.quantity, t.payment_method, t.total_price, t.delivery_location, t.transaction_date,
           p.name AS product_name, p.image, p.price
    FROM transactions t
    JOIN products p ON t.product_id = p.id
    WHERE t.user_id = ?
    ORDER BY t.transaction_date DESC
");

if (!$stmt) {
    die("Query error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate statistics
$total_spent = 0;
$total_orders = 0;
$total_items = 0;

$stmt_stats = $conn->prepare("
    SELECT COUNT(*) as total_orders, SUM(total_price) as total_spent, SUM(quantity) as total_items
    FROM transactions
    WHERE user_id = ?
");
$stmt_stats->bind_param("i", $user_id);
$stmt_stats->execute();
$stats_result = $stmt_stats->get_result();
$stats = $stats_result->fetch_assoc();

$total_orders = $stats['total_orders'] ?? 0;
$total_spent = $stats['total_spent'] ?? 0;
$total_items = $stats['total_items'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History - Pawganic Supplies</title>
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

        .header-section {
            text-align: center;
            margin-bottom: 30px;
            padding: 30px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .header-section h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c2416;
            margin-bottom: 8px;
        }

        .header-section p {
            color: #666;
            font-size: 0.95rem;
            margin: 0;
        }

        .container-main {
            max-width: 1100px;
            margin: 0 auto;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            text-align: center;
            transition: all 0.3s ease;
            border-top: 3px solid #8a5d2f;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2rem;
            color: #8a5d2f;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c2416;
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 600;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .empty-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #2c2416;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            font-size: 1.05rem;
            margin-bottom: 30px;
        }

        .btn-shop {
            background: linear-gradient(135deg, #8a5d2f 0%, #6b4723 100%);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-shop:hover {
            background: linear-gradient(135deg, #6b4723 0%, #5a3919 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(138, 93, 47, 0.3);
            color: white;
        }

        /* Order Card */
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border-left: 4px solid #8a5d2f;
        }

        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .order-info-left {
            flex: 1;
        }

        .order-number {
            font-size: 0.85rem;
            color: #666;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .order-date {
            font-size: 0.8rem;
            color: #999;
            margin-top: 2px;
        }

        .order-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* Product Info */
        .product-info {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f0e8d8;
            align-items: flex-start;
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f5f0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid #e0d0b0;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-size: 1rem;
            font-weight: 700;
            color: #2c2416;
            margin-bottom: 4px;
        }

        .product-quantity {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }

        .product-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #8a5d2f;
        }

        /* Order Details */
        .order-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }

        .detail-item {
            background: #f8f5f0;
            padding: 10px;
            border-radius: 8px;
        }

        .detail-label {
            font-size: 0.75rem;
            color: #666;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
        }

        .detail-value {
            font-size: 0.9rem;
            color: #2c2416;
            font-weight: 600;
        }

        /* Delivery Info */
        .delivery-info {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            border-left: 3px solid #27ae60;
            padding: 10px;
            border-radius: 8px;
        }

        .delivery-info i {
            color: #27ae60;
            margin-right: 8px;
            font-size: 0.95rem;
        }

        .delivery-label {
            font-weight: 700;
            color: #229954;
            margin-bottom: 4px;
            font-size: 0.85rem;
        }

        .delivery-address {
            color: #2c2416;
            margin: 0;
            word-break: break-word;
            font-size: 0.9rem;
        }

        /* Section Title */
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c2416;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #8a5d2f;
            display: inline-block;
        }

        /* Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn-back {
            background: white;
            color: #8a5d2f;
            border: 2px solid #ddd;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            border-color: #8a5d2f;
            background: #f8f5f0;
            color: #8a5d2f;
        }

        .btn-back i {
            margin-right: 6px;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }

            .header-section {
                margin-bottom: 20px;
                padding: 20px 15px;
                border-radius: 12px;
            }

            .header-section h1 {
                font-size: 1.5rem;
                margin-bottom: 6px;
            }

            .header-section p {
                font-size: 0.95rem;
            }

            .stats-container {
                gap: 12px;
                margin-bottom: 25px;
            }

            .stat-card {
                padding: 18px;
                border-radius: 10px;
            }

            .stat-icon {
                font-size: 1.8rem;
                margin-bottom: 8px;
            }

            .stat-value {
                font-size: 1.3rem;
                margin-bottom: 4px;
            }

            .stat-label {
                font-size: 0.85rem;
            }

            .section-title {
                font-size: 1.1rem;
                margin-bottom: 15px;
            }

            .order-card {
                padding: 12px;
                margin-bottom: 10px;
                border-radius: 10px;
            }

            .order-header {
                gap: 8px;
                margin-bottom: 10px;
            }

            .order-number {
                font-size: 0.8rem;
            }

            .order-date {
                font-size: 0.75rem;
            }

            .status-badge {
                padding: 3px 8px;
                font-size: 0.7rem;
                border-radius: 12px;
            }

            .product-info {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 8px;
                margin-bottom: 10px;
                padding-bottom: 10px;
            }

            .product-image {
                width: 60px;
                height: 60px;
                border-radius: 6px;
            }

            .product-name {
                font-size: 0.9rem;
                margin-bottom: 3px;
            }

            .product-quantity {
                font-size: 0.8rem;
            }

            .product-price {
                font-size: 1rem;
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

            .delivery-info {
                padding: 10px;
                border-left: 3px solid #27ae60;
                margin-top: 8px;
                border-radius: 6px;
            }

            .delivery-label {
                font-size: 0.8rem;
                margin-bottom: 3px;
            }

            .delivery-address {
                font-size: 0.85rem;
            }

            .action-buttons {
                gap: 8px;
                margin-top: 25px;
                justify-content: center;
            }

            .btn-back {
                padding: 8px 12px;
                font-size: 0.85rem;
                border-radius: 8px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px 10px;
                background: linear-gradient(135deg, #f8f5f0 0%, #f1e6d0 100%);
            }

            .header-section {
                margin-bottom: 15px;
                padding: 15px 10px;
                border-radius: 10px;
            }

            .header-section h1 {
                font-size: 1.2rem;
                margin-bottom: 4px;
            }

            .header-section p {
                font-size: 0.85rem;
            }

            .stats-container {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 8px;
                margin-bottom: 15px;
            }

            .stat-card {
                padding: 12px;
                border-radius: 8px;
                border-top: 2px solid #8a5d2f;
            }

            .stat-icon {
                font-size: 1.5rem;
                margin-bottom: 5px;
            }

            .stat-value {
                font-size: 1.1rem;
                margin-bottom: 2px;
            }

            .stat-label {
                font-size: 0.7rem;
            }

            .container-main {
                max-width: 100%;
                padding: 0;
            }

            .section-title {
                font-size: 1rem;
                margin-bottom: 12px;
                border-bottom: 2px solid #8a5d2f;
            }

            .order-card {
                padding: 10px;
                margin-bottom: 8px;
                border-radius: 8px;
                border-left: 3px solid #8a5d2f;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
                margin-bottom: 8px;
            }

            .order-info-left {
                width: 100%;
            }

            .order-number {
                font-size: 0.75rem;
            }

            .order-date {
                font-size: 0.7rem;
                margin-top: 1px;
            }

            .order-status {
                justify-content: flex-start;
                width: 100%;
            }

            .status-badge {
                padding: 2px 6px;
                font-size: 0.65rem;
                border-radius: 10px;
            }

            .product-info {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 6px;
                margin-bottom: 8px;
                padding-bottom: 8px;
                border-bottom: 1px solid #f0e8d8;
            }

            .product-image {
                width: 50px;
                height: 50px;
                border-radius: 5px;
                border: 1px solid #e0d0b0;
            }

            .product-details {
                width: 100%;
            }

            .product-name {
                font-size: 0.85rem;
                margin-bottom: 2px;
                font-weight: 700;
            }

            .product-quantity {
                font-size: 0.75rem;
                margin-bottom: 2px;
            }

            .product-price {
                font-size: 0.95rem;
                font-weight: 700;
            }

            .order-details-grid {
                grid-template-columns: 1fr;
                gap: 6px;
                margin-bottom: 6px;
            }

            .detail-item {
                padding: 6px;
                border-radius: 5px;
                background: #f8f5f0;
            }

            .detail-label {
                font-size: 0.65rem;
                margin-bottom: 2px;
                text-transform: uppercase;
            }

            .detail-value {
                font-size: 0.8rem;
                font-weight: 600;
            }

            .delivery-info {
                padding: 8px;
                border-left: 2px solid #27ae60;
                margin-top: 6px;
                border-radius: 5px;
                background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            }

            .delivery-info i {
                font-size: 0.85rem;
                margin-right: 6px;
            }

            .delivery-label {
                font-size: 0.75rem;
                margin-bottom: 2px;
                font-weight: 700;
            }

            .delivery-address {
                font-size: 0.8rem;
                word-break: break-word;
            }

            .empty-state {
                padding: 40px 20px;
                border-radius: 12px;
            }

            .empty-icon {
                font-size: 2.5rem;
            }

            .empty-state h3 {
                font-size: 1.1rem;
            }

            .empty-state p {
                font-size: 0.95rem;
            }

            .btn-shop {
                padding: 10px 20px;
                font-size: 0.9rem;
                border-radius: 8px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 6px;
                margin-top: 20px;
                justify-content: center;
            }

            .btn-back {
                padding: 10px;
                font-size: 0.8rem;
                border-radius: 6px;
                width: 100%;
                text-align: center;
            }

            .btn-back i {
                margin-right: 4px;
                font-size: 0.9rem;
            }
        }

        @media print {
            body {
                background: white;
            }
            .action-buttons {
                display: none;
            }
            .header-section {
                box-shadow: none;
                border-bottom: 2px solid #e0d0b0;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header-section">
    <h1><i class="fas fa-history me-2"></i>Purchase History</h1>
    <p>Track your orders and delivery status</p>
</div>

<div class="container-main">

    <!-- Statistics Cards -->
    <?php if ($total_orders > 0): ?>
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-value"><?= $total_orders ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div class="stat-value"><?= $total_items ?></div>
            <div class="stat-label">Items Purchased</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-peso-sign"></i></div>
            <div class="stat-value">₱<?= number_format($total_spent, 2) ?></div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Orders List -->
    <div style="margin-bottom: 20px;">
        <h2 class="section-title"><i class="fas fa-list me-2"></i>Your Orders</h2>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($order = $result->fetch_assoc()): ?>
        <div class="order-card">
            <!-- Order Header -->
            <div class="order-header">
                <div class="order-info-left">
                    <div class="order-number">Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
                    <div class="order-date">
                        <i class="fas fa-calendar-alt me-2"></i><?= date("M d, Y • h:i A", strtotime($order['transaction_date'])) ?>
                    </div>
                </div>
                <div class="order-status">
                    <span class="status-badge">
                        <i class="fas fa-check-circle me-1"></i>Delivered
                    </span>
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <div class="product-image">
                    <?php if (!empty($order['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($order['image']) ?>" alt="<?= htmlspecialchars($order['product_name']) ?>">
                    <?php else: ?>
                        <i class="fas fa-box" style="font-size: 2.5rem; color: #ccc;"></i>
                    <?php endif; ?>
                </div>
                <div class="product-details">
                    <div class="product-name"><?= htmlspecialchars($order['product_name']) ?></div>
                    <div class="product-quantity">Quantity: <strong><?= $order['quantity'] ?></strong></div>
                    <div class="product-quantity">Unit Price: <strong>₱<?= number_format($order['price'], 2) ?></strong></div>
                    <div class="product-price">₱<?= number_format($order['total_price'], 2) ?></div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="order-details-grid">
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-credit-card me-1"></i>Payment Method</div>
                    <div class="detail-value"><?= htmlspecialchars($order['payment_method']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-truck me-1"></i>Delivery Status</div>
                    <div class="detail-value" style="color: #27ae60;">
                        <i class="fas fa-check-circle me-1"></i>Delivered
                    </div>
                </div>
            </div>

            <!-- Delivery Address -->
            <div class="delivery-info">
                <div class="delivery-label">
                    <i class="fas fa-map-marker-alt"></i>Delivery Address
                </div>
                <p class="delivery-address">
                    <?= htmlspecialchars($order['delivery_location']) ?>
                </p>
            </div>
        </div>
        <?php endwhile; ?>

    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h3>No Purchase History Yet</h3>
            <p>You haven't made any purchases yet. Start shopping now and find your favorite pet products!</p>
            <a href="shop.php" class="btn-shop">
                <i class="fas fa-shopping-cart me-2"></i>Start Shopping
            </a>
        </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="action-buttons" style="margin-top: 40px;">
        <a href="shop.php" class="btn-back">
            <i class="fas fa-shopping-bag"></i>Continue Shopping
        </a>
        <a href="main.php" class="btn-back">
            <i class="fas fa-home"></i>Back to Home
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>