<?php
session_start();
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'admin') {
    $user_id = $_POST['user_id'];
    
    try {
        // Find out if the account being updated is an admin or user
        $stmt_role = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
        $stmt_role->execute([$user_id]);
        $user_data = $stmt_role->fetch();

        // Apply correct security based on role
        if ($user_data['role'] === 'admin') {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        } else {
            $new_password = $_POST['new_password']; // Save as plain text
        }

        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$new_password, $user_id]);
        
        header("Location: settings.php?pw_success=1");
        exit();
    } catch(PDOException $e) {
        header("Location: settings.php?error=Failed to change password: " . $e->getMessage());
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>