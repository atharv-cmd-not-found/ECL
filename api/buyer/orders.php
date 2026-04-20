<?php
// buyer/orders.php
require_once __DIR__ . '/../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$stmt = $pdo->prepare("SELECT o.*, 
    (SELECT GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR ', ') 
     FROM order_items oi 
     JOIN products p ON oi.product_id = p.id 
     WHERE oi.order_id = o.id) as item_details
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - PureVital</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="container glass">
        <a href="/" class="logo">PureVital</a>
        <ul class="nav-links">
            <li><a href="/">Shop</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="#" id="logoutBtn">Logout</a></li>
        </ul>
    </nav>

    <main class="container mb-2">
        <h2 style="margin: 2rem 0;">Order History</h2>
        
        <?php if (empty($orders)): ?>
            <div class="glass" style="padding: 3rem; text-align: center;">
                <p>You haven't placed any orders yet.</p>
                <a href="/" class="btn btn-primary" style="margin-top: 1rem;">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="grid" style="grid-template-columns: 1fr; gap: 1.5rem;">
                <?php foreach ($orders as $order): ?>
                    <div class="glass" style="padding: 1.5rem; border-radius: 1rem; border-left: 4px solid var(--primary);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <div>
                                <span style="color: var(--text-muted); font-size: 0.9rem;">Order ID:</span>
                                <span style="font-weight: 600;">#<?php echo $order['id']; ?></span>
                            </div>
                            <div style="text-align: right;">
                                <span style="background: var(--primary); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.8rem; font-weight: 700;">
                                    <?php echo strtoupper($order['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Items:</p>
                            <p><?php echo htmlspecialchars($order['item_details']); ?></p>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">Date:</p>
                                <p><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div style="text-align: right;">
                                <p style="color: var(--text-muted); font-size: 0.9rem;">Total Amount:</p>
                                <p style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">₹<?php echo number_format($order['total_amount'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="/assets/js/main.js"></script>
</body>
</html>
