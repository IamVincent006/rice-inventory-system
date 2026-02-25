<?php
session_start();
require_once '../config/db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM products ORDER BY rice_type ASC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Rice Inventory | Our Bigasan</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --harvest-gold: #facc15;
            --surface: #ffffff;
            --bg-gray: #f8fafc;
            --dark-blue: #0f172a;
            --transition: all 0.25s ease;
        }

        body { 
            background-color: var(--bg-gray); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--dark-blue);
            margin: 0;
        }

        .main-content { 
            flex-grow: 1; 
            padding: 1.5rem; 
            margin-left: 280px; 
            transition: margin 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .main-content { margin-left: 0; padding-top: 80px; }
        }

        .inventory-card {
            border-radius: 1.5rem;
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            background: var(--surface);
        }

        .table thead th {
            background: #f1f5f9;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            color: #64748b;
            padding: 1.25rem;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .badge-stock {
            padding: 0.5rem 0.75rem;
            border-radius: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
        }

        .action-btn {
            width: 40px; height: 40px;
            display: inline-flex;
            align-items: center; justify-content: center;
            border-radius: 0.75rem;
            transition: var(--transition);
        }

        .modal-content {
            border-radius: 1.5rem;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }

        .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            font-size: 1rem; /* Prevents mobile zoom */
        }
    </style>
</head>
<body class="d-flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h2 class="fw-800 mb-1">Rice Inventory</h2>
                    <p class="text-secondary mb-0 small">Manage stock levels and variety pricing</p>
                </div>
                <button class="btn btn-dark px-4 py-2 fw-700 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addRiceModal">
                    <i class="ph-bold ph-plus me-2"></i> Add New Variety
                </button>
            </div>

            <?php if(isset($_GET['delete_error'])): ?>
                <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4 small">
                    <i class="ph-fill ph-warning-circle me-2"></i> Archive failed: Variety linked to sales records.
                </div>
            <?php endif; ?>

            <div class="card inventory-card overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Variety</th>
                                <th>Stock Level</th>
                                <th>Price/kg</th>
                                <th>Half Sack</th>
                                <th>Full Sack</th>
                                <th class="text-end no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-800 text-dark"><?php echo htmlspecialchars($row['rice_type']); ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;">UID: #RC-0<?php echo $row['product_id']; ?></div>
                                </td>
                                <td>
                                    <span class="badge-stock <?php echo ($row['current_stock_kg'] < 50) ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'; ?>">
                                        <?php echo number_format($row['current_stock_kg'], 1); ?> kg
                                    </span>
                                </td>
                                <td class="fw-600 text-dark">₱<?php echo number_format($row['price_kilo'], 2); ?></td>
                                <td class="fw-600 text-muted">₱<?php echo number_format($row['price_half_sack'], 2); ?></td>
                                <td class="fw-600 text-muted">₱<?php echo number_format($row['price_sack'], 2); ?></td>
                                <td class="no-print">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-success action-btn" onclick="openRestockModal(<?php echo $row['product_id']; ?>, '<?php echo addslashes($row['rice_type']); ?>')">
                                            <i class="ph-bold ph-plus-circle"></i>
                                        </button>
                                        <button class="btn btn-light border action-btn" onclick="openEditModal(...)">
                                            <i class="ph-bold ph-pencil-simple"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addRiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-2 p-md-4">
                <form action="process_add_rice.php" method="POST">
                    <div class="modal-header border-0">
                        <h5 class="fw-800">Register Variety</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="small fw-700 text-uppercase mb-2 d-block text-secondary">Name</label>
                            <input type="text" name="rice_type" class="form-control" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-sm-4">
                                <label class="small fw-700 text-uppercase mb-2 d-block text-secondary">₱/kg</label>
                                <input type="number" step="0.01" name="p_kilo" class="form-control" required>
                            </div>
                            <div class="col-12 col-sm-4">
                                <label class="small fw-700 text-uppercase mb-2 d-block text-secondary">₱/Half</label>
                                <input type="number" step="0.01" name="p_half" class="form-control" required>
                            </div>
                            <div class="col-12 col-sm-4">
                                <label class="small fw-700 text-uppercase mb-2 d-block text-secondary">₱/Sack</label>
                                <input type="number" step="0.01" name="p_sack" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-dark w-100 py-3 fw-700">Add to Warehouse</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>