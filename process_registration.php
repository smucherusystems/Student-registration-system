<?php
// Include database configuration
require_once 'config/database.php';

// Set content type to JSON for AJAX responses
header('Content-Type: application/json');

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data and sanitize inputs
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$course = trim($_POST['course'] ?? '');
$gender = trim($_POST['gender'] ?? '');

// Basic server-side validation
$errors = [];

// Validate name
if (empty($name)) {
    $errors[] = 'Name is required';
} elseif (strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters long';
}

// Validate email
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}

// Validate phone
if (empty($phone)) {
    $errors[] = 'Phone number is required';
} elseif (strlen(preg_replace('/\D/', '', $phone)) < 10) {
    $errors[] = 'Phone number must be at least 10 digits';
}

// Validate course
if (empty($course)) {
    $errors[] = 'Course selection is required';
}

// Validate gender
if (empty($gender)) {
    $errors[] = 'Gender selection is required';
} elseif (!in_array($gender, ['Male', 'Female', 'Other'])) {
    $errors[] = 'Invalid gender selection';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Check if email already exists
    $check_email_sql = "SELECT id FROM students WHERE email = :email";
    $check_email_stmt = $pdo->prepare($check_email_sql);
    $check_email_stmt->execute(['email' => $email]);
    
    if ($check_email_stmt->rowCount() > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Prepare SQL insert statement
    $sql = "INSERT INTO students (name, email, phone, course, gender) 
            VALUES (:name, :email, :phone, :course, :gender)";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':course', $course);
    $stmt->bindParam(':gender', $gender);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Success response
        echo json_encode([
            'success' => true, 
            'message' => 'Student registered successfully!',
            'student_id' => $pdo->lastInsertId()
        ]);
    } else {
        // Database error
        throw new Exception('Failed to insert student record');
    }
    
} catch (PDOException $e) {
    // Database connection or query error
    error_log("Database error: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // General error
    error_log("Error: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>