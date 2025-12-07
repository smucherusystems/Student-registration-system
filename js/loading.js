/**
 * Loading States and Transitions Utility
 * Provides functions to show/hide loading indicators and skeleton loaders
 * 
 * Features:
 * - Full-page loading overlay with spinner
 * - Skeleton loaders for cards and tables
 * - Button and form loading states
 * - Smooth fade-in animations
 * - AJAX wrapper with automatic loading states
 * 
 * Usage:
 * 1. Add loading overlay HTML to your page:
 *    <div id="loadingOverlay" class="loading-overlay active">
 *        <div class="loading-spinner"></div>
 *    </div>
 * 
 * 2. Call hideLoading() when page is ready
 * 3. Use showLoading()/hideLoading() for async operations
 * 4. Use skeleton loaders for better UX during data fetching
 */

// Initialize loading overlay on page load
(function() {
    // Show loading overlay immediately when script loads
    window.addEventListener('load', function() {
        // Page is fully loaded, hide the overlay
        setTimeout(hideLoading, 100);
    });
})();

// Show loading overlay
function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.add('active');
    }
}

// Hide loading overlay
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// Show skeleton loaders for stat cards
function showStatSkeletons() {
    const statsGrid = document.querySelector('.stats-grid');
    if (!statsGrid) return;
    
    statsGrid.innerHTML = `
        <div class="skeleton-card">
            <div class="skeleton-stat">
                <div class="skeleton skeleton-icon"></div>
                <div class="skeleton-content">
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
            </div>
        </div>
        <div class="skeleton-card">
            <div class="skeleton-stat">
                <div class="skeleton skeleton-icon"></div>
                <div class="skeleton-content">
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
            </div>
        </div>
        <div class="skeleton-card">
            <div class="skeleton-stat">
                <div class="skeleton skeleton-icon"></div>
                <div class="skeleton-content">
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
            </div>
        </div>
        <div class="skeleton-card">
            <div class="skeleton-stat">
                <div class="skeleton skeleton-icon"></div>
                <div class="skeleton-content">
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
            </div>
        </div>
    `;
}

// Show skeleton loaders for action cards
function showActionSkeletons() {
    const actionsGrid = document.querySelector('.quick-actions-grid');
    if (!actionsGrid) return;
    
    actionsGrid.innerHTML = `
        <div class="skeleton-action-card">
            <div class="skeleton skeleton-action-icon"></div>
            <div class="skeleton skeleton-action-title"></div>
            <div class="skeleton skeleton-action-text"></div>
        </div>
        <div class="skeleton-action-card">
            <div class="skeleton skeleton-action-icon"></div>
            <div class="skeleton skeleton-action-title"></div>
            <div class="skeleton skeleton-action-text"></div>
        </div>
        <div class="skeleton-action-card">
            <div class="skeleton skeleton-action-icon"></div>
            <div class="skeleton skeleton-action-title"></div>
            <div class="skeleton skeleton-action-text"></div>
        </div>
        <div class="skeleton-action-card">
            <div class="skeleton skeleton-action-icon"></div>
            <div class="skeleton skeleton-action-title"></div>
            <div class="skeleton skeleton-action-text"></div>
        </div>
    `;
}

// Simulate loading for demonstration (can be used for AJAX operations)
function simulateDataFetch(callback, duration = 1000) {
    showLoading();
    setTimeout(() => {
        hideLoading();
        if (callback) callback();
    }, duration);
}

// Add loading state to a button during async operations
function setButtonLoading(button, isLoading) {
    if (!button) return;
    
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.innerHTML = '<span class="button-spinner"></span> Loading...';
        button.classList.add('loading');
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || button.textContent;
        button.classList.remove('loading');
    }
}

// Add loading state to a form during submission
function setFormLoading(form, isLoading) {
    if (!form) return;
    
    const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
    const inputs = form.querySelectorAll('input, select, textarea, button');
    
    if (isLoading) {
        inputs.forEach(input => input.disabled = true);
        if (submitButton) {
            setButtonLoading(submitButton, true);
        }
    } else {
        inputs.forEach(input => input.disabled = false);
        if (submitButton) {
            setButtonLoading(submitButton, false);
        }
    }
}

// Fade in elements with staggered animation
function fadeInElements(selector, staggerDelay = 100) {
    const elements = document.querySelectorAll(selector);
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * staggerDelay);
    });
}

// Show loading state for table data
function showTableLoading(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    
    tbody.innerHTML = `
        <tr>
            <td colspan="100%" style="text-align: center; padding: 2rem;">
                <div class="loading-spinner" style="margin: 0 auto;"></div>
                <p style="margin-top: 1rem; color: #7f8c8d;">Loading data...</p>
            </td>
        </tr>
    `;
}

// Hide table loading and restore content
function hideTableLoading(tableId, content) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    
    tbody.innerHTML = content;
}

// Wrapper for fetch API with automatic loading states
async function fetchWithLoading(url, options = {}, showOverlay = false) {
    try {
        if (showOverlay) {
            showLoading();
        }
        
        const response = await fetch(url, options);
        
        if (showOverlay) {
            hideLoading();
        }
        
        return response;
    } catch (error) {
        if (showOverlay) {
            hideLoading();
        }
        throw error;
    }
}

// Add loading state to a card element
function setCardLoading(cardElement, isLoading) {
    if (!cardElement) return;
    
    if (isLoading) {
        cardElement.classList.add('card-loading');
    } else {
        cardElement.classList.remove('card-loading');
    }
}

// Show skeleton loader for a specific container
function showSkeletonLoader(containerId, type = 'card') {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    let skeletonHTML = '';
    
    switch(type) {
        case 'stat':
            skeletonHTML = `
                <div class="skeleton-card">
                    <div class="skeleton-stat">
                        <div class="skeleton skeleton-icon"></div>
                        <div class="skeleton-content">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
            `;
            break;
        case 'action':
            skeletonHTML = `
                <div class="skeleton-action-card">
                    <div class="skeleton skeleton-action-icon"></div>
                    <div class="skeleton skeleton-action-title"></div>
                    <div class="skeleton skeleton-action-text"></div>
                </div>
            `;
            break;
        case 'table':
            skeletonHTML = `
                <tr>
                    <td colspan="100%" style="text-align: center; padding: 2rem;">
                        <div class="loading-spinner" style="margin: 0 auto;"></div>
                        <p style="margin-top: 1rem; color: #7f8c8d;">Loading data...</p>
                    </td>
                </tr>
            `;
            break;
        default:
            skeletonHTML = `
                <div class="skeleton" style="height: 100px; width: 100%;"></div>
            `;
    }
    
    container.innerHTML = skeletonHTML;
}

// Smooth scroll to element with offset
function smoothScrollTo(elementId, offset = 80) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
    const offsetPosition = elementPosition - offset;
    
    window.scrollTo({
        top: offsetPosition,
        behavior: 'smooth'
    });
}

// Add pulse animation to element (useful for highlighting updates)
function pulseElement(element) {
    if (!element) return;
    
    element.style.animation = 'none';
    setTimeout(() => {
        element.style.animation = 'pulse 0.5s ease-in-out';
    }, 10);
}

// CSS for pulse animation (add to style if not present)
const pulseStyle = document.createElement('style');
pulseStyle.textContent = `
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
`;
document.head.appendChild(pulseStyle);
