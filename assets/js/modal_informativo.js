(function () {
    function initializeModal() {
        var modal = document.querySelector('[data-sp-info-modal]');
        if (!modal || modal.dataset.spInitialized === '1') {
            return;
        }

        modal.dataset.spInitialized = '1';
        var preview = modal.getAttribute('data-preview') === '1';
        var mode = modal.getAttribute('data-mode') || 'una_vez';
        var delay = parseInt(modal.getAttribute('data-delay') || '0', 10);
        var hash = modal.getAttribute('data-hash') || 'default';
        var storageKey = 'sp_info_modal_seen_' + hash;

        function storageGet(key) {
            try {
                return window.localStorage.getItem(key);
            } catch (error) {
                return null;
            }
        }

        function storageSet(key, value) {
            try {
                window.localStorage.setItem(key, value);
            } catch (error) {
                // Storage can be blocked; keep the modal usable for this visit.
                return false;
            }
        }

        var shouldShow = preview || mode === 'siempre' || storageGet(storageKey) !== '1';

        if (!shouldShow) {
            modal.remove();
            return;
        }

        function markSeen() {
            if (!preview && mode !== 'siempre') {
                storageSet(storageKey, '1');
            }
        }

        function openModal() {
            modal.hidden = false;
            modal.offsetHeight;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            markSeen();
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            setTimeout(function () {
                modal.remove();
            }, 190);
        }

        modal.querySelectorAll('[data-sp-modal-close], [data-sp-modal-primary]').forEach(function (element) {
            element.addEventListener('click', function (event) {
                if (element.getAttribute('href') === '#') {
                    event.preventDefault();
                }
                closeModal();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        window.setTimeout(openModal, Math.max(0, delay));
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeModal, { once: true });
    } else {
        initializeModal();
    }
})();
