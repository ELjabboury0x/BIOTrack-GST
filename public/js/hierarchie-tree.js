(function () {
    var hierarchyRegistered = false;

    function normalize(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function selfText(node) {
        if (!node || typeof node !== 'object') {
            return '';
        }

        return normalize([
            node.name,
            node.code,
            node.service_code,
            node.responsable,
        ].join(' '));
    }

    function nodeMatches(node, normalizedQuery) {
        if (normalizedQuery === '') {
            return true;
        }

        if (selfText(node).includes(normalizedQuery)) {
            return true;
        }

        var children = Array.isArray(node && node.children) ? node.children : [];
        for (var i = 0; i < children.length; i += 1) {
            if (nodeMatches(children[i], normalizedQuery)) {
                return true;
            }
        }

        return false;
    }

    function registerHierarchyAlpine() {
        if (!window.Alpine || hierarchyRegistered) {
            return;
        }

        var Alpine = window.Alpine;

        var hasHierarchyStore = false;
        try {
            hasHierarchyStore = !!Alpine.store('hierarchyTree');
        } catch (error) {
            hasHierarchyStore = false;
        }

        if (!hasHierarchyStore) {
            Alpine.store('hierarchyTree', {
                query: '',
            });
        }

        Alpine.data('hierarchyTreeShell', function (tree, config) {
            var safeConfig = config && typeof config === 'object' ? config : {};
            var rootElement = document.getElementById('hierarchy-module');
            var disableAutoRefresh = !!(rootElement && rootElement.getAttribute('data-disable-auto-refresh') === '1');

            return {
                tree: Array.isArray(tree) ? tree : [],
                searchQuery: '',
                config: {
                    createUrl: String(safeConfig.createUrl || ''),
                    updateServiceUrl: String(safeConfig.updateServiceUrl || safeConfig.createUrl || ''),
                    reloadUrl: String(safeConfig.reloadUrl || ''),
                    csrf: String(safeConfig.csrf || ''),
                },
                showModal: false,
                showEditModal: false,
                isSaving: false,
                isUpdating: false,
                formError: '',
                editFormError: '',
                autoRefreshTimer: null,
                editAnchorRect: null,
                form: {
                    node_type: 'etage',
                    floor_level: '',
                    service_id: '',
                    parent_floor_level: '',
                },
                editForm: {
                    structure_service_id: '',
                    service_id: '',
                    name: '',
                    code: '',
                    parent_floor_level: '',
                },
                init: function () {
                    var initialQuery = '';
                    try {
                        initialQuery = String((Alpine.store('hierarchyTree') || {}).query || '');
                    } catch (error) {
                        initialQuery = '';
                    }

                    this.searchQuery = initialQuery;
                    this.syncSearchStore();

                    if (typeof this.$watch === 'function') {
                        this.$watch('searchQuery', () => {
                            this.syncSearchStore();
                        });
                    }

                    var self = this;
                    window.addEventListener('resize', function () {
                        self.positionEditModal();
                    });
                    window.addEventListener('scroll', function () {
                        self.positionEditModal();
                    }, true);

                    this.startAutoRefresh();
                },
                syncSearchStore: function () {
                    try {
                        Alpine.store('hierarchyTree').query = String(this.searchQuery || '');
                    } catch (error) {
                    }
                },
                clearSearch: function () {
                    this.searchQuery = '';
                    this.syncSearchStore();
                },
                hasVisibleNodes: function (query) {
                    var normalizedQuery = normalize(query);
                    if (normalizedQuery === '') {
                        return this.tree.length > 0;
                    }

                    return this.tree.some(function (node) {
                        return nodeMatches(node, normalizedQuery);
                    });
                },
                floorOptions: function () {
                    return (this.tree || []).filter(function (node) {
                        return String(node && node.type || '') === 'etage';
                    });
                },
                firstSelectableValue: function (selectId) {
                    var select = document.getElementById(selectId);
                    if (!select || !select.options) {
                        return '';
                    }

                    for (var i = 0; i < select.options.length; i += 1) {
                        var option = select.options[i];
                        if (!option.disabled && String(option.value || '').trim() !== '') {
                            return String(option.value);
                        }
                    }

                    return '';
                },
                openModal: function (type) {
                    this.formError = '';
                    if (type === 'service' || type === 'etage') {
                        this.form.node_type = type;
                    }

                    this.form.floor_level = this.firstSelectableValue('hierarchy-floor-level');
                    this.form.service_id = this.firstSelectableValue('hierarchy-service-id');
                    this.form.parent_floor_level = this.firstSelectableValue('hierarchy-parent-floor');
                    this.showModal = true;
                },
                closeModal: function () {
                    this.showModal = false;
                    this.formError = '';
                },
                captureEditAnchorRect: function (anchorTarget, structureServiceId) {
                    var anchorElement = null;

                    if (anchorTarget && anchorTarget.nodeType === 1) {
                        anchorElement = anchorTarget.closest('.hier-service-edit-btn') || anchorTarget;
                    }

                    if (!anchorElement && String(structureServiceId || '').trim() !== '') {
                        anchorElement = document.querySelector(
                            '.hier-service-edit-btn[data-edit-structure-id="' + String(structureServiceId).trim() + '"]'
                        );
                    }

                    if (!anchorElement || typeof anchorElement.getBoundingClientRect !== 'function') {
                        return null;
                    }

                    var rect = anchorElement.getBoundingClientRect();
                    return {
                        top: Number(rect.top || 0),
                        left: Number(rect.left || 0),
                        right: Number(rect.right || 0),
                        bottom: Number(rect.bottom || 0),
                        width: Number(rect.width || 0),
                        height: Number(rect.height || 0),
                    };
                },
                normalizeAnchorRect: function (rawRect) {
                    if (!rawRect || typeof rawRect !== 'object') {
                        return null;
                    }

                    var top = Number(rawRect.top);
                    var left = Number(rawRect.left);
                    var right = Number(rawRect.right);
                    var bottom = Number(rawRect.bottom);
                    var width = Number(rawRect.width);
                    var height = Number(rawRect.height);

                    if (![top, left, right, bottom, width, height].every(Number.isFinite)) {
                        return null;
                    }

                    return {
                        top: top,
                        left: left,
                        right: right,
                        bottom: bottom,
                        width: width,
                        height: height,
                    };
                },
                positionEditModal: function () {
                    if (!this.showEditModal) {
                        return;
                    }

                    var dialog = document.getElementById('hierarchy-edit-service-dialog');
                    if (!dialog) {
                        return;
                    }

                    var viewportPadding = 12;
                    var preferredGap = 8;

                    dialog.style.position = 'fixed';
                    dialog.style.margin = '0';
                    dialog.style.transform = 'none';
                    dialog.style.width = 'min(92vw, 32rem)';
                    dialog.style.maxWidth = '32rem';
                    dialog.style.maxHeight = '82vh';
                    dialog.style.left = viewportPadding + 'px';
                    dialog.style.top = viewportPadding + 'px';
                    dialog.style.visibility = 'hidden';

                    var dialogWidth = dialog.offsetWidth || 512;
                    var dialogHeight = dialog.offsetHeight || 420;

                    var centeredLeft = Math.max(viewportPadding, Math.round((window.innerWidth - dialogWidth) / 2));
                    var centeredTop = Math.max(viewportPadding, Math.round((window.innerHeight - dialogHeight) / 2));

                    var left = centeredLeft;
                    var top = centeredTop;

                    var anchorRect = this.editAnchorRect;
                    if (anchorRect && Number.isFinite(Number(anchorRect.left))) {
                        var availableBelow = Math.max(
                            0,
                            window.innerHeight - (Number(anchorRect.bottom || 0) + preferredGap) - viewportPadding
                        );
                        var dynamicMaxHeight = Math.min(
                            Math.round(window.innerHeight * 0.82),
                            Math.max(180, Math.floor(availableBelow))
                        );
                        dialog.style.maxHeight = dynamicMaxHeight + 'px';

                        dialogHeight = dialog.offsetHeight || dialogHeight;

                        left = Number(anchorRect.right || 0) + preferredGap;
                        var anchorTop = Number(anchorRect.top || 0);
                        var anchorBottom = Number(anchorRect.bottom || 0);
                        var fitsBelow = (anchorBottom + preferredGap + dialogHeight) <= (window.innerHeight - viewportPadding);
                        var fitsAbove = (anchorTop - preferredGap - dialogHeight) >= viewportPadding;

                        if (fitsBelow) {
                            top = anchorBottom + preferredGap;
                        } else if (fitsAbove) {
                            top = anchorTop - dialogHeight - preferredGap;
                        } else {
                            top = Math.max(viewportPadding, Math.min(anchorTop, window.innerHeight - dialogHeight - viewportPadding));
                        }

                        if (left + dialogWidth > window.innerWidth - viewportPadding) {
                            left = Number(anchorRect.left || 0) - dialogWidth - preferredGap;
                        }

                        if (left < viewportPadding) {
                            left = Math.max(
                                viewportPadding,
                                Math.min(Number(anchorRect.left || 0), window.innerWidth - dialogWidth - viewportPadding)
                            );
                        }

                        top = Math.min(top, window.innerHeight - dialogHeight - viewportPadding);
                        top = Math.max(viewportPadding, top);
                    }

                    dialog.style.left = Math.round(left) + 'px';
                    dialog.style.top = Math.round(top) + 'px';
                    dialog.style.visibility = 'visible';
                },
                openEditModal: function (detail, anchorTarget) {
                    var payload = detail && typeof detail === 'object' ? detail : {};

                    this.editFormError = '';
                    this.editForm.structure_service_id = String(payload.structure_service_id || '');
                    this.editForm.service_id = String(payload.service_id || '');
                    this.editForm.name = String(payload.name || '').trim();
                    this.editForm.code = String(payload.code || '').trim();
                    this.editForm.parent_floor_level = String(
                        payload.parent_floor_level !== undefined && payload.parent_floor_level !== null
                            ? payload.parent_floor_level
                            : this.firstSelectableValue('hierarchy-edit-parent-floor-level')
                    );

                    if (this.editForm.parent_floor_level === '') {
                        this.editForm.parent_floor_level = this.firstSelectableValue('hierarchy-edit-parent-floor-level');
                    }

                    this.editAnchorRect = this.normalizeAnchorRect(payload.anchor_rect)
                        || this.normalizeAnchorRect(window.__hierarchyLastEditAnchorRect)
                        || this.captureEditAnchorRect(anchorTarget, this.editForm.structure_service_id);

                    this.showEditModal = true;

                    var self = this;
                    window.requestAnimationFrame(function () {
                        self.positionEditModal();
                    });
                },
                closeEditModal: function () {
                    this.showEditModal = false;
                    this.editFormError = '';
                    this.editAnchorRect = null;
                },
                refreshTree: async function () {
                    if (!this.config.reloadUrl) {
                        return;
                    }

                    var response = await fetch(this.config.reloadUrl, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    var data = await response.json();
                    if (Array.isArray(data && data.floors)) {
                        this.tree = data.floors;
                    } else if (Array.isArray(data && data.tree)) {
                        this.tree = data.tree;
                    }
                },
                refreshTreePanelMarkup: async function () {
                    try {
                        var response = await fetch(window.location.href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            return false;
                        }

                        var html = await response.text();
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');

                        var incomingPanel = doc.querySelector('.hierarchy-tree-panel');
                        var currentPanel = this.$root.querySelector('.hierarchy-tree-panel');

                        if (!incomingPanel || !currentPanel) {
                            return false;
                        }

                        currentPanel.innerHTML = incomingPanel.innerHTML;

                        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                            window.Alpine.initTree(currentPanel);
                        }

                        return true;
                    } catch (error) {
                        return false;
                    }
                },
                refreshSingleFloorPanelMarkup: async function (floorLevel) {
                    try {
                        var normalizedLevel = Number(floorLevel);
                        if (!Number.isFinite(normalizedLevel)) {
                            return false;
                        }

                        var response = await fetch(window.location.href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            return false;
                        }

                        var html = await response.text();
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');

                        var selector = '.hierarchy-node[data-floor-level="' + String(normalizedLevel) + '"]';
                        var incomingFloorNode = doc.querySelector(selector);
                        var currentPanel = this.$root.querySelector('.hierarchy-tree-panel');
                        var currentFloorNode = currentPanel ? currentPanel.querySelector(selector) : null;

                        if (incomingFloorNode && currentFloorNode) {
                            currentFloorNode.outerHTML = incomingFloorNode.outerHTML;
                        } else if (incomingFloorNode && currentPanel) {
                            var currentTreeList = currentPanel.querySelector('.hierarchy-tree-list');
                            if (currentTreeList) {
                                currentTreeList.appendChild(incomingFloorNode.cloneNode(true));
                            } else {
                                return false;
                            }
                        } else {
                            return false;
                        }

                        if (window.Alpine && typeof window.Alpine.initTree === 'function' && currentPanel) {
                            window.Alpine.initTree(currentPanel);
                        }

                        return true;
                    } catch (error) {
                        return false;
                    }
                },
                startAutoRefresh: function () {
                    if (disableAutoRefresh) {
                        return;
                    }

                    var self = this;
                    if (self.autoRefreshTimer) {
                        return;
                    }

                    self.autoRefreshTimer = window.setInterval(async function () {
                        if (self.showModal || self.showEditModal || self.isSaving || self.isUpdating) {
                            return;
                        }

                        await self.refreshTreePanelMarkup();
                    }, 30000);
                },
                submitNodeForm: async function () {
                    this.formError = '';

                    if (!this.config.createUrl) {
                        this.formError = 'Action indisponible.';
                        return;
                    }

                    if (this.form.node_type === 'etage' && String(this.form.floor_level || '').trim() === '') {
                        this.formError = 'Veuillez sélectionner un étage entre -1 et 4.';
                        return;
                    }

                    if (this.form.node_type === 'service') {
                        if (!String(this.form.service_id || '').trim()) {
                            this.formError = 'Veuillez sélectionner un service existant.';
                            return;
                        }

                        if (!String(this.form.parent_floor_level || '').trim()) {
                            this.formError = 'Veuillez sélectionner un étage parent.';
                            return;
                        }
                    }

                    this.isSaving = true;

                    try {
                        var nodeType = this.form.node_type === 'service' ? 'service' : 'etage';

                        var payload = {
                            form_mode: 'tree_node',
                            node_type: nodeType,
                            floor_level: nodeType === 'etage'
                                ? Number(this.form.floor_level)
                                : null,
                            service_id: nodeType === 'service'
                                ? Number(this.form.service_id)
                                : null,
                            parent_floor_level: nodeType === 'service'
                                ? Number(this.form.parent_floor_level)
                                : null,
                        };

                        var response = await fetch(this.config.createUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.config.csrf,
                            },
                            body: JSON.stringify(payload),
                        });

                        var data = {};
                        try {
                            data = await response.json();
                        } catch (error) {
                            data = {};
                        }

                        if (!response.ok || !data.success) {
                            var firstError = '';
                            if (data && data.errors && typeof data.errors === 'object') {
                                var errorKeys = Object.keys(data.errors);
                                if (errorKeys.length > 0) {
                                    var firstKey = errorKeys[0];
                                    var firstValue = data.errors[firstKey];
                                    if (Array.isArray(firstValue) && firstValue.length > 0) {
                                        firstError = String(firstValue[0] || '');
                                    }
                                }
                            }

                            this.formError = firstError || String((data && data.message) || 'Impossible d\'ajouter cet élément.');
                            return;
                        }

                        if (nodeType === 'service') {
                            var floorLevelForRefresh = Number(this.form.parent_floor_level);
                            var singleFloorUpdated = await this.refreshSingleFloorPanelMarkup(floorLevelForRefresh);
                            if (!singleFloorUpdated) {
                                this.formError = 'Service ajouté, mais l\'étage ciblé n\'a pas pu être actualisé. Rechargez la page.';
                                return;
                            }

                            this.closeModal();
                            return;
                        }

                        if (Array.isArray(data.floors)) {
                            this.tree = data.floors;
                        } else if (Array.isArray(data.tree)) {
                            this.tree = data.tree;
                        } else {
                            await this.refreshTree();
                        }

                        var panelUpdated = await this.refreshTreePanelMarkup();
                        if (!panelUpdated) {
                            this.formError = 'Ajout effectué, mais l\'actualisation de l\'arbre a échoué. Rechargez la page.';
                            return;
                        }

                        this.closeModal();
                    } catch (error) {
                        this.formError = 'Erreur réseau, veuillez réessayer.';
                    } finally {
                        this.isSaving = false;
                    }
                },
                submitEditServiceForm: async function () {
                    this.editFormError = '';

                    if (!this.config.updateServiceUrl) {
                        this.editFormError = 'Action indisponible.';
                        return;
                    }

                    if (!String(this.editForm.structure_service_id || '').trim()) {
                        this.editFormError = 'Le service à modifier est introuvable.';
                        return;
                    }

                    if (!String(this.editForm.name || '').trim()) {
                        this.editFormError = 'Le nom du service est obligatoire.';
                        return;
                    }

                    if (!String(this.editForm.code || '').trim()) {
                        this.editFormError = 'Le code du service est obligatoire.';
                        return;
                    }

                    if (!String(this.editForm.parent_floor_level || '').trim()) {
                        this.editFormError = 'Veuillez sélectionner un étage parent.';
                        return;
                    }

                    this.isUpdating = true;

                    try {
                        var payload = {
                            form_mode: 'update_service_node',
                            structure_service_id: Number(this.editForm.structure_service_id),
                            service_id: String(this.editForm.service_id || '').trim() === ''
                                ? null
                                : Number(this.editForm.service_id),
                            name: String(this.editForm.name || '').trim(),
                            code: String(this.editForm.code || '').trim(),
                            parent_floor_level: Number(this.editForm.parent_floor_level),
                        };

                        var response = await fetch(this.config.updateServiceUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.config.csrf,
                            },
                            body: JSON.stringify(payload),
                        });

                        var data = {};
                        try {
                            data = await response.json();
                        } catch (error) {
                            data = {};
                        }

                        if (!response.ok || !data.success) {
                            var firstError = '';
                            if (data && data.errors && typeof data.errors === 'object') {
                                var errorKeys = Object.keys(data.errors);
                                if (errorKeys.length > 0) {
                                    var firstKey = errorKeys[0];
                                    var firstValue = data.errors[firstKey];
                                    if (Array.isArray(firstValue) && firstValue.length > 0) {
                                        firstError = String(firstValue[0] || '');
                                    }
                                }
                            }

                            this.editFormError = firstError || String((data && data.message) || 'Impossible de modifier ce service.');
                            return;
                        }

                        if (Array.isArray(data.floors)) {
                            this.tree = data.floors;
                        } else if (Array.isArray(data.tree)) {
                            this.tree = data.tree;
                        } else {
                            await this.refreshTree();
                        }

                        var panelUpdated = await this.refreshTreePanelMarkup();
                        if (!panelUpdated) {
                            this.editFormError = 'Modification effectuée, mais l\'actualisation de l\'arbre a échoué. Rechargez la page.';
                            return;
                        }

                        this.closeEditModal();
                    } catch (error) {
                        this.editFormError = 'Erreur réseau, veuillez réessayer.';
                    } finally {
                        this.isUpdating = false;
                    }
                },
            };
        });

        Alpine.data('hierarchyTreeNode', function (node, level) {
            return {
                node: node || {},
                open: false,
                toggle: function () {
                    this.open = !this.open;
                },
                hasChildren: function () {
                    return Array.isArray(this.node.children) && this.node.children.length > 0;
                },
                isExpanded: function () {
                    return this.open;
                },
                isVisible: function (query) {
                    var normalizedQuery = normalize(query);
                    if (normalizedQuery === '') {
                        return true;
                    }

                    return nodeMatches(this.node, normalizedQuery);
                },
            };
        });

        hierarchyRegistered = true;

        // Alpine may already be started before this script runs.
        // Re-initialize hierarchy roots so click handlers/modal bindings become active.
        setTimeout(function () {
            if (!window.Alpine || typeof window.Alpine.initTree !== 'function') {
                return;
            }

            var roots = document.querySelectorAll('.hierarchy-module[x-data]');
            roots.forEach(function (root) {
                if (!root._x_dataStack) {
                    window.Alpine.initTree(root);
                }
            });
        }, 0);
    }

    if (window.Alpine) {
        registerHierarchyAlpine();
    }

    document.addEventListener('alpine:init', function () {
        registerHierarchyAlpine();
    });

    document.addEventListener('DOMContentLoaded', function () {
        var root = document.getElementById('hierarchy-module');
        if (!root) {
            return;
        }

        var addBtn = document.getElementById('hierarchy-add-btn');
        var modal = document.getElementById('hierarchy-node-modal');
        var closeXBtn = document.getElementById('hierarchy-modal-close-x');
        var cancelBtn = document.getElementById('hierarchy-modal-cancel');
        var form = document.getElementById('hierarchy-node-form');
        var editModal = document.getElementById('hierarchy-edit-service-modal');
        var editCloseXBtn = document.getElementById('hierarchy-edit-modal-close-x');
        var editCancelBtn = document.getElementById('hierarchy-edit-modal-cancel');
        var editForm = document.getElementById('hierarchy-edit-service-form');
        var typeSelect = document.getElementById('hierarchy-node-type');
        var floorLevelWrap = document.getElementById('hierarchy-floor-level-wrap');
        var serviceSelectWrap = document.getElementById('hierarchy-service-select-wrap');
        var parentFloorWrap = document.getElementById('hierarchy-parent-floor-wrap');
        var floorLevelSelect = document.getElementById('hierarchy-floor-level');
        var serviceIdSelect = document.getElementById('hierarchy-service-id');
        var parentFloorSelect = document.getElementById('hierarchy-parent-floor');
        var errorBox = document.getElementById('hierarchy-form-error');
        var editStructureInput = document.getElementById('hierarchy-edit-structure-id');
        var editServiceInput = document.getElementById('hierarchy-edit-service-id');
        var editNameInput = document.getElementById('hierarchy-edit-service-name');
        var editCodeInput = document.getElementById('hierarchy-edit-service-code');
        var editParentFloorSelect = document.getElementById('hierarchy-edit-parent-floor-level');
        var editErrorBox = document.getElementById('hierarchy-edit-form-error');
        var currentEditButton = null;
        var currentEditHostRow = null;

        function findDirectChildByClass(parent, className) {
            if (!parent || !parent.children) {
                return null;
            }

            for (var index = 0; index < parent.children.length; index += 1) {
                var child = parent.children[index];
                if (child && child.classList && child.classList.contains(className)) {
                    return child;
                }
            }

            return null;
        }

        function isShown(element) {
            if (!element) {
                return false;
            }

            return window.getComputedStyle(element).display !== 'none';
        }

        function applyFallbackFloorExpansion(nodeElement, shouldOpen) {
            if (!nodeElement) {
                return;
            }

            var childrenWrap = findDirectChildByClass(nodeElement, 'hierarchy-children');
            var emptyWrap = findDirectChildByClass(nodeElement, 'hier-floor-empty');
            var arrow = nodeElement.querySelector('.hier-floor-arrow');

            if (childrenWrap) {
                childrenWrap.style.display = shouldOpen ? '' : 'none';
            }

            if (emptyWrap) {
                emptyWrap.style.display = shouldOpen ? '' : 'none';
            }

            if (arrow) {
                arrow.classList.toggle('is-open', shouldOpen);
            }
        }

        function getAlpineComponent() {
            if (!root || !root._x_dataStack || root._x_dataStack.length === 0) {
                return null;
            }

            return root._x_dataStack[0] || null;
        }

        function setError(message) {
            if (!errorBox) {
                return;
            }

            var text = String(message || '').trim();
            if (text === '') {
                errorBox.textContent = '';
                errorBox.style.display = 'none';
                return;
            }

            errorBox.textContent = text;
            errorBox.style.display = 'block';
        }

        function setEditError(message) {
            if (!editErrorBox) {
                return;
            }

            var text = String(message || '').trim();
            if (text === '') {
                editErrorBox.textContent = '';
                editErrorBox.style.display = 'none';
                return;
            }

            editErrorBox.textContent = text;
            editErrorBox.style.display = 'block';
        }

        function isElementShown(element) {
            if (!element) {
                return false;
            }

            return window.getComputedStyle(element).display !== 'none';
        }

        function firstSelectableValue(selectElement) {
            if (!selectElement || !selectElement.options) {
                return '';
            }

            for (var i = 0; i < selectElement.options.length; i += 1) {
                var option = selectElement.options[i];
                if (!option.disabled && String(option.value || '').trim() !== '') {
                    return String(option.value);
                }
            }

            return '';
        }

        function toggleFormByType() {
            if (!typeSelect) {
                return;
            }

            var isService = typeSelect.value === 'service';

            if (floorLevelWrap) {
                floorLevelWrap.style.display = isService ? 'none' : '';
            }

            if (serviceSelectWrap) {
                serviceSelectWrap.style.display = isService ? '' : 'none';
            }

            if (parentFloorWrap) {
                parentFloorWrap.style.display = isService ? '' : 'none';
            }
        }

        function openModalFallback(type) {
            if (!modal) {
                return;
            }

            if (typeSelect) {
                typeSelect.value = type === 'service' ? 'service' : 'etage';
            }

            if (floorLevelSelect) {
                floorLevelSelect.value = firstSelectableValue(floorLevelSelect);
            }

            if (serviceIdSelect) {
                serviceIdSelect.value = firstSelectableValue(serviceIdSelect);
            }

            if (parentFloorSelect) {
                parentFloorSelect.value = firstSelectableValue(parentFloorSelect);
            }

            toggleFormByType();
            setError('');
            modal.style.display = 'flex';
        }

        function closeModalFallback() {
            if (!modal) {
                return;
            }

            setError('');
            modal.style.display = 'none';
        }

        function detailFromEditButton(button) {
            if (!button) {
                return {};
            }

            return {
                structure_service_id: String(button.getAttribute('data-edit-structure-id') || '').trim(),
                service_id: String(button.getAttribute('data-edit-service-id') || '').trim(),
                name: String(button.getAttribute('data-edit-name') || '').trim(),
                code: String(button.getAttribute('data-edit-code') || '').trim(),
                parent_floor_level: String(button.getAttribute('data-edit-floor-level') || '').trim(),
            };
        }

        function ensureEditHostRow(hostRow) {
            if (!hostRow || !editModal) {
                return false;
            }

            var hostPosition = window.getComputedStyle(hostRow).position;
            if (hostPosition === 'static') {
                hostRow.style.position = 'relative';
            }

            if (editModal.parentElement !== hostRow) {
                hostRow.appendChild(editModal);
            }

            return true;
        }

        function positionEditModalFallback(anchorButton, hostRow) {
            var dialog = document.getElementById('hierarchy-edit-service-dialog');
            if (!dialog || !editModal || !isElementShown(editModal) || !anchorButton || !hostRow) {
                return;
            }

            var gap = 8;
            var pad = 8;

            editModal.style.position = 'absolute';
            editModal.style.left = '0px';
            editModal.style.top = '0px';
            editModal.style.width = '100%';
            editModal.style.height = '0px';
            editModal.style.display = 'block';
            editModal.style.pointerEvents = 'none';

            dialog.style.position = 'absolute';
            dialog.style.margin = '0';
            dialog.style.transform = 'none';
            dialog.style.width = 'min(92vw, 32rem)';
            dialog.style.maxWidth = '32rem';
            dialog.style.maxHeight = '70vh';
            dialog.style.overflowY = 'auto';
            dialog.style.left = '0px';
            dialog.style.top = '0px';
            dialog.style.visibility = 'hidden';

            var hostRect = hostRow.getBoundingClientRect();
            var buttonRect = anchorButton.getBoundingClientRect();
            var dialogWidth = dialog.offsetWidth || 512;
            var dialogHeight = dialog.offsetHeight || 420;

            var left = (buttonRect.right - hostRect.left) + gap;
            if (left + dialogWidth > hostRect.width - pad) {
                left = (buttonRect.left - hostRect.left) - dialogWidth - gap;
            }

            if (left < pad) {
                left = Math.max(
                    pad,
                    Math.min((buttonRect.left - hostRect.left), hostRect.width - dialogWidth - pad)
                );
            }

            var top = (buttonRect.bottom - hostRect.top) + gap;
            var projectedBottom = hostRect.top + top + dialogHeight;

            if (projectedBottom > (window.innerHeight - pad)) {
                var aboveTop = (buttonRect.top - hostRect.top) - dialogHeight - gap;
                if ((hostRect.top + aboveTop) >= pad) {
                    top = aboveTop;
                }
            }

            dialog.style.left = Math.round(left) + 'px';
            dialog.style.top = Math.round(top) + 'px';
            dialog.style.visibility = 'visible';
        }

        function openEditModalFallback(detail, anchorButton) {
            if (!editModal) {
                return;
            }

            var payload = detail && typeof detail === 'object' ? detail : {};
            var button = anchorButton && anchorButton.nodeType === 1
                ? anchorButton.closest('.hier-service-edit-btn')
                : null;

            if (!button && String(payload.structure_service_id || '').trim() !== '') {
                button = root.querySelector(
                    '.hier-service-edit-btn[data-edit-structure-id="' + String(payload.structure_service_id).trim() + '"]'
                );
            }

            if (!button) {
                return;
            }

            var hostRow = button.closest('.hier-service-row');
            if (!ensureEditHostRow(hostRow)) {
                return;
            }

            if (editStructureInput) {
                editStructureInput.value = String(payload.structure_service_id || '');
            }

            if (editServiceInput) {
                editServiceInput.value = String(payload.service_id || '');
            }

            if (editNameInput) {
                editNameInput.value = String(payload.name || '').trim();
            }

            if (editCodeInput) {
                editCodeInput.value = String(payload.code || '').trim();
            }

            if (editParentFloorSelect) {
                var floorValue = payload.parent_floor_level !== undefined && payload.parent_floor_level !== null
                    ? String(payload.parent_floor_level)
                    : firstSelectableValue(editParentFloorSelect);
                editParentFloorSelect.value = floorValue;
            }

            currentEditButton = button;
            currentEditHostRow = hostRow;

            setEditError('');
            editModal.classList.remove('hidden');
            editModal.style.display = 'block';

            window.requestAnimationFrame(function () {
                positionEditModalFallback(currentEditButton, currentEditHostRow);
            });
        }

        function closeEditModalFallback() {
            if (!editModal) {
                return;
            }

            setEditError('');
            editModal.style.display = 'none';
            editModal.classList.add('hidden');
            editModal.style.pointerEvents = '';

            var dialog = document.getElementById('hierarchy-edit-service-dialog');
            if (dialog) {
                dialog.style.visibility = '';
                dialog.style.left = '';
                dialog.style.top = '';
            }

            if (root && editModal.parentElement !== root) {
                root.appendChild(editModal);
            }

            currentEditButton = null;
            currentEditHostRow = null;
        }

        async function refreshTreePanelFallback() {
            try {
                var response = await fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return false;
                }

                var html = await response.text();
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');

                var incomingPanel = doc.querySelector('.hierarchy-tree-panel');
                var currentPanel = root.querySelector('.hierarchy-tree-panel');

                if (!incomingPanel || !currentPanel) {
                    return false;
                }

                currentPanel.innerHTML = incomingPanel.innerHTML;

                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(currentPanel);
                }

                return true;
            } catch (error) {
                return false;
            }
        }

        async function refreshSingleFloorPanelFallback(floorLevel) {
            try {
                var normalizedLevel = Number(floorLevel);
                if (!Number.isFinite(normalizedLevel)) {
                    return false;
                }

                var response = await fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return false;
                }

                var html = await response.text();
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');

                var selector = '.hierarchy-node[data-floor-level="' + String(normalizedLevel) + '"]';
                var incomingFloorNode = doc.querySelector(selector);
                var currentPanel = root.querySelector('.hierarchy-tree-panel');
                var currentFloorNode = currentPanel ? currentPanel.querySelector(selector) : null;

                if (incomingFloorNode && currentFloorNode) {
                    currentFloorNode.outerHTML = incomingFloorNode.outerHTML;
                } else if (incomingFloorNode && currentPanel) {
                    var currentTreeList = currentPanel.querySelector('.hierarchy-tree-list');
                    if (!currentTreeList) {
                        return false;
                    }

                    currentTreeList.appendChild(incomingFloorNode.cloneNode(true));
                } else {
                    return false;
                }

                if (window.Alpine && typeof window.Alpine.initTree === 'function' && currentPanel) {
                    window.Alpine.initTree(currentPanel);
                }

                return true;
            } catch (error) {
                return false;
            }
        }

        function openUsingBestPath(type) {
            var component = getAlpineComponent();
            if (component && typeof component.openModal === 'function') {
                component.openModal(type);
                return;
            }

            openModalFallback(type);
        }

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                openUsingBestPath('etage');
            });
        }

        if (closeXBtn) {
            closeXBtn.addEventListener('click', function () {
                closeModalFallback();
            });
        }

        if (editCloseXBtn) {
            editCloseXBtn.addEventListener('click', function () {
                closeEditModalFallback();
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                closeModalFallback();
            });
        }

        if (editCancelBtn) {
            editCancelBtn.addEventListener('click', function () {
                closeEditModalFallback();
            });
        }

        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModalFallback();
                }
            });
        }

        if (editModal) {
            editModal.addEventListener('click', function (event) {
                if (event.target === editModal) {
                    closeEditModalFallback();
                }
            });
        }

        root.addEventListener('click', function (event) {
            var editButton = event.target && event.target.closest
                ? event.target.closest('.hier-service-edit-btn')
                : null;

            if (!editButton || !root.contains(editButton)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            openEditModalFallback(detailFromEditButton(editButton), editButton);
        });

        root.addEventListener('click', function (event) {
            var header = event.target && event.target.closest
                ? event.target.closest('.hier-floor-header')
                : null;

            if (!header || !root.contains(header)) {
                return;
            }

            var nodeElement = header.closest('.hierarchy-node');
            if (!nodeElement) {
                return;
            }

            var childrenWrap = findDirectChildByClass(nodeElement, 'hierarchy-children');
            var emptyWrap = findDirectChildByClass(nodeElement, 'hier-floor-empty');

            if (!childrenWrap && !emptyWrap) {
                return;
            }

            var wasExpanded = isShown(childrenWrap) || isShown(emptyWrap);

            window.requestAnimationFrame(function () {
                var isExpandedNow = isShown(childrenWrap) || isShown(emptyWrap);

                // If Alpine did not toggle UI state, apply a safe DOM fallback.
                if (isExpandedNow === wasExpanded) {
                    applyFallbackFloorExpansion(nodeElement, !wasExpanded);
                }
            });
        });

        document.addEventListener('hierarchy-edit-service', function (event) {
            openEditModalFallback(
                event && event.detail ? event.detail : {},
                event && event.target ? event.target : null
            );
        });

        document.addEventListener('click', function (event) {
            if (!isElementShown(editModal)) {
                return;
            }

            var clickedEditButton = event.target && event.target.closest
                ? event.target.closest('.hier-service-edit-btn')
                : null;
            if (clickedEditButton) {
                return;
            }

            var dialog = document.getElementById('hierarchy-edit-service-dialog');
            if (dialog && !dialog.contains(event.target)) {
                closeEditModalFallback();
            }
        });

        window.addEventListener('resize', function () {
            positionEditModalFallback(currentEditButton, currentEditHostRow);
        });

        window.addEventListener('scroll', function () {
            positionEditModalFallback(currentEditButton, currentEditHostRow);
        }, true);

        if (typeSelect) {
            typeSelect.addEventListener('change', function () {
                toggleFormByType();
            });
            toggleFormByType();
        }

        if (form) {
            form.addEventListener('submit', async function (event) {
                var component = getAlpineComponent();
                if (component && typeof component.submitNodeForm === 'function') {
                    return;
                }

                event.preventDefault();
                setError('');

                var nodeType = typeSelect ? String(typeSelect.value || 'etage') : 'etage';
                var floorLevel = floorLevelSelect ? String(floorLevelSelect.value || '').trim() : '';
                var serviceId = serviceIdSelect ? String(serviceIdSelect.value || '').trim() : '';
                var parentFloorLevel = parentFloorSelect ? String(parentFloorSelect.value || '').trim() : '';

                if (nodeType === 'etage' && floorLevel === '') {
                    setError('Veuillez sélectionner un étage entre -1 et 4.');
                    return;
                }

                if (nodeType === 'service') {
                    if (serviceId === '') {
                        setError('Veuillez sélectionner un service existant.');
                        return;
                    }

                    if (parentFloorLevel === '') {
                        setError('Veuillez sélectionner un étage parent.');
                        return;
                    }
                }

                var createUrl = String(root.getAttribute('data-create-url') || '');
                var csrf = String(root.getAttribute('data-csrf-token') || '');

                if (createUrl === '') {
                    setError('Action indisponible.');
                    return;
                }

                var payload = {
                    form_mode: 'tree_node',
                    node_type: nodeType,
                    floor_level: nodeType === 'etage' ? Number(floorLevel) : null,
                    service_id: nodeType === 'service' ? Number(serviceId) : null,
                    parent_floor_level: nodeType === 'service' ? Number(parentFloorLevel) : null,
                };

                try {
                    var response = await fetch(createUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify(payload),
                    });

                    var data = {};
                    try {
                        data = await response.json();
                    } catch (error) {
                        data = {};
                    }

                    if (!response.ok || !data.success) {
                        var message = String((data && data.message) || 'Impossible d\'ajouter cet élément.');

                        if (data && data.errors && typeof data.errors === 'object') {
                            var keys = Object.keys(data.errors);
                            if (keys.length > 0) {
                                var first = data.errors[keys[0]];
                                if (Array.isArray(first) && first.length > 0) {
                                    message = String(first[0] || message);
                                }
                            }
                        }

                        setError(message);
                        return;
                    }

                    if (nodeType === 'service') {
                        var singleFloorUpdated = await refreshSingleFloorPanelFallback(parentFloorLevel);
                        if (!singleFloorUpdated) {
                            setError('Service ajouté, mais l\'étage ciblé n\'a pas pu être actualisé. Rechargez la page.');
                            return;
                        }

                        closeModalFallback();
                        return;
                    }

                    var panelUpdated = await refreshTreePanelFallback();
                    if (!panelUpdated) {
                        setError('Ajout effectué, mais l\'actualisation de l\'arbre a échoué. Rechargez la page.');
                        return;
                    }

                    closeModalFallback();
                } catch (error) {
                    setError('Erreur réseau, veuillez réessayer.');
                }
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                setEditError('');

                var structureServiceId = editStructureInput ? String(editStructureInput.value || '').trim() : '';
                var serviceId = editServiceInput ? String(editServiceInput.value || '').trim() : '';
                var name = editNameInput ? String(editNameInput.value || '').trim() : '';
                var code = editCodeInput ? String(editCodeInput.value || '').trim() : '';
                var parentFloorLevel = editParentFloorSelect ? String(editParentFloorSelect.value || '').trim() : '';

                if (structureServiceId === '') {
                    setEditError('Le service à modifier est introuvable.');
                    return;
                }

                if (name === '') {
                    setEditError('Le nom du service est obligatoire.');
                    return;
                }

                if (code === '') {
                    setEditError('Le code du service est obligatoire.');
                    return;
                }

                if (parentFloorLevel === '') {
                    setEditError('Veuillez sélectionner un étage parent.');
                    return;
                }

                var updateUrl = String(root.getAttribute('data-update-service-url') || root.getAttribute('data-create-url') || '');
                var csrf = String(root.getAttribute('data-csrf-token') || '');

                if (updateUrl === '') {
                    setEditError('Action indisponible.');
                    return;
                }

                var payload = {
                    form_mode: 'update_service_node',
                    structure_service_id: Number(structureServiceId),
                    service_id: serviceId === '' ? null : Number(serviceId),
                    name: name,
                    code: code,
                    parent_floor_level: Number(parentFloorLevel),
                };

                try {
                    var response = await fetch(updateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify(payload),
                    });

                    var data = {};
                    try {
                        data = await response.json();
                    } catch (error) {
                        data = {};
                    }

                    if (!response.ok || !data.success) {
                        var message = String((data && data.message) || 'Impossible de modifier ce service.');

                        if (data && data.errors && typeof data.errors === 'object') {
                            var keys = Object.keys(data.errors);
                            if (keys.length > 0) {
                                var first = data.errors[keys[0]];
                                if (Array.isArray(first) && first.length > 0) {
                                    message = String(first[0] || message);
                                }
                            }
                        }

                        setEditError(message);
                        return;
                    }

                    var panelUpdated = await refreshTreePanelFallback();
                    if (!panelUpdated) {
                        setEditError('Modification effectuée, mais l\'actualisation de l\'arbre a échoué. Rechargez la page.');
                        return;
                    }

                    closeEditModalFallback();
                } catch (error) {
                    setEditError('Erreur réseau, veuillez réessayer.');
                }
            });
        }

        window.setInterval(function () {
            if (root.getAttribute('data-disable-auto-refresh') === '1') {
                return;
            }

            var component = getAlpineComponent();
            if (component && typeof component.refreshTreePanelMarkup === 'function') {
                return;
            }

            if (isElementShown(modal) || isElementShown(editModal)) {
                return;
            }

            refreshTreePanelFallback();
        }, 30000);
    });
})();