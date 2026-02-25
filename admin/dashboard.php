<?php
session_start();
require_once '../config/db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$today = date('Y-m-d');
$stmt_sales = $pdo->prepare("SELECT SUM(total_price) as total_today FROM sales WHERE DATE(sale_date) = ?");
$stmt_sales->execute([$today]);
$total_sales_today = $stmt_sales->fetch()['total_today'] ?? 0;

$stmt_sacks = $pdo->prepare("SELECT SUM(quantity) as sacks_today FROM sales WHERE unit_type = 'Sack' AND DATE(sale_date) = ?");
$stmt_sacks->execute([$today]);
$total_sacks_today = $stmt_sacks->fetch()['sacks_today'] ?? 0;

$stmt_stock = $pdo->query("SELECT SUM(current_stock_kg) as total_kg FROM products");
$remaining_stock = $stmt_stock->fetch()['total_kg'] ?? 0;

$stmt_low = $pdo->query("SELECT rice_type, current_stock_kg FROM products WHERE current_stock_kg < 50");
$low_stock_items = $stmt_low->fetchAll();

$stmt_recent = $pdo->query("SELECT s.*, p.rice_type FROM sales s JOIN products p ON s.product_id = p.product_id ORDER BY s.sale_date DESC LIMIT 5");
$recent_sales = $stmt_recent->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Rice ni Mang Kanor Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 12px; }
        .main-content { flex-grow: 1; padding: 30px; }
        .alert-low-stock { border-left: 5px solid #dc3545; }
    </style>
</head>
<body class="d-flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Business Overview</h2>
                <div class="text-end">
                    <p class="mb-0 text-muted"><?php echo date('l, F j, Y'); ?></p>
                    <span class="badge bg-dark">Admin: <?php echo $_SESSION['full_name']; ?></span>
                </div>
            </div>

            <?php if (!empty($low_stock_items)): ?>
            <div class="alert alert-warning alert-dismissible fade show alert-low-stock shadow-sm mb-4">
                <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Stock Warning!</h5>
                <p class="mb-0">The following varieties are running low (below 50kg): 
                    <?php foreach($low_stock_items as $item): ?>
                        <span class="badge bg-danger ms-2"><?php echo htmlspecialchars($item['rice_type']); ?> (<?php echo $item['current_stock_kg']; ?>kg)</span>
                    <?php endforeach; ?>
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card p-4 bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white p-3 rounded-3 me-3"><i class="bi bi-cash-coin fs-4"></i></div>
                            <div>
                                <p class="text-muted mb-0 small text-uppercase fw-bold">Daily Revenue</p>
                                <h3 class="fw-bold mb-0">₱ <?php echo number_format($total_sales_today, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning text-white p-3 rounded-3 me-3"><i class="bi bi-bag-check fs-4"></i></div>
                            <div>
                                <p class="text-muted mb-0 small text-uppercase fw-bold">Sacks Sold Today</p>
                                <h3 class="fw-bold mb-0"><?php echo number_format($total_sacks_today); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="bg-success text-white p-3 rounded-3 me-3"><i class="bi bi-box-seam fs-4"></i></div>
                            <div>
                                <p class="text-muted mb-0 small text-uppercase fw-bold">Warehouse Stock</p>
                                <h3 class="fw-bold mb-0"><?php echo number_format($remaining_stock, 1); ?> kg</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-4 shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Recent Sales Activity</h5>
                    <a href="reports.php" class="btn btn-sm btn-link text-decoration-none">View All Reports</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Variety</th>
                                <th>Unit Type</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_sales as $sale): ?>
                            <tr>
                                <td class="text-muted small"><?php echo date('M d, Y h:i A', strtotime($sale['sale_date'])); ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($sale['rice_type']); ?></td>
                                <td><span class="badge rounded-pill bg-light text-dark border"><?php echo $sale['unit_type']; ?></span></td>
                                <td><?php echo $sale['quantity']; ?></td>
                                <td class="fw-bold text-success">₱ <?php echo number_format($sale['total_price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recent_sales)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No sales yet. Get started at the POS!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>