# Contributing to Student Management System

Thank you for your interest in contributing to the Student Management System! This document provides guidelines and instructions for contributing to this project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [How to Contribute](#how-to-contribute)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

### Our Standards

- Use welcoming and inclusive language
- Be respectful of differing viewpoints and experiences
- Gracefully accept constructive criticism
- Focus on what is best for the community
- Show empathy towards other community members

## Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally
3. Set up the development environment (see below)
4. Create a new branch for your feature or bugfix
5. Make your changes
6. Test thoroughly
7. Submit a pull request

## Development Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache or Nginx web server
- Git
- Composer (optional, for future dependencies)

### Installation

1. Clone your fork:
   ```bash
   git clone https://github.com/YOUR-USERNAME/student-management-system.git
   cd student-management-system
   ```

2. Set up the database:
   ```bash
   mysql -u root -p < db.sql
   mysql -u root -p < migration_add_grades_fees_attendance.sql
   ```

3. Configure database connection:
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. Start your local server:
   ```bash
   php -S localhost:8000
   ```

5. Access the application at `http://localhost:8000`

## How to Contribute

### Types of Contributions

We welcome various types of contributions:

- **Bug fixes**: Fix issues reported in the issue tracker
- **New features**: Add new functionality to the system
- **Documentation**: Improve or add documentation
- **Code refactoring**: Improve code quality and maintainability
- **Tests**: Add or improve test coverage
- **UI/UX improvements**: Enhance the user interface and experience

### Workflow

1. **Check existing issues**: Look for existing issues or create a new one
2. **Discuss**: Comment on the issue to discuss your approach
3. **Fork & Branch**: Create a feature branch from `main`
4. **Develop**: Write your code following our standards
5. **Test**: Ensure all functionality works correctly
6. **Commit**: Make clear, descriptive commits
7. **Push**: Push your changes to your fork
8. **Pull Request**: Submit a PR with a clear description

## Coding Standards

### PHP Standards

Follow PSR-12 coding standards:

```php
<?php
// Use proper indentation (4 spaces)
// Use meaningful variable names
// Add comments for complex logic

class StudentController
{
    public function getStudent(int $id): ?array
    {
        // Validate input
        if ($id <= 0) {
            return null;
        }
        
        // Fetch student data
        $student = $this->fetchStudentById($id);
        
        return $student;
    }
}
```

### JavaScript Standards

- Use ES6+ features where appropriate
- Use `const` and `let` instead of `var`
- Use meaningful function and variable names
- Add JSDoc comments for functions

```javascript
/**
 * Fetch student grades from the API
 * @param {number} studentId - The student ID
 * @returns {Promise<Object>} Grade data
 */
async function fetchStudentGrades(studentId) {
    const response = await fetch(`manage_grades.php?action=fetch&student_id=${studentId}`);
    return response.json();
}
```

### CSS Standards

- Use meaningful class names
- Follow BEM naming convention where appropriate
- Keep selectors simple and maintainable
- Add comments for complex styles

```css
/* Component: Student Card */
.student-card {
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.student-card__header {
    font-size: 1.2rem;
    font-weight: bold;
}
```

### Database Standards

- Use prepared statements for all queries
- Add proper indexes for frequently queried columns
- Use meaningful table and column names
- Add foreign key constraints where appropriate

```php
// Good: Using prepared statements
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute(['id' => $studentId]);

// Bad: Direct query concatenation
$query = "SELECT * FROM students WHERE id = " . $studentId; // NEVER DO THIS
```

## Commit Guidelines

### Commit Message Format

Use clear, descriptive commit messages:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples

```
feat(grades): add grade visualization chart

- Implement Chart.js integration
- Add subject-wise performance chart
- Color-code grades based on percentage

Closes #123
```

```
fix(fees): prevent overpayment validation

- Add client-side validation
- Update server-side checks
- Display clear error messages

Fixes #456
```

## Pull Request Process

### Before Submitting

1. **Test thoroughly**: Ensure all features work as expected
2. **Update documentation**: Update README.md if needed
3. **Check code style**: Follow coding standards
4. **Run existing tests**: Make sure nothing breaks
5. **Add comments**: Explain complex logic

### PR Description Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
Describe how you tested your changes

## Screenshots (if applicable)
Add screenshots for UI changes

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No new warnings generated
- [ ] Tests added/updated
```

### Review Process

1. Maintainers will review your PR
2. Address any requested changes
3. Once approved, your PR will be merged
4. Your contribution will be credited

## Reporting Bugs

### Before Reporting

1. Check if the bug has already been reported
2. Verify it's actually a bug and not expected behavior
3. Collect relevant information

### Bug Report Template

```markdown
**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce:
1. Go to '...'
2. Click on '...'
3. See error

**Expected behavior**
What you expected to happen.

**Screenshots**
If applicable, add screenshots.

**Environment:**
- OS: [e.g., Windows 10]
- Browser: [e.g., Chrome 96]
- PHP Version: [e.g., 7.4]
- MySQL Version: [e.g., 8.0]

**Additional context**
Any other relevant information.
```

## Suggesting Features

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
A clear description of the problem.

**Describe the solution you'd like**
A clear description of what you want to happen.

**Describe alternatives you've considered**
Alternative solutions or features you've considered.

**Additional context**
Any other context, mockups, or examples.
```

## Questions?

If you have questions about contributing:

- Open an issue with the `question` label
- Check existing documentation
- Review closed issues for similar questions

## Recognition

Contributors will be recognized in:
- README.md contributors section
- Release notes
- Project documentation

Thank you for contributing to the Student Management System! 🎉
