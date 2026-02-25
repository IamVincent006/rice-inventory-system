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
    <title>Settings | Rice ni Mang KanorStore Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .main-content { flex-grow: 1; padding: 30px; }
        .card { border: none; border-radius: 12px; }
        .visible-password { font-family: monospace; font-size: 1.1em; letter-spacing: 1px; }
    </style>
</head>
<body class="d-flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">User Management</h2>
                <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus me-2"></i> Create Account
                </button>
            </div>

            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success shadow-sm"><i class="bi bi-check-circle-fill me-2"></i>Account created successfully!</div>
            <?php endif; ?>
            <?php if(isset($_GET['pw_success'])): ?>
                <div class="alert alert-primary shadow-sm"><i class="bi bi-key-fill me-2"></i>Password updated successfully!</div>
            <?php endif; ?>
            <?php if(isset($_GET['delete_success'])): ?>
                <div class="alert alert-success shadow-sm"><i class="bi bi-trash-fill me-2"></i>User account deleted.</div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <div class="card shadow-sm p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Role</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $user): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="text-muted"><i class="bi bi-shield-lock me-1"></i> Hidden</span>
                                    <?php else: ?>
                                        <span class="text-danger fw-bold visible-password"><?php echo htmlspecialchars($user['password']); ?></span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge <?php echo ($user['role'] === 'admin') ? 'bg-primary' : 'bg-secondary'; ?>">
                                        <?php echo strtoupper($user['role']); ?>
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <?php echo date('M d, Y h:i A', strtotime($user['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-dark" onclick="openPasswordModal(<?php echo $user['user_id']; ?>, '<?php echo addslashes($user['username']); ?>')" title="Change Password">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        
                                        <?php if ($user['username'] !== 'admin' && $user['user_id'] !== $_SESSION['user_id']): ?>
                                            <form action="process_delete_user.php" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete <?php echo addslashes($user['username']); ?>?');" style="margin:0;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Account">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete this account"><i class="bi bi-trash"></i></button>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="process_add_user.php" method="POST">
                    <div class="modal-header"><h5 class="modal-title">New System Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label>Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                        <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
                        <div class="mb-3"><label>Password</label><input type="text" name="password" class="form-control" required></div>
                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" class="form-select">
                                <option value="user">User (Standard POS Access Only)</option>
                                <option value="admin">Admin (Full Access to Settings & Reports)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-dark">Create User</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form action="process_change_password.php" method="POST">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Update Password</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="pw_user_id">
                        <p class="small text-muted mb-2">Changing password for: <strong id="pw_username" class="text-dark"></strong></p>
                        <div class="mb-3">
                            <label class="small fw-bold">New Password</label>
                            <input type="text" name="new_password" class="form-control" required minlength="4">
                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="submit" class="btn btn-primary w-100">Save Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openPasswordModal(id, username) {
        document.getElementById('pw_user_id').value = id;
        document.getElementById('pw_username').innerText = username;
        new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>