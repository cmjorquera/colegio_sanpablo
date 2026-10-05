(function () {
    var shell = document.getElementById('adminShell');
    var sidebar = document.getElementById('adminSidebar');
    var toggle = document.getElementById('toggleSidebar');
    var mobileToggle = document.getElementById('mobileMenuToggle');
    var overlay = document.getElementById('sidebarOverlay');

    if (!shell || shell.dataset.sidebarReady === '1') {
        window.adminSidebarReady = true;
        return;
    }

    window.adminSidebarReady = true;
    shell.dataset.sidebarReady = '1';

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

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented) {
            return;
        }
        var denied = event.target.closest('[data-admin-denied]');
        if (!denied) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        var message = denied.getAttribute('data-admin-denied') || 'No tienes permiso para realizar esta accion.';
        if (window.Swal) {
            Swal.fire({ icon: 'warning', title: 'Permiso requerido', text: message });
        } else if (window.adminNotify) {
            adminNotify({ title: 'Permiso requerido', msg: message, type: 'danger', autoClose: 3200 });
        } else {
            alert(message);
        }
    }, true);
})();
