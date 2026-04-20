<?php
// api/products.php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    // Only allow GET for everyone if it's listing
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($action === 'create' || $action === 'update') {
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $price = $data['price'] ?? 0;
        $stock = $data['stock'] ?? 0;
        $category_id = $data['category_id'] ?? 0;
        $image_url = $data['image_url'] ?? '';

        if (empty($name) || empty($price) || empty($category_id)) {
            echo json_encode(['error' => 'Name, price, and category are required.']);
            exit;
        }

        try {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $stock, $category_id, $image_url]);
                echo json_encode(['success' => 'Product created successfully']);
            } else {
                $id = $data['id'] ?? 0;
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $stock, $category_id, $image_url, $id]);
                echo json_encode(['success' => 'Product updated successfully']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    } elseif ($action === 'delete') {
        $id = $data['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => 'Product deleted successfully']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'categories') {
        $stmt = $pdo->query("SELECT * FROM categories");
        echo json_encode($stmt->fetchAll());
    }
}
?>
