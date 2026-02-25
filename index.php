<?php
session_start();
require_once 'config/db_config.php';

// If user is already logged in, redirect them based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/pos.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mang Kanor Rice Store | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #212529; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: 'Inter', sans-serif; }
        .login-card { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
    </style>
</head>
<body>

    <div class="login-card">
        <h3 class="text-center mb-4 fw-bold">Rice ni Mang Kanor Pro</h3>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center small">Invalid username or password.</div>
        <?php endif; ?>

        <form action="auth/login_process.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required placeholder="Enter username">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn btn-dark w-100 py-2">Sign In</button>
        </form>
    </div>

</body>
</html>