# Database Migration Instructions

## Task 1.2: Execute Migration and Verify Schema

This document provides instructions for running the database migration to add grades, fees, and attendance tables.

## Files Created

1. **run_migration.php** - CLI-based migration runner with detailed verification
2. **web_migration.php** - Web-based migration runner with visual interface
3. **verify_migration.php** - Simple verification script

## How to Run the Migration

### Option 1: Web Interface (Recommended)

1. Ensure your web server (Apache/Nginx) is running
2. Open your browser and navigate to:
   ```
   http://localhost/web_migration.php
   ```
   (Adjust the URL based on your local server configuration)

3. The page will automatically:
   - Execute all migration SQL statements
   - Create the three new tables (grades, fees, attendance)
   - Insert sample data for testing
   - Verify table creation
   - Check foreign key constraints
   - Verify indexes
   - Test CASCADE DELETE behavior

4. Review the results on the page

### Option 2: Command Line

If you prefer CLI, run:
```bash
php run_migration.php
```

This will execute the migration and display detailed verification results in the terminal.

### Option 3: Simple Verification

To just check if tables exist without running the full migration:
```bash
php verify_migration.php
```

## What the Migration Does

### Creates Three New Tables:

1. **grades** - Stores student academic performance
   - Columns: id, student_id, subject_name, marks, max_marks, exam_type, exam_date
   - Foreign key to students table with CASCADE DELETE
   - Indexes on student_id and exam_date

2. **fees** - Tracks student financial records
   - Columns: id, student_id, fee_type, assigned_amount, paid_amount, due_date, payment_date, status
   - Foreign key to students table with CASCADE DELETE
   - Indexes on student_id, status, and due_date

3. **attendance** - Records student attendance
   - Columns: id, student_id, attendance_date, status, notes
   - Foreign key to students table with CASCADE DELETE
   - Unique constraint on (student_id, attendance_date)
   - Indexes on student_id and attendance_date

### Inserts Sample Data:

- 10 grade entries for existing students
- 6 fee records with various statuses
- 20 attendance records

### Verifies:

- ✓ All tables created successfully
- ✓ Foreign key constraints with CASCADE DELETE
- ✓ Indexes created properly
- ✓ CASCADE DELETE behavior works correctly

## Expected Results

After successful migration, you should see:

- **grades table**: 10+ records
- **fees table**: 6+ records  
- **attendance table**: 20+ records
- **Foreign keys**: 3 constraints (one per table) referencing students.id with CASCADE DELETE
- **Indexes**: Multiple indexes for performance optimization
- **CASCADE DELETE test**: All related records deleted when parent student is removed

## Troubleshooting

### Database Connection Error
- Check `config/database.php` for correct credentials
- Ensure MySQL server is running
- Verify database 'student_management' exists

### Tables Already Exist
- The migration uses `CREATE TABLE IF NOT EXISTS`, so it's safe to run multiple times
- Existing tables won't be modified

### Permission Errors
- Ensure database user has CREATE, INSERT, and DELETE privileges

## Requirements Satisfied

This task satisfies requirements:
- **5.4**: CASCADE DELETE constraints for referential integrity
- **5.5**: Foreign key constraints between students and related tables

## Next Steps

After successful migration:
1. Verify all tables exist and contain sample data
2. Proceed to Task 2.1: Create manage_grades.php API endpoint
