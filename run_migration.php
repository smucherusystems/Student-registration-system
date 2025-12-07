<?php
/**
 * Migration Runner Script
 * Executes the database migration and verifies schema
 */

require_once 'config/database.php';

echo "=== Starting Database Migration ===\n\n";

// Read the migration SQL file
$migrationFile = 'migration_add_grades_fees_attendance.sql';
if (!file_exists($migrationFile)) {
    die("ERROR: Migration file not found: $migrationFile\n");
}

$sql = file_get_contents($migrationFile);
if ($sql === false) {
    die("ERROR: Could not read migration file\n");
}

// Split SQL statements (simple split by semicolon)
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        // Filter out empty statements and comments
        return !empty($stmt) && 
               !preg_match('/^\s*--/', $stmt) && 
               !preg_match('/^\s*\/\*/', $stmt);
    }
);

echo "Found " . count($statements) . " SQL statements to execute\n\n";

// Execute each statement
$successCount = 0;
$errorCount = 0;

foreach ($statements as $index => $statement) {
    try {
        $pdo->exec($statement);
        $successCount++;
        
        // Show progress for table creation
        if (stripos($statement, 'CREATE TABLE') !== false) {
            preg_match('/CREATE TABLE.*?(\w+)\s*\(/i', $statement, $matches);
            if (isset($matches[1])) {
                echo "✓ Created table: {$matches[1]}\n";
            }
        } elseif (stripos($statement, 'INSERT INTO') !== false) {
            preg_match('/INSERT INTO\s+(\w+)/i', $statement, $matches);
            if (isset($matches[1])) {
                echo "✓ Inserted sample data into: {$matches[1]}\n";
            }
        }
    } catch (PDOException $e) {
        $errorCount++;
        echo "✗ Error executing statement " . ($index + 1) . ": " . $e->getMessage() . "\n";
    }
}

echo "\n=== Migration Summary ===\n";
echo "Successful: $successCount\n";
echo "Errors: $errorCount\n\n";

if ($errorCount > 0) {
    echo "Migration completed with errors. Please review the errors above.\n\n";
} else {
    echo "Migration completed successfully!\n\n";
}

// Verify schema
echo "=== Verifying Schema ===\n\n";

try {
    // Check tables exist
    $tables = ['grades', 'fees', 'attendance'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
            
            // Count records
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $countStmt->fetch()['count'];
            echo "  - Records: $count\n";
        } else {
            echo "✗ Table '$table' NOT found\n";
        }
    }
    
    echo "\n=== Verifying Foreign Keys ===\n\n";
    
    // Verify foreign key constraints
    $fkQuery = "
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            DELETE_RULE
        FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = '" . DB_NAME . "'
            AND TABLE_NAME IN ('grades', 'fees', 'attendance')
    ";
    
    $stmt = $pdo->query($fkQuery);
    $foreignKeys = $stmt->fetchAll();
    
    if (count($foreignKeys) > 0) {
        foreach ($foreignKeys as $fk) {
            echo "✓ Foreign key: {$fk['TABLE_NAME']}.{$fk['CONSTRAINT_NAME']}\n";
            echo "  - References: {$fk['REFERENCED_TABLE_NAME']}\n";
            echo "  - On Delete: {$fk['DELETE_RULE']}\n";
        }
    } else {
        echo "✗ No foreign keys found\n";
    }
    
    echo "\n=== Verifying Indexes ===\n\n";
    
    // Verify indexes
    $indexQuery = "
        SELECT 
            TABLE_NAME,
            INDEX_NAME,
            GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as COLUMNS,
            NON_UNIQUE
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = '" . DB_NAME . "'
            AND TABLE_NAME IN ('grades', 'fees', 'attendance')
        GROUP BY TABLE_NAME, INDEX_NAME, NON_UNIQUE
        ORDER BY TABLE_NAME, INDEX_NAME
    ";
    
    $stmt = $pdo->query($indexQuery);
    $indexes = $stmt->fetchAll();
    
    if (count($indexes) > 0) {
        foreach ($indexes as $idx) {
            $type = $idx['NON_UNIQUE'] == 0 ? 'UNIQUE' : 'INDEX';
            echo "✓ {$type}: {$idx['TABLE_NAME']}.{$idx['INDEX_NAME']}\n";
            echo "  - Columns: {$idx['COLUMNS']}\n";
        }
    } else {
        echo "✗ No indexes found\n";
    }
    
    echo "\n=== Testing Cascade Delete ===\n\n";
    
    // Test cascade delete with a test student
    echo "Creating test student for cascade delete test...\n";
    
    $pdo->beginTransaction();
    
    try {
        // Insert test student
        $stmt = $pdo->prepare("
            INSERT INTO students (name, email, phone, course, gender) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute(['Test Student CASCADE', 'cascade@test.com', '1234567890', 'Test Course', 'Male']);
        $testStudentId = $pdo->lastInsertId();
        echo "✓ Created test student (ID: $testStudentId)\n";
        
        // Insert related records
        $pdo->exec("INSERT INTO grades (student_id, subject_name, marks, max_marks, exam_type, exam_date) 
                    VALUES ($testStudentId, 'Test Subject', 85.00, 100.00, 'Test', '2024-12-01')");
        echo "✓ Added grade record\n";
        
        $pdo->exec("INSERT INTO fees (student_id, fee_type, assigned_amount, due_date, status) 
                    VALUES ($testStudentId, 'Test Fee', 1000.00, '2024-12-31', 'pending')");
        echo "✓ Added fee record\n";
        
        $pdo->exec("INSERT INTO attendance (student_id, attendance_date, status) 
                    VALUES ($testStudentId, '2024-12-01', 'present')");
        echo "✓ Added attendance record\n";
        
        // Count related records before delete
        $gradeCount = $pdo->query("SELECT COUNT(*) as count FROM grades WHERE student_id = $testStudentId")->fetch()['count'];
        $feeCount = $pdo->query("SELECT COUNT(*) as count FROM fees WHERE student_id = $testStudentId")->fetch()['count'];
        $attendanceCount = $pdo->query("SELECT COUNT(*) as count FROM attendance WHERE student_id = $testStudentId")->fetch()['count'];
        
        echo "\nBefore delete:\n";
        echo "  - Grades: $gradeCount\n";
        echo "  - Fees: $feeCount\n";
        echo "  - Attendance: $attendanceCount\n";
        
        // Delete the student
        $pdo->exec("DELETE FROM students WHERE id = $testStudentId");
        echo "\n✓ Deleted test student\n";
        
        // Count related records after delete
        $gradeCountAfter = $pdo->query("SELECT COUNT(*) as count FROM grades WHERE student_id = $testStudentId")->fetch()['count'];
        $feeCountAfter = $pdo->query("SELECT COUNT(*) as count FROM fees WHERE student_id = $testStudentId")->fetch()['count'];
        $attendanceCountAfter = $pdo->query("SELECT COUNT(*) as count FROM attendance WHERE student_id = $testStudentId")->fetch()['count'];
        
        echo "\nAfter delete:\n";
        echo "  - Grades: $gradeCountAfter\n";
        echo "  - Fees: $feeCountAfter\n";
        echo "  - Attendance: $attendanceCountAfter\n";
        
        if ($gradeCountAfter == 0 && $feeCountAfter == 0 && $attendanceCountAfter == 0) {
            echo "\n✓ CASCADE DELETE working correctly! All related records were deleted.\n";
        } else {
            echo "\n✗ CASCADE DELETE failed! Some related records remain.\n";
        }
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "✗ Error during cascade delete test: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Verification Complete ===\n";
    
} catch (PDOException $e) {
    echo "✗ Error during verification: " . $e->getMessage() . "\n";
}
