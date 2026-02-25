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
    <title>Rice Inventory | Rice ni Mang Kanor Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .main-content { flex-grow: 1; padding: 30px; }
        .table { background: white; border-radius: 12px; overflow: hidden; }
    </style>
</head>
<body class="d-flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Rice Inventory</h2>
                <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addRiceModal">
                    <i class="bi bi-plus-lg me-2"></i> Add New Variety
                </button>
            </div>

            <?php if(isset($_GET['delete_error'])): ?>
                <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i>Cannot delete! Existing sales are tied to this variety.</div>
            <?php endif; ?>
            <?php if(isset($_GET['delete_success'])): ?>
                <div class="alert alert-success shadow-sm"><i class="bi bi-trash me-2"></i>Rice variety deleted safely.</div>
            <?php endif; ?>
            <?php if(isset($_GET['edit_success'])): ?>
                <div class="alert alert-primary shadow-sm"><i class="bi bi-pencil-square me-2"></i>Rice variety updated successfully!</div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Variety</th>
                                <th>Current Stock</th>
                                <th>Price / kg</th>
                                <th>Price / Half</th>
                                <th>Price / Sack</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $row): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['rice_type']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($row['current_stock_kg'] < 50) ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo number_format($row['current_stock_kg'], 1); ?> kg
                                    </span>
                                </td>
                                <td>₱ <?php echo number_format($row['price_kilo'], 2); ?></td>
                                <td>₱ <?php echo number_format($row['price_half_sack'], 2); ?></td>
                                <td>₱ <?php echo number_format($row['price_sack'], 2); ?></td>
                                <td class="small text-muted">
                                    <?php echo date('M d, Y h:i A', strtotime($row['last_updated'])); ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-success" onclick="openRestockModal(<?php echo $row['product_id']; ?>, '<?php echo addslashes($row['rice_type']); ?>')" title="Quick Restock">
                                            <i class="bi bi-patch-plus"></i>
                                        </button>
                                        
                                        <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(<?php echo $row['product_id']; ?>, '<?php echo addslashes($row['rice_type']); ?>', <?php echo $row['current_stock_kg']; ?>, <?php echo $row['price_kilo']; ?>, <?php echo $row['price_half_sack']; ?>, <?php echo $row['price_sack']; ?>)" title="Edit Details">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        
                                        <form action="process_delete_rice.php" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete <?php echo addslashes($row['rice_type']); ?>?');" style="margin:0;">
                                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Variety">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="process_add_rice.php" method="POST">
                    <div class="modal-header"><h5>Add New Variety</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label>Variety Name</label><input type="text" name="rice_type" class="form-control" required></div>
                        <div class="mb-3"><label>Initial Stock (kg)</label><input type="number" step="0.01" name="initial_stock" class="form-control" required></div>
                        <div class="row">
                            <div class="col"><label>Price/kg</label><input type="number" step="0.01" name="p_kilo" class="form-control" required></div>
                            <div class="col"><label>Price/Half</label><input type="number" step="0.01" name="p_half" class="form-control" required></div>
                            <div class="col"><label>Price/Sack</label><input type="number" step="0.01" name="p_sack" class="form-control" required></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-dark">Save Variety</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editRiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="process_edit_rice.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Rice Variety</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="edit_id">
                        <div class="mb-3"><label>Variety Name</label><input type="text" name="rice_type" id="edit_name" class="form-control" required></div>
                        <div class="mb-3"><label>Current Stock (kg)</label><input type="number" step="0.01" name="current_stock_kg" id="edit_stock" class="form-control" required></div>
                        <div class="row">
                            <div class="col"><label>Price/kg</label><input type="number" step="0.01" name="p_kilo" id="edit_pk" class="form-control" required></div>
                            <div class="col"><label>Price/Half</label><input type="number" step="0.01" name="p_half" id="edit_ph" class="form-control" required></div>
                            <div class="col"><label>Price/Sack</label><input type="number" step="0.01" name="p_sack" id="edit_ps" class="form-control" required></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="restockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="process_restock.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Restock: <span id="restockName"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="restockId">
                        <div class="mb-3">
                            <label class="form-label">How many kilograms to add?</label>
                            <input type="number" step="0.01" name="added_weight" class="form-control" placeholder="e.g. 50 for 1 sack" required>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-success">Confirm Restock</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openRestockModal(id, name) {
        document.getElementById('restockId').value = id;
        document.getElementById('restockName').innerText = name;
        new bootstrap.Modal(document.getElementById('restockModal')).show();
    }

    function openEditModal(id, name, stock, pk, ph, ps) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_stock').value = stock;
        document.getElementById('edit_pk').value = pk;
        document.getElementById('edit_ph').value = ph;
        document.getElementById('edit_ps').value = ps;
        new bootstrap.Modal(document.getElementById('editRiceModal')).show();
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>