-- Migration Script: Add Grades, Fees, and Attendance Tables
-- Requirements: 5.1, 5.2, 5.3, 5.4, 5.5

USE student_management;

-- Create grades table
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    marks DECIMAL(5,2) NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    exam_type VARCHAR(50) NOT NULL,
    exam_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_exam_date (exam_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create fees table
CREATE TABLE IF NOT EXISTS fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    fee_type VARCHAR(100) NOT NULL,
    assigned_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    due_date DATE NOT NULL,
    payment_date DATE NULL,
    status ENUM('pending', 'partial', 'paid', 'overdue') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_date (student_id, attendance_date),
    INDEX idx_student_id (student_id),
    INDEX idx_attendance_date (attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data for testing

-- Insert sample grades for existing students
INSERT INTO grades (student_id, subject_name, marks, max_marks, exam_type, exam_date) VALUES
(1, 'Database Systems', 85.00, 100.00, 'Midterm', '2024-10-15'),
(1, 'Web Development', 92.00, 100.00, 'Midterm', '2024-10-16'),
(1, 'Data Structures', 78.00, 100.00, 'Midterm', '2024-10-17'),
(1, 'Database Systems', 88.00, 100.00, 'Final', '2024-11-20'),
(1, 'Web Development', 95.00, 100.00, 'Final', '2024-11-21'),
(2, 'Marketing Fundamentals', 90.00, 100.00, 'Midterm', '2024-10-15'),
(2, 'Business Ethics', 87.00, 100.00, 'Midterm', '2024-10-16'),
(2, 'Financial Accounting', 82.00, 100.00, 'Midterm', '2024-10-17'),
(2, 'Marketing Fundamentals', 93.00, 100.00, 'Final', '2024-11-20'),
(2, 'Business Ethics', 89.00, 100.00, 'Final', '2024-11-21');

-- Insert sample fees for existing students
INSERT INTO fees (student_id, fee_type, assigned_amount, paid_amount, due_date, payment_date, status) VALUES
(1, 'Tuition Fee - Fall 2024', 5000.00, 5000.00, '2024-09-01', '2024-08-28', 'paid'),
(1, 'Lab Fee', 500.00, 300.00, '2024-09-15', '2024-09-10', 'partial'),
(1, 'Library Fee', 200.00, 0.00, '2024-10-01', NULL, 'overdue'),
(2, 'Tuition Fee - Fall 2024', 4500.00, 4500.00, '2024-09-01', '2024-08-30', 'paid'),
(2, 'Activity Fee', 300.00, 0.00, '2024-09-15', NULL, 'overdue'),
(2, 'Technology Fee', 400.00, 0.00, '2024-12-15', NULL, 'pending');

-- Insert sample attendance for existing students
INSERT INTO attendance (student_id, attendance_date, status, notes) VALUES
(1, '2024-11-01', 'present', NULL),
(1, '2024-11-04', 'present', NULL),
(1, '2024-11-05', 'absent', 'Medical appointment'),
(1, '2024-11-06', 'present', NULL),
(1, '2024-11-07', 'present', NULL),
(1, '2024-11-08', 'late', 'Traffic delay'),
(1, '2024-11-11', 'present', NULL),
(1, '2024-11-12', 'present', NULL),
(1, '2024-11-13', 'present', NULL),
(1, '2024-11-14', 'present', NULL),
(2, '2024-11-01', 'present', NULL),
(2, '2024-11-04', 'present', NULL),
(2, '2024-11-05', 'present', NULL),
(2, '2024-11-06', 'excused', 'Family emergency'),
(2, '2024-11-07', 'present', NULL),
(2, '2024-11-08', 'present', NULL),
(2, '2024-11-11', 'absent', NULL),
(2, '2024-11-12', 'present', NULL),
(2, '2024-11-13', 'present', NULL),
(2, '2024-11-14', 'present', NULL);

-- Verification queries (commented out - uncomment to run after migration)
-- SELECT 'Grades table created' AS status, COUNT(*) AS record_count FROM grades;
-- SELECT 'Fees table created' AS status, COUNT(*) AS record_count FROM fees;
-- SELECT 'Attendance table created' AS status, COUNT(*) AS record_count FROM attendance;
-- 
-- -- Verify foreign key constraints
-- SELECT 
--     TABLE_NAME,
--     CONSTRAINT_NAME,
--     REFERENCED_TABLE_NAME
-- FROM information_schema.KEY_COLUMN_USAGE
-- WHERE TABLE_SCHEMA = 'student_management'
--     AND REFERENCED_TABLE_NAME IS NOT NULL
--     AND TABLE_NAME IN ('grades', 'fees', 'attendance');
--
-- -- Verify indexes
-- SELECT 
--     TABLE_NAME,
--     INDEX_NAME,
--     COLUMN_NAME
-- FROM information_schema.STATISTICS
-- WHERE TABLE_SCHEMA = 'student_management'
--     AND TABLE_NAME IN ('grades', 'fees', 'attendance')
-- ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
