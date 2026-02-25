<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; min-height: 100vh;">
    <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4 fw-bold">Rice ni Mang Kanor <span class="text-warning">Pro</span></span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="nav-item mb-2">
                <a href="../admin/dashboard.php" class="nav-link text-white">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="../admin/inventory.php" class="nav-link text-white">
                    <i class="bi bi-box-seam me-2"></i> Rice Inventory
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="../admin/reports.php" class="nav-link text-white">
                    <i class="bi bi-graph-up me-2"></i> Finance Reports
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="../admin/settings.php" class="nav-link text-white">
                    <i class="bi bi-gear me-2"></i> User Settings
                </a>
            </li>
        <?php endif; ?>

        <li class="nav-item mb-2">
            <a href="../user/pos.php" class="nav-link text-white">
                <i class="bi bi-cart3 me-2"></i> Point of Sale (POS)
            </a>
        </li>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="../auth/logout.php" class="btn btn-outline-danger w-100 text-decoration-none">
            <strong>Logout</strong>
        </a>
    </div>
</div>