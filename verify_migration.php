<?php
/**
 * Simple Migration Verification Script
 */

require_once 'config/database.php';

echo "Verifying Migration...\n\n";

try {
    // Check if tables exist
    $tables = ['grades', 'fees', 'attendance'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $countStmt->fetch()['count'];
            echo "Table '$table': EXISTS ($count records)\n";
        } else {
            echo "Table '$table': NOT FOUND\n";
        }
    }
    
    echo "\nVerification complete.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
