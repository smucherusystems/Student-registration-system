/**
 * Search Utility
 * Provides real-time search and filtering functionality for tables
 */

/**
 * Initialize search functionality for a table
 * @param {string} tableId - The ID of the table to search
 * @param {string} searchInputId - The ID of the search input field
 * @param {object} options - Optional configuration
 * @returns {object} Search controller object
 */
function initializeSearch(tableId, searchInputId, options = {}) {
    const table = document.getElementById(tableId);
    const searchInput = document.getElementById(searchInputId);
    
    if (!table || !searchInput) {
        console.error('Table or search input not found');
        return null;
    }
    
    // Default options
    const config = {
        highlightMatches: options.highlightMatches !== false, // Default true
        caseSensitive: options.caseSensitive || false,
        searchDelay: options.searchDelay || 300, // Debounce delay in ms
        minChars: options.minChars || 0, // Minimum characters before search
        noResultsMessage: options.noResultsMessage || 'No results found',
        columnsToSearch: options.columnsToSearch || null, // Array of column indices, null = all
        onSearch: options.onSearch || null, // Callback function
        emptyStateElement: options.emptyStateElement || null // Custom empty state element
    };
    
    let searchTimeout = null;
    let originalRows = [];
    
    // Store original row data
    const tbody = table.querySelector('tbody');
    if (tbody) {
        originalRows = Array.from(tbody.querySelectorAll('tr'));
    }
    
    /**
     * Perform the search
     * @param {string} searchTerm - The search term
     */
    function performSearch(searchTerm) {
        // Clear previous highlights
        clearHighlights();
        
        // Check minimum characters
        if (searchTerm.length < config.minChars && searchTerm.length > 0) {
            return;
        }
        
        // If search is empty, show all rows
        if (searchTerm.length === 0) {
            showAllRows();
            hideNoResultsMessage();
            if (config.onSearch) {
                config.onSearch(originalRows.length, originalRows.length);
            }
            return;
        }
        
        // Prepare search term
        const term = config.caseSensitive ? searchTerm : searchTerm.toLowerCase();
        let visibleCount = 0;
        
        // Search through rows
        originalRows.forEach(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            let rowMatches = false;
            
            // Determine which columns to search
            const columnsToCheck = config.columnsToSearch 
                ? cells.filter((_, index) => config.columnsToSearch.includes(index))
                : cells;
            
            // Check each cell
            columnsToCheck.forEach(cell => {
                const cellText = config.caseSensitive 
                    ? cell.textContent 
                    : cell.textContent.toLowerCase();
                
                if (cellText.includes(term)) {
                    rowMatches = true;
                    
                    // Highlight matching text
                    if (config.highlightMatches) {
                        highlightText(cell, term);
                    }
                }
            });
            
            // Show or hide row based on match
            if (rowMatches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        if (visibleCount === 0) {
            showNoResultsMessage();
        } else {
            hideNoResultsMessage();
        }
        
        // Call callback if provided
        if (config.onSearch) {
            config.onSearch(visibleCount, originalRows.length);
        }
    }
    
    /**
     * Highlight matching text in a cell
     * @param {HTMLElement} cell - The cell element
     * @param {string} searchTerm - The search term to highlight
     */
    function highlightText(cell, searchTerm) {
        const originalText = cell.textContent;
        const regex = new RegExp(`(${escapeRegex(searchTerm)})`, config.caseSensitive ? 'g' : 'gi');
        
        // Only highlight if cell contains only text (no nested elements)
        if (cell.children.length === 0) {
            const highlightedText = originalText.replace(regex, '<mark class="search-highlight">$1</mark>');
            cell.innerHTML = highlightedText;
        }
    }
    
    /**
     * Clear all highlights
     */
    function clearHighlights() {
        const highlights = table.querySelectorAll('.search-highlight');
        highlights.forEach(highlight => {
            const parent = highlight.parentNode;
            parent.replaceChild(document.createTextNode(highlight.textContent), highlight);
            parent.normalize(); // Merge adjacent text nodes
        });
    }
    
    /**
     * Show all rows
     */
    function showAllRows() {
        originalRows.forEach(row => {
            row.style.display = '';
        });
    }
    
    /**
     * Show no results message
     */
    function showNoResultsMessage() {
        let noResultsRow = table.querySelector('.no-results-row');
        
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row';
            
            const cell = document.createElement('td');
            cell.colSpan = table.querySelector('thead tr')?.children.length || 1;
            cell.className = 'no-results-cell';
            cell.textContent = config.noResultsMessage;
            
            noResultsRow.appendChild(cell);
            tbody.appendChild(noResultsRow);
        }
        
        noResultsRow.style.display = '';
        
        // Show custom empty state if provided
        if (config.emptyStateElement) {
            const emptyState = document.getElementById(config.emptyStateElement);
            if (emptyState) {
                emptyState.style.display = 'block';
            }
        }
    }
    
    /**
     * Hide no results message
     */
    function hideNoResultsMessage() {
        const noResultsRow = table.querySelector('.no-results-row');
        if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
        
        // Hide custom empty state if provided
        if (config.emptyStateElement) {
            const emptyState = document.getElementById(config.emptyStateElement);
            if (emptyState) {
                emptyState.style.display = 'none';
            }
        }
    }
    
    /**
     * Escape special regex characters
     * @param {string} string - String to escape
     * @returns {string} Escaped string
     */
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    /**
     * Handle search input
     */
    function handleSearchInput() {
        const searchTerm = searchInput.value.trim();
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            performSearch(searchTerm);
        }, config.searchDelay);
    }
    
    // Attach event listener
    searchInput.addEventListener('input', handleSearchInput);
    
    // Return controller object
    return {
        search: performSearch,
        clear: () => {
            searchInput.value = '';
            performSearch('');
        },
        refresh: () => {
            originalRows = Array.from(tbody.querySelectorAll('tr:not(.no-results-row)'));
            performSearch(searchInput.value.trim());
        },
        destroy: () => {
            searchInput.removeEventListener('input', handleSearchInput);
            clearHighlights();
            showAllRows();
            hideNoResultsMessage();
        },
        getVisibleRows: () => {
            return originalRows.filter(row => row.style.display !== 'none');
        },
        getTotalRows: () => {
            return originalRows.length;
        }
    };
}

/**
 * Initialize multi-column filter functionality
 * @param {string} tableId - The ID of the table to filter
 * @param {object} filters - Object mapping filter IDs to column indices
 * @returns {object} Filter controller object
 */
function initializeFilters(tableId, filters) {
    const table = document.getElementById(tableId);
    
    if (!table) {
        console.error('Table not found');
        return null;
    }
    
    const tbody = table.querySelector('tbody');
    const originalRows = Array.from(tbody.querySelectorAll('tr'));
    const activeFilters = {};
    
    /**
     * Apply all active filters
     */
    function applyFilters() {
        let visibleCount = 0;
        
        originalRows.forEach(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            let rowMatches = true;
            
            // Check each active filter
            for (const [columnIndex, filterValue] of Object.entries(activeFilters)) {
                if (filterValue === '' || filterValue === 'all') continue;
                
                const cell = cells[columnIndex];
                if (!cell) continue;
                
                const cellText = cell.textContent.trim().toLowerCase();
                const filterText = filterValue.toLowerCase();
                
                if (cellText !== filterText) {
                    rowMatches = false;
                    break;
                }
            }
            
            // Show or hide row
            if (rowMatches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        return visibleCount;
    }
    
    /**
     * Set up filter for a column
     * @param {string} filterId - The ID of the filter select element
     * @param {number} columnIndex - The column index to filter
     */
    function setupFilter(filterId, columnIndex) {
        const filterElement = document.getElementById(filterId);
        
        if (!filterElement) {
            console.error(`Filter element ${filterId} not found`);
            return;
        }
        
        filterElement.addEventListener('change', (e) => {
            const value = e.target.value;
            
            if (value === '' || value === 'all') {
                delete activeFilters[columnIndex];
            } else {
                activeFilters[columnIndex] = value;
            }
            
            applyFilters();
        });
    }
    
    // Set up all filters
    for (const [filterId, columnIndex] of Object.entries(filters)) {
        setupFilter(filterId, columnIndex);
    }
    
    return {
        clear: () => {
            // Clear all filter values
            for (const filterId of Object.keys(filters)) {
                const filterElement = document.getElementById(filterId);
                if (filterElement) {
                    filterElement.value = '';
                }
            }
            // Clear active filters
            Object.keys(activeFilters).forEach(key => delete activeFilters[key]);
            applyFilters();
        },
        refresh: applyFilters
    };
}
