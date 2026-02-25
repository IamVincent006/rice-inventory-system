<?php
session_start();
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // HYBRID PASSWORD CHECK
        $is_valid = false;
        if ($user) {
            if ($user['role'] === 'admin') {
                $is_valid = password_verify($password, $user['password']); // Secure check for Admin
            } else {
                $is_valid = ($password === $user['password']); // Plain text check for User
            }
        }

        if ($is_valid) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../user/pos.php");
            }
            exit();
        } else {
            header("Location: ../index.php?error=1");
            exit();
        }
    } catch(PDOException $e) {
        die("System Error: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>