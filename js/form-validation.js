/**
 * Form Validation Utility
 * Provides comprehensive form validation with real-time feedback
 */

/**
 * Validation rules
 */
const ValidationRules = {
    required: (value) => {
        return value.trim() !== '';
    },
    
    email: (value) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(value);
    },
    
    phone: (value) => {
        const phoneRegex = /^[\d\s\-\+\(\)]+$/;
        return phoneRegex.test(value) && value.replace(/\D/g, '').length >= 10;
    },
    
    number: (value) => {
        return !isNaN(value) && value.trim() !== '';
    },
    
    integer: (value) => {
        return Number.isInteger(Number(value));
    },
    
    positive: (value) => {
        return Number(value) > 0;
    },
    
    nonNegative: (value) => {
        return Number(value) >= 0;
    },
    
    min: (value, min) => {
        return Number(value) >= Number(min);
    },
    
    max: (value, max) => {
        return Number(value) <= Number(max);
    },
    
    minLength: (value, length) => {
        return value.length >= Number(length);
    },
    
    maxLength: (value, length) => {
        return value.length <= Number(length);
    },
    
    pattern: (value, pattern) => {
        const regex = new RegExp(pattern);
        return regex.test(value);
    },
    
    date: (value) => {
        const date = new Date(value);
        return date instanceof Date && !isNaN(date);
    },
    
    futureDate: (value) => {
        const date = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return date >= today;
    },
    
    pastDate: (value) => {
        const date = new Date(value);
        const today = new Date();
        today.setHours(23, 59, 59, 999);
        return date <= today;
    },
    
    match: (value, matchValue) => {
        return value === matchValue;
    },
    
    url: (value) => {
        try {
            new URL(value);
            return true;
        } catch {
            return false;
        }
    }
};

/**
 * Default error messages
 */
const DefaultMessages = {
    required: 'This field is required',
    email: 'Please enter a valid email address',
    phone: 'Please enter a valid phone number',
    number: 'Please enter a valid number',
    integer: 'Please enter a whole number',
    positive: 'Please enter a positive number',
    nonNegative: 'Please enter a non-negative number',
    min: 'Value must be at least {min}',
    max: 'Value must be at most {max}',
    minLength: 'Must be at least {length} characters',
    maxLength: 'Must be at most {length} characters',
    pattern: 'Invalid format',
    date: 'Please enter a valid date',
    futureDate: 'Date must be in the future',
    pastDate: 'Date must be in the past',
    match: 'Values do not match',
    url: 'Please enter a valid URL'
};

/**
 * FormValidator class
 */
class FormValidator {
    /**
     * Create a new FormValidator instance
     * @param {string|HTMLFormElement} form - Form element or selector
     * @param {object} options - Configuration options
     */
    constructor(form, options = {}) {
        this.form = typeof form === 'string' ? document.querySelector(form) : form;
        
        if (!this.form) {
            console.error('Form not found');
            return;
        }
        
        this.config = {
            realTime: options.realTime !== false, // Default true
            validateOnBlur: options.validateOnBlur !== false, // Default true
            validateOnInput: options.validateOnInput !== false, // Default true
            showSuccessState: options.showSuccessState !== false, // Default true
            preventSubmit: options.preventSubmit !== false, // Default true
            scrollToError: options.scrollToError !== false, // Default true
            focusFirstError: options.focusFirstError !== false, // Default true
            customMessages: options.customMessages || {},
            onValidate: options.onValidate || null,
            onSubmit: options.onSubmit || null
        };
        
        this.fields = new Map();
        this.errors = new Map();
        
        this.init();
    }
    
    /**
     * Initialize form validation
     */
    init() {
        // Find all fields with validation rules
        const inputs = this.form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            const rules = this.getValidationRules(input);
            if (rules.length > 0) {
                this.fields.set(input.name || input.id, {
                    element: input,
                    rules: rules
                });
                
                // Add event listeners
                if (this.config.validateOnBlur) {
                    input.addEventListener('blur', () => this.validateField(input));
                }
                
                if (this.config.validateOnInput) {
                    input.addEventListener('input', () => {
                        if (this.errors.has(input.name || input.id)) {
                            this.validateField(input);
                        }
                    });
                }
            }
        });
        
        // Handle form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }
    
    /**
     * Get validation rules from element attributes
     * @param {HTMLElement} element - Input element
     * @returns {Array} Array of validation rules
     */
    getValidationRules(element) {
        const rules = [];
        
        // Required
        if (element.hasAttribute('required') || element.dataset.required === 'true') {
            rules.push({ type: 'required' });
        }
        
        // Type-based validation
        if (element.type === 'email') {
            rules.push({ type: 'email' });
        }
        
        if (element.type === 'tel' || element.dataset.validate === 'phone') {
            rules.push({ type: 'phone' });
        }
        
        if (element.type === 'number' || element.dataset.validate === 'number') {
            rules.push({ type: 'number' });
        }
        
        if (element.type === 'url') {
            rules.push({ type: 'url' });
        }
        
        if (element.type === 'date' || element.dataset.validate === 'date') {
            rules.push({ type: 'date' });
        }
        
        // Min/Max
        if (element.hasAttribute('min')) {
            rules.push({ type: 'min', value: element.getAttribute('min') });
        }
        
        if (element.hasAttribute('max')) {
            rules.push({ type: 'max', value: element.getAttribute('max') });
        }
        
        // MinLength/MaxLength
        if (element.hasAttribute('minlength')) {
            rules.push({ type: 'minLength', value: element.getAttribute('minlength') });
        }
        
        if (element.hasAttribute('maxlength')) {
            rules.push({ type: 'maxLength', value: element.getAttribute('maxlength') });
        }
        
        // Pattern
        if (element.hasAttribute('pattern')) {
            rules.push({ type: 'pattern', value: element.getAttribute('pattern') });
        }
        
        // Custom data attributes
        if (element.dataset.validate) {
            const validateTypes = element.dataset.validate.split(',');
            validateTypes.forEach(type => {
                type = type.trim();
                if (ValidationRules[type] && !rules.find(r => r.type === type)) {
                    rules.push({ type: type });
                }
            });
        }
        
        // Match field
        if (element.dataset.match) {
            rules.push({ type: 'match', value: element.dataset.match });
        }
        
        return rules;
    }
    
    /**
     * Validate a single field
     * @param {HTMLElement} element - Input element
     * @returns {boolean} Whether field is valid
     */
    validateField(element) {
        const fieldName = element.name || element.id;
        const fieldData = this.fields.get(fieldName);
        
        if (!fieldData) return true;
        
        const value = element.value;
        let isValid = true;
        let errorMessage = '';
        
        // Check each rule
        for (const rule of fieldData.rules) {
            const validator = ValidationRules[rule.type];
            
            if (!validator) continue;
            
            // Skip non-required empty fields
            if (value.trim() === '' && rule.type !== 'required') {
                continue;
            }
            
            let valid = false;
            
            // Apply validation
            if (rule.type === 'match') {
                const matchElement = this.form.querySelector(`[name="${rule.value}"], #${rule.value}`);
                valid = validator(value, matchElement ? matchElement.value : '');
            } else if (rule.value !== undefined) {
                valid = validator(value, rule.value);
            } else {
                valid = validator(value);
            }
            
            if (!valid) {
                isValid = false;
                errorMessage = this.getErrorMessage(rule, element);
                break;
            }
        }
        
        // Update UI
        if (isValid) {
            this.clearFieldError(element);
            if (this.config.showSuccessState && value.trim() !== '') {
                this.showFieldSuccess(element);
            }
            this.errors.delete(fieldName);
        } else {
            this.showFieldError(element, errorMessage);
            this.errors.set(fieldName, errorMessage);
        }
        
        return isValid;
    }
    
    /**
     * Get error message for a rule
     * @param {object} rule - Validation rule
     * @param {HTMLElement} element - Input element
     * @returns {string} Error message
     */
    getErrorMessage(rule, element) {
        const fieldName = element.name || element.id;
        
        // Check custom messages
        if (this.config.customMessages[fieldName] && this.config.customMessages[fieldName][rule.type]) {
            return this.config.customMessages[fieldName][rule.type];
        }
        
        // Check data attribute
        if (element.dataset[`error${rule.type.charAt(0).toUpperCase() + rule.type.slice(1)}`]) {
            return element.dataset[`error${rule.type.charAt(0).toUpperCase() + rule.type.slice(1)}`];
        }
        
        // Use default message
        let message = DefaultMessages[rule.type] || 'Invalid value';
        
        // Replace placeholders
        if (rule.value !== undefined) {
            message = message.replace(`{${rule.type}}`, rule.value);
            message = message.replace('{min}', rule.value);
            message = message.replace('{max}', rule.value);
            message = message.replace('{length}', rule.value);
        }
        
        return message;
    }
    
    /**
     * Show field error
     * @param {HTMLElement} element - Input element
     * @param {string} message - Error message
     */
    showFieldError(element, message) {
        element.classList.add('invalid');
        element.classList.remove('valid');
        
        // Find or create error message element
        let errorElement = element.parentElement.querySelector('.form-validation-message.error');
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-validation-message error';
            element.parentElement.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'flex';
    }
    
    /**
     * Clear field error
     * @param {HTMLElement} element - Input element
     */
    clearFieldError(element) {
        element.classList.remove('invalid');
        
        const errorElement = element.parentElement.querySelector('.form-validation-message.error');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
    
    /**
     * Show field success state
     * @param {HTMLElement} element - Input element
     */
    showFieldSuccess(element) {
        element.classList.add('valid');
    }
    
    /**
     * Validate entire form
     * @returns {boolean} Whether form is valid
     */
    validateForm() {
        let isValid = true;
        let firstErrorElement = null;
        
        this.fields.forEach((fieldData) => {
            const valid = this.validateField(fieldData.element);
            
            if (!valid) {
                isValid = false;
                if (!firstErrorElement) {
                    firstErrorElement = fieldData.element;
                }
            }
        });
        
        // Focus first error
        if (!isValid && firstErrorElement && this.config.focusFirstError) {
            firstErrorElement.focus();
            
            if (this.config.scrollToError) {
                firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        // Call validation callback
        if (this.config.onValidate) {
            this.config.onValidate(isValid, this.errors);
        }
        
        return isValid;
    }
    
    /**
     * Handle form submission
     * @param {Event} e - Submit event
     */
    handleSubmit(e) {
        const isValid = this.validateForm();
        
        if (!isValid && this.config.preventSubmit) {
            e.preventDefault();
            return false;
        }
        
        if (this.config.onSubmit) {
            e.preventDefault();
            this.config.onSubmit(e, isValid);
        }
    }
    
    /**
     * Reset form validation
     */
    reset() {
        this.errors.clear();
        
        this.fields.forEach((fieldData) => {
            fieldData.element.classList.remove('valid', 'invalid');
            this.clearFieldError(fieldData.element);
        });
    }
    
    /**
     * Get all errors
     * @returns {Map} Map of field errors
     */
    getErrors() {
        return this.errors;
    }
    
    /**
     * Check if form is valid
     * @returns {boolean}
     */
    isValid() {
        return this.errors.size === 0;
    }
    
    /**
     * Add custom validation rule
     * @param {string} name - Rule name
     * @param {Function} validator - Validator function
     * @param {string} message - Error message
     */
    static addRule(name, validator, message) {
        ValidationRules[name] = validator;
        DefaultMessages[name] = message;
    }
}

/**
 * Simple form validation function (for quick use)
 * @param {string|HTMLFormElement} form - Form element or selector
 * @param {object} options - Configuration options
 * @returns {FormValidator} FormValidator instance
 */
function validateForm(form, options = {}) {
    return new FormValidator(form, options);
}
