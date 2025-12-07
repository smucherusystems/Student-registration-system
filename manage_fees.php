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
        case 'assign':
            handleAssignFee($pdo);
            break;
        
        case 'record_payment':
            handleRecordPayment($pdo);
            break;
        
        case 'update_status':
            handleUpdateFeeStatus($pdo);
            break;
        
        case 'fetch':
            handleFetchFees($pdo);
            break;
        
        case 'fetch_all':
            handleFetchAllFees($pdo);
            break;
        
        case 'calculate_balance':
            handleCalculateBalance($pdo);
            break;
        
        case 'auto_update_status':
            handleAutoUpdateStatus($pdo);
            break;
        
        case 'fetch_summary':
            handleFetchSummary($pdo);
            break;
        
        case 'fetch_payment_history':
            handleFetchPaymentHistory($pdo);
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Fee management error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}

/**
 * Assign a new fee to a student
 */
function handleAssignFee($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Get and validate input
    $student_id = trim($_POST['student_id'] ?? '');
    $fee_type = trim($_POST['fee_type'] ?? '');
    $assigned_amount = trim($_POST['assigned_amount'] ?? '');
    $due_date = trim($_POST['due_date'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($student_id) || !is_numeric($student_id)) {
        $errors[] = 'Valid student ID is required';
    }
    
    if (empty($fee_type) || strlen($fee_type) > 100) {
        $errors[] = 'Fee type is required and must be less than 100 characters';
    }
    
    if (empty($assigned_amount) || !is_numeric($assigned_amount) || $assigned_amount <= 0) {
        $errors[] = 'Valid assigned amount is required (must be greater than 0)';
    }
    
    if (empty($due_date)) {
        $errors[] = 'Due date is required';
    } else {
        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $due_date);
        if (!$date || $date->format('Y-m-d') !== $due_date) {
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
    
    // Determine initial status based on due date
    $status = (strtotime($due_date) < time()) ? 'overdue' : 'pending';
    
    // Insert fee
    $sql = "INSERT INTO fees (student_id, fee_type, assigned_amount, paid_amount, due_date, status) 
            VALUES (:student_id, :fee_type, :assigned_amount, 0.00, :due_date, :status)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'student_id' => $student_id,
        'fee_type' => $fee_type,
        'assigned_amount' => $assigned_amount,
        'due_date' => $due_date,
        'status' => $status
    ]);
    
    if ($result) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Fee assigned successfully',
            'fee_id' => $pdo->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to assign fee']);
    }
}

/**
 * Record a payment for a fee
 */
function handleRecordPayment($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Get and validate input
    $fee_id = trim($_POST['fee_id'] ?? '');
    $payment_amount = trim($_POST['payment_amount'] ?? '');
    $payment_date = trim($_POST['payment_date'] ?? date('Y-m-d'));
    
    // Validation
    $errors = [];
    
    if (empty($fee_id) || !is_numeric($fee_id)) {
        $errors[] = 'Valid fee ID is required';
    }
    
    if (empty($payment_amount) || !is_numeric($payment_amount) || $payment_amount <= 0) {
        $errors[] = 'Valid payment amount is required (must be greater than 0)';
    }
    
    if (!empty($payment_date)) {
        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $payment_date);
        if (!$date || $date->format('Y-m-d') !== $payment_date) {
            $errors[] = 'Invalid date format (use YYYY-MM-DD)';
        }
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Check if fee exists and get current details
    $check_sql = "SELECT id, assigned_amount, paid_amount FROM fees WHERE id = :fee_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['fee_id' => $fee_id]);
    $fee = $check_stmt->fetch();
    
    if (!$fee) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Fee not found']);
        return;
    }
    
    // Calculate new paid amount
    $new_paid_amount = floatval($fee['paid_amount']) + floatval($payment_amount);
    
    // Validate to prevent overpayment
    if ($new_paid_amount > floatval($fee['assigned_amount'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Payment amount exceeds outstanding balance',
            'assigned_amount' => floatval($fee['assigned_amount']),
            'paid_amount' => floatval($fee['paid_amount']),
            'outstanding_balance' => floatval($fee['assigned_amount']) - floatval($fee['paid_amount']),
            'attempted_payment' => floatval($payment_amount)
        ]);
        return;
    }
    
    // Determine new status
    $balance = floatval($fee['assigned_amount']) - $new_paid_amount;
    if ($balance <= 0) {
        $new_status = 'paid';
    } else if ($new_paid_amount > 0) {
        $new_status = 'partial';
    } else {
        $new_status = 'pending';
    }
    
    // Update fee with payment
    $sql = "UPDATE fees 
            SET paid_amount = :paid_amount, 
                payment_date = :payment_date,
                status = :status
            WHERE id = :fee_id";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'fee_id' => $fee_id,
        'paid_amount' => $new_paid_amount,
        'payment_date' => $payment_date,
        'status' => $new_status
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'new_paid_amount' => $new_paid_amount,
            'outstanding_balance' => $balance,
            'status' => $new_status
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to record payment']);
    }
}

/**
 * Update fee status
 */
function handleUpdateFeeStatus($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Get and validate input
    $fee_id = trim($_POST['fee_id'] ?? '');
    $status = trim($_POST['status'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($fee_id) || !is_numeric($fee_id)) {
        $errors[] = 'Valid fee ID is required';
    }
    
    $valid_statuses = ['pending', 'partial', 'paid', 'overdue'];
    if (empty($status) || !in_array($status, $valid_statuses)) {
        $errors[] = 'Valid status is required (pending, partial, paid, or overdue)';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Check if fee exists
    $check_sql = "SELECT id FROM fees WHERE id = :fee_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['fee_id' => $fee_id]);
    
    if (!$check_stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Fee not found']);
        return;
    }
    
    // Update fee status
    $sql = "UPDATE fees SET status = :status WHERE id = :fee_id";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'fee_id' => $fee_id,
        'status' => $status
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Fee status updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update fee status']);
    }
}

/**
 * Fetch fees by student ID with optional filtering
 */
function handleFetchFees($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $student_id = trim($_GET['student_id'] ?? '');
    $status_filter = trim($_GET['status'] ?? '');
    $fee_type_filter = trim($_GET['fee_type'] ?? '');
    
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
    
    // Build query with filters
    $sql = "SELECT id, fee_type, assigned_amount, paid_amount, 
                   (assigned_amount - paid_amount) as outstanding_balance,
                   due_date, payment_date, status, created_at, updated_at
            FROM fees 
            WHERE student_id = :student_id";
    
    $params = ['student_id' => $student_id];
    
    // Add status filter if provided
    if (!empty($status_filter)) {
        $sql .= " AND status = :status";
        $params['status'] = $status_filter;
    }
    
    // Add fee type filter if provided
    if (!empty($fee_type_filter)) {
        $sql .= " AND fee_type LIKE :fee_type";
        $params['fee_type'] = '%' . $fee_type_filter . '%';
    }
    
    $sql .= " ORDER BY due_date DESC, created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $total_assigned = 0;
    $total_paid = 0;
    $total_outstanding = 0;
    
    foreach ($fees as $fee) {
        $total_assigned += $fee['assigned_amount'];
        $total_paid += $fee['paid_amount'];
        $total_outstanding += $fee['outstanding_balance'];
    }
    
    echo json_encode([
        'success' => true,
        'student' => [
            'id' => $student['id'],
            'name' => $student['name']
        ],
        'fees' => $fees,
        'summary' => [
            'total_assigned' => round($total_assigned, 2),
            'total_paid' => round($total_paid, 2),
            'total_outstanding' => round($total_outstanding, 2),
            'fee_count' => count($fees)
        ]
    ]);
}

/**
 * Fetch all fees with student information
 */
function handleFetchAllFees($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    // Optional status filter
    $status_filter = trim($_GET['status'] ?? '');
    
    // Build query
    $sql = "SELECT f.id, f.student_id, s.name as student_name, f.fee_type, 
                   f.assigned_amount, f.paid_amount,
                   (f.assigned_amount - f.paid_amount) as outstanding_balance,
                   f.due_date, f.payment_date, f.status,
                   f.created_at, f.updated_at
            FROM fees f
            INNER JOIN students s ON f.student_id = s.id";
    
    $params = [];
    
    // Add status filter if provided
    if (!empty($status_filter)) {
        $sql .= " WHERE f.status = :status";
        $params['status'] = $status_filter;
    }
    
    $sql .= " ORDER BY f.due_date DESC, f.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $total_assigned = 0;
    $total_paid = 0;
    $total_outstanding = 0;
    
    foreach ($fees as $fee) {
        $total_assigned += $fee['assigned_amount'];
        $total_paid += $fee['paid_amount'];
        $total_outstanding += $fee['outstanding_balance'];
    }
    
    echo json_encode([
        'success' => true,
        'fees' => $fees,
        'summary' => [
            'total_assigned' => round($total_assigned, 2),
            'total_paid' => round($total_paid, 2),
            'total_outstanding' => round($total_outstanding, 2),
            'fee_count' => count($fees)
        ]
    ]);
}

/**
 * Handle calculate balance API request
 */
function handleCalculateBalance($pdo) {
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
    
    $outstanding_balance = calculateOutstandingBalance($pdo, $student_id);
    
    echo json_encode([
        'success' => true,
        'student_id' => intval($student_id),
        'student_name' => $student['name'],
        'outstanding_balance' => $outstanding_balance
    ]);
}

/**
 * Handle auto update status API request
 */
function handleAutoUpdateStatus($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $fee_id = trim($_POST['fee_id'] ?? '');
    
    // If fee_id is provided, update only that fee, otherwise update all
    if (!empty($fee_id)) {
        if (!is_numeric($fee_id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Valid fee ID is required']);
            return;
        }
        
        // Check if fee exists
        $check_sql = "SELECT id FROM fees WHERE id = :fee_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute(['fee_id' => $fee_id]);
        
        if (!$check_stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Fee not found']);
            return;
        }
        
        $updated_count = autoUpdateFeeStatus($pdo, $fee_id);
    } else {
        $updated_count = autoUpdateFeeStatus($pdo);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Fee status updated successfully',
        'updated_count' => $updated_count
    ]);
}

/**
 * Handle fetch summary API request
 */
function handleFetchSummary($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $summary = fetchFeeSummary($pdo);
    
    echo json_encode([
        'success' => true,
        'summary' => $summary
    ]);
}

/**
 * Calculate outstanding balance for a student
 * 
 * @param PDO $pdo Database connection
 * @param int $student_id Student ID
 * @return float Outstanding balance
 */
function calculateOutstandingBalance($pdo, $student_id) {
    $sql = "SELECT COALESCE(SUM(assigned_amount - paid_amount), 0) as outstanding_balance 
            FROM fees 
            WHERE student_id = :student_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['student_id' => $student_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return round(floatval($result['outstanding_balance']), 2);
}

/**
 * Automatically update fee status based on payments and due dates
 * 
 * @param PDO $pdo Database connection
 * @param int $fee_id Fee ID (optional, if null updates all fees)
 * @return int Number of fees updated
 */
function autoUpdateFeeStatus($pdo, $fee_id = null) {
    $sql = "UPDATE fees 
            SET status = CASE
                WHEN paid_amount >= assigned_amount THEN 'paid'
                WHEN paid_amount > 0 AND paid_amount < assigned_amount THEN 'partial'
                WHEN paid_amount = 0 AND due_date < CURDATE() THEN 'overdue'
                ELSE 'pending'
            END";
    
    $params = [];
    
    if ($fee_id !== null) {
        $sql .= " WHERE id = :fee_id";
        $params['fee_id'] = $fee_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->rowCount();
}

/**
 * Fetch fee summary for dashboard
 * 
 * @param PDO $pdo Database connection
 * @return array Fee summary statistics
 */
function fetchFeeSummary($pdo) {
    $sql = "SELECT 
                COUNT(*) as total_fees,
                COALESCE(SUM(assigned_amount), 0) as total_assigned,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(assigned_amount - paid_amount), 0) as total_outstanding,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'partial' THEN 1 END) as partial_count,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_count
            FROM fees";
    
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'total_fees' => intval($result['total_fees']),
        'total_assigned' => round(floatval($result['total_assigned']), 2),
        'total_paid' => round(floatval($result['total_paid']), 2),
        'total_outstanding' => round(floatval($result['total_outstanding']), 2),
        'pending_count' => intval($result['pending_count']),
        'partial_count' => intval($result['partial_count']),
        'paid_count' => intval($result['paid_count']),
        'overdue_count' => intval($result['overdue_count'])
    ];
}

/**
 * Handle fetch payment history API request
 * Returns a timeline of fee assignments, payments, and status changes
 */
function handleFetchPaymentHistory($pdo) {
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
    
    // Fetch all fees for the student
    $sql = "SELECT id, fee_type, assigned_amount, paid_amount, 
                   due_date, payment_date, status, created_at, updated_at
            FROM fees 
            WHERE student_id = :student_id
            ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['student_id' => $student_id]);
    $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build timeline events
    $timeline = [];
    
    foreach ($fees as $fee) {
        // Add fee assignment event
        $timeline[] = [
            'type' => 'assignment',
            'date' => $fee['created_at'],
            'fee_type' => $fee['fee_type'],
            'amount' => floatval($fee['assigned_amount']),
            'due_date' => $fee['due_date'],
            'description' => 'Fee assigned'
        ];
        
        // Add payment event if payment was made
        if ($fee['payment_date'] && floatval($fee['paid_amount']) > 0) {
            $timeline[] = [
                'type' => 'payment',
                'date' => $fee['payment_date'],
                'fee_type' => $fee['fee_type'],
                'amount' => floatval($fee['paid_amount']),
                'status' => $fee['status'],
                'description' => 'Payment recorded'
            ];
        }
        
        // Add status change event if status was updated after creation
        if ($fee['updated_at'] !== $fee['created_at']) {
            $timeline[] = [
                'type' => 'status_change',
                'date' => $fee['updated_at'],
                'fee_type' => $fee['fee_type'],
                'status' => $fee['status'],
                'description' => 'Status updated to ' . $fee['status']
            ];
        }
    }
    
    // Sort timeline by date (most recent first)
    usort($timeline, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    echo json_encode([
        'success' => true,
        'student' => [
            'id' => $student['id'],
            'name' => $student['name']
        ],
        'timeline' => $timeline,
        'count' => count($timeline)
    ]);
}
