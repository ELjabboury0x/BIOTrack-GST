<!-- Dynamic Table Component -->
@php
    $isMajorUser = auth()->user()?->role === 'major';
    $tableData = $data ?? [];
    $deleteEntityLabel = trim((string) ($deleteEntityLabel ?? 'cet enregistrement'));
    $buttonStyle = $buttonStyle ?? null;
    $isEquipmentsStyle = $buttonStyle === 'equipments';

    $secondaryButtonClass = $isEquipmentsStyle
        ? 'inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 px-4 text-sm font-semibold text-gray-700 transition-all duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300'
        : 'px-4 py-2.5 border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 text-sm font-semibold text-gray-600';

    $importButtonClass = $isEquipmentsStyle
        ? 'inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300'
        : 'px-4 py-2.5 border border-blue-200 text-blue-600 rounded-xl hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 text-sm font-semibold';

    $exportButtonClass = $isEquipmentsStyle
        ? 'inline-flex h-10 items-center gap-2 rounded-lg bg-slate-700 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300'
        : 'px-4 py-2.5 border border-green-200 text-green-600 rounded-xl hover:bg-green-50 hover:border-green-300 transition-all duration-200 text-sm font-semibold';

    $addButtonClass = $isEquipmentsStyle
        ? 'inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300'
        : 'gst-hover-scale px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200 font-semibold flex items-center gap-2';

    $tableColumns = $columns ?? [
        ['key' => 'id', 'label' => 'ID', 'visible' => true, 'type' => 'text'],
        ['key' => 'name', 'label' => 'Nom', 'visible' => true, 'type' => 'text'],
        ['key' => 'status', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
        ['key' => 'date', 'label' => 'Date', 'visible' => true, 'type' => 'date'],
    ];
@endphp
<div class="bg-white rounded-2xl shadow-md overflow-hidden animate-fade-in border border-gray-100" 
    x-data="tableComponent({{ \Illuminate\Support\Js::from($tableData) }}, {{ \Illuminate\Support\Js::from($tableColumns) }})"
     style="animation-delay: 0.8s">
    
    <!-- Table Header Actions -->
    <div class="p-6 border-b border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
            <!-- Search Bar -->
            <div class="relative">
                <i class="fas fa-search absolute left-3.5 top-3 text-gray-400"></i>
                <input type="text" 
                       placeholder="Rechercher..." 
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition-all duration-200 bg-gray-50/50"
                       @input="searchTerm = $el.value; filterData()">
            </div>

            <!-- Column Toggle & Other Actions -->
            <div class="table-toolbar-actions flex justify-center gap-2">
                <button @click="toggleColumnVisibility()" 
                        class="{{ $secondaryButtonClass }}">
                    <i class="fas fa-columns mr-2 text-gray-400"></i> Colonnes
                </button>
                @if (($showImportAction ?? true) === true && !$isMajorUser)
                    <button onclick="showImportModal()" 
                            class="{{ $importButtonClass }}">
                        <i class="fas fa-upload mr-2"></i> Importer
                    </button>
                @endif
                @if (($showExportAction ?? true) === true)
                    <button @click="exportToPdf()" 
                            class="{{ $exportButtonClass }}">
                        <i class="fas fa-file-export mr-2"></i> Exporter
                    </button>
                @endif
            </div>

            <!-- Add New Button -->
            @if (($showAddButton ?? false) === true && !$isMajorUser)
                <div class="flex justify-end">
                    @if (!empty($addButtonRoute ?? null))
                        <a href="{{ route($addButtonRoute) }}"
                           class="{{ $addButtonClass }}">
                            <i class="fas {{ $addButtonIcon ?? 'fa-plus' }}"></i> {{ $addButtonLabel ?? 'Ajouter' }}
                        </a>
                    @else
                        <button onclick="showAddRecordModal()" 
                                class="{{ $addButtonClass }}">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                    @endif
                </div>
            @endif
        </div>

        <!-- Column Visibility Dropdown (Hidden by default) -->
        <div x-show="showColumnDropdown" 
             @click.away="showColumnDropdown = false"
             class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm font-semibold text-gray-700 mb-3">Afficher/Masquer colonnes:</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <template x-for="column in columns" :key="column.key">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" 
                               :checked="column.visible" 
                               @change="column.visible = !column.visible"
                               class="rounded">
                        <span class="text-sm text-gray-700" x-text="column.label"></span>
                    </label>
                </template>
            </div>

            <div class="mt-4 border-t border-gray-200 pt-4">
                <p class="text-sm font-semibold text-gray-700 mb-2">Ajouter une colonne manuellement</p>
                <div class="flex flex-col md:flex-row gap-2">
                    <input type="text"
                           x-model="manualColumnLabel"
                           placeholder="Nom de la colonne"
                           class="w-full md:w-72 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <button @click="addManualColumn()"
                            type="button"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold">
                        Ajouter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <template x-for="column in columns.filter(c => c.visible)" :key="column.key">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
                            @click="sortBy(column.key)">
                            <div class="flex items-center space-x-2">
                                <span x-text="column.label"></span>
                                <i class="fas text-xs"
                                   :class="sortColumn === column.key ? (sortOrder === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-up-down text-gray-300'"></i>
                            </div>
                        </th>
                    </template>
                    @if(!$isMajorUser)
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, index) in paginatedData" :key="index">
                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                        <template x-for="column in columns.filter(c => c.visible)" :key="column.key">
                            <td class="px-6 py-4 text-sm text-gray-700" x-text="formatCell(row[column.key], column.type)"></td>
                        </template>
                        @if(!$isMajorUser)
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                @if (($showFillAction ?? true) === true && !$isMajorUser)
                                <template x-if="row.fill_url">
                                    <button @click="fillRow(row)" class="text-indigo-600 hover:text-indigo-800 transition-colors p-2" title="Remplir le rapport">
                                        <i class="fas fa-file-signature"></i>
                                    </button>
                                </template>
                                @endif
                                @if (($showCloseAction ?? true) === true && !$isMajorUser)
                                <template x-if="row.close_url && row.can_close">
                                    <button @click="closeRow(row)" class="text-green-600 hover:text-green-800 transition-colors p-2" title="Clôturer">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                </template>
                                @endif
                                @if (($showEditAction ?? true) === true && !$isMajorUser)
                                <button @click="editRow(row)" class="text-blue-600 hover:text-blue-800 transition-colors p-2">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif
                                @if (($showDeleteAction ?? true) === true && !$isMajorUser)
                                <button @click="deleteRow(row)" class="text-red-600 hover:text-red-800 transition-colors p-2">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                </template>
                <template x-if="paginatedData.length === 0">
                    <tr>
                        <td :colspan="columns.filter(c => c.visible).length + 1" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>Aucune donnée disponible</p>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-600">
            Affichage <span x-text="filteredData.length ? ((currentPage - 1) * itemsPerPage + 1) : 0"></span> à 
            <span x-text="Math.min(currentPage * itemsPerPage, filteredData.length)"></span> 
            sur <span x-text="filteredData.length"></span> résultats
        </div>
        <div class="flex gap-2">
            <button @click="previousPage()" 
                    :disabled="currentPage === 1"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </button>
            <template x-for="page in totalPages" :key="page">
                <button @click="currentPage = page"
                        :class="currentPage === page ? 'bg-blue-500 text-white' : 'border border-gray-300 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    <span x-text="page"></span>
                </button>
            </template>
            <button @click="nextPage()"
                    :disabled="currentPage === totalPages"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <div x-show="showDeleteModal"
         x-transition.opacity
         class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center px-4"
         style="display: none;">
        <div @click.away="cancelDelete()" class="w-full max-w-md bg-white rounded-xl shadow-xl p-6">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                    <i class="fas fa-trash"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800">Confirmer la suppression</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Voulez-vous vraiment supprimer {{ $deleteEntityLabel }} ? Cette action est irréversible.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button"
                        @click="cancelDelete()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Annuler
                </button>
                <button type="button"
                        @click="confirmDelete()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function tableComponent(initialData, initialColumns) {
    return {
        // Data
        allData: initialData,
        filteredData: initialData,
        searchTerm: '',
        
        // Pagination
        currentPage: 1,
        itemsPerPage: 10,
        
        // Sorting
        sortColumn: null,
        sortOrder: 'asc',
        
        // UI
        showColumnDropdown: false,
        manualColumnLabel: '',
        showDeleteModal: false,
        rowToDelete: null,
        
        // Columns (customize per module)
        columns: Array.isArray(initialColumns) && initialColumns.length > 0
            ? initialColumns
            : [
                { key: 'id', label: 'ID', visible: true, type: 'text' },
                { key: 'name', label: 'Nom', visible: true, type: 'text' },
                { key: 'status', label: 'Statut', visible: true, type: 'status' },
                { key: 'date', label: 'Date', visible: true, type: 'date' }
            ],
        
        // Computed properties
        get paginatedData() {
            let start = (this.currentPage - 1) * this.itemsPerPage;
            let end = start + this.itemsPerPage;
            return this.filteredData.slice(start, end);
        },
        
        get totalPages() {
            return Math.ceil(this.filteredData.length / this.itemsPerPage);
        },
        
        // Methods
        filterData() {
            this.currentPage = 1;
            if (!this.searchTerm) {
                this.filteredData = [...this.allData];
            } else {
                const term = this.searchTerm.toLowerCase();
                this.filteredData = this.allData.filter(row => {
                    return Object.values(row).some(val => 
                        val && val.toString().toLowerCase().includes(term)
                    );
                });
            }
        },
        
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortOrder = 'asc';
            }
            
            this.filteredData.sort((a, b) => {
                const aVal = a[column];
                const bVal = b[column];
                
                if (aVal < bVal) return this.sortOrder === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortOrder === 'asc' ? 1 : -1;
                return 0;
            });
        },
        
        toggleColumnVisibility() {
            this.showColumnDropdown = !this.showColumnDropdown;
        },

        addManualColumn() {
            const label = (this.manualColumnLabel || '').trim();

            if (!label) {
                alert('Veuillez saisir un nom de colonne.');
                return;
            }

            let key = label
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');

            if (!key) {
                key = `colonne_${this.columns.length + 1}`;
            }

            const existingKeys = this.columns.map(column => column.key);
            let uniqueKey = key;
            let suffix = 1;

            while (existingKeys.includes(uniqueKey)) {
                uniqueKey = `${key}_${suffix}`;
                suffix++;
            }

            this.columns.push({
                key: uniqueKey,
                label,
                visible: true,
                type: 'text',
            });

            this.allData = this.allData.map(row => ({ ...row, [uniqueKey]: row[uniqueKey] ?? '-' }));
            this.filteredData = this.filteredData.map(row => ({ ...row, [uniqueKey]: row[uniqueKey] ?? '-' }));
            this.manualColumnLabel = '';
        },
        
        formatCell(value, type) {
            if (!value) return '-';
            if (type === 'date') return new Date(value).toLocaleDateString('fr-FR');
            if (type === 'status') return this.getStatusBadge(value);
            return value;
        },
        
        getStatusBadge(status) {
            const badges = {
                'actif': '✓ Actif',
                'en_cours': '⏳ En Cours',
                'en_attente': '⏸ En Attente',
                'termine': '✓ Terminé',
                'inactif': '✗ Inactif',
                'fonctionnel': '✓ Fonctionnel',
                'reserve': '🟡 Réserve',
                'panne': '⚠️ Panne',
                'hors_service': '⛔ Hors service',
                'draft': '📝 Brouillon',
                'submitted': '📨 Soumis',
                'validated': '✅ Validé',
                'closed': '🔒 Clôturé'
            };
            return badges[status] || status;
        },
        
        previousPage() {
            if (this.currentPage > 1) this.currentPage--;
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        },
        
        editRow(row) {
            if (row && row.edit_url) {
                window.location.href = row.edit_url;
                return;
            }

            if (typeof showAddRecordModal === 'function') {
                showAddRecordModal(row);
            }
        },

        fillRow(row) {
            if (row && row.fill_url) {
                window.location.href = row.fill_url;
            }
        },

        closeRow(row) {
            if (row && row.close_url) {
                window.location.href = row.close_url;
            }
        },
        
        deleteRow(row) {
            this.rowToDelete = row;
            this.showDeleteModal = true;
        },

        cancelDelete() {
            this.showDeleteModal = false;
            this.rowToDelete = null;
        },

        confirmDelete() {
            const row = this.rowToDelete;

            if (!row) {
                this.cancelDelete();
                return;
            }

            this.showDeleteModal = false;

            if (row && row.delete_url) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = row.delete_url;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';

                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
                return;
            }

            this.allData = this.allData.filter(r => r.id !== row.id);
            this.filterData();
            this.rowToDelete = null;
        },

        exportToPdf() {
            if (!window.jspdf || !window.jspdf.jsPDF) {
                alert('Le module PDF est indisponible.');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape' });

            const visibleColumns = this.columns.filter(column => column.visible);
            const headers = [visibleColumns.map(column => column.label)];
            const rows = this.filteredData.map(row => {
                return visibleColumns.map(column => {
                    const value = this.formatCell(row[column.key], column.type);
                    return value ? value.toString() : '-';
                });
            });

            doc.setFontSize(12);
            doc.text('Export du tableau', 14, 12);
            doc.setFontSize(9);
            doc.text(`Date: ${new Date().toLocaleDateString('fr-FR')}`, 14, 18);

            if (typeof doc.autoTable === 'function') {
                doc.autoTable({
                    head: headers,
                    body: rows,
                    startY: 24,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [37, 99, 235] },
                });
            }

            const dateSuffix = new Date().toISOString().slice(0, 10);
            doc.save(`tableau-${dateSuffix}.pdf`);
        },

        downloadCsv(fileName, headers, rows) {
            const escapeCell = (value) => {
                const text = (value ?? '').toString();
                const escaped = text.replace(/"/g, '""');
                return `"${escaped}"`;
            };

            const csvLines = [
                headers.map(escapeCell).join(','),
                ...rows.map((row) => row.map(escapeCell).join(',')),
            ];

            const blob = new Blob(["\uFEFF" + csvLines.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        },

        exportToExcel() {
            const visibleColumns = this.columns.filter(column => column.visible);

            if (!window.XLSX) {
                const headers = visibleColumns.map(column => column.label);
                const rows = this.filteredData.map(row => {
                    return visibleColumns.map(column => this.formatCell(row[column.key], column.type));
                });

                const dateSuffix = new Date().toISOString().slice(0, 10);
                this.downloadCsv(`tableau-${dateSuffix}.csv`, headers, rows);
                return;
            }

            const rows = this.filteredData.map(row => {
                const formattedRow = {};

                visibleColumns.forEach(column => {
                    formattedRow[column.label] = this.formatCell(row[column.key], column.type);
                });

                return formattedRow;
            });

            const worksheet = XLSX.utils.json_to_sheet(rows);
            const workbook = XLSX.utils.book_new();

            XLSX.utils.book_append_sheet(workbook, worksheet, 'Tableau');

            const dateSuffix = new Date().toISOString().slice(0, 10);
            XLSX.writeFile(workbook, `tableau-${dateSuffix}.xlsx`);
        }
    };
}

function showAddRecordModal(row = null) {
    document.getElementById('addRecordModal').classList.remove('hidden');
}

function showImportModal() {
    document.getElementById('importExcelModal').classList.remove('hidden');
}

function exportTableToPdf() {
    const tableRoot = document.querySelector('[x-data^="tableComponent("]');

    if (!tableRoot || !tableRoot._x_dataStack || !tableRoot._x_dataStack[0] || typeof tableRoot._x_dataStack[0].exportToPdf !== 'function') {
        alert('Tableau non disponible pour export PDF.');
        return;
    }

    tableRoot._x_dataStack[0].exportToPdf();
}

function exportTableToExcel() {
    const tableRoot = document.querySelector('[x-data^="tableComponent("]');

    if (!tableRoot || !tableRoot._x_dataStack || !tableRoot._x_dataStack[0] || typeof tableRoot._x_dataStack[0].exportToExcel !== 'function') {
        alert('Tableau non disponible pour export Excel.');
        return;
    }

    tableRoot._x_dataStack[0].exportToExcel();
}
</script>
