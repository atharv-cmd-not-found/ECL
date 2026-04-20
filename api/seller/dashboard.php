<?php
// seller/dashboard.php
require_once __DIR__ . '/../../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header('Location: /login');
    exit;
}

// Fetch seller-specific stats or all products for now
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - PureVital</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="container glass">
        <a href="/" class="logo">PureVital Seller</a>
        <ul class="nav-links">
            <li><a href="dashboard.php">Inventory</a></li>
            <li><a href="manage_products.php" class="btn btn-primary" style="padding: 0.5rem 1rem;">Add Product</a></li>
            <li><a href="#" id="logoutBtn">Logout</a></li>
        </ul>
    </nav>

    <main class="container mb-2">
        <h2 style="margin: 2rem 0;">Inventory Management</h2>
        
        <div class="glass" style="padding: 2rem; border-radius: 1rem; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; color: var(--text);">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                        <th style="padding: 1rem;">Product</th>
                        <th style="padding: 1rem;">Category</th>
                        <th style="padding: 1rem;">Price</th>
                        <th style="padding: 1rem;">Stock</th>
                        <th style="padding: 1rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="5" style="padding: 2rem; text-align: center;">No products found. Start by adding one!</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($p['name']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($p['category_name']); ?></td>
                                <td style="padding: 1rem;">₹<?php echo number_format($p['price'], 2); ?></td>
                                <td style="padding: 1rem;"><?php echo $p['stock']; ?> units</td>
                                <td style="padding: 1rem;">
                                    <a href="manage_products.php?id=<?php echo $p['id']; ?>" style="color: var(--secondary); text-decoration: none; margin-right: 1rem;">Edit</a>
                                    <button class="delete-btn" data-id="<?php echo $p['id']; ?>" style="background: none; border: none; color: var(--error); cursor: pointer;">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="/assets/js/main.js"></script>
    <script>
        // Seller specific logic
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (confirm('Are you sure you want to delete this product?')) {
                    const id = btn.getAttribute('data-id');
                    const res = await fetch('/api/products.php?action=delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const data = await res.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error);
                    }
                }
            });
        });
    </script>
</body>
</html>
