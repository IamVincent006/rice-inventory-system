<?php
session_start();
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'admin') {
    $id = $_POST['product_id'];
    $added_weight = $_POST['added_weight'];

    try {
        $sql = "UPDATE products SET current_stock_kg = current_stock_kg + ? WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$added_weight, $id]);
        header("Location: inventory.php?restock_success=1");
        exit();
    } catch(PDOException $e) {
        die("Error updating stock: " . $e->getMessage());
    }
}
?>