<?php
session_start();
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $unit_type  = $_POST['unit_type']; 
    $quantity   = $_POST['qty'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        // Calculate Price & Weight based on the selected unit
        $weight_sub = 0; $unit_price = 0;
        if ($unit_type === 'Kilo') { 
            $weight_sub = 1 * $quantity; 
            $unit_price = $product['price_kilo']; 
        } elseif ($unit_type === 'Half-Sack') { 
            $weight_sub = 25 * $quantity; 
            $unit_price = $product['price_half_sack']; 
        } elseif ($unit_type === 'Sack') { 
            $weight_sub = 50 * $quantity; 
            $unit_price = $product['price_sack']; 
        }

        $total_price = $unit_price * $quantity;

        // Check if there is enough stock before allowing the sale
        if ($product['current_stock_kg'] < $weight_sub) {
            header("Location: pos.php?error=Not enough stock to complete this order!");
            exit();
        }

        // Execute Database Transaction
        $pdo->beginTransaction();
        
        $stmt_sale = $pdo->prepare("INSERT INTO sales (product_id, user_id, unit_type, quantity, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt_sale->execute([$product_id, $_SESSION['user_id'], $unit_type, $quantity, $total_price]);
        
        $stmt_stock = $pdo->prepare("UPDATE products SET current_stock_kg = current_stock_kg - ? WHERE product_id = ?");
        $stmt_stock->execute([$weight_sub, $product_id]);
        
        $pdo->commit();

        // Redirect back with a simple success message
        header("Location: pos.php?success=1");
        exit();

    } catch(Exception $e) { 
        $pdo->rollBack(); 
        die("Error processing sale: " . $e->getMessage()); 
    }
}
?>