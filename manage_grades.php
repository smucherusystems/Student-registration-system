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
        case 'add':
            handleAddGrade($pdo);
            break;
        
        case 'update':
            handleUpdateGrade($pdo);
            break;
        
        case 'delete':
            handleDeleteGrade($pdo);
            break;
        
        case 'fetch':
            handleFetchGrades($pdo);
            break;
        
        case 'fetch_all':
            handleFetchAllGrades($pdo);
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Grade management error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}

/**
 * Add a new grade entry
 */
function handleAddGrade($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Get and validate input
    $student_id = trim($_POST['student_id'] ?? '');
    $subject_name = trim($_POST['subject_name'] ?? '');
    $marks = trim($_POST['marks'] ?? '');
    $max_marks = trim($_POST['max_marks'] ?? '100');
    $exam_type = trim($_POST['exam_type'] ?? '');
    $exam_date = trim($_POST['exam_date'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($student_id) || !is_numeric($student_id)) {
        $errors[] = 'Valid student ID is required';
    }
    
    if (empty($subject_name) || strlen($subject_name) > 100) {
        $errors[] = 'Subject name is required and must be less than 100 characters';
    }
    
    if (empty($marks) || !is_numeric($marks) || $marks < 0) {
        $errors[] = 'Valid marks are required (must be a positive number)';
    }
    
    if (empty($max_marks) || !is_numeric($max_marks) || $max_marks <= 0) {
        $errors[] = 'Valid maximum marks are required (must be greater than 0)';
    }
    
    if (!empty($marks) && !empty($max_marks) && is_numeric($marks) && is_numeric($max_marks)) {
        if (floatval($marks) > floatval($max_marks)) {
            $errors[] = 'Marks cannot exceed maximum marks';
        }
    }
    
    if (empty($exam_type) || strlen($exam_type) > 50) {
        $errors[] = 'Exam type is required and must be less than 50 characters';
    }
    
    if (empty($exam_date)) {
        $errors[] = 'Exam date is required';
    } else {
        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $exam_date);
        if (!$date || $date->format('Y-m-d') !== $exam_date) {
            $errors[] = 'Invalid date format (use YYYY-MM-DD)';
        }
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
    
    // Insert grade
    $sql = "INSERT INTO grades (student_id, subject_name, marks, max_marks, exam_type, exam_date) 
            VALUES (:student_id, :subject_name, :marks, :max_marks, :exam_type, :exam_date)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'student_id' => $student_id,
        'subject_name' => $subject_name,
        'marks' => $marks,
        'max_marks' => $max_marks,
        'exam_type' => $exam_type,
        'exam_date' => $exam_date
    ]);
    
    if ($result) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Grade added successfully',
            'grade_id' => $pdo->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add grade']);
    }
}

/**
 * Update an existing grade entry
 */
function handleUpdateGrade($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Get and validate input
    $grade_id = trim($_POST['grade_id'] ?? '');
    $subject_name = trim($_POST['subject_name'] ?? '');
    $marks = trim($_POST['marks'] ?? '');
    $max_marks = trim($_POST['max_marks'] ?? '');
    $exam_type = trim($_POST['exam_type'] ?? '');
    $exam_date = trim($_POST['exam_date'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($grade_id) || !is_numeric($grade_id)) {
        $errors[] = 'Valid grade ID is required';
    }
    
    if (empty($subject_name) || strlen($subject_name) > 100) {
        $errors[] = 'Subject name is required and must be less than 100 characters';
    }
    
    if (empty($marks) || !is_numeric($marks) || $marks < 0) {
        $errors[] = 'Valid marks are required (must be a positive number)';
    }
    
    if (empty($max_marks) || !is_numeric($max_marks) || $max_marks <= 0) {
        $errors[] = 'Valid maximum marks are required (must be greater than 0)';
    }
    
    if (!empty($marks) && !empty($max_marks) && is_numeric($marks) && is_numeric($max_marks)) {
        if (floatval($marks) > floatval($max_marks)) {
            $errors[] = 'Marks cannot exceed maximum marks';
        }
    }
    
    if (empty($exam_type) || strlen($exam_type) > 50) {
        $errors[] = 'Exam type is required and must be less than 50 characters';
    }
    
    if (empty($exam_date)) {
        $errors[] = 'Exam date is required';
    } else {
        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $exam_date);
        if (!$date || $date->format('Y-m-d') !== $exam_date) {
            $errors[] = 'Invalid date format (use YYYY-MM-DD)';
        }
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Check if grade exists
    $check_sql = "SELECT id FROM grades WHERE id = :grade_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['grade_id' => $grade_id]);
    
    if (!$check_stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Grade not found']);
        return;
    }
    
    // Update grade
    $sql = "UPDATE grades 
            SET subject_name = :subject_name, 
                marks = :marks, 
                max_marks = :max_marks, 
                exam_type = :exam_type, 
                exam_date = :exam_date 
            WHERE id = :grade_id";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'grade_id' => $grade_id,
        'subject_name' => $subject_name,
        'marks' => $marks,
        'max_marks' => $max_marks,
        'exam_type' => $exam_type,
        'exam_date' => $exam_date
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Grade updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update grade']);
    }
}

/**
 * Delete a grade entry
 */
function handleDeleteGrade($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Get grade ID
    $grade_id = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $grade_id = trim($_POST['grade_id'] ?? '');
    } else {
        parse_str(file_get_contents("php://input"), $delete_data);
        $grade_id = trim($delete_data['grade_id'] ?? '');
    }
    
    // Validation
    if (empty($grade_id) || !is_numeric($grade_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid grade ID is required']);
        return;
    }
    
    // Check if grade exists
    $check_sql = "SELECT id FROM grades WHERE id = :grade_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['grade_id' => $grade_id]);
    
    if (!$check_stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Grade not found']);
        return;
    }
    
    // Delete grade
    $sql = "DELETE FROM grades WHERE id = :grade_id";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute(['grade_id' => $grade_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Grade deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete grade']);
    }
}

/**
 * Fetch grades by student ID
 */
function handleFetchGrades($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $student_id = trim($_GET['student_id'] ?? '');
    
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
    
    // Fetch grades
    $sql = "SELECT id, subject_name, marks, max_marks, exam_type, exam_date, 
                   ROUND((marks / max_marks) * 100, 2) as percentage,
                   created_at, updated_at
            FROM grades 
            WHERE student_id = :student_id 
            ORDER BY exam_date DESC, created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['student_id' => $student_id]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals and averages
    $total_marks = 0;
    $total_max_marks = 0;
    
    foreach ($grades as $grade) {
        $total_marks += $grade['marks'];
        $total_max_marks += $grade['max_marks'];
    }
    
    $average_percentage = $total_max_marks > 0 ? round(($total_marks / $total_max_marks) * 100, 2) : 0;
    
    echo json_encode([
        'success' => true,
        'student' => [
            'id' => $student['id'],
            'name' => $student['name']
        ],
        'grades' => $grades,
        'summary' => [
            'total_marks' => $total_marks,
            'total_max_marks' => $total_max_marks,
            'average_percentage' => $average_percentage,
            'grade_count' => count($grades)
        ]
    ]);
}

/**
 * Fetch all grades with student information
 */
function handleFetchAllGrades($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Fetch all grades with student information
    $sql = "SELECT g.id, g.student_id, s.name as student_name, g.subject_name, 
                   g.marks, g.max_marks, g.exam_type, g.exam_date,
                   ROUND((g.marks / g.max_marks) * 100, 2) as percentage,
                   g.created_at, g.updated_at
            FROM grades g
            INNER JOIN students s ON g.student_id = s.id
            ORDER BY g.exam_date DESC, g.created_at DESC";
    
    $stmt = $pdo->query($sql);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'grades' => $grades,
        'count' => count($grades)
    ]);
}

/**
 * Calculate total marks for a student
 * 
 * @param PDO $pdo Database connection
 * @param int $student_id Student ID
 * @return array Array containing total_marks and total_max_marks
 */
function calculateTotalMarks($pdo, $student_id) {
    $sql = "SELECT COALESCE(SUM(marks), 0) as total_marks, COALESCE(SUM(max_marks), 0) as total_max_marks FROM grades WHERE student_id = :student_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['student_id' => $student_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'total_marks' => floatval($result['total_marks']),
        'total_max_marks' => floatval($result['total_max_marks'])
    ];
}

/**
 * Calculate average percentage for a student
 * 
 * @param PDO $pdo Database connection
 * @param int $student_id Student ID
 * @return float Average percentage (0-100)
 */
function calculateAveragePercentage($pdo, $student_id) {
    $totals = calculateTotalMarks($pdo, $student_id);
    
    if ($totals['total_max_marks'] > 0) {
        return round(($totals['total_marks'] / $totals['total_max_marks']) * 100, 2);
    }
    
    return 0.0;
}

/**
 * Fetch all grades for a student with calculated values
 * 
 * @param PDO $pdo Database connection
 * @param int $student_id Student ID
 * @return array Array containing grades, totals, and calculated values
 */
function fetchGradesWithCalculations($pdo, $student_id) {
    $sql = "SELECT id, student_id, subject_name, marks, max_marks, exam_type, exam_date, ROUND((marks / max_marks) * 100, 2) as percentage, created_at, updated_at FROM grades WHERE student_id = :student_id ORDER BY exam_date DESC, created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['student_id' => $student_id]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totals = calculateTotalMarks($pdo, $student_id);
    $average_percentage = calculateAveragePercentage($pdo, $student_id);
    
    return [
        'grades' => $grades,
        'total_marks' => $totals['total_marks'],
        'total_max_marks' => $totals['total_max_marks'],
        'average_percentage' => $average_percentage,
        'grade_count' => count($grades)
    ];
}
