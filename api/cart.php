<?php
// api/cart.php
require_once __DIR__ . '/../config/db.php';
session_start();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Global Auth Check for Cart
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $action === 'get') {
        echo json_encode(['error' => 'Please login to use the cart', 'login_required' => true]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'add') {
        $productId = $data['product_id'] ?? null;
        if (!$productId) {
            echo json_encode(['error' => 'Product ID missing']);
            exit;
        }

        // Check if product exists and is in stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            echo json_encode(['error' => 'Product not found']);
            exit;
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]++;
        } else {
            $_SESSION['cart'][$productId] = 1;
        }

        echo json_encode(['success' => 'Added to cart', 'cart_count' => array_sum($_SESSION['cart'])]);
    } elseif ($action === 'remove') {
        $productId = $data['product_id'] ?? null;
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
        echo json_encode(['success' => 'Removed from cart']);
    } elseif ($action === 'update') {
        $productId = $data['product_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = max(1, $quantity);
        }
        echo json_encode(['success' => 'Cart updated']);
    }
} elseif ($action === 'get') {
    $cartData = [];
    if (!empty($_SESSION['cart'])) {
        $ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();

        foreach ($products as $p) {
            $p['quantity'] = $_SESSION['cart'][$p['id']];
            $cartData[] = $p;
        }
    }
    echo json_encode($cartData);
}
?>
