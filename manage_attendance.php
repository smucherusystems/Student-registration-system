<?php
// Include database configuration
require_once 'config/database.php';

// Start session
session_start();

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get action parameter
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'mark':
            handleMarkAttendance($pdo);
            break;
        
        case 'update':
            handleUpdateAttendance($pdo);
            break;
        
        case 'fetch':
            handleFetchAttendance($pdo);
            break;
        
        case 'fetch_all':
            handleFetchAllAttendance($pdo);
            break;
        
        case 'calculate_percentage':
            handleCalculatePercentage($pdo);
            break;
        
        case 'fetch_statistics':
            handleFetchStatistics($pdo);
            break;
        
        case 'fetch_calendar':
            handleFetchCalendar($pdo);
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Attendance management error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}

/**
 * Mark attendance for a student
 */
function handleMarkAttendance($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Get and validate input
    $student_id = trim($_POST['student_id'] ?? '');
    $attendance_date = trim($_POST['attendance_date'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($student_id) || !is_numeric($student_id)) {
        $errors[] = 'Valid student ID is required';
    }
    
    if (empty($attendance_date)) {
        $errors[] = 'Attendance date is required';
    } else {
        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $attendance_date);
        if (!$date || $date->format('Y-m-d') !== $attendance_date) {
            $errors[] = 'Invalid date format (use YYYY-MM-DD)';
        }
    }
    
    $valid_statuses = ['present', 'absent', 'late', 'excused'];
    if (empty($status) || !in_array($status, $valid_statuses)) {
        $errors[] = 'Valid status is required (present, absent, late, or excused)';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Check if student exists
    $check_sql = "SELECT id FROM students WHERE id = :student_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['student_id' => $student_id]);
    
    if (!$check_stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        return;
    }
    
    // Check for duplicate entry (unique constraint validation)
    $duplicate_sql = "SELECT id FROM attendance WHERE student_id = :student_id AND attendance_date = :attendance_date";
    $duplicate_stmt = $pdo->prepare($duplicate_sql);
    $duplicate_stmt->execute([
        'student_id' => $student_id,
        'attendance_date' => $attendance_date
    ]);
    
    if ($duplicate_stmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Attendance already marked for this student on this date. Use update action to modify.'
        ]);
        return;
    }
    
    // Insert attendance record
    $sql = "INSERT INTO attendance (student_id, attendance_date, status, notes) 
            VALUES (:student_id, :attendance_date, :status, :notes)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'student_id' => $student_id,
        'attendance_date' => $attendance_date,
        'status' => $status,
        'notes' => $notes
    ]);
    
    if ($result) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Attendance marked successfully',
            'attendance_id' => $pdo->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to mark attendance']);
    }
}

/**
 * Update attendance status for an existing record
 */
function handleUpdateAttendance($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Get and validate input
    $attendance_id = trim($_POST['attendance_id'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($attendance_id) || !is_numeric($attendance_id)) {
        $errors[] = 'Valid attendance ID is required';
    }
    
    $valid_statuses = ['present', 'absent', 'late', 'excused'];
    if (empty($status) || !in_array($status, $valid_statuses)) {
        $errors[] = 'Valid status is required (present, absent, late, or excused)';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Check if attendance record exists
    $check_sql = "SELECT id FROM attendance WHERE id = :attendance_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['attendance_id' => $attendance_id]);
    
    if (!$check_stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Attendance record not found']);
        return;
    }
    
    // Update attendance record
    $sql = "UPDATE attendance 
            SET status = :status, 
                notes = :notes 
            WHERE id = :attendance_id";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'attendance_id' => $attendance_id,
        'status' => $status,
        'notes' => $notes
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Attendance updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update attendance']);
    }
}

/**
 * Fetch attendance records by student ID and optional date range
 */
function handleFetchAttendance($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $student_id = trim($_GET['student_id'] ?? '');
    $start_date = trim($_GET['start_date'] ?? '');
    $end_date = trim($_GET['end_date'] ?? '');
    
    // Validation
    if (empty($student_id) || !is_numeric($student_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid student ID is required']);
        return;
    }
    
    // Validate date formats if provided
    if (!empty($start_date)) {
        $date = DateTime::createFromFormat('Y-m-d', $start_date);
        if (!$date || $date->format('Y-m-d') !== $start_date) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid start date format (use YYYY-MM-DD)']);
            return;
        }
    }
    
    if (!empty($end_date)) {
        $date = DateTime::createFromFormat('Y-m-d', $end_date);
        if (!$date || $date->format('Y-m-d') !== $end_date) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid end date format (use YYYY-MM-DD)']);
            return;
        }
    }
    
    // Check if student exists
    $check_sql = "SELECT id, name FROM students WHERE id = :student_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['student_id' => $student_id]);
    $student = $check_stmt->fetch();
    
    if (!$student) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        return;
    }
    
    // Build query with date range filters
    $sql = "SELECT id, attendance_date, status, notes, created_at, updated_at
            FROM attendance 
            WHERE student_id = :student_id";
    
    $params = ['student_id' => $student_id];
    
    // Add date range filters if provided
    if (!empty($start_date)) {
        $sql .= " AND attendance_date >= :start_date";
        $params['start_date'] = $start_date;
    }
    
    if (!empty($end_date)) {
        $sql .= " AND attendance_date <= :end_date";
        $params['end_date'] = $end_date;
    }
    
    $sql .= " ORDER BY attendance_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate attendance statistics
    $total_days = count($attendance_records);
    $present_count = 0;
    $absent_count = 0;
    $late_count = 0;
    $excused_count = 0;
    
    foreach ($attendance_records as $record) {
        switch ($record['status']) {
            case 'present':
                $present_count++;
                break;
            case 'absent':
                $absent_count++;
                break;
            case 'late':
                $late_count++;
                break;
            case 'excused':
                $excused_count++;
                break;
        }
    }
    
    // Calculate attendance percentage (present + late as attended)
    $attended_count = $present_count + $late_count;
    $attendance_percentage = $total_days > 0 ? round(($attended_count / $total_days) * 100, 2) : 0;
    
    echo json_encode([
        'success' => true,
        'student' => [
            'id' => $student['id'],
            'name' => $student['name']
        ],
        'attendance' => $attendance_records,
        'statistics' => [
            'total_days' => $total_days,
            'present_count' => $present_count,
            'absent_count' => $absent_count,
            'late_count' => $late_count,
            'excused_count' => $excused_count,
            'attended_count' => $attended_count,
            'attendance_percentage' => $attendance_percentage
        ],
        'date_range' => [
            'start_date' => $start_date ?: null,
            'end_date' => $end_date ?: null
        ]
    ]);
}

/**
 * Fetch all attendance records with student information
 */
function handleFetchAllAttendance($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Optional filters
    $attendance_date = trim($_GET['attendance_date'] ?? '');
    $status_filter = trim($_GET['status'] ?? '');
    
    // Build query
    $sql = "SELECT a.id, a.student_id, s.name as student_name, s.course,
                   a.attendance_date, a.status, a.notes,
                   a.created_at, a.updated_at
            FROM attendance a
            INNER JOIN students s ON a.student_id = s.id";
    
    $params = [];
    $where_clauses = [];
    
    // Add date filter if provided
    if (!empty($attendance_date)) {
        $date = DateTime::createFromFormat('Y-m-d', $attendance_date);
        if ($date && $date->format('Y-m-d') === $attendance_date) {
            $where_clauses[] = "a.attendance_date = :attendance_date";
            $params['attendance_date'] = $attendance_date;
        }
    }
    
    // Add status filter if provided
    if (!empty($status_filter)) {
        $valid_statuses = ['present', 'absent', 'late', 'excused'];
        if (in_array($status_filter, $valid_statuses)) {
            $where_clauses[] = "a.status = :status";
            $params['status'] = $status_filter;
        }
    }
    
    // Add WHERE clause if filters exist
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    $sql .= " ORDER BY a.attendance_date DESC, s.name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'attendance' => $attendance_records,
        'count' => count($attendance_records)
    ]);
}

/**
 * Calculate attendance percentage for a student
 */
function handleCalculatePercentage($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $student_id = trim($_GET['student_id'] ?? '');
    $start_date = trim($_GET['start_date'] ?? '');
    $end_date = trim($_GET['end_date'] ?? '');
    
    // Validation
    if (empty($student_id) || !is_numeric($student_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid student ID is required']);
        return;
    }
    
    // Check if student exists
    $check_sql = "SELECT id, name FROM students WHERE id = :student_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['student_id' => $student_id]);
    $student = $check_stmt->fetch();
    
    if (!$student) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        return;
    }
    
    $percentage = calculateAttendancePercentage($pdo, $student_id, $start_date, $end_date);
    
    echo json_encode([
        'success' => true,
        'student_id' => intval($student_id),
        'student_name' => $student['name'],
        'attendance_percentage' => $percentage,
        'date_range' => [
            'start_date' => $start_date ?: null,
            'end_date' => $end_date ?: null
        ]
    ]);
}

/**
 * Fetch attendance statistics for dashboard
 */
function handleFetchStatistics($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $statistics = fetchAttendanceStatistics($pdo);
    
    echo json_encode([
        'success' => true,
        'statistics' => $statistics
    ]);
}

/**
 * Fetch attendance calendar data for a student
 */
function handleFetchCalendar($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $student_id = trim($_GET['student_id'] ?? '');
    $month = trim($_GET['month'] ?? '');
    $year = trim($_GET['year'] ?? '');
    
    // Validation
    if (empty($student_id) || !is_numeric($student_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid student ID is required']);
        return;
    }
    
    // Check if student exists
    $check_sql = "SELECT id, name FROM students WHERE id = :student_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['student_id' => $student_id]);
    $student = $check_stmt->fetch();
    
    if (!$student) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        return;
    }
    
    $calendar_data = generateAttendanceCalendar($pdo, $student_id, $month, $year);
    
    echo json_encode([
        'success' => true,
        'student' => [
            'id' => $student['id'],
            'name' => $student['name']
        ],
        'calendar' => $calendar_data
    ]);
}

/**
 * Calculate attendance percentage for a student
 * 
 * @param PDO $pdo Database connection
 * @param int $student_id Student ID
 * @param string $start_date Start date (optional)
 * @param string $end_date End date (optional)
 * @return float Attendance percentage (0-100)
 */
function calculateAttendancePercentage($pdo, $student_id, $start_date = '', $end_date = '') {
    $sql = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as attended_days
            FROM attendance 
            WHERE student_id = :student_id";
    
    $params = ['student_id' => $student_id];
    
    // Add date range filters if provided
    if (!empty($start_date)) {
        $sql .= " AND attendance_date >= :start_date";
        $params['start_date'] = $start_date;
    }
    
    if (!empty($end_date)) {
        $sql .= " AND attendance_date <= :end_date";
        $params['end_date'] = $end_date;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_days = intval($result['total_days']);
    $attended_days = intval($result['attended_days']);
    
    if ($total_days > 0) {
        return round(($attended_days / $total_days) * 100, 2);
    }
    
    return 0.0;
}

/**
 * Fetch attendance statistics for dashboard
 * 
 * @param PDO $pdo Database connection
 * @return array Attendance statistics
 */
function fetchAttendanceStatistics($pdo) {
    $sql = "SELECT 
                COUNT(DISTINCT student_id) as total_students_with_attendance,
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count,
                SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as attended_count
            FROM attendance";
    
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_records = intval($result['total_records']);
    $attended_count = intval($result['attended_count']);
    
    $overall_percentage = $total_records > 0 ? round(($attended_count / $total_records) * 100, 2) : 0;
    
    return [
        'total_students_with_attendance' => intval($result['total_students_with_attendance']),
        'total_records' => $total_records,
        'present_count' => intval($result['present_count']),
        'absent_count' => intval($result['absent_count']),
        'late_count' => intval($result['late_count']),
        'excused_count' => intval($result['excused_count']),
        'attended_count' => $attended_count,
        'overall_attendance_percentage' => $overall_percentage
    ];
}

/**
 * Generate attendance calendar data for a student
 * 
 * @param PDO $pdo Database connection
 * @param int $student_id Student ID
 * @param string $month Month (1-12, optional)
 * @param string $year Year (YYYY, optional)
 * @return array Calendar data with attendance records
 */
function generateAttendanceCalendar($pdo, $student_id, $month = '', $year = '') {
    // Use current month/year if not provided
    if (empty($month) || !is_numeric($month) || $month < 1 || $month > 12) {
        $month = date('n');
    }
    
    if (empty($year) || !is_numeric($year) || $year < 2000 || $year > 2100) {
        $year = date('Y');
    }
    
    $month = intval($month);
    $year = intval($year);
    
    // Get first and last day of the month
    $first_day = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
    $last_day = date('Y-m-d', mktime(0, 0, 0, $month + 1, 0, $year));
    
    // Fetch attendance records for the month
    $sql = "SELECT attendance_date, status, notes
            FROM attendance 
            WHERE student_id = :student_id 
            AND attendance_date >= :first_day 
            AND attendance_date <= :last_day
            ORDER BY attendance_date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'student_id' => $student_id,
        'first_day' => $first_day,
        'last_day' => $last_day
    ]);
    
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create a map of date => attendance data
    $attendance_map = [];
    foreach ($attendance_records as $record) {
        $attendance_map[$record['attendance_date']] = [
            'status' => $record['status'],
            'notes' => $record['notes']
        ];
    }
    
    // Generate calendar structure
    $days_in_month = intval(date('t', mktime(0, 0, 0, $month, 1, $year)));
    $first_day_of_week = intval(date('w', mktime(0, 0, 0, $month, 1, $year))); // 0 (Sunday) to 6 (Saturday)
    
    $calendar_days = [];
    
    // Add empty days for alignment
    for ($i = 0; $i < $first_day_of_week; $i++) {
        $calendar_days[] = [
            'day' => null,
            'date' => null,
            'status' => null,
            'notes' => null,
            'is_empty' => true
        ];
    }
    
    // Add actual days of the month
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $attendance = $attendance_map[$date] ?? null;
        
        $calendar_days[] = [
            'day' => $day,
            'date' => $date,
            'status' => $attendance ? $attendance['status'] : null,
            'notes' => $attendance ? $attendance['notes'] : null,
            'is_empty' => false
        ];
    }
    
    // Calculate statistics for the month
    $present_count = 0;
    $absent_count = 0;
    $late_count = 0;
    $excused_count = 0;
    
    foreach ($attendance_records as $record) {
        switch ($record['status']) {
            case 'present':
                $present_count++;
                break;
            case 'absent':
                $absent_count++;
                break;
            case 'late':
                $late_count++;
                break;
            case 'excused':
                $excused_count++;
                break;
        }
    }
    
    $total_marked_days = count($attendance_records);
    $attended_count = $present_count + $late_count;
    $attendance_percentage = $total_marked_days > 0 ? round(($attended_count / $total_marked_days) * 100, 2) : 0;
    
    return [
        'month' => $month,
        'year' => $year,
        'month_name' => date('F', mktime(0, 0, 0, $month, 1, $year)),
        'days_in_month' => $days_in_month,
        'first_day_of_week' => $first_day_of_week,
        'calendar_days' => $calendar_days,
        'statistics' => [
            'total_marked_days' => $total_marked_days,
            'present_count' => $present_count,
            'absent_count' => $absent_count,
            'late_count' => $late_count,
            'excused_count' => $excused_count,
            'attended_count' => $attended_count,
            'attendance_percentage' => $attendance_percentage
        ]
    ];
}
