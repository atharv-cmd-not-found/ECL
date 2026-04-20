<?php
// api/auth.php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($action === 'register') {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'buyer';

        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['error' => 'All fields are required.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Invalid email format.']);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $role]);
            echo json_encode(['success' => 'Registration successful. Please login.']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['error' => 'Email already exists.']);
            } else {
                echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
            }
        }

    } elseif ($action === 'login') {
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['error' => 'Email and password are required.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            echo json_encode(['success' => 'Login successful', 'role' => $user['role']]);
        } else {
            echo json_encode(['error' => 'Invalid email or password.']);
        }
    }
} elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => 'Logged out successfully']);
} elseif ($action === 'status') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'isLoggedIn' => true,
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ]);
    } else {
        echo json_encode(['isLoggedIn' => false]);
    }
}
?>
