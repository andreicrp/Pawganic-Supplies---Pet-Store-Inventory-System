<?php
session_start();
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About Us - Pawganic Supplies</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    /* Navigation Bar */
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

    /* Active link indicator */
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

    .profile-dropdown:hover .dropdown-content,
    .profile-dropdown.open .dropdown-content {
      display: block;
      opacity: 1;
      transform: translateY(0);
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: var(--card-bg);
      backdrop-filter: blur(5px);
      right: 0;
      top: 42px;
      border-radius: 10px;
      box-shadow: 0 8px 20px var(--shadow);
      z-index: 10;
      min-width: 180px;
      opacity: 0;
      transform: translateY(-5px);
      transition: all 0.3s ease;
      overflow: hidden;
      border: 1px solid rgba(193, 154, 107, 0.3);
    }

    .dropdown-content a {
      padding: 12px 16px;
      color: var(--accent-dark);
      text-decoration: none;
      display: flex;
      align-items: center;
      transition: background-color 0.2s ease;
    }

    .dropdown-content a i {
      margin-right: 10px;
      width: 16px;
      text-align: center;
      color: var(--accent);
    }

    .dropdown-content a:hover {
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

    /* About Us Content */
    .about-container {
      max-width: 1200px;
      margin: 60px auto;
      padding: 40px;
      background-color: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      box-shadow: 0 15px 30px var(--shadow);
      border: 1px solid rgba(193, 154, 107, 0.3);
    }

    .about-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .about-header h1 {
      font-size: 36px;
      color: var(--accent-dark);
      margin-bottom: 15px;
      position: relative;
      display: inline-block;
    }

    .about-header h1::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(to right, var(--accent-light), var(--accent));
      border-radius: 3px;
    }

    .about-section {
      margin-bottom: 40px;
    }

    .about-section h2 {
      font-size: 24px;
      color: var(--accent-dark);
      margin-bottom: 20px;
      position: relative;
      padding-left: 20px;
    }

    .about-section h2::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 10px;
      height: 10px;
      background-color: var(--accent);
      border-radius: 50%;
    }

    .about-section p {
      margin-bottom: 15px;
      line-height: 1.8;
      color: var(--text-dark);
      font-size: 16px;
    }

    .team-section {
      margin-top: 50px;
    }

    /* Footer */
    footer {
      background-color: var(--accent-dark);
      color: var(--text-light);
      padding: 70px 5% 25px;
      position: relative;
    }

    footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 12px;
      background: linear-gradient(to right, var(--accent-light), var(--accent), var(--accent-light));
    }

    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 45px;
      margin-bottom: 45px;
    }

    .footer-section h3 {
      margin-bottom: 28px;
      font-size: 24px;
      position: relative;
      padding-bottom: 15px;
      display: inline-block;
    }

    .footer-section h3::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 55px;
      height: 3px;
      background-color: var(--accent-light);
      border-radius: 3px;
    }

    .footer-section p {
      margin-bottom: 22px;
      line-height: 1.8;
      color: #ddd;
    }

    .footer-links a {
      display: block;
      color: #ddd;
      text-decoration: none;
      margin-bottom: 14px;
      transition: color 0.3s ease, transform 0.3s ease;
      position: relative;
      padding-left: 15px;
    }

    .footer-links a::before {
      content: '→';
      position: absolute;
      left: 0;
      opacity: 0;
      transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .footer-links a:hover {
      color: var(--accent-light);
      transform: translateX(5px);
    }

    .footer-links a:hover::before {
      opacity: 1;
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

    /* Responsive Adjustments */
    @media (max-width: 992px) {
      .about-container {
        margin: 40px 5%;
        padding: 30px;
      }
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

      .about-header h1 {
        font-size: 30px;
      }

      .about-section h2 {
        font-size: 22px;
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

      .about-container {
        padding: 20px;
        margin: 30px 4%;
      }

      .about-header h1 {
        font-size: 26px;
      }

      .about-section h2 {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>

  <div class="navbar">
    <div class="logo">
      <a href="main.php" style="text-decoration: none;">
        <img src="images/Pawagnic Supplies logo.png" alt="Pawganic Logo" class="logo-img">
      </a>
    </div>

    <div class="nav-links">
      <a href="main.php">Home</a>
      <a href="shop.php">Shop</a>
      <a href="about.php" class="active">About</a>
  
      <?php
        if (isset($_SESSION['user_id'])) {
          // Show Admin link only if user is admin
          if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            echo '<a href="admin.php">Admin</a>';
          }

          // Get username from session or database
          if (isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
          } else {
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($username);
            $stmt->fetch();
            $stmt->close();
            $username = $username ?? 'User';
          }
          
          $role = $_SESSION['role'] ?? 'user';
          $balance = $_SESSION['balance'] ?? 0;
          
          echo '
          <div class="profile-dropdown">
            <img src="images/profile.jpg" alt="Profile" class="profile-pic">
            <div class="dropdown-content">
              <div class="dropdown-profile-info">
                <div class="dropdown-profile-name">' . htmlspecialchars($username) . '</div>
                <div class="dropdown-profile-role">' . htmlspecialchars($role) . '</div>
                <div class="dropdown-profile-balance">₱' . number_format($balance, 2) . '</div>
              </div>
              <a href="profile.php"><i class="fas fa-user"></i>Profile</a>
              <a href="purchase_history.php"><i class="fas fa-history"></i>Purchase History</a>
              <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
            </div>
          </div>';
        } else {
          echo '<a href="login.php" class="login-link"><i class="fas fa-sign-in-alt"></i> Login</a>';
        }
      ?>
    </div>
  </div>

  <!-- About Us Content -->
  <div class="about-container">
    <div class="about-header">
      <h1>About Pawganic Supplies</h1>
    </div>

    <div class="about-section">
      <h2>Our Story</h2>
      <p>Founded in 2020, Pawganic Supplies began with a simple mission: to create premium cat treats that are both delicious and nutritious. Our journey started when our founder, a dedicated cat lover, noticed the lack of truly high-quality, natural treat options for feline companions.</p>
      <p>What began as homemade recipes for a beloved pet quickly evolved into a passion project to provide all cats with treats they deserve - made with love, care, and the finest ingredients.</p>
    </div>

    <div class="about-section">
      <h2>Our Philosophy</h2>
      <p>At Pawganic Supplies, we believe that cats deserve the best. Our philosophy is built on three core principles:</p>
      <p><strong>Quality:</strong> We use only premium, human-grade ingredients sourced from trusted suppliers.</p>
      <p><strong>Health:</strong> All our products are developed alongside veterinary nutritionists to ensure they support feline wellness.</p>
      <p><strong>Sustainability:</strong> We are committed to environmentally friendly practices in our production and packaging.</p>
    </div>

    <div class="about-section">
      <h2>Our Process</h2>
      <p>Each batch of Pawganic treats is carefully crafted in our dedicated kitchen facility. We bake in small batches to ensure quality control and maximum freshness. Before any product reaches your doorstep, it undergoes rigorous testing for both taste (by our feline taste-testers!) and nutritional value.</p>
    </div>

    <div class="about-section">
      <h2>Our Promise</h2>
      <p>We promise to never compromise on quality or cut corners. Every treat we make is something we would proudly feed to our own cats. We stand behind our products 100% - if your cat doesn't love our treats, we offer a satisfaction guarantee.</p>
      <p>Thank you for choosing Pawganic Supplies. We look forward to bringing joy to your feline friends one treat at a time.</p>
    </div>

    <!-- FAQ Section with Enhanced Accordion -->
    <div class="about-section faq-section">
      <h2>Frequently Asked Questions</h2>
      <div class="accordion-container">
        <!-- FAQ Item 1 -->
        <div class="accordion-item">
          <div class="accordion-header" onclick="toggleAccordion(this)">
            <h3 class="accordion-title"><i class="fas fa-chevron-right"></i>Are your treats safe for all cats?</h3>
          </div>
          <div class="accordion-content">
            <p class="accordion-text">Yes! All Pawganic treats are formulated with veterinary nutritionists and made from natural, cat-safe ingredients. However, we always recommend checking with your vet if your cat has specific dietary needs or allergies.</p>
          </div>
        </div>

        <!-- FAQ Item 2 -->
        <div class="accordion-item">
          <div class="accordion-header" onclick="toggleAccordion(this)">
            <h3 class="accordion-title"><i class="fas fa-chevron-right"></i>How should I store the treats?</h3>
          </div>
          <div class="accordion-content">
            <p class="accordion-text">Store treats in a cool, dry place in their original packaging. For maximum freshness, use within 30 days of opening. Our treats contain no artificial preservatives, so they're best enjoyed fresh!</p>
          </div>
        </div>

        <!-- FAQ Item 3 -->
        <div class="accordion-item">
          <div class="accordion-header" onclick="toggleAccordion(this)">
            <h3 class="accordion-title"><i class="fas fa-chevron-right"></i>What ingredients do you use?</h3>
          </div>
          <div class="accordion-content">
            <p class="accordion-text">We use only premium, human-grade ingredients including chicken, turkey, fish, and organic vegetables. All ingredients are sourced from trusted suppliers who meet our high standards for quality and sustainability.</p>
          </div>
        </div>

        <!-- FAQ Item 4 -->
        <div class="accordion-item">
          <div class="accordion-header" onclick="toggleAccordion(this)">
            <h3 class="accordion-title"><i class="fas fa-chevron-right"></i>Do you offer international shipping?</h3>
          </div>
          <div class="accordion-content">
            <p class="accordion-text">Currently, we ship within the Philippines. We're working on expanding our shipping options. Contact our support team at meow@pawganic.com for inquiries about international orders.</p>
          </div>
        </div>

        <!-- FAQ Item 5 -->
        <div class="accordion-item">
          <div class="accordion-header" onclick="toggleAccordion(this)">
            <h3 class="accordion-title"><i class="fas fa-chevron-right"></i>What if my cat doesn't like the treats?</h3>
          </div>
          <div class="accordion-content">
            <p class="accordion-text">We offer a 100% satisfaction guarantee! If your cat doesn't enjoy our treats, contact us for a full refund or product exchange. Your feline friend's happiness is our priority.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
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
          <a href="main.php">→ Home</a>
          <a href="shop.php">→ Shop</a>
          <a href="about.php">→ About</a>
          <a href="#faq">→ FAQs</a>
          <a href="#">→ Cat Care Tips</a>
        </div>
      </div>
      <div class="footer-section footer-contact">
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

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Profile dropdown click handler
      const profileDropdown = document.querySelector('.profile-dropdown');
      const profilePic = document.querySelector('.profile-pic');
      const dropdownLinks = document.querySelectorAll('.dropdown-content a');

      if (profileDropdown) {
        profilePic.addEventListener('click', function(e) {
          e.stopPropagation();
          profileDropdown.classList.toggle('open');
        });

        dropdownLinks.forEach(link => {
          link.addEventListener('click', function(e) {
            // Keep dropdown open briefly for smooth transition
            profileDropdown.classList.add('open');
            // Allow navigation to happen smoothly
            setTimeout(() => {
              profileDropdown.classList.remove('open');
            }, 100);
          });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
          if (!profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('open');
          }
        });
      }

      // Add active class to current page in navbar
      const currentLocation = window.location.href;
      const navLinks = document.querySelectorAll('.nav-links a');
      navLinks.forEach(link => {
        if (link.href === currentLocation) {
          link.classList.add('active');
        }
      });
    });

    // Accordion toggle function
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