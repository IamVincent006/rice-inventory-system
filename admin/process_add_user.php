<?php
session_start();
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'admin') {
    $name = $_POST['full_name'];
    $user = $_POST['username'];
    $role = $_POST['role'];
    
    // HYBRID SAVING
    if ($role === 'admin') {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    } else {
        $pass = $_POST['password']; // Plain text for users
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $user, $pass, $role]);
        header("Location: settings.php?success=Account Created");
        exit();
    } catch(PDOException $e) { 
        header("Location: settings.php?error=Error: " . $e->getMessage()); 
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>