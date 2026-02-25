<style>
    :root {
        --sidebar-bg: #0f172a;
        --nav-hover: rgba(250, 204, 21, 0.1);
        --accent: #facc15;
        --sidebar-width: 280px;
    }

    /* --- Desktop Sidebar Styling --- */
    .sidebar-container {
        width: var(--sidebar-width);
        min-height: 100vh;
        background: var(--sidebar-bg);
        border-right: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        flex-direction: column;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 2000;
        position: fixed;
        left: 0;
        top: 0;
    }

    /* --- Mobile Navigation Bar --- */
    .mobile-top-nav {
        display: none;
        background: var(--sidebar-bg);
        padding: 0.75rem 1rem;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1500;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* --- Adaptive Layout Logic --- */
    @media (max-width: 991.98px) {
        .sidebar-container {
            transform: translateX(-100%); /* Hidden by default on mobile */
            width: 85%; /* Take up most of the screen when open */
            max-width: 300px;
        }
        
        .sidebar-container.active {
            transform: translateX(0);
            box-shadow: 10px 0 50px rgba(0,0,0,0.5);
        }

        .mobile-top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Responsive spacer for fixed sidebar */
        .sidebar-spacer { display: none !important; }
        
        /* Add padding to the top of pages to account for mobile header */
        body { padding-top: 60px; }
    }

    /* Navigation Styling */
    .nav-link-custom {
        color: #94a3b8;
        font-weight: 600;
        padding: 0.8rem 1.2rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
        text-decoration: none;
        margin-bottom: 0.5rem;
    }

    .nav-link-custom:hover, .nav-link-custom.active {
        background: var(--nav-hover);
        color: var(--accent);
    }

    .nav-link-custom.active {
        background: var(--accent);
        color: #0f172a !important;
    }

    .logout-btn {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
        font-weight: 700;
        border-radius: 0.75rem;
        transition: all 0.2s;
        text-decoration: none;
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        z-index: 1900;
    }

    .overlay.active { display: block; }

    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
</style>

<div class="mobile-top-nav">
    <div class="text-white fw-800 fs-5">
        <i class="ph-fill ph-leaf text-warning me-2"></i> Mang Kanor
    </div>
    <button class="btn text-white p-0 fs-2" onclick="toggleSidebar()">
        <i class="ph ph-list"></i>
    </button>
</div>

<div class="overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="sidebar-container p-3 text-white" id="mainSidebar">
    <div class="d-flex justify-content-between align-items-center mb-4 d-lg-block">
        <div class="sidebar-brand p-2">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none">
                <i class="ph-fill ph-leaf text-warning me-2" style="font-size: 1.8rem;"></i>
                <span class="fs-5 fw-800">Our Bigasan <span class="text-warning text-uppercase small">Pro</span></span>
            </a>
        </div>
        <button class="btn text-white d-lg-none" onclick="toggleSidebar()">
            <i class="ph ph-x fs-3"></i>
        </button>
    </div>

    <ul class="nav flex-column mb-auto">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
                <a href="../admin/dashboard.php" class="nav-link-custom <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="ph ph-chart-pie-slice me-3"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/inventory.php" class="nav-link-custom <?php echo ($current_page == 'inventory.php') ? 'active' : ''; ?>">
                    <i class="ph ph-package me-3"></i> Rice Inventory
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/reports.php" class="nav-link-custom <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                    <i class="ph ph-trend-up me-3"></i> Finance Reports
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/settings.php" class="nav-link-custom <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                    <i class="ph ph-users-four me-3"></i> User Settings
                </a>
            </li>
            <hr class="opacity-10 my-3">
        <?php endif; ?>

        <li class="nav-item">
            <a href="../user/pos.php" class="nav-link-custom <?php echo ($current_page == 'pos.php') ? 'active' : ''; ?>">
                <i class="ph ph-monitor me-3"></i> Point of Sale
            </a>
        </li>
    </ul>

    <div class="mt-auto pt-4 border-top border-secondary">
        <div class="user-info d-flex align-items-center mb-3 p-2 rounded-3" style="background: rgba(255,255,255,0.03);">
            <div class="avatar me-2 bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                <i class="ph-fill ph-user text-dark"></i>
            </div>
            <div class="overflow-hidden">
                <p class="mb-0 small fw-bold text-truncate text-white"><?php echo $_SESSION['full_name']; ?></p>
                <p class="mb-0 text-muted" style="font-size: 0.65rem;"><?php echo strtoupper($_SESSION['role']); ?></p>
            </div>
        </div>
        <a href="../auth/logout.php" class="btn logout-btn w-100 py-2 d-flex align-items-center justify-content-center">
            <i class="ph ph-sign-out me-2"></i> Logout
        </a>
    </div>
</div>

<div class="sidebar-spacer" style="width: var(--sidebar-width); min-width: var(--sidebar-width);"></div>

<script>
    function toggleSidebar() {
        document.getElementById('mainSidebar').classList.toggle('active');
        document.getElementById('sidebarOverlay').classList.toggle('active');
    }
</script>