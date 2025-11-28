// Student Registration Form Validation
document.addEventListener('DOMContentLoaded', function() {
    const registrationForm = document.getElementById('registrationForm');
    
    if (registrationForm) {
        // Add event listener for form submission
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Validate all fields
            const isValid = validateForm();
            
            if (isValid) {
                // If validation passes, submit the form via AJAX
                submitForm();
            }
        });

        // Real-time validation for each field
        setupRealTimeValidation();
    }
});

// Function to validate the entire form
function validateForm() {
    let isValid = true;

    // Validate Name
    if (!validateName()) isValid = false;
    
    // Validate Email
    if (!validateEmail()) isValid = false;
    
    // Validate Phone
    if (!validatePhone()) isValid = false;
    
    // Validate Course
    if (!validateCourse()) isValid = false;
    
    // Validate Gender
    if (!validateGender()) isValid = false;

    return isValid;
}

// Real-time validation setup
function setupRealTimeValidation() {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const courseSelect = document.getElementById('course');
    const genderInputs = document.querySelectorAll('input[name="gender"]');

    // Name validation on input change
    nameInput.addEventListener('input', validateName);
    nameInput.addEventListener('blur', validateName);

    // Email validation on input change
    emailInput.addEventListener('input', validateEmail);
    emailInput.addEventListener('blur', validateEmail);

    // Phone validation on input change
    phoneInput.addEventListener('input', validatePhone);
    phoneInput.addEventListener('blur', validatePhone);

    // Course validation on change
    courseSelect.addEventListener('change', validateCourse);

    // Gender validation on change
    genderInputs.forEach(input => {
        input.addEventListener('change', validateGender);
    });
}

// Name validation function
function validateName() {
    const nameInput = document.getElementById('name');
    const nameError = document.getElementById('nameError');
    const name = nameInput.value.trim();

    if (name === '') {
        showError(nameError, 'Name is required');
        return false;
    } else if (name.length < 2) {
        showError(nameError, 'Name must be at least 2 characters long');
        return false;
    } else if (!/^[a-zA-Z\s]+$/.test(name)) {
        showError(nameError, 'Name can only contain letters and spaces');
        return false;
    } else {
        hideError(nameError);
        return true;
    }
}

// Email validation function
function validateEmail() {
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    const email = emailInput.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email === '') {
        showError(emailError, 'Email is required');
        return false;
    } else if (!emailRegex.test(email)) {
        showError(emailError, 'Please enter a valid email address');
        return false;
    } else {
        hideError(emailError);
        return true;
    }
}

// Phone validation function
function validatePhone() {
    const phoneInput = document.getElementById('phone');
    const phoneError = document.getElementById('phoneError');
    const phone = phoneInput.value.trim();
    const phoneRegex = /^[\d\s\-\(\)]+$/;

    if (phone === '') {
        showError(phoneError, 'Phone number is required');
        return false;
    } else if (!phoneRegex.test(phone)) {
        showError(phoneError, 'Please enter a valid phone number');
        return false;
    } else if (phone.replace(/\D/g, '').length < 10) {
        showError(phoneError, 'Phone number must be at least 10 digits');
        return false;
    } else {
        hideError(phoneError);
        return true;
    }
}

// Course validation function
function validateCourse() {
    const courseSelect = document.getElementById('course');
    const courseError = document.getElementById('courseError');
    const course = courseSelect.value;

    if (course === '') {
        showError(courseError, 'Please select a course');
        return false;
    } else {
        hideError(courseError);
        return true;
    }
}

// Gender validation function
function validateGender() {
    const genderError = document.getElementById('genderError');
    const genderSelected = document.querySelector('input[name="gender"]:checked');

    if (!genderSelected) {
        showError(genderError, 'Please select a gender');
        return false;
    } else {
        hideError(genderError);
        return true;
    }
}

// Helper function to show error messages
function showError(errorElement, message) {
    errorElement.textContent = message;
    errorElement.style.display = 'block';
}

// Helper function to hide error messages
function hideError(errorElement) {
    errorElement.textContent = '';
    errorElement.style.display = 'none';
}

// Function to submit form via AJAX
function submitForm() {
    const form = document.getElementById('registrationForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('message');

    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Registering...';
    submitButton.disabled = true;

    // Send AJAX request to PHP backend
    fetch('process_registration.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showMessage(messageDiv, data.message, 'success');
            // Reset form
            form.reset();
        } else {
            // Show error message
            showMessage(messageDiv, data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage(messageDiv, 'An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    });
}

// Function to display messages to user
function showMessage(messageDiv, message, type) {
    messageDiv.textContent = message;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';

    // Hide message after 5 seconds
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 5000);
}