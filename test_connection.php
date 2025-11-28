<?php
require_once 'config/database.php';

try {
    // Test database connection
    echo "<h2>✅ Database Connection Successful!</h2>";
    
    // Test query to count students
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $result = $stmt->fetch();
    echo "<p>Number of students in database: " . $result['count'] . "</p>";
    
    // Test query to list admin users
    echo "<h3>Admin Users:</h3>";
    $stmt = $pdo->query("SELECT id, username, created_at FROM admin_users");
    echo "<ul>";
    while ($row = $stmt->fetch()) {
        echo "<li>ID: " . htmlspecialchars($row['id']) . 
             " - Username: " . htmlspecialchars($row['username']) . 
             " (Created: " . $row['created_at'] . ")</li>";
    }
    echo "</ul>";
    
    // Test query to list students
    echo "<h3>Sample Students:</h3>";
    $stmt = $pdo->query("SELECT * FROM students LIMIT 5");
    echo "<table border='1' cellpadding='5'>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Gender</th>
            </tr>";
    
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['course']) . "</td>";
        echo "<td>" . $row['gender'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    die("<h2>❌ Database Error:</h2><p>" . $e->getMessage() . "</p>");
}
?>
