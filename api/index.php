<?php
// index.php - Landing Page / Home
require_once __DIR__ . '/../config/db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PureVital - Health & Supplements</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="container glass">
        <a href="index.php" class="logo">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
            PureVital
        </a>
        <ul class="nav-links">
            <li><a href="/">Shop</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_role'] === 'seller'): ?>
                    <li><a href="seller/dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="buyer/orders.php">My Orders</a></li>
                    <li><a href="buyer/cart.php">Cart</a></li>
                <?php endif; ?>
                <li><a href="#" id="logoutBtn">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
            <?php else: ?>
                <li><a href="/login">Login</a></li>
                <li><a href="/register">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <header class="container" style="padding: 4rem 0; text-align: center;">
        <h1 style="font-size: 3.5rem; margin-bottom: 1rem;">Premium Supplements for <span style="color: var(--primary);">Maximum Performance</span></h1>
        <p style="color: var(--text-muted); font-size: 1.2rem; max-width: 600px; margin: 0 auto 2rem;">Fuel your body with science-backed nutrition. Shop our curated collection of high-quality health supplements.</p>
        <a href="#products" class="btn btn-primary">Browse Shop</a>
    </header>

    <main class="container" id="products">
        <h2 class="mb-2">Featured Products</h2>
        <div class="grid" id="productGrid">
            <?php
            $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id");
            $products = $stmt->fetchAll();

            if (empty($products)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem;" class="glass">
                    <p>No products available yet. Check back soon!</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <div class="product-card glass">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($p['image_url'] ?: 'https://via.placeholder.com/300x300?text=Premium+Supplement'); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($p['category_name']); ?></div>
                            <h3 class="product-title"><?php echo htmlspecialchars($p['name']); ?></h3>
                            <div class="product-price">₹<?php echo number_format($p['price'], 2); ?></div>
                            <button class="btn btn-primary btn-block add-to-cart" data-id="<?php echo $p['id']; ?>">Add to Cart</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="container">
        <p>&copy; 2026 PureVital Health Supplements. Built with Excellence.</p>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>
