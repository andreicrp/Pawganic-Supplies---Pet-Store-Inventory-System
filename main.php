<?php
session_start();
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pawganic Supplies - Premium Cat Treats</title>
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

    /* Hero Section */
    .hero {
      position: relative;
      height: 650px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 0 20px;
      overflow: hidden;
      margin-bottom: 60px;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(rgba(125, 90, 60, 0.6), rgba(125, 90, 60, 0.8));
      z-index: 1;
    }

    .hero-content {
      position: relative;
      z-index: 2;
      max-width: 800px;
      background-color: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      padding: 50px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.3);
      animation: glow 3s infinite alternate;
    }

    @keyframes glow {
      from {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
      }
      to {
        box-shadow: 0 15px 40px rgba(166, 124, 82, 0.3);
      }
    }

    .hero h1 {
      font-size: 52px;
      margin-bottom: 20px;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      color: var(--text-light);
      letter-spacing: 1px;
    }

    .hero p {
      font-size: 22px;
      margin-bottom: 30px;
      color: #f0f0f0;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .cta-button {
      display: inline-block;
      background-color: var(--accent-light);
      color: var(--text-dark);
      padding: 16px 36px;
      border-radius: 30px;
      font-size: 18px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      position: relative;
      overflow: hidden;
      z-index: 1;
      letter-spacing: 0.5px;
    }

    .cta-button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background-color: var(--accent);
      transition: all 0.4s ease;
      z-index: -1;
    }

    .cta-button:hover::before {
      left: 0;
    }

    .cta-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
      color: white;
    }

    /* Slideshow */
    .slideshow-container {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
    }

    .slide {
      width: 100%;
      height: 100%;
      object-fit: cover;
      position: absolute;
      top: 0;
      left: 0;
      opacity: 0;
      transition: opacity 1.5s ease-in-out;
      filter: brightness(0.9);
    }

    .slide.active {
      opacity: 1;
    }

    /* Features Section */
    .features {
      padding: 90px 5%;
      background-color: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(15px);
      border-radius: 25px;
      margin: 0 5% 90px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.07);
      border: 1px solid rgba(193, 154, 107, 0.3);
    }

    .section-title {
      text-align: center;
      font-size: 40px;
      margin-bottom: 60px;
      color: var(--accent-dark);
      position: relative;
      letter-spacing: 1px;
      word-wrap: break-word;
      word-break: break-word;
      overflow-wrap: break-word;
      white-space: normal;
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 4px;
      background: linear-gradient(to right, var(--accent-light), var(--accent));
      border-radius: 4px;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 40px;
    }

    .feature-card {
      background-color: var(--card-bg);
      padding: 45px 35px;
      border-radius: 20px;
      box-shadow: 0 15px 30px var(--shadow);
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      text-align: center;
      border: 1px solid rgba(193, 154, 107, 0.2);
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 0;
      background: linear-gradient(to bottom, rgba(193, 154, 107, 0.15), transparent);
      transition: height 0.5s ease;
      z-index: -1;
    }

    .feature-card:hover::before {
      height: 100%;
    }

    .feature-card:hover {
      transform: translateY(-15px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.18);
    }

    .feature-icon {
      font-size: 60px;
      color: var(--accent);
      margin-bottom: 30px;
      transition: transform 0.3s ease;
    }

    .feature-card:hover .feature-icon {
      transform: scale(1.15);
    }

    .feature-card h3 {
      margin-bottom: 20px;
      font-size: 26px;
      color: var(--accent-dark);
      word-wrap: break-word;
      word-break: break-word;
      overflow-wrap: break-word;
    }

    .feature-card p {
      color: var(--text-dark);
      font-size: 17px;
      line-height: 1.8;
      word-wrap: break-word;
      word-break: break-word;
      overflow-wrap: break-word;
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

    /* Responsive Adjustments */
    @media (max-width: 992px) {
      .features {
        margin: 0 3% 60px;
        padding: 60px 4%;
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

      .hero {
        height: 550px;
      }

      .hero h1 {
        font-size: 38px;
      }

      .hero p {
        font-size: 18px;
      }

      .hero-content {
        padding: 30px;
      }

      .section-title {
        font-size: 32px;
        letter-spacing: 0.5px;
        padding: 0 10px;
      }
      
      .features {
        padding: 50px 15px;
        margin: 0 0 50px 0;
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

      .hero {
        height: 480px;
      }

      .hero h1 {
        font-size: 28px;
      }

      .hero p {
        font-size: 16px;
      }

      .hero-content {
        padding: 20px;
      }

      .cta-button {
        padding: 12px 24px;
        font-size: 16px;
      }
      
      .feature-card {
        padding: 25px 20px;
        word-wrap: break-word;
        word-break: break-word;
      }

      .section-title {
        font-size: 26px;
        margin-bottom: 30px;
        letter-spacing: 0px;
        padding: 0 10px;
      }

      .features {
        padding: 40px 15px;
        margin: 0 0 40px 0;
      }
    }

    @media (max-width: 480px) {
      .section-title {
        font-size: 22px;
        margin-bottom: 20px;
        letter-spacing: 0px;
        padding: 0 8px;
      }

      .feature-card {
        padding: 20px 15px;
      }

      .feature-card h3 {
        font-size: 20px;
      }

      .feature-card p {
        font-size: 15px;
      }

      .features {
        padding: 30px 12px;
        margin: 0;
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

    /* Promotional Banner */
    .promo-banner {
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
      padding: 25px;
      border-radius: 15px;
      text-align: center;
      margin: 60px 5% 60px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      position: relative;
      overflow: hidden;
    }

    .promo-banner::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -20%;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(20px); }
    }

    .promo-content {
      position: relative;
      z-index: 2;
    }

    .promo-banner h2 {
      color: white;
      font-size: 32px;
      margin-bottom: 10px;
      font-weight: 700;
    }

    .promo-banner p {
      color: rgba(255, 255, 255, 0.9);
      font-size: 18px;
      margin-bottom: 20px;
    }

    .discount-badge {
      display: inline-block;
      background: white;
      color: var(--accent-dark);
      padding: 10px 20px;
      border-radius: 25px;
      font-weight: 700;
      font-size: 20px;
      margin: 0 10px;
    }

    .promo-cta {
      display: inline-block;
      background: white;
      color: var(--accent);
      padding: 14px 32px;
      border-radius: 30px;
      font-weight: 600;
      text-decoration: none;
      margin-top: 15px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .promo-cta:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    /* Testimonials Section */
    .testimonials-section {
      padding: 90px 5%;
      background-color: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(15px);
      border-radius: 25px;
      margin: 60px 5% 0;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.07);
      border: 1px solid rgba(193, 154, 107, 0.3);
    }

    .testimonials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 35px;
      margin-top: 50px;
    }

    .testimonial-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(232, 217, 181, 0.3));
      padding: 35px;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
      border-left: 5px solid var(--accent-light);
      transition: all 0.4s ease;
    }

    .testimonial-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 35px rgba(166, 124, 82, 0.2);
    }

    .testimonial-stars {
      color: #ffc107;
      font-size: 18px;
      margin-bottom: 15px;
      letter-spacing: 2px;
    }

    .testimonial-text {
      color: var(--text-dark);
      font-size: 16px;
      font-style: italic;
      line-height: 1.8;
      margin-bottom: 20px;
      position: relative;
      padding-left: 25px;
    }

    .testimonial-text::before {
      content: '"';
      position: absolute;
      left: 0;
      top: -10px;
      font-size: 50px;
      color: var(--accent-light);
      opacity: 0.3;
    }

    .testimonial-author {
      font-weight: 700;
      color: var(--accent-dark);
      font-size: 16px;
      margin-bottom: 5px;
    }

    .testimonial-role {
      font-size: 14px;
      color: #888;
    }

    /* CTA Section */
    .cta-section {
      padding: 80px 5%;
      text-align: center;
      background: linear-gradient(135deg, var(--accent-dark), var(--accent));
      margin: 60px 5% 0;
      border-radius: 25px;
      color: white;
      position: relative;
      overflow: hidden;
    }

    .cta-section::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 500px;
      height: 500px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
    }

    .cta-section h2 {
      font-size: 42px;
      margin-bottom: 20px;
      position: relative;
      z-index: 2;
    }

    .cta-section p {
      font-size: 20px;
      margin-bottom: 30px;
      position: relative;
      z-index: 2;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    .cta-btn {
      display: inline-block;
      background: white;
      color: var(--accent-dark);
      padding: 16px 40px;
      border-radius: 30px;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.3s ease;
      position: relative;
      z-index: 2;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .cta-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    /* Stats Section */
    .stats-section {
      padding: 60px 5%;
      background: linear-gradient(135deg, rgba(232, 217, 181, 0.6), rgba(247, 238, 216, 0.6));
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 40px;
      text-align: center;
      margin: 0 0 60px 0;
    }

    .stat-item {
      padding: 30px;
    }

    .stat-number {
      font-size: 48px;
      font-weight: 700;
      color: var(--accent);
      margin-bottom: 10px;
    }

    .stat-label {
      font-size: 18px;
      color: var(--accent-dark);
      font-weight: 600;
    }

    /* Enhanced Responsive Adjustments */
    @media (max-width: 768px) {
      .promo-banner {
        margin: 40px 3% 40px;
        padding: 20px;
      }

      .promo-banner h2 {
        font-size: 24px;
      }

      .promo-banner p {
        font-size: 16px;
      }

      .discount-badge {
        font-size: 18px;
      }

      .testimonials-section {
        margin: 40px 3% 0;
        padding: 50px 4%;
      }

      .cta-section {
        margin: 40px 3% 0;
        padding: 60px 4%;
      }

      .cta-section h2 {
        font-size: 32px;
      }

      .cta-section p {
        font-size: 18px;
      }

      .stats-section {
        padding: 40px 4%;
        gap: 25px;
      }
    }

    @media (max-width: 576px) {
      .promo-banner {
        margin: 30px 0;
        padding: 15px;
      }

      .promo-banner h2 {
        font-size: 20px;
      }

      .discount-badge {
        display: block;
        margin: 10px 0;
        font-size: 16px;
      }

      .testimonials-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .testimonial-card {
        padding: 20px;
      }

      .cta-section {
        margin: 30px 0;
        padding: 40px 15px;
      }

      .cta-section h2 {
        font-size: 24px;
      }

      .cta-section p {
        font-size: 16px;
      }

      .stat-number {
        font-size: 36px;
      }

      .stat-label {
        font-size: 14px;
      }
    }

    /* Login Required Section */
    .login-required {
      background: linear-gradient(135deg, rgba(166, 124, 82, 0.1), rgba(193, 154, 107, 0.1));
      padding: 60px 40px;
      text-align: center;
      border-radius: 15px;
      border: 2px dashed rgba(166, 124, 82, 0.3);
    }

    .login-required i {
      font-size: 72px;
      color: var(--accent);
      margin-bottom: 20px;
      opacity: 0.6;
    }

    .login-required h3 {
      font-size: 28px;
      color: var(--accent-dark);
      margin-bottom: 15px;
      font-weight: 700;
    }

    .login-required p {
      font-size: 18px;
      color: var(--text-dark);
      margin-bottom: 30px;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
    }

    .login-required-btn {
      display: inline-block;
      background: var(--accent);
      color: white;
      padding: 14px 40px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      font-size: 16px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .login-required-btn:hover {
      background: var(--accent-dark);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }
  </style>
</head>
<body>

  <div class="navbar">
  <div class="logo">
  <a href="main.php" style="text-decoration: none;">
    <img src="images/Pawagnic Supplies logo.png" alt="Pawganic Logo" style="height: 50px; width: auto;">
  </a>
</div>

    <div class="nav-links">
      <a href="#">Home</a>
      <a href="shop.php">Shop</a>
      <a href="about.php">About</a>
  

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

  <!-- Hero Section with Slideshow -->
  <section class="hero">
    <div class="slideshow-container">
      <img class="slide" src="banner/banner1.png" alt="Cat Treats">
      <img class="slide" src="banner/banner2.png" alt="Cat Enjoying Treats">
      <img class="slide" src="banner/banner3.png" alt="Premium Cat Treats">
      <img class="slide" src="banner/banner4.jpg" alt="Premium Cat Treats">
    </div>
    <div class="hero-content">
      <h1>Pawganic Supplies</h1>
      <p>Premium treats your feline friends can't resist. Made with love, approved by cats.</p>
      <a href="shop.php" class="cta-button">Shop Treats</a>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features">
    <h2 class="section-title">Why Cats Love Us</h2>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-paw"></i>
        </div>
        <h3>All-Natural Ingredients</h3>
        <p>Our treats are made with 100% natural ingredients, carefully selected to provide both taste and nutrition for your feline companion.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-heart"></i>
        </div>
        <h3>Vet Approved</h3>
        <p>Each treat recipe is developed alongside veterinary nutritionists to ensure they support your cat's health while being irresistibly delicious.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-shipping-fast"></i>
        </div>
        <h3>Fresh Delivery</h3>
        <p>We bake in small batches and ship directly to your door to ensure maximum freshness that keeps your cat purring for more.</p>
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="stats-section">
    <div class="stat-item">
      <div class="stat-number">10K+</div>
      <div class="stat-label">Happy Cats</div>
    </div>
    <div class="stat-item">
      <div class="stat-number">50K+</div>
      <div class="stat-label">Treats Sold</div>
    </div>
    <div class="stat-item">
      <div class="stat-number">4.9★</div>
      <div class="stat-label">Customer Rating</div>
    </div>
    <div class="stat-item">
      <div class="stat-number">24/7</div>
      <div class="stat-label">Support</div>
    </div>
  </section>

  <!-- Promotional Banner -->
  <section class="promo-banner">
    <div class="promo-content">
      <h2>🎉 Summer Treats Sale!</h2>
      <p>Treat your feline friend to premium quality treats at unbeatable prices</p>
      <span class="discount-badge">Get 20% OFF</span>
      <br>
      <a href="shop.php" class="promo-cta">Shop Now</a>
    </div>
  </section>

  <!-- Testimonials Section -->
  <section class="testimonials-section">
    <h2 class="section-title">What Cat Parents Say</h2>
    <div class="testimonials-grid">
      <div class="testimonial-card">
        <div class="testimonial-stars">★★★★★</div>
        <p class="testimonial-text">My fluffy won't stop meowing for these treats! They're absolutely delicious and made with such care. Highly recommended!</p>
        <div class="testimonial-author">Maria Santos</div>
        <div class="testimonial-role">Verified Buyer</div>
      </div>

      <div class="testimonial-card">
        <div class="testimonial-stars">★★★★★</div>
        <p class="testimonial-text">The quality is amazing and my vet approved them immediately. I love that they use only natural ingredients. Best investment for my cat's happiness!</p>
        <div class="testimonial-author">Juan Dela Cruz</div>
        <div class="testimonial-role">Pet Owner</div>
      </div>

      <div class="testimonial-card">
        <div class="testimonial-stars">★★★★★</div>
        <p class="testimonial-text">Finally found treats that my picky eater loves! The salmon bites are her absolute favorite. Customer service is top-notch too!</p>
        <div class="testimonial-author">Ana Reyes</div>
        <div class="testimonial-role">Cat Lover</div>
      </div>
    </div>
  </section>

  <!-- FAQ Section with Enhanced Accordion -->
  <section class="features" id="faq">
    <div style="max-width: 1400px; margin: 0 auto;">
      <h2 class="section-title" style="text-align: center; margin-bottom: 50px; color: var(--accent-dark);">
        <i class="fas fa-question-circle" style="color: var(--accent); margin-right: 12px;"></i>Frequently Asked Questions
      </h2>

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Accordion visible only to logged-in users -->
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
      <?php else: ?>
        <!-- Login prompt for non-logged-in users -->
        <div class="login-required">
          <div>
            <i class="fas fa-lock"></i>
            <h3>Unlock Premium FAQs</h3>
            <p>Log in to your account to access detailed answers to all your questions about our premium cat treats and delivery information.</p>
            <a href="login.php" class="login-required-btn">Log In Now</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Final CTA Section -->
  <section class="cta-section">
    <h2>Ready to Delight Your Feline Friend?</h2>
    <p>Join thousands of happy cat parents who trust Pawganic Supplies for premium, natural treats. Your cat will thank you!</p>
    <a href="shop.php" class="cta-btn">Start Shopping Now</a>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>About Pawganic Supplies</h3>
        <p> Since 2020, Pawganic Supplies has been on a mission to delight cats with premium, health-conscious treats crafted by devoted cat lovers to support feline wellness in every bite.</p>
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

      // Slideshow functionality
      let currentSlide = 0;
      const slides = document.querySelectorAll('.slide');

      function showSlide(index) {
        slides.forEach((slide, i) => {
          slide.classList.remove('active');
          if (i === index) {
            slide.classList.add('active');
          }
        });
      }

      function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
      }

      // Initialize slideshow
      showSlide(currentSlide);
      setInterval(nextSlide, 5000); // Rotate every 5 seconds

      // Intersection Observer for fade-in animations
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            observer.unobserve(entry.target);
          }
        });
      }, observerOptions);

      // Observe cards and sections for fade-in animation
      const cardsAndSections = document.querySelectorAll('.feature-card, .product-card, .testimonial-card, .stat-item');
      cardsAndSections.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
      });

      // Counter animation for stats
      const stats = document.querySelectorAll('.stat-number');
      const statsSection = document.querySelector('.stats-section');
      let statsAnimated = false;

      const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !statsAnimated) {
            statsAnimated = true;
            stats.forEach(stat => {
              const text = stat.textContent;
              const number = parseInt(text.match(/\d+/)[0]);
              let current = 0;
              const increment = number / 50;
              
              const counter = setInterval(() => {
                current += increment;
                if (current >= number) {
                  stat.textContent = text;
                  clearInterval(counter);
                } else {
                  stat.textContent = Math.floor(current) + text.replace(/\d+/, '');
                }
              }, 30);
            });
          }
        });
      }, { threshold: 0.5 });

      if (statsSection) {
        statsObserver.observe(statsSection);
      }
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