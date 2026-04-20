<?php
// seller/manage_products.php
require_once __DIR__ . '/../../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header('Location: /login');
    exit;
}

$id = $_GET['id'] ?? null;
$product = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Edit' : 'Add'; ?> Product - PureVital</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="container glass">
        <a href="dashboard.php" class="logo">PureVital Seller</a>
        <ul class="nav-links">
            <li><a href="dashboard.php">Inventory</a></li>
            <li><a href="#" id="logoutBtn">Logout</a></li>
        </ul>
    </nav>

    <main class="auth-wrapper">
        <div class="auth-card glass" style="max-width: 600px;">
            <h2><?php echo $id ? 'Update' : 'Add New'; ?> Product</h2>
            <form id="productForm">
                <input type="hidden" id="p_id" value="<?php echo $id; ?>">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="name" required value="<?php echo $product['name'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select id="category_id">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($product['category_id']) && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (INR)</label>
                    <input type="number" step="0.01" id="price" required value="<?php echo $product['price'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" id="stock" required value="<?php echo $product['stock'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" id="image_url" placeholder="Paste image link here" value="<?php echo $product['image_url'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="description" rows="4" style="width: 100%;"><?php echo $product['description'] ?? ''; ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><?php echo $id ? 'Save Changes' : 'Create Product'; ?></button>
            </form>
        </div>
    </main>

    <script src="/assets/js/main.js"></script>
    <script>
        document.getElementById('productForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('p_id').value;
            const name = document.getElementById('name').value;
            const category_id = document.getElementById('category_id').value;
            const price = document.getElementById('price').value;
            const stock = document.getElementById('stock').value;
            const image_url = document.getElementById('image_url').value;
            const description = document.getElementById('description').value;

            const action = id ? 'update' : 'create';
            const res = await fetch('/api/products.php?action=' + action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, name, category_id, price, stock, image_url, description })
            });

            const data = await res.json();
            if (data.success) {
                alert(data.success);
                window.location.href = 'dashboard.php';
            } else {
                alert(data.error);
            }
        });
    </script>
</body>
</html>
