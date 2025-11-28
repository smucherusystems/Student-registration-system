// Main JavaScript file for additional functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Student Management System loaded successfully');
    
    // Initialize any additional functionality here
    initializeSystem();
});

// System initialization function
function initializeSystem() {
    // Add any global event listeners or initialization code here
    
    // Example: Add confirmation for delete actions
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this student record?')) {
                e.preventDefault();
            }
        });
    });
}

// Utility function for formatting phone numbers
function formatPhoneNumber(phone) {
    // Remove all non-digit characters
    const cleaned = phone.replace(/\D/g, '');
    
    // Check if the number looks like a US phone number
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    if (match) {
        return '(' + match[1] + ') ' + match[2] + '-' + match[3];
    }
    
    return phone; // Return original if format doesn't match
}

// Function to handle logout
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

// Export functions for use in other modules (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { formatPhoneNumber, handleLogout };
}