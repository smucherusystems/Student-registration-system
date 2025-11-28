<?php
// Start session and check if user is logged in
session_start();

// Simple authentication check - in a real application, you'd use proper session management
$is_logged_in = true; // This would normally come from session validation

if (!$is_logged_in) {
    header('Location: login.html');
    exit;
}

// Include database configuration
require_once 'config/database.php';

// Handle delete operation
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    try {
        $delete_sql = "DELETE FROM students WHERE id = :id";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute(['id' => $delete_id]);
        
        $delete_message = "Student record deleted successfully!";
    } catch (PDOException $e) {
        $delete_error = "Error deleting record: " . $e->getMessage();
    }
}

// Fetch all students from database
try {
    $sql = "SELECT * FROM students ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching students: " . $e->getMessage();
    $students = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Student Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Admin Dashboard</h1>
                <ul class="nav-menu">
                    <li><a href="index.html" class="nav-link">Home</a></li>
                    <li><a href="registration.html" class="nav-link">Register Student</a></li>
                    <li><a href="login.html" class="nav-link">Admin Login</a></li>
                    <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="table-container">
                <h2>Registered Students</h2>
                
                <!-- Display messages -->
                <?php if (isset($delete_message)): ?>
                    <div class="message success"><?php echo htmlspecialchars($delete_message); ?></div>
                <?php endif; ?>
                
                <?php if (isset($delete_error)): ?>
                    <div class="message error"><?php echo htmlspecialchars($delete_error); ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Students Table -->
                <?php if (empty($students)): ?>
                    <div class="message">No students registered yet.</div>
                <?php else: ?>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Course</th>
                                <th>Gender</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($student['course']); ?></td>
                                    <td><?php echo htmlspecialchars($student['gender']); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($student['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary">Edit</a>
                                        <a href="dashboard.php?delete_id=<?php echo $student['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Summary -->
                    <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                        <strong>Total Students: <?php echo count($students); ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Student Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>