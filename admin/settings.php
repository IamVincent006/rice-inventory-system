<?php
session_start();
require_once '../config/db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$stmt = $pdo->query("SELECT user_id, full_name, username, password, role, created_at FROM users ORDER BY created_at ASC");
$all_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>User Settings | Our Bigasan</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
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

        /* --- Scalable Layout Engine --- */
        .main-content { 
            flex-grow: 1; 
            padding: 1.5rem; 
            margin-left: 280px; 
            transition: margin 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .main-content { margin-left: 0; padding-top: 80px; }
        }

        .settings-card {
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
            white-space: nowrap; /* Ensures data clarity on mobile scroll */
        }

        .credential-box {
            font-family: 'JetBrains Mono', monospace;
            background: #f1f5f9;
            padding: 0.25rem 0.6rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            color: #e11d48;
            font-weight: 600;
        }

        .action-btn {
            width: 40px; height: 40px;
            display: inline-flex;
            align-items: center; justify-content: center;
            border-radius: 0.75rem;
            transition: var(--transition);
        }

        /* --- Responsive Modal UI --- */
        .modal-content {
            border-radius: 1.5rem;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }

        .form-control, .form-select {
            border-radius: 0.75rem;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            font-size: 1rem; /* Prevents auto-zoom on iPhone */
        }
    </style>
</head>
<body class="d-flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
                <div>
                    <h2 class="fw-800 mb-1">User Management</h2>
                    <p class="text-secondary mb-0 small">Security & Access Control</p>
                </div>
                <button class="btn btn-dark px-4 py-2 fw-700 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="ph-bold ph-user-plus me-2"></i> Create Account
                </button>
            </div>

            <?php if(isset($_GET['success']) || isset($_GET['pw_success']) || isset($_GET['delete_success'])): ?>
                <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center p-3">
                    <i class="ph-fill ph-check-circle me-2 fs-4"></i> 
                    <div class="small fw-600">Database updated successfully.</div>
                </div>
            <?php endif; ?>

            <div class="card settings-card overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Security Key</th>
                                <th>Role</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $user): ?>
                            <tr>
                                <td>
                                    <div class="fw-800 text-dark"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                    <div class="text-muted small d-md-none"><?php echo $user['role']; ?></div>
                                </td>
                                <td class="text-secondary">@<?php echo htmlspecialchars($user['username']); ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="text-muted small"><i class="ph ph-lock-key me-1"></i> Private</span>
                                    <?php else: ?>
                                        <code class="credential-box"><?php echo htmlspecialchars($user['password']); ?></code>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?php echo ($user['role'] === 'admin') ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary'; ?> px-3 py-2">
                                        <?php echo strtoupper($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-light border action-btn" onclick="openPasswordModal(<?php echo $user['user_id']; ?>, '<?php echo addslashes($user['username']); ?>')">
                                            <i class="ph-bold ph-key"></i>
                                        </button>
                                        <?php if ($user['username'] !== 'admin' && $user['user_id'] !== $_SESSION['user_id']): ?>
                                            <form action="process_delete_user.php" method="POST" onsubmit="return confirm('Archive user account?');" class="m-0">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger action-btn">
                                                    <i class="ph-bold ph-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-light action-btn opacity-25" disabled><i class="ph-bold ph-lock"></i></button>
                                        <?php endif; ?>
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

    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-2 p-md-4">
                <form action="process_add_user.php" method="POST">
                    <div class="modal-header border-0">
                        <h5 class="fw-800">New System Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="small fw-700 text-uppercase text-secondary mb-2 d-block">Full Name</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="small fw-700 text-uppercase text-secondary mb-2 d-block">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="small fw-700 text-uppercase text-secondary mb-2 d-block">Initial Key</label>
                                <input type="text" name="password" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="small fw-700 text-uppercase text-secondary mb-2 d-block">System Role</label>
                                <select name="role" class="form-select">
                                    <option value="user">User (POS Only)</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-dark w-100 py-3 fw-800 shadow-sm">PROVISION ACCESS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>