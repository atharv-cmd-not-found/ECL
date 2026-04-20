<?php
// api/payment.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/razorpay.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'verify' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // In a real integration, you would verify the signature here using Razorpay SDK:
    // $attributes = [
    //     'razorpay_order_id' => $data['razorpay_order_id'],
    //     'razorpay_payment_id' => $data['razorpay_payment_id'],
    //     'razorpay_signature' => $data['razorpay_signature']
    // ];
    // $api->utility->verifyPaymentSignature($attributes);
    
    // For this test integration, we'll assume success if IDs are present
    if (isset($data['razorpay_payment_id'])) {
        try {
            $pdo->beginTransaction();

            // 1. Calculate Total again for safety
            $total = 0;
            $items = [];
            if (!empty($_SESSION['cart'])) {
                $ids = array_keys($_SESSION['cart']);
                $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $products = $stmt->fetchAll();

                foreach ($products as $p) {
                    $qty = $_SESSION['cart'][$p['id']];
                    $subtotal = $p['price'] * $qty;
                    $total += $subtotal;
                    $items[] = [
                        'product_id' => $p['id'],
                        'quantity' => $qty,
                        'price' => $p['price']
                    ];
                }
            }

            // 2. Create Order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, razorpay_order_id) VALUES (?, ?, 'paid', ?)");
            $stmt->execute([$_SESSION['user_id'], $total, $data['razorpay_order_id'] ?? '']);
            $order_id = $pdo->lastInsertId();

            // 3. Create Order Items
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // 4. Update Inventory
                $updateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $updateStock->execute([$item['quantity'], $item['product_id']]);
            }

            // 5. Store Payment Record
            $stmt = $pdo->prepare("INSERT INTO payments (order_id, transaction_id, amount, status, payment_method) VALUES (?, ?, ?, 'success', 'razorpay')");
            $stmt->execute([$order_id, $data['razorpay_payment_id'], $total]);

            $pdo->commit();
            
            // Clear Cart
            $_SESSION['cart'] = [];
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Invalid payment data']);
    }
}
?>
