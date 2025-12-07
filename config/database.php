<?php
// Database configuration - Update these according to your MySQL 8.0 setup
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
<<<<<<< HEAD
define('DB_PASSWORD', 'NewPassword123!'); // Your MySQL 8.0 password
=======
define('DB_PASSWORD', 'Password'); // Your MySQL 8.0 password
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
define('DB_NAME', 'student_management');

// Attempt to connect to MySQL database
try {
    $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
} catch(PDOException $e) {
    die("ERROR: Could not connect to database. " . $e->getMessage());
}
?>