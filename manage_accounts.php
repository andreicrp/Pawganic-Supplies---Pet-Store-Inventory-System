<?php 
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// === Handle Updates and Deletes === //
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update role and balance
    if (isset($_POST['update_id'], $_POST['role'], $_POST['balance'])) {
        $update_id = $_POST['update_id'];
        $role = $_POST['role'];
        $balance = $_POST['balance'];

        $stmt = $conn->prepare("UPDATE users SET role = ?, balance = ? WHERE id = ?");
        $stmt->bind_param("sdi", $role, $balance, $update_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "User updated successfully.";
        }
    }

    // Delete user
    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        if ($delete_id != $_SESSION['user_id']) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $delete_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "User deleted successfully.";
            }
        }
    }

    // Update username
    if (isset($_POST['update_username_id'], $_POST['new_username'])) {
        $id = $_POST['update_username_id'];
        $new_username = $_POST['new_username'];

        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $new_username, $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Username updated successfully.";
        }
    }

    // Update password
    if (isset($_POST['update_password_id'], $_POST['new_password'])) {
        $id = $_POST['update_password_id'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Password updated successfully.";
        }
    }

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Count total users for stats
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$admin_count = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'Admin'")->fetch_assoc()['total'];
$user_count = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'User'")->fetch_assoc()['total'];
$total_balance = $conn->query("SELECT SUM(balance) as total FROM users")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts | Pet Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #d3c4a0, #f7f2e8);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .top-header {
            background: linear-gradient(90deg, #a4703f, #eac285);
            color: #fff;
            padding: 20px 30px;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-header h2 {
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-btn:hover {
            background: linear-gradient(135deg, #d3c4a0, #e0d0b0);
            color: #7d5a2a;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }
        
        .manage-title-img {
            display: block;
            max-width: 300px;
            margin: 0 auto 20px;
        }

        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #fffaf1, #f5ecd6);
            border-radius: 12px;
            padding: 18px;
            flex: 1;
            min-width: 200px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
            text-align: center;
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
            font-size: 1.6rem;
            font-weight: 700;
            color: #7d5a2a;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #a4703f;
            font-weight: 500;
        }

        .user-card {
            background: linear-gradient(135deg, #fff, #f9f5f0);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
            transition: all 0.3s ease-in-out;
            border-left: 5px solid #d3c4a0;
        }

        .user-card:hover {
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
            border-left: 5px solid #a4703f;
        }
        
        .user-card.current-user {
            border-left: 5px solid #5bc0de;
            background: linear-gradient(135deg, #f0f9ff, #e0f3fd);
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 15px;
            border: 1px solid #e0d0b0;
            background-color: #fffcf6;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #a4703f;
            box-shadow: 0 0 10px rgba(164, 112, 63, 0.25);
            background-color: #fff;
        }

        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .btn-update {
            background: linear-gradient(135deg, #5cb85c, #449d44);
            border: none;
            color: white;
        }

        .btn-update:hover {
            background: linear-gradient(135deg, #4cae4c, #398439);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(92, 184, 92, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, #d9534f, #c9302c);
            border: none;
            color: white;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #c9302c, #ac2925);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(217, 83, 79, 0.3);
        }

        .btn-edit {
            background: linear-gradient(135deg, #f0ad4e, #ec971f);
            border: none;
            color: white;
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #ec971f, #d58512);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(240, 173, 78, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #b9a678, #d3c4a0);
            border: none;
            color: white;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #a4703f, #b9a678);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(185, 166, 120, 0.3);
        }

        .badge-role {
            border-radius: 8px;
            padding: 5px 10px;
            font-size: 13px;
            color: white;
            display: inline-block;
            font-weight: 600;
        }

        .badge-admin {
            background: linear-gradient(135deg, #d9534f, #c9302c);
        }

        .badge-user {
            background: linear-gradient(135deg, #5bc0de, #46b8da);
        }

        .modal-content {
            border-radius: 20px;
            background: linear-gradient(135deg, #fff, #f9f5f0);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            border: none;
        }
        
        .modal-header {
            border-bottom: 1px solid #e0d0b0;
            padding: 20px 25px;
        }
        
        .modal-body {
            padding: 25px;
        }

        .toast {
            z-index: 1055;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: none;
        }
        
        .toast-success {
            background: linear-gradient(135deg, #5cb85c, #449d44);
            color: white;
        }
        
        .username-display {
            font-size: 18px;
            font-weight: 600;
            color: #7d5a2a;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .balance-display {
            font-size: 16px;
            color: #a4703f;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .search-container {
            margin-bottom: 20px;
            position: relative;
        }
        
        .search-input {
            padding-left: 40px;
            background-color: #fffcf6;
            border: 1px solid #e0d0b0;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a4703f;
        }
        
        .actions-container {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        /* Responsive fixes */
        @media (max-width: 768px) {
            .user-card {
                padding: 15px;
            }
            
            .actions-container {
                flex-direction: column;
                width: 100%;
            }
            
            .actions-container .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="top-header">
    <h2><i class="bi bi-people-fill"></i> Manage Accounts</h2>
    <a href="admin.php" class="back-btn"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="container pb-5">
    <img src="images/manage.png" alt="Manage Accounts" class="manage-title-img">

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="toast show position-fixed top-0 end-0 m-4 toast-success" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= $_SESSION['success_message']; ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php unset($_SESSION['success_message']); endif; ?>
    
    <!-- Stats Row -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-value"><?= $total_users ?></div>
            <div class="stat-label">Total Accounts</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
            <div class="stat-value"><?= $admin_count ?></div>
            <div class="stat-label">Administrators</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person"></i></div>
            <div class="stat-value"><?= $user_count ?></div>
            <div class="stat-label">Regular Users</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">₱<?= number_format($total_balance, 2) ?></div>
            <div class="stat-label">Total Balance</div>
        </div>
    </div>
    
    <!-- Search Bar -->
    <div class="search-container">
        <i class="bi bi-search search-icon"></i>
        <input type="text" id="userSearch" class="form-control search-input" placeholder="Search for users...">
    </div>

    <div id="usersContainer">
    <?php
    $users = $conn->query("SELECT * FROM users ORDER BY role DESC, username ASC");
    while ($u = $users->fetch_assoc()):
        $id = htmlspecialchars($u['id']);
        $username = htmlspecialchars($u['username']);
        $role = htmlspecialchars($u['role']);
        $balance = htmlspecialchars($u['balance']);
        $is_current_user = ($id == $_SESSION['user_id']);
    ?>
    <div class="user-card <?= $is_current_user ? 'current-user' : '' ?>">
        <div class="row align-items-center">
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="user-info">
                    <div class="username-display">
                        <i class="bi bi-person-circle"></i> <?= $username ?>
                        <?php if($is_current_user): ?>
                            <span class="badge bg-info ms-2">You</span>
                        <?php endif; ?>
                    </div>
                    <span class="badge-role <?= $role === 'Admin' ? 'badge-admin' : 'badge-user' ?>">
                        <i class="bi bi-<?= $role === 'Admin' ? 'shield-lock' : 'person' ?>"></i> 
                        <?= $role ?>
                    </span>
                    <div class="balance-display">
                        <i class="bi bi-wallet2"></i> ₱<?= number_format($balance, 2) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-5 mb-3 mb-md-0">
                <form method="POST" class="row g-2">
                    <input type="hidden" name="update_id" value="<?= $id ?>">
                    <div class="col-6">
                        <select name="role" class="form-select">
                            <option value="User" <?= $role === 'User' ? 'selected' : '' ?>>User</option>
                            <option value="Admin" <?= $role === 'Admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="balance" value="<?= $balance ?>" step="0.01" class="form-control">
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-update w-100">
                            <i class="bi bi-check-circle"></i> Update Account
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <div class="actions-container">
                    <button class="btn btn-edit flex-grow-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $id ?>">
                        <i class="bi bi-pencil-square"></i> Edit Login
                    </button>
                    
                    <?php if (!$is_current_user): ?>
                        <form method="POST" class="flex-grow-1 d-inline">
                            <input type="hidden" name="delete_id" value="<?= $id ?>">
                            <button class="btn btn-delete w-100" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-delete flex-grow-1" disabled>
                            <i class="bi bi-shield-fill-exclamation"></i> Can't Delete Self
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for editing -->
    <div class="modal fade" id="editModal<?= $id ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>
                        Edit Login for <?= $username ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" class="mb-4">
                        <div class="mb-3">
                            <label for="new_username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" id="new_username" name="new_username" class="form-control" value="<?= $username ?>" required>
                            </div>
                        </div>
                        <input type="hidden" name="update_username_id" value="<?= $id ?>">
                        <button class="btn btn-update w-100">
                            <i class="bi bi-check-circle"></i> Update Username
                        </button>
                    </form>
                    
                    <hr>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword<?= $id ?>">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="update_password_id" value="<?= $id ?>">
                        <button class="btn btn-danger w-100">
                            <i class="bi bi-shield-lock-fill"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    </div>
    
    <a href="admin.php" class="btn btn-secondary w-100 mt-4">
        <i class="bi bi-arrow-left-circle-fill"></i> Back to Dashboard
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toast notification
    const toastEl = document.querySelector('.toast');
    if (toastEl) new bootstrap.Toast(toastEl).show();
    
    // Password toggle visibility
    document.querySelectorAll('[id^="togglePassword"]').forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Change the eye icon
            const eyeIcon = this.querySelector('i');
            eyeIcon.classList.toggle('bi-eye');
            eyeIcon.classList.toggle('bi-eye-slash');
        });
    });
    
    // Search functionality
    document.getElementById('userSearch').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const userCards = document.querySelectorAll('.user-card');
        
        userCards.forEach(card => {
            const username = card.querySelector('.username-display').textContent.toLowerCase();
            const role = card.querySelector('.badge-role').textContent.toLowerCase();
            
            if (username.includes(searchValue) || role.includes(searchValue)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>