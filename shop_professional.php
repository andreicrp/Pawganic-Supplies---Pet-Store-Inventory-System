<?php
include 'db.php';

$user_balance = isset($_SESSION['user_id']) ? $_SESSION['balance'] ?? 0 : 0;

// Get all categories for filter
$categories_stmt = $conn->prepare("SELECT DISTINCT category FROM products");
$categories_stmt->execute();
$categories_result = $categories_stmt->get_result();

$search_query = "";
$sort_by = "default";
$category_filter = "";

// Build query based on filters
$query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

// Apply category filter
if (isset($_POST['category']) && !empty($_POST['category'])) {
    $category_filter = $_POST['category'];
    $query .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

// Apply search
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $search_query = '%' . $_POST['search'] . '%';
    $query .= " AND (name LIKE ? OR category LIKE ?)";
    $params[] = $search_query;
    $params[] = $search_query;
    $types .= "ss";
}

// Apply sorting
if (isset($_POST['sort_by']) && !empty($_POST['sort_by'])) {
    $sort_by = $_POST['sort_by'];
    switch ($sort_by) {
        case "price_low":
            $query .= " ORDER BY price ASC";
            break;
        case "price_high":
            $query .= " ORDER BY price DESC";
            break;
        case "name_asc":
            $query .= " ORDER BY name ASC";
            break;
        case "stock":
            $query .= " ORDER BY stock DESC";
            break;
        default:
            $query .= " ORDER BY id DESC";
    }
} else {
    $query .= " ORDER BY id DESC";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Pawganic Supplies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8a5d2f;
            --primary-light: #c9a86d;
            --primary-dark: #6b4b1d;
            --accent: #85a876;
            --accent-light: #c6deba;
            --bg-light: #f7f2e8;
            --bg-gradient: linear-gradient(135deg, #d3c4a0, #ebf8e1);
            --text-dark: #5a4226;
            --text-light: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg-gradient);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(15px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .navbar-brand img {
            height: 45px;
            width: auto;
        }

        .nav-center {
            flex: 1;
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 0 30px;
        }

        .nav-center a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            padding: 8px 0;
        }

        .nav-center a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--primary-light);
            transition: width 0.3s ease;
        }

        .nav-center a:hover::after,
        .nav-center a.active::after {
            width: 100%;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .balance-display {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            font-size: 24px;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .cart-icon:hover {
            transform: scale(1.1);
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--primary);
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Header Section */
        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .page-header h1 {
            font-size: 48px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 18px;
            color: var(--primary-dark);
            font-weight: 500;
        }

        /* Filter & Search Section */
        .filter-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .filter-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            border: 2px solid #e0d0b0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(201, 168, 109, 0.1);
            outline: none;
        }

        .btn-search {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(138, 93, 47, 0.3);
        }

        .btn-reset {
            background: rgba(211, 196, 160, 0.3);
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-reset:hover {
            background: var(--primary);
            color: white;
        }

        /* Results Info */
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 15px 0;
        }

        .results-count {
            color: var(--primary);
            font-weight: 600;
        }

        /* Product Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            height: 250px;
            background: linear-gradient(135deg, #f7f2e6, #fffdf0);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            max-height: 90%;
            max-width: 90%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .product-badge.out-of-stock {
            background: #ff6b6b;
        }

        .product-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .product-category {
            display: inline-block;
            background: rgba(211, 196, 160, 0.2);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .product-stock {
            font-size: 13px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .product-stock.in-stock {
            color: var(--accent);
        }

        .product-stock.out-of-stock {
            color: #ff6b6b;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .btn-add-cart {
            flex: 1;
            background: rgba(138, 93, 47, 0.1);
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 10px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-add-cart:hover:not(:disabled) {
            background: var(--primary);
            color: white;
        }

        .btn-buy {
            flex: 1;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border: none;
            color: white;
            padding: 10px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-buy:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(138, 93, 47, 0.3);
        }

        .btn-add-cart:disabled,
        .btn-buy:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* No Products Found */
        .no-products {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
        }

        .no-products i {
            font-size: 60px;
            color: rgba(138, 93, 47, 0.2);
            margin-bottom: 20px;
        }

        .no-products h3 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .no-products p {
            color: var(--primary-dark);
            font-size: 16px;
        }

        /* Cart Panel */
        .slide-cart {
            position: fixed;
            top: 0;
            right: -450px;
            width: 450px;
            height: 100vh;
            background: var(--bg-light);
            box-shadow: -8px 0 25px rgba(0, 0, 0, 0.2);
            transition: right 0.4s ease;
            z-index: 1000;
            padding: 30px;
            overflow-y: auto;
            border-left: 5px solid var(--primary);
        }

        .slide-cart.active {
            right: 0;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .cart-header h3 {
            margin: 0;
            color: var(--primary);
            font-weight: 700;
        }

        .close-cart {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--primary);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close-cart:hover {
            color: var(--primary-light);
            transform: scale(1.1);
        }

        .cart-items {
            min-height: 300px;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            align-items: center;
        }

        .cart-item-image {
            width: 60px;
            height: 60px;
            background: rgba(211, 196, 160, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cart-item-image img {
            max-width: 100%;
            max-height: 100%;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .cart-item-price {
            color: var(--primary);
            font-weight: 700;
            font-size: 16px;
        }

        .cart-total {
            border-top: 2px solid var(--primary);
            padding-top: 15px;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            justify-content: space-between;
        }

        .btn-checkout {
            width: 100%;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border: none;
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(133, 168, 118, 0.3);
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white;
            padding: 40px 20px;
            margin-top: 60px;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-section h4 {
            font-weight: 700;
            margin-bottom: 15px;
        }

        .footer-section p {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.9;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
            transform: translateX(5px);
        }

        .footer-copyright {
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            font-size: 14px;
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }

            .filter-row {
                grid-template-columns: 1fr 1fr;
            }

            .slide-cart {
                width: 100%;
                max-width: 400px;
            }
        }

        @media (max-width: 768px) {
            .nav-center {
                display: none;
            }

            .page-header h1 {
                font-size: 32px;
            }

            .filter-row {
                grid-template-columns: 1fr;
            }

            .products-grid {
                grid-template-columns: 1fr 1fr;
            }

            .slide-cart {
                width: 100%;
                right: -100%;
            }
        }

        @media (max-width: 576px) {
            .products-grid {
                grid-template-columns: 1fr;
            }

            .product-actions {
                flex-direction: column;
            }

            .filter-section {
                padding: 20px;
            }
        }

        /* Toast */
        .toast-notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 999;
            animation: slideInUp 0.3s ease;
        }

        .toast-notification.show {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @keyframes slideInUp {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar">
    <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
        <a href="main.php" class="navbar-brand">
            <img src="images/Pawagnic Supplies logo.png" alt="Logo">
            Pawganic
        </a>
        
        <div class="nav-center">
            <a href="main.php">Home</a>
            <a href="shop.php" class="active">Shop</a>
            <a href="about.php">About</a>
        </div>

        <div class="nav-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="balance-display">
                    <i class="fas fa-wallet"></i> ₱<?= number_format($user_balance, 2) ?>
                </div>
                <a href="profile.php" style="color: var(--primary); text-decoration: none; font-weight: 600; cursor: pointer;">
                    <img src="images/profile.jpg" alt="Profile" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                </a>
            <?php else: ?>
                <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Login</a>
            <?php endif; ?>
            
            <div class="cart-icon" onclick="toggleCart()">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" id="cartCount">0</span>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="main-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Our Shop</h1>
        <p>Premium pet supplies for your furry friends</p>
    </div>

    <!-- Filters & Search -->
    <div class="filter-section">
        <div class="filter-title">
            <i class="fas fa-filter"></i> Filter Products
        </div>
        <form method="POST">
            <div class="filter-row">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($_POST['search'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php 
                        $categories_stmt->execute();
                        $categories_result = $categories_stmt->get_result();
                        while ($cat = $categories_result->fetch_assoc()): 
                        ?>
                            <option value="<?= $cat['category'] ?>" <?= ($category_filter === $cat['category']) ? 'selected' : '' ?>>
                                <?= $cat['category'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sort">Sort By</label>
                    <select id="sort" name="sort_by" class="form-select">
                        <option value="default" <?= ($sort_by === 'default') ? 'selected' : '' ?>>Newest</option>
                        <option value="price_low" <?= ($sort_by === 'price_low') ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= ($sort_by === 'price_high') ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name_asc" <?= ($sort_by === 'name_asc') ? 'selected' : '' ?>>Name: A to Z</option>
                        <option value="stock" <?= ($sort_by === 'stock') ? 'selected' : '' ?>>In Stock</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
                <div class="form-group">
                    <a href="shop.php" class="btn-reset">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <span class="results-count">
            <i class="fas fa-box"></i> <?= $result->num_rows ?> products found
        </span>
    </div>

    <!-- Products Grid -->
    <?php if ($result->num_rows > 0): ?>
        <div class="products-grid">
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image']) && file_exists("uploads/" . $product['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <div style="text-align: center; color: rgba(138, 93, 47, 0.3);">
                                <i class="fas fa-image fa-3x"></i>
                                <p style="margin-top: 10px; font-size: 12px;">No Image</p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <span class="product-badge">In Stock</span>
                        <?php else: ?>
                            <span class="product-badge out-of-stock">Out of Stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <span class="product-category"><?= $product['category'] ?></span>
                        
                        <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                        
                        <div class="product-stock <?= $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                            <i class="fas <?= $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                            <?= $product['stock'] > 0 ? $product['stock'] . ' available' : 'Out of stock' ?>
                        </div>

                        <div class="product-actions" style="margin-top: auto;">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button class="btn-add-cart" onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', <?= $product['price'] ?>, 'uploads/<?= htmlspecialchars($product['image']) ?>')" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                                <form method="POST" action="buy_product.php" style="flex: 1;">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn-buy" style="width: 100%;" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                        <i class="fas fa-shopping-bag"></i> Buy Now
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn-add-cart" style="text-decoration: none; text-align: center;">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                                <a href="login.php" class="btn-buy" style="text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-products">
            <i class="fas fa-search"></i>
            <h3>No Products Found</h3>
            <p>Try adjusting your search or filter criteria</p>
        </div>
    <?php endif; ?>
</div>

<!-- Shopping Cart Panel -->
<div class="slide-cart" id="cartPanel">
    <div class="cart-header">
        <h3>
            <i class="fas fa-shopping-bag"></i> Your Cart
        </h3>
        <button class="close-cart" onclick="toggleCart()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="cart-items" id="cartItems">
        <p style="text-align: center; color: var(--primary); padding: 20px;">Your cart is empty</p>
    </div>

    <div class="cart-total">
        <span>Total:</span>
        <span id="cartTotal">₱0.00</span>
    </div>

    <a href="checkout.php" class="btn-checkout">
        <i class="fas fa-check-circle"></i> Proceed to Checkout
    </a>
</div>

<!-- Toast Notification -->
<div id="toast" class="toast-notification">
    <i class="fas fa-check-circle"></i>
    <span id="toastMessage">Product added to cart!</span>
</div>

<!-- Footer -->
<footer>
    <div class="footer-content">
        <div class="footer-section">
            <h4><i class="fas fa-paw"></i> About Us</h4>
            <p>Pawganic Supplies is your trusted source for premium pet products. We're dedicated to providing the best quality items for your beloved pets.</p>
        </div>
        <div class="footer-section">
            <h4>Quick Links</h4>
            <div class="footer-links">
                <a href="main.php">Home</a>
                <a href="shop.php">Shop</a>
                <a href="about.php">About Us</a>
                <a href="login.php">Login</a>
            </div>
        </div>
        <div class="footer-section">
            <h4>Contact Info</h4>
            <p><i class="fas fa-map-marker-alt"></i> 123 Pet Street, Pet City</p>
            <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
            <p><i class="fas fa-envelope"></i> info@pawganic.com</p>
        </div>
    </div>
    <div class="footer-copyright">
        <p>&copy; <?= date('Y') ?> Pawganic Supplies. All rights reserved.</p>
    </div>
</footer>

<script>
    // Cart Management
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    function toggleCart() {
        document.getElementById('cartPanel').classList.toggle('active');
    }

    function addToCart(id, name, price, image) {
        const existingItem = cart.find(item => item.id === id);
        
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({ id, name, price, image, quantity: 1 });
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartDisplay();
        showToast(`${name} added to cart!`);
    }

    function updateCartDisplay() {
        const cartItems = document.getElementById('cartItems');
        const cartCount = document.getElementById('cartCount');
        let total = 0;
        let count = 0;

        if (cart.length === 0) {
            cartItems.innerHTML = '<p style="text-align: center; color: var(--primary); padding: 20px;">Your cart is empty</p>';
            cartCount.textContent = '0';
        } else {
            cartItems.innerHTML = cart.map(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                count += item.quantity;
                return `
                    <div class="cart-item">
                        <div class="cart-item-image">
                            ${item.image ? `<img src="${item.image}" alt="${item.name}">` : '<i class="fas fa-box"></i>'}
                        </div>
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">₱${item.price.toFixed(2)} x ${item.quantity}</div>
                        </div>
                        <button onclick="removeFromCart(${item.id})" style="background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 16px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }).join('');
            cartCount.textContent = count;
        }

        document.getElementById('cartTotal').textContent = '₱' + total.toFixed(2);
    }

    function removeFromCart(id) {
        cart = cart.filter(item => item.id !== id);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartDisplay();
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        document.getElementById('toastMessage').textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // Initialize cart display
    updateCartDisplay();
</script>

</body>
</html>
