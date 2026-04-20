<?php
// buyer/checkout.php
require_once __DIR__ . '/../../config/db.php';
session_start();
require_once __DIR__ . '/../../config/razorpay.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header('Location: /');
    exit;
}

// Calculate total
$total = 0;
$ids = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($ids) - 1) . '?';
$stmt = $pdo->prepare("SELECT price, id FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

foreach ($products as $p) {
    $total += $p['price'] * $_SESSION['cart'][$p['id']];
}

// In a real app, you'd create a Razorpay order here via their SDK/API
// For this demo, we'll simulate the order ID generation or use a placeholder
$razorpay_order_id = 'order_' . bin2hex(random_bytes(8));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - PureVital</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <nav class="container glass">
        <a href="/" class="logo">PureVital</a>
    </nav>

    <main class="auth-wrapper" style="padding: 2rem 0;">
        <div class="auth-card glass text-center" style="max-width: 500px;">
            <h2 style="margin-bottom: 2rem;">Checkout Details</h2>
            
            <div style="text-align: left; margin-bottom: 2rem;">
                <div class="form-group">
                    <label>Shipping Address</label>
                    <textarea id="shipping_address" rows="3" placeholder="Enter your full address" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem;"></textarea>
                </div>
                <div class="form-group">
                    <label>Preferred Delivery Time</label>
                    <select id="delivery_time" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem;">
                        <option value="Flexible">Flexible (Anytime)</option>
                        <option value="Morning (9 AM - 12 PM)">Morning (9 AM - 12 PM)</option>
                        <option value="Afternoon (12 PM - 4 PM)">Afternoon (12 PM - 4 PM)</option>
                        <option value="Evening (4 PM - 8 PM)">Evening (4 PM - 8 PM)</option>
                    </select>
                </div>
            </div>

            <div style="font-size: 1.1rem; margin-bottom: 2rem; padding: 1rem; background: rgba(var(--primary-rgb), 0.1); border-radius: 0.5rem;">
                <p style="color: var(--text-muted); margin-bottom: 0.5rem;">Amount to Pay:</p>
                <p style="font-size: 2rem; font-weight: 700; color: var(--primary);">₹<?php echo number_format($total, 2); ?></p>
            </div>
            
            <button id="payBtn" class="btn btn-primary btn-block" style="padding: 1.25rem;">Pay Now with Razorpay</button>
            <p style="margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">Secure payment powered by Razorpay</p>
        </div>
    </main>

    <script>
        document.getElementById('payBtn').onclick = function(e) {
            const address = document.getElementById('shipping_address').value;
            if (!address) {
                alert('Please enter your shipping address.');
                return;
            }

            var options = {
                "key": "<?php echo RAZORPAY_KEY_ID; ?>",
                "amount": "<?php echo $total * 100; ?>", 
                "currency": "INR",
                "name": "PureVital Supplements",
                "description": "Purchase from PureVital",
                "order_id": "<?php echo $razorpay_order_id; ?>",
                "handler": function (response){
                    // Pass address and time along with payment response
                    response.shipping_address = address;
                    response.delivery_time = document.getElementById('delivery_time').value;
                    verifyPayment(response);
                },
                "prefill": {
                    "name": "<?php echo $_SESSION['user_name']; ?>",
                    "email": "test@example.com"
                },
                "theme": {
                    "color": "#10B981"
                }
            };
            var rzp1 = new Razorpay(options);
            rzp1.open();
            e.preventDefault();
        }

        async function verifyPayment(response) {
            const res = await fetch('/api/payment.php?action=verify', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(response)
            });
            const data = await res.json();
            if (data.success) {
                alert('Payment Successful! Your order has been placed.');
                window.location.href = '/buyer/orders';
            } else {
                alert('Payment verification failed: ' + data.error);
            }
        }
    </script>
</body>
</html>
