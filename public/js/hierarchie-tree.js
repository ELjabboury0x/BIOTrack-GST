document.addEventListener('DOMContentLoaded', function () {
    var tree = document.getElementById('hierarchy-tree');
    if (!tree) {
        return;
    }

    var searchInput = document.getElementById('hierarchy-search');
    var clearSearchButton = document.getElementById('hierarchy-search-clear');

    tree.querySelectorAll('.tree-item').forEach(function (item) {
        var toggle = item.querySelector(':scope > .tree-node .tree-toggle');
        var children = item.querySelector(':scope > .tree-children');

        if (!toggle || !children) {
            return;
        }

        var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        if (isExpanded) {
            children.classList.add('is-open');
            children.style.maxHeight = 'none';
        }
    });

    function toggleBranch(toggle, children) {
        var expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');

        if (expanded) {
            if (children.style.maxHeight === 'none') {
                children.style.maxHeight = children.scrollHeight + 'px';
            }

            children.style.maxHeight = children.scrollHeight + 'px';
            requestAnimationFrame(function () {
                children.style.maxHeight = '0px';
            });
            children.classList.remove('is-open');
            return;
        }

        children.style.maxHeight = children.scrollHeight + 'px';
        children.classList.add('is-open');

        window.setTimeout(function () {
            if (toggle.getAttribute('aria-expanded') === 'true') {
                children.style.maxHeight = 'none';
            }
        }, 220);
    }

    tree.addEventListener('click', function (event) {
        var toggle = event.target.closest('.tree-toggle');
        if (!toggle || toggle.classList.contains('tree-toggle-placeholder')) {
            return;
        }

        var item = toggle.closest('.tree-item');
        if (!item) {
            return;
        }

        var children = item.querySelector(':scope > .tree-children');
        if (!children) {
            return;
        }

        toggleBranch(toggle, children);
    });

    function ensureVisibleAncestors(item) {
        var parent = item.parentElement;

        while (parent && parent !== tree) {
            if (parent.classList.contains('tree-children')) {
                parent.style.display = '';
                parent.classList.add('is-open');
                parent.style.maxHeight = 'none';

                var ownerItem = parent.closest('.tree-item');
                if (ownerItem) {
                    var ownerToggle = ownerItem.querySelector(':scope > .tree-node .tree-toggle');
                    if (ownerToggle) {
                        ownerToggle.setAttribute('aria-expanded', 'true');
                    }
                }
            }
            parent = parent.parentElement;
        }
    }

    function applySearchFilter(term) {
        var normalized = (term || '').trim().toLowerCase();
        var items = Array.prototype.slice.call(tree.querySelectorAll('.tree-item'));

        if (normalized === '') {
            items.forEach(function (item) {
                item.style.display = '';
                var children = item.querySelector(':scope > .tree-children');
                var toggle = item.querySelector(':scope > .tree-node .tree-toggle');

                if (children && toggle) {
                    var expanded = toggle.getAttribute('aria-expanded') === 'true';
                    if (expanded) {
                        children.style.display = '';
                        children.style.maxHeight = 'none';
                    } else {
                        children.style.maxHeight = '0px';
                    }
                }
            });
            return;
        }

        items.forEach(function (item) {
            item.style.display = 'none';
        });

        items.forEach(function (item) {
            var label = item.querySelector(':scope > .tree-node .tree-label');
            var meta = item.querySelector(':scope > .tree-node .tree-meta');
            var text = ((label ? label.textContent : '') + ' ' + (meta ? meta.textContent : '')).toLowerCase();

            if (text.indexOf(normalized) !== -1) {
                item.style.display = '';
                ensureVisibleAncestors(item);
            }
        });
    }

    if (searchInput) {
        var searchTimer = null;
        searchInput.addEventListener('input', function () {
            if (searchTimer) {
                window.clearTimeout(searchTimer);
            }

            searchTimer = window.setTimeout(function () {
                applySearchFilter(searchInput.value);
            }, 120);
        });
    }

    if (clearSearchButton && searchInput) {
        clearSearchButton.addEventListener('click', function () {
            searchInput.value = '';
            applySearchFilter('');
            searchInput.focus();
        });
    }
});
