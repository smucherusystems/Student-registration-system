<?php
require_once 'config/database.php';

// Test login with default admin credentials
$username = 'admin';
$password = 'admin123'; // The password we hashed in db.sql

// Simulate login process
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    if (password_verify($password, $user['password_hash'])) {
        echo "<h2>✅ Login Successful!</h2>";
        echo "<p>Welcome, " . htmlspecialchars($user['username']) . "!</p>";
        
        // Test session would be started here
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        echo "<p>Session variables set:</p>";
        echo "<pre>";
        print_r([
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username']
        ]);
        echo "</pre>";
    } else {
        echo "<h2>❌ Invalid Password</h2>";
    }
} else {
    echo "<h2>❌ User not found</h2>";
}
?>
