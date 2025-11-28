# Student Management System

A web-based application for managing student records with user authentication.

## Features

- User authentication system (login/logout)
- Student registration and management
- View, add, and manage student records
- Responsive web interface
- Secure password hashing
- Database-driven architecture

## Prerequisites

- PHP 7.4 or higher
- MySQL 8.0 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   ```

2. Import the database:
   - Create a new MySQL database named `student_management`
   - Import the `db.sql` file to set up the database structure and default admin user

3. Configure the database connection:
   - Edit `config/database.php` with your database credentials
   - Update the following constants as needed:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'your_username');
     define('DB_PASSWORD', 'your_password');
     define('DB_NAME', 'student_management');
     ```

4. Place the project files in your web server's root directory

## Default Login Credentials

- **Username:** admin
- **Password:** admin123

## Project Structure

```
project-root/
├── config/
│   └── database.php    # Database configuration
├── css/
│   └── style.css       # Stylesheets
├── js/
│   ├── main.js         # Main JavaScript file
│   └── ...
├── dashboard.php       # Main dashboard
├── index.html          # Landing page
├── login.html          # Login page
├── logout.php          # Logout handler
├── process_login.php   # Login processing
├── process_registration.php  # Registration handling
└── db.sql              # Database schema and sample data
```

## Database Schema

### Tables

1. **students**
   - id (INT, PK)
   - name (VARCHAR)
   - email (VARCHAR, UNIQUE)
   - phone (VARCHAR)
   - course (VARCHAR)
   - gender (ENUM)
   - created_at (TIMESTAMP)

2. **admin_users**
   - id (INT, PK)
   - username (VARCHAR, UNIQUE)
   - password_hash (VARCHAR)
   - created_at (TIMESTAMP)

## Security Notes

- Passwords are hashed using PHP's `password_hash()`
- SQL injection prevention using PDO prepared statements
- Session management for user authentication
- Always change the default admin password after first login

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For support, please open an issue in the repository or contact the development team.
