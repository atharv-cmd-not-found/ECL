<?php
// buyer/cart.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - PureVital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="container glass">
        <a href="../index.php" class="logo">PureVital</a>
        <ul class="nav-links">
            <li><a href="../index.php">Shop</a></li>
            <li><a href="orders.php">My Orders</a></li>
            <li><a href="#" id="logoutBtn">Logout</a></li>
        </ul>
    </nav>

    <main class="container mb-2">
        <h2 style="margin: 2rem 0;">Shopping Cart</h2>
        <div id="cartContent">
            <!-- Loaded via AJAX -->
            <div class="glass" style="padding: 3rem; text-align: center;">
                <p>Loading your cart...</p>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        async function loadCart() {
            const res = await fetch('../api/cart.php?action=get');
            const cartItems = await res.json();
            const container = document.getElementById('cartContent');

            if (cartItems.length === 0) {
                container.innerHTML = `
                    <div class="glass" style="padding: 3rem; text-align: center;">
                        <p style="margin-bottom: 2rem;">Your cart is empty.</p>
                        <a href="../index.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                `;
                return;
            }

            let total = 0;
            let html = `
                <div class="glass" style="padding: 2rem; border-radius: 1rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                                <th style="padding: 1rem;">Product</th>
                                <th style="padding: 1rem;">Price</th>
                                <th style="padding: 1rem;">Quantity</th>
                                <th style="padding: 1rem;">Subtotal</th>
                                <th style="padding: 1rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            cartItems.forEach(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                html += `
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem;">${item.name}</td>
                        <td style="padding: 1rem;">₹${parseFloat(item.price).toFixed(2)}</td>
                        <td style="padding: 1rem;">
                            <input type="number" value="${item.quantity}" min="1" style="width: 60px;" onchange="updateQty(${item.id}, this.value)">
                        </td>
                        <td style="padding: 1rem;">₹${subtotal.toFixed(2)}</td>
                        <td style="padding: 1rem;">
                            <button onclick="removeItem(${item.id})" style="background: none; border: none; color: var(--error); cursor: pointer;">Remove</button>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                    <div style="margin-top: 2rem; text-align: right;">
                        <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Total: ₹${total.toFixed(2)}</h3>
                        <a href="checkout.php" class="btn btn-primary" style="padding: 1rem 3rem;">Proceed to Checkout</a>
                    </div>
                </div>
            `;
            container.innerHTML = html;
        }

        async function updateQty(productId, quantity) {
            await fetch('../api/cart.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity })
            });
            loadCart();
        }

        async function removeItem(productId) {
            await fetch('../api/cart.php?action=remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            });
            loadCart();
        }

        loadCart();
    </script>
</body>
</html>
