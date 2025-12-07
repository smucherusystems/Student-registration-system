// Login Form Handling
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            validateLoginForm();
        });
    }
});

// Login form validation and submission
function validateLoginForm() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    let isValid = true;

    // Reset error messages
    document.getElementById('usernameError').style.display = 'none';
    document.getElementById('passwordError').style.display = 'none';

    // Validate username
    if (username === '') {
        showError('usernameError', 'Username is required');
        isValid = false;
    }

    // Validate password
    if (password === '') {
        showError('passwordError', 'Password is required');
        isValid = false;
    }

    if (isValid) {
        submitLoginForm(username, password);
    }
}

// Submit login form via AJAX
function submitLoginForm(username, password) {
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);

    const submitButton = document.querySelector('#loginForm button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Logging in...';
    submitButton.disabled = true;

    fetch('process_login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('message', data.message, 'success');
            // Redirect to dashboard after successful login
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1000);
        } else {
            showMessage('message', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('message', 'An error occurred during login', 'error');
    })
    .finally(() => {
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    });
}

// Helper functions (same as in validation.js)
function showError(errorElementId, message) {
    const errorElement = document.getElementById(errorElementId);
    errorElement.textContent = message;
    errorElement.style.display = 'block';
}

function showMessage(messageDivId, message, type) {
    const messageDiv = document.getElementById(messageDivId);
    messageDiv.textContent = message;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
}