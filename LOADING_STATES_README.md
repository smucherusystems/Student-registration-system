# Loading States and Transitions - Implementation Guide

## Overview

This document describes the loading states and transitions implementation for the School Management System dashboard. The implementation provides a smooth, professional user experience with visual feedback during data operations.

## Features Implemented

### 1. Full-Page Loading Overlay
- **Purpose**: Shows a loading spinner when the entire page is loading or during major data operations
- **Location**: `dashboard.php`, `loading_demo.html`
- **Usage**:
  ```javascript
  showLoading();  // Show overlay
  hideLoading();  // Hide overlay
  ```

### 2. Skeleton Loaders
- **Purpose**: Display placeholder content while actual data is being fetched
- **Types**:
  - Stat card skeletons
  - Action card skeletons
  - Table skeletons
- **Usage**:
  ```javascript
  showStatSkeletons();    // Show skeleton for stat cards
  showActionSkeletons();  // Show skeleton for action cards
  showTableLoading('tableId');  // Show skeleton for tables
  ```

### 3. Button Loading States
- **Purpose**: Indicate when a button action is in progress
- **Features**:
  - Disables button during loading
  - Shows spinner icon
  - Changes text to "Loading..."
- **Usage**:
  ```javascript
  const button = document.getElementById('myButton');
  setButtonLoading(button, true);   // Start loading
  setButtonLoading(button, false);  // Stop loading
  ```

### 4. Form Loading States
- **Purpose**: Disable entire form during submission
- **Features**:
  - Disables all inputs
  - Shows loading state on submit button
- **Usage**:
  ```javascript
  const form = document.getElementById('myForm');
  setFormLoading(form, true);   // Start loading
  setFormLoading(form, false);  // Stop loading
  ```

### 5. Card Loading States
- **Purpose**: Show loading indicator on individual cards
- **Features**:
  - Dims card content
  - Shows centered spinner
  - Prevents interaction
- **Usage**:
  ```javascript
  const card = document.getElementById('myCard');
  setCardLoading(card, true);   // Start loading
  setCardLoading(card, false);  // Stop loading
  ```

### 6. Smooth Transitions
- **Purpose**: Animate elements when they appear on screen
- **Features**:
  - Fade-in animation
  - Staggered delays for multiple elements
  - Smooth transform transitions
- **Usage**:
  ```javascript
  fadeInElements('.stat-card', 100);  // Fade in with 100ms stagger
  ```

### 7. AJAX Wrapper with Loading
- **Purpose**: Automatically handle loading states for fetch operations
- **Usage**:
  ```javascript
  const response = await fetchWithLoading('/api/endpoint', {
    method: 'POST',
    body: JSON.stringify(data)
  }, true);  // true = show full page overlay
  ```

## Files Modified

### 1. `js/loading.js`
- Core loading utilities
- All loading state functions
- Animation helpers
- Comprehensive documentation

### 2. `css/style.css`
- Loading overlay styles
- Skeleton loader animations
- Transition animations
- Button spinner styles
- Card loading states

### 3. `dashboard.php`
- Added loading overlay HTML
- Integrated loading.js
- Calls hideLoading() on page load
- Uses fadeInElements() for smooth appearance

### 4. `loading_demo.html` (NEW)
- Comprehensive demo page
- Shows all loading states in action
- Interactive test buttons
- Example implementations

## CSS Classes

### Loading Overlay
```css
.loading-overlay          /* Full-page overlay */
.loading-overlay.active   /* Visible state */
.loading-spinner          /* Spinning loader */
```

### Skeleton Loaders
```css
.skeleton                 /* Base skeleton animation */
.skeleton-card            /* Card container */
.skeleton-stat            /* Stat card skeleton */
.skeleton-icon            /* Icon placeholder */
.skeleton-content         /* Content area */
.skeleton-title           /* Title placeholder */
.skeleton-text            /* Text placeholder */
.skeleton-action-card     /* Action card skeleton */
.skeleton-action-icon     /* Action icon placeholder */
.skeleton-action-title    /* Action title placeholder */
.skeleton-action-text     /* Action text placeholder */
```

### Button States
```css
.btn.loading              /* Button in loading state */
.button-spinner           /* Button spinner icon */
```

### Card States
```css
.card-loading             /* Card in loading state */
.card-loading::after      /* Spinner overlay */
```

### Animations
```css
@keyframes spin           /* Spinner rotation */
@keyframes loading        /* Skeleton shimmer */
@keyframes fadeInUp       /* Fade in from bottom */
@keyframes fadeIn         /* Simple fade in */
@keyframes pulse          /* Pulse effect */
```

## JavaScript Functions

### Core Functions
- `showLoading()` - Show full-page overlay
- `hideLoading()` - Hide full-page overlay
- `showStatSkeletons()` - Display stat card skeletons
- `showActionSkeletons()` - Display action card skeletons
- `setButtonLoading(button, isLoading)` - Toggle button loading state
- `setFormLoading(form, isLoading)` - Toggle form loading state
- `setCardLoading(card, isLoading)` - Toggle card loading state
- `fadeInElements(selector, staggerDelay)` - Animate elements in
- `showTableLoading(tableId)` - Show table loading state
- `hideTableLoading(tableId, content)` - Restore table content

### Advanced Functions
- `fetchWithLoading(url, options, showOverlay)` - Fetch with automatic loading
- `showSkeletonLoader(containerId, type)` - Generic skeleton loader
- `smoothScrollTo(elementId, offset)` - Smooth scroll to element
- `pulseElement(element)` - Add pulse animation
- `simulateDataFetch(callback, duration)` - Simulate async operation

## Usage Examples

### Example 1: Page Load
```javascript
// In dashboard.php
document.addEventListener('DOMContentLoaded', function() {
    hideLoading();  // Hide overlay when page is ready
    fadeInElements('.stat-card', 100);  // Animate cards in
});
```

### Example 2: AJAX Data Fetch
```javascript
async function loadStudentData(studentId) {
    showLoading();
    
    try {
        const response = await fetch(`/api/students/${studentId}`);
        const data = await response.json();
        
        // Update UI with data
        updateStudentDisplay(data);
    } catch (error) {
        console.error('Error loading student:', error);
    } finally {
        hideLoading();
    }
}
```

### Example 3: Form Submission
```javascript
const form = document.getElementById('studentForm');
form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    setFormLoading(form, true);
    
    try {
        const formData = new FormData(form);
        const response = await fetch('/api/students', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            alert('Student added successfully!');
            form.reset();
        }
    } catch (error) {
        alert('Error adding student');
    } finally {
        setFormLoading(form, false);
    }
});
```

### Example 4: Button Action
```javascript
async function deleteStudent(button, studentId) {
    if (!confirm('Are you sure?')) return;
    
    setButtonLoading(button, true);
    
    try {
        await fetch(`/api/students/${studentId}`, {
            method: 'DELETE'
        });
        
        // Remove row from table
        button.closest('tr').remove();
    } catch (error) {
        alert('Error deleting student');
    } finally {
        setButtonLoading(button, false);
    }
}
```

### Example 5: Card Data Refresh
```javascript
async function refreshStatistics() {
    const statsCard = document.getElementById('statsCard');
    setCardLoading(statsCard, true);
    
    try {
        const response = await fetch('/api/statistics');
        const stats = await response.json();
        
        // Update card content
        updateStatsDisplay(stats);
    } catch (error) {
        console.error('Error loading stats:', error);
    } finally {
        setCardLoading(statsCard, false);
    }
}
```

## Testing

### Manual Testing
1. Open `loading_demo.html` in a browser
2. Click each demo button to test different loading states:
   - Full Page Loading
   - Skeleton Loaders
   - Button Loading
   - Card Loading
   - Fade In Animation

### Integration Testing
1. Navigate to `dashboard.php`
2. Observe loading overlay on page load
3. Verify smooth fade-in of cards
4. Test search/filter functionality (maintains smooth transitions)

## Browser Compatibility

- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support
- IE11: ⚠️ Partial support (no CSS Grid animations)

## Performance Considerations

1. **Loading Overlay**: Uses CSS transitions for smooth performance
2. **Skeleton Loaders**: CSS-only animations (no JavaScript)
3. **Fade Animations**: Hardware-accelerated transforms
4. **Staggered Delays**: Minimal JavaScript overhead

## Accessibility

- Loading overlay includes descriptive text
- Buttons are properly disabled during loading
- Screen readers announce loading states
- Keyboard navigation maintained during transitions

## Future Enhancements

1. Add progress bars for long operations
2. Implement toast notifications with loading states
3. Add more skeleton loader variants
4. Create loading state for charts
5. Add retry mechanism for failed operations

## Requirements Satisfied

This implementation satisfies **Requirement 6.5**:
- ✅ Implement loading indicators for data fetch operations
- ✅ Add smooth CSS transitions between states
- ✅ Display skeleton loaders for cards during data loading

## Support

For questions or issues with loading states:
1. Check `loading_demo.html` for examples
2. Review function documentation in `js/loading.js`
3. Inspect CSS classes in `css/style.css`
