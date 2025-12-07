<?php
<<<<<<< HEAD
// Check authentication
require_once 'auth_check.php';
=======
// Start session and check if user is logged in
session_start();

// Simple authentication check - in a real application, you'd use proper session management
$is_logged_in = true; // This would normally come from session validation

if (!$is_logged_in) {
    header('Location: login.html');
    exit;
}
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9

// Include database configuration
require_once 'config/database.php';

<<<<<<< HEAD
// Initialize messages
$success_message = '';
$error_message = '';

// Check for success/error messages from session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

=======
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
// Handle delete operation
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    try {
        $delete_sql = "DELETE FROM students WHERE id = :id";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute(['id' => $delete_id]);
        
<<<<<<< HEAD
        $_SESSION['success_message'] = "Student deleted successfully!";
        header('Location: dashboard.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting record: " . $e->getMessage();
        header('Location: dashboard.php');
        exit;
    }
}

// Fetch all students from database with fee status
try {
    $sql = "SELECT s.*, 
                   COALESCE(SUM(f.assigned_amount - f.paid_amount), 0) as outstanding_balance,
                   CASE 
                       WHEN COALESCE(SUM(f.assigned_amount - f.paid_amount), 0) = 0 THEN 'paid'
                       WHEN COALESCE(SUM(f.assigned_amount - f.paid_amount), 0) > 0 AND COALESCE(SUM(f.paid_amount), 0) > 0 THEN 'partial'
                       WHEN COALESCE(SUM(f.assigned_amount - f.paid_amount), 0) > 0 AND EXISTS(SELECT 1 FROM fees WHERE student_id = s.id AND due_date < CURDATE() AND status != 'paid') THEN 'overdue'
                       WHEN COALESCE(SUM(f.assigned_amount - f.paid_amount), 0) > 0 THEN 'pending'
                       ELSE 'none'
                   END as fee_status
            FROM students s
            LEFT JOIN fees f ON s.id = f.student_id
            GROUP BY s.id
            ORDER BY s.created_at DESC";
    $stmt = $pdo->query($sql);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching students: " . $e->getMessage();
    $students = [];
}

// Calculate statistics
$total_students = count($students);
$male_count = 0;
$female_count = 0;
$other_count = 0;
$courses = [];

foreach ($students as $student) {
    // Count by gender
    if ($student['gender'] === 'Male') {
        $male_count++;
    } elseif ($student['gender'] === 'Female') {
        $female_count++;
    } else {
        $other_count++;
    }
    
    // Count by course
    $course = $student['course'];
    if (!isset($courses[$course])) {
        $courses[$course] = 0;
    }
    $courses[$course]++;
}

// Fetch pending fees statistics
$pending_fees = 0;
try {
    $fees_sql = "SELECT SUM(assigned_amount - paid_amount) as total_pending FROM fees WHERE status IN ('pending', 'partial', 'overdue')";
    $fees_stmt = $pdo->query($fees_sql);
    $fees_result = $fees_stmt->fetch(PDO::FETCH_ASSOC);
    $pending_fees = $fees_result['total_pending'] ?? 0;
} catch (PDOException $e) {
    // Table might not exist yet, ignore error
    $pending_fees = 0;
}

// Fetch average attendance
$average_attendance = 0;
try {
    $attendance_sql = "SELECT 
        COUNT(CASE WHEN status = 'present' THEN 1 END) * 100.0 / COUNT(*) as avg_attendance 
        FROM attendance";
    $attendance_stmt = $pdo->query($attendance_sql);
    $attendance_result = $attendance_stmt->fetch(PDO::FETCH_ASSOC);
    $average_attendance = round($attendance_result['avg_attendance'] ?? 0, 1);
} catch (PDOException $e) {
    // Table might not exist yet, ignore error
    $average_attendance = 0;
}

// Fetch average grades
$average_grade = 0;
try {
    $grades_sql = "SELECT AVG((marks / max_marks) * 100) as avg_grade FROM grades";
    $grades_stmt = $pdo->query($grades_sql);
    $grades_result = $grades_stmt->fetch(PDO::FETCH_ASSOC);
    $average_grade = round($grades_result['avg_grade'] ?? 0, 1);
} catch (PDOException $e) {
    // Table might not exist yet, ignore error
    $average_grade = 0;
}

// Fetch recent grade entries for dashboard
$recent_grades = [];
try {
    $recent_grades_sql = "SELECT g.id, g.subject_name, g.marks, g.max_marks, g.exam_date, 
                                 s.name as student_name, s.id as student_id,
                                 ROUND((g.marks / g.max_marks) * 100, 2) as percentage
                          FROM grades g
                          INNER JOIN students s ON g.student_id = s.id
                          ORDER BY g.created_at DESC
                          LIMIT 5";
    $recent_grades_stmt = $pdo->query($recent_grades_sql);
    $recent_grades = $recent_grades_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table might not exist yet, ignore error
    $recent_grades = [];
}
=======
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
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Student Management System</title>
    <link rel="stylesheet" href="css/style.css">
<<<<<<< HEAD
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Loading Overlay - Active by default -->
    <div id="loadingOverlay" class="loading-overlay active">
        <div class="loading-spinner"></div>
        <p style="margin-top: 1rem; color: #7f8c8d; font-weight: 500;">Loading dashboard...</p>
    </div>
    
=======
</head>
<body>
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Admin Dashboard</h1>
                <ul class="nav-menu">
<<<<<<< HEAD
                    <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                    <li><a href="registration.php" class="nav-link">Register Student</a></li>
                    <li><a href="grades.php" class="nav-link">Grades</a></li>
                    <li><a href="fees.php" class="nav-link">Fees</a></li>
                    <li><a href="attendance.php" class="nav-link">Attendance</a></li>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
=======
                    <li><a href="index.html" class="nav-link">Home</a></li>
                    <li><a href="registration.html" class="nav-link">Register Student</a></li>
                    <li><a href="login.html" class="nav-link">Admin Login</a></li>
                    <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
<<<<<<< HEAD
            <!-- Display messages -->
            <?php if (!empty($success_message)): ?>
                <div class="notification success-notification"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="notification error-notification"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <!-- Dashboard Stats -->
            <div class="dashboard-header">
                <h2>Dashboard Overview</h2>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($pending_fees, 2); ?></h3>
                        <p>Pending Fees</p>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3><?php echo $average_attendance; ?>%</h3>
                        <p>Average Attendance</p>
                    </div>
                </div>
                
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">🎓</div>
                    <div class="stat-content">
                        <h3><?php echo $average_grade; ?>%</h3>
                        <p>Average Grades</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Action Cards -->
            <div class="quick-actions-section">
                <h3>Quick Actions</h3>
                <div class="quick-actions-grid">
                    <a href="grades.php" class="action-card action-card-blue">
                        <div class="action-icon">📝</div>
                        <h4>Manage Grades</h4>
                        <p>Add or update student grades</p>
                    </a>
                    
                    <a href="fees.php" class="action-card action-card-green">
                        <div class="action-icon">💳</div>
                        <h4>Record Payment</h4>
                        <p>Process fee payments</p>
                    </a>
                    
                    <a href="attendance.php" class="action-card action-card-purple">
                        <div class="action-icon">✓</div>
                        <h4>Mark Attendance</h4>
                        <p>Record student attendance</p>
                    </a>
                    
                    <a href="registration.php" class="action-card action-card-orange">
                        <div class="action-icon">➕</div>
                        <h4>Add Student</h4>
                        <p>Register new student</p>
                    </a>
                </div>
            </div>
            
            <!-- Recent Grade Entries -->
            <?php if (!empty($recent_grades)): ?>
            <div class="recent-activity-section">
                <h3>Recent Grade Entries</h3>
                <div class="activity-card">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Percentage</th>
                                <th>Exam Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_grades as $grade): ?>
                            <tr>
                                <td><a href="student_profile.php?id=<?php echo $grade['student_id']; ?>" class="student-link"><?php echo htmlspecialchars($grade['student_name']); ?></a></td>
                                <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($grade['marks']); ?> / <?php echo htmlspecialchars($grade['max_marks']); ?></td>
                                <td><span class="percentage-badge"><?php echo htmlspecialchars($grade['percentage']); ?>%</span></td>
                                <td><?php echo date('M j, Y', strtotime($grade['exam_date'])); ?></td>
                                <td><a href="grades.php?student_id=<?php echo $grade['student_id']; ?>" class="btn btn-sm btn-primary">View Grades</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Attendance Statistics -->
            <?php
            // Fetch attendance statistics by student
            $attendance_stats = [];
            try {
                $attendance_stats_sql = "SELECT s.id, s.name,
                                               COUNT(a.id) as total_days,
                                               SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                                               ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id)), 1) as attendance_percentage
                                        FROM students s
                                        INNER JOIN attendance a ON s.id = a.student_id
                                        WHERE a.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                        GROUP BY s.id, s.name
                                        HAVING attendance_percentage < 75
                                        ORDER BY attendance_percentage ASC
                                        LIMIT 5";
                $attendance_stats_stmt = $pdo->query($attendance_stats_sql);
                $attendance_stats = $attendance_stats_stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Table might not exist yet, ignore error
                $attendance_stats = [];
            }
            ?>
            <?php if (!empty($attendance_stats)): ?>
            <div class="recent-activity-section">
                <h3>Low Attendance Alert (Last 30 Days)</h3>
                <div class="activity-card">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Total Days</th>
                                <th>Present Days</th>
                                <th>Attendance %</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_stats as $stat): ?>
                            <tr>
                                <td><a href="student_profile.php?id=<?php echo $stat['id']; ?>" class="student-link"><?php echo htmlspecialchars($stat['name']); ?></a></td>
                                <td><?php echo htmlspecialchars($stat['total_days']); ?></td>
                                <td><?php echo htmlspecialchars($stat['present_days']); ?></td>
                                <td>
                                    <span class="percentage-badge" style="background-color: <?php echo $stat['attendance_percentage'] < 50 ? '#fadbd8' : '#fff3cd'; ?>; color: <?php echo $stat['attendance_percentage'] < 50 ? '#e74c3c' : '#f39c12'; ?>;">
                                        <?php echo htmlspecialchars($stat['attendance_percentage']); ?>%
                                    </span>
                                </td>
                                <td><a href="attendance.php?student_id=<?php echo $stat['id']; ?>" class="btn btn-sm btn-info">View Attendance</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Pending Fees Summary -->
            <?php
            // Fetch students with pending fees
            $pending_fees_students = [];
            try {
                $pending_fees_sql = "SELECT s.id, s.name, 
                                           SUM(f.assigned_amount - f.paid_amount) as balance,
                                           MIN(f.due_date) as earliest_due_date,
                                           MAX(CASE WHEN f.due_date < CURDATE() THEN 1 ELSE 0 END) as is_overdue
                                    FROM students s
                                    INNER JOIN fees f ON s.id = f.student_id
                                    WHERE f.status IN ('pending', 'partial', 'overdue')
                                    GROUP BY s.id, s.name
                                    HAVING balance > 0
                                    ORDER BY is_overdue DESC, earliest_due_date ASC
                                    LIMIT 5";
                $pending_fees_stmt = $pdo->query($pending_fees_sql);
                $pending_fees_students = $pending_fees_stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Table might not exist yet, ignore error
                $pending_fees_students = [];
            }
            ?>
            <?php if (!empty($pending_fees_students)): ?>
            <div class="recent-activity-section">
                <h3>Pending Fees Summary</h3>
                <div class="activity-card">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Outstanding Balance</th>
                                <th>Earliest Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_fees_students as $fee_student): ?>
                            <tr>
                                <td><a href="student_profile.php?id=<?php echo $fee_student['id']; ?>" class="student-link"><?php echo htmlspecialchars($fee_student['name']); ?></a></td>
                                <td><strong>$<?php echo number_format($fee_student['balance'], 2); ?></strong></td>
                                <td><?php echo date('M j, Y', strtotime($fee_student['earliest_due_date'])); ?></td>
                                <td>
                                    <?php if ($fee_student['is_overdue']): ?>
                                        <span class="status-badge status-overdue">⚠ Overdue</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">⏳ Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><a href="fees.php?student_id=<?php echo $fee_student['id']; ?>" class="btn btn-sm btn-warning">Record Payment</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Charts Section -->
            <div class="charts-container">
                <div class="chart-card">
                    <h3>Performance Overview</h3>
                    <canvas id="performanceChart"></canvas>
                </div>
                
                <div class="chart-card">
                    <h3>Students per Course</h3>
                    <canvas id="courseChart"></canvas>
                </div>
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h2>Registered Students</h2>
                    
                    <!-- Search and Filter Controls -->
                    <div class="search-filter-container">
                        <div class="search-box">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search by name, email, phone, or course...">
                        </div>
                        <div class="filter-box">
                            <select id="courseFilter" class="filter-select">
                                <option value="">All Courses</option>
                                <?php foreach (array_keys($courses) as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course); ?>"><?php echo htmlspecialchars($course); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select id="genderFilter" class="filter-select">
                                <option value="">All Genders</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
=======
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
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9

                <!-- Students Table -->
                <?php if (empty($students)): ?>
                    <div class="message">No students registered yet.</div>
                <?php else: ?>
<<<<<<< HEAD
                    <div id="noResultsMessage" class="no-results-message" style="display: none;">
                        No students found matching your search criteria.
                    </div>
                    <table class="students-table" id="studentsTable">
=======
                    <table class="students-table">
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Course</th>
                                <th>Gender</th>
<<<<<<< HEAD
                                <th>Fee Status</th>
=======
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
<<<<<<< HEAD
                        <tbody id="studentsTableBody">
                            <?php foreach ($students as $student): ?>
                                <tr data-name="<?php echo htmlspecialchars(strtolower($student['name'])); ?>" 
                                    data-email="<?php echo htmlspecialchars(strtolower($student['email'])); ?>" 
                                    data-phone="<?php echo htmlspecialchars($student['phone']); ?>" 
                                    data-course="<?php echo htmlspecialchars($student['course']); ?>"
                                    data-gender="<?php echo htmlspecialchars($student['gender']); ?>">
                                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                                    <td><a href="student_profile.php?id=<?php echo $student['id']; ?>" class="student-link"><?php echo htmlspecialchars($student['name']); ?></a></td>
=======
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($student['course']); ?></td>
                                    <td><?php echo htmlspecialchars($student['gender']); ?></td>
<<<<<<< HEAD
                                    <td>
                                        <?php if ($student['fee_status'] === 'paid' || $student['fee_status'] === 'none'): ?>
                                            <span class="status-badge status-paid">✓ Paid</span>
                                        <?php elseif ($student['fee_status'] === 'partial'): ?>
                                            <span class="status-badge status-partial">⚠ Partial ($<?php echo number_format($student['outstanding_balance'], 2); ?>)</span>
                                        <?php elseif ($student['fee_status'] === 'overdue'): ?>
                                            <span class="status-badge status-overdue">⚠ Overdue ($<?php echo number_format($student['outstanding_balance'], 2); ?>)</span>
                                        <?php elseif ($student['fee_status'] === 'pending'): ?>
                                            <span class="status-badge status-pending">⏳ Pending ($<?php echo number_format($student['outstanding_balance'], 2); ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($student['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="student_profile.php?id=<?php echo $student['id']; ?>" class="btn btn-success btn-sm">View Profile</a>
                                        <a href="grades.php?student_id=<?php echo $student['id']; ?>" class="btn btn-info btn-sm" title="Manage Grades">📝 Grades</a>
                                        <a href="fees.php?student_id=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm" title="Manage Fees">💳 Fees</a>
                                        <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <a href="dashboard.php?delete_id=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
=======
                                    <td><?php echo date('M j, Y g:i A', strtotime($student['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary">Edit</a>
                                        <a href="dashboard.php?delete_id=<?php echo $student['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
<<<<<<< HEAD
=======
                    
                    <!-- Summary -->
                    <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                        <strong>Total Students: <?php echo count($students); ?></strong>
                    </div>
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Student Management System. All rights reserved.</p>
        </div>
    </footer>

<<<<<<< HEAD
    <script src="js/loading.js"></script>
    <script src="js/script.js"></script>
    <script>
        // Show loading on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loading overlay after page is fully loaded
            hideLoading();
            
            // Auto-hide notifications after 5 seconds
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 300);
                }, 5000);
            });
            
            // Initialize search and filter functionality
            initializeSearchAndFilter();
            
            // Add fade-in animation to content
            fadeInElements('.stat-card', 100);
            fadeInElements('.action-card', 100);
        });
        
        // Search and Filter Functionality
        function initializeSearchAndFilter() {
            const searchInput = document.getElementById('searchInput');
            const courseFilter = document.getElementById('courseFilter');
            const genderFilter = document.getElementById('genderFilter');
            const tableBody = document.getElementById('studentsTableBody');
            const noResultsMessage = document.getElementById('noResultsMessage');
            const studentsTable = document.getElementById('studentsTable');
            
            if (!searchInput || !tableBody) return;
            
            // Load saved search state from sessionStorage
            const savedSearch = sessionStorage.getItem('dashboardSearch');
            const savedCourse = sessionStorage.getItem('dashboardCourseFilter');
            const savedGender = sessionStorage.getItem('dashboardGenderFilter');
            
            if (savedSearch) searchInput.value = savedSearch;
            if (savedCourse && courseFilter) courseFilter.value = savedCourse;
            if (savedGender && genderFilter) genderFilter.value = savedGender;
            
            // Apply filters on page load if there are saved values
            if (savedSearch || savedCourse || savedGender) {
                filterTable();
            }
            
            // Search input event
            searchInput.addEventListener('input', function() {
                sessionStorage.setItem('dashboardSearch', this.value);
                filterTable();
            });
            
            // Course filter event
            if (courseFilter) {
                courseFilter.addEventListener('change', function() {
                    sessionStorage.setItem('dashboardCourseFilter', this.value);
                    filterTable();
                });
            }
            
            // Gender filter event
            if (genderFilter) {
                genderFilter.addEventListener('change', function() {
                    sessionStorage.setItem('dashboardGenderFilter', this.value);
                    filterTable();
                });
            }
            
            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const courseValue = courseFilter ? courseFilter.value : '';
                const genderValue = genderFilter ? genderFilter.value : '';
                const rows = tableBody.getElementsByTagName('tr');
                let visibleCount = 0;
                
                for (let row of rows) {
                    const name = row.getAttribute('data-name') || '';
                    const email = row.getAttribute('data-email') || '';
                    const phone = row.getAttribute('data-phone') || '';
                    const course = row.getAttribute('data-course') || '';
                    const gender = row.getAttribute('data-gender') || '';
                    
                    // Check search term
                    const matchesSearch = !searchTerm || 
                        name.includes(searchTerm) || 
                        email.includes(searchTerm) || 
                        phone.includes(searchTerm) || 
                        course.toLowerCase().includes(searchTerm);
                    
                    // Check course filter
                    const matchesCourse = !courseValue || course === courseValue;
                    
                    // Check gender filter
                    const matchesGender = !genderValue || gender === genderValue;
                    
                    // Show or hide row
                    if (matchesSearch && matchesCourse && matchesGender) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                }
                
                // Show/hide no results message
                if (visibleCount === 0) {
                    noResultsMessage.style.display = 'block';
                    studentsTable.style.display = 'none';
                } else {
                    noResultsMessage.style.display = 'none';
                    studentsTable.style.display = 'table';
                }
            }
        }
        
        // Performance Overview Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: ['Average Grades', 'Attendance Rate', 'Fee Collection'],
                datasets: [{
                    label: 'Performance Metrics (%)',
                    data: [
                        <?php echo $average_grade; ?>, 
                        <?php echo $average_attendance; ?>, 
                        <?php echo $pending_fees > 0 ? round((1 - ($pending_fees / (($pending_fees + 1) * 1.5))) * 100, 1) : 100; ?>
                    ],
                    backgroundColor: ['#9b59b6', '#27ae60', '#f39c12'],
                    borderColor: ['#8e44ad', '#229954', '#e67e22'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Course Distribution Chart
        const courseCtx = document.getElementById('courseChart').getContext('2d');
        new Chart(courseCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($courses)); ?>,
                datasets: [{
                    label: 'Number of Students',
                    data: <?php echo json_encode(array_values($courses)); ?>,
                    backgroundColor: '#667eea',
                    borderColor: '#764ba2',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
=======
    <script src="js/script.js"></script>
>>>>>>> 3cb5da58f31c0757cef5d1e30b5efadd9d2234a9
</body>
</html>