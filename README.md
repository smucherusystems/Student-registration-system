# Student Management System

A comprehensive web-based application for managing student records, academic performance, financial transactions, and attendance tracking with secure user authentication.

## Overview

The Student Management System is a full-featured educational administration platform designed to streamline the management of student data, academic records, fee payments, and attendance tracking. Built with modern web technologies, it provides an intuitive interface for administrators to efficiently manage all aspects of student information in one centralized system.

## Key Features

### 🔐 Authentication & Security
- Secure admin authentication system with session management
- Password hashing using PHP's `password_hash()` function
- SQL injection prevention using PDO prepared statements
- Protected routes with centralized authentication checks
- Automatic session timeout and logout functionality

### 👥 Student Management
- Complete student registration with validation
- Comprehensive student profiles with all related data
- Edit and update student information
- Delete student records with cascade deletion
- Advanced search and filtering capabilities
- Real-time search across multiple fields (name, email, phone, course)

### 📚 Grade Management
- Add, view, and manage student grades
- Support for multiple exam types (Quiz, Midterm, Final, Assignment, Project, Practical)
- Automatic percentage calculation
- Subject-wise performance tracking
- Visual grade charts and analytics
- Class ranking system based on average performance
- Grade history with detailed records

### 💰 Fee Management
- Assign fees to students with due dates
- Record partial and full payments
- Automatic fee status updates (pending, partial, paid, overdue)
- Outstanding balance tracking
- Payment history timeline
- Overdue fee alerts
- Fee summary dashboard with statistics
- Validation to prevent overpayment

### 📅 Attendance Management
- Daily attendance marking with multiple status options (present, absent, late, excused)
- Bulk attendance operations (mark all present/absent)
- Attendance calendar view with color-coded status
- Attendance percentage calculation
- Date range filtering
- Low attendance alerts
- Monthly attendance reports
- Duplicate entry prevention

### 📊 Dashboard & Analytics
- Modern card-based dashboard layout
- Real-time statistics (total students, pending fees, average attendance, average grades)
- Quick action cards for common tasks
- Recent activity feeds
- Performance charts using Chart.js
- Student distribution by course and gender
- Responsive design for all devices

### 🎨 Modern UI/UX
- Clean, professional interface design
- Responsive layout for mobile, tablet, and desktop
- Loading states and smooth transitions
- Toast notifications for user feedback
- Color-coded status indicators
- Interactive data tables
- Form validation with real-time feedback

## Prerequisites

- PHP 7.4 or higher
- MySQL 8.0 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd student-management-system
```

### Step 2: Set Up the Database

1. Create a new MySQL database:
   ```sql
   CREATE DATABASE student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Import the initial database schema:
   ```bash
   mysql -u your_username -p student_management < db.sql
   ```

3. Run the migration to add grades, fees, and attendance tables:
   ```bash
   mysql -u your_username -p student_management < migration_add_grades_fees_attendance.sql
   ```

### Step 3: Configure Database Connection

Edit `config/database.php` with your database credentials:

```php
<?php
$host = 'localhost';
$dbname = 'student_management';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

### Step 4: Deploy to Web Server

1. Copy all project files to your web server's document root (e.g., `/var/www/html/` or `htdocs/`)
2. Ensure PHP has write permissions for session management
3. Configure your web server (Apache/Nginx) to serve the application

### Step 5: Access the Application

1. Open your web browser and navigate to: `http://localhost/` or your server URL
2. Click "Admin Login" to access the admin panel
3. Use the default credentials (see below)

## Quick Start Guide

### First Time Setup

1. **Login as Admin**
   - Navigate to the login page
   - Enter default credentials (username: `admin`, password: `admin123`)
   - **Important:** Change the default password immediately after first login

2. **Register Students**
   - Click "Register Student" in the navigation
   - Fill in student details (name, email, phone, course, gender)
   - Submit the form to add the student

3. **Manage Grades**
   - Go to "Grades" from the navigation
   - Select a student from the dropdown
   - Add grades with subject, marks, exam type, and date
   - View performance charts and statistics

4. **Manage Fees**
   - Navigate to "Fees"
   - Select a student
   - Assign fees with type, amount, and due date
   - Record payments as they are received

5. **Track Attendance**
   - Go to "Attendance"
   - Select a date
   - Mark attendance for all students (present, absent, late, excused)
   - View attendance calendar and statistics

## Default Login Credentials

- **Username:** admin
- **Password:** admin123

## Project Structure

```
project-root/
├── config/
│   └── database.php              # Database configuration
├── css/
│   └── style.css                 # Main stylesheet with modern design system
├── js/
│   ├── form-validation.js        # Form validation utility
│   ├── loading.js                # Loading state management
│   ├── modal.js                  # Modal dialog utility
│   ├── notifications.js          # Toast notification system
│   ├── script.js                 # Main JavaScript file
│   ├── search.js                 # Search and filter utility
│   └── validation.js             # Additional validation helpers
├── auth_check.php                # Centralized authentication check
├── dashboard.php                 # Main admin dashboard
├── registration.php              # Student registration page
├── edit_student.php              # Edit student information
├── student_profile.php           # Comprehensive student profile
├── grades.php                    # Grade management interface
├── fees.php                      # Fee management interface
├── attendance.php                # Attendance management interface
├── manage_grades.php             # Grade management API
├── manage_fees.php               # Fee management API
├── manage_attendance.php         # Attendance management API
├── index.html                    # Public landing page
├── login.html                    # Admin login page
├── logout.php                    # Logout handler
├── process_login.php             # Login processing
├── process_registration.php      # Student registration processing
├── db.sql                        # Initial database schema
└── migration_add_grades_fees_attendance.sql  # Enhanced schema migration
```

## Database Schema

### Tables

1. **students**
   - id (INT, PK, AUTO_INCREMENT)
   - name (VARCHAR 100)
   - email (VARCHAR 100, UNIQUE)
   - phone (VARCHAR 20)
   - course (VARCHAR 100)
   - gender (ENUM: 'Male', 'Female', 'Other')
   - created_at (TIMESTAMP)
   - updated_at (TIMESTAMP)

2. **admin_users**
   - id (INT, PK, AUTO_INCREMENT)
   - username (VARCHAR 50, UNIQUE)
   - password_hash (VARCHAR 255)
   - created_at (TIMESTAMP)

3. **grades**
   - id (INT, PK, AUTO_INCREMENT)
   - student_id (INT, FK → students.id, CASCADE DELETE)
   - subject_name (VARCHAR 100)
   - marks (DECIMAL 5,2)
   - max_marks (DECIMAL 5,2, DEFAULT 100.00)
   - exam_type (VARCHAR 50)
   - exam_date (DATE)
   - created_at (TIMESTAMP)
   - updated_at (TIMESTAMP)
   - Indexes: student_id, exam_date

4. **fees**
   - id (INT, PK, AUTO_INCREMENT)
   - student_id (INT, FK → students.id, CASCADE DELETE)
   - fee_type (VARCHAR 100)
   - assigned_amount (DECIMAL 10,2)
   - paid_amount (DECIMAL 10,2, DEFAULT 0.00)
   - due_date (DATE)
   - payment_date (DATE, NULL)
   - status (ENUM: 'pending', 'partial', 'paid', 'overdue')
   - created_at (TIMESTAMP)
   - updated_at (TIMESTAMP)
   - Indexes: student_id, status, due_date

5. **attendance**
   - id (INT, PK, AUTO_INCREMENT)
   - student_id (INT, FK → students.id, CASCADE DELETE)
   - attendance_date (DATE)
   - status (ENUM: 'present', 'absent', 'late', 'excused')
   - notes (TEXT, NULL)
   - created_at (TIMESTAMP)
   - updated_at (TIMESTAMP)
   - Unique Key: (student_id, attendance_date)
   - Indexes: student_id, attendance_date

## Technology Stack

### Backend
- **PHP 7.4+**: Server-side scripting
- **MySQL 8.0+**: Relational database management
- **PDO**: Database abstraction layer with prepared statements

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Modern styling with Grid and Flexbox
- **JavaScript (ES6+)**: Client-side interactivity
- **Chart.js**: Data visualization library

### Architecture
- **MVC Pattern**: Separation of concerns
- **RESTful API**: JSON-based backend APIs
- **AJAX**: Asynchronous data operations
- **Session-based Authentication**: Secure user sessions

## API Endpoints

### Grade Management API (`manage_grades.php`)
- `GET ?action=fetch&student_id={id}` - Fetch grades for a student
- `GET ?action=fetch_all` - Fetch all grades
- `POST action=add` - Add new grade
- `POST action=update` - Update existing grade
- `POST action=delete` - Delete grade

### Fee Management API (`manage_fees.php`)
- `GET ?action=fetch&student_id={id}` - Fetch fees for a student
- `GET ?action=fetch_summary` - Fetch fee statistics
- `GET ?action=fetch_payment_history&student_id={id}` - Fetch payment timeline
- `POST action=assign` - Assign new fee
- `POST action=record_payment` - Record payment

### Attendance Management API (`manage_attendance.php`)
- `GET ?action=fetch_all&attendance_date={date}` - Fetch attendance for a date
- `GET ?action=fetch_statistics` - Fetch attendance statistics
- `GET ?action=fetch_calendar&student_id={id}&month={m}&year={y}` - Fetch calendar data
- `POST action=mark` - Mark attendance (bulk operation)

## Security Features

### Authentication & Authorization
- Secure password hashing using `password_hash()` with bcrypt algorithm
- Session-based authentication with timeout
- Centralized authentication checks (`auth_check.php`)
- Protected routes - all admin pages require authentication
- Automatic redirect to login for unauthorized access

### Data Security
- SQL injection prevention using PDO prepared statements
- XSS protection with `htmlspecialchars()` for output
- CSRF protection through session validation
- Input validation on both client and server side
- Parameterized queries for all database operations

### Best Practices
- **Change default credentials immediately** after installation
- Use strong passwords (minimum 8 characters, mix of letters, numbers, symbols)
- Regular database backups
- Keep PHP and MySQL updated
- Use HTTPS in production environments
- Implement rate limiting for login attempts (recommended)
- Regular security audits

## Browser Compatibility

- Chrome 90+ ✅
- Firefox 88+ ✅
- Safari 14+ ✅
- Edge 90+ ✅
- Opera 76+ ✅

## Performance Optimization

- Lazy loading for images and charts
- Efficient database queries with proper indexing
- Client-side caching for static resources
- Minified CSS and JavaScript (recommended for production)
- Optimized database schema with foreign keys and indexes

## Troubleshooting

### Common Issues

**Issue: Database connection failed**
- Solution: Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists and user has proper permissions

**Issue: Session errors**
- Solution: Ensure PHP has write permissions to session directory
- Check `session.save_path` in `php.ini`

**Issue: Charts not displaying**
- Solution: Ensure Chart.js CDN is accessible
- Check browser console for JavaScript errors
- Verify internet connection for CDN resources

**Issue: 404 errors for pages**
- Solution: Ensure all PHP files are in the correct directory
- Check web server configuration
- Verify file permissions (644 for files, 755 for directories)

## Future Enhancements

- [ ] Email notifications for overdue fees and low attendance
- [ ] PDF report generation for student profiles
- [ ] Multi-language support
- [ ] Role-based access control (teachers, students, admin)
- [ ] Mobile application
- [ ] Bulk import/export functionality (CSV, Excel)
- [ ] Advanced analytics and reporting
- [ ] Parent portal for viewing student progress
- [ ] SMS notifications
- [ ] Integration with payment gateways

## Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards
- Follow PSR-12 coding standards for PHP
- Use meaningful variable and function names
- Comment complex logic
- Write clean, maintainable code
- Test thoroughly before submitting PR

## License

This project is open source and available under the [MIT License](LICENSE).

## Changelog

### Version 2.0.0 (Current)
- ✨ Added comprehensive grade management system
- ✨ Added fee management with payment tracking
- ✨ Added attendance management with calendar view
- ✨ Enhanced dashboard with statistics and charts
- ✨ Implemented student profile with performance reports
- 🎨 Modern UI redesign with card-based layout
- 🔒 Enhanced security with centralized authentication
- 📱 Fully responsive design for all devices

### Version 1.0.0
- ✨ Initial release
- 👥 Basic student registration and management
- 🔐 Admin authentication system
- 📊 Simple dashboard

## Support & Contact

For support, questions, or feedback:
- 📧 Email: support@studentmanagement.com
- 🐛 Issues: Open an issue in the repository
- 💬 Discussions: Use GitHub Discussions
- 📖 Documentation: See this README and inline code comments

## Acknowledgments

- Chart.js for beautiful data visualizations
- PHP community for excellent documentation
- All contributors who have helped improve this project

---

**Made with ❤️ for educational institutions worldwide**
