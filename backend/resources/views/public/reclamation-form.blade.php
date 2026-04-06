<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BioTrack GST - Réclamation {{ $service->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100">
    @php
        $equipmentOptions = collect($equipments ?? [])->map(function ($equipment) {
            $inventory = trim((string) ($equipment->inventory_number_current ?? ''));
            $designation = trim((string) ($equipment->designation ?? ''));
            $label = trim($inventory . ' - ' . $designation, ' -');

            if ($label === '') {
                $label = 'Équipement #' . (int) ($equipment->id ?? 0);
            }

            return [
                'id' => (int) ($equipment->id ?? 0),
                'inventory' => $inventory,
                'designation' => $designation,
                'label' => $label,
            ];
        })->filter(fn ($item) => (int) ($item['id'] ?? 0) > 0)->values();

        $selectedEquipmentId = (int) old('equipment_id', 0);
    @endphp
    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="rounded-2xl border border-sky-100 bg-white shadow-xl overflow-hidden">
            <div class="px-6 md:px-8 py-6 bg-gradient-to-r from-sky-700 via-cyan-700 to-blue-700 text-white">
                <img src="{{ asset('images/logo-gst.png') }}?v={{ filemtime(public_path('images/logo-gst.png')) }}" alt="BioTrack GST" class="h-14 w-auto object-contain mb-3">
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Formulaire de Réclamation Biomédicale</h1>
                <p class="text-sm text-sky-100 mt-2">Service: <span class="font-semibold">{{ $service->name }}</span> ({{ $service->code }})</p>
            </div>

            <div class="p-6 md:p-8">
                <div class="rounded-xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-cyan-800 text-sm">
                    Merci de renseigner précisément l'équipement et la panne pour accélérer l'intervention de l'équipe biomédicale.
                </div>

                @if (session('success'))
                    <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.reclamation.store', ['service_code' => ($serviceToken ?? ($service->code ?: ('ID-' . $service->id)))]) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf

                        <div id="async_feedback" class="hidden rounded-lg px-4 py-3 text-sm"></div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nom de l'utilisateur</label>
                        <input type="text" name="reported_by_name" value="{{ old('reported_by_name') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-sky-500 focus:ring-2 focus:ring-sky-100" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Service</label>
                        <input type="text" value="{{ $service->name }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg bg-slate-100" readonly>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Équipement</label>
                        <input type="hidden" name="equipment_id" id="equipment_id" value="{{ $selectedEquipmentId > 0 ? $selectedEquipmentId : '' }}" required>
                        <div class="relative">
                            <input type="text" id="equipment_search" placeholder="Rechercher par N° inventaire ou nom d'équipement" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-sky-500 focus:ring-2 focus:ring-sky-100" autocomplete="off" required>
                            <div id="equipment_results" class="hidden absolute z-30 left-0 right-0 mt-1 max-h-72 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg"></div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Numéro de salle</label>
                        <input type="text" name="room_number" value="{{ old('room_number') }}" placeholder="Numero de salle / De chambre / De box" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Description de la panne</label>
                        <textarea name="description" rows="5" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-sky-500 focus:ring-2 focus:ring-sky-100" required>{{ old('description') }}</textarea>
                        <p class="text-xs text-slate-500 mt-1">Vous pouvez ajouter des photos via le champ <strong>Pièces jointes</strong> juste en dessous (jusqu'à 5 images).</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pièces jointes</label>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Joindre des images</label>
                            <input id="attachments_upload" type="file" name="attachments[]" accept=".jpg,.jpeg,.png,.webp,image/*" multiple class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                        </div>

                        <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-semibold text-slate-700 mb-2">Caméra (ordinateur)</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" id="start_camera_btn" class="px-3 py-1.5 bg-sky-600 text-white rounded-lg text-sm font-semibold hover:bg-sky-700">Ouvrir caméra</button>
                                <button type="button" id="take_photo_btn" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50" disabled>Prendre photo</button>
                                <button type="button" id="retake_photo_btn" class="px-3 py-1.5 border border-slate-300 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-100 hidden">Reprendre</button>
                            </div>

                            <div class="mt-3 hidden" id="camera_wrapper">
                                <video id="camera_preview" autoplay playsinline class="w-full max-w-md rounded-lg border border-slate-300 bg-black"></video>
                            </div>

                            <p id="camera_status" class="mt-2 text-xs text-slate-600">Aucune photo prise.</p>
                            <img id="camera_selected_preview" class="mt-2 hidden w-28 h-28 rounded-lg border border-slate-300 object-cover" alt="Photo sélectionnée">

                            <input id="attachments_camera_desktop" type="file" name="attachments[]" multiple class="hidden">
                            <input id="attachments_camera_fallback" type="file" accept="image/*" capture="environment" class="hidden">
                        </div>

                        <p class="text-xs text-slate-500 mt-1">Maximum 5 images, 4MB par image.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Priorité</label>
                        <select name="priority" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:border-sky-500 focus:ring-2 focus:ring-sky-100" required>
                            <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normale</option>
                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgente</option>
                        </select>
                    </div>

                    <div class="pt-2 flex flex-wrap gap-3">
                        <button type="submit" class="px-6 py-2 bg-gradient-to-r from-sky-600 to-cyan-600 text-white rounded-lg hover:from-sky-700 hover:to-cyan-700 font-semibold">Envoyer la réclamation</button>
                        <a href="{{ route('public.reclamation.index') }}" class="px-6 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 font-semibold">Changer de service</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const options = @json($equipmentOptions);
            const hiddenInput = document.getElementById('equipment_id');
            const searchInput = document.getElementById('equipment_search');
            const resultsBox = document.getElementById('equipment_results');
            const form = searchInput ? searchInput.closest('form') : null;
                const asyncFeedback = document.getElementById('async_feedback');
                const submitButton = form ? form.querySelector('button[type="submit"]') : null;
            const startCameraBtn = document.getElementById('start_camera_btn');
            const takePhotoBtn = document.getElementById('take_photo_btn');
            const retakePhotoBtn = document.getElementById('retake_photo_btn');
            const cameraWrapper = document.getElementById('camera_wrapper');
            const cameraPreview = document.getElementById('camera_preview');
            const cameraStatus = document.getElementById('camera_status');
            const cameraSelectedPreview = document.getElementById('camera_selected_preview');
            const desktopCameraInput = document.getElementById('attachments_camera_desktop');
            const cameraFallbackInput = document.getElementById('attachments_camera_fallback');
            const isMobileDevice = /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent || '');
            const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);
            const hasSecureCameraContext = window.isSecureContext || isLocalhost;
            const backendMaxImageBytes = 4 * 1024 * 1024;
            const optimizeThresholdBytes = Math.floor(backendMaxImageBytes * 0.9);

            let cameraStream = null;
            let capturedFiles = [];
            let cameraPreviewUrl = null;

            function syncDesktopCapturedFiles() {
                if (!desktopCameraInput || typeof DataTransfer === 'undefined') {
                    return;
                }

                const dt = new DataTransfer();
                capturedFiles.forEach(function (file) {
                    dt.items.add(file);
                });

                desktopCameraInput.files = dt.files;
            }

            function updateCameraSelectionUI(file, sourceLabel) {
                if (cameraPreviewUrl) {
                    URL.revokeObjectURL(cameraPreviewUrl);
                    cameraPreviewUrl = null;
                }

                if (!file) {
                    if (cameraStatus) {
                        cameraStatus.className = 'mt-2 text-xs text-slate-600';
                        cameraStatus.textContent = 'Aucune photo prise.';
                    }

                    if (cameraSelectedPreview) {
                        cameraSelectedPreview.src = '';
                        cameraSelectedPreview.classList.add('hidden');
                    }

                    retakePhotoBtn?.classList.add('hidden');
                    return;
                }

                if (cameraStatus) {
                    cameraStatus.className = 'mt-2 text-xs text-emerald-700';
                    cameraStatus.textContent = `Photo sélectionnée (${sourceLabel}).`;
                }

                if (cameraSelectedPreview) {
                    cameraPreviewUrl = URL.createObjectURL(file);
                    cameraSelectedPreview.src = cameraPreviewUrl;
                    cameraSelectedPreview.classList.remove('hidden');
                }

                retakePhotoBtn?.classList.remove('hidden');
            }

            function showCameraError(message) {
                if (!asyncFeedback) {
                    return;
                }

                asyncFeedback.className = 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm';
                asyncFeedback.textContent = message;
            }

            function openFallbackPicker() {
                if (!cameraFallbackInput) {
                    return;
                }

                cameraFallbackInput.value = '';
                cameraFallbackInput.click();
            }

            async function startCamera() {
                if (!hasSecureCameraContext && !isMobileDevice) {
                    showCameraError('Caméra navigateur bloquée sur URL LAN en HTTP. Utilisez 127.0.0.1, ou activez HTTPS pour l\'accès caméra sur le réseau local.');

                    if (cameraStatus) {
                        cameraStatus.className = 'mt-2 text-xs text-amber-700';
                        cameraStatus.textContent = 'Mode LAN HTTP: caméra directe indisponible (restriction du navigateur).';
                    }

                    return;
                }

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    openFallbackPicker();
                    return;
                }

                try {
                    cameraStream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment' },
                        audio: false
                    });

                    if (cameraPreview) {
                        cameraPreview.srcObject = cameraStream;
                    }

                    cameraWrapper?.classList.remove('hidden');
                    takePhotoBtn?.removeAttribute('disabled');
                } catch (error) {
                    if (isMobileDevice) {
                        openFallbackPicker();
                    } else {
                        showCameraError('Impossible d\'ouvrir la caméra. Vérifiez les permissions du navigateur.');
                    }
                }
            }

            function stopCamera() {
                if (cameraStream) {
                    cameraStream.getTracks().forEach(function (track) {
                        track.stop();
                    });
                    cameraStream = null;
                }

                if (cameraPreview) {
                    cameraPreview.srcObject = null;
                }

                cameraWrapper?.classList.add('hidden');
                takePhotoBtn?.setAttribute('disabled', 'disabled');
            }

            function takePhoto() {
                if (!cameraPreview || !cameraStream) {
                    openFallbackPicker();
                    return;
                }

                const width = cameraPreview.videoWidth || 1280;
                const height = cameraPreview.videoHeight || 720;
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const context = canvas.getContext('2d');
                if (!context) {
                    return;
                }

                context.drawImage(cameraPreview, 0, 0, width, height);

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        showCameraError('Capture impossible. Réessayez.');
                        return;
                    }

                    const file = new File([blob], `camera-${Date.now()}.jpg`, { type: 'image/jpeg' });
                    capturedFiles = [file];
                    syncDesktopCapturedFiles();
                    updateCameraSelectionUI(file, 'caméra');
                    stopCamera();
                }, 'image/jpeg', 0.92);
            }

            startCameraBtn?.addEventListener('click', startCamera);
            takePhotoBtn?.addEventListener('click', takePhoto);
            retakePhotoBtn?.addEventListener('click', function () {
                capturedFiles = [];
                syncDesktopCapturedFiles();
                updateCameraSelectionUI(null);
                startCamera();
            });

            cameraFallbackInput?.addEventListener('change', function () {
                const fallbackFile = cameraFallbackInput.files && cameraFallbackInput.files[0] ? cameraFallbackInput.files[0] : null;
                if (!fallbackFile) {
                    return;
                }

                capturedFiles = [fallbackFile];
                syncDesktopCapturedFiles();
                updateCameraSelectionUI(fallbackFile, 'fichier');
                stopCamera();
            });

            if (!hiddenInput || !searchInput || !resultsBox || !form) {
                return;
            }

            function normalize(value) {
                return (value || '').toString().toLowerCase().trim();
            }

            function applySelection(item) {
                if (!item) {
                    return;
                }

                hiddenInput.value = item.id;
                searchInput.value = item.label || (item.inventory + ' - ' + item.designation);
                searchInput.setCustomValidity('');
                resultsBox.classList.add('hidden');
            }

            function renderResults(term) {
                const query = normalize(term);
                const filtered = options.filter(function (item) {
                    const haystack = normalize((item.inventory || '') + ' ' + (item.designation || '') + ' ' + (item.label || ''));
                    return query === '' || haystack.includes(query);
                }).slice(0, 40);

                resultsBox.innerHTML = '';
                if (filtered.length === 0) {
                    resultsBox.classList.add('hidden');
                    return;
                }

                filtered.forEach(function (item) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'w-full text-left px-3 py-2 text-sm text-slate-700 hover:bg-sky-50';
                    btn.textContent = item.label || (item.inventory + ' - ' + item.designation);
                    btn.addEventListener('mousedown', function (event) {
                        event.preventDefault();
                        applySelection(item);
                    });
                    btn.addEventListener('click', function () {
                        applySelection(item);
                    });
                    resultsBox.appendChild(btn);
                });

                resultsBox.classList.remove('hidden');
            }

            function resolveFromInput() {
                const q = normalize(searchInput.value);
                if (!q) {
                    hiddenInput.value = '';
                    return;
                }

                const exact = options.find(function (item) {
                    return normalize(item.label) === q || normalize(item.inventory) === q;
                });

                if (exact) {
                    applySelection(exact);
                    return;
                }

                const partial = options.filter(function (item) {
                    const haystack = normalize((item.inventory || '') + ' ' + (item.designation || '') + ' ' + (item.label || ''));
                    return haystack.includes(q);
                });

                if (partial.length === 1) {
                    applySelection(partial[0]);
                }
            }

            function loadImageFromFile(file) {
                return new Promise(function (resolve, reject) {
                    const reader = new FileReader();
                    reader.onload = function () {
                        const image = new Image();
                        image.onload = function () {
                            resolve(image);
                        };
                        image.onerror = reject;
                        image.src = reader.result;
                    };
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });
            }

            function canvasToBlob(canvas, type, quality) {
                return new Promise(function (resolve) {
                    canvas.toBlob(function (blob) {
                        resolve(blob);
                    }, type, quality);
                });
            }

            async function optimizeImageFile(file) {
                const isImage = file && typeof file.type === 'string' && file.type.startsWith('image/');
                if (!isImage) {
                    return file;
                }

                if (file.size <= optimizeThresholdBytes) {
                    return file;
                }

                try {
                    const image = await loadImageFromFile(file);
                    const maxSide = isMobileDevice ? 1024 : 1600;
                    const outputType = file.type === 'image/webp' ? 'image/webp' : 'image/jpeg';
                    const quality = isMobileDevice ? 0.68 : 0.82;
                    let width = image.width;
                    let height = image.height;

                    if (Math.max(width, height) > maxSide) {
                        const ratio = width / height;
                        if (ratio >= 1) {
                            width = maxSide;
                            height = Math.round(maxSide / ratio);
                        } else {
                            height = maxSide;
                            width = Math.round(maxSide * ratio);
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const context = canvas.getContext('2d');
                    if (!context) {
                        return file;
                    }

                    context.imageSmoothingEnabled = true;
                    context.imageSmoothingQuality = isMobileDevice ? 'medium' : 'high';
                    context.drawImage(image, 0, 0, width, height);
                    const blob = await canvasToBlob(canvas, outputType, quality);

                    if (!blob || blob.size >= file.size) {
                        return file;
                    }

                    const extension = outputType === 'image/webp' ? '.webp' : '.jpg';
                    const optimizedName = (file.name || `image-${Date.now()}`).replace(/\.[^.]+$/, '') + extension;
                    return new File([blob], optimizedName, { type: outputType });
                } catch (error) {
                    return file;
                }
            }

            const initialId = parseInt(hiddenInput.value || '0', 10);
            if (initialId > 0) {
                const initial = options.find(function (item) { return item.id === initialId; });
                if (initial) {
                    searchInput.value = initial.label || (initial.inventory + ' - ' + initial.designation);
                }
            }

            searchInput.addEventListener('focus', function () {
                renderResults(searchInput.value);
            });

            searchInput.addEventListener('input', function () {
                hiddenInput.value = '';
                searchInput.setCustomValidity('');
                renderResults(searchInput.value);
            });

            searchInput.addEventListener('blur', function () {
                resolveFromInput();
                setTimeout(function () {
                    resultsBox.classList.add('hidden');
                }, 200);
            });

            resultsBox.addEventListener('mousedown', function (event) {
                event.preventDefault();
            });

            form.addEventListener('submit', async function (event) {
                if (!hiddenInput.value) {
                    resolveFromInput();
                }

                if (!hiddenInput.value) {
                    event.preventDefault();
                    searchInput.setCustomValidity('Veuillez sélectionner un équipement de la liste.');
                    searchInput.reportValidity();
                    renderResults(searchInput.value);
                    return;
                }

                searchInput.setCustomValidity('');

                if (!window.fetch) {
                    return;
                }

                event.preventDefault();

                if (asyncFeedback) {
                    asyncFeedback.classList.add('hidden');
                    asyncFeedback.className = 'hidden rounded-lg px-4 py-3 text-sm';
                    asyncFeedback.textContent = '';
                }

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-70', 'cursor-not-allowed');
                    submitButton.textContent = 'Préparation des images...';
                }

                try {
                    const originalFormData = new FormData(form);
                    const originalAttachments = originalFormData.getAll('attachments[]');
                    const hasFilesNeedingOptimization = originalAttachments.some(function (entry) {
                        return entry instanceof File
                            && !!entry.name
                            && typeof entry.type === 'string'
                            && entry.type.startsWith('image/')
                            && entry.size > optimizeThresholdBytes;
                    });

                    let optimizedAttachments = originalAttachments;

                    if (hasFilesNeedingOptimization) {
                        optimizedAttachments = await Promise.all(
                            originalAttachments.map(async function (entry, index) {
                                if (!(entry instanceof File) || !entry.name) {
                                    return entry;
                                }

                                if (submitButton && originalAttachments.length > 0) {
                                    submitButton.textContent = `Préparation des images... (${index + 1}/${originalAttachments.length})`;
                                }

                                return optimizeImageFile(entry);
                            })
                        );
                    }

                    const requestData = new FormData();
                    originalFormData.forEach(function (value, key) {
                        if (key !== 'attachments[]') {
                            requestData.append(key, value);
                        }
                    });

                    optimizedAttachments.forEach(function (entry) {
                        requestData.append('attachments[]', entry);
                    });

                    if (submitButton) {
                        submitButton.textContent = 'Envoi en cours...';
                    }

                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: requestData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        if (asyncFeedback) {
                            asyncFeedback.className = 'rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700 text-sm';
                            asyncFeedback.textContent = 'Réclamation envoyée avec succès. L\'équipe du dashboard est notifiée en temps réel.';
                        }

                        form.reset();
                        hiddenInput.value = '';
                        searchInput.value = '';
                        resultsBox.innerHTML = '';
                        resultsBox.classList.add('hidden');
                        capturedFiles = [];
                        syncDesktopCapturedFiles();
                        updateCameraSelectionUI(null);
                        stopCamera();
                        return;
                    }

                    if (response.status === 422) {
                        const payload = await response.json();
                        const errors = Object.values(payload.errors || {}).flat();
                        if (asyncFeedback) {
                            asyncFeedback.className = 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm';
                            asyncFeedback.innerHTML = `<ul class="list-disc list-inside">${errors.map(function (item) {
                                return `<li>${item}</li>`;
                            }).join('')}</ul>`;
                        }
                        return;
                    }

                    if (asyncFeedback) {
                        asyncFeedback.className = 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm';
                        asyncFeedback.textContent = 'Erreur lors de l\'envoi. Veuillez réessayer.';
                    }
                } catch (error) {
                    if (asyncFeedback) {
                        asyncFeedback.className = 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm';
                        asyncFeedback.textContent = 'Connexion interrompue. Vérifiez le réseau puis réessayez.';
                    }
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.classList.remove('opacity-70', 'cursor-not-allowed');
                        submitButton.textContent = 'Envoyer la réclamation';
                    }
                }
            });
        });
    </script>
</body>
</html>
