<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shopping Cart</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    #cart-toggle-btn {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 999;
      background-color: #007bff;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 20px;
      cursor: pointer;
      font-size: 16px;
    }

    .slide-cart {
      position: fixed;
      top: 0;
      right: -400px;
      width: 400px;
      height: 100%;
      background: #fff;
      box-shadow: -4px 0 10px rgba(0, 0, 0, 0.2);
      transition: right 0.3s ease;
      z-index: 998;
      padding: 20px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
    }

    .slide-cart h2 {
      margin-bottom: 20px;
      border-bottom: 2px solid #007bff;
      padding-bottom: 10px;
      color: #007bff;
    }

    .cart-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
    }

    .cart-item .item-name {
      font-weight: bold;
    }

    .cart-item .item-price {
      color: #28a745;
    }

    .cart-footer {
      margin-top: auto;
      border-top: 2px solid #ddd;
      padding-top: 20px;
    }

    .cart-footer button {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 10px;
      cursor: pointer;
      font-size: 16px;
      width: 100%;
    }

    .cart-footer button:hover {
      background-color: #218838;
    }

    .empty-cart {
      text-align: center;
      color: #777;
      font-style: italic;
    }
  </style>
</head>
<body>

<!-- Cart Toggle Button -->
<button onclick="toggleCart()" id="cart-toggle-btn">🛒 View Cart</button>

<!-- Slide-In Cart Panel -->
<div class="slide-cart" id="slideCart">
  <h2>Your Cart</h2>
  <div id="cartContents">
    <!-- Example Items -->
    <div class="cart-item">
      <div>
        <div class="item-name">Product A</div>
        <div class="item-price">₱500</div>
      </div>
      <div>Qty: 1</div>
    </div>

    <div class="cart-item">
      <div>
        <div class="item-name">Product B</div>
        <div class="item-price">₱250</div>
      </div>
      <div>Qty: 2</div>
    </div>

    <!-- If cart is empty, show this instead -->
    <!-- <p class="empty-cart">Your cart is empty.</p> -->
  </div>

  <div class="cart-footer">
    <button>Checkout</button>
  </div>
</div>

<!-- JavaScript for Toggling Cart -->
<script>
  function toggleCart() {
    const cart = document.getElementById('slideCart');
    cart.style.right = cart.style.right === '0px' ? '-400px' : '0px';
  }
</script>

</body>
</html>
