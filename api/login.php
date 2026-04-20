<?php
// login.php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PureVital</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-wrapper">
    <div class="auth-card glass">
        <h2>Welcome Back</h2>
        <form id="loginForm">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="email" required placeholder="name@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <p class="text-center" style="margin-top: 1.5rem; color: var(--text-muted);">
            Don't have an account? <a href="/register" style="color: var(--primary);">Register</a>
        </p>
    </div>
    <script src="/assets/js/main.js"></script>
</body>
</html>
