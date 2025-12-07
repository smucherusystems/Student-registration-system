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
    <title>Fee Management - Student Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay active">
        <div class="loading-spinner"></div>
        <p style="margin-top: 1rem; color: #7f8c8d; font-weight: 500;">Loading fee management...</p>
    </div>
    
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Fee Management</h1>
                <ul class="nav-menu">
                    <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                    <li><a href="registration.php" class="nav-link">Register Student</a></li>
                    <li><a href="grades.php" class="nav-link">Grades</a></li>
                    <li><a href="fees.php" class="nav-link active">Fees</a></li>
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
                <h2>Fee Management</h2>
                <p>Assign fees and record payments for students</p>
            </div>

            <!-- Outstanding Balance Summary -->
            <div id="balanceSummary" class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h2 style="color: white; border-bottom-color: rgba(255,255,255,0.3);">Outstanding Balance Summary</h2>
                <div class="stats-grid" style="margin-top: 1rem;">
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #f39c12;">
                        <div class="stat-icon">💰</div>
                        <div class="stat-content">
                            <h3 id="totalOutstanding">$0.00</h3>
                            <p>Total Outstanding</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #e74c3c;">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-content">
                            <h3 id="overdueCount">0</h3>
                            <p>Overdue Fees</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #3498db;">
                        <div class="stat-icon">📋</div>
                        <div class="stat-content">
                            <h3 id="pendingCount">0</h3>
                            <p>Pending Fees</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: rgba(255,255,255,0.95); border-left-color: #27ae60;">
                        <div class="stat-icon">✓</div>
                        <div class="stat-content">
                            <h3 id="paidCount">0</h3>
                            <p>Paid Fees</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Selector -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2>Select Student</h2>
                <div class="form-group">
                    <label for="studentSelect">Choose a student to manage fees:</label>
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

            <!-- Fee Assignment Form -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2>Assign New Fee</h2>
                <form id="assignFeeForm" class="registration-form">
                    <div class="form-group">
                        <label for="feeType">Fee Type *</label>
                        <input type="text" id="feeType" name="fee_type" class="form-input" placeholder="e.g., Tuition, Lab Fee, Library Fee" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="assignedAmount">Amount *</label>
                        <input type="number" id="assignedAmount" name="assigned_amount" class="form-input" placeholder="0.00" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="dueDate">Due Date *</label>
                        <input type="date" id="dueDate" name="due_date" class="form-input" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full">Assign Fee</button>
                </form>
            </div>

            <!-- Payment Recording Form -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2>Record Payment</h2>
                <form id="recordPaymentForm" class="registration-form">
                    <div class="form-group">
                        <label for="feeSelect">Select Fee *</label>
                        <select id="feeSelect" name="fee_id" class="form-input" required>
                            <option value="">-- Select a fee to pay --</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="paymentAmount">Payment Amount *</label>
                        <input type="number" id="paymentAmount" name="payment_amount" class="form-input" placeholder="0.00" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="paymentDate">Payment Date *</label>
                        <input type="date" id="paymentDate" name="payment_date" class="form-input" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-full">Record Payment</button>
                </form>
            </div>

            <!-- Fees Table -->
            <div class="card">
                <h2>Fee Records</h2>
                <div id="noStudentMessage" style="text-align: center; padding: 2rem; color: #7f8c8d;">
                    Please select a student to view their fee records.
                </div>
                <div id="feesTableContainer" style="display: none;">
                    <div id="studentFeeBalance" class="fee-summary" style="margin-bottom: 1.5rem;">
                        <p><strong>Student:</strong> <span id="selectedStudentName">-</span></p>
                        <p><strong>Total Assigned:</strong> $<span id="studentTotalAssigned">0.00</span></p>
                        <p><strong>Total Paid:</strong> $<span id="studentTotalPaid">0.00</span></p>
                        <p><strong>Outstanding Balance:</strong> <span style="font-size: 1.2rem; font-weight: bold; color: #e74c3c;">$<span id="studentOutstanding">0.00</span></span></p>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="students-table" id="feesTable">
                            <thead>
                                <tr>
                                    <th>Fee Type</th>
                                    <th>Assigned Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Balance</th>
                                    <th>Due Date</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="feesTableBody">
                                <!-- Fee records will be populated here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="noFeesMessage" style="display: none; text-align: center; padding: 2rem; color: #7f8c8d; background-color: #f8f9fa; border-radius: 8px; margin-top: 1rem;">
                        No fee records found for this student.
                    </div>
                </div>
            </div>
            
            <!-- Payment History Timeline -->
            <div class="card" style="margin-top: 2rem;">
                <h2>Payment History Timeline</h2>
                <div id="noStudentMessageTimeline" style="text-align: center; padding: 2rem; color: #7f8c8d;">
                    Please select a student to view their payment history.
                </div>
                <div id="paymentTimelineContainer" style="display: none;">
                    <div id="paymentTimeline" class="payment-timeline">
                        <!-- Timeline items will be populated here -->
                    </div>
                    
                    <div id="noPaymentsMessage" style="display: none; text-align: center; padding: 2rem; color: #7f8c8d; background-color: #f8f9fa; border-radius: 8px; margin-top: 1rem;">
                        No payment history found for this student.
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
        let currentStudentId = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            hideLoading();
            loadFeeSummary();
            
            // Set default payment date to today
            document.getElementById('paymentDate').valueAsDate = new Date();
            
            // Student selector change event
            document.getElementById('studentSelect').addEventListener('change', handleStudentChange);
            
            // Form submissions
            document.getElementById('assignFeeForm').addEventListener('submit', handleAssignFee);
            document.getElementById('recordPaymentForm').addEventListener('submit', handleRecordPayment);
            
            // Real-time validation for payment amount
            setupPaymentValidation();
            
            // Real-time validation for assigned amount
            setupAssignedAmountValidation();
        });
        
        // Setup real-time payment validation
        function setupPaymentValidation() {
            const paymentAmountInput = document.getElementById('paymentAmount');
            const feeSelect = document.getElementById('feeSelect');
            
            paymentAmountInput.addEventListener('input', function() {
                const paymentAmount = parseFloat(this.value);
                const selectedOption = feeSelect.options[feeSelect.selectedIndex];
                
                if (selectedOption && selectedOption.dataset.balance && paymentAmount > 0) {
                    const outstandingBalance = parseFloat(selectedOption.dataset.balance);
                    
                    if (paymentAmount > outstandingBalance) {
                        this.style.borderColor = '#e74c3c';
                        this.style.backgroundColor = '#fee';
                    } else {
                        this.style.borderColor = '#27ae60';
                        this.style.backgroundColor = '#efe';
                    }
                } else {
                    this.style.borderColor = '';
                    this.style.backgroundColor = '';
                }
            });
            
            // Reset validation styling when fee selection changes
            feeSelect.addEventListener('change', function() {
                paymentAmountInput.style.borderColor = '';
                paymentAmountInput.style.backgroundColor = '';
            });
        }
        
        // Setup real-time assigned amount validation
        function setupAssignedAmountValidation() {
            const assignedAmountInput = document.getElementById('assignedAmount');
            
            assignedAmountInput.addEventListener('input', function() {
                const amount = parseFloat(this.value);
                
                if (isNaN(amount) || amount <= 0) {
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.style.borderColor = '#27ae60';
                }
            });
            
            assignedAmountInput.addEventListener('blur', function() {
                if (this.value === '' || parseFloat(this.value) > 0) {
                    this.style.borderColor = '';
                }
            });
        }

        // Handle student selection change
        function handleStudentChange(e) {
            currentStudentId = e.target.value;
            
            if (currentStudentId) {
                loadStudentFees(currentStudentId);
                document.getElementById('noStudentMessage').style.display = 'none';
                document.getElementById('feesTableContainer').style.display = 'block';
                document.getElementById('noStudentMessageTimeline').style.display = 'none';
                document.getElementById('paymentTimelineContainer').style.display = 'block';
            } else {
                document.getElementById('noStudentMessage').style.display = 'block';
                document.getElementById('feesTableContainer').style.display = 'none';
                document.getElementById('noStudentMessageTimeline').style.display = 'block';
                document.getElementById('paymentTimelineContainer').style.display = 'none';
                document.getElementById('feeSelect').innerHTML = '<option value="">-- Select a fee to pay --</option>';
            }
        }

        // Load fee summary for dashboard
        function loadFeeSummary() {
            fetch('manage_fees.php?action=fetch_summary')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const summary = data.summary;
                        document.getElementById('totalOutstanding').textContent = '$' + summary.total_outstanding.toFixed(2);
                        document.getElementById('overdueCount').textContent = summary.overdue_count;
                        document.getElementById('pendingCount').textContent = summary.pending_count;
                        document.getElementById('paidCount').textContent = summary.paid_count;
                    }
                })
                .catch(error => {
                    console.error('Error loading fee summary:', error);
                });
        }

        // Load fees for selected student
        function loadStudentFees(studentId) {
            showLoading();
            
            fetch(`manage_fees.php?action=fetch&student_id=${studentId}`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        displayStudentFees(data);
                        populateFeeSelect(data.fees);
                        loadPaymentHistory(studentId);
                    } else {
                        showNotification(data.message || 'Error loading fees', 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading fees:', error);
                    showNotification('Error loading fees. Please try again.', 'error');
                });
        }
        
        // Load payment history timeline for selected student
        function loadPaymentHistory(studentId) {
            fetch(`manage_fees.php?action=fetch_payment_history&student_id=${studentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayPaymentTimeline(data.timeline);
                    } else {
                        console.error('Error loading payment history:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading payment history:', error);
                });
        }
        
        // Display payment timeline
        function displayPaymentTimeline(timeline) {
            const timelineContainer = document.getElementById('paymentTimeline');
            const noPaymentsMessage = document.getElementById('noPaymentsMessage');
            
            timelineContainer.innerHTML = '';
            
            if (timeline.length === 0) {
                noPaymentsMessage.style.display = 'block';
                return;
            }
            
            noPaymentsMessage.style.display = 'none';
            
            timeline.forEach(event => {
                const timelineItem = document.createElement('div');
                timelineItem.className = 'timeline-item';
                
                let markerIcon = '';
                let amountHtml = '';
                let detailsHtml = '';
                let badgeHtml = '';
                
                if (event.type === 'assignment') {
                    markerIcon = '📋';
                    amountHtml = `<div class="timeline-amount assignment">$${event.amount.toFixed(2)}</div>`;
                    detailsHtml = `
                        <p><strong>Fee Type:</strong> ${escapeHtml(event.fee_type)}</p>
                        <p><strong>Due Date:</strong> ${formatDate(event.due_date)}</p>
                    `;
                } else if (event.type === 'payment') {
                    markerIcon = '💰';
                    amountHtml = `<div class="timeline-amount payment">$${event.amount.toFixed(2)}</div>`;
                    detailsHtml = `
                        <p><strong>Fee Type:</strong> ${escapeHtml(event.fee_type)}</p>
                    `;
                    if (event.status) {
                        badgeHtml = `<span class="timeline-badge ${event.status}">${event.status.toUpperCase()}</span>`;
                    }
                } else if (event.type === 'status_change') {
                    markerIcon = '🔄';
                    detailsHtml = `
                        <p><strong>Fee Type:</strong> ${escapeHtml(event.fee_type)}</p>
                    `;
                    if (event.status) {
                        badgeHtml = `<span class="timeline-badge ${event.status}">${event.status.toUpperCase()}</span>`;
                    }
                }
                
                timelineItem.innerHTML = `
                    <div class="timeline-marker ${event.type}">${markerIcon}</div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h3 class="timeline-title">${escapeHtml(event.description)}</h3>
                            <span class="timeline-date">${formatDateTime(event.date)}</span>
                        </div>
                        <div class="timeline-details">
                            ${detailsHtml}
                            ${amountHtml}
                            ${badgeHtml}
                        </div>
                    </div>
                `;
                
                timelineContainer.appendChild(timelineItem);
            });
        }

        // Display student fees in table
        function displayStudentFees(data) {
            const student = data.student;
            const fees = data.fees;
            const summary = data.summary;
            
            // Update student info
            document.getElementById('selectedStudentName').textContent = student.name;
            document.getElementById('studentTotalAssigned').textContent = summary.total_assigned.toFixed(2);
            document.getElementById('studentTotalPaid').textContent = summary.total_paid.toFixed(2);
            document.getElementById('studentOutstanding').textContent = summary.total_outstanding.toFixed(2);
            
            // Update table
            const tbody = document.getElementById('feesTableBody');
            tbody.innerHTML = '';
            
            if (fees.length === 0) {
                document.getElementById('feesTable').style.display = 'none';
                document.getElementById('noFeesMessage').style.display = 'block';
                return;
            }
            
            document.getElementById('feesTable').style.display = 'table';
            document.getElementById('noFeesMessage').style.display = 'none';
            
            fees.forEach(fee => {
                const row = document.createElement('tr');
                const balance = parseFloat(fee.outstanding_balance);
                const statusClass = `status-${fee.status}`;
                
                row.innerHTML = `
                    <td>${escapeHtml(fee.fee_type)}</td>
                    <td>$${parseFloat(fee.assigned_amount).toFixed(2)}</td>
                    <td>$${parseFloat(fee.paid_amount).toFixed(2)}</td>
                    <td style="font-weight: bold; color: ${balance > 0 ? '#e74c3c' : '#27ae60'};">
                        $${balance.toFixed(2)}
                    </td>
                    <td>${formatDate(fee.due_date)}</td>
                    <td>${fee.payment_date ? formatDate(fee.payment_date) : '-'}</td>
                    <td><span class="status-badge ${statusClass}">${fee.status}</span></td>
                `;
                
                tbody.appendChild(row);
            });
        }

        // Populate fee select dropdown for payment
        function populateFeeSelect(fees) {
            const feeSelect = document.getElementById('feeSelect');
            feeSelect.innerHTML = '<option value="">-- Select a fee to pay --</option>';
            
            // Only show unpaid or partially paid fees
            const unpaidFees = fees.filter(fee => fee.status !== 'paid');
            
            unpaidFees.forEach(fee => {
                const balance = parseFloat(fee.outstanding_balance);
                const option = document.createElement('option');
                option.value = fee.id;
                option.textContent = `${fee.fee_type} - Balance: $${balance.toFixed(2)} (Due: ${formatDate(fee.due_date)})`;
                option.dataset.balance = balance;
                feeSelect.appendChild(option);
            });
            
            // Update payment amount when fee is selected
            feeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.dataset.balance) {
                    document.getElementById('paymentAmount').value = selectedOption.dataset.balance;
                }
            });
        }

        // Handle assign fee form submission
        function handleAssignFee(e) {
            e.preventDefault();
            
            if (!currentStudentId) {
                showNotification('Please select a student first', 'error');
                return;
            }
            
            // Client-side validation
            const feeType = document.getElementById('feeType').value.trim();
            const assignedAmount = parseFloat(document.getElementById('assignedAmount').value);
            const dueDate = document.getElementById('dueDate').value;
            
            // Validate fee type
            if (!feeType || feeType.length === 0) {
                showNotification('Fee type is required', 'error');
                return;
            }
            
            if (feeType.length > 100) {
                showNotification('Fee type must be less than 100 characters', 'error');
                return;
            }
            
            // Validate assigned amount
            if (isNaN(assignedAmount) || assignedAmount <= 0) {
                showNotification('Assigned amount must be a positive number', 'error');
                return;
            }
            
            // Validate due date
            if (!dueDate) {
                showNotification('Due date is required', 'error');
                return;
            }
            
            const formData = new FormData(e.target);
            formData.append('action', 'assign');
            formData.append('student_id', currentStudentId);
            
            showLoading();
            
            fetch('manage_fees.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    showNotification('Fee assigned successfully!', 'success');
                    e.target.reset();
                    loadStudentFees(currentStudentId);
                    loadFeeSummary();
                } else {
                    showNotification(data.message || 'Error assigning fee', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error assigning fee:', error);
                showNotification('Error assigning fee. Please try again.', 'error');
            });
        }

        // Handle record payment form submission
        function handleRecordPayment(e) {
            e.preventDefault();
            
            if (!currentStudentId) {
                showNotification('Please select a student first', 'error');
                return;
            }
            
            // Client-side validation to prevent overpayment
            const feeSelect = document.getElementById('feeSelect');
            const paymentAmount = parseFloat(document.getElementById('paymentAmount').value);
            const selectedOption = feeSelect.options[feeSelect.selectedIndex];
            
            if (selectedOption && selectedOption.dataset.balance) {
                const outstandingBalance = parseFloat(selectedOption.dataset.balance);
                
                if (paymentAmount > outstandingBalance) {
                    showNotification(
                        `Payment amount ($${paymentAmount.toFixed(2)}) exceeds outstanding balance ($${outstandingBalance.toFixed(2)}). Please enter a valid amount.`,
                        'error'
                    );
                    return;
                }
            }
            
            // Validate payment amount is positive
            if (paymentAmount <= 0) {
                showNotification('Payment amount must be greater than zero', 'error');
                return;
            }
            
            const formData = new FormData(e.target);
            formData.append('action', 'record_payment');
            
            showLoading();
            
            fetch('manage_fees.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    showNotification('Payment recorded successfully!', 'success');
                    e.target.reset();
                    document.getElementById('paymentDate').valueAsDate = new Date();
                    loadStudentFees(currentStudentId);
                    loadFeeSummary();
                } else {
                    showNotification(data.message || 'Error recording payment', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error recording payment:', error);
                showNotification('Error recording payment. Please try again.', 'error');
            });
        }

        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type === 'success' ? 'success-notification' : 'error-notification'}`;
            notification.textContent = message;
            
            const container = document.querySelector('.container');
            container.insertBefore(notification, container.firstChild);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        }

        // Format date helper
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        
        // Format date and time helper
        function formatDateTime(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Escape HTML helper
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
