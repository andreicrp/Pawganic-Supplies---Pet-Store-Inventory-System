<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

$stmt = $conn->prepare("SELECT username, role, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $role, $balance);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_username') {
        $new_username = $_POST['new_username'] ?? '';
        
        if (empty($new_username)) {
            $message = 'Username cannot be empty!';
            $messageType = 'error';
        } else if (strlen($new_username) < 3) {
            $message = 'Username must be at least 3 characters long!';
            $messageType = 'error';
        } else {
            try {
                $update_stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_username, $user_id);
                
                if ($update_stmt->execute()) {
                    $username = $new_username;
                    $message = 'Username updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Username already exists!';
                    $messageType = 'error';
                }
                $update_stmt->close();
            } catch (Exception $e) {
                $message = 'Username already exists!';
                $messageType = 'error';
            }
        }
    } 
    else if ($action == 'update_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $check_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_stmt->bind_result($hashed_password);
        $check_stmt->fetch();
        $check_stmt->close();
        
        if (!password_verify($current_password, $hashed_password)) {
            $message = 'Current password is incorrect!';
            $messageType = 'error';
        } else if (empty($new_password)) {
            $message = 'New password cannot be empty!';
            $messageType = 'error';
        } else if (strlen($new_password) < 6) {
            $message = 'New password must be at least 6 characters long!';
            $messageType = 'error';
        } else if ($new_password !== $confirm_password) {
            $message = 'Passwords do not match!';
            $messageType = 'error';
        } else {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_new_password, $user_id);
            
            if ($update_stmt->execute()) {
                $message = 'Password updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating password!';
                $messageType = 'error';
            }
            $update_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile - Pawganic Supplies</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --gradient-start: #e8d9b5;
      --gradient-end: #f7eed8;
      --accent: #a67c52;
      --accent-dark: #7d5a3c;
      --accent-light: #c19a6b;
      --text-dark: #443626;
      --text-light: #ffffff;
      --shadow: rgba(0, 0, 0, 0.1);
      --card-bg: rgba(255, 255, 255, 0.8);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
      background-attachment: fixed;
      color: var(--text-dark);
      line-height: 1.6;
    }

    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(15px);
      padding: 15px 5%;
      position: sticky;
      top: 0;
      z-index: 100;
      border-bottom: 4px solid #333;
    }

    .logo-img {
      height: 50px;
      width: auto;
      transition: transform 0.3s ease;
    }

    .logo-img:hover {
      transform: scale(1.05);
    }

    .navbar .logo {
      color: var(--accent-dark);
      font-size: 28px;
      font-weight: bold;
      letter-spacing: 1px;
      display: flex;
      align-items: center;
      transition: transform 0.3s ease;
    }
    
    .navbar .logo:hover {
      transform: scale(1.05);
    }

    .logo i {
      margin-right: 8px;
      color: var(--accent);
    }

    .navbar .nav-links {
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .navbar .nav-links a {
      color: var(--accent-dark);
      text-decoration: none;
      padding: 10px 16px;
      border-radius: 25px;
      transition: all 0.3s ease;
      font-weight: 600;
      font-size: 16px;
      position: relative;
      overflow: hidden;
    }

    .navbar .nav-links a::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 0;
      background-color: var(--accent-light);
      transition: height 0.3s ease;
      z-index: -1;
      opacity: 0.3;
    }

    .navbar .nav-links a:hover::before {
      height: 100%;
    }

    .navbar .nav-links a:hover {
      color: var(--accent-dark);
      transform: translateY(-2px);
    }

    .navbar .nav-links a.active {
      background-color: var(--accent-light);
      color: var(--text-dark);
      box-shadow: 0 4px 8px rgba(166, 124, 82, 0.25);
    }

    .profile-pic {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--accent);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .profile-pic:hover {
      transform: scale(1.1);
      box-shadow: 0 4px 12px rgba(166, 124, 82, 0.4);
    }

    .profile-dropdown {
      position: relative;
      display: inline-block;
    }

    .profile-dropdown:hover .dropdown-content {
      display: block;
      opacity: 1;
      transform: translateY(0);
    }

    .profile-dropdown .dropdown-content {
      display: none;
      position: absolute;
      background-color: var(--card-bg);
      backdrop-filter: blur(5px);
      right: 0;
      top: 42px;
      border-radius: 10px;
      box-shadow: 0 8px 20px var(--shadow);
      z-index: 1000;
      min-width: 180px;
      opacity: 0;
      transform: translateY(-5px);
      transition: all 0.3s ease;
      overflow: hidden;
      border: 1px solid rgba(193, 154, 107, 0.3);
    }

    .profile-dropdown .dropdown-content a {
      padding: 12px 16px;
      color: var(--accent-dark);
      text-decoration: none;
      display: flex;
      align-items: center;
      transition: background-color 0.2s ease;
    }

    .profile-dropdown .dropdown-content a i {
      margin-right: 10px;
      width: 16px;
      text-align: center;
      color: var(--accent);
    }

    .profile-dropdown .dropdown-content a:hover {
      background-color: rgba(166, 124, 82, 0.1);
    }

    .dropdown-profile-info {
      padding: 16px 16px 12px 16px;
      border-bottom: 1px solid rgba(193, 154, 107, 0.2);
      text-align: center;
    }

    .dropdown-profile-name {
      font-weight: 700;
      color: var(--accent-dark);
      font-size: 15px;
      margin-bottom: 6px;
    }

    .dropdown-profile-role {
      display: none;
      font-size: 12px;
      color: #999;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }

    .dropdown-profile-balance {
      font-size: 13px;
      color: var(--accent);
      font-weight: 600;
    }

    .profile-container {
      max-width: 1000px;
      margin: 40px auto;
      background: var(--card-bg);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 10px 30px var(--shadow);
    }

    .profile-header {
      display: flex;
      align-items: center;
      gap: 30px;
      margin-bottom: 40px;
      padding-bottom: 30px;
      border-bottom: 2px solid rgba(0, 0, 0, 0.1);
    }

    .profile-avatar {
      width: 120px;
      height: 120px;
      background: linear-gradient(135deg, var(--accent), var(--accent-light));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 50px;
      color: white;
      box-shadow: 0 4px 15px rgba(166, 124, 82, 0.3);
    }

    .profile-info {
      flex: 1;
    }

    .profile-info h1 {
      font-size: 32px;
      color: var(--text-dark);
      margin-bottom: 15px;
    }

    .profile-details {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 10px;
    }

    .detail-item {
      background: rgba(0, 0, 0, 0.05);
      padding: 15px;
      border-radius: 10px;
      border-left: 4px solid var(--accent-light);
    }

    .detail-label {
      font-size: 12px;
      color: #999;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 5px;
    }

    .detail-value {
      font-size: 18px;
      color: var(--text-dark);
      font-weight: bold;
    }

    .badge-role {
      display: inline-block;
      padding: 6px 12px;
      background: var(--accent-light);
      color: white;
      border-radius: 20px;
      font-size: 12px;
      text-transform: uppercase;
      margin-top: 10px;
    }

    .edit-section {
      margin-bottom: 40px;
    }

    .edit-section h2 {
      font-size: 22px;
      color: var(--text-dark);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .edit-section h2 i {
      color: var(--accent-light);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: var(--text-dark);
      font-weight: 500;
      font-size: 14px;
    }

    .form-control {
      border-radius: 10px;
      padding: 12px 15px;
      border: 1px solid #ddd;
      font-size: 16px;
      transition: all 0.3s ease;
      width: 100%;
      color: var(--text-dark);
    }

    .form-control:focus {
      border-color: var(--accent-light);
      box-shadow: 0 0 10px rgba(193, 154, 107, 0.25);
      outline: none;
    }

    .btn-update {
      background: linear-gradient(to right, var(--accent), var(--accent-light));
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 16px;
    }

    .btn-update:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(166, 124, 82, 0.3);
    }

    .btn-logout {
      background: #dc3545;
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 16px;
    }

    .btn-logout:hover {
      background: #c82333;
    }

    .edit-form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      background: rgba(0, 0, 0, 0.03);
      padding: 25px;
      border-radius: 15px;
      margin-bottom: 20px;
    }

    .divider {
      border-top: 2px solid rgba(0, 0, 0, 0.1);
      margin: 40px 0;
    }

    #toastNotification {
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      border: none;
      min-width: 300px;
      display: none;
      animation: slideIn 0.3s ease-out;
    }

    #toastNotification.hide {
      display: none !important;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(400px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    #toastNotification.success .toast-header {
      background-color: #d4edda;
      border-bottom: 1px solid #c3e6cb;
    }

    #toastNotification.success .toast-body {
      background-color: #f8f9fa;
      color: #155724;
    }

    #toastNotification.error .toast-header {
      background-color: #f8d7da;
      border-bottom: 1px solid #f5c6cb;
    }

    #toastNotification.error .toast-body {
      background-color: #f8f9fa;
      color: #721c24;
    }

    #toastNotification .toast-header strong {
      color: inherit;
    }

    #toastNotification .btn-close {
      filter: brightness(0.7);
    }

    footer {
      background: linear-gradient(135deg, var(--accent-dark), var(--accent));
      color: var(--text-light);
      padding: 60px 5% 30px;
      margin-top: 60px;
    }

    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 40px;
      max-width: 1400px;
      margin: 0 auto 40px;
    }

    .footer-section h3 {
      font-size: 20px;
      margin-bottom: 18px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .footer-section p {
      font-size: 14px;
      line-height: 1.8;
      opacity: 0.9;
    }

    .footer-links {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .footer-links a {
      color: var(--text-light);
      text-decoration: none;
      font-size: 14px;
      padding: 8px 0;
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .footer-links a::before {
      content: '→ ';
      position: relative;
      transition: all 0.3s ease;
    }

    .footer-links a:hover {
      padding-left: 8px;
    }

    .footer-links a:hover::before {
      margin-right: 5px;
    }

    .social-links {
      display: flex;
      gap: 18px;
      margin-top: 22px;
    }

    .social-links a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 45px;
      height: 45px;
      background-color: rgba(255, 255, 255, 0.12);
      border-radius: 50%;
      color: var(--text-light);
      transition: all 0.3s ease;
    }

    .social-links a:hover {
      background-color: var(--accent-light);
      color: var(--accent-dark);
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.25);
    }

    .copyright {
      text-align: center;
      padding-top: 22px;
      border-top: 1px solid rgba(255, 255, 255, 0.15);
      font-size: 14px;
      color: #aaa;
    }

    @media (max-width: 768px) {
      .navbar {
        padding: 15px 4%;
      }

      .navbar .nav-links {
        gap: 12px;
      }

      .navbar .nav-links a {
        padding: 8px 12px;
        font-size: 15px;
      }

      .profile-container {
        margin: 20px;
        padding: 20px;
      }

      .profile-header {
        flex-direction: column;
        text-align: center;
      }

      .edit-form-row {
        grid-template-columns: 1fr;
      }

      .profile-details {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 576px) {
      .navbar .logo {
        font-size: 22px;
      }

      .navbar .nav-links {
        gap: 8px;
      }

      .navbar .nav-links a {
        padding: 6px 10px;
        font-size: 13px;
      }

      .profile-container {
        margin: 10px;
        padding: 15px;
      }

      .profile-info h1 {
        font-size: 24px;
      }

      .edit-section h2 {
        font-size: 18px;
      }
    }

    /* Enhanced Accordion Styles */
    .faq-section {
      margin-top: 60px;
    }

    .accordion-container {
      background: var(--card-bg);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 4px 15px var(--shadow);
      border: 1px solid rgba(193, 154, 107, 0.2);
    }

    .accordion-item {
      border: none;
      margin-bottom: 15px;
      border-radius: 12px;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.6);
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .accordion-item:hover {
      box-shadow: 0 4px 12px rgba(166, 124, 82, 0.2);
      transform: translateY(-2px);
    }

    .accordion-header {
      background: rgba(255, 255, 255, 0.7);
      padding: 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: all 0.3s ease;
      border-bottom: 2px solid transparent;
    }

    .accordion-item.active .accordion-header {
      background: linear-gradient(135deg, rgba(166, 124, 82, 0.1), rgba(193, 154, 107, 0.1));
      border-bottom: 2px solid var(--accent-light);
    }

    .accordion-header:hover {
      background: linear-gradient(135deg, rgba(166, 124, 82, 0.05), rgba(193, 154, 107, 0.05));
    }

    .accordion-title {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 18px;
      font-weight: 600;
      color: var(--accent-dark);
      margin: 0;
      transition: all 0.3s ease;
    }

    .accordion-title i {
      font-size: 20px;
      color: var(--accent);
      transition: transform 0.3s ease;
    }

    .accordion-item.active .accordion-title i {
      transform: rotate(90deg);
    }

    .accordion-content {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease, padding 0.3s ease;
      padding: 0 20px;
    }

    .accordion-item.active .accordion-content {
      max-height: 500px;
      padding: 0 20px 20px 20px;
    }

    .accordion-text {
      color: var(--text-dark);
      font-size: 15px;
      line-height: 1.8;
      margin: 0;
    }
  </style>
</head>
<body>

  <div class="position-fixed p-3" style="top: 0; right: 0; z-index: 11;">
    <div id="toastNotification" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header" id="toastHeader">
        <strong class="me-auto">Notification</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="toastBody"></div>
    </div>
  </div>

  <div class="navbar">
    <div class="logo">
      <a href="main.php" style="text-decoration: none;">
        <img src="images/Pawagnic Supplies logo.png" alt="Pawganic Logo" class="logo-img">
      </a>
    </div>
    <div class="nav-links">
      <a href="main.php">Home</a>
      <a href="shop.php">Shop</a>
      <a href="about.php">About</a>
      <?php
        if (isset($_SESSION['user_id'])) {
          if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            echo '<a href="admin.php">Admin</a>';
          }
          $nav_username = $_SESSION['username'] ?? $username;
          $nav_role = $_SESSION['role'] ?? $role;
          $nav_balance = $_SESSION['balance'] ?? $balance;
          echo '
          <div class="profile-dropdown">
            <img src="images/profile.jpg" alt="Profile" class="profile-pic">
            <div class="dropdown-content">
              <div class="dropdown-profile-info">
                <div class="dropdown-profile-name">' . htmlspecialchars($nav_username) . '</div>
                <div class="dropdown-profile-role">' . htmlspecialchars($nav_role) . '</div>
                <div class="dropdown-profile-balance">₱' . number_format($nav_balance, 2) . '</div>
              </div>
              <a href="profile.php"><i class="fas fa-user"></i>Profile</a>
              <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
            </div>
          </div>';
        } else {
          echo '<a href="login.php" class="login-link"><i class="fas fa-sign-in-alt"></i> Login</a>';
        }
      ?>
    </div>
  </div>

  <div class="profile-container">
    <div class="profile-header">
      <div class="profile-avatar">
        <i class="fas fa-user"></i>
      </div>
      <div class="profile-info">
        <h1><?php echo htmlspecialchars($username); ?></h1>
        <div class="profile-details">
          <div class="detail-item">
            <div class="detail-label">Role</div>
            <div class="detail-value">
              <span class="badge-role"><?php echo htmlspecialchars($role); ?></span>
            </div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Account Balance</div>
            <div class="detail-value">₱<?php echo number_format($balance, 2); ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Member Since</div>
            <div class="detail-value">June 2026</div>
          </div>
        </div>
      </div>
    </div>

    <div class="divider"></div>

    <div class="edit-section">
      <h2><i class="fas fa-user-edit"></i> Change Username</h2>
      <form method="POST" id="usernameForm">
        <input type="hidden" name="action" value="update_username">
        <div class="edit-form-row">
          <div class="form-group">
            <label for="current_username">Current Username</label>
            <input type="text" class="form-control" id="current_username" value="<?php echo htmlspecialchars($username); ?>" disabled>
          </div>
          <div class="form-group">
            <label for="new_username">New Username</label>
            <input type="text" class="form-control" id="new_username" name="new_username" placeholder="Enter new username" required>
          </div>
        </div>
        <button type="submit" class="btn-update">
          <i class="fas fa-save"></i> Update Username
        </button>
      </form>
    </div>

    <div class="divider"></div>

    <div class="edit-section">
      <h2><i class="fas fa-lock"></i> Change Password</h2>
      <form method="POST" id="passwordForm">
        <input type="hidden" name="action" value="update_password">
        <div class="edit-form-row">
          <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password" required>
          </div>
          <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password" required>
          </div>
          <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
          </div>
        </div>
        <button type="submit" class="btn-update">
          <i class="fas fa-save"></i> Update Password
        </button>
      </form>
    </div>

    <div class="divider"></div>

    <div class="edit-section">
      <h2><i class="fas fa-sign-out-alt"></i> Account</h2>
      <p style="color: var(--text-dark); margin-bottom: 20px;">Click the button below to log out from your account.</p>
      <a href="logout.php" class="btn-logout">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>

  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>About Pawganic Supplies</h3>
        <p>Since 2020, Pawganic Supplies has been on a mission to delight cats with premium, health-conscious treats crafted by devoted cat lovers to support feline wellness in every bite.</p>
        <div class="social-links">
          <a href="https://www.facebook.com/"><i class="fab fa-facebook-f"></i></a>
          <a href="https://x.com/home"><i class="fab fa-twitter"></i></a>
          <a href="https://www.instagram.com/"><i class="fab fa-instagram"></i></a>
          <a href="https://www.tiktok.com/en/"><i class="fab fa-tiktok"></i></a>
        </div>
      </div>
      <div class="footer-section">
        <h3>Quick Links</h3>
        <div class="footer-links">
          <a href="main.php">Home</a>
          <a href="shop.php">Shop</a>
          <a href="about.php">About</a>
          <a href="main.php#faq">FAQs</a>
          <a href="#">Cat Care Tips</a>
        </div>
      </div>
      <div class="footer-section">
        <h3>Contact Us</h3>
        <p><i class="fas fa-map-marker-alt"></i> 123 Feline Street, Purrville, PH</p>
        <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
        <p><i class="fas fa-envelope"></i> meow@pawganic.com</p>
        <p><i class="fas fa-clock"></i> Mon-Fri: 9AM-6PM</p>
      </div>
    </div>
    <div class="copyright">
      <p>&copy; <?php echo date('Y'); ?> Pawganic Supplies. All rights reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function showToast(message, type = 'success') {
      const toastElement = document.getElementById('toastNotification');
      const toastHeader = document.getElementById('toastHeader');
      const toastBody = document.getElementById('toastBody');
      
      toastBody.textContent = message;
      toastElement.classList.remove('success', 'error');
      toastElement.classList.add(type);
      
      const headerText = type === 'success' ? 'Success!' : 'Error!';
      toastHeader.innerHTML = `<strong class="me-auto">${headerText}</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>`;
      
      toastElement.classList.remove('hide');
      
      if (typeof bootstrap !== 'undefined') {
        const toast = new bootstrap.Toast(toastElement, {
          delay: type === 'success' ? 2000 : 3000
        });
        toast.show();
      } else {
        toastElement.style.display = 'block';
        setTimeout(() => {
          toastElement.style.display = 'none';
        }, type === 'success' ? 2000 : 3000);
      }
    }

    <?php if ($message): ?>
      showToast('<?php echo addslashes($message); ?>', '<?php echo $messageType; ?>');
    <?php endif; ?>

    document.getElementById('usernameForm').addEventListener('submit', function(e) {
      const newUsername = document.getElementById('new_username').value.trim();
      if (newUsername.length < 3) {
        e.preventDefault();
        showToast('Username must be at least 3 characters long!', 'error');
      }
    });

    document.getElementById('passwordForm').addEventListener('submit', function(e) {
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      if (newPassword.length < 6) {
        e.preventDefault();
        showToast('Password must be at least 6 characters long!', 'error');
        return;
      }
      
      if (newPassword !== confirmPassword) {
        e.preventDefault();
        showToast('Passwords do not match!', 'error');
      }
    });

    document.addEventListener("DOMContentLoaded", function() {
      const currentLocation = window.location.href;
      const navLinks = document.querySelectorAll('.nav-links a');
      navLinks.forEach(link => {
        if (link.href === currentLocation) {
          link.classList.add('active');
        }
      });
    });

    function toggleAccordion(header) {
      const item = header.parentElement;
      const isActive = item.classList.contains('active');
      
      // Close all other items
      document.querySelectorAll('.accordion-item').forEach(el => {
        el.classList.remove('active');
      });
      
      // Toggle current item
      if (!isActive) {
        item.classList.add('active');
      }
    }
  </script>
</body>
</html>
