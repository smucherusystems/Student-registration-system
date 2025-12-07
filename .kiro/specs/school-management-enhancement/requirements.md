# Requirements Document

## Introduction

This document outlines the requirements for enhancing the existing Student Management System into a comprehensive School Management Tool. The enhancement will add grade management, fee tracking, attendance monitoring, and performance reporting capabilities while improving the user interface with modern, responsive design. The system will maintain secure admin authentication and expand functionality to provide complete student lifecycle management.

## Glossary

- **System**: The School Management Tool web application
- **Admin**: An authenticated administrator user with full access to manage students, grades, fees, and attendance
- **Student Record**: A database entry containing student personal information, enrollment details, and associated academic data
- **Grade Entry**: A record of marks/scores for a student in a specific subject or course
- **Fee Record**: A financial record tracking assigned fees, payments made, and outstanding balances for a student
- **Attendance Record**: A log of student presence/absence for specific dates or sessions
- **Dashboard**: The main administrative interface displaying statistics, charts, and quick access to management features
- **Session**: A secure, server-side authentication state that validates admin access
- **PDO Prepared Statement**: A parameterized SQL query that prevents SQL injection attacks
- **Responsive Layout**: A user interface design that adapts to different screen sizes and devices

## Requirements

### Requirement 1

**User Story:** As an admin, I want to add and update student grades/marks so that I can maintain accurate academic records for each student

#### Acceptance Criteria

1. WHEN the Admin navigates to a student's grade management page, THE System SHALL display all existing grade entries for that student with subject names, marks, and dates
2. WHEN the Admin submits a new grade entry with valid student ID, subject name, and marks, THE System SHALL store the grade record in the database using PDO prepared statements
3. WHEN the Admin updates an existing grade entry, THE System SHALL modify the grade record and display a success notification
4. THE System SHALL calculate and display the total marks and average percentage for each student based on all grade entries
5. WHERE a grade entry form is displayed, THE System SHALL validate that marks are numeric values within acceptable ranges before submission

### Requirement 2

**User Story:** As an admin, I want to track and manage student fees including assignments, payments, and balances so that I can monitor the financial status of each student

#### Acceptance Criteria

1. WHEN the Admin assigns a fee to a student, THE System SHALL create a fee record with fee type, amount, due date, and student association
2. WHEN the Admin records a payment for a student, THE System SHALL update the fee record with payment amount and payment date
3. THE System SHALL calculate and display the outstanding balance for each student by subtracting total payments from total assigned fees
4. WHEN the Admin views the fee management page, THE System SHALL display all fee records with assigned amounts, paid amounts, and balances in a searchable table
5. IF a fee payment exceeds the assigned amount, THEN THE System SHALL display a validation error and prevent the transaction

### Requirement 3

**User Story:** As an admin, I want to manage student attendance records so that I can track presence and absence patterns for each student

#### Acceptance Criteria

1. WHEN the Admin accesses the attendance management interface, THE System SHALL display a list of students with options to mark attendance for specific dates
2. WHEN the Admin marks a student as present or absent for a date, THE System SHALL store the attendance record with student ID, date, and status
3. THE System SHALL calculate and display attendance percentage for each student based on total present days divided by total recorded days
4. WHEN the Admin views a student's profile, THE System SHALL display the attendance history with dates and status indicators
5. THE System SHALL prevent duplicate attendance entries for the same student and date combination

### Requirement 4

**User Story:** As an admin, I want to view comprehensive performance reports for students so that I can assess academic progress and identify areas needing attention

#### Acceptance Criteria

1. WHEN the Admin requests a student performance report, THE System SHALL display grades, attendance percentage, fee status, and overall performance metrics
2. THE System SHALL generate visual representations of student performance data using charts and graphs
3. WHEN the Admin filters performance reports by course or date range, THE System SHALL display only records matching the specified criteria
4. THE System SHALL calculate and display class rankings based on average grades for comparative analysis
5. WHERE performance data is incomplete, THE System SHALL indicate missing information clearly without displaying incorrect calculations

### Requirement 5

**User Story:** As an admin, I want an enhanced database model with proper relationships so that the system can efficiently store and retrieve grades, fees, and attendance data

#### Acceptance Criteria

1. THE System SHALL include a grades table with columns for grade ID, student ID foreign key, subject name, marks, maximum marks, and entry date
2. THE System SHALL include a fees table with columns for fee ID, student ID foreign key, fee type, assigned amount, paid amount, due date, and payment date
3. THE System SHALL include an attendance table with columns for attendance ID, student ID foreign key, attendance date, and status
4. WHEN a student record is deleted, THE System SHALL cascade delete all associated grades, fees, and attendance records to maintain referential integrity
5. THE System SHALL enforce foreign key constraints between students table and all related tables to prevent orphaned records

### Requirement 6

**User Story:** As an admin, I want a modern, responsive user interface with cards and clean design so that I can efficiently navigate and manage the system on any device

#### Acceptance Criteria

1. THE System SHALL display dashboard statistics in card-based layouts with icons, numbers, and descriptive labels
2. WHEN the Admin accesses the System from a mobile device, THE System SHALL adapt the layout to fit smaller screen sizes while maintaining functionality
3. THE System SHALL use consistent color schemes, typography, and spacing throughout all pages for professional appearance
4. WHEN the Admin views student lists or data tables, THE System SHALL provide search functionality to filter records by name, email, or course
5. THE System SHALL display loading indicators during data fetch operations and smooth transitions between interface states

### Requirement 7

**User Story:** As an admin, I want interactive forms with validation and notifications so that I can confidently enter data and receive immediate feedback on actions

#### Acceptance Criteria

1. WHEN the Admin submits a form with invalid data, THE System SHALL display specific error messages indicating which fields require correction
2. WHEN the Admin successfully adds or updates grades, fees, or attendance, THE System SHALL display a success notification with action confirmation
3. THE System SHALL validate all numeric inputs for grades and fees to ensure they are positive numbers within acceptable ranges
4. WHEN the Admin interacts with editable forms, THE System SHALL provide clear visual indicators for required fields and input formats
5. THE System SHALL use JavaScript for client-side validation to provide immediate feedback before server submission

### Requirement 8

**User Story:** As an admin, I want secure access control with session management so that only authenticated administrators can access student management features

#### Acceptance Criteria

1. WHEN an unauthenticated user attempts to access the dashboard or management pages, THE System SHALL redirect them to the login page
2. THE System SHALL validate admin credentials using secure password hashing and PDO prepared statements during login
3. WHEN the Admin successfully logs in, THE System SHALL create a server-side session and store authentication status
4. THE System SHALL remove links to registration and dashboard pages from the public home page to prevent unauthorized access attempts
5. WHEN the Admin logs out, THE System SHALL destroy the session and redirect to the login page

### Requirement 9

**User Story:** As an admin, I want searchable student records with quick access to detailed information so that I can efficiently locate and manage individual students

#### Acceptance Criteria

1. WHEN the Admin enters text in the search field, THE System SHALL filter the student list in real-time to show only matching records
2. THE System SHALL search across student name, email, phone, and course fields to provide comprehensive results
3. WHEN the Admin clicks on a student record, THE System SHALL display detailed information including grades, fees, and attendance in an organized layout
4. THE System SHALL provide quick action buttons for common tasks such as adding grades, recording payments, and marking attendance
5. THE System SHALL maintain search state when navigating between pages to preserve user context

### Requirement 10

**User Story:** As an admin, I want the registration and dashboard pages accessible only after login so that the system maintains proper security boundaries

#### Acceptance Criteria

1. THE System SHALL remove navigation links to registration and dashboard pages from the public index.html home page
2. WHEN the Admin successfully authenticates, THE System SHALL display navigation links to registration and dashboard pages in the authenticated header
3. THE System SHALL implement session checks on registration.html and dashboard.php to verify admin authentication before rendering content
4. IF an unauthenticated user attempts direct URL access to protected pages, THEN THE System SHALL redirect them to the login page
5. THE System SHALL maintain consistent navigation structure across all authenticated pages with logout functionality clearly visible
