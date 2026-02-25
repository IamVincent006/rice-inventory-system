<?php
session_start();
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'admin') {
    $product_id = $_POST['product_id'];

    try {
        // Attempt to delete the product
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Success redirect
        header("Location: inventory.php?delete_success=1");
        exit();

    } catch(PDOException $e) {
        // If it fails (usually because this product is linked to existing sales logs)
        header("Location: inventory.php?delete_error=1");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>