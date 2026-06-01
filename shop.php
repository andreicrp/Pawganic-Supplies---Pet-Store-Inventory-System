<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_balance = $_SESSION['balance'] ?? 0;

$search_query = "";
$sort_by = "";
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $search_query = '%' . $_POST['search'] . '%';
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
    $stmt->bind_param("s", $search_query);
} elseif (isset($_POST['sort_by']) && $_POST['sort_by'] != "") {
    switch ($_POST['sort_by']) {
        case "price_asc": $stmt = $conn->prepare("SELECT * FROM products ORDER BY price ASC"); break;
        case "price_desc": $stmt = $conn->prepare("SELECT * FROM products ORDER BY price DESC"); break;
        case "stock_asc": $stmt = $conn->prepare("SELECT * FROM products ORDER BY stock ASC"); break;
        case "stock_desc": $stmt = $conn->prepare("SELECT * FROM products ORDER BY stock DESC"); break;
        default: $stmt = $conn->prepare("SELECT * FROM products"); break;
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM products");
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    body {
        background: linear-gradient(135deg, #d3c4a0, #ebf8e1);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 0;
        margin: 0;
        min-height: 100vh;
        color: #5a4226;
        display: flex;
        flex-direction: column;
    }

    .container {
        padding: 0 20px;
    }

    .balance {
        display: block;
        position: fixed;
        top: 20px;
        left: 20px;
        background: linear-gradient(to right, #8a5d2f, #c9a86d);
        color: #fff;
        padding: 12px 24px;
        border-radius: 15px;
        font-weight: 600;
        z-index: 999;
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        backdrop-filter: blur(5px);
        border: 2px solid rgba(255,255,255,0.2);
        transition: all 0.3s ease;
    }

    .balance:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .toast-container {
        position: fixed;
        bottom: 30px;
        left: 30px;
        z-index: 1055;
    }

    .custom-toast {
        background: linear-gradient(135deg, #8a5d2f, #c9a86d);
        border-radius: 15px;
        font-size: 1rem;
        padding: 15px 20px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        min-width: 250px;
        max-width: 320px;
        color: white;
        border-left: 5px solid #ffd78a;
    }

    .custom-toast .toast-body {
        padding: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .custom-toast .btn-close {
        margin-left: 12px;
        filter: invert(1);
    }

    /* CSS Variables */
    :root {
        --primary-brown: #8a5d2f;
        --light-brown: #c9a86d;
        --dark-brown: #6b4b1d;
        --accent: #c19a6b;
        --accent-dark: #8a5d2f;
        --light-bg: #e8d9b5;
        --lighter-bg: #f7eed8;
        --text-dark: #6b4b1d;
    }

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

    /* Navigation Bar */
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(15px);
      padding: 20px 5%;
      position: sticky;
      top: 0;
      z-index: 100;
      border-bottom: 4px solid #333;
      width: 100%;
      box-sizing: border-box;
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
      transform: scale(1.08);
      box-shadow: 0 0 15px rgba(166, 124, 82, 0.4);
    }

    /* Profile Dropdown */
    .profile-dropdown {
        position: relative;
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .profile-pic {
        height: 40px;
        width: 40px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.3);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .profile-pic:hover {
        border-color: #ffd78a;
        box-shadow: 0 0 10px rgba(255,215,138,0.5);
    }

    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        min-width: 220px;
        z-index: 1000;
        margin-top: 10px;
        border: 1px solid #e0e0e0;
    }

    .profile-dropdown:hover .dropdown-content {
        display: block;
    }

    .dropdown-profile-info {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        background: linear-gradient(135deg, rgba(226, 197, 168, 0.1), rgba(199, 177, 148, 0.1));
        border-radius: 12px 12px 0 0;
    }

    .dropdown-profile-name {
        font-weight: 600;
        color: var(--primary-brown);
        font-size: 0.95rem;
    }

    .dropdown-profile-role {
        font-size: 0.8rem;
        color: #999;
        margin-top: 2px;
    }

    .dropdown-profile-balance {
        font-size: 0.85rem;
        color: var(--primary-brown);
        font-weight: 500;
        margin-top: 5px;
    }

    .dropdown-content a {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-dark);
        text-decoration: none;
        padding: 12px 15px;
        transition: all 0.3s ease;
        border-radius: 8px;
    }

    .dropdown-content a:hover {
        background: linear-gradient(135deg, rgba(226, 197, 168, 0.15), rgba(199, 177, 148, 0.15));
        color: var(--primary-brown);
        padding-left: 20px;
    }

    .dropdown-content a i {
        width: 18px;
    }

    /* Footer */
    footer {
      background-color: var(--accent-dark);
      color: var(--text-light);
      padding: 70px 5% 25px;
      position: relative;
      width: 100%;
      box-sizing: border-box;
      margin-top: 60px;
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

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }

    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 45px;
      height: 45px;
      background: rgba(193, 154, 107, 0.2);
      border-radius: 50%;
      color: var(--text-light);
      transition: all 0.3s ease;
      text-decoration: none;
      border: 2px solid var(--accent-light);
    }

    .social-links a:hover {
      background: var(--accent-light);
      color: var(--accent-dark);
      transform: translateY(-5px);
    }

    .footer-links {
      display: flex;
      flex-direction: column;
      gap: 15px;
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

    .copyright {
      text-align: center;
      padding-top: 22px;
      border-top: 1px solid rgba(255, 255, 255, 0.15);
      font-size: 14px;
      color: #aaa;
    }

    .card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background-color: rgba(255, 253, 240, 0.85);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        backdrop-filter: blur(5px);
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }

    .card-img-container {
        height: 250px;
        background: linear-gradient(145deg, #f7f2e6, #fffdf0);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-bottom: 2px solid rgba(211, 196, 160, 0.4);
    }

    .card-img-container img {
        max-height: 85%;
        max-width: 85%;
        object-fit: contain;
        transition: transform 0.5s ease;
    }

    .card:hover .card-img-container img {
        transform: scale(1.08);
    }

    .card-body {
        background-color: rgba(255, 253, 240, 0.9);
        padding: 25px;
        position: relative;
    }

    .card-title {
        color: #6b4b1d;
        font-weight: 700;
        font-size: 1.3rem;
        margin-bottom: 15px;
        border-bottom: 2px dashed rgba(211, 196, 160, 0.5);
        padding-bottom: 10px;
    }

    .price-tag {
        position: absolute;
        top: -15px;
        right: 20px;
        background: linear-gradient(135deg, #8a5d2f, #c9a86d);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 700;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        border: 2px solid rgba(255,255,255,0.3);
    }

    .category-badge {
        background-color: rgba(211, 196, 160, 0.25);
        color: #6b4b1d;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 12px;
        border: 1px solid rgba(211, 196, 160, 0.5);
    }

    .stock-info {
        color: #6b4b1d;
        font-weight: 600;
        margin-top: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .stock-info i {
        color: #85a876;
    }

    .card-footer {
        background-color: rgba(244, 237, 215, 0.8);
        padding: 20px;
        border-top: none;
    }

    .btn-outline-primary {
        border: 2px solid #8a5d2f !important;
        color: #6b4b1d !important;
        background-color: transparent !important;
        transition: all 0.3s ease;
        padding: 10px 0;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 12px;
    }

    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #8a5d2f, #c9a86d) !important;
        color: #fff !important;
        border-color: #8a5d2f !important;
        transform: translateY(-2px);
    }

    .btn-outline-primary:focus,
    .btn-outline-primary:focus-visible {
        border-color: #8a5d2f !important;
        color: #6b4b1d !important;
        box-shadow: 0 0 0 0.25rem rgba(138, 93, 47, 0.25) !important;
        outline: none !important;
    }

    .btn-outline-primary:active {
        background: linear-gradient(135deg, #8a5d2f, #c9a86d) !important;
        color: #fff !important;
        border-color: #8a5d2f !important;
    }

    .btn-primary {
        background: linear-gradient(135deg, #8a5d2f, #c9a86d);
        border: none;
        color: #fff;
        transition: all 0.3s ease;
        padding: 12px 0;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        margin-top: 10px;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #7a4d1f, #b9984d);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }

    .btn-primary:focus,
    .btn-primary:focus-visible {
        background: linear-gradient(135deg, #8a5d2f, #c9a86d) !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
        outline: none !important;
    }

    .btn-primary:active {
        background: linear-gradient(135deg, #7a4d1f, #b9984d) !important;
        color: #fff !important;
        box-shadow: 0 6px 12px rgba(0,0,0,0.2) !important;
    }

    .btn-success {
        background: linear-gradient(135deg, #85a876, #c6deba);
        border: none;
        color: #fff;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #75986a, #b6cead);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }

    .btn-success:focus,
    .btn-success:focus-visible {
        background: linear-gradient(135deg, #85a876, #c6deba) !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
        outline: none !important;
    }

    .btn-success:active {
        background: linear-gradient(135deg, #75986a, #b6cead) !important;
        color: #fff !important;
        box-shadow: 0 6px 12px rgba(0,0,0,0.2) !important;
    }

    .btn-danger {
        background: linear-gradient(135deg, #d17838, #edb279);
        border: none;
        color: #fff;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #c06828, #dda269);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }

    .btn-danger:focus,
    .btn-danger:focus-visible {
        background: linear-gradient(135deg, #d17838, #edb279) !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
        outline: none !important;
    }

    .btn-danger:active {
        background: linear-gradient(135deg, #c06828, #dda269) !important;
        color: #fff !important;
        box-shadow: 0 6px 12px rgba(0,0,0,0.2) !important;
    }

    /* Back to Homepage button */
    .home-btn {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 999;
        background: linear-gradient(135deg, #8a5d2f, #c9a86d);
        border: none;
        color: white;
        padding: 15px 28px;
        border-radius: 15px;
        font-weight: 600;
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        backdrop-filter: blur(5px);
        border: 2px solid rgba(255,255,255,0.2);
        text-decoration: none;
    }

    .home-btn:hover {
        background: linear-gradient(135deg, #7a4d1f, #b9984d);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        color: white;
    }

    /* Cart button - adjusted position */
    .cart-btn {
        background: linear-gradient(135deg, #8a5d2f, #c9a86d);
        border: none;
        color: white;
        padding: 10px 18px;
        border-radius: 50%;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        cursor: pointer;
    }

    .cart-btn:hover {
        background: linear-gradient(135deg, #7a4d1f, #b9984d);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }

    /* Redesigned slide cart */
    .slide-cart {
        position: fixed;
        top: 0;
        right: -450px;
        width: 450px;
        height: 100%;
        background: linear-gradient(135deg, #fffdf0, #f1e6d0);
        box-shadow: -8px 0 25px rgba(0,0,0,0.25);
        transition: right 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        z-index: 998;
        padding: 30px;
        overflow-y: auto;
        border-left: 5px solid #c9a86d;
    }

    .slide-cart h4 {
        color: #6b4b1d;
        border-bottom: 3px solid #d3c4a0;
        padding-bottom: 15px;
        margin-bottom: 25px;
        font-weight: 700;
        text-align: center;
        letter-spacing: 1px;
        margin: 0;
    }

    .cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px solid #d3c4a0;
        padding-bottom: 15px;
        margin-bottom: 25px;
    }

    .cart-header h4 {
        margin: 0;
        color: #6b4b1d;
        font-weight: 700;
        text-align: left;
        letter-spacing: 1px;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .close-cart-btn {
        background: none;
        border: none;
        color: #6b4b1d;
        font-size: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: auto;
    }
    }

    .close-cart-btn:hover {
        color: #c9a86d;
        transform: scale(1.1);
    }

    .cart-item {
        border-bottom: 1px solid #e0d0b0;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 15px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .cart-item:hover {
        background-color: rgba(255, 255, 255, 0.9);
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        transform: translateY(-3px);
    }

    .cart-item input[type='number'] {
        width: 70px;
        border: 2px solid #d3c4a0;
        border-radius: 8px;
        padding: 8px 12px;
        background-color: #fffdf0;
        color: #6b4b1d;
        font-weight: 600;
    }

    .remove-btn {
        background: none;
        border: none;
        color: #d17838;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 8px 15px;
        border-radius: 8px;
    }

    .remove-btn:hover {
        background-color: #ffebcc;
        color: #c06828;
    }

    .checkout-btn {
        width: 100%;
        margin-top: 25px;
        padding: 15px;
        font-weight: 700;
        border-radius: 15px;
        letter-spacing: 1px;
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        border: none;
        background: linear-gradient(135deg, #85a876, #c6deba);
    }

    .checkout-btn:hover {
        background: linear-gradient(135deg, #75986a, #b6cead);
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }

    /* Form controls styling */
    .form-control, .form-select {
        border: 2px solid #d3c4a0;
        border-radius: 12px;
        padding: 12px 20px;
        background-color: rgba(255, 255, 255, 0.8);
        color: #6b4b1d;
        font-weight: 500;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(201, 168, 109, 0.25) !important;
        border-color: #8a5d2f !important;
        background-color: rgba(255, 255, 255, 0.95) !important;
        outline: none !important;
    }

    .form-control::placeholder {
        color: #b09a74;
    }

    .search-section {
        background-color: rgba(255, 253, 240, 0.7);
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        backdrop-filter: blur(5px);
        border: 2px solid rgba(211, 196, 160, 0.3);
    }

    .search-btn {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #6b4b1d;
        margin-bottom: 30px;
        text-align: center;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        letter-spacing: 1px;
    }

    .logout-btn {
        background: linear-gradient(135deg, #d17838, #edb279);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 15px;
        font-weight: 600;
        letter-spacing: 0.8px;
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .logout-btn:hover {
        background: linear-gradient(135deg, #c06828, #dda269);
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }

    .no-items {
        background-color: rgba(255, 235, 204, 0.7);
        color: #6b4b1d;
        border: 2px solid #d3c4a0;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        font-size: 1.1rem;
        box-shadow: 0 6px 12px rgba(0,0,0,0.08);
    }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">
      <a href="main.php" style="text-decoration: none;">
        <img src="images/Pawagnic Supplies logo.png" alt="Pawganic Logo" class="logo-img" style="height: 50px; width: auto;">
      </a>
    </div>
    <div class="nav-links">
      <a href="main.php">Home</a>
      <a href="shop.php" class="active">Shop</a>
      <a href="about.php">About</a>
      <?php
        if (isset($_SESSION['user_id'])) {
          if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            echo '<a href="admin.php">Admin</a>';
          }
          $nav_username = $_SESSION['username'] ?? 'User';
          $nav_role = $_SESSION['role'] ?? 'customer';
          $nav_balance = $_SESSION['balance'] ?? 0;
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
      <button onclick="toggleCart()" class="cart-btn">
          <i class="fas fa-shopping-cart"></i>
      </button>
    </div>
</div>

<div class="container mt-4">

<!-- Toast Container -->
<div class="toast-container">
    <div id="toastMessage" class="toast text-white border-0 custom-toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
    <div class="toast-body">
        Product added to cart!
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    </div>
</div>

<!-- Slide Cart -->
<div id="cart-panel" class="slide-cart">
    <div class="cart-header">
        <h4><i class="fas fa-shopping-bag me-2"></i>Your Cart</h4>
        <button class="close-cart-btn" onclick="toggleCart()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div id="cart-items"></div>
    <a href="checkout.php" class="btn btn-success mt-3 checkout-btn">
        <i class="fas fa-check-circle me-2"></i> Proceed to Checkout
    </a>
</div>

<h2 class="page-title">Browse Our Products</h2>

<!-- Search + Sort -->
<div class="search-section">
    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text" style="background-color: rgba(211, 196, 160, 0.3); border: 2px solid #d3c4a0; border-right: none; border-radius: 12px 0 0 12px;">
                    <i class="fas fa-search" style="color: #8a5d2f;"></i>
                </span>
                <input type="text" name="search" class="form-control" placeholder="Search products" value="<?= htmlspecialchars($_POST['search'] ?? '') ?>" style="border-left: none; border-radius: 0 12px 12px 0;">
            </div>
        </div>
        <div class="col-md-3">
            <select name="sort_by" class="form-select" onchange="this.form.submit()">
                <option value="">Sort by</option>
                <option value="price_asc" <?= ($_POST['sort_by'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= ($_POST['sort_by'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="stock_asc" <?= ($_POST['sort_by'] ?? '') === 'stock_asc' ? 'selected' : '' ?>>Stock: Low to High</option>
                <option value="stock_desc" <?= ($_POST['sort_by'] ?? '') === 'stock_desc' ? 'selected' : '' ?>>Stock: High to Low</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100 search-btn">
                <i class="fas fa-filter me-2"></i> Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Product Grid -->
<?php if ($result->num_rows > 0): ?>
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-img-container">
                    <?php if (!empty($row['image']) && file_exists("uploads/" . $row['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    <?php else: ?>
                        <div class="d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <i class="fas fa-image fa-3x mb-3" style="color: #d3c4a0;"></i>
                            <span class="text-muted">No Image Available</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="price-tag">₱<?= number_format($row['price'], 2) ?></div>
                    <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                    <span class="category-badge">
                        <i class="fas fa-tag me-1"></i><?= $row['category'] ?>
                    </span>
                    <div class="stock-info">
                        <i class="fas <?= $row['stock'] > 0 ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                        <?= $row['stock'] > 0 ? $row['stock'] . ' in stock' : 'Out of stock' ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-outline-primary mb-3 w-100" onclick="addToCart(<?= $row['id'] ?>)" <?= $row['stock'] <= 0 ? 'disabled' : '' ?>>
                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                    </button>
                    <form method="POST" action="checkout.php">
                        <input type="hidden" name="buy_now" value="1">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-primary w-100" <?= $row['stock'] <= 0 ? 'disabled' : '' ?>>
                            <?php if ($row['stock'] <= 0): ?>
                                <i class="fas fa-ban me-2"></i>Out of Stock
                            <?php else: ?>
                                <i class="fas fa-shopping-bag me-2"></i>Buy Now
                            <?php endif; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="no-items">
    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
    <p class="mb-0">No items found matching your criteria.</p>
</div>
<?php endif; ?>

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
function toggleCart() {
    const panel = document.getElementById('cart-panel');
    panel.style.right = panel.style.right === '0px' ? '-450px' : '0px';
}

function updateCartDisplay() {
    fetch('cart_contents.php')
        .then(res => res.text())
        .then(html => {
            document.getElementById('cart-items').innerHTML = html;
        });
}

function addToCart(productId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    })
    .then(res => res.text())
    .then(data => {
        showToast(data);
        updateCartDisplay();
    });
}

function removeFromCart(productId) {
    fetch('cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&product_id=${productId}`
    })
    .then(res => res.text())
    .then(data => {
        if (data !== 'success') {
            showToast('Item Removed Successfully!');
        }
        updateCartDisplay();
    });
}

function updateQuantity(productId, quantity) {
    fetch('cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&product_id=${productId}&quantity=${quantity}`
    })
    .then(res => res.text())
    .then(data => {
        if (data !== 'success') {
            showToast('Updated Successfully!');
        }
        updateCartDisplay();
    });
}

function showToast(message) {
    const toastEl = document.getElementById('toastMessage');
    if (!toastEl) return; // Exit if toast element doesn't exist
    const toastBody = toastEl.querySelector('.toast-body');
    if (!toastBody) return; // Exit if toast body doesn't exist
    toastBody.textContent = message;

    const toast = new bootstrap.Toast(toastEl, {
        delay: 3000 // Set the delay to 3000 milliseconds (3 seconds)
    });
    toast.show();
}

document.addEventListener('DOMContentLoaded', function() {
    updateCartDisplay();
});
</script>
</body>
</html>