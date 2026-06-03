<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_items = [];
$total = 0;

// Check if it's a Buy Now request
if (isset($_POST['buy_now']) && $_POST['buy_now'] == "1" && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['stock'] < $quantity) {
            echo "<script>alert('Not enough stock for Buy Now.'); window.location='shop.php';</script>";
            exit;
        }

        $cart_items[] = [
            'product_id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $quantity,
            'image' => $row['image'],
            'category' => $row['category']
        ];

        $total = $row['price'] * $quantity;
        $buy_now = true;
    } else {
        echo "<script>alert('Product not found.'); window.location='shop.php';</script>";
        exit;
    }
} else {
    $stmt = $conn->prepare("SELECT c.product_id, p.name, p.price, p.image, c.quantity, p.category
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id
                            WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['price'] * $row['quantity'];
    }

    if (empty($cart_items)) {
        echo "<script>alert('Your cart is empty!'); window.location='shop.php';</script>";
        exit;
    }

    $buy_now = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - Pawganic Supplies</title>
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
            margin-bottom: 40px;
            padding-top: 20px;
        }

        .header-section h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c2416;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .header-section p {
            color: #666;
            font-size: 1.05rem;
            margin: 0;
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #999;
            font-size: 0.95rem;
        }

        .step.active {
            color: #8a5d2f;
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f0e8d8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: #999;
        }

        .step.active .step-number {
            background: #8a5d2f;
            color: white;
        }

        .step-divider {
            width: 20px;
            height: 2px;
            background: #ddd;
            margin: 0 5px;
        }

        /* Main Container */
        .checkout-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .checkout-wrapper {
                grid-template-columns: 1fr;
            }
        }

        /* Main Form Section */
        .checkout-form {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        @media (max-width: 768px) {
            .checkout-form {
                padding: 24px;
            }
        }

        .form-section {
            margin-bottom: 40px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c2416;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #8a5d2f;
            font-size: 1.3rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c2416;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: #333;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #8a5d2f;
            box-shadow: 0 0 0 3px rgba(138, 93, 47, 0.1);
        }

        .form-control::placeholder {
            color: #999;
        }

        /* Payment Method Selection */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        @media (max-width: 480px) {
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }

        .payment-option {
            position: relative;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 16px;
            border: 2px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            flex-direction: column;
            text-align: center;
        }

        .payment-option input[type="radio"]:checked + .payment-label {
            border-color: #8a5d2f;
            background: rgba(138, 93, 47, 0.03);
        }

        .payment-label:hover {
            border-color: #c9a86d;
            background: rgba(201, 168, 109, 0.03);
        }

        .payment-icon {
            font-size: 2.2rem;
            color: #8a5d2f;
        }

        .payment-label-text {
            font-weight: 600;
            color: #2c2416;
            font-size: 0.95rem;
        }

        /* Order Summary Sidebar */
        .order-summary-sidebar {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        @media (max-width: 768px) {
            .order-summary-sidebar {
                position: relative;
                top: 0;
            }
        }

        .summary-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c2416;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f0e8d8;
        }

        .summary-item {
            display: flex;
            margin-bottom: 16px;
            gap: 12px;
        }

        .summary-item-image {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            background: #f8f5f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .summary-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .summary-item-details {
            flex: 1;
            min-width: 0;
        }

        .summary-item-name {
            font-weight: 600;
            color: #2c2416;
            font-size: 0.95rem;
            margin-bottom: 4px;
            word-break: break-word;
        }

        .summary-item-price {
            color: #666;
            font-size: 0.9rem;
        }

        .summary-divider {
            height: 1px;
            background: #f0e8d8;
            margin: 20px 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
            color: #666;
        }

        .summary-row.total {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c2416;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 2px solid #f0e8d8;
        }

        /* Buttons */
        .btn-checkout {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 24px;
        }

        .btn-primary-checkout {
            background: linear-gradient(135deg, #8a5d2f 0%, #6b4723 100%);
            color: white;
        }

        .btn-primary-checkout:hover {
            background: linear-gradient(135deg, #6b4723 0%, #5a3919 100%);
            box-shadow: 0 4px 12px rgba(138, 93, 47, 0.3);
            transform: translateY(-2px);
        }

        .btn-secondary-checkout {
            background: white;
            color: #8a5d2f;
            border: 2px solid #ddd;
        }

        .btn-secondary-checkout:hover {
            border-color: #8a5d2f;
            background: #f8f5f0;
        }

        /* Trust Badges */
        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #f0e8d8;
            flex-wrap: wrap;
        }

        .badge-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: #666;
        }

        .badge-icon {
            font-size: 1.1rem;
            color: #8a5d2f;
        }

        /* Address Row */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 16px 0;
        }

        .checkbox-group input[type="checkbox"] {
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: #8a5d2f;
        }

        .checkbox-group label {
            cursor: pointer;
            margin: 0;
            color: #666;
            font-size: 0.95rem;
        }
        
        h2 {
            color: #8d5d34;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0d0b0;
            position: relative;
            display: inline-block;
        }
        
        h2:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60%;
            height: 3px;
            background: linear-gradient(90deg, #a4703f, transparent);
        }
        
        .order-summary {
            background-color: rgba(255, 250, 241, 0.7);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 14px;
            margin-right: 15px;
            border: 3px solid #e0d0b0;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .product-img:hover {
            transform: scale(1.05);
        }
        
        .table {
            border-radius: 16px;
            overflow: hidden;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead {
            background: linear-gradient(135deg, #d3c4a0, #c1b089);
            color: #5e4420;
        }
        
        .table th {
            font-weight: 600;
            padding: 16px 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .table td {
            padding: 16px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e0d0b0;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.8);
            transform: translateY(-2px);
        }
        
        .table tfoot {
            background-color: #fff2d8;
            color: #8d5d34;
            font-weight: bold;
        }
        
        /* Form Section */
        .payment-form {
            background-color: rgba(255, 250, 241, 0.7);
            border-radius: 16px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .form-label {
            color: #8d5d34;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 8px;
            color: #a4703f;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0d0b0;
            border-radius: 12px;
            padding: 14px 18px;
            background-color: rgba(255, 255, 255, 0.8);
            color: #745537;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(211, 196, 160, 0.25);
            border-color: #a4703f;
            background-color: rgba(255, 255, 255, 0.95);
        }
        
        .form-control::placeholder {
            color: #b5a48b;
            font-style: italic;
        }
        
        /* Buttons */
        .btn-secondary {
            background: linear-gradient(135deg, #b9a678, #a4925e);
            border: none;
            color: #fff;
            transition: all 0.3s ease;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #a4925e, #8d5d34);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #a1c181, #8eb36c);
            border: none;
            color: #fff;
            transition: all 0.3s ease;
            border-radius: 12px;
            padding: 12px 28px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #8eb36c, #7a9c58);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        
        /* Product Details */
        .product-name {
            color: #5e4420;
            font-weight: 600;
            font-size: 1.05rem;
        }
        
        .category-badge {
            background-color: #e0d0b0;
            color: #5e4420;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        
        /* Total Section */
        .total-row {
            font-size: 1.25rem;
            color: #8d5d34;
        }
        
        /* Payment Method Icons */
        .payment-icons {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .payment-icon {
            width: 60px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            border-radius: 8px;
            padding: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .payment-icon.active, .payment-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #a4703f;
        }
        
        /* Section Title */

        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }

            .header-section {
                margin-bottom: 30px;
                padding: 25px 15px;
            }

            .header-section h1 {
                font-size: 1.8rem;
            }

            .progress-steps {
                gap: 8px;
                margin-bottom: 30px;
            }

            .step {
                gap: 4px;
                font-size: 0.8rem;
            }

            .step-number {
                width: 28px;
                height: 28px;
                font-size: 0.8rem;
            }

            .step-divider {
                display: none;
            }

            .checkout-wrapper {
                gap: 15px;
            }

            .checkout-form {
                padding: 20px;
            }

            .form-section {
                margin-bottom: 25px;
            }

            .section-title {
                font-size: 1rem;
                margin-bottom: 18px;
                gap: 8px;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-label {
                font-size: 0.9rem;
                margin-bottom: 6px;
            }

            .form-control, .form-select {
                padding: 10px 12px;
                font-size: 0.95rem;
            }

            .payment-methods {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .payment-label {
                padding: 12px;
            }

            .payment-icon {
                font-size: 1.5rem;
            }

            .payment-label-text {
                font-size: 0.85rem;
            }

            .order-summary-sidebar {
                padding: 20px;
                position: static;
                top: auto;
            }

            .summary-title {
                font-size: 1rem;
                margin-bottom: 15px;
            }

            .summary-item {
                gap: 10px;
                margin-bottom: 12px;
            }

            .summary-item-image {
                width: 60px;
                height: 60px;
            }

            .trust-badges {
                gap: 10px;
                margin-top: 15px;
            }

            .badge-item {
                gap: 5px;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px 10px;
            }

            .header-section {
                margin-bottom: 20px;
                padding: 15px 10px;
                border-radius: 12px;
            }

            .header-section h1 {
                font-size: 1.3rem;
                margin-bottom: 5px;
            }

            .header-section p {
                font-size: 0.85rem;
            }

            .progress-steps {
                flex-direction: column;
                gap: 5px;
                margin-bottom: 20px;
            }

            .step {
                font-size: 0.75rem;
                gap: 3px;
                width: 100%;
                justify-content: flex-start;
            }

            .checkout-wrapper {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .checkout-form {
                padding: 15px;
                border-radius: 12px;
            }

            .form-section {
                margin-bottom: 20px;
            }

            .section-title {
                font-size: 0.95rem;
                margin-bottom: 15px;
                padding-bottom: 8px;
                gap: 6px;
            }

            .section-title i {
                font-size: 1rem;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .form-label {
                font-size: 0.85rem;
                margin-bottom: 5px;
            }

            .form-control, .form-select {
                padding: 8px 10px;
                font-size: 0.9rem;
                border-radius: 8px;
            }

            .payment-methods {
                grid-template-columns: 1fr;
                gap: 8px;
                margin-bottom: 15px;
            }

            .payment-option {
                margin-bottom: 0;
            }

            .payment-label {
                flex-direction: column;
                padding: 10px;
                gap: 8px;
            }

            .payment-icon {
                font-size: 1.3rem;
                width: 100%;
                height: auto;
                padding: 6px;
            }

            .payment-label-text {
                font-size: 0.8rem;
            }

            .order-summary-sidebar {
                padding: 15px;
                margin-bottom: 0;
            }

            .summary-title {
                font-size: 0.95rem;
                margin-bottom: 12px;
            }

            .summary-item {
                gap: 8px;
                margin-bottom: 10px;
            }

            .summary-item-image {
                width: 50px;
                height: 50px;
            }

            .summary-item-name {
                font-size: 0.85rem;
            }

            .summary-item-price {
                font-size: 0.8rem;
            }

            .summary-row {
                font-size: 0.85rem;
                margin-bottom: 8px;
            }

            .summary-row.total {
                font-size: 1rem;
                margin-top: 10px;
                padding-top: 10px;
            }

            .btn-checkout {
                padding: 12px;
                font-size: 0.9rem;
                margin-top: 15px;
                width: 100%;
            }

            .trust-badges {
                flex-direction: column;
                gap: 8px;
                margin-top: 10px;
                padding-top: 10px;
            }

            .badge-item {
                gap: 5px;
                font-size: 0.7rem;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- Header Section -->
<div class="header-section">
    <h1><i class="fas fa-lock me-2"></i>Secure Checkout</h1>
    <p>Complete your order securely - Philippine Peso (₱)</p>
</div>

<!-- Progress Steps -->
<div class="progress-steps">
    <div class="step active">
        <div class="step-number">1</div>
        <span>Order Summary</span>
    </div>
    <div class="step-divider"></div>
    <div class="step active">
        <div class="step-number">2</div>
        <span>Payment Info</span>
    </div>
    <div class="step-divider"></div>
    <div class="step">
        <div class="step-number">3</div>
        <span>Confirmation</span>
    </div>
</div>

<!-- Main Checkout Section -->
<div class="checkout-wrapper">
    <!-- Main Form -->
    <form action="process_payment.php" method="POST" class="checkout-form">
        <!-- Delivery Information -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-map-marker-alt"></i>Delivery Information
            </h3>
            
            <div class="form-group">
                <label class="form-label" for="location"><i class="fas fa-home"></i> Full Address</label>
                <input type="text" name="location" id="location" class="form-control" placeholder="Enter your complete delivery address" required>
            </div>
        </div>

        <!-- Payment Method Selection -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-credit-card"></i>Select Payment Method
            </h3>

            <div class="payment-methods">
                <div class="payment-option">
                    <input type="radio" name="method" id="gcash" value="GCash" required onchange="updatePaymentDisplay()">
                    <label for="gcash" class="payment-label">
                        <div class="payment-icon"><i class="fas fa-mobile-alt"></i></div>
                        <div class="payment-label-text">GCash</div>
                    </label>
                </div>

                <div class="payment-option">
                    <input type="radio" name="method" id="paypal" value="PayPal" required onchange="updatePaymentDisplay()">
                    <label for="paypal" class="payment-label">
                        <div class="payment-icon"><i class="fab fa-paypal"></i></div>
                        <div class="payment-label-text">PayPal</div>
                    </label>
                </div>

                <div class="payment-option">
                    <input type="radio" name="method" id="mastercard" value="MasterCard" required onchange="updatePaymentDisplay()">
                    <label for="mastercard" class="payment-label">
                        <div class="payment-icon"><i class="fab fa-cc-mastercard"></i></div>
                        <div class="payment-label-text">MasterCard</div>
                    </label>
                </div>

                <div class="payment-option">
                    <input type="radio" name="method" id="applepay" value="Apple Pay" required onchange="updatePaymentDisplay()">
                    <label for="applepay" class="payment-label">
                        <div class="payment-icon"><i class="fab fa-apple"></i></div>
                        <div class="payment-label-text">Apple Pay</div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Payment Credentials -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-key"></i>Payment Details
            </h3>

            <div class="form-group">
                <label class="form-label" for="credential">
                    <i id="methodIcon" class="fas fa-credit-card"></i> <span id="credentialLabel">Payment Credential</span>
                </label>
                <input type="text" name="credential" id="credential" class="form-control" placeholder="Enter payment credential" required>
                <small style="color: #999; margin-top: 6px; display: block;">Your payment information is encrypted and secure</small>
            </div>
        </div>

        <!-- Hidden Fields -->
        <input type="hidden" name="total" value="<?= $total ?>">
        <input type="hidden" name="buy_now" value="<?= $buy_now ? '1' : '0' ?>">
        <?php if ($buy_now): ?>
            <input type="hidden" name="product_id" value="<?= $cart_items[0]['product_id'] ?>">
            <input type="hidden" name="quantity" value="<?= $cart_items[0]['quantity'] ?>">
        <?php endif; ?>

        <!-- Action Buttons -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 40px;">
            <a href="shop.php" class="btn-checkout btn-secondary-checkout" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-arrow-left me-2"></i>Back to Shop
            </a>
            <button type="submit" class="btn-checkout btn-primary-checkout">
                <i class="fas fa-check-circle me-2"></i>Confirm & Pay ₱<?= number_format($total, 2) ?>
            </button>
        </div>

        <!-- Trust Badges -->
        <div class="trust-badges">
            <div class="badge-item">
                <span class="badge-icon"><i class="fas fa-lock"></i></span>
                <span>Secure Payment</span>
            </div>
            <div class="badge-item">
                <span class="badge-icon"><i class="fas fa-shield-alt"></i></span>
                <span>SSL Encrypted</span>
            </div>
            <div class="badge-item">
                <span class="badge-icon"><i class="fas fa-check-circle"></i></span>
                <span>Verified Checkout</span>
            </div>
        </div>
    </form>

    <!-- Order Summary Sidebar -->
    <div class="order-summary-sidebar">
        <h4 class="summary-title"><i class="fas fa-shopping-bag me-2"></i>Order Summary</h4>

        <?php foreach ($cart_items as $item): ?>
            <div class="summary-item">
                <div class="summary-item-image">
                    <?php if (!empty($item['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <?php else: ?>
                        <i class="fas fa-box" style="font-size: 2rem; color: #ccc;"></i>
                    <?php endif; ?>
                </div>
                <div class="summary-item-details">
                    <div class="summary-item-name"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="summary-item-price">
                        <?= $item['quantity'] ?> × ₱<?= number_format($item['price'], 2) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="summary-divider"></div>

        <?php 
        // Calculate subtotal and tax (for demo purposes)
        $subtotal = $total;
        $tax = $subtotal * 0.12; // 12% VAT
        $grand_total = $subtotal + $tax;
        ?>

        <div class="summary-row">
            <span>Subtotal:</span>
            <span>₱<?= number_format($subtotal, 2) ?></span>
        </div>
        <div class="summary-row">
            <span>VAT (12%):</span>
            <span>₱<?= number_format($tax, 2) ?></span>
        </div>
        <div class="summary-row">
            <span>Shipping:</span>
            <span style="color: #27ae60;">Free</span>
        </div>

        <div class="summary-row total">
            <span>Total Amount:</span>
            <span>₱<?= number_format($grand_total, 2) ?></span>
        </div>

        <div style="background: #f0f8f5; padding: 12px; border-radius: 8px; margin-top: 16px; font-size: 0.9rem; color: #27ae60; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-truck"></i>
            <span><strong>Free Shipping</strong> on all orders</span>
        </div>
    </div>
</div>

<script>
    function updatePaymentDisplay() {
        const method = document.querySelector('input[name="method"]:checked');
        if (!method) return;

        const credentialInput = document.querySelector('input[name="credential"]');
        const credentialLabel = document.querySelector('#credentialLabel');
        const methodIcon = document.getElementById('methodIcon');
        const value = method.value;

        const paymentDetails = {
            'GCash': {
                icon: 'fas fa-mobile-alt',
                placeholder: 'e.g., 0917XXXXXXX',
                label: 'GCash Number'
            },
            'PayPal': {
                icon: 'fab fa-paypal',
                placeholder: 'e.g., yourname@example.com',
                label: 'PayPal Email'
            },
            'MasterCard': {
                icon: 'fab fa-cc-mastercard',
                placeholder: 'e.g., 1234 5678 9012 3456',
                label: 'Card Number'
            },
            'Apple Pay': {
                icon: 'fab fa-apple',
                placeholder: 'e.g., appleid@example.com',
                label: 'Apple ID Email'
            }
        };

        const details = paymentDetails[value];
        if (details) {
            methodIcon.className = details.icon;
            credentialInput.placeholder = details.placeholder;
            credentialLabel.textContent = details.label;
        }
    }

    // Initialize payment display on page load
    document.addEventListener('DOMContentLoaded', function() {
        updatePaymentDisplay();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>