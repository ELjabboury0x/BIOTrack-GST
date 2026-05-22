/* ============================================
   GMAO Dashboard - Main JavaScript
   ============================================ */

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupEventListeners();
});

/**
 * Initialize Dashboard
 */
function initializeDashboard() {
    console.log('Dashboard initialized');
    
    // Load sidebar state from localStorage
    const sidebarOpen = localStorage.getItem('sidebarOpen') !== 'false';
    
    // Setup smooth transitions
    document.body.style.scrollBehavior = 'smooth';
    
    // Initialize tooltips if any
    initializeTooltips();
}

/**
 * Setup Event Listeners
 */
function setupEventListeners() {
    // Notification button
    const notificationBtn = document.getElementById('notificationBtn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });

    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = (this.getAttribute('href') || '').trim();
            if (!href.startsWith('#') || href === '#') {
                return;
            }

            const target = document.getElementById(href.slice(1)) || document.querySelector(href);
            if (!target) {
                return;
            }

            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        });
    });
}

/**
 * Initialize Tooltips
 */
function initializeTooltips() {
    // Add tooltip functionality if needed
}

/**
 * Close All Modals
 */
function closeAllModals() {
    document.querySelectorAll('.fixed[id*="Modal"]').forEach(modal => {
        modal.classList.add('hidden');
    });
}

/**
 * Show Notification
 */
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 animate-slide-in-right`;
    
    const backgroundColor = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'warning': 'bg-yellow-500',
        'info': 'bg-blue-500'
    }[type] || 'bg-gray-500';

    notification.classList.add(backgroundColor);
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-3"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Format Date
 */
function formatDate(date, format = 'DD/MM/YYYY') {
    if (typeof date === 'string') {
        date = new Date(date);
    }

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    return format
        .replace('DD', day)
        .replace('MM', month)
        .replace('YYYY', year);
}

/**
 * Format Currency
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD'
    }).format(value);
}

/**
 * Format Number
 */
function formatNumber(value, decimals = 0) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(value);
}

/**
 * Debounce Function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle Function
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * API Call Helper
 */
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(endpoint, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || `HTTP error! status: ${response.status}`);
        }

        return result;
    } catch (error) {
        console.error('API Error:', error);
        showNotification('Une erreur est survenue', 'error');
        throw error;
    }
}

/**
 * Table Utilities
 */
const tableUtils = {
    /**
     * Export Table to CSV
     */
    exportToCSV: function(tableId, filename = 'export.csv') {
        const table = document.getElementById(tableId);
        if (!table) return;

        let csv = [];
        const rows = table.querySelectorAll('tr');

        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const csvRow = Array.from(cols).map(col => {
                let text = col.textContent.trim();
                text = text.replace(/"/g, '""');
                if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                    text = `"${text}"`;
                }
                return text;
            });
            csv.push(csvRow.join(','));
        });

        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    },

    /**
     * Print Table
     */
    printTable: function(tableId) {
        const printWindow = window.open('', '', 'height=600,width=800');
        const table = document.getElementById(tableId);
        
        printWindow.document.write('<html><head><title>Imprimer</title>');
        printWindow.document.write('<link rel="stylesheet" href="/css/dashboard.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(table.outerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
};

/**
 * Form Utilities
 */
const formUtils = {
    /**
     * Validate Email
     */
    validateEmail: function(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },

    /**
     * Validate Phone
     */
    validatePhone: function(phone) {
        const regex = /^(\+?212|0)[5-7]\d{8}$/;
        return regex.test(phone);
    },

    /**
     * Get Form Data
     */
    getFormData: function(formId) {
        const form = document.getElementById(formId);
        if (!form) return null;
        return new FormData(form);
    },

    /**
     * Clear Form
     */
    clearForm: function(formId) {
        const form = document.getElementById(formId);
        if (form) form.reset();
    },

    /**
     * Show Form Error
     */
    showError: function(fieldName, message) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.classList.add('border-red-500');
            const error = document.createElement('p');
            error.className = 'text-red-500 text-xs mt-1';
            error.textContent = message;
            field.parentNode.appendChild(error);
        }
    },

    /**
     * Clear Errors
     */
    clearErrors: function() {
        document.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
        });
        document.querySelectorAll('.text-red-500').forEach(el => el.remove());
    }
};

/**
 * Grid Utilities
 */
const gridUtils = {
    /**
     * Responsive Grid
     */
    setupResponsiveGrid: function(containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        const handleResize = debounce(() => {
            const width = container.offsetWidth;
            if (width < 640) {
                container.style.gridTemplateColumns = '1fr';
            } else if (width < 1024) {
                container.style.gridTemplateColumns = 'repeat(2, 1fr)';
            } else {
                container.style.gridTemplateColumns = 'repeat(3, 1fr)';
            }
        }, 250);

        window.addEventListener('resize', handleResize);
        handleResize();
    }
};

/**
 * Cookie Utilities
 */
const cookieUtils = {
    /**
     * Set Cookie
     */
    setCookie: function(name, value, days = 7) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    },

    /**
     * Get Cookie
     */
    getCookie: function(name) {
        const nameEQ = name + '=';
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i].trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length);
            }
        }
        return null;
    },

    /**
     * Delete Cookie
     */
    deleteCookie: function(name) {
        this.setCookie(name, '', -1);
    }
};

window.tableUtils = tableUtils;
window.formUtils = formUtils;
window.gridUtils = gridUtils;
window.cookieUtils = cookieUtils;
