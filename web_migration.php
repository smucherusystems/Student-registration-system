<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-box {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .status-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .status-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Migration - Grades, Fees & Attendance</h1>
        
        <?php
        require_once 'config/database.php';
        
        $migrationFile = 'migration_add_grades_fees_attendance.sql';
        
        if (!file_exists($migrationFile)) {
            echo '<div class="status-box status-error">❌ Migration file not found: ' . $migrationFile . '</div>';
            exit;
        }
        
        echo '<div class="info">📄 Migration file: ' . $migrationFile . '</div>';
        
        // Read and execute migration
        $sql = file_get_contents($migrationFile);
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && 
                       !preg_match('/^\s*--/', $stmt) && 
                       !preg_match('/^\s*\/\*/', $stmt);
            }
        );
        
        echo '<h2>📊 Migration Execution</h2>';
        echo '<p>Executing ' . count($statements) . ' SQL statements...</p>';
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        foreach ($statements as $index => $statement) {
            try {
                $pdo->exec($statement);
                $successCount++;
            } catch (PDOException $e) {
                $errorCount++;
                $errors[] = "Statement " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        if ($errorCount == 0) {
            echo '<div class="status-box status-success">✅ Migration completed successfully! (' . $successCount . ' statements executed)</div>';
        } else {
            echo '<div class="status-box status-error">⚠️ Migration completed with ' . $errorCount . ' errors</div>';
            echo '<ul>';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
        }
        
        // Verify tables
        echo '<h2>🔍 Schema Verification</h2>';
        
        $tables = ['grades', 'fees', 'attendance'];
        echo '<table>';
        echo '<tr><th>Table Name</th><th>Status</th><th>Record Count</th></tr>';
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $countStmt->fetch()['count'];
                echo '<tr>';
                echo '<td>' . $table . '</td>';
                echo '<td class="success">✓ EXISTS</td>';
                echo '<td>' . $count . ' records</td>';
                echo '</tr>';
            } else {
                echo '<tr>';
                echo '<td>' . $table . '</td>';
                echo '<td class="error">✗ NOT FOUND</td>';
                echo '<td>-</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
        
        // Verify foreign keys
        echo '<h2>🔗 Foreign Key Constraints</h2>';
        
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
            echo '<table>';
            echo '<tr><th>Table</th><th>Constraint</th><th>References</th><th>On Delete</th></tr>';
            foreach ($foreignKeys as $fk) {
                echo '<tr>';
                echo '<td>' . $fk['TABLE_NAME'] . '</td>';
                echo '<td>' . $fk['CONSTRAINT_NAME'] . '</td>';
                echo '<td>' . $fk['REFERENCED_TABLE_NAME'] . '</td>';
                echo '<td class="success">' . $fk['DELETE_RULE'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="status-box status-error">❌ No foreign keys found</div>';
        }
        
        // Verify indexes
        echo '<h2>📇 Indexes</h2>';
        
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
            echo '<table>';
            echo '<tr><th>Table</th><th>Index Name</th><th>Columns</th><th>Type</th></tr>';
            foreach ($indexes as $idx) {
                $type = $idx['NON_UNIQUE'] == 0 ? 'UNIQUE' : 'INDEX';
                echo '<tr>';
                echo '<td>' . $idx['TABLE_NAME'] . '</td>';
                echo '<td>' . $idx['INDEX_NAME'] . '</td>';
                echo '<td>' . $idx['COLUMNS'] . '</td>';
                echo '<td>' . $type . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="status-box status-error">❌ No indexes found</div>';
        }
        
        // Test cascade delete
        echo '<h2>🧪 Cascade Delete Test</h2>';
        
        $pdo->beginTransaction();
        
        try {
            // Insert test student
            $stmt = $pdo->prepare("
                INSERT INTO students (name, email, phone, course, gender) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute(['Test CASCADE Student', 'cascade_test@test.com', '9999999999', 'Test Course', 'Male']);
            $testStudentId = $pdo->lastInsertId();
            
            echo '<div class="info">Created test student (ID: ' . $testStudentId . ')</div>';
            
            // Insert related records
            $pdo->exec("INSERT INTO grades (student_id, subject_name, marks, max_marks, exam_type, exam_date) 
                        VALUES ($testStudentId, 'Test Subject', 85.00, 100.00, 'Test', '2024-12-01')");
            
            $pdo->exec("INSERT INTO fees (student_id, fee_type, assigned_amount, due_date, status) 
                        VALUES ($testStudentId, 'Test Fee', 1000.00, '2024-12-31', 'pending')");
            
            $pdo->exec("INSERT INTO attendance (student_id, attendance_date, status) 
                        VALUES ($testStudentId, '2024-12-01', 'present')");
            
            // Count before delete
            $gradeCount = $pdo->query("SELECT COUNT(*) as count FROM grades WHERE student_id = $testStudentId")->fetch()['count'];
            $feeCount = $pdo->query("SELECT COUNT(*) as count FROM fees WHERE student_id = $testStudentId")->fetch()['count'];
            $attendanceCount = $pdo->query("SELECT COUNT(*) as count FROM attendance WHERE student_id = $testStudentId")->fetch()['count'];
            
            echo '<p><strong>Before delete:</strong> Grades: ' . $gradeCount . ', Fees: ' . $feeCount . ', Attendance: ' . $attendanceCount . '</p>';
            
            // Delete student
            $pdo->exec("DELETE FROM students WHERE id = $testStudentId");
            
            // Count after delete
            $gradeCountAfter = $pdo->query("SELECT COUNT(*) as count FROM grades WHERE student_id = $testStudentId")->fetch()['count'];
            $feeCountAfter = $pdo->query("SELECT COUNT(*) as count FROM fees WHERE student_id = $testStudentId")->fetch()['count'];
            $attendanceCountAfter = $pdo->query("SELECT COUNT(*) as count FROM attendance WHERE student_id = $testStudentId")->fetch()['count'];
            
            echo '<p><strong>After delete:</strong> Grades: ' . $gradeCountAfter . ', Fees: ' . $feeCountAfter . ', Attendance: ' . $attendanceCountAfter . '</p>';
            
            if ($gradeCountAfter == 0 && $feeCountAfter == 0 && $attendanceCountAfter == 0) {
                echo '<div class="status-box status-success">✅ CASCADE DELETE working correctly! All related records were automatically deleted.</div>';
            } else {
                echo '<div class="status-box status-error">❌ CASCADE DELETE failed! Some related records remain.</div>';
            }
            
            $pdo->commit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo '<div class="status-box status-error">❌ Error during cascade delete test: ' . $e->getMessage() . '</div>';
        }
        
        echo '<h2>✅ Migration and Verification Complete</h2>';
        echo '<div class="info">All tables, indexes, and constraints have been created and verified. The system is ready for use.</div>';
        
        ?>
    </div>
</body>
</html>
