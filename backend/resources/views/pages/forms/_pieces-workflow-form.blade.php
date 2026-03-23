@php
    $editing = isset($piece) && $piece;

    $currentPhase = old('phase', $editing ? ($piece->phase ?: 'decharge') : 'decharge');
    $currentEntryMode = old('entry_mode', $editing ? ($piece->entry_mode ?: 'form') : 'form');

    $action = $editing ? route('pieces.update', $piece) : route('pieces.store');
    $cancelUrl = route('pieces');
    $fieldClass = 'mt-1 w-full rounded-xl border-2 border-slate-400 bg-white text-slate-900 placeholder-slate-500 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100';
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        {{ $editing ? 'Modifier mouvement de pièce' : 'Nouveau mouvement de pièce' }}
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">Gestion Décharge et Réception / Retour avec import PDF intelligent.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="badge-decharge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">
                        <i class="fas fa-arrow-up-right-from-square"></i>
                        Décharge
                    </span>
                    <span id="badge-retour" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200 opacity-60">
                        <i class="fas fa-rotate-left"></i>
                        Réception / Retour
                    </span>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mx-6 mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 text-sm">
                <div class="font-semibold mb-1">Validation échouée</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="spare-part-workflow-form" class="p-6 space-y-6">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <input type="hidden" name="phase" id="phase-input" value="{{ $currentPhase }}">
            <input type="hidden" name="entry_mode" id="entry-mode-input" value="{{ $currentEntryMode }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <button type="button" class="phase-tab px-4 py-3 rounded-xl border text-left transition-all duration-200 hover:shadow-sm" data-phase="decharge">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-800"><i class="fas fa-arrow-up-right-from-square mr-2 text-rose-500"></i>Décharge</span>
                        <span class="text-xs text-slate-500">Sortie de stock</span>
                    </div>
                </button>
                <button type="button" class="phase-tab px-4 py-3 rounded-xl border text-left transition-all duration-200 hover:shadow-sm" data-phase="retour">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-800"><i class="fas fa-rotate-left mr-2 text-emerald-500"></i>Réception / Retour</span>
                        <span class="text-xs text-slate-500">Entrée ou retour atelier</span>
                    </div>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <button type="button" class="entry-mode-btn rounded-xl border px-4 py-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm text-left" data-mode="pdf">
                    <div class="font-semibold text-slate-800 mb-1"><i class="fas fa-file-pdf mr-2 text-red-500"></i>Importer un PDF</div>
                    <p class="text-sm text-slate-500">Le formulaire détaillé devient optionnel.</p>
                </button>
                <button type="button" class="entry-mode-btn rounded-xl border px-4 py-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm text-left" data-mode="form">
                    <div class="font-semibold text-slate-800 mb-1"><i class="fas fa-pen-to-square mr-2 text-blue-500"></i>Remplir le formulaire</div>
                    <p class="text-sm text-slate-500">Tous les champs métier requis sont affichés.</p>
                </button>
            </div>

            <div id="pdf-zone" class="hidden rounded-2xl border-2 border-dashed border-rose-200 bg-rose-50/40 p-6">
                <div class="text-center">
                    <div class="mx-auto mb-3 w-12 h-12 rounded-full bg-white border border-rose-200 flex items-center justify-center text-rose-600">
                        <i class="fas fa-cloud-arrow-up text-lg"></i>
                    </div>
                    <h3 class="font-semibold text-slate-800">Déposer le PDF de justificatif</h3>
                    <p class="text-sm text-slate-500 mt-1">Format accepté: PDF, taille max 15 Mo.</p>
                    <div class="mt-4">
                        <input type="file" accept="application/pdf" name="document_pdf" id="document_pdf" class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-rose-600 file:text-white hover:file:bg-rose-700 transition-colors">
                    </div>
                    @if ($editing && $piece->document_pdf_path)
                        <p class="mt-3 text-xs text-slate-500">
                            PDF actuel: <span class="font-medium">{{ basename($piece->document_pdf_path) }}</span>
                        </p>
                    @endif
                </div>
            </div>

            <div id="form-zone" class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-slate-50/40 p-5">
                    <h3 class="text-base font-semibold text-slate-800 mb-4">
                        <i class="fas fa-microchip mr-2 text-blue-500"></i>Section 1: Informations pièce
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Code pièce</label>
                            <input name="code" value="{{ old('code', $editing ? $piece->code : '') }}" placeholder="Ex: SP-2026-001" class="{{ $fieldClass }}">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Nom de la pièce</label>
                            <input name="name" value="{{ old('name', $editing ? $piece->name : '') }}" placeholder="Ex: Sonde ECG adulte" class="{{ $fieldClass }}">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Quantité</label>
                            <input type="number" min="0" name="quantity" value="{{ old('quantity', $editing ? $piece->quantity : '') }}" placeholder="Ex: 2" class="{{ $fieldClass }}">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Fournisseur</label>
                            <input name="supplier" value="{{ old('supplier', $editing ? $piece->supplier : '') }}" placeholder="Ex: Siemens Healthineers" class="{{ $fieldClass }}">
                        </div>
                        <div id="serial-number-wrap">
                            <label class="text-sm font-medium text-slate-700">Numéro de série (SN)</label>
                            <input name="serial_number" value="{{ old('serial_number', $editing ? $piece->serial_number : '') }}" placeholder="Ex: SN-AX7-99321" class="{{ $fieldClass }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-slate-700">Description</label>
                            <textarea rows="3" name="description" placeholder="Détail de la pièce ou du besoin..." class="{{ $fieldClass }}">{{ old('description', $editing ? $piece->description : '') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-slate-50/40 p-5">
                    <h3 class="text-base font-semibold text-slate-800 mb-4">
                        <i class="fas fa-stethoscope mr-2 text-indigo-500"></i>Section 2: Informations intervention
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div id="decharge-date-wrap">
                            <label class="text-sm font-medium text-slate-700">Date de décharge</label>
                            <input type="date" name="discharge_date" value="{{ old('discharge_date', $editing && $piece->discharge_date ? $piece->discharge_date->format('Y-m-d') : '') }}" class="{{ $fieldClass }}">
                        </div>
                        <div id="retour-date-wrap">
                            <label class="text-sm font-medium text-slate-700">Date de retour</label>
                            <input type="date" name="return_date" value="{{ old('return_date', $editing && $piece->return_date ? $piece->return_date->format('Y-m-d') : '') }}" class="{{ $fieldClass }}">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Service</label>
                            <select name="service_id" class="{{ $fieldClass }}">
                                <option value="">Sélectionner un service</option>
                                @foreach (($services ?? []) as $service)
                                    <option value="{{ $service->id }}" {{ (string) old('service_id', $editing ? $piece->service_id : '') === (string) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="retour-state-wrap">
                            <label class="text-sm font-medium text-slate-700">État</label>
                            <select name="condition_state" class="{{ $fieldClass }}">
                                <option value="">Choisir un état</option>
                                <option value="neuf" {{ old('condition_state', $editing ? $piece->condition_state : '') === 'neuf' ? 'selected' : '' }}>Neuf</option>
                                <option value="repare" {{ old('condition_state', $editing ? $piece->condition_state : '') === 'repare' ? 'selected' : '' }}>Réparé</option>
                                <option value="hs" {{ old('condition_state', $editing ? $piece->condition_state : '') === 'hs' ? 'selected' : '' }}>HS</option>
                            </select>
                        </div>
                        <div id="assistant-tech-wrap">
                            <label class="text-sm font-medium text-slate-700">Technicien assistant</label>
                            <select name="assistant_technician_id" class="{{ $fieldClass }}">
                                <option value="">Sélectionner</option>
                                @foreach (($technicians ?? []) as $technician)
                                    <option value="{{ $technician->id }}" {{ (string) old('assistant_technician_id', $editing ? $piece->assistant_technician_id : '') === (string) $technician->id ? 'selected' : '' }}>{{ $technician->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="retour-tech-wrap">
                            <label class="text-sm font-medium text-slate-700">Technicien</label>
                            <select name="return_technician_id" class="{{ $fieldClass }}">
                                <option value="">Sélectionner</option>
                                @foreach (($technicians ?? []) as $technician)
                                    <option value="{{ $technician->id }}" {{ (string) old('return_technician_id', $editing ? $piece->return_technician_id : '') === (string) $technician->id ? 'selected' : '' }}>{{ $technician->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="retour-comment-wrap" class="md:col-span-2">
                            <label class="text-sm font-medium text-slate-700">Commentaire</label>
                            <textarea rows="3" name="comment" class="{{ $fieldClass }}">{{ old('comment', $editing ? $piece->comment : '') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-slate-50/40 p-5">
                    <h3 class="text-base font-semibold text-slate-800 mb-4">
                        <i class="fas fa-signature mr-2 text-amber-500"></i>Section 3: Signature / validation
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div id="action-user-wrap">
                            <label class="text-sm font-medium text-slate-700">Utilisateur ayant effectué l'action</label>
                            <select name="action_user_id" class="{{ $fieldClass }}">
                                <option value="">Sélectionner</option>
                                @foreach (($actionUsers ?? []) as $user)
                                    <option value="{{ $user->id }}" {{ (string) old('action_user_id', $editing ? $piece->action_user_id : auth()->id()) === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="major-wrap">
                            <label class="text-sm font-medium text-slate-700">Major signataire</label>
                            <select name="major_signer_id" class="{{ $fieldClass }}">
                                <option value="">Sélectionner</option>
                                @foreach (($majors ?? []) as $major)
                                    <option value="{{ $major->id }}" {{ (string) old('major_signer_id', $editing ? $piece->major_signer_id : '') === (string) $major->id ? 'selected' : '' }}>{{ $major->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ $cancelUrl }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">Annuler</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-semibold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-sm hover:shadow transition-all duration-200">
                    <i class="fas fa-floppy-disk mr-2"></i>
                    {{ $editing ? 'Mettre à jour' : 'Enregistrer' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('spare-part-workflow-form');
    if (!form) {
        return;
    }

    var phaseInput = document.getElementById('phase-input');
    var entryModeInput = document.getElementById('entry-mode-input');

    var phaseTabs = form.querySelectorAll('.phase-tab');
    var modeButtons = form.querySelectorAll('.entry-mode-btn');

    var badgeDecharge = document.getElementById('badge-decharge');
    var badgeRetour = document.getElementById('badge-retour');

    var formZone = document.getElementById('form-zone');
    var pdfZone = document.getElementById('pdf-zone');

    var dechargeFields = ['serial-number-wrap', 'decharge-date-wrap', 'assistant-tech-wrap', 'action-user-wrap', 'major-wrap'];
    var retourFields = ['retour-date-wrap', 'retour-state-wrap', 'retour-tech-wrap', 'retour-comment-wrap'];

    function setActiveStyles(buttons, predicate, activeClass, inactiveClass) {
        buttons.forEach(function (button) {
            if (predicate(button)) {
                button.classList.add.apply(button.classList, activeClass);
                button.classList.remove.apply(button.classList, inactiveClass);
            } else {
                button.classList.remove.apply(button.classList, activeClass);
                button.classList.add.apply(button.classList, inactiveClass);
            }
        });
    }

    function setBlockVisibility(ids, visible) {
        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) {
                return;
            }
            el.classList.toggle('hidden', !visible);
        });
    }

    function updateUi() {
        var phase = phaseInput.value || 'decharge';
        var mode = entryModeInput.value || 'form';

        setActiveStyles(
            phaseTabs,
            function (btn) { return btn.dataset.phase === phase; },
            ['border-blue-500', 'bg-blue-50', 'shadow-sm'],
            ['border-slate-200', 'bg-white']
        );

        setActiveStyles(
            modeButtons,
            function (btn) { return btn.dataset.mode === mode; },
            ['border-indigo-500', 'bg-indigo-50', 'shadow-sm'],
            ['border-slate-200', 'bg-white']
        );

        badgeDecharge.classList.toggle('opacity-60', phase !== 'decharge');
        badgeRetour.classList.toggle('opacity-60', phase !== 'retour');

        formZone.classList.toggle('hidden', mode === 'pdf');
        pdfZone.classList.toggle('hidden', mode !== 'pdf');

        setBlockVisibility(dechargeFields, phase === 'decharge');
        setBlockVisibility(retourFields, phase === 'retour');
    }

    phaseTabs.forEach(function (button) {
        button.addEventListener('click', function () {
            phaseInput.value = button.dataset.phase;
            updateUi();
        });
    });

    modeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            entryModeInput.value = button.dataset.mode;
            updateUi();
        });
    });

    updateUi();
})();
</script>
