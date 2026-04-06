<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nouvelle Réclamation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="bg-white rounded-xl shadow-md p-6 md:p-8">
            <h1 class="text-2xl font-bold text-gray-800">Nouvelle Réclamation</h1>
            <p class="text-sm text-gray-600 mt-2">Déclarez une panne ou un incident sur un équipement biomédical.</p>

            @if(($services ?? collect())->isNotEmpty())
                <div class="mt-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Réclamation rapide par service</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($services as $service)
                            <button type="button"
                                    class="service-quick-btn px-3 py-1.5 rounded-lg border border-blue-200 text-blue-700 text-xs font-semibold hover:bg-blue-50"
                                    data-service-id="{{ $service->id }}">
                                {{ $service->code }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('operator.defects.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de l'utilisateur</label>
                    <input type="text" value="{{ auth()->user()?->name ?: auth()->user()?->login ?: 'Utilisateur' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Service</label>
                    <select id="service_id" name="service_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        <option value="">Choisir un service</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ (string) old('service_id') === (string) $service->id ? 'selected' : '' }}>{{ $service->code }} - {{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Équipement biomédical</label>
                    <select id="equipment_id" name="equipment_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        <option value="">Choisir un équipement</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input name="room_number" value="{{ old('room_number') }}" placeholder="Salle (optionnel)" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <select name="priority" class="px-4 py-2 border border-gray-300 rounded-lg" required>
                            <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normale</option>
                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgente</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description de la panne</label>
                    <textarea name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700">
                            📷
                        </span>
                        Ajouter une photo / pièce jointe
                    </label>
                    <input id="attachments_upload" type="file" name="attachments[]" accept=".jpg,.jpeg,.png,.webp,image/*" multiple
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white">
                    <p class="text-xs text-gray-500 mt-1">Vous pouvez prendre une photo ou ajouter jusqu'à 5 images (jpg, png, webp, max 4MB chacune).</p>

                    <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
                        <p class="text-xs font-semibold text-slate-700 mb-2">Caméra (ordinateur)</p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button" id="start_camera_btn" class="px-3 py-1.5 bg-sky-600 text-white rounded-lg text-sm font-semibold hover:bg-sky-700">Ouvrir caméra</button>
                            <button type="button" id="take_photo_btn" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50" disabled>Prendre photo</button>
                            <button type="button" id="retake_photo_btn" class="px-3 py-1.5 bg-gray-600 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 hidden">Reprendre</button>
                        </div>

                        <div id="camera_box" class="mt-3 hidden">
                            <video id="camera_preview" class="w-full max-h-56 rounded-lg bg-black" autoplay playsinline muted></video>
                            <canvas id="camera_canvas" class="hidden"></canvas>
                        </div>

                        <p id="camera_status" class="text-xs text-slate-500 mt-2">Aucune photo prise.</p>
                        <input id="attachments_camera_desktop" type="file" name="attachments[]" multiple class="hidden">
                        <input id="attachments_camera_fallback" type="file" accept="image/*" capture="environment" class="hidden">
                    </div>

                    @error('attachments')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    @error('attachments.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <div id="attachmentsPreview" class="mt-2 text-xs text-gray-600"></div>
                </div>

                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">Envoyer la réclamation</button>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const serviceSelect = document.getElementById('service_id');
    const equipmentSelect = document.getElementById('equipment_id');
    const oldEquipmentId = '{{ old('equipment_id', '') }}';
    const quickServiceButtons = document.querySelectorAll('.service-quick-btn');
    const attachmentsInput = document.getElementById('attachments_upload');
    const attachmentsPreview = document.getElementById('attachmentsPreview');
    const startCameraBtn = document.getElementById('start_camera_btn');
    const takePhotoBtn = document.getElementById('take_photo_btn');
    const retakePhotoBtn = document.getElementById('retake_photo_btn');
    const cameraBox = document.getElementById('camera_box');
    const cameraPreview = document.getElementById('camera_preview');
    const cameraCanvas = document.getElementById('camera_canvas');
    const cameraStatus = document.getElementById('camera_status');
    const desktopCameraInput = document.getElementById('attachments_camera_desktop');
    const cameraFallbackInput = document.getElementById('attachments_camera_fallback');
    let cameraStream = null;
    let capturedFiles = [];

    function isLikelyMobile() {
        return /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent || '');
    }

    function renderEquipmentOptions(items, selectedId = '') {
        equipmentSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Choisir un équipement';
        equipmentSelect.appendChild(placeholder);

        if (!Array.isArray(items) || items.length === 0) {
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = 'Aucun équipement biomédical pour ce service';
            emptyOption.disabled = true;
            equipmentSelect.appendChild(emptyOption);
            equipmentSelect.value = '';
            return;
        }

        items.forEach(item => {
            const option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = `${item.inventory_number_current || '-'} - ${item.designation || ''}`;
            if (selectedId && String(selectedId) === String(item.id)) {
                option.selected = true;
            }
            equipmentSelect.appendChild(option);
        });
    }

    async function loadEquipmentsByService(serviceId, selectedEquipmentId = '') {
        if (!serviceId) {
            renderEquipmentOptions([], '');
            return;
        }

        equipmentSelect.innerHTML = '<option value="">Chargement des équipements...</option>';

        try {
            const response = await fetch(`/dashboard/operator/defects/services/${serviceId}/equipments`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                renderEquipmentOptions([], '');
                return;
            }

            const data = await response.json();
            renderEquipmentOptions(data.items || [], selectedEquipmentId);
        } catch (error) {
            renderEquipmentOptions([], '');
        }
    }

    serviceSelect.addEventListener('change', function () {
        loadEquipmentsByService(this.value);
    });

    quickServiceButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const serviceId = this.getAttribute('data-service-id') || '';
            serviceSelect.value = serviceId;
            loadEquipmentsByService(serviceId);
        });
    });

    if (serviceSelect.value) {
        loadEquipmentsByService(serviceSelect.value, oldEquipmentId);
    } else if (quickServiceButtons.length === 1) {
        const uniqueServiceId = quickServiceButtons[0].getAttribute('data-service-id') || '';
        if (uniqueServiceId) {
            serviceSelect.value = uniqueServiceId;
            loadEquipmentsByService(uniqueServiceId, oldEquipmentId);
        } else {
            renderEquipmentOptions([], '');
        }
    } else {
        renderEquipmentOptions([], '');
    }

    function syncDesktopCapturedFiles() {
        if (!desktopCameraInput) {
            return;
        }

        if (typeof DataTransfer === 'undefined') {
            if (cameraStatus) {
                cameraStatus.textContent = 'Capture non supportée sur cet appareil. Utilisez « Joindre des images ».';
            }
            return;
        }

        try {
            const transfer = new DataTransfer();
            capturedFiles.forEach((file) => transfer.items.add(file));
            desktopCameraInput.files = transfer.files;
        } catch (error) {
            if (cameraStatus) {
                cameraStatus.textContent = 'Impossible d’ajouter la photo capturée automatiquement. Utilisez « Joindre des images ».';
            }
        }
    }

    function compressImageToJpeg(file) {
        return new Promise((resolve) => {
            if (!file || !file.type || !file.type.startsWith('image/')) {
                resolve(file);
                return;
            }

            const reader = new FileReader();
            reader.onload = function () {
                const image = new Image();
                image.onload = function () {
                    const maxDimension = 1920;
                    let width = image.width;
                    let height = image.height;

                    if (width > maxDimension || height > maxDimension) {
                        const ratio = Math.min(maxDimension / width, maxDimension / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const context = canvas.getContext('2d');

                    if (!context) {
                        resolve(file);
                        return;
                    }

                    context.drawImage(image, 0, 0, width, height);

                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            resolve(file);
                            return;
                        }

                        const compressed = new File(
                            [blob],
                            `major-complaint-${Date.now()}.jpg`,
                            { type: 'image/jpeg' }
                        );
                        resolve(compressed);
                    }, 'image/jpeg', 0.82);
                };

                image.onerror = function () {
                    resolve(file);
                };

                image.src = String(reader.result || '');
            };

            reader.onerror = function () {
                resolve(file);
            };

            reader.readAsDataURL(file);
        });
    }

    function updateAttachmentsPreview() {
        if (!attachmentsPreview) {
            return;
        }

        const uploaded = Array.from(attachmentsInput?.files || []);
        const cameraCaptured = Array.from(desktopCameraInput?.files || []);
        const files = [...uploaded, ...cameraCaptured];

        if (files.length === 0) {
            attachmentsPreview.textContent = '';
            return;
        }

        attachmentsPreview.innerHTML = files
            .map((file, index) => `${index + 1}. ${file.name}`)
            .join('<br>');
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }

        if (cameraPreview) {
            cameraPreview.srcObject = null;
        }

        cameraBox?.classList.add('hidden');
        if (takePhotoBtn) takePhotoBtn.disabled = true;
    }

    async function startCamera() {
        stopCamera();

        // On mobile, always use native camera capture input
        if (isLikelyMobile()) {
            if (cameraStatus) {
                cameraStatus.textContent = 'Ouverture caméra mobile...';
            }
            cameraFallbackInput?.click();
            return;
        }

        // getUserMedia requires a secure context (HTTPS or localhost/127.0.0.1).
        // On plain HTTP with a LAN IP the browser removes the API entirely.
        const hasMediaApi = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

        if (!hasMediaApi) {
            // Offer the file-picker fallback so the user can still attach a photo
            if (cameraStatus) {
                cameraStatus.textContent = 'Caméra en direct indisponible (HTTP non sécurisé). Sélectionnez une photo via le sélecteur de fichiers.';
            }
            cameraFallbackInput?.click();
            return;
        }

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' },
                audio: false,
            });

            if (cameraPreview) {
                cameraPreview.srcObject = cameraStream;
            }

            cameraBox?.classList.remove('hidden');
            if (takePhotoBtn) takePhotoBtn.disabled = false;
            if (retakePhotoBtn) retakePhotoBtn.classList.add('hidden');
            if (cameraStatus) cameraStatus.textContent = 'Caméra active. Cliquez sur « Prendre photo ».';
        } catch (error) {
            // getUserMedia existed but failed — give a clear reason
            const msg = (error.name === 'NotAllowedError')
                ? 'Accès caméra refusé. Autorisez la caméra dans les paramètres du navigateur.'
                : (error.name === 'NotFoundError')
                    ? 'Aucune caméra détectée sur cet appareil.'
                    : 'Caméra indisponible (' + error.message + '). Sélection de fichier ouverte à la place.';
            if (cameraStatus) cameraStatus.textContent = msg;
            // Open file picker as a last resort
            cameraFallbackInput?.click();
        }
    }

    function takePhoto() {
        if (!cameraPreview || !cameraCanvas) {
            return;
        }

        const width = cameraPreview.videoWidth || 1280;
        const height = cameraPreview.videoHeight || 720;
        if (width <= 0 || height <= 0) {
            return;
        }

        cameraCanvas.width = width;
        cameraCanvas.height = height;
        const context = cameraCanvas.getContext('2d');
        if (!context) {
            return;
        }

        context.drawImage(cameraPreview, 0, 0, width, height);

        cameraCanvas.toBlob(function (blob) {
            if (!blob) {
                return;
            }

            const file = new File([blob], `major-complaint-${Date.now()}.jpg`, { type: 'image/jpeg' });
            capturedFiles = [file];
            syncDesktopCapturedFiles();
            updateAttachmentsPreview();
            if (cameraStatus) cameraStatus.textContent = `Photo prête: ${file.name}`;
            if (retakePhotoBtn) retakePhotoBtn.classList.remove('hidden');
            stopCamera();
        }, 'image/jpeg', 0.92);
    }

    startCameraBtn?.addEventListener('click', startCamera);
    takePhotoBtn?.addEventListener('click', takePhoto);
    retakePhotoBtn?.addEventListener('click', function () {
        capturedFiles = [];
        syncDesktopCapturedFiles();
        updateAttachmentsPreview();
        startCamera();
    });

    cameraFallbackInput?.addEventListener('change', async function () {
        const file = cameraFallbackInput.files && cameraFallbackInput.files[0] ? cameraFallbackInput.files[0] : null;
        if (!file) {
            return;
        }

        const compressedFile = await compressImageToJpeg(file);
        capturedFiles = [compressedFile];
        syncDesktopCapturedFiles();
        updateAttachmentsPreview();
        if (cameraStatus) cameraStatus.textContent = `Photo prête: ${compressedFile.name}`;
        if (retakePhotoBtn) retakePhotoBtn.classList.remove('hidden');
        stopCamera();

        cameraFallbackInput.value = '';
    });

    if (attachmentsInput && attachmentsPreview) {
        attachmentsInput.addEventListener('change', function () {
            updateAttachmentsPreview();
        });
    }

    window.addEventListener('beforeunload', stopCamera);

    if (attachmentsPreview) {
        const files = Array.from(attachmentsInput?.files || []);
            if (files.length === 0) {
                attachmentsPreview.textContent = '';
            } else {
                updateAttachmentsPreview();
            }
    }
});
</script>
</body>
</html>
