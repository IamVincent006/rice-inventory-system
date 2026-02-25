<?php
// Aiven Cloud Database Credentials (from your screenshot)
$host     = 'mysql-130b28ca-gelodaza06-a6cd.l.aivencloud.com';
$port     = '21695';
$dbname   = 'defaultdb'; 
$username = 'avnadmin';
$password = 'AVNS_ShF_TzLSUw9eVohb6sR';

// Connection String with SSL requirement for Aivenn
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // SSL is required for Aiven cloud connections to keep your data safe
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, 
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
    // If you see this message, the connection is successful!
    // echo "Cloud Connection Successful!"; 

} catch (PDOException $e) {
    // If this fails, check if your internet is active or if the password is correct
    die("Cloud Database Connection Failed: " . $e->getMessage());
}
?>