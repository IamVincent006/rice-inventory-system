<?php
session_start();
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'admin') {
    $name = $_POST['rice_type'];
    $stock = $_POST['initial_stock'];
    $pk = $_POST['p_kilo'];
    $ph = $_POST['p_half'];
    $ps = $_POST['p_sack'];

    try {
        $sql = "INSERT INTO products (rice_type, current_stock_kg, price_kilo, price_half_sack, price_sack) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $stock, $pk, $ph, $ps]);
        header("Location: inventory.php?success=1");
        exit();
    } catch(PDOException $e) {
        die("Error adding product: " . $e->getMessage());
    }
}
?>