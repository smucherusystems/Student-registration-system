/**
 * Modal Utility
 * Provides reusable modal dialog functionality
 */

/**
 * Modal class for creating and managing modal dialogs
 */
class Modal {
    /**
     * Create a new Modal instance
     * @param {string} modalId - The ID of the modal element
     * @param {object} options - Optional configuration
     */
    constructor(modalId, options = {}) {
        this.modalId = modalId;
        this.modal = document.getElementById(modalId);
        
        // Configuration options
        this.config = {
            closeOnBackdropClick: options.closeOnBackdropClick !== false, // Default true
            closeOnEscape: options.closeOnEscape !== false, // Default true
            showCloseButton: options.showCloseButton !== false, // Default true
            onOpen: options.onOpen || null,
            onClose: options.onClose || null,
            animation: options.animation !== false, // Default true
            backdrop: options.backdrop !== false // Default true
        };
        
        // Create modal if it doesn't exist
        if (!this.modal) {
            this.createModal();
        }
        
        // Initialize modal
        this.init();
    }
    
    /**
     * Create modal HTML structure
     */
    createModal() {
        const modalHTML = `
            <div id="${this.modalId}" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
                <div class="modal-backdrop"></div>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title"></h3>
                            ${this.config.showCloseButton ? '<button class="modal-close" aria-label="Close">&times;</button>' : ''}
                        </div>
                        <div class="modal-body"></div>
                        <div class="modal-footer"></div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById(this.modalId);
    }
    
    /**
     * Initialize modal event listeners
     */
    init() {
        // Close button
        if (this.config.showCloseButton) {
            const closeBtn = this.modal.querySelector('.modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.close());
            }
        }
        
        // Backdrop click
        if (this.config.closeOnBackdropClick) {
            const backdrop = this.modal.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.addEventListener('click', () => this.close());
            }
        }
        
        // Escape key
        if (this.config.closeOnEscape) {
            this.escapeHandler = (e) => {
                if (e.key === 'Escape' && this.isOpen()) {
                    this.close();
                }
            };
            document.addEventListener('keydown', this.escapeHandler);
        }
    }
    
    /**
     * Open the modal
     * @param {object} content - Optional content to set before opening
     */
    open(content = {}) {
        // Set content if provided
        if (content.title) {
            this.setTitle(content.title);
        }
        if (content.body) {
            this.setBody(content.body);
        }
        if (content.footer) {
            this.setFooter(content.footer);
        }
        
        // Show modal
        this.modal.classList.add('modal-open');
        this.modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        
        // Focus first focusable element
        setTimeout(() => {
            const focusable = this.modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (focusable) {
                focusable.focus();
            }
        }, 100);
        
        // Call onOpen callback
        if (this.config.onOpen) {
            this.config.onOpen(this);
        }
    }
    
    /**
     * Close the modal
     */
    close() {
        this.modal.classList.remove('modal-open');
        this.modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        
        // Call onClose callback
        if (this.config.onClose) {
            this.config.onClose(this);
        }
    }
    
    /**
     * Check if modal is open
     * @returns {boolean}
     */
    isOpen() {
        return this.modal.classList.contains('modal-open');
    }
    
    /**
     * Toggle modal open/close
     */
    toggle() {
        if (this.isOpen()) {
            this.close();
        } else {
            this.open();
        }
    }
    
    /**
     * Set modal title
     * @param {string} title - The title text or HTML
     */
    setTitle(title) {
        const titleElement = this.modal.querySelector('.modal-title');
        if (titleElement) {
            titleElement.innerHTML = title;
        }
    }
    
    /**
     * Set modal body content
     * @param {string|HTMLElement} content - The body content
     */
    setBody(content) {
        const bodyElement = this.modal.querySelector('.modal-body');
        if (bodyElement) {
            if (typeof content === 'string') {
                bodyElement.innerHTML = content;
            } else if (content instanceof HTMLElement) {
                bodyElement.innerHTML = '';
                bodyElement.appendChild(content);
            }
        }
    }
    
    /**
     * Set modal footer content
     * @param {string|HTMLElement} content - The footer content
     */
    setFooter(content) {
        const footerElement = this.modal.querySelector('.modal-footer');
        if (footerElement) {
            if (typeof content === 'string') {
                footerElement.innerHTML = content;
            } else if (content instanceof HTMLElement) {
                footerElement.innerHTML = '';
                footerElement.appendChild(content);
            }
        }
    }
    
    /**
     * Get modal element
     * @returns {HTMLElement}
     */
    getElement() {
        return this.modal;
    }
    
    /**
     * Destroy the modal and remove event listeners
     */
    destroy() {
        if (this.escapeHandler) {
            document.removeEventListener('keydown', this.escapeHandler);
        }
        
        if (this.modal && this.modal.parentNode) {
            this.modal.parentNode.removeChild(this.modal);
        }
    }
}

/**
 * Create a confirmation modal
 * @param {object} options - Configuration options
 * @returns {Promise} Promise that resolves with true/false
 */
function confirmModal(options = {}) {
    return new Promise((resolve) => {
        const modalId = 'confirm-modal-' + Date.now();
        const title = options.title || 'Confirm Action';
        const message = options.message || 'Are you sure?';
        const confirmText = options.confirmText || 'Confirm';
        const cancelText = options.cancelText || 'Cancel';
        const confirmClass = options.confirmClass || 'btn-danger';
        
        // Create modal
        const modal = new Modal(modalId, {
            closeOnBackdropClick: false,
            onClose: () => {
                modal.destroy();
            }
        });
        
        // Set content
        modal.setTitle(title);
        modal.setBody(`<p>${message}</p>`);
        
        // Create footer buttons
        const footer = document.createElement('div');
        footer.className = 'modal-button-group';
        
        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn btn-secondary';
        cancelBtn.textContent = cancelText;
        cancelBtn.onclick = () => {
            modal.close();
            resolve(false);
        };
        
        const confirmBtn = document.createElement('button');
        confirmBtn.className = `btn ${confirmClass}`;
        confirmBtn.textContent = confirmText;
        confirmBtn.onclick = () => {
            modal.close();
            resolve(true);
        };
        
        footer.appendChild(cancelBtn);
        footer.appendChild(confirmBtn);
        modal.setFooter(footer);
        
        // Open modal
        modal.open();
    });
}

/**
 * Create an alert modal
 * @param {object} options - Configuration options
 */
function alertModal(options = {}) {
    return new Promise((resolve) => {
        const modalId = 'alert-modal-' + Date.now();
        const title = options.title || 'Alert';
        const message = options.message || '';
        const buttonText = options.buttonText || 'OK';
        const type = options.type || 'info'; // info, success, warning, error
        
        // Create modal
        const modal = new Modal(modalId, {
            onClose: () => {
                modal.destroy();
            }
        });
        
        // Set content with icon
        const icons = {
            info: 'ℹ',
            success: '✓',
            warning: '⚠',
            error: '✕'
        };
        
        modal.setTitle(title);
        modal.setBody(`
            <div class="alert-modal-content alert-${type}">
                <div class="alert-icon">${icons[type] || icons.info}</div>
                <p>${message}</p>
            </div>
        `);
        
        // Create footer button
        const footer = document.createElement('div');
        footer.className = 'modal-button-group';
        
        const okBtn = document.createElement('button');
        okBtn.className = 'btn btn-primary';
        okBtn.textContent = buttonText;
        okBtn.onclick = () => {
            modal.close();
            resolve(true);
        };
        
        footer.appendChild(okBtn);
        modal.setFooter(footer);
        
        // Open modal
        modal.open();
    });
}

/**
 * Create a prompt modal
 * @param {object} options - Configuration options
 * @returns {Promise} Promise that resolves with input value or null
 */
function promptModal(options = {}) {
    return new Promise((resolve) => {
        const modalId = 'prompt-modal-' + Date.now();
        const title = options.title || 'Input Required';
        const message = options.message || 'Please enter a value:';
        const defaultValue = options.defaultValue || '';
        const placeholder = options.placeholder || '';
        const confirmText = options.confirmText || 'Submit';
        const cancelText = options.cancelText || 'Cancel';
        
        // Create modal
        const modal = new Modal(modalId, {
            closeOnBackdropClick: false,
            onClose: () => {
                modal.destroy();
            }
        });
        
        // Set content
        modal.setTitle(title);
        modal.setBody(`
            <p>${message}</p>
            <input type="text" id="${modalId}-input" class="form-control" 
                   value="${defaultValue}" placeholder="${placeholder}" />
        `);
        
        // Create footer buttons
        const footer = document.createElement('div');
        footer.className = 'modal-button-group';
        
        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn btn-secondary';
        cancelBtn.textContent = cancelText;
        cancelBtn.onclick = () => {
            modal.close();
            resolve(null);
        };
        
        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'btn btn-primary';
        confirmBtn.textContent = confirmText;
        confirmBtn.onclick = () => {
            const input = document.getElementById(`${modalId}-input`);
            const value = input ? input.value : '';
            modal.close();
            resolve(value);
        };
        
        footer.appendChild(cancelBtn);
        footer.appendChild(confirmBtn);
        modal.setFooter(footer);
        
        // Open modal
        modal.open();
        
        // Focus input
        setTimeout(() => {
            const input = document.getElementById(`${modalId}-input`);
            if (input) {
                input.focus();
                input.select();
            }
        }, 100);
    });
}
