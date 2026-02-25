<?php
require_once 'config/db_config.php';

$fullName = "System Administrator";
$username = "admin";
$password = "admin123"; // This is the password you will type to log in
$role = "admin";

// Generate the secure hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$fullName, $username, $hashed_password, $role]);
    
    echo "<h3>Admin user created successfully!</h3>";
    echo "<p>Username: <b>admin</b></p>";
    echo "<p>Password: <b>admin123</b></p>";
    echo "<a href='index.php'>Click here to go to the Login Page</a>";
} catch(PDOException $e) {
    echo "Error (You might have already created the user): " . $e->getMessage();
}
?>