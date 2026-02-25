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
$limit = 50; 
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

// 2. Fetch the actual records for THIS PAGE only
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reports | Our Bigasan</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --harvest-gold: #facc15;
            --surface: #ffffff;
            --bg-gray: #f8fafc;
            --dark-blue: #0f172a;
            --transition: all 0.2s ease;
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
            margin-left: 280px; 
            transition: margin 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .main-content { margin-left: 0; padding-top: 80px; }
        }

        .report-card { border-radius: 1.5rem; border: none; background: var(--surface); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .filter-panel { background: var(--dark-blue); color: white; border-radius: 1.5rem; }
        
        .form-control, .form-select {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: white !important;
            font-size: 1rem; /* Prevents mobile zoom */
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.1);
            border-color: var(--harvest-gold);
            box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.1);
        }

        .form-select option { background: var(--dark-blue); }

        .table thead th {
            background: #f1f5f9;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            color: #64748b;
            padding: 1.25rem;
            border: none;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }

        .pagination .page-link {
            border: none;
            margin: 0 3px;
            border-radius: 0.5rem;
            color: var(--dark-blue);
            font-weight: 600;
            padding: 0.5rem 0.8rem;
        }

        .pagination .active .page-link { background: var(--harvest-gold); color: var(--dark-blue); }

        /* Print Settings for Report Cleanliness */
        @media print {
            .sidebar-container, .no-print, .sidebar-spacer, .overlay { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; width: 100%; }
            body { background: white; padding: 0; }
            .report-card { box-shadow: none; border: 1px solid #eee; border-radius: 0; }
        }
    </style>
</head>
<body class="d-flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 mb-md-5 no-print gap-3">
                <div>
                    <h2 class="fw-800 mb-1">Store Intelligence</h2>
                    <p class="text-secondary mb-0 small">Historical Sales Data & Audit</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white border shadow-sm rounded-3 px-3 fw-600 h-100" onclick="window.print()">
                        <i class="ph ph-printer me-1"></i> <span class="d-none d-md-inline">Print</span>
                    </button>
                    <a href="export_sales.php" class="btn btn-success shadow-sm rounded-3 px-3 fw-600 d-flex align-items-center">
                        <i class="ph ph-file-xls me-1"></i> <span class="d-none d-md-inline">Excel</span>
                    </a>
                </div>
            </div>

            <div class="card filter-panel p-4 mb-4 no-print border-0 shadow-lg">
                <form id="filter-form" method="GET" action="reports.php">
                    <input type="hidden" name="page" value="1"> 
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="small fw-700 text-uppercase opacity-50 mb-2 d-block">Start</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $_GET['start_date'] ?? ''; ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="small fw-700 text-uppercase opacity-50 mb-2 d-block">End</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $_GET['end_date'] ?? ''; ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="small fw-700 text-uppercase opacity-50 mb-2 d-block">Variety</label>
                            <select name="product_id" class="form-select">
                                <option value="">All Types</option>
                                <?php foreach($all_products as $p): ?>
                                    <option value="<?php echo $p['product_id']; ?>" <?php if(isset($_GET['product_id']) && $_GET['product_id'] == $p['product_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($p['rice_type']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <button type="submit" class="btn btn-warning w-100 py-2 fw-800 text-dark">
                                <i class="ph-bold ph-funnel me-1"></i> FILTER
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top border-secondary d-flex flex-wrap align-items-center gap-2">
                        <span class="small fw-700 text-uppercase opacity-50 me-2">Presets:</span>
                        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="setQuickDate('today')">Today</button>
                        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="setQuickDate('week')">Week</button>
                        <a href="reports.php" class="btn btn-sm btn-link text-warning ms-auto fw-700 text-decoration-none p-0">Clear Filters</a>
                    </div>
                </form>
            </div>

            <div class="card report-card p-4 border-start border-4 border-success shadow-sm mb-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                    <div>
                        <h6 class="text-secondary fw-700 text-uppercase small mb-0">Filtered Revenue Total</h6>
                        <small class="text-muted">Calculated from <?php echo number_format($total_rows); ?> sales records</small>
                    </div>
                    <h2 class="fw-800 mb-0 text-success">₱ <?php echo number_format($total_filtered_sales, 2); ?></h2>
                </div>
            </div>

            <div class="card report-card overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Variety</th>
                                <th>Unit</th>
                                <th>Qty</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reports as $row): ?>
                            <tr>
                                <td class="small text-secondary"><?php echo date('M d | h:i A', strtotime($row['sale_date'])); ?></td>
                                <td class="fw-800"><?php echo htmlspecialchars($row['rice_type']); ?></td>
                                <td><span class="badge bg-light text-dark border px-2 py-1"><?php echo $row['unit_type']; ?></span></td>
                                <td class="fw-700"><?php echo $row['quantity']; ?></td>
                                <td class="fw-800 text-dark text-end">₱<?php echo number_format($row['total_price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($reports)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No data matches your criteria.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="p-3 border-top no-print bg-light">
                    <nav aria-label="Pagination">
                        <ul class="pagination pagination-sm justify-content-center mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link shadow-sm" href="<?php echo buildPageLink($page - 1); ?>"><i class="ph ph-caret-left"></i></a>
                            </li>
                            <li class="page-item disabled d-md-none"><span class="page-link"><?php echo "$page / $total_pages"; ?></span></li>
                            
                            <?php 
                            $start_page = max(1, $page - 1);
                            $end_page = min($total_pages, $page + 1);
                            for ($i = $start_page; $i <= $end_page; $i++): 
                            ?>
                                <li class="page-item d-none d-md-block <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link shadow-sm" href="<?php echo buildPageLink($i); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link shadow-sm" href="<?php echo buildPageLink($page + 1); ?>"><i class="ph ph-caret-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
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
            let first = today.getDate() - today.getDay();
            let firstDay = new Date(today.setDate(first));
            let lastDay = new Date(today.setDate(firstDay.getDate() + 6));
            start.value = formatDate(firstDay);
            end.value = formatDate(lastDay);
        }
        document.getElementById('filter-form').submit();
    }
    </script>
</body>
</html>