<?php
// Check authentication
require_once 'auth_check.php';

// Include database configuration
require_once 'config/database.php';

// Fetch all students for dropdown
try {
    $students_sql = "SELECT id, name, course FROM students ORDER BY name ASC";
    $students_stmt = $pdo->query($students_sql);
    $students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $students = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Management - Student Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay active">
        <div class="loading-spinner"></div>
        <p style="margin-top: 1rem; color: #7f8c8d; font-weight: 500;">Loading grade management...</p>
    </div>
    
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Grade Management</h1>
                <ul class="nav-menu">
                    <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                    <li><a href="registration.php" class="nav-link">Register Student</a></li>
                    <li><a href="grades.php" class="nav-link active">Grades</a></li>
                    <li><a href="fees.php" class="nav-link">Fees</a></li>
                    <li><a href="attendance.php" class="nav-link">Attendance</a></li>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="dashboard-header">
                <h2>Grade Management</h2>
                <p>Add and manage student grades and academic performance</p>
            </div>

            <!-- Grade Statistics Summary -->
            <div id="gradeSummary" class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h2 style="color: white; border-bottom-color: rgba(255,255,255,0.3);">Grade Statistics</h2>
                <div class="stats-grid" style="margin-top: 1rem;">
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #27ae60;">
                        <div class="stat-icon">📊</div>
                        <div class="stat-content">
                            <h3 id="overallAverage">0%</h3>
                            <p>Overall Average</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #3498db;">
                        <div class="stat-icon">📚</div>
                        <div class="stat-content">
                            <h3 id="totalGrades">0</h3>
                            <p>Total Grades</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #f39c12;">
                        <div class="stat-icon">👥</div>
                        <div class="stat-content">
                            <h3 id="studentsWithGrades">0</h3>
                            <p>Students with Grades</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #9b59b6;">
                        <div class="stat-icon">📖</div>
                        <div class="stat-content">
                            <h3 id="totalSubjects">0</h3>
                            <p>Unique Subjects</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Selector -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2>Select Student</h2>
                <div class="form-group">
                    <label for="studentSelect">Choose a student to manage grades:</label>
                    <select id="studentSelect" class="form-input">
                        <option value="">-- Select a Student --</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo htmlspecialchars($student['id']); ?>">
                                <?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['course']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Add Grade Form -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2>Add New Grade</h2>
                <form id="addGradeForm" class="registration-form">
                    <div class="form-group">
                        <label for="subjectName">Subject Name *</label>
                        <input type="text" id="subjectName" name="subject_name" class="form-input" placeholder="e.g., Mathematics, Physics, English" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="marks">Marks Obtained *</label>
                            <input type="number" id="marks" name="marks" class="form-input" placeholder="0" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="maxMarks">Maximum Marks *</label>
                            <input type="number" id="maxMarks" name="max_marks" class="form-input" placeholder="100" step="0.01" min="0.01" value="100" required>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="examType">Exam Type *</label>
                            <select id="examType" name="exam_type" class="form-input" required>
                                <option value="">-- Select Exam Type --</option>
                                <option value="Quiz">Quiz</option>
                                <option value="Assignment">Assignment</option>
                                <option value="Midterm">Midterm</option>
                                <option value="Final">Final</option>
                                <option value="Project">Project</option>
                                <option value="Practical">Practical</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="examDate">Exam Date *</label>
                            <input type="date" id="examDate" name="exam_date" class="form-input" required>
                        </div>
                    </div>
                    
                    <div id="percentageDisplay" style="padding: 1rem; background: #f8f9fa; border-radius: 8px; margin-bottom: 1rem; display: none;">
                        <strong>Percentage:</strong> <span id="calculatedPercentage" style="font-size: 1.2rem; color: #27ae60;">0%</span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full">Add Grade</button>
                </form>
            </div>

            <!-- Grades Table -->
            <div class="card">
                <h2>Grade Records</h2>
                <div id="noStudentMessage" style="text-align: center; padding: 2rem; color: #7f8c8d;">
                    Please select a student to view their grade records.
                </div>
                <div id="gradesTableContainer" style="display: none;">
                    <div id="studentGradeSummary" class="grade-summary" style="margin-bottom: 1.5rem; padding: 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px;">
                        <p style="margin: 0.5rem 0;"><strong>Student:</strong> <span id="selectedStudentName">-</span></p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-top: 1rem;">
                            <div>
                                <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Total Marks</p>
                                <p style="margin: 0; font-size: 1.3rem; font-weight: bold;"><span id="studentTotalMarks">0</span> / <span id="studentTotalMaxMarks">0</span></p>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Average Percentage</p>
                                <p style="margin: 0; font-size: 1.3rem; font-weight: bold;"><span id="studentAveragePercentage">0</span>%</p>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Total Subjects</p>
                                <p style="margin: 0; font-size: 1.3rem; font-weight: bold;"><span id="studentGradeCount">0</span></p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="students-table" id="gradesTable">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Marks</th>
                                    <th>Max Marks</th>
                                    <th>Percentage</th>
                                    <th>Exam Type</th>
                                    <th>Exam Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="gradesTableBody">
                                <!-- Grade records will be populated here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="noGradesMessage" style="display: none; text-align: center; padding: 2rem; color: #7f8c8d; background-color: #f8f9fa; border-radius: 8px; margin-top: 1rem;">
                        No grade records found for this student.
                    </div>
                </div>
            </div>
            
            <!-- Grade Visualization -->
            <div class="card" style="margin-top: 2rem; display: none;" id="gradeChartContainer">
                <h2>Performance Visualization</h2>
                <div style="max-width: 800px; margin: 0 auto;">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Student Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/loading.js"></script>
    <script>
        let currentStudentId = null;
        let gradeChart = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            hideLoading();
            loadGradeSummary();
            
            // Set default exam date to today
            document.getElementById('examDate').valueAsDate = new Date();
            
            // Student selector change event
            document.getElementById('studentSelect').addEventListener('change', handleStudentChange);
            
            // Form submission
            document.getElementById('addGradeForm').addEventListener('submit', handleAddGrade);
            
            // Real-time percentage calculation
            setupPercentageCalculation();
            
            // Check if student_id is in URL
            const urlParams = new URLSearchParams(window.location.search);
            const studentId = urlParams.get('student_id');
            if (studentId) {
                document.getElementById('studentSelect').value = studentId;
                handleStudentChange({ target: { value: studentId } });
            }
        });
        
        // Setup real-time percentage calculation
        function setupPercentageCalculation() {
            const marksInput = document.getElementById('marks');
            const maxMarksInput = document.getElementById('maxMarks');
            const percentageDisplay = document.getElementById('percentageDisplay');
            const calculatedPercentage = document.getElementById('calculatedPercentage');
            
            function calculatePercentage() {
                const marks = parseFloat(marksInput.value);
                const maxMarks = parseFloat(maxMarksInput.value);
                
                if (!isNaN(marks) && !isNaN(maxMarks) && maxMarks > 0) {
                    const percentage = ((marks / maxMarks) * 100).toFixed(2);
                    calculatedPercentage.textContent = percentage + '%';
                    percentageDisplay.style.display = 'block';
                    
                    // Color code based on percentage
                    if (percentage >= 90) {
                        calculatedPercentage.style.color = '#27ae60';
                    } else if (percentage >= 75) {
                        calculatedPercentage.style.color = '#3498db';
                    } else if (percentage >= 60) {
                        calculatedPercentage.style.color = '#f39c12';
                    } else {
                        calculatedPercentage.style.color = '#e74c3c';
                    }
                    
                    // Validate marks don't exceed max marks
                    if (marks > maxMarks) {
                        marksInput.style.borderColor = '#e74c3c';
                        marksInput.style.backgroundColor = '#fee';
                    } else {
                        marksInput.style.borderColor = '#27ae60';
                        marksInput.style.backgroundColor = '#efe';
                    }
                } else {
                    percentageDisplay.style.display = 'none';
                    marksInput.style.borderColor = '';
                    marksInput.style.backgroundColor = '';
                }
            }
            
            marksInput.addEventListener('input', calculatePercentage);
            maxMarksInput.addEventListener('input', calculatePercentage);
        }

        // Handle student selection change
        function handleStudentChange(e) {
            currentStudentId = e.target.value;
            
            if (currentStudentId) {
                loadStudentGrades(currentStudentId);
                document.getElementById('noStudentMessage').style.display = 'none';
                document.getElementById('gradesTableContainer').style.display = 'block';
            } else {
                document.getElementById('noStudentMessage').style.display = 'block';
                document.getElementById('gradesTableContainer').style.display = 'none';
                document.getElementById('gradeChartContainer').style.display = 'none';
            }
        }

        // Load grade summary for dashboard
        function loadGradeSummary() {
            fetch('manage_grades.php?action=fetch_all')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const grades = data.grades;
                        
                        // Calculate statistics
                        let totalMarks = 0;
                        let totalMaxMarks = 0;
                        let uniqueStudents = new Set();
                        let uniqueSubjects = new Set();
                        
                        grades.forEach(grade => {
                            totalMarks += parseFloat(grade.marks);
                            totalMaxMarks += parseFloat(grade.max_marks);
                            uniqueStudents.add(grade.student_id);
                            uniqueSubjects.add(grade.subject_name);
                        });
                        
                        const overallAverage = totalMaxMarks > 0 ? ((totalMarks / totalMaxMarks) * 100).toFixed(1) : 0;
                        
                        document.getElementById('overallAverage').textContent = overallAverage + '%';
                        document.getElementById('totalGrades').textContent = grades.length;
                        document.getElementById('studentsWithGrades').textContent = uniqueStudents.size;
                        document.getElementById('totalSubjects').textContent = uniqueSubjects.size;
                    }
                })
                .catch(error => {
                    console.error('Error loading grade summary:', error);
                });
        }

        // Load grades for selected student
        function loadStudentGrades(studentId) {
            showLoading();
            
            fetch(`manage_grades.php?action=fetch&student_id=${studentId}`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        displayStudentGrades(data);
                        createGradeChart(data.grades);
                    } else {
                        showNotification(data.message || 'Error loading grades', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading grades:', error);
                    showNotification('Error loading grades. Please try again.', 'error');
                });
        }

        // Display student grades in table
        function displayStudentGrades(data) {
            const student = data.student;
            const grades = data.grades;
            const summary = data.summary;
            
            // Update student info
            document.getElementById('selectedStudentName').textContent = student.name;
            document.getElementById('studentTotalMarks').textContent = summary.total_marks.toFixed(2);
            document.getElementById('studentTotalMaxMarks').textContent = summary.total_max_marks.toFixed(2);
            document.getElementById('studentAveragePercentage').textContent = summary.average_percentage.toFixed(2);
            document.getElementById('studentGradeCount').textContent = summary.grade_count;
            
            // Update table
            const tbody = document.getElementById('gradesTableBody');
            tbody.innerHTML = '';
            
            if (grades.length === 0) {
                document.getElementById('gradesTable').style.display = 'none';
                document.getElementById('noGradesMessage').style.display = 'block';
                document.getElementById('gradeChartContainer').style.display = 'none';
                return;
            }
            
            document.getElementById('gradesTable').style.display = 'table';
            document.getElementById('noGradesMessage').style.display = 'none';
            document.getElementById('gradeChartContainer').style.display = 'block';
            
            grades.forEach(grade => {
                const row = document.createElement('tr');
                const percentage = parseFloat(grade.percentage);
                
                // Color code percentage
                let percentageColor = '#27ae60';
                if (percentage < 60) percentageColor = '#e74c3c';
                else if (percentage < 75) percentageColor = '#f39c12';
                else if (percentage < 90) percentageColor = '#3498db';
                
                row.innerHTML = `
                    <td>${escapeHtml(grade.subject_name)}</td>
                    <td>${parseFloat(grade.marks).toFixed(2)}</td>
                    <td>${parseFloat(grade.max_marks).toFixed(2)}</td>
                    <td><span style="font-weight: bold; color: ${percentageColor};">${percentage.toFixed(2)}%</span></td>
                    <td>${escapeHtml(grade.exam_type)}</td>
                    <td>${formatDate(grade.exam_date)}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="deleteGrade(${grade.id})">Delete</button>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }

        // Create grade visualization chart
        function createGradeChart(grades) {
            const ctx = document.getElementById('gradeChart');
            
            if (!ctx || grades.length === 0) return;
            
            // Destroy existing chart
            if (gradeChart) {
                gradeChart.destroy();
            }
            
            // Prepare data
            const labels = grades.map(g => g.subject_name);
            const percentages = grades.map(g => parseFloat(g.percentage));
            
            // Create chart
            gradeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Percentage',
                        data: percentages,
                        backgroundColor: percentages.map(p => {
                            if (p >= 90) return 'rgba(39, 174, 96, 0.8)';
                            if (p >= 75) return 'rgba(52, 152, 219, 0.8)';
                            if (p >= 60) return 'rgba(243, 156, 18, 0.8)';
                            return 'rgba(231, 76, 60, 0.8)';
                        }),
                        borderColor: percentages.map(p => {
                            if (p >= 90) return 'rgba(39, 174, 96, 1)';
                            if (p >= 75) return 'rgba(52, 152, 219, 1)';
                            if (p >= 60) return 'rgba(243, 156, 18, 1)';
                            return 'rgba(231, 76, 60, 1)';
                        }),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Score: ' + context.parsed.y.toFixed(2) + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Handle add grade form submission
        function handleAddGrade(e) {
            e.preventDefault();
            
            if (!currentStudentId) {
                showNotification('Please select a student first', 'error');
                return;
            }
            
            // Client-side validation
            const marks = parseFloat(document.getElementById('marks').value);
            const maxMarks = parseFloat(document.getElementById('maxMarks').value);
            
            if (marks > maxMarks) {
                showNotification('Marks cannot exceed maximum marks', 'error');
                return;
            }
            
            if (maxMarks <= 0) {
                showNotification('Maximum marks must be greater than zero', 'error');
                return;
            }
            
            const formData = new FormData(e.target);
            formData.append('action', 'add');
            formData.append('student_id', currentStudentId);
            
            showLoading();
            
            fetch('manage_grades.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    showNotification('Grade added successfully!', 'success');
                    e.target.reset();
                    document.getElementById('examDate').valueAsDate = new Date();
                    document.getElementById('percentageDisplay').style.display = 'none';
                    loadStudentGrades(currentStudentId);
                    loadGradeSummary();
                } else {
                    showNotification(data.message || 'Error adding grade', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error adding grade:', error);
                showNotification('Error adding grade. Please try again.', 'error');
            });
        }

        // Delete grade
        function deleteGrade(gradeId) {
            if (!confirm('Are you sure you want to delete this grade? This action cannot be undone.')) {
                return;
            }
            
            showLoading();
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('grade_id', gradeId);
            
            fetch('manage_grades.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    showNotification('Grade deleted successfully!', 'success');
                    loadStudentGrades(currentStudentId);
                    loadGradeSummary();
                } else {
                    showNotification(data.message || 'Error deleting grade', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error deleting grade:', error);
                showNotification('Error deleting grade. Please try again.', 'error');
            });
        }

        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification ${type}-notification`;
            notification.textContent = message;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '10000';
            notification.style.minWidth = '300px';
            
            document.body.appendChild(notification);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>
