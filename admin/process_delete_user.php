<?php
session_start();
require_once '../config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'admin') {
    $user_id = $_POST['user_id'];

    // Security Check: Prevent the admin from accidentally deleting their own currently active account
    if ($user_id == $_SESSION['user_id']) {
        header("Location: settings.php?error=You cannot delete your own active session account.");
        exit();
    }

    try {
        // Attempt to delete the user. (The main 'admin' username is protected in the UI, but we add an extra layer here)
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND username != 'admin'");
        $stmt->execute([$user_id]);
        
        header("Location: settings.php?delete_success=1");
        exit();
        
    } catch(PDOException $e) {
        // ERROR 1451 is MySQL's standard Foreign Key Constraint error.
        // This triggers if the user has already recorded sales in the POS.
        if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
            header("Location: settings.php?error=Cannot delete! This user has already processed sales. Please 'Change Password' to lock them out instead, so your sales history remains intact.");
        } else {
            header("Location: settings.php?error=Database Error: " . $e->getMessage());
        }
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>