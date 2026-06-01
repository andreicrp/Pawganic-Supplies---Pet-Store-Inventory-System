<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #d3c4a0;
            --primary-dark: #bfae85;
            --secondary: #8a7e63;
            --light-bg: #f7f2e8;
            --dark-text: #4a4a4a;
        }
        
        body {
        background: linear-gradient(135deg, #d3c4a0, #f7f2e8);
        font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .dashboard-container {
            background-color: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            max-width: 480px;
            width: 100%;
            text-align: center;
            animation: fadeInUp 0.7s ease-out;
        }

        .logo-container {
            margin-bottom: 30px;
        }

        .dashboard-title-img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .welcome-text {
            color: var(--dark-text);
            margin-bottom: 30px;
            font-size: 16px;
        }

        .menu-button {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 16px 20px;
            font-size: 17px;
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
            color: white;
            background-color: var(--primary);
            border: none;
            text-align: left;
            position: relative;
            text-decoration: none;
            overflow: hidden;
        }

        .menu-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .menu-button i {
            margin-right: 12px;
            font-size: 20px;
            width: 24px;
            text-align: center;
        }
        
        .menu-button::after {
            content: "";
            position: absolute;
            height: 100%;
            width: 3px;
            background-color: rgba(255, 255, 255, 0.5);
            top: 0;
            left: 0;
            transition: width 0.3s ease;
        }
        
        .menu-button:hover::after {
            width: 6px;
        }
        
        .menu-button.logout {
            background-color: #f5e1c9;
            color: var(--dark-text);
            margin-top: 10px;
            border-top: 1px solid #f0f0f0;
            padding-top: 20px;
        }
        
        .menu-button.logout:hover {
            background-color: #edd3b4;
        }

        .menu-section {
            margin-top: 35px;
        }

        .admin-badge {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        @keyframes fadeInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @media (max-width: 576px) {
            .dashboard-container {
                padding: 30px 20px;
                border-radius: 15px;
                margin: 20px;
            }
            
            .menu-button {
                padding: 14px 16px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="logo-container">
            <img src="images/dashboard.png" alt="Dashboard Logo" class="dashboard-title-img">
            <div class="admin-badge">
                <i class="fas fa-shield-alt"></i> Administrator
            </div>
        </div>
        
        <p class="welcome-text">Welcome to your admin control panel. Select an option below to manage your system.</p>
        
        <div class="menu-section">
            <a href="index.php" class="menu-button">
                <i class="fas fa-box-open"></i>
                <span>Inventory Management</span>
            </a>
            
            <a href="manage_accounts.php" class="menu-button">
                <i class="fas fa-users-cog"></i>
                <span>User Accounts</span>
            </a>
            
            <a href="purchase_history.php" class="menu-button">
                <i class="fas fa-history"></i>
                <span>Purchase Records</span>
            </a>
            
            <a href="logout.php" class="menu-button logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 