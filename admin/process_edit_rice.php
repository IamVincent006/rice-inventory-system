<?php
session_start();
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'admin') {
    $id = $_POST['product_id'];
    $name = $_POST['rice_type'];
    $stock = $_POST['current_stock_kg']; // Allows manual correction of stock
    $pk = $_POST['p_kilo'];
    $ph = $_POST['p_half'];
    $ps = $_POST['p_sack'];

    try {
        $sql = "UPDATE products SET rice_type=?, current_stock_kg=?, price_kilo=?, price_half_sack=?, price_sack=? WHERE product_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $stock, $pk, $ph, $ps, $id]);
        
        header("Location: inventory.php?edit_success=1");
        exit();
    } catch(PDOException $e) {
        header("Location: inventory.php?edit_error=1");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>