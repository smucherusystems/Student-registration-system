<?php
// Check authentication
require_once 'auth_check.php';

// Include database configuration
require_once 'config/database.php';

// Fetch all students for attendance marking
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
    <title>Attendance Management - Student Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay active">
        <div class="loading-spinner"></div>
        <p style="margin-top: 1rem; color: #7f8c8d; font-weight: 500;">Loading attendance management...</p>
    </div>
    
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Attendance Management</h1>
                <ul class="nav-menu">
                    <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                    <li><a href="registration.php" class="nav-link">Register Student</a></li>
                    <li><a href="grades.php" class="nav-link">Grades</a></li>
                    <li><a href="fees.php" class="nav-link">Fees</a></li>
                    <li><a href="attendance.php" class="nav-link active">Attendance</a></li>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="dashboard-header">
                <h2>Attendance Management</h2>
                <p>Mark and track student attendance</p>
            </div>

            <!-- Attendance Statistics Summary -->
            <div id="statisticsSummary" class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h2 style="color: white; border-bottom-color: rgba(255,255,255,0.3);">Attendance Statistics</h2>
                <div class="stats-grid" style="margin-top: 1rem;">
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #27ae60;">
                        <div class="stat-icon">✓</div>
                        <div class="stat-content">
                            <h3 id="overallPercentage">0%</h3>
                            <p>Overall Attendance</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #3498db;">
                        <div class="stat-icon">📊</div>
                        <div class="stat-content">
                            <h3 id="totalRecords">0</h3>
                            <p>Total Records</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #27ae60;">
                        <div class="stat-icon">👥</div>
                        <div class="stat-content">
                            <h3 id="presentCount">0</h3>
                            <p>Present Today</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #e74c3c;">
                        <div class="stat-icon">❌</div>
                        <div class="stat-content">
                            <h3 id="absentCount">0</h3>
                            <p>Absent Today</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Selector and Bulk Actions -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2>Mark Attendance</h2>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="attendanceDate">Select Date:</label>
                    <input type="date" id="attendanceDate" class="form-input" style="max-width: 300px;">
                </div>
                
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                    <button id="markAllPresentBtn" class="btn btn-success">
                        <span>✓</span> Mark All Present
                    </button>
                    <button id="markAllAbsentBtn" class="btn btn-danger">
                        <span>❌</span> Mark All Absent
                    </button>
                    <button id="saveAttendanceBtn" class="btn btn-primary">
                        <span>💾</span> Save Attendance
                    </button>
                    <button id="loadAttendanceBtn" class="btn btn-secondary">
                        <span>🔄</span> Load Existing
                    </button>
                </div>
                
                <div id="attendanceMessage" style="display: none; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"></div>
            </div>

            <!-- Attendance Calendar View -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2>Attendance Calendar View</h2>
                
                <!-- Student Selector and Date Range -->
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; align-items: flex-end;">
                    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                        <label for="calendarStudentSelect">Select Student:</label>
                        <select id="calendarStudentSelect" class="form-input">
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo htmlspecialchars($student['id']); ?>">
                                    <?php echo htmlspecialchars($student['name']); ?> - <?php echo htmlspecialchars($student['course']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="min-width: 150px; margin-bottom: 0;">
                        <label for="calendarMonth">Month:</label>
                        <select id="calendarMonth" class="form-input">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="min-width: 120px; margin-bottom: 0;">
                        <label for="calendarYear">Year:</label>
                        <select id="calendarYear" class="form-input">
                            <!-- Years will be populated by JavaScript -->
                        </select>
                    </div>
                    
                    <button id="loadCalendarBtn" class="btn btn-primary" style="height: fit-content;">
                        <span>📅</span> Load Calendar
                    </button>
                </div>
                
                <!-- Calendar Display -->
                <div id="calendarContainer" style="display: none;">
                    <div id="calendarHeader" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px;">
                        <h3 id="calendarTitle" style="margin: 0; color: white;">Calendar</h3>
                        <div id="calendarStats" style="display: flex; gap: 1.5rem; font-size: 0.9rem;">
                            <span>📊 <strong id="calendarPercentage">0%</strong></span>
                            <span>✓ <strong id="calendarPresent">0</strong></span>
                            <span>❌ <strong id="calendarAbsent">0</strong></span>
                        </div>
                    </div>
                    
                    <!-- Legend -->
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; padding: 0.75rem; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div class="calendar-legend-box" style="background-color: #27ae60;"></div>
                            <span>Present</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div class="calendar-legend-box" style="background-color: #e74c3c;"></div>
                            <span>Absent</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div class="calendar-legend-box" style="background-color: #f39c12;"></div>
                            <span>Late</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div class="calendar-legend-box" style="background-color: #3498db;"></div>
                            <span>Excused</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div class="calendar-legend-box" style="background-color: #ecf0f1; border: 1px solid #bdc3c7;"></div>
                            <span>Not Marked</span>
                        </div>
                    </div>
                    
                    <!-- Calendar Grid -->
                    <div class="calendar-grid">
                        <div class="calendar-day-header">Sun</div>
                        <div class="calendar-day-header">Mon</div>
                        <div class="calendar-day-header">Tue</div>
                        <div class="calendar-day-header">Wed</div>
                        <div class="calendar-day-header">Thu</div>
                        <div class="calendar-day-header">Fri</div>
                        <div class="calendar-day-header">Sat</div>
                    </div>
                    <div class="calendar-grid" id="calendarDays" style="margin-top: 0.5rem;">
                        <!-- Calendar days will be populated by JavaScript -->
                    </div>
                </div>
                
                <div id="calendarMessage" style="display: none; padding: 2rem; text-align: center; color: #7f8c8d; background-color: #f8f9fa; border-radius: 8px;">
                    Select a student and click "Load Calendar" to view attendance patterns
                </div>
            </div>

            <!-- Student List for Attendance Marking -->
            <div class="card">
                <h2>Student List</h2>
                
                <!-- Search Box -->
                <div class="search-box" style="margin-bottom: 1.5rem;">
                    <input type="text" id="studentSearchInput" class="search-input" placeholder="Search students by name or course...">
                </div>
                
                <div id="noStudentsMessage" style="display: none; text-align: center; padding: 2rem; color: #7f8c8d; background-color: #f8f9fa; border-radius: 8px;">
                    No students registered yet.
                </div>
                
                <div id="studentListContainer">
                    <div style="overflow-x: auto;">
                        <table class="students-table" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Quick Actions</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody">
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 2rem; color: #7f8c8d;">
                                            No students available
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr data-student-id="<?php echo htmlspecialchars($student['id']); ?>" 
                                            data-student-name="<?php echo htmlspecialchars(strtolower($student['name'])); ?>"
                                            data-student-course="<?php echo htmlspecialchars(strtolower($student['course'])); ?>">
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['course']); ?></td>
                                            <td>
                                                <select class="status-select form-input" style="padding: 0.5rem; min-width: 120px;">
                                                    <option value="">-- Select --</option>
                                                    <option value="present">Present</option>
                                                    <option value="absent">Absent</option>
                                                    <option value="late">Late</option>
                                                    <option value="excused">Excused</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <button class="btn btn-success btn-sm quick-present" title="Mark Present">
                                                        ✓
                                                    </button>
                                                    <button class="btn btn-danger btn-sm quick-absent" title="Mark Absent">
                                                        ❌
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" class="notes-input form-input" placeholder="Optional notes..." style="padding: 0.5rem; min-width: 150px;">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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
        let attendanceData = new Map();
        let hasUnsavedChanges = false;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            hideLoading();
            
            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('attendanceDate').value = today;
            
            // Load statistics
            loadAttendanceStatistics();
            
            // Load attendance for today
            loadExistingAttendance();
            
            // Initialize event listeners
            initializeEventListeners();
            
            // Initialize search functionality
            initializeSearch();
            
            // Initialize calendar functionality
            initializeCalendar();
            
            // Check if students exist
            const tbody = document.getElementById('attendanceTableBody');
            const rows = tbody.querySelectorAll('tr[data-student-id]');
            if (rows.length === 0) {
                document.getElementById('noStudentsMessage').style.display = 'block';
                document.getElementById('studentListContainer').style.display = 'none';
            }
        });
        
        // Initialize all event listeners
        function initializeEventListeners() {
            // Date change event
            document.getElementById('attendanceDate').addEventListener('change', function() {
                if (hasUnsavedChanges) {
                    if (confirm('You have unsaved changes. Do you want to load attendance for the new date?')) {
                        loadExistingAttendance();
                        hasUnsavedChanges = false;
                    } else {
                        // Revert to previous date
                        this.value = new Date().toISOString().split('T')[0];
                    }
                } else {
                    loadExistingAttendance();
                }
            });
            
            // Bulk action buttons
            document.getElementById('markAllPresentBtn').addEventListener('click', () => markAllStatus('present'));
            document.getElementById('markAllAbsentBtn').addEventListener('click', () => markAllStatus('absent'));
            document.getElementById('saveAttendanceBtn').addEventListener('click', saveAttendance);
            document.getElementById('loadAttendanceBtn').addEventListener('click', loadExistingAttendance);
            
            // Quick action buttons for each student
            document.querySelectorAll('.quick-present').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const select = row.querySelector('.status-select');
                    select.value = 'present';
                    updateRowStyle(row, 'present');
                    hasUnsavedChanges = true;
                });
            });
            
            document.querySelectorAll('.quick-absent').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const select = row.querySelector('.status-select');
                    select.value = 'absent';
                    updateRowStyle(row, 'absent');
                    hasUnsavedChanges = true;
                });
            });
            
            // Status select change events
            document.querySelectorAll('.status-select').forEach(select => {
                select.addEventListener('change', function() {
                    const row = this.closest('tr');
                    updateRowStyle(row, this.value);
                    hasUnsavedChanges = true;
                });
            });
            
            // Notes input change events
            document.querySelectorAll('.notes-input').forEach(input => {
                input.addEventListener('input', function() {
                    hasUnsavedChanges = true;
                });
            });
        }
        
        // Initialize search functionality
        function initializeSearch() {
            const searchInput = document.getElementById('studentSearchInput');
            const tbody = document.getElementById('attendanceTableBody');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = tbody.querySelectorAll('tr[data-student-id]');
                
                rows.forEach(row => {
                    const name = row.getAttribute('data-student-name') || '';
                    const course = row.getAttribute('data-student-course') || '';
                    
                    if (name.includes(searchTerm) || course.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
        
        // Update row styling based on status
        function updateRowStyle(row, status) {
            // Remove all status classes
            row.classList.remove('status-present', 'status-absent', 'status-late', 'status-excused');
            
            // Add appropriate class
            if (status) {
                row.classList.add(`status-${status}`);
                
                // Update row background color
                switch(status) {
                    case 'present':
                        row.style.backgroundColor = '#d5f4e6';
                        break;
                    case 'absent':
                        row.style.backgroundColor = '#fadbd8';
                        break;
                    case 'late':
                        row.style.backgroundColor = '#fff3cd';
                        break;
                    case 'excused':
                        row.style.backgroundColor = '#d1ecf1';
                        break;
                    default:
                        row.style.backgroundColor = '';
                }
            } else {
                row.style.backgroundColor = '';
            }
        }
        
        // Mark all students with a specific status
        function markAllStatus(status) {
            const tbody = document.getElementById('attendanceTableBody');
            const rows = tbody.querySelectorAll('tr[data-student-id]');
            
            rows.forEach(row => {
                // Only mark visible rows (respecting search filter)
                if (row.style.display !== 'none') {
                    const select = row.querySelector('.status-select');
                    select.value = status;
                    updateRowStyle(row, status);
                }
            });
            
            hasUnsavedChanges = true;
            showMessage(`All visible students marked as ${status}`, 'success');
        }
        
        // Load existing attendance for selected date
        function loadExistingAttendance() {
            const date = document.getElementById('attendanceDate').value;
            
            if (!date) {
                showMessage('Please select a date', 'error');
                return;
            }
            
            showLoading();
            
            fetch(`manage_attendance.php?action=fetch_all&attendance_date=${date}`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        populateAttendanceData(data.attendance);
                        showMessage(`Loaded ${data.count} attendance records for ${date}`, 'success');
                        hasUnsavedChanges = false;
                    } else {
                        // No records found, clear the form
                        clearAttendanceForm();
                        showMessage('No attendance records found for this date', 'info');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading attendance:', error);
                    showMessage('Error loading attendance. Please try again.', 'error');
                });
        }
        
        // Populate attendance data into the form
        function populateAttendanceData(attendanceRecords) {
            // Clear existing data
            clearAttendanceForm();
            
            // Create a map of student_id => attendance record
            const attendanceMap = new Map();
            attendanceRecords.forEach(record => {
                attendanceMap.set(record.student_id.toString(), record);
            });
            
            // Populate the form
            const tbody = document.getElementById('attendanceTableBody');
            const rows = tbody.querySelectorAll('tr[data-student-id]');
            
            rows.forEach(row => {
                const studentId = row.getAttribute('data-student-id');
                const attendance = attendanceMap.get(studentId);
                
                if (attendance) {
                    const select = row.querySelector('.status-select');
                    const notesInput = row.querySelector('.notes-input');
                    
                    select.value = attendance.status;
                    notesInput.value = attendance.notes || '';
                    updateRowStyle(row, attendance.status);
                }
            });
        }
        
        // Clear attendance form
        function clearAttendanceForm() {
            const tbody = document.getElementById('attendanceTableBody');
            const rows = tbody.querySelectorAll('tr[data-student-id]');
            
            rows.forEach(row => {
                const select = row.querySelector('.status-select');
                const notesInput = row.querySelector('.notes-input');
                
                select.value = '';
                notesInput.value = '';
                updateRowStyle(row, '');
            });
        }
        
        // Save attendance
        function saveAttendance() {
            const date = document.getElementById('attendanceDate').value;
            
            if (!date) {
                showMessage('Please select a date', 'error');
                return;
            }
            
            // Collect attendance data
            const tbody = document.getElementById('attendanceTableBody');
            const rows = tbody.querySelectorAll('tr[data-student-id]');
            const attendanceRecords = [];
            
            rows.forEach(row => {
                const studentId = row.getAttribute('data-student-id');
                const select = row.querySelector('.status-select');
                const notesInput = row.querySelector('.notes-input');
                const status = select.value;
                const notes = notesInput.value.trim();
                
                if (status) {
                    attendanceRecords.push({
                        student_id: studentId,
                        status: status,
                        notes: notes
                    });
                }
            });
            
            if (attendanceRecords.length === 0) {
                showMessage('Please mark attendance for at least one student', 'error');
                return;
            }
            
            // Check for existing attendance records first (client-side duplicate prevention)
            showLoading();
            
            fetch(`manage_attendance.php?action=fetch_all&attendance_date=${date}`)
                .then(response => response.json())
                .then(data => {
                    let existingRecords = new Set();
                    
                    if (data.success && data.attendance) {
                        // Create a set of student IDs that already have attendance for this date
                        data.attendance.forEach(record => {
                            existingRecords.add(record.student_id.toString());
                        });
                    }
                    
                    // Filter out records that already exist
                    const newRecords = attendanceRecords.filter(record => 
                        !existingRecords.has(record.student_id.toString())
                    );
                    
                    const duplicateCount = attendanceRecords.length - newRecords.length;
                    
                    if (duplicateCount > 0) {
                        showMessage(`${duplicateCount} student(s) already have attendance marked for this date. Only new records will be saved.`, 'warning');
                    }
                    
                    if (newRecords.length === 0) {
                        hideLoading();
                        showMessage('All selected students already have attendance marked for this date. Use "Load Existing" to modify.', 'info');
                        return;
                    }
                    
                    // Save only new attendance records
                    saveAttendanceRecords(newRecords, date);
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error checking existing attendance:', error);
                    showMessage('Error checking existing records. Please try again.', 'error');
                });
        }
        
        // Helper function to save attendance records
        function saveAttendanceRecords(attendanceRecords, date) {
            let successCount = 0;
            let errorCount = 0;
            let promises = [];
            
            attendanceRecords.forEach(record => {
                const formData = new FormData();
                formData.append('action', 'mark');
                formData.append('student_id', record.student_id);
                formData.append('attendance_date', date);
                formData.append('status', record.status);
                formData.append('notes', record.notes);
                
                const promise = fetch('manage_attendance.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successCount++;
                    } else {
                        errorCount++;
                        console.error('Error saving attendance:', data.message);
                    }
                })
                .catch(error => {
                    errorCount++;
                    console.error('Error saving attendance:', error);
                });
                
                promises.push(promise);
            });
            
            Promise.all(promises).then(() => {
                hideLoading();
                
                if (successCount > 0) {
                    showMessage(`Successfully saved ${successCount} attendance record(s)`, 'success');
                    hasUnsavedChanges = false;
                    
                    // Auto-refresh attendance display
                    loadAttendanceStatistics();
                    loadExistingAttendance();
                }
                
                if (errorCount > 0) {
                    showMessage(`${errorCount} record(s) failed to save. Please try again.`, 'error');
                }
            });
        }
        
        // Load attendance statistics
        function loadAttendanceStatistics() {
            fetch('manage_attendance.php?action=fetch_statistics')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.statistics;
                        document.getElementById('overallPercentage').textContent = stats.overall_attendance_percentage + '%';
                        document.getElementById('totalRecords').textContent = stats.total_records;
                        
                        // Load today's stats
                        loadTodayStats();
                    }
                })
                .catch(error => {
                    console.error('Error loading statistics:', error);
                });
        }
        
        // Load today's attendance stats
        function loadTodayStats() {
            const today = new Date().toISOString().split('T')[0];
            
            fetch(`manage_attendance.php?action=fetch_all&attendance_date=${today}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const attendance = data.attendance;
                        let presentCount = 0;
                        let absentCount = 0;
                        
                        attendance.forEach(record => {
                            if (record.status === 'present' || record.status === 'late') {
                                presentCount++;
                            } else if (record.status === 'absent') {
                                absentCount++;
                            }
                        });
                        
                        document.getElementById('presentCount').textContent = presentCount;
                        document.getElementById('absentCount').textContent = absentCount;
                    }
                })
                .catch(error => {
                    console.error('Error loading today stats:', error);
                });
        }
        
        // Show message
        function showMessage(message, type) {
            const messageDiv = document.getElementById('attendanceMessage');
            messageDiv.textContent = message;
            messageDiv.style.display = 'block';
            
            // Set color based on type
            switch(type) {
                case 'success':
                    messageDiv.style.backgroundColor = '#d4edda';
                    messageDiv.style.color = '#155724';
                    messageDiv.style.borderLeft = '4px solid #28a745';
                    break;
                case 'error':
                    messageDiv.style.backgroundColor = '#f8d7da';
                    messageDiv.style.color = '#721c24';
                    messageDiv.style.borderLeft = '4px solid #dc3545';
                    break;
                case 'warning':
                    messageDiv.style.backgroundColor = '#fff3cd';
                    messageDiv.style.color = '#856404';
                    messageDiv.style.borderLeft = '4px solid #ffc107';
                    break;
                case 'info':
                    messageDiv.style.backgroundColor = '#d1ecf1';
                    messageDiv.style.color = '#0c5460';
                    messageDiv.style.borderLeft = '4px solid #17a2b8';
                    break;
            }
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageDiv.style.opacity = '0';
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                    messageDiv.style.opacity = '1';
                }, 300);
            }, 5000);
        }
        
        // Warn user about unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });
        
        // Initialize calendar functionality
        function initializeCalendar() {
            // Populate year dropdown
            const yearSelect = document.getElementById('calendarYear');
            const currentYear = new Date().getFullYear();
            
            for (let year = currentYear - 5; year <= currentYear + 1; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === currentYear) {
                    option.selected = true;
                }
                yearSelect.appendChild(option);
            }
            
            // Set current month
            const currentMonth = new Date().getMonth() + 1;
            document.getElementById('calendarMonth').value = currentMonth;
            
            // Load calendar button event
            document.getElementById('loadCalendarBtn').addEventListener('click', loadCalendar);
        }
        
        // Load calendar data
        function loadCalendar() {
            const studentId = document.getElementById('calendarStudentSelect').value;
            const month = document.getElementById('calendarMonth').value;
            const year = document.getElementById('calendarYear').value;
            
            if (!studentId) {
                showMessage('Please select a student', 'error');
                return;
            }
            
            showLoading();
            
            fetch(`manage_attendance.php?action=fetch_calendar&student_id=${studentId}&month=${month}&year=${year}`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        displayCalendar(data);
                        document.getElementById('calendarContainer').style.display = 'block';
                        document.getElementById('calendarMessage').style.display = 'none';
                    } else {
                        showMessage(data.message || 'Error loading calendar', 'error');
                        document.getElementById('calendarContainer').style.display = 'none';
                        document.getElementById('calendarMessage').style.display = 'block';
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading calendar:', error);
                    showMessage('Error loading calendar. Please try again.', 'error');
                    document.getElementById('calendarContainer').style.display = 'none';
                    document.getElementById('calendarMessage').style.display = 'block';
                });
        }
        
        // Display calendar data
        function displayCalendar(data) {
            const calendar = data.calendar;
            const student = data.student;
            const stats = calendar.statistics;
            
            // Update calendar header
            document.getElementById('calendarTitle').textContent = 
                `${student.name} - ${calendar.month_name} ${calendar.year}`;
            
            // Update statistics
            document.getElementById('calendarPercentage').textContent = stats.attendance_percentage + '%';
            document.getElementById('calendarPresent').textContent = stats.attended_count;
            document.getElementById('calendarAbsent').textContent = stats.absent_count;
            
            // Generate calendar days
            const calendarDaysContainer = document.getElementById('calendarDays');
            calendarDaysContainer.innerHTML = '';
            
            calendar.calendar_days.forEach(day => {
                const dayDiv = document.createElement('div');
                dayDiv.className = 'calendar-day';
                
                if (day.is_empty) {
                    dayDiv.classList.add('empty');
                } else {
                    // Add day number
                    const dayNumber = document.createElement('div');
                    dayNumber.className = 'calendar-day-number';
                    dayNumber.textContent = day.day;
                    dayDiv.appendChild(dayNumber);
                    
                    // Add status styling and badge
                    if (day.status) {
                        dayDiv.classList.add(day.status);
                        
                        const statusBadge = document.createElement('div');
                        statusBadge.className = 'calendar-day-status';
                        statusBadge.textContent = day.status;
                        dayDiv.appendChild(statusBadge);
                        
                        // Add tooltip if there are notes
                        if (day.notes) {
                            const tooltip = document.createElement('div');
                            tooltip.className = 'calendar-day-tooltip';
                            tooltip.textContent = day.notes;
                            dayDiv.appendChild(tooltip);
                        }
                    } else {
                        dayDiv.classList.add('not-marked');
                    }
                }
                
                calendarDaysContainer.appendChild(dayDiv);
            });
        }
    </script>
</body>
</html>
