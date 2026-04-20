<?php
// register.php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PureVital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-wrapper">
    <div class="auth-card glass">
        <h2>Join PureVital</h2>
        <form id="registerForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="name" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="email" required placeholder="name@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" required placeholder="••••••••">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select id="role">
                    <option value="buyer">Buyer</option>
                    <option value="seller">Seller</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>
        <p class="text-center" style="margin-top: 1.5rem; color: var(--text-muted);">
            Already have an account? <a href="login.php" style="color: var(--primary);">Login</a>
        </p>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
