<?php
session_start();
include 'db.php';

// Routing logic - redirect based on user role
if (isset($_SESSION['user_id'])) {
    // User is logged in, check their role
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && $user['role'] === 'admin') {
        // Admin user - stay on this admin inventory page
    } else {
        // Regular user - redirect to main page
        header("Location: main.php");
        exit;
    }
} else {
    // Not logged in - redirect to login page
    header("Location: main.php");
    exit;
}

// DELETE PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $productId = $_POST['id'];
    if ($productId) {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        header("Location: index.php?status=deleted");
        exit;
    }
}

// UPDATE PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = $_POST['id'];
    $productName = $_POST['name'];
    $category = $_POST['category'];
    $newStock = $_POST['stock'];
    $price = $_POST['price'];
    $expiryDate = $_POST['expiry_date'] ?: null;
    
    // Check if a new image is uploaded
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === 0) {
        $imagePath = 'uploads/' . basename($_FILES['new_image']['name']);
        move_uploaded_file($_FILES['new_image']['tmp_name'], $imagePath);

        $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, stock = ?, price = ?, expiry_date = ?, image = ? WHERE id = ?");
        $imageFileName = $_FILES['new_image']['name'];
        $stmt->bind_param("ssisssi", $productName, $category, $newStock, $price, $expiryDate, $imageFileName, $productId);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, stock = ?, price = ?, expiry_date = ? WHERE id = ?");
        $stmt->bind_param("ssissi", $productName, $category, $newStock, $price, $expiryDate, $productId);
    }

    $stmt->execute();
    header("Location: index.php?status=updated");
    exit;
}

// SEARCH PRODUCTS
$search_query = "";
if (isset($_POST['search'])) {
    $search_query = $_POST['search'];
    $param = "%$search_query%";
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
    $stmt->bind_param("s", $param);
} else {
    $stmt = $conn->prepare("SELECT * FROM products");
}

$stmt->execute();
$result = $stmt->get_result();

// Status Messages
$statusMessage = '';
$statusClass = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'deleted':
            $statusMessage = 'Product successfully deleted!';
            $statusClass = 'alert-success';
            break;
        case 'updated':
            $statusMessage = 'Product successfully updated!';
            $statusClass = 'alert-success';
            break;
        case 'added':
            $statusMessage = 'New product successfully added!';
            $statusClass = 'alert-success';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pet Store Inventory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary-color: #c2b280;
        --primary-light: #d3c4a0;
        --primary-dark: #a69660;
        --secondary-color: #eac285;
        --secondary-light: #f0d5a6;
        --secondary-dark: #d3a45e;
        --accent-color: #553311;
        --text-light: #f8f0e5;
        --text-dark: #4d3319;
        --light-bg: #f9f2e7;
        --card-bg: #ffffff;
        --danger: #e05d5d;
        --warning: #e9b949;
        --success: #7d9b76;
        --border-radius: 16px;
        --shadow: 0 10px 30px rgba(107, 75, 45, 0.08);
        --transition: all 0.3s ease;
    }

    body {
        background: linear-gradient(135deg, #d3c4a0, #f7f2e8);
        font-family: 'Poppins', sans-serif;
        padding: 0;
        margin: 0;
        color: #333;
    }

    .container {
        background-color: #d3c4a0   ;
        border-radius: var(--border-radius);
        padding: 30px;
        margin-top: 30px;
        margin-bottom: 30px;
        box-shadow: var(--shadow);
    }

    .navbar {
        background: linear-gradient(to right, var(--accent-color), var(--primary-dark));
        padding: 1rem 2rem;
        box-shadow: 0 2px 10px rgba(107, 75, 45, 0.2);
    }

    .navbar-brand {
        font-weight: 700;
        color: var(--text-light);
        display: flex;
        align-items: center;
        font-size: 1.4rem;
    }

    .navbar-brand i {
        font-size: 1.5rem;
        margin-right: 10px;
        color: var(--secondary-color);
    }

    .nav-link {
        color: var(--text-light) !important;
        opacity: 0.8;
        transition: var(--transition);
        font-weight: 500;
        margin: 0 5px;                  
        padding: 8px 16px;
        border-radius: 8px;
    }

    .nav-link:hover, .nav-link.active {
        opacity: 1;
        background-color: rgba(255, 255, 255, 0.1);
    }

    .banner {
        background-image: url('images/pet-banner.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
        padding: 100px 20px;
        margin-bottom: 40px;
        position: relative;
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    .banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(107, 75, 45, 0.8), rgba(164, 112, 63, 0.7));
        z-index: 1;
    }

    .banner-content {
        position: relative;
        z-index: 2;
    }

    .banner h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .banner p {
        font-size: 1.2rem;
        max-width: 700px;
        margin: 0 auto;
    }

    .search-container {
        max-width: 700px;
        margin: 30px auto;
        position: relative;
    }

    .search-container .input-group {
        border-radius: 50px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .search-container .form-control {
        border: none;
        padding: 15px 25px;
        font-size: 1rem;
        background-color: var(--light-bg);
        border-radius: 50px 0 0 50px;
    }

    .search-container .btn {
        padding: 0 30px;
        background: linear-gradient(145deg, var(--primary-color), var(--primary-dark));
        border: none;
        color: white;
        border-radius: 0 50px 50px 0;
        font-weight: 500;
    }

    .search-container .btn:hover {
        background: linear-gradient(145deg, var(--primary-dark), var(--primary-color));
    }

    .filter-row {
        margin-bottom: 30px;
    }

    .filter-btn {
        border: 1px solid #e0e0e0;
        background-color: white;
        transition: var(--transition);
        border-radius: 50px;
        padding: 8px 20px;
        margin-right: 10px;
        margin-bottom: 10px;
        font-weight: 500;
        color: #666;
    }

    .filter-btn:hover, .filter-btn.active {
        background: linear-gradient(145deg, var(--primary-color), var(--primary-dark));
        color: white;
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(107, 75, 45, 0.1);
    }

    .product-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
        height: 100%;
        background-color: var(--card-bg);
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(107, 75, 45, 0.15);
    }

    .product-image {
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background-color: #f8f8f8;
        position: relative;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .product-card:hover .product-image img {
        transform: scale(1.05);
    }

    .card-body {
        padding: 1.8rem;
    }

    .price-tag {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(145deg, var(--primary-color), var(--primary-dark));
        color: white;
        font-weight: 600;
        padding: 8px 15px;
        border-radius: 50px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        z-index: 2;
    }

    .card-title {
        font-weight: 600;
        font-size: 1.25rem;
        margin-bottom: 0.8rem;
        color: var(--text-dark);
    }

    .category-badge {
        background-color: var(--light-bg);
        color: var(--primary-dark);
        font-size: 0.8rem;
        padding: 0.4rem 0.9rem;
        border-radius: 50px;
        display: inline-block;
        margin-bottom: 1rem;
        font-weight: 500;
    }

    .product-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1.2rem;
        font-size: 0.9rem;
    }

    .product-meta div {
        display: flex;
        align-items: center;
    }

    .product-meta i {
        margin-right: 8px;
        color: var(--secondary-dark);
    }

    .stock-indicator {
        display: flex;
        align-items: center;
        margin-bottom: 1.2rem;
    }

    .stock-bar {
        height: 8px;
        flex-grow: 1;
        background-color: #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        margin-right: 10px;
    }

    .stock-level {
        height: 100%;
        background-color: var(--success);
        border-radius: 10px;
    }

    .stock-level.low {
        background-color: var(--danger);
    }

    .stock-level.medium {
        background-color: var(--warning);
    }

    .stock-text {
        font-size: 0.9rem;
        white-space: nowrap;
        font-weight: 500;
    }

    .stock-text.low {
        color: var(--danger);
    }

    .stock-text.medium {
        color: var(--warning);
    }

    .stock-text.high {
        color: var(--success);
    }

    .btn-action {
        border-radius: 7    0px;
        padding: 10px 40px;
        font-weight: 500;
        margin-right: 0.5rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-edit {
        background-color: #f5eadd;
        color: var(--primary-dark);
        border: 1px solid #e9d5b9;
        width: 48%;
    }

    .btn-edit:hover {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .btn-delete {
        background-color: #fadcd9;
        color: var(--danger);
        border: 1px solid #f5c0b8;
        width: 48%;
    }

    .btn-delete:hover {
        background-color: var(--danger);
        color: white;
        border-color: var(--danger);
    }

    .btn-add {
        background: linear-gradient(145deg, var(--primary-color), var(--primary-dark));
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 50px;
        font-weight: 600;
        margin-right: 15px;
        transition: var(--transition);
        box-shadow: 0 4px 12px rgba(164, 112, 63, 0.3);
    }

    .btn-add:hover {
        background: linear-gradient(145deg, var(--primary-dark), var(--primary-color));
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(164, 112, 63, 0.4);
    }

    .btn-back {
        background-color: #f5f5f5;
        color: #616161;
        border: none;
        padding: 15px 30px;
        border-radius: 50px;
        font-weight: 600;
        transition: var(--transition);
    }

    .btn-back:hover {
        background-color: #e0e0e0;
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .modal-content {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background-color: var(--light-bg);
        border-bottom: none;
        padding: 1.5rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .modal-title {
        font-weight: 600;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
    }

    .modal-title i {
        margin-right: 10px;
        color: var(--primary-color);
    }

    .modal-body {
        padding: 1.8rem;
    }

    .modal-footer {
        border-top: none;
        padding: 1rem 1.8rem 1.8rem;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.6rem;
        color: var(--text-dark);
        display: block;
    }

    .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(164, 112, 63, 0.15);
        border-color: var(--primary-color);
    }

    .input-group .input-group-text {
        background-color: #f5f5f5;
        border-color: #e0e0e0;
        border-radius: 10px 0 0 10px;
    }

    .status-message {
        margin-bottom: 30px;
        animation: fadeIn 0.6s;
        border-radius: 10px;
        padding: 12px 20px;
        display: flex;
        align-items: center;
    }

    .status-message i {
        margin-right: 10px;
        font-size: 1.2rem;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    footer {
        background: linear-gradient(to right, var(--accent-color), var(--primary-dark));
        color: var(--text-light);
        padding: 60px 0 40px;
        margin-top: 80px;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    footer .container {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        background: transparent;
        box-shadow: none;
    }

    footer .footer-section {
        flex: 1;
        min-width: 250px;
        margin-bottom: 30px;
        padding: 0 15px;
    }

    footer h4 {
        font-size: 1.2rem;
        margin-bottom: 25px;
        position: relative;
        color: var(--secondary-color);
        font-weight: 600;
    }

    footer h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -10px;
        width: 40px;
        height: 3px;
        background-color: var(--secondary-color);
        border-radius: 3px;
    }

    footer p, footer ul {
        margin: 0;
        padding: 0;
        list-style: none;
        color: rgba(255, 255, 255, 0.7);
        line-height: 1.7;
    }

    footer ul li {
        margin-bottom: 12px;
        display: flex;
        align-items: center;
    }

    footer ul li i {
        margin-right: 10px;
        color: var(--secondary-light);
    }

    footer ul li a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: var(--transition);
    }

    footer ul li a:hover {
        color: white;
        padding-left: 5px;
    }

    .copyright {
        background-color: rgba(0, 0, 0, 0.2);
        color: rgba(255, 255, 255, 0.6);
        text-align: center;
        padding: 20px 0;
        font-size: 0.9rem;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background-color: #f9f9f9;
        border-radius: var(--border-radius);
        margin: 40px 0;
    }

    .empty-state i {
        font-size: 5rem;
        color: #ddd;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-weight: 600;
        color: #555;
        margin-bottom: 15px;
    }

    .empty-state p {
        color: #888;
        max-width: 400px;
        margin: 0 auto;
    }

    .card-actions {
        display: flex;
        justify-content: space-between;
    }

    .pagination {
        margin-top: 40px;
        display: flex;
        justify-content: center;
    }

    .pagination .page-item .page-link {
        border: none;
        margin: 0 5px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #555;
        border-radius: 50%;
        transition: var(--transition);
    }

    .pagination .page-item .page-link:hover {
        background-color: var(--light-bg);
        color: var(--primary-dark);
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(145deg, var(--primary-color), var(--primary-dark));
        color: white;
    }

    .pagination .page-item.disabled .page-link {
        color: #ccc;
    }

    /* Image preview in modal */
    .image-preview {
        max-width: 100%;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 20px;
        background-color: #f5f5f5;
        padding: 15px;
        text-align: center;
    }

    .image-preview img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 6px;
    }

    /* Custom floating labels */
    .form-floating {
        position: relative;
        margin-bottom: 20px;
    }

    .form-floating label {
        position: absolute;
        top: 0;
        left: 0;
        padding: 0.75rem 1rem;
        pointer-events: none;
        transition: all 0.25s ease;
        color: #777;
    }

    .form-floating .form-control:focus ~ label,
    .form-floating .form-control:not(:placeholder-shown) ~ label {
        transform: translateY(-60%) translateX(-10%) scale(0.85);
        padding: 0 5px;
        left: 10px;
        background-color: white;
        color: var(--primary-color);
        font-weight: 500;
    }

    .form-floating .form-control {
        height: calc(3.5rem + 2px);
        padding: 1.25rem 1rem;
    }

    .product-count {
        color: #666;
        margin-bottom: 20px;
        font-size: 1rem;
    }

    /* Animation for cards */
    .animate-card {
        animation: fadeInUp 0.5s;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 992px) {
        .banner h1 {
            font-size: 2.5rem;
        }
    }

    @media (max-width: 768px) {
        .banner h1 {
            font-size: 2rem;
        }
        
        .banner p {
            font-size: 1rem;
        }
        
        .container {
            padding: 20px;
        }

        .btn-add, .btn-back {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            margin-right: 0;
        }
    }

    @media (max-width: 576px) {
        .card-actions {
            flex-direction: column;
        }
        
        .btn-action {
            width: 100%;
            margin-bottom: 0.5rem;
            margin-right: 0;
        }

        .product-meta {
            flex-direction: column;
            gap: 8px;
        }
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
    }

    ::-webkit-scrollbar-track {
        background: #f5eadd;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--primary-dark);
    }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="content-wrapper">
        <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="index.php">
            <img src="images/Pawagnic Supplies logo.png" alt="Pawganic Supplies Logo" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Inventory</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add.php">Add Product</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

        <div class="container">
            <!-- Banner Section -->
            <div class="banner">
                <div class="banner-content">
                    <h1>Pet Store Inventory Management</h1>
                    <p>Efficiently manage your pet products, track inventory, and optimize your store's operations</p>
                </div>
            </div>

            <!-- Status Message -->
            <?php if ($statusMessage): ?>
            <div class="alert <?= $statusClass ?> status-message" role="alert">
                <i class="fas fa-info-circle me-2"></i> <?= $statusMessage ?>
            </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <form method="POST" class="search-container">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Search for products..." value="<?= htmlspecialchars($search_query) ?>">
                    <button class="btn" type="submit">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
            </form>

            <!-- Filter Buttons -->
            <div class="row filter-row">
                <div class="col-12 d-flex justify-content-center flex-wrap">
                    <button class="btn filter-btn active me-2 mb-2">All Products</button>
                    <button class="btn filter-btn me-2 mb-2">Food</button>
                    <button class="btn filter-btn me-2 mb-2">Toys</button>
                    <button class="btn filter-btn me-2 mb-2">Accessories</button>
                    <button class="btn filter-btn me-2 mb-2">Medicine</button>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="row">
                <?php while ($row = $result->fetch_assoc()): 
                    // Determine stock level class
                    $stockClass = 'high';
                    if ($row['stock'] <= 5) {
                        $stockClass = 'low';
                    } else if ($row['stock'] <= 15) {
                        $stockClass = 'medium';
                    }
                    
                    // Calculate stock percentage for visual bar
                    $stockPercentage = min(100, ($row['stock'] / 30) * 100);
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card product-card">
                        <div class="product-image">
                            <?php if (!empty($row['image']) && file_exists('uploads/' . $row['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 w-100 bg-light">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <span class="category-badge"><?= htmlspecialchars($row['category']) ?></span>
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            
                            <div class="product-meta">
                                <div>
                                    <i class="fas fa-tag"></i>
                                    <span>₱<?= number_format($row['price'], 2) ?></span>
                                </div>
                                <div>
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?= $row['expiry_date'] ?: 'No Expiry' ?></span>
                                </div>
                            </div>

                            <div class="stock-indicator">
                                <div class="stock-bar">
                                    <div class="stock-level <?= $stockClass ?>" style="width: <?= $stockPercentage ?>%"></div>
                                </div>
                                <span class="stock-text"><?= $row['stock'] ?> in stock</span>
                            </div>
                            
                            <div class="d-flex mt-3 card-actions">
                                <button class="btn btn-edit btn-action" data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>"
                                    data-category="<?= htmlspecialchars($row['category']) ?>" data-stock="<?= $row['stock'] ?>"
                                    data-price="<?= $row['price'] ?>" data-expiry="<?= $row['expiry_date'] ?>"
                                    data-image="<?= htmlspecialchars($row['image'] ?? '') ?>">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>

                                <form action="index.php" method="POST" class="ms-auto" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="delete_product" value="true">
                                    <button type="submit" class="btn btn-delete btn-action">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- No Results Message -->
            <?php if ($result->num_rows === 0): ?>
            <div class="text-center my-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>No products found</h3>
                <p class="text-muted">Try adjusting your search or add new products.</p>
            </div>
            <?php endif; ?>

            <!-- Navigation -->
            <div class="text-center mt-5 mb-5">
                <a href="add.php" class="btn btn-add">
                    <i class="fas fa-plus me-2"></i> Add New Product
                </a>
                <a href="admin.php" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                </a>
            </div>
        </div> <!-- End container -->
    </div> <!-- End content-wrapper -->

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-section">
                <h4>About Us</h4>
                <p>Your trusted pet store inventory management system designed to make pet business operations seamless and efficient.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Inventory</a></li>
                    <li><a href="add.php">Add Product</a></li>
                    <li><a href="admin.php">Dashboard</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact Us</h4>
                <ul>
                    <li><i class="fas fa-envelope me-2"></i> contact@petstore.com</li>
                    <li><i class="fas fa-phone me-2"></i> +1 234 567 8900</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i> 123 Pet Street, Animalia</li>
                </ul>
            </div>
        </div>
    </footer>
    <div class="copyright">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Pet Store Inventory System. All rights reserved.</p>
        </div>
    </div>
</div> <!-- End page-wrapper -->

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="index.php" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i> Edit Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="productId" name="id">
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="productName" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-control" id="productCategory" name="category" required>
                        <option value="Food">Food</option>
                        <option value="Toys">Toys</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Medicine">Medicine</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Price (₱)</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" step="0.01" class="form-control" id="productPrice" name="price" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="newStock" name="stock" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" class="form-control" id="expiryDate" name="expiry_date">
                </div>
                <div class="mb-3">
                    <label class="form-label">Current Image</label>
                    <div id="currentImage" class="mt-2 text-center p-3 bg-light rounded">
                        <!-- Image will be inserted here via JavaScript -->
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload New Image</label>
                    <input type="file" class="form-control" name="new_image" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="update_stock" class="btn btn-primary" style="background-color: var(--primary-color); border-color: var(--primary-dark);">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Fix for the edit modal functionality
document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('productId').value = button.getAttribute('data-id');
    document.getElementById('productName').value = button.getAttribute('data-name');
    
    // Handle category selection in dropdown
    const categorySelect = document.getElementById('productCategory');
    const category = button.getAttribute('data-category');
    for(let i = 0; i < categorySelect.options.length; i++) {
        if(categorySelect.options[i].value === category) {
            categorySelect.selectedIndex = i;
            break;
        }
    }
    
    document.getElementById('productPrice').value = button.getAttribute('data-price');
    document.getElementById('newStock').value = button.getAttribute('data-stock');
    
    // Format date properly for input field
    const expiryDate = button.getAttribute('data-expiry');
    if(expiryDate) {
        document.getElementById('expiryDate').value = expiryDate;
    } else {
        document.getElementById('expiryDate').value = '';
    }

    // Show current image if exists
    const image = button.getAttribute('data-image');
    const currentImageDiv = document.getElementById('currentImage');
    
    if(image && image.trim() !== '') {
        currentImageDiv.innerHTML = `<img src="uploads/${image}" alt="Current Image" style="max-height: 150px; max-width: 100%;" />`;
    } else {
        currentImageDiv.innerHTML = `<div class="text-muted">No image available</div>`;
    }
});

// Category filter functionality
document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', function() {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Here you would add filtering logic based on category
        // For now, we'll just reload the page with a category parameter
        if(this.textContent.trim() !== 'All Products') {
            window.location.href = `index.php?category=${this.textContent.trim()}`;
        } else {
            window.location.href = 'index.php';
        }
    });
});

// Add animation to cards
document.querySelectorAll('.product-card').forEach((card, index) => {
    setTimeout(() => {
        card.classList.add('animate-card');
    }, 100 * index);
});
</script>
</body>
</html>