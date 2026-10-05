(function () {
    function notifyDenied(message) {
        if (window.Swal) {
            Swal.fire({ icon: 'warning', title: 'Permiso requerido', text: message, confirmButtonColor: '#26384f' });
        } else if (window.adminNotify) {
            adminNotify({ title: 'Permiso requerido', msg: message, type: 'warning' });
        } else {
            alert(message);
        }
    }

    window.adminNotifyDenied = notifyDenied;

    document.addEventListener('click', function (event) {
        var denied = event.target.closest('[data-admin-denied]');
        if (!denied) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        notifyDenied(denied.getAttribute('data-admin-denied') || 'No tienes permiso para realizar esta accion.');
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        var shell = document.getElementById('adminShell');
        var sidebar = document.getElementById('adminSidebar');
        var toggle = document.getElementById('toggleSidebar');
        var mobileToggle = document.getElementById('mobileMenuToggle');
        var overlay = document.getElementById('sidebarOverlay');

        if (shell && shell.dataset.sidebarReady !== '1') {
            shell.dataset.sidebarReady = '1';
            window.adminSidebarReady = true;

            if (localStorage.getItem('adminSidebarCollapsed') === '1') {
                shell.classList.add('collapsed');
                document.documentElement.classList.add('adm-sidebar-collapsed');
            }

            if (toggle) {
                toggle.addEventListener('click', function () {
                    shell.classList.toggle('collapsed');
                    var isCollapsed = shell.classList.contains('collapsed');
                    document.documentElement.classList.toggle('adm-sidebar-collapsed', isCollapsed);
                    localStorage.setItem('adminSidebarCollapsed', isCollapsed ? '1' : '0');
                });
            }

            if (mobileToggle && sidebar && overlay) {
                mobileToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('mobile-open');
                    overlay.style.display = sidebar.classList.contains('mobile-open') ? 'block' : 'none';
                });
            }

            document.querySelectorAll('[data-admin-submenu-toggle]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var subitems = button.nextElementSibling;
                    if (!subitems || !subitems.classList.contains('nav-subitems')) {
                        return;
                    }
                    var isOpen = subitems.classList.toggle('is-open');
                    button.classList.toggle('is-open', isOpen);
                    button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            });
        }

        document.querySelectorAll('[data-perm-row-toggle]').forEach(function (toggleButton) {
            toggleButton.addEventListener('click', function () {
                var row = toggleButton.closest('tr');
                if (!row) {
                    return;
                }
                var checks = Array.from(row.querySelectorAll('input[type="checkbox"]')).filter(function (input) {
                    return !input.disabled;
                });
                var shouldCheck = checks.some(function (input) { return !input.checked; });
                checks.forEach(function (input) { input.checked = shouldCheck; });
            });
        });
    });
})();
