<?php
// Check authentication
require_once 'auth_check.php';

// Include database configuration
require_once 'config/database.php';

// Get student ID from URL parameter
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id <= 0) {
    $_SESSION['error_message'] = "Invalid student ID";
    header('Location: dashboard.php');
    exit;
}

// Fetch student personal information
try {
    $sql = "SELECT * FROM students WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        $_SESSION['error_message'] = "Student not found";
        header('Location: dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching student data: " . $e->getMessage();
    header('Location: dashboard.php');
    exit;
}

// Fetch and aggregate grades data with calculations
try {
    $grades_sql = "SELECT id, subject_name, marks, max_marks, exam_type, exam_date,
                          ROUND((marks / max_marks) * 100, 2) as percentage
                   FROM grades 
                   WHERE student_id = :student_id 
                   ORDER BY exam_date DESC";
    $grades_stmt = $pdo->prepare($grades_sql);
    $grades_stmt->execute(['student_id' => $student_id]);
    $grades = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals and averages
    $total_marks = 0;
    $total_max_marks = 0;
    
    foreach ($grades as $grade) {
        $total_marks += $grade['marks'];
        $total_max_marks += $grade['max_marks'];
    }
    
    $average_percentage = $total_max_marks > 0 ? round(($total_marks / $total_max_marks) * 100, 2) : 0;
    $grade_count = count($grades);
} catch (PDOException $e) {
    $grades = [];
    $total_marks = 0;
    $total_max_marks = 0;
    $average_percentage = 0;
    $grade_count = 0;
}

// Fetch and aggregate fees data with balance calculations
try {
    $fees_sql = "SELECT id, fee_type, assigned_amount, paid_amount,
                        (assigned_amount - paid_amount) as outstanding_balance,
                        due_date, payment_date, status
                 FROM fees 
                 WHERE student_id = :student_id 
                 ORDER BY due_date DESC";
    $fees_stmt = $pdo->prepare($fees_sql);
    $fees_stmt->execute(['student_id' => $student_id]);
    $fees = $fees_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate fee totals
    $total_assigned = 0;
    $total_paid = 0;
    $total_outstanding = 0;
    
    foreach ($fees as $fee) {
        $total_assigned += $fee['assigned_amount'];
        $total_paid += $fee['paid_amount'];
        $total_outstanding += $fee['outstanding_balance'];
    }
    
    $fee_count = count($fees);
} catch (PDOException $e) {
    $fees = [];
    $total_assigned = 0;
    $total_paid = 0;
    $total_outstanding = 0;
    $fee_count = 0;
}

// Fetch and aggregate attendance data with percentage
try {
    $attendance_sql = "SELECT id, attendance_date, status, notes
                       FROM attendance 
                       WHERE student_id = :student_id 
                       ORDER BY attendance_date DESC
                       LIMIT 30";
    $attendance_stmt = $pdo->prepare($attendance_sql);
    $attendance_stmt->execute(['student_id' => $student_id]);
    $attendance_records = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate attendance statistics
    $attendance_stats_sql = "SELECT 
                                COUNT(*) as total_days,
                                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count,
                                SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as attended_count
                             FROM attendance 
                             WHERE student_id = :student_id";
    $attendance_stats_stmt = $pdo->prepare($attendance_stats_sql);
    $attendance_stats_stmt->execute(['student_id' => $student_id]);
    $attendance_stats = $attendance_stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_days = intval($attendance_stats['total_days']);
    $attended_count = intval($attendance_stats['attended_count']);
    $attendance_percentage = $total_days > 0 ? round(($attended_count / $total_days) * 100, 2) : 0;
    
    $present_count = intval($attendance_stats['present_count']);
    $absent_count = intval($attendance_stats['absent_count']);
    $late_count = intval($attendance_stats['late_count']);
    $excused_count = intval($attendance_stats['excused_count']);
} catch (PDOException $e) {
    $attendance_records = [];
    $total_days = 0;
    $attendance_percentage = 0;
    $present_count = 0;
    $absent_count = 0;
    $late_count = 0;
    $excused_count = 0;
}

// Function to generate performance metrics
function generatePerformanceMetrics($student_id, $pdo, $course_filter = null, $start_date = null, $end_date = null) {
    $metrics = [
        'grades' => ['available' => false, 'data' => null],
        'attendance' => ['available' => false, 'data' => null],
        'fees' => ['available' => false, 'data' => null],
        'overall_status' => 'incomplete'
    ];
    
    // Grades metrics with date filtering
    try {
        $grades_sql = "SELECT 
                          COUNT(*) as total_subjects,
                          SUM(marks) as total_marks,
                          SUM(max_marks) as total_max_marks,
                          ROUND(AVG((marks / max_marks) * 100), 2) as average_percentage
                       FROM grades 
                       WHERE student_id = :student_id";
        
        $params = ['student_id' => $student_id];
        
        if ($start_date && $end_date) {
            $grades_sql .= " AND exam_date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $start_date;
            $params['end_date'] = $end_date;
        }
        
        $grades_stmt = $pdo->prepare($grades_sql);
        $grades_stmt->execute($params);
        $grades_data = $grades_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($grades_data['total_subjects'] > 0) {
            $metrics['grades']['available'] = true;
            $metrics['grades']['data'] = [
                'total_subjects' => intval($grades_data['total_subjects']),
                'total_marks' => floatval($grades_data['total_marks']),
                'total_max_marks' => floatval($grades_data['total_max_marks']),
                'average_percentage' => floatval($grades_data['average_percentage'])
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching grades metrics: " . $e->getMessage());
    }
    
    // Attendance metrics with date filtering
    try {
        $attendance_sql = "SELECT 
                              COUNT(*) as total_days,
                              SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as attended_days,
                              SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                              SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
                           FROM attendance 
                           WHERE student_id = :student_id";
        
        $params = ['student_id' => $student_id];
        
        if ($start_date && $end_date) {
            $attendance_sql .= " AND attendance_date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $start_date;
            $params['end_date'] = $end_date;
        }
        
        $attendance_stmt = $pdo->prepare($attendance_sql);
        $attendance_stmt->execute($params);
        $attendance_data = $attendance_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($attendance_data['total_days'] > 0) {
            $metrics['attendance']['available'] = true;
            $attendance_percentage = round(($attendance_data['attended_days'] / $attendance_data['total_days']) * 100, 2);
            $metrics['attendance']['data'] = [
                'total_days' => intval($attendance_data['total_days']),
                'attended_days' => intval($attendance_data['attended_days']),
                'present_days' => intval($attendance_data['present_days']),
                'absent_days' => intval($attendance_data['absent_days']),
                'attendance_percentage' => $attendance_percentage
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching attendance metrics: " . $e->getMessage());
    }
    
    // Fees metrics with date filtering
    try {
        $fees_sql = "SELECT 
                        COUNT(*) as total_fees,
                        SUM(assigned_amount) as total_assigned,
                        SUM(paid_amount) as total_paid,
                        SUM(assigned_amount - paid_amount) as outstanding_balance,
                        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                        SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_count
                     FROM fees 
                     WHERE student_id = :student_id";
        
        $params = ['student_id' => $student_id];
        
        if ($start_date && $end_date) {
            $fees_sql .= " AND due_date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $start_date;
            $params['end_date'] = $end_date;
        }
        
        $fees_stmt = $pdo->prepare($fees_sql);
        $fees_stmt->execute($params);
        $fees_data = $fees_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fees_data['total_fees'] > 0) {
            $metrics['fees']['available'] = true;
            $payment_percentage = $fees_data['total_assigned'] > 0 ? 
                round(($fees_data['total_paid'] / $fees_data['total_assigned']) * 100, 2) : 0;
            $metrics['fees']['data'] = [
                'total_fees' => intval($fees_data['total_fees']),
                'total_assigned' => floatval($fees_data['total_assigned']),
                'total_paid' => floatval($fees_data['total_paid']),
                'outstanding_balance' => floatval($fees_data['outstanding_balance']),
                'paid_count' => intval($fees_data['paid_count']),
                'overdue_count' => intval($fees_data['overdue_count']),
                'payment_percentage' => $payment_percentage
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching fees metrics: " . $e->getMessage());
    }
    
    // Determine overall status
    $available_count = 0;
    if ($metrics['grades']['available']) $available_count++;
    if ($metrics['attendance']['available']) $available_count++;
    if ($metrics['fees']['available']) $available_count++;
    
    if ($available_count === 3) {
        $metrics['overall_status'] = 'complete';
    } elseif ($available_count > 0) {
        $metrics['overall_status'] = 'partial';
    }
    
    return $metrics;
}

// Function to calculate class rankings based on average grades
function calculateClassRanking($student_id, $pdo, $course_filter = null) {
    try {
        // Calculate average for all students
        $ranking_sql = "SELECT 
                           s.id,
                           s.name,
                           s.course,
                           COALESCE(AVG((g.marks / g.max_marks) * 100), 0) as average_percentage
                        FROM students s
                        LEFT JOIN grades g ON s.id = g.student_id
                        WHERE 1=1";
        
        $params = [];
        
        if ($course_filter) {
            $ranking_sql .= " AND s.course = :course";
            $params['course'] = $course_filter;
        }
        
        $ranking_sql .= " GROUP BY s.id, s.name, s.course
                         HAVING COUNT(g.id) > 0
                         ORDER BY average_percentage DESC";
        
        $ranking_stmt = $pdo->prepare($ranking_sql);
        $ranking_stmt->execute($params);
        $rankings = $ranking_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Find current student's rank
        $rank = 0;
        $total_students = count($rankings);
        $student_average = 0;
        
        foreach ($rankings as $index => $student) {
            if ($student['id'] == $student_id) {
                $rank = $index + 1;
                $student_average = $student['average_percentage'];
                break;
            }
        }
        
        return [
            'rank' => $rank,
            'total_students' => $total_students,
            'student_average' => round($student_average, 2),
            'rankings' => $rankings,
            'available' => $rank > 0
        ];
    } catch (PDOException $e) {
        error_log("Error calculating class ranking: " . $e->getMessage());
        return [
            'rank' => 0,
            'total_students' => 0,
            'student_average' => 0,
            'rankings' => [],
            'available' => false
        ];
    }
}

// Get filter parameters
$course_filter = isset($_GET['course']) && !empty($_GET['course']) ? $_GET['course'] : null;
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

// Generate performance metrics with filters
$performance_metrics = generatePerformanceMetrics($student_id, $pdo, $course_filter, $start_date, $end_date);

// Calculate class ranking (use student's course if no filter specified)
$ranking_course = $course_filter ?: $student['course'];
$class_ranking = calculateClassRanking($student_id, $pdo, $ranking_course);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - <?php echo htmlspecialchars($student['name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Student Profile</h1>
                <ul class="nav-menu">
                    <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                    <li><a href="registration.php" class="nav-link">Register Student</a></li>
                    <li><a href="grades.php" class="nav-link">Grades</a></li>
                    <li><a href="fees.php" class="nav-link">Fees</a></li>
                    <li><a href="attendance.php" class="nav-link">Attendance</a></li>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <!-- Back Button -->
            <div style="margin-bottom: 20px;">
                <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>

            <!-- Personal Information Card -->
            <div class="card" style="margin-bottom: 30px;">
                <h2>Personal Information</h2>
                <div class="profile-info-grid">
                    <div class="profile-info-item">
                        <strong>Student ID:</strong>
                        <span><?php echo htmlspecialchars($student['id']); ?></span>
                    </div>
                    <div class="profile-info-item">
                        <strong>Name:</strong>
                        <span><?php echo htmlspecialchars($student['name']); ?></span>
                    </div>
                    <div class="profile-info-item">
                        <strong>Email:</strong>
                        <span><?php echo htmlspecialchars($student['email']); ?></span>
                    </div>
                    <div class="profile-info-item">
                        <strong>Phone:</strong>
                        <span><?php echo htmlspecialchars($student['phone']); ?></span>
                    </div>
                    <div class="profile-info-item">
                        <strong>Course:</strong>
                        <span><?php echo htmlspecialchars($student['course']); ?></span>
                    </div>
                    <div class="profile-info-item">
                        <strong>Gender:</strong>
                        <span><?php echo htmlspecialchars($student['gender']); ?></span>
                    </div>
                    <div class="profile-info-item">
                        <strong>Registration Date:</strong>
                        <span><?php echo date('M j, Y', strtotime($student['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Performance Summary Cards -->
            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-content">
                        <h3><?php echo $average_percentage; ?>%</h3>
                        <p>Average Grade</p>
                        <small><?php echo $grade_count; ?> subjects</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($total_outstanding, 2); ?></h3>
                        <p>Outstanding Fees</p>
                        <small>$<?php echo number_format($total_paid, 2); ?> paid</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✓</div>
                    <div class="stat-content">
                        <h3><?php echo $attendance_percentage; ?>%</h3>
                        <p>Attendance Rate</p>
                        <small><?php echo $attended_count; ?>/<?php echo $total_days; ?> days</small>
                    </div>
                </div>
            </div>

            <!-- Academic Performance Section -->
            <div class="card" style="margin-bottom: 30px;">
                <h2>Academic Performance</h2>
                
                <?php if (empty($grades)): ?>
                    <p class="message">No grade records found for this student.</p>
                <?php else: ?>
                    <div class="grade-summary" style="margin-bottom: 20px;">
                        <p><strong>Total Marks:</strong> <?php echo $total_marks; ?> / <?php echo $total_max_marks; ?></p>
                        <p><strong>Average Percentage:</strong> <?php echo $average_percentage; ?>%</p>
                        <p><strong>Total Subjects:</strong> <?php echo $grade_count; ?></p>
                    </div>
                    
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Max Marks</th>
                                <th>Percentage</th>
                                <th>Exam Type</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['marks']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['max_marks']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['percentage']); ?>%</td>
                                    <td><?php echo htmlspecialchars($grade['exam_type']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($grade['exam_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Fee Status Section -->
            <div class="card" style="margin-bottom: 30px;">
                <h2>Fee Status</h2>
                
                <?php if (empty($fees)): ?>
                    <p class="message">No fee records found for this student.</p>
                <?php else: ?>
                    <div class="fee-summary" style="margin-bottom: 20px;">
                        <p><strong>Total Assigned:</strong> $<?php echo number_format($total_assigned, 2); ?></p>
                        <p><strong>Total Paid:</strong> $<?php echo number_format($total_paid, 2); ?></p>
                        <p><strong>Outstanding Balance:</strong> <span style="color: <?php echo $total_outstanding > 0 ? '#e74c3c' : '#27ae60'; ?>;">$<?php echo number_format($total_outstanding, 2); ?></span></p>
                    </div>
                    
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Fee Type</th>
                                <th>Assigned</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fees as $fee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fee['fee_type']); ?></td>
                                    <td>$<?php echo number_format($fee['assigned_amount'], 2); ?></td>
                                    <td>$<?php echo number_format($fee['paid_amount'], 2); ?></td>
                                    <td>$<?php echo number_format($fee['outstanding_balance'], 2); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($fee['due_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $fee['status']; ?>">
                                            <?php echo ucfirst($fee['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Performance Report Section -->
            <div class="card" style="margin-bottom: 30px;">
                <h2>Performance Report</h2>
                
                <!-- Filter Form -->
                <form method="GET" action="student_profile.php" style="margin-bottom: 20px;">
                    <input type="hidden" name="id" value="<?php echo $student_id; ?>">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label for="start_date">Start Date:</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date ?: ''); ?>" class="form-input">
                        </div>
                        <div>
                            <label for="end_date">End Date:</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date ?: ''); ?>" class="form-input">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="student_profile.php?id=<?php echo $student_id; ?>" class="btn btn-secondary">Clear Filters</a>
                </form>

                <!-- Performance Metrics Display -->
                <div class="performance-metrics">
                    <div class="metric-status">
                        <strong>Report Status:</strong> 
                        <span class="status-badge status-<?php echo $performance_metrics['overall_status']; ?>">
                            <?php echo ucfirst($performance_metrics['overall_status']); ?>
                        </span>
                        <?php if ($performance_metrics['overall_status'] === 'incomplete'): ?>
                            <p class="message" style="margin-top: 10px;">No data available for the selected period.</p>
                        <?php elseif ($performance_metrics['overall_status'] === 'partial'): ?>
                            <p class="message" style="margin-top: 10px;">Some data is missing. Report shows available information only.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Grades Metrics -->
                    <?php if ($performance_metrics['grades']['available']): ?>
                        <div class="metric-section" style="margin-top: 20px;">
                            <h3>📚 Academic Performance</h3>
                            <div class="metric-grid">
                                <div class="metric-item">
                                    <span class="metric-label">Total Subjects:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['grades']['data']['total_subjects']; ?></span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Total Marks:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['grades']['data']['total_marks']; ?> / <?php echo $performance_metrics['grades']['data']['total_max_marks']; ?></span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Average Percentage:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['grades']['data']['average_percentage']; ?>%</span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="metric-section" style="margin-top: 20px;">
                            <h3>📚 Academic Performance</h3>
                            <p class="message">No grade data available for the selected period.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Attendance Metrics -->
                    <?php if ($performance_metrics['attendance']['available']): ?>
                        <div class="metric-section" style="margin-top: 20px;">
                            <h3>✓ Attendance Performance</h3>
                            <div class="metric-grid">
                                <div class="metric-item">
                                    <span class="metric-label">Total Days:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['attendance']['data']['total_days']; ?></span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Attended Days:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['attendance']['data']['attended_days']; ?></span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Present Days:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['attendance']['data']['present_days']; ?></span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Absent Days:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['attendance']['data']['absent_days']; ?></span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Attendance Rate:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['attendance']['data']['attendance_percentage']; ?>%</span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="metric-section" style="margin-top: 20px;">
                            <h3>✓ Attendance Performance</h3>
                            <p class="message">No attendance data available for the selected period.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Fees Metrics -->
                    <?php if ($performance_metrics['fees']['available']): ?>
                        <div class="metric-section" style="margin-top: 20px;">
                            <h3>💰 Fee Payment Performance</h3>
                            <div class="metric-grid">
                                <div class="metric-item">
                                    <span class="metric-label">Total Fees:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['fees']['data']['total_fees']; ?></span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Total Assigned:</span>
                                    <span class="metric-value">$<?php echo number_format($performance_metrics['fees']['data']['total_assigned'], 2); ?></span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Total Paid:</span>
                                    <span class="metric-value">$<?php echo number_format($performance_metrics['fees']['data']['total_paid'], 2); ?></span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Outstanding Balance:</span>
                                    <span class="metric-value" style="color: <?php echo $performance_metrics['fees']['data']['outstanding_balance'] > 0 ? '#e74c3c' : '#27ae60'; ?>;">
                                        $<?php echo number_format($performance_metrics['fees']['data']['outstanding_balance'], 2); ?>
                                    </span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Payment Rate:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['fees']['data']['payment_percentage']; ?>%</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Paid Fees:</span>
                                    <span class="metric-value"><?php echo $performance_metrics['fees']['data']['paid_count']; ?></span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Overdue Fees:</span>
                                    <span class="metric-value" style="color: <?php echo $performance_metrics['fees']['data']['overdue_count'] > 0 ? '#e74c3c' : '#27ae60'; ?>;">
                                        <?php echo $performance_metrics['fees']['data']['overdue_count']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="metric-section" style="margin-top: 20px;">
                            <h3>💰 Fee Payment Performance</h3>
                            <p class="message">No fee data available for the selected period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Class Ranking Section -->
            <div class="card" style="margin-bottom: 30px;">
                <h2>Class Ranking</h2>
                
                <?php if ($class_ranking['available']): ?>
                    <div class="ranking-summary" style="margin-bottom: 20px;">
                        <div class="ranking-highlight">
                            <h3>Your Rank: <?php echo $class_ranking['rank']; ?> / <?php echo $class_ranking['total_students']; ?></h3>
                            <p>Course: <?php echo htmlspecialchars($ranking_course); ?></p>
                            <p>Your Average: <?php echo $class_ranking['student_average']; ?>%</p>
                        </div>
                    </div>

                    <h3>Top 10 Students in <?php echo htmlspecialchars($ranking_course); ?></h3>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Name</th>
                                <th>Average Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $top_students = array_slice($class_ranking['rankings'], 0, 10);
                            foreach ($top_students as $index => $ranked_student): 
                                $is_current = $ranked_student['id'] == $student_id;
                                $row_style = $is_current ? ' style="background-color: #fff3cd; font-weight: bold;"' : '';
                            ?>
                                <tr<?php echo $row_style; ?>>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($ranked_student['name']); ?>
                                        <?php if ($is_current): ?>
                                            <span style="color: #856404;">(You)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo round($ranked_student['average_percentage'], 2); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="message">No ranking data available. Grade records are required for ranking calculation.</p>
                <?php endif; ?>
            </div>

            <!-- Attendance Section -->
            <div class="card" style="margin-bottom: 30px;">
                <h2>Attendance Record</h2>
                
                <?php if ($total_days === 0): ?>
                    <p class="message">No attendance records found for this student.</p>
                <?php else: ?>
                    <div class="attendance-summary" style="margin-bottom: 20px;">
                        <p><strong>Total Days:</strong> <?php echo $total_days; ?></p>
                        <p><strong>Present:</strong> <?php echo $present_count; ?> days</p>
                        <p><strong>Absent:</strong> <?php echo $absent_count; ?> days</p>
                        <p><strong>Late:</strong> <?php echo $late_count; ?> days</p>
                        <p><strong>Excused:</strong> <?php echo $excused_count; ?> days</p>
                        <p><strong>Attendance Percentage:</strong> <?php echo $attendance_percentage; ?>%</p>
                    </div>
                    
                    <h3>Recent Attendance (Last 30 Days)</h3>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_records as $record): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($record['attendance_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $record['status']; ?>">
                                            <?php echo ucfirst($record['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['notes'] ?: '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Student Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
