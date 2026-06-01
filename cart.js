// Function to remove an item from the cart
function removeFromCart(productId) {
    fetch('cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&product_id=${productId}`
    })
    .then(res => res.text())
    .then(() => {
        updateCartDisplay();
    });
}

// Function to update the quantity of a cart item
function updateQuantity(productId, quantity) {
    fetch('cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&product_id=${productId}&quantity=${quantity}`
    })
    .then(res => res.text())
    .then(() => {
        updateCartDisplay();
    });
}

// Function to refresh the cart display after an update
function updateCartDisplay() {
    fetch('cart_contents.php')
        .then(res => res.text())
        .then(html => {
            document.getElementById('cartContents').innerHTML = html;
        });
}

// Trigger to load the cart when the page is loaded
document.addEventListener('DOMContentLoaded', updateCartDisplay);
