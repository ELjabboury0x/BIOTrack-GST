<!-- Add Record Modal -->
<div id="addRecordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
     x-data="{ formData: {} }"
     @click.self="$el.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-screen overflow-y-auto animate-modal">
        <!-- Header -->
        <div class="sticky top-0 bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 border-b border-blue-400">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Ajouter un nouvel enregistrement</h2>
                <button onclick="this.closest('.fixed').classList.add('hidden')" class="text-2xl hover:bg-blue-700 p-2 rounded-lg transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            <form class="space-y-6" method="POST" action="{{ route('equipements.store') }}">
                @csrf
                <!-- Dynamic Form Fields (will be populated based on columns) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Example Fields - will be replaced dynamically -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nom <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="designation"
                               placeholder="Entrez le nom" 
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="inventory_number_current"
                               placeholder="Entrez le code" 
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Numéro de série <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="serial_number"
                               placeholder="Entrez le numéro de série"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Marque
                        </label>
                        <input type="text"
                               name="brand_name"
                               placeholder="Entrez la marque"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Date de fabrication
                        </label>
                        <input type="date"
                               name="manufacture_date"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Icône inventaire
                        </label>
                        <select name="icon_class" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="fas fa-stethoscope">Stéthoscope</option>
                            <option value="fas fa-heartbeat">Cardio</option>
                            <option value="fas fa-x-ray">Imagerie</option>
                            <option value="fas fa-syringe">Injection</option>
                            <option value="fas fa-lungs">Respiratoire</option>
                            <option value="fas fa-hospital">Hospitalier</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Catégorie
                        </label>
                        <select name="category_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option selected>Sélectionner une catégorie santé</option>
                            <option>Imagerie médicale</option>
                            <option>Diagnostic patient</option>
                            <option>Monitorage et surveillance</option>
                            <option>Réanimation et soins intensifs</option>
                            <option>Anesthésie</option>
                            <option>Bloc opératoire et chirurgie</option>
                            <option>Stérilisation hospitalière</option>
                            <option>Laboratoire d’analyses</option>
                            <option>Perfusion et injection</option>
                            <option>Respiratoire et oxygénothérapie</option>
                            <option>Cardiologie (ECG, défibrillateur)</option>
                            <option>Néonatal et pédiatrie</option>
                            <option>Urgences et transport médical</option>
                            <option>Dentaire</option>
                            <option>Ophtalmologie</option>
                            <option>ORL</option>
                            <option>Rééducation et physiothérapie</option>
                            <option>Dialyse et néphrologie</option>
                            <option>Instruments médicaux généraux</option>
                            <option>Mobilier médical</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Service
                        </label>
                        <select name="service_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option>Réanimation Pédiatrique</option>
                            <option>Urgences pédiatriques</option>
                            <option>Consultations et Explorations Fonctionnelles Pédiatriques</option>
                            <option>Chirurgie Pédiatrique Traumato-orthopédique</option>
                            <option>Chirurgie Pédiatrique Urologique-Viscérale</option>
                            <option>Néonatologie (Réanimation néonatale)</option>
                            <option>Pédiatrie</option>
                            <option>Unité d'Oncologie Pédiatrique</option>
                            <option>Unité Technique d'Accouchement</option>
                            <option>Unité de gynécologie</option>
                            <option>Unité d'obstétrique</option>
                            <option>Unité de PMA (Procréation Médicalement Assistée)</option>
                            <option>Bloc Opératoire Central - Module 3 (Chirurgie pédiatrique)</option>
                            <option>Bloc Opératoire Central - Module 4 (Césarienne)</option>
                            <option>Bloc Opératoire Central - Réveil Enfant</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Statut
                        </label>
                        <select name="lifecycle_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                            <option value="en_maintenance">En maintenance</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            État de fonctionnement
                        </label>
                        <select name="operational_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="fonctionnel">Fonctionnel</option>
                            <option value="reserve">Fonctionnel avec réserve</option>
                            <option value="panne">En panne</option>
                            <option value="hors_service">Hors service</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Hôpital <span class="text-red-500">*</span>
                        </label>
                        <select name="hospital_code" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="HSP">HSP</option>
                            <option value="HME">HME</option>
                            <option value="HO">HO</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Société
                        </label>
                        <input type="text"
                               name="company_name"
                               placeholder="Nom de la société"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Date marché
                        </label>
                        <input type="date"
                               name="market_date"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Vérification achevée
                        </label>
                        <select name="verification_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Non renseigné</option>
                            <option value="oui">Oui</option>
                            <option value="non">Non</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Magasin
                        </label>
                        <input type="text"
                               name="store_name"
                               placeholder="Nom du magasin"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Localisation exacte
                        </label>
                        <input type="text"
                               name="exact_location"
                               placeholder="Ex: Bâtiment A, 2ème étage, Salle 204"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea name="description" placeholder="Entrez une description" 
                                  rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Commentaire étiquette SN enlevée
                        </label>
                        <textarea placeholder="Entrez une description" 
                                  name="serial_label_comment"
                                  rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button type="button" 
                            onclick="this.closest('.fixed').classList.add('hidden')"
                            class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold transition-colors">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg font-semibold transition-all">
                        <i class="fas fa-plus mr-2"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
