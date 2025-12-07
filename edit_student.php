<?php
// Check authentication
require_once 'auth_check.php';

// Include database configuration
require_once 'config/database.php';

// Get student ID from URL
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id === 0) {
    $_SESSION['error_message'] = "Invalid student ID";
    header('Location: dashboard.php');
    exit;
}

// Fetch student data
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone is required";
    }
    
    if (empty($course)) {
        $errors[] = "Course is required";
    }
    
    if (empty($gender)) {
        $errors[] = "Gender is required";
    }
    
    if (empty($errors)) {
        try {
            // Check if email already exists for another student
            $check_sql = "SELECT id FROM students WHERE email = :email AND id != :id";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute(['email' => $email, 'id' => $student_id]);
            
            if ($check_stmt->fetch()) {
                $errors[] = "Email already exists for another student";
            } else {
                // Update student
                $update_sql = "UPDATE students SET name = :name, email = :email, phone = :phone, course = :course, gender = :gender WHERE id = :id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'course' => $course,
                    'gender' => $gender,
                    'id' => $student_id
                ]);
                
                $_SESSION['success_message'] = "Student updated successfully!";
                header('Location: dashboard.php');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Error updating student: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Student Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Edit Student</h1>
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
        <div class="form-container">
            <h2>Edit Student Details</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="notification error-notification">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="registration-form" id="editForm">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="course">Course *</label>
                    <select id="course" name="course" required>
                        <option value="">Select a course</option>
                        <option value="Computer Science" <?php echo $student['course'] === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                        <option value="Business Administration" <?php echo $student['course'] === 'Business Administration' ? 'selected' : ''; ?>>Business Administration</option>
                        <option value="Electrical Engineering" <?php echo $student['course'] === 'Electrical Engineering' ? 'selected' : ''; ?>>Electrical Engineering</option>
                        <option value="Mechanical Engineering" <?php echo $student['course'] === 'Mechanical Engineering' ? 'selected' : ''; ?>>Mechanical Engineering</option>
                        <option value="Civil Engineering" <?php echo $student['course'] === 'Civil Engineering' ? 'selected' : ''; ?>>Civil Engineering</option>
                        <option value="Medicine" <?php echo $student['course'] === 'Medicine' ? 'selected' : ''; ?>>Medicine</option>
                        <option value="Law" <?php echo $student['course'] === 'Law' ? 'selected' : ''; ?>>Law</option>
                        <option value="Arts and Humanities" <?php echo $student['course'] === 'Arts and Humanities' ? 'selected' : ''; ?>>Arts and Humanities</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Gender *</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="gender" value="Male" <?php echo $student['gender'] === 'Male' ? 'checked' : ''; ?> required>
                            Male
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="Female" <?php echo $student['gender'] === 'Female' ? 'checked' : ''; ?> required>
                            Female
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="Other" <?php echo $student['gender'] === 'Other' ? 'checked' : ''; ?> required>
                            Other
                        </label>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-success btn-full">Update Student</button>
                    <a href="dashboard.php" class="btn btn-secondary btn-full">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Student Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/validation.js"></script>
</body>
</html>
