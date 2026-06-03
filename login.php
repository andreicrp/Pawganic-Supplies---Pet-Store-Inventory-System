<?php
include 'db.php';

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT id, password, role, balance FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password, $role, $balance);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                session_start();
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                $_SESSION['balance'] = $balance;
                
                header("Location: " . ($role == "admin" ? "admin.php" : "main.php"));
                exit;
            } else {
                $message = 'Invalid password!';
                $messageType = 'error';
            }
        } else {
            $message = 'No account found! Click Register to create one.';
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = 'Login error. Please try again.';
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pawagnic Supplies - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-image: url('images/loginbg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 40px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(183, 172, 137, 0.3);
            animation: fadeIn 0.8s ease-out forwards;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, #d3c4a0, #b7ac89);
        }

        .login-title-img {
            display: block;
            margin: 0 auto 30px;
            max-width: 100%;
            width: 80%;
            height: auto;
            transition: transform 0.3s ease;
        }

        .login-title-img:hover {
            transform: scale(1.03);
        }

        h2 {
            text-align: center;
            color: #b7ac89;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .form-group:focus-within label {
            color: #b7ac89;
        }

        .form-control {
            border-radius: 15px;
            padding: 15px 15px 15px 45px;
            font-size: 16px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
            width: 100%;
            background-color: #f9f9f9;
        }

        .form-control:focus {
            border-color: #b7ac89;
            box-shadow: 0 0 10px rgba(183, 172, 137, 0.25);
            background-color: #fff;
        }

        .form-icon {
            position: absolute;
            left: 15px;
            top: 48px;
            color: #b7ac89;
            font-size: 18px;
            z-index: 10;
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 48px;
            color: #999;
            cursor: pointer;
            font-size: 18px;
        }

        .btn-primary {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            background: linear-gradient(to right, #b7ac89, #d3c4a0);
            border: none;
            border-radius: 15px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 4px 8px rgba(183, 172, 137, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #a59b7b, #c0b28c);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(183, 172, 137, 0.3);
        }

        .btn-primary:active {
            transform: translateY(1px);
        }

        .btn-secondary {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            border-radius: 15px;
            border: 2px solid #b7ac89;
            background-color: transparent;
            color: #b7ac89;
            margin-top: 15px;
            display: block;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-secondary:hover {
            background-color: #b7ac89;
            color: white;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #999;
            font-size: 14px;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }

        .divider::before {
            margin-right: 10px;
        }

        .divider::after {
            margin-left: 10px;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }

        .forgot-password a {
            color: #b7ac89;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #a59b7b;
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 24px;
            }
        }

        /* Toast Styles */
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
    </style>
</head>
<body>

    <!-- Toast Container -->
    <div class="position-fixed p-3" style="top: 0; right: 0; z-index: 11;">
        <div id="toastNotification" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header" id="toastHeader">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastBody">
                
            </div>
        </div>
    </div>

    <div class="login-container">
        <img src="images/Pawagnic Supplies logo.png" alt="Pawagnic Supplies" class="login-title-img">
        <h2>Welcome Back</h2>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <i class="fas fa-user form-icon"></i>
                <input type="text" id="username" name="username" class="form-control" required placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock form-icon"></i>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            </div>
            
            <div class="forgot-password">
                    
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
        </form>
        
        <div class="divider">OR</div>
        
        <a href="register.php" class="btn btn-secondary">
            <i class="fas fa-user-plus me-2"></i> Create Account
        </a>
    </div>

    <script>
        // Toast notification function
        function showToast(message, type = 'error') {
            const toastElement = document.getElementById('toastNotification');
            const toastHeader = document.getElementById('toastHeader');
            const toastBody = document.getElementById('toastBody');
            
            // Set message and type
            toastBody.textContent = message;
            
            // Remove previous type classes
            toastElement.classList.remove('success', 'error');
            
            // Add the appropriate class
            toastElement.classList.add(type);
            
            // Update header title
            const headerText = type === 'success' ? 'Success!' : 'Error!';
            toastHeader.innerHTML = `<strong class="me-auto">${headerText}</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>`;
            
            // Show toast with proper delay for Bootstrap loading
            toastElement.classList.remove('hide');
            
            // Use Bootstrap Toast if available, otherwise use fallback
            if (typeof bootstrap !== 'undefined') {
                const toast = new bootstrap.Toast(toastElement, {
                    delay: 3000
                });
                toast.show();
            } else {
                // Fallback: manually show the toast
                toastElement.style.display = 'block';
                setTimeout(() => {
                    toastElement.style.display = 'none';
                }, 3000);
            }
        }

        // Check if there's a message to display
        <?php if ($message): ?>
            showToast('<?php echo addslashes($message); ?>', '<?php echo $messageType; ?>');
        <?php endif; ?>

        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });

        // Add animation to form elements
        const formControls = document.querySelectorAll('.form-control');
        formControls.forEach((control, index) => {
            control.style.opacity = '0';
            control.style.transform = 'translateY(20px)';
            control.style.transition = 'all 0.5s ease';
            control.style.transitionDelay = `${0.1 + index * 0.1}s`;
            
            setTimeout(() => {
                control.style.opacity = '1';
                control.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>