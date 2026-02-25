<?php
session_start();
require_once '../config/db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch all products for the dropdown filter
$stmt_products = $pdo->query("SELECT product_id, rice_type FROM products ORDER BY rice_type ASC");
$all_products = $stmt_products->fetchAll();

// ---------------------------------------------------------
// PAGINATION SETTINGS
// ---------------------------------------------------------
$limit = 50; // How many rows to show per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// ---------------------------------------------------------
// FILTERING LOGIC
// ---------------------------------------------------------
$where_clauses = ["1=1"];
$params = [];

if (!empty($_GET['start_date'])) {
    $where_clauses[] = "DATE(s.sale_date) >= ?";
    $params[] = $_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $where_clauses[] = "DATE(s.sale_date) <= ?";
    $params[] = $_GET['end_date'];
}
if (!empty($_GET['product_id'])) {
    $where_clauses[] = "s.product_id = ?";
    $params[] = $_GET['product_id'];
}

$where_sql = implode(" AND ", $where_clauses);

// 1. Get TOTAL count for Pagination & Total Sales Calculator
$sql_count = "SELECT COUNT(*) as total_rows, SUM(total_price) as grand_total 
              FROM sales s WHERE $where_sql";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$count_data = $stmt_count->fetch();
$total_rows = $count_data['total_rows'] ?? 0;
$total_filtered_sales = $count_data['grand_total'] ?? 0;
$total_pages = ceil($total_rows / $limit);

// 2. Fetch the actual records for THIS PAGE only (LIMIT & OFFSET)
$sql = "SELECT s.*, p.rice_type, u.full_name as seller 
        FROM sales s 
        JOIN products p ON s.product_id = p.product_id 
        JOIN users u ON s.user_id = u.user_id 
        WHERE $where_sql 
        ORDER BY s.sale_date DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Helper function to build pagination URLs without losing filters
function buildPageLink($pageNum) {
    $query = $_GET;
    $query['page'] = $pageNum;
    return '?' . http_build_query($query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales History | RiceStore Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .main-content { flex-grow: 1; padding: 30px; }
        .filter-card { background: white; border-radius: 12px; border-left: 5px solid #212529; }
        @media print {
            #sidebar, .no-print { display: none !important; }
            .main-content { padding: 0; width: 100%; }
        }
    </style>
</head>
<body class="d-flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h2 class="fw-bold">Sales History & Filters</h2>
                <div class="btn-group">
                    <button class="btn btn-outline-danger btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Current Page
                    </button>
                    <a href="export_sales.php" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export ALL to Excel
                    </a>
                </div>
            </div>

            <div class="card shadow-sm p-4 mb-4 filter-card no-print border-0">
                <form id="filter-form" method="GET" action="reports.php">
                    <input type="hidden" name="page" value="1"> 
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $_GET['start_date'] ?? ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">End Date</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $_GET['end_date'] ?? ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Filter by Rice Variety</label>
                            <select name="product_id" class="form-select">
                                <option value="">-- All Varieties --</option>
                                <?php foreach($all_products as $p): ?>
                                    <option value="<?php echo $p['product_id']; ?>" <?php if(isset($_GET['product_id']) && $_GET['product_id'] == $p['product_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($p['rice_type']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search me-2"></i> Apply Filters</button>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <span class="small text-muted me-2">Quick Filters:</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setQuickDate('today')">Today</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setQuickDate('week')">This Week</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setQuickDate('month')">This Month</button>
                        <a href="reports.php" class="btn btn-sm btn-link text-danger">Clear All</a>
                    </div>
                </form>
            </div>

            <div class="alert alert-success shadow-sm d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2"></i> Computed Total Sales</h5>
                    <small class="text-success-emphasis">Based on <?php echo number_format($total_rows); ?> total matching records.</small>
                </div>
                <h3 class="mb-0 fw-bold">₱ <?php echo number_format($total_filtered_sales, 2); ?></h3>
            </div>

            <div class="card border-0 shadow-sm p-4">
                <div class="table-responsive mb-3">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Date & Time</th>
                                <th>Cashier</th>
                                <th>Rice Variety</th>
                                <th>Unit</th>
                                <th>Qty</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reports as $row): ?>
                            <tr>
                                <td><?php echo date('M d, Y | h:i A', strtotime($row['sale_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['seller']); ?></td>
                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['rice_type']); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $row['unit_type']; ?></span></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td class="fw-bold">₱ <?php echo number_format($row['total_price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($reports)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No sales found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav aria-label="Sales Report Pagination" class="no-print">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPageLink($page - 1); ?>">Previous</a>
                        </li>
                        
                        <?php 
                        // Show up to 5 page numbers to keep UI clean
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++): 
                        ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active bg-dark border-dark' : ''; ?>">
                                <a class="page-link text-dark" href="<?php echo buildPageLink($i); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPageLink($page + 1); ?>">Next</a>
                        </li>
                    </ul>
                    <div class="text-center mt-2 small text-muted">
                        Showing page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </div>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function setQuickDate(type) {
        let today = new Date();
        let start = document.getElementById('start_date');
        let end = document.getElementById('end_date');
        
        let formatDate = (date) => {
            let d = new Date(date), month = '' + (d.getMonth() + 1), day = '' + d.getDate(), year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [year, month, day].join('-');
        }

        if (type === 'today') {
            start.value = formatDate(today);
            end.value = formatDate(today);
        } else if (type === 'week') {
            let first = today.getDate() - today.getDay(); // Sunday
            let firstDay = new Date(today.setDate(first));
            let lastDay = new Date(today.setDate(firstDay.getDate() + 6));
            start.value = formatDate(firstDay);
            end.value = formatDate(lastDay);
        } else if (type === 'month') {
            let firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            let lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            start.value = formatDate(firstDay);
            end.value = formatDate(lastDay);
        }
        document.getElementById('filter-form').submit();
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>