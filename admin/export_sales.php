<?php
session_start();
require_once '../config/db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$stmt = $pdo->query("SELECT s.sale_date, u.full_name as seller, p.rice_type, s.unit_type, s.quantity, s.total_price 
                    FROM sales s 
                    JOIN products p ON s.product_id = p.product_id 
                    JOIN users u ON s.user_id = u.user_id 
                    ORDER BY s.sale_date DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "Rice_Sales_Report_" . date('Y-m-d_H-i') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');
fputcsv($output, array('Date/Time', 'Seller', 'Rice Variety', 'Unit Type', 'Quantity', 'Total Price (PHP)'));

if (count($rows) > 0) {
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit();
?>