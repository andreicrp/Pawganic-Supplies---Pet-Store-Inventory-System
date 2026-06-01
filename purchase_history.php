<?php
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle Excel export
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    // Fetch all transaction data
    $stmt = $conn->prepare("
        SELECT t.*, p.name AS product_name, u.username 
        FROM transactions t
        JOIN products p ON t.product_id = p.id
        JOIN users u ON t.user_id = u.id
        ORDER BY t.transaction_date DESC
    ");
    
    if (!$stmt) {
        die("Query error: " . $conn->error);
    }
    
    $stmt->execute();
    $export_result = $stmt->get_result();
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="purchase_history_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create Excel content
    echo "
        <table border='1'>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Payment Method</th>
                    <th>Credential</th>
                    <th>Delivery Location</th>
                    <th>Total Price</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>";
            
    while ($row = $export_result->fetch_assoc()) {
        echo "<tr>
            <td>" . htmlspecialchars($row['username']) . "</td>
            <td>" . htmlspecialchars($row['product_name']) . "</td>
            <td>" . $row['quantity'] . "</td>
            <td>" . htmlspecialchars($row['payment_method']) . "</td>
            <td>" . htmlspecialchars($row['payment_credential']) . "</td>
            <td>" . htmlspecialchars($row['delivery_location']) . "</td>
            <td>₱" . number_format($row['total_price'], 2) . "</td>
            <td>" . date("M d, Y H:i", strtotime($row['transaction_date'])) . "</td>
        </tr>";
    }
    
    echo "</tbody></table>";
    exit;
}

if (isset($_GET['clear']) && $_GET['clear'] === '1') {
    $conn->query("DELETE FROM transactions");
    header("Location: " . $_SERVER['PHP_SELF'] . "?cleared=1");
    exit;
}

$stmt = $conn->prepare("
    SELECT t.*, p.name AS product_name, u.username 
    FROM transactions t
    JOIN products p ON t.product_id = p.id
    JOIN users u ON t.user_id = u.id
    ORDER BY t.transaction_date DESC
");

if (!$stmt) {
    die("Query error: " . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Purchase History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #d3c4a0, #ebf8e1);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .page-header {
            background: linear-gradient(90deg, #a4703f, #eac285);
            color: #fff;
            padding: 20px 30px;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #fffaf1, #f5ecd6);
            color: #a4703f;
            border: 2px solid #d3c4a0;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }
        
        .back-btn:hover {
            background: linear-gradient(135deg, #d3c4a0, #e0d0b0);
            color: #7d5a2a;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }
        
        .table-container {
            background: linear-gradient(135deg, #fffaf1, #f5ecd6);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .table-responsive {
            max-height: 520px;
            overflow-y: auto;
            border-radius: 12px;
            scrollbar-width: thin;
            scrollbar-color: #d3c4a0 transparent;
        }
        
        .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #d3c4a0;
            border-radius: 10px;
        }
        
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 0;
        }
        
        .table th {
            background: linear-gradient(135deg, #a4703f, #d3c4a0);
            color: #fff;
            font-weight: 600;
            padding: 15px 12px;
            position: sticky;
            top: 0;
            z-index: 1;
            text-align: center;
            border: none;
        }
        
        .table td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e0d0b0;
            text-align: center;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #fffaf1, #f5ecd6);
            border-radius: 12px;
            padding: 20px;
            flex: 1;
            min-width: 250px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            font-size: 2rem;
            color: #a4703f;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #7d5a2a;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #a4703f;
            font-weight: 500;
        }
        
        .badge {
            font-size: 0.85em;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 10px;
        }
        
        .payment-badge {
            background: linear-gradient(135deg, #a4703f, #eac285);
            color: #fff;
        }
        
        .clear-btn {
            background: linear-gradient(135deg, #e28743, #ffc285);
            border: none;
            color: #fff;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .clear-btn:hover {
            background: linear-gradient(135deg, #d17838, #edb279);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .alert {
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #a1c181, #ebf8e1);
            color: #3e5f22;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #b9a678, #f5ecd6);
            color: #7d5a2a;
        }
        
        .price-value {
            color: #a4703f;
            font-weight: 700;
        }
        
        .section-title {
            color: #7d5a2a;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .action-btn {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .export-btn {
            background: linear-gradient(135deg, #b9a678, #d3c4a0);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .export-btn:hover {
            background: linear-gradient(135deg, #a4703f, #b9a678);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>

<div class="page-header d-flex justify-content-between align-items-center">
    <h2><i class="bi bi-bag-check"></i> Purchase History</h2>
    <a href="admin.php" class="btn back-btn"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="container">

    <?php if (isset($_GET['cleared'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> All transaction history cleared successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="stats-container">
        <?php 
        // Calculate total sales amount
        $total_sales = 0;
        $methods = [];
        $total_items = 0;
        $most_used_method = 'None'; // Initialize with default value
        
        if ($result->num_rows > 0) {
            // Save the current result pointer
            $temp_result = $result;
            
            while ($row = $temp_result->fetch_assoc()) {
                $total_sales += $row['total_price'];
                $methods[$row['payment_method']] = isset($methods[$row['payment_method']]) ? $methods[$row['payment_method']] + 1 : 1;
                $total_items += $row['quantity'];
            }
            
            // Find most used payment method
            if (!empty($methods)) {
                $most_used_method = array_search(max($methods), $methods);
            }
            
            // Reset the result pointer
            $result->data_seek(0);
        }
        ?>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div class="stat-value"><?= $result->num_rows ?></div>
            <div class="stat-label">Total Transactions</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">₱<?= number_format($total_sales, 2) ?></div>
            <div class="stat-label">Total Sales</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-value"><?= $total_items ?></div>
            <div class="stat-label">Items Sold</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-credit-card"></i></div>
            <div class="stat-value"><?= htmlspecialchars($most_used_method) ?></div>
            <div class="stat-label">Top Payment Method</div>
        </div>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="section-title"><i class="bi bi-table"></i> Transaction Records</h5>
                <div class="d-flex gap-2">
                    <a href="?export=excel" class="btn export-btn"><i class="bi bi-file-earmark-excel"></i> Export to Excel</a>
                    <a href="?clear=1" class="btn clear-btn" onclick="return confirm('Are you sure you want to clear all transaction history? This action cannot be undone.')">
                        <i class="bi bi-trash"></i> Clear All History
                    </a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Payment Method</th>
                            <th>Credential</th>
                            <th>Delivery Location</th>
                            <th>Total Price</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><span class="badge payment-badge"><?= htmlspecialchars($row['payment_method']) ?></span></td>
                                <td><?= htmlspecialchars($row['payment_credential']) ?></td>
                                <td><?= htmlspecialchars($row['delivery_location']) ?></td>
                                <td class="price-value">₱<?= number_format($row['total_price'], 2) ?></td>
                                <td><?= date("M d, Y H:i", strtotime($row['transaction_date'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill me-2"></i> No transactions recorded yet.
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>