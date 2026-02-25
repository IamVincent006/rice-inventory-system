<?php
session_start();
require_once '../config/db_config.php'; // Ensure this points to your InfinityFree config

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // 1. Fetch current hashed password from database
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // 2. Verify current password
    if ($user && password_verify($current_pass, $user['password'])) {
        if ($new_pass === $confirm_pass) {
            // 3. Hash new password and update
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            if ($update->execute([$hashed_password, $_SESSION['user_id']])) {
                $message = "<div class='alert alert-success'>Password updated successfully!</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>New passwords do not match.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Current password is incorrect.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password - Bigas Inventory</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white"><h4>Change Password</h4></div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Password</button>
                            <a href="dashboard.php" class="btn btn-link w-100 mt-2">Back to Dashboard</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>