# Design Document

## Overview

This design document outlines the architecture and implementation approach for enhancing the existing Student Management System into a comprehensive School Management Tool. The enhancement will add grade management, fee tracking, attendance monitoring, and performance reporting while maintaining the existing authentication system and improving the user interface with modern, responsive design patterns.

The system will continue to use PHP with PDO for backend operations, MySQL for data persistence, and vanilla JavaScript for client-side interactivity. The design emphasizes security through prepared statements, session management, and input validation while providing an intuitive, card-based interface for efficient data management.

## Architecture

### System Architecture

The application follows a traditional three-tier architecture:

1. **Presentation Layer**: HTML/CSS/JavaScript frontend with responsive design
2. **Application Layer**: PHP backend with session management and business logic
3. **Data Layer**: MySQL database with normalized schema and foreign key relationships

### Technology Stack

- **Backend**: PHP 7.4+ with PDO for database operations
- **Database**: MySQL 8.0 with InnoDB engine for transaction support
- **Frontend**: HTML5, CSS3 with Flexbox/Grid, Vanilla JavaScript
- **Charts**: Chart.js for data visualization
- **Session Management**: PHP native sessions for authentication state

### Security Architecture

- PDO prepared statements for all database queries to prevent SQL injection
- Server-side session validation on all protected pages
- Password hashing using PHP's password_hash() and password_verify()
- Input sanitization and validation on both client and server sides
- HTTPS enforcement (recommended for production)

## Components and Interfaces

### 1. Database Schema Enhancement

#### New Tables

**grades table**
```sql
CREATE TABLE grades (
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
);
```

**fees table**
```sql
CREATE TABLE fees (
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
);
```

**attendance table**
```sql
CREATE TABLE attendance (
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
);
```

### 2. Backend PHP Components

#### Session Management Module (auth_check.php)
Centralized authentication check to be included in all protected pages:
```php
<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}
?>
```

#### Grade Management API (manage_grades.php)
Handles CRUD operations for student grades:
- Add new grade entry
- Update existing grade
- Delete grade entry
- Fetch grades by student ID
- Calculate total marks and averages

#### Fee Management API (manage_fees.php)
Handles fee-related operations:
- Assign new fee to student
- Record payment
- Update fee status
- Calculate outstanding balance
- Fetch fee records with filtering

#### Attendance Management API (manage_attendance.php)
Handles attendance operations:
- Mark attendance for single/multiple students
- Update attendance status
- Calculate attendance percentage
- Fetch attendance records by date range

#### Student Profile API (student_profile.php)
Aggregates all student data:
- Personal information
- Academic performance (grades)
- Financial status (fees)
- Attendance records
- Performance metrics

### 3. Frontend Components

#### Enhanced Dashboard (dashboard.php)
Redesigned with modern card-based layout:
- Statistics cards: Total students, pending fees, average attendance, average grades
- Quick action cards: Add grades, record payment, mark attendance
- Recent activities feed
- Performance charts
- Searchable student table with inline actions

#### Student Profile Page (student_profile.php)
Comprehensive view of individual student:
- Personal information card
- Academic performance card with grades table
- Fee status card with payment history
- Attendance card with calendar view
- Performance metrics and charts

#### Grade Management Page (grades.php)
Interface for managing student grades:
- Student selector dropdown
- Grade entry form with subject, marks, exam type
- Grades table with edit/delete actions
- Automatic calculation of totals and averages
- Subject-wise performance chart

#### Fee Management Page (fees.php)
Interface for managing student fees:
- Student selector dropdown
- Fee assignment form
- Payment recording form
- Fee records table with status indicators
- Outstanding balance summary
- Payment history timeline

#### Attendance Management Page (attendance.php)
Interface for marking and viewing attendance:
- Date selector
- Student list with quick mark present/absent buttons
- Bulk attendance marking
- Attendance calendar view
- Attendance statistics by student/date range

### 4. UI Component Library

#### Card Component
Reusable card design for consistent layout:
```css
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 1.5rem;
    transition: transform 0.3s, box-shadow 0.3s;
}
```

#### Modal Component
For forms and confirmations:
```javascript
class Modal {
    constructor(modalId) { /* ... */ }
    open() { /* ... */ }
    close() { /* ... */ }
}
```

#### Notification System
Toast notifications for user feedback:
```javascript
function showNotification(message, type) {
    // Display toast notification
    // Auto-dismiss after 5 seconds
}
```

#### Search/Filter Component
Real-time search functionality:
```javascript
function initializeSearch(tableId, searchInputId) {
    // Filter table rows based on search input
}
```

## Data Models

### Student Model (Enhanced)
```php
class Student {
    public $id;
    public $name;
    public $email;
    public $phone;
    public $course;
    public $gender;
    public $created_at;
    
    // Calculated properties
    public $total_grades;
    public $average_grade;
    public $attendance_percentage;
    public $outstanding_fees;
}
```

### Grade Model
```php
class Grade {
    public $id;
    public $student_id;
    public $subject_name;
    public $marks;
    public $max_marks;
    public $exam_type;
    public $exam_date;
    
    public function getPercentage() {
        return ($this->marks / $this->max_marks) * 100;
    }
}
```

### Fee Model
```php
class Fee {
    public $id;
    public $student_id;
    public $fee_type;
    public $assigned_amount;
    public $paid_amount;
    public $due_date;
    public $payment_date;
    public $status;
    
    public function getBalance() {
        return $this->assigned_amount - $this->paid_amount;
    }
    
    public function updateStatus() {
        $balance = $this->getBalance();
        if ($balance <= 0) return 'paid';
        if ($this->paid_amount > 0) return 'partial';
        if (strtotime($this->due_date) < time()) return 'overdue';
        return 'pending';
    }
}
```

### Attendance Model
```php
class Attendance {
    public $id;
    public $student_id;
    public $attendance_date;
    public $status;
    public $notes;
    
    public static function calculatePercentage($student_id, $start_date, $end_date) {
        // Calculate attendance percentage for date range
    }
}
```

## Error Handling

### Client-Side Validation
- Real-time form validation using JavaScript
- Visual feedback for invalid inputs
- Prevent form submission until all validations pass

### Server-Side Validation
- Validate all inputs before database operations
- Return JSON responses with error details
- HTTP status codes for different error types:
  - 400: Bad Request (validation errors)
  - 401: Unauthorized (authentication failure)
  - 404: Not Found (resource doesn't exist)
  - 409: Conflict (duplicate entry)
  - 500: Internal Server Error (database/system errors)

### Database Error Handling
```php
try {
    // Database operation
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'A database error occurred. Please try again.'
    ]);
}
```

### User-Friendly Error Messages
- Generic messages for users (avoid exposing system details)
- Detailed logging for developers
- Notification system for displaying errors

## Testing Strategy

### Manual Testing Approach

#### Unit Testing Focus Areas
1. **Database Operations**
   - Test CRUD operations for grades, fees, attendance
   - Verify foreign key constraints and cascading deletes
   - Test calculation functions (averages, balances, percentages)

2. **Form Validation**
   - Test client-side validation rules
   - Test server-side validation with invalid inputs
   - Test edge cases (negative numbers, dates, etc.)

3. **Session Management**
   - Test authentication flow
   - Test session timeout behavior
   - Test unauthorized access attempts

#### Integration Testing Focus Areas
1. **End-to-End Workflows**
   - Complete student registration to grade entry flow
   - Fee assignment to payment recording flow
   - Attendance marking and reporting flow

2. **Data Consistency**
   - Verify calculated values match database queries
   - Test data integrity across related tables
   - Verify cascade delete operations

#### User Acceptance Testing
1. **Usability Testing**
   - Test navigation flow between pages
   - Verify responsive design on different devices
   - Test search and filter functionality

2. **Performance Testing**
   - Test page load times with sample data
   - Test search performance with large datasets
   - Verify chart rendering performance

### Testing Checklist

**Authentication & Authorization**
- [ ] Login with valid credentials succeeds
- [ ] Login with invalid credentials fails
- [ ] Protected pages redirect to login when not authenticated
- [ ] Session persists across page navigation
- [ ] Logout clears session and redirects to login

**Grade Management**
- [ ] Add new grade entry successfully
- [ ] Update existing grade entry
- [ ] Delete grade entry with confirmation
- [ ] Calculate total marks correctly
- [ ] Calculate average percentage correctly
- [ ] Validate marks within acceptable range

**Fee Management**
- [ ] Assign new fee to student
- [ ] Record payment successfully
- [ ] Calculate outstanding balance correctly
- [ ] Update fee status automatically
- [ ] Prevent overpayment
- [ ] Display payment history

**Attendance Management**
- [ ] Mark attendance for single student
- [ ] Mark attendance for multiple students
- [ ] Prevent duplicate attendance entries
- [ ] Calculate attendance percentage correctly
- [ ] Display attendance calendar view

**UI/UX**
- [ ] Responsive design works on mobile devices
- [ ] Search functionality filters results correctly
- [ ] Notifications display and auto-dismiss
- [ ] Charts render correctly with data
- [ ] Forms validate inputs before submission
- [ ] Loading states display during operations

**Security**
- [ ] SQL injection attempts are prevented
- [ ] XSS attempts are sanitized
- [ ] Session hijacking is prevented
- [ ] Direct URL access to protected pages is blocked

## Implementation Phases

### Phase 1: Database Schema Enhancement
1. Create migration script for new tables
2. Add foreign key constraints
3. Create indexes for performance
4. Test cascade delete operations

### Phase 2: Backend API Development
1. Implement grade management API
2. Implement fee management API
3. Implement attendance management API
4. Implement student profile aggregation API
5. Add error handling and logging

### Phase 3: Frontend UI Enhancement
1. Redesign dashboard with card-based layout
2. Create grade management interface
3. Create fee management interface
4. Create attendance management interface
5. Create student profile page
6. Implement search and filter functionality

### Phase 4: Security Hardening
1. Remove public links to protected pages
2. Add session checks to all protected pages
3. Implement CSRF protection
4. Add rate limiting for API endpoints
5. Sanitize all user inputs

### Phase 5: Testing & Refinement
1. Perform manual testing of all features
2. Test responsive design on multiple devices
3. Optimize database queries
4. Refine UI based on usability feedback
5. Document API endpoints and usage

## Design Decisions and Rationales

### Why Card-Based Layout?
Card-based design provides visual separation of content, improves scannability, and creates a modern, professional appearance. Cards are also inherently responsive and work well on mobile devices.

### Why Separate API Files?
Separating concerns into dedicated API files (manage_grades.php, manage_fees.php, etc.) improves maintainability, makes testing easier, and allows for future API versioning.

### Why Cascade Delete?
Using CASCADE DELETE ensures data integrity by automatically removing related records when a student is deleted, preventing orphaned data in the database.

### Why Client and Server Validation?
Client-side validation provides immediate feedback and improves user experience, while server-side validation ensures security and data integrity regardless of client-side manipulation.

### Why Status Enum for Fees?
Using ENUM for fee status provides database-level validation, improves query performance, and ensures consistency in status values across the application.

### Why Separate Attendance Table?
A dedicated attendance table with unique constraints on student_id and date prevents duplicate entries and allows for efficient querying of attendance patterns over time.

### Why Chart.js?
Chart.js is lightweight, easy to implement, responsive, and provides sufficient visualization capabilities for the dashboard without requiring complex setup or dependencies.

### Why Not Use a Framework?
The existing codebase uses vanilla PHP and JavaScript. Maintaining consistency with the current architecture reduces complexity and learning curve while meeting all functional requirements.
