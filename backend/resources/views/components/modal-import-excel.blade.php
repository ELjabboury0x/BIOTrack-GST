<!-- Import Excel Modal -->
<div id="importExcelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
     x-data="excelImporter()"
     @click.self="$el.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-screen overflow-y-auto animate-modal">
        <!-- Header -->
        <div class="sticky top-0 bg-gradient-to-r from-green-500 to-green-600 text-white p-6 border-b border-green-400">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Importer Excel</h2>
                <button onclick="this.closest('.fixed').classList.add('hidden')" class="text-2xl hover:bg-green-700 p-2 rounded-lg transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="p-8">
            <!-- Step Indicator -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center" :class="{ 'opacity-50': step !== 1 }">
                    <div class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center font-bold">1</div>
                    <span class="ml-2 font-semibold">Télécharger</span>
                </div>
                <div class="flex-1 h-1 mx-4" :class="step > 1 ? 'bg-green-500' : 'bg-gray-300'"></div>
                <div class="flex items-center" :class="{ 'opacity-50': step !== 2 }">
                    <div class="w-10 h-10 rounded-full" :class="step >= 2 ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'" class="flex items-center justify-center font-bold">2</div>
                    <span class="ml-2 font-semibold">Mapper</span>
                </div>
                <div class="flex-1 h-1 mx-4" :class="step > 2 ? 'bg-green-500' : 'bg-gray-300'"></div>
                <div class="flex items-center" :class="{ 'opacity-50': step !== 3 }">
                    <div class="w-10 h-10 rounded-full" :class="step >= 3 ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'" class="flex items-center justify-center font-bold">3</div>
                    <span class="ml-2 font-semibold">Importer</span>
                </div>
            </div>

            <!-- Step 1: File Upload -->
            <div x-show="step === 1">
                <div class="border-2 border-dashed border-green-300 rounded-lg p-12 text-center hover:border-green-500 transition-colors cursor-pointer"
                     @click="$el.querySelector('input[type=file]').click()">
                          <input id="import-excel-file" name="import_excel_file" type="file" 
                           accept=".xlsx,.xls,.csv"
                           @change="handleFileUpload($event)"
                           class="hidden">
                    <i class="fas fa-file-excel text-4xl text-green-500 mb-4 block"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Télécharger un fichier Excel</h3>
                </div>

                <template x-if="fileName">
                    <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-green-700 flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            Fichier sélectionné: <strong x-text="fileName" class="ml-2"></strong>
                        </p>
                    </div>
                </template>
            </div>

            <!-- Step 2: Column Mapping -->
            <div x-show="step === 2">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Mapper les colonnes</h3>

                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <template x-for="(excelCol, index) in excelColumns" :key="index">
                        <div class="flex items-center gap-4">
                            <div class="w-32 px-4 py-2 bg-gray-50 rounded-lg border border-gray-300">
                                <p class="text-sm font-mono text-gray-700" x-text="excelCol"></p>
                            </div>
                            <i class="fas fa-arrow-right text-gray-400"></i>
                            <select x-model="columnMap[index]"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <option value="">Ignorer cette colonne</option>
                                <template x-for="field in systemFields" :key="field.key">
                                    <option :value="field.key" x-text="field.label"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                </div>

                <!-- Add new field option -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <button type="button"
                            @click="showNewFieldForm = !showNewFieldForm"
                            class="text-blue-600 font-semibold flex items-center">
                        <i class="fas fa-plus mr-2"></i> Créer un nouveau champ
                    </button>
                    <div x-show="showNewFieldForm" class="mt-4 space-y-3">
                           <input id="new-field-name" name="new_field_name" type="text" 
                               placeholder="Nom du champ"
                               x-model="newFieldName"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                           <select id="new-field-type" name="new_field_type" x-model="newFieldType"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="text">Texte</option>
                            <option value="number">Nombre</option>
                            <option value="date">Date</option>
                            <option value="email">Email</option>
                        </select>
                        <button type="button"
                                @click="addNewField()"
                                class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Ajouter le champ
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Preview & Confirm -->
            <div x-show="step === 3">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Aperçu des données</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 border-b border-gray-300">
                            <tr>
                                <template x-for="header in previewHeaders" :key="header">
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700" x-text="header"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, rowIndex) in previewData.slice(0, 5)" :key="rowIndex">
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <template x-for="(value, colIndex) in row" :key="colIndex">
                                        <td class="px-4 py-2 text-gray-700" x-text="value"></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-8 border-t border-gray-200 mt-8">
                <button type="button"
                        @click="previousStep()"
                        x-show="step > 1"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">
                    <i class="fas fa-chevron-left mr-2"></i> Précédent
                </button>
                <button type="button"
                        @click="nextStep()"
                        x-show="step < 3"
                        class="ml-auto px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 font-semibold">
                    Suivant <i class="fas fa-chevron-right ml-2"></i>
                </button>
                <button type="button"
                        @click="confirmImport()"
                        x-show="step === 3"
                        class="ml-auto px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 font-semibold">
                    <i class="fas fa-check mr-2"></i> Importer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function excelImporter() {
    return {
        step: 1,
        fileName: '',
        fileData: null,
        excelColumns: [],
        systemFields: [
            { key: 'inventory_number_current', label: 'Code à barres' },
            { key: 'designation', label: 'Description de l\'équipement' },
            { key: 'serial_number', label: 'N° de série' },
            { key: 'unit_name', label: 'Unité' },
            { key: 'sector_name', label: 'Secteur' },
            { key: 'sector_description', label: 'Description secteur' },
            { key: 'brand_name', label: 'Marque' },
            { key: 'model_name', label: 'Modèle' },
            { key: 'market_label', label: 'Marché' },
            { key: 'lot_number', label: 'Lot' }
        ],
        columnMap: {},
        previewData: [],
        previewHeaders: [],
        showNewFieldForm: false,
        newFieldName: '',
        newFieldType: 'text',

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.fileName = file.name;
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const workbook = XLSX.read(e.target.result, { type: 'binary' });
                    const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                    const data = XLSX.utils.sheet_to_json(worksheet, { defval: '' });
                    
                    this.excelColumns = Object.keys(data[0] || {});
                    this.fileData = data;
                    this.previewData = data.slice(0, 5);
                    this.previewHeaders = this.excelColumns;
                    this.columnMap = {};
                    
                    // Auto-map obvious columns
                    this.excelColumns.forEach((col, idx) => {
                        const lowerCol = col.toLowerCase();
                        if (lowerCol.includes('code à barres') || lowerCol.includes('code a barres') || lowerCol.includes('code-barres') || lowerCol.includes('barcode')) this.columnMap[idx] = 'inventory_number_current';
                        else if (lowerCol.includes('description de l\'équipement') || lowerCol.includes('description de l\'equipement') || lowerCol.includes('designation') || lowerCol.includes('désignation')) this.columnMap[idx] = 'designation';
                        else if (lowerCol.includes('n° de série') || lowerCol.includes('n° serie') || lowerCol.includes('numéro de série') || lowerCol.includes('numero de serie') || lowerCol.includes('série') || lowerCol.includes('serie')) this.columnMap[idx] = 'serial_number';
                        else if (lowerCol === 'unité' || lowerCol === 'unite') this.columnMap[idx] = 'unit_name';
                        else if (lowerCol === 'secteur') this.columnMap[idx] = 'sector_name';
                        else if (lowerCol.includes('description secteur')) this.columnMap[idx] = 'sector_description';
                        else if (lowerCol.includes('marque')) this.columnMap[idx] = 'brand_name';
                        else if (lowerCol.includes('modèle') || lowerCol.includes('modele')) this.columnMap[idx] = 'model_name';
                        else if (lowerCol.includes('marché') || lowerCol.includes('marche')) this.columnMap[idx] = 'market_label';
                        else if (lowerCol === 'lot' || lowerCol.includes('lot')) this.columnMap[idx] = 'lot_number';
                    });
                } catch (error) {
                    alert('Erreur lors de la lecture du fichier: ' + error.message);
                }
            };
            reader.readAsBinaryString(file);
        },

        nextStep() {
            if (this.step === 1 && !this.fileName) {
                alert('Veuillez sélectionner un fichier');
                return;
            }
            this.step++;
        },

        previousStep() {
            if (this.step > 1) this.step--;
        },

        addNewField() {
            if (this.newFieldName.trim()) {
                this.systemFields.push({ key: this.newFieldName, label: this.newFieldName });
                this.newFieldName = '';
                this.newFieldType = 'text';
                this.showNewFieldForm = false;
            }
        },

        async confirmImport() {
            const mappedData = this.fileData.map(row => {
                const newRow = {};
                Object.entries(this.columnMap).forEach(([excelIdx, systemField]) => {
                    if (systemField) {
                        newRow[systemField] = row[this.excelColumns[excelIdx]];
                    }
                });
                return newRow;
            }).filter(row => (row.inventory_number_current || row.designation));

            if (!mappedData.length) {
                alert('Aucune ligne exploitable trouvée. Vérifiez le mapping des colonnes.');
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('{{ route('equipements.import-excel') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ rows: mappedData, replace_existing: false })
                });

                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Erreur pendant l\'import.');
                }

                alert(`Import terminé: ${result.deleted ?? 0} supprimés, ${result.created} créés, ${result.updated} mis à jour, ${result.skipped} ignorés.`);
                window.location.href = '{{ route('equipements') }}';
            } catch (error) {
                alert(error.message);
            }
        }
    };
}
</script>
