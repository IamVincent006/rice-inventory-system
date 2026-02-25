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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard | Our Bigasan</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --harvest-gold: #facc15;
            --surface: #ffffff;
            --bg-gray: #f8fafc;
            --dark-blue: #0f172a;
            --transition: all 0.3s ease;
        }

        body { 
            background-color: var(--bg-gray); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--dark-blue);
            margin: 0;
        }

        /* Responsive Main Content Logic */
        .main-content { 
            flex-grow: 1; 
            padding: 1.5rem; 
            margin-left: 280px; /* Aligned with sidebar */
            transition: margin 0.3s ease, padding 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .main-content { margin-left: 0; padding: 1rem; padding-top: 80px; }
        }

        .stat-card {
            border: none;
            border-radius: 1.5rem;
            transition: var(--transition);
            background: var(--surface);
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .icon-box {
            width: 54px; height: 54px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 1rem;
            flex-shrink: 0;
        }

        .alert-low-stock { 
            border: none;
            background: #fff1f2;
            color: #9f1239;
            border-left: 6px solid #e11d48;
            border-radius: 1.25rem;
        }

        .table-card {
            border-radius: 1.5rem;
            border: none;
            overflow: hidden;
        }

        .table thead th {
            background: #f1f5f9;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem 1.25rem;
        }

        .table tbody td {
            padding: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap; /* Prevents text wrapping on small screens, triggering horizontal scroll instead */
        }

        .badge-variety {
            background: rgba(15, 23, 42, 0.05);
            color: var(--dark-blue);
            font-weight: 700;
        }
    </style>
</head>
<body class="d-flex flex-column flex-lg-row">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-5 gap-3">
                <div>
                    <h2 class="fw-800 mb-1 fs-3">Business Overview</h2>
                    <p class="text-secondary mb-0 small"><?php echo date('l, F d, Y'); ?></p>
                </div>
                <div>
                    <span class="badge bg-white text-dark shadow-sm p-3 rounded-4 border w-100 w-sm-auto text-start">
                        <i class="ph-fill ph-shield-check text-success me-2"></i> 
                        <?php echo explode(' ', $_SESSION['full_name'])[0]; ?> (Admin)
                    </span>
                </div>
            </div>

            <?php if (!empty($low_stock_items)): ?>
            <div class="alert alert-low-stock shadow-sm mb-5 p-4 fade show border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="pe-3">
                        <h6 class="fw-800 mb-2 d-flex align-items-center">
                            <i class="ph-fill ph-warning-octagon me-2"></i> 
                            Low Stock Alert
                        </h6>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <?php foreach($low_stock_items as $item): ?>
                                <span class="badge bg-danger rounded-pill px-3 py-2" style="font-size: 0.7rem;">
                                    <?php echo htmlspecialchars($item['rice_type']); ?> (<?php echo $item['current_stock_kg']; ?>kg)
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <?php endif; ?>

            <div class="row g-3 g-md-4 mb-5">
                <div class="col-12 col-md-4">
                    <div class="card stat-card p-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success-subtle text-success me-3">
                                <i class="ph-fill ph-currency-circle-dollar fs-3"></i>
                            </div>
                            <div>
                                <p class="text-secondary small fw-700 text-uppercase mb-0" style="font-size: 0.65rem; letter-spacing: 0.5px;">Today's Revenue</p>
                                <h4 class="fw-800 mb-0 text-dark">₱ <?php echo number_format($total_sales_today, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card stat-card p-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-warning-subtle text-warning me-3">
                                <i class="ph-fill ph-package fs-3"></i>
                            </div>
                            <div>
                                <p class="text-secondary small fw-700 text-uppercase mb-0" style="font-size: 0.65rem; letter-spacing: 0.5px;">Sacks Sold</p>
                                <h4 class="fw-800 mb-0 text-dark"><?php echo number_format($total_sacks_today); ?> <small class="fw-400 fs-6 opacity-50">pcs</small></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card stat-card p-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary-subtle text-primary me-3">
                                <i class="ph-fill ph-warehouse fs-3"></i>
                            </div>
                            <div>
                                <p class="text-secondary small fw-700 text-uppercase mb-0" style="font-size: 0.65rem; letter-spacing: 0.5px;">Current Stock</p>
                                <h4 class="fw-800 mb-0 text-dark"><?php echo number_format($remaining_stock, 1); ?> <small class="fw-400 fs-6 opacity-50">kg</small></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card table-card shadow-sm border-0 bg-white">
                <div class="p-4 bg-white border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <h5 class="fw-800 mb-0 fs-6">Recent Activity</h5>
                    <a href="reports.php" class="btn btn-sm px-4 rounded-pill btn-dark fw-700" style="font-size: 0.7rem;">
                        View Reports <i class="ph ph-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Variety</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_sales as $sale): ?>
                            <tr>
                                <td class="text-secondary small"><?php echo date('h:i A', strtotime($sale['sale_date'])); ?></td>
                                <td>
                                    <span class="badge badge-variety py-2 px-3 rounded-3" style="font-size: 0.75rem;"><?php echo htmlspecialchars($sale['rice_type']); ?></span>
                                </td>
                                <td><span class="small fw-600 text-muted"><?php echo $sale['unit_type']; ?></span></td>
                                <td class="fw-700"><?php echo $sale['quantity']; ?></td>
                                <td class="fw-800 text-dark text-end">₱ <?php echo number_format($sale['total_price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>