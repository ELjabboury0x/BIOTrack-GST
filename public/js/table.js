/* ============================================
   Table Component - Enhanced Functionality
   ============================================ */

/**
 * Table Manager Class
 */
class TableManager {
    constructor(containerSelector, options = {}) {
        this.container = document.querySelector(containerSelector);
        this.options = {
            pageSize: options.pageSize || 10,
            searchDelay: options.searchDelay || 300,
            ...options
        };

        this.data = options.data || [];
        this.filteredData = [...this.data];
        this.currentPage = 1;
        this.sortColumn = null;
        this.sortOrder = 'asc';
        this.selectedRows = new Set();

        this.init();
    }

    /**
     * Initialize Table
     */
    init() {
        this.setupEventListeners();
        this.render();
    }

    /**
     * Setup Event Listeners
     */
    setupEventListeners() {
        // Search functionality
        const searchInput = this.container?.querySelector('[data-search]');
        if (searchInput) {
            searchInput.addEventListener('input', debounce((e) => {
                this.search(e.target.value);
            }, this.options.searchDelay));
        }

        // Sort functionality
        this.container?.querySelectorAll('[data-sort]').forEach(header => {
            header.addEventListener('click', (e) => {
                this.sort(header.dataset.sort);
            });
        });

        // Row selection
        this.container?.querySelectorAll('[data-row-selector]').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.toggleRowSelection(e.target.dataset.rowId, e.target.checked);
            });
        });
    }

    /**
     * Search Data
     */
    search(query) {
        this.currentPage = 1;

        if (!query.trim()) {
            this.filteredData = [...this.data];
            return;
        }

        const lowerQuery = query.toLowerCase();
        this.filteredData = this.data.filter(row => {
            return Object.values(row).some(value =>
                value && value.toString().toLowerCase().includes(lowerQuery)
            );
        });

        this.render();
    }

    /**
     * Sort Data
     */
    sort(columnName) {
        if (this.sortColumn === columnName) {
            this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = columnName;
            this.sortOrder = 'asc';
        }

        this.filteredData.sort((a, b) => {
            const aVal = a[columnName];
            const bVal = b[columnName];

            if (aVal == null) return 1;
            if (bVal == null) return -1;

            if (typeof aVal === 'string') {
                return this.sortOrder === 'asc'
                    ? aVal.localeCompare(bVal)
                    : bVal.localeCompare(aVal);
            }

            return this.sortOrder === 'asc' ? aVal - bVal : bVal - aVal;
        });

        this.currentPage = 1;
        this.render();
    }

    /**
     * Paginate Data
     */
    getPaginatedData() {
        const start = (this.currentPage - 1) * this.options.pageSize;
        const end = start + this.options.pageSize;
        return this.filteredData.slice(start, end);
    }

    /**
     * Get Total Pages
     */
    getTotalPages() {
        return Math.ceil(this.filteredData.length / this.options.pageSize);
    }

    /**
     * Toggle Row Selection
     */
    toggleRowSelection(rowId, isSelected) {
        if (isSelected) {
            this.selectedRows.add(rowId);
        } else {
            this.selectedRows.delete(rowId);
        }
    }

    /**
     * Get Selected Rows
     */
    getSelectedRows() {
        return this.data.filter(row => this.selectedRows.has(row.id));
    }

    /**
     * Render Table
     */
    render() {
        // Re-render implementation based on template
    }

    /**
     * Update Row
     */
    updateRow(id, data) {
        const index = this.data.findIndex(row => row.id === id);
        if (index !== -1) {
            this.data[index] = { ...this.data[index], ...data };
            this.filteredData = [...this.data];
            this.render();
        }
    }

    /**
     * Delete Row
     */
    deleteRow(id) {
        this.data = this.data.filter(row => row.id !== id);
        this.filteredData = this.filteredData.filter(row => row.id !== id);
        this.selectedRows.delete(id);
        this.render();
    }

    /**
     * Add Row
     */
    addRow(data) {
        const newRow = {
            id: Math.max(...this.data.map(r => r.id || 0)) + 1,
            ...data
        };
        this.data.unshift(newRow);
        this.filteredData = [...this.data];
        this.render();
        return newRow;
    }
}

/**
 * Excel Import/Export Utilities
 */
const excelUtils = {
    downloadCsv: function(rows, filename) {
        const escapeCell = (value) => {
            const text = (value ?? '').toString();
            const escaped = text.replace(/"/g, '""');
            return `"${escaped}"`;
        };

        const csvText = rows.map((row) => row.map(escapeCell).join(',')).join('\n');
        const blob = new Blob(["\uFEFF" + csvText], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    },

    /**
     * Export to Excel
     */
    exportToExcel: function(data, filename = 'export.xlsx') {
        const keys = Object.keys(data[0] || {});

        // Fallback to CSV when XLSX library is unavailable.
        if (typeof XLSX === 'undefined') {
            const rows = [keys, ...data.map((row) => keys.map((key) => row[key] ?? ''))];
            const csvFilename = filename.replace(/\.xlsx$/i, '.csv');
            this.downloadCsv(rows, csvFilename);
            return;
        }

        // Convert data to worksheet
        const worksheet = XLSX.utils.json_to_sheet(data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Sheet1');

        // Style worksheet
        worksheet['!cols'] = [{ wch: 12 }, { wch: 15 }, { wch: 20 }];

        // Generate file
        XLSX.writeFile(workbook, filename);
    },

    /**
     * Import from Excel
     */
    importFromExcel: function(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                    const jsonData = XLSX.utils.sheet_to_json(worksheet);

                    resolve({
                        columns: Object.keys(jsonData[0] || {}),
                        data: jsonData
                    });
                } catch (error) {
                    reject(error);
                }
            };

            reader.onerror = () => reject(new Error('File read error'));
            reader.readAsArrayBuffer(file);
        });
    },

    /**
     * Download Template
     */
    downloadTemplate: function(columns, filename = 'template.xlsx') {
        const sample = columns.reduce((obj, col) => {
            const label = (col || '').toString().toLowerCase();
            if (label.includes('date')) {
                obj[col] = '2026-03-12';
            } else if (label.includes('prix')) {
                obj[col] = '1500';
            } else if (label.includes('qte') || label.includes('qté') || label.includes('quantite') || label.includes('quantité')) {
                obj[col] = '10';
            } else if (label.includes('code')) {
                obj[col] = 'CODE-001';
            } else if (label.includes('nom')) {
                obj[col] = 'Exemple Nom';
            } else if (label.includes('fournisseur')) {
                obj[col] = 'Exemple Fournisseur';
            } else {
                obj[col] = 'Exemple';
            }

            return obj;
        }, {});

        const empty = columns.reduce((obj, col) => {
            obj[col] = '';
            return obj;
        }, {});

        const data = [sample, empty];

        this.exportToExcel(data, filename);
    }
};

/**
 * Advanced Filters
 */
class AdvancedFilter {
    constructor(containerSelector) {
        this.container = document.querySelector(containerSelector);
        this.filters = [];
    }

    /**
     * Add Filter
     */
    addFilter(column, operator, value) {
        this.filters.push({ column, operator, value });
    }

    /**
     * Remove Filter
     */
    removeFilter(index) {
        this.filters.splice(index, 1);
    }

    /**
     * Apply Filters
     */
    apply(data) {
        return data.filter(row => {
            return this.filters.every(filter => {
                const value = row[filter.column];

                switch (filter.operator) {
                    case 'equals':
                        return value === filter.value;
                    case 'contains':
                        return value && value.toString().includes(filter.value);
                    case 'gt':
                        return value > filter.value;
                    case 'lt':
                        return value < filter.value;
                    case 'gte':
                        return value >= filter.value;
                    case 'lte':
                        return value <= filter.value;
                    case 'in':
                        return Array.isArray(filter.value) && filter.value.includes(value);
                    default:
                        return true;
                }
            });
        });
    }

    /**
     * Clear Filters
     */
    clear() {
        this.filters = [];
    }
}

/**
 * Bulk Actions
 */
class BulkActions {
    constructor(tableManager) {
        this.tableManager = tableManager;
    }

    /**
     * Delete Selected
     */
    deleteSelected() {
        const selected = this.tableManager.getSelectedRows();
        if (selected.length === 0) {
            alert('Aucune ligne sélectionnée');
            return;
        }

        if (confirm(`Supprimer ${selected.length} enregistrement(s)?`)) {
            selected.forEach(row => {
                this.tableManager.deleteRow(row.id);
            });
            showNotification(`${selected.length} enregistrement(s) supprimé(s)`, 'success');
        }
    }

    /**
     * Export Selected
     */
    exportSelected() {
        const selected = this.tableManager.getSelectedRows();
        if (selected.length === 0) {
            alert('Aucune ligne sélectionnée');
            return;
        }

        excelUtils.exportToExcel(selected, 'export-selected.xlsx');
        showNotification('Données exportées avec succès', 'success');
    }

    /**
     * Change Status for Selected
     */
    changeStatus(newStatus) {
        const selected = this.tableManager.getSelectedRows();
        if (selected.length === 0) {
            alert('Aucune ligne sélectionnée');
            return;
        }

        selected.forEach(row => {
            this.tableManager.updateRow(row.id, { statut: newStatus });
        });
        showNotification(`Statut mis à jour pour ${selected.length} enregistrement(s)`, 'success');
    }
}

/**
 * Row Actions
 */
const rowActions = {
    /**
     * Edit Row
     */
    edit: function(rowId) {
        console.log('Editing row:', rowId);
        showAddRecordModal();
    },

    /**
     * Delete Row
     */
    delete: function(rowId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement?')) {
            apiCall(`/api/records/${rowId}`, 'DELETE')
                .then(() => {
                    showNotification('Enregistrement supprimé avec succès', 'success');
                    location.reload();
                })
                .catch(error => {
                    showNotification('Erreur lors de la suppression', 'error');
                });
        }
    },

    /**
     * View Details
     */
    viewDetails: function(rowId) {
        console.log('Viewing details for row:', rowId);
        // Implement details view
    },

    /**
     * Duplicate Row
     */
    duplicate: function(rowId) {
        apiCall(`/api/records/${rowId}/duplicate`, 'POST')
            .then(() => {
                showNotification('Enregistrement dupliqué', 'success');
                location.reload();
            })
            .catch(error => {
                showNotification('Erreur lors de la duplication', 'error');
            });
    }
};

/**
 * Helper Functions
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

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 animate-slide-in-right bg-${type === 'success' ? 'green' : 'red'}-500`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

window.TableManager = TableManager;
window.excelUtils = excelUtils;
window.AdvancedFilter = AdvancedFilter;
window.BulkActions = BulkActions;
window.rowActions = rowActions;
