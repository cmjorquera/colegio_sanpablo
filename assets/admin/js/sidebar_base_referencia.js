// ─── Sidebar toggle ──────────────────────────────────────────────────────────

(function () {
    const STORAGE_KEY = 'sidebar_collapsed';

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const layout  = document.querySelector('.layout');
        if (!sidebar || !layout) return;

        if (window.innerWidth <= 768) {
            layout.classList.add('mobile-sidebar-open');
            return;
        }

        const isCollapsed = layout.classList.toggle('sidebar-collapsed');
        localStorage.setItem(STORAGE_KEY, isCollapsed ? '1' : '0');
    }

    function closeMobileSidebar() {
        const layout = document.querySelector('.layout');
        if (layout) layout.classList.remove('mobile-sidebar-open');
    }

    function applySavedState() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved === '1') {
            const layout = document.querySelector('.layout');
            if (layout) layout.classList.add('sidebar-collapsed');
        }
    }

    document.addEventListener('DOMContentLoaded', applySavedState);
    window.toggleSidebar = toggleSidebar;
    window.closeMobileSidebar = closeMobileSidebar;
}());
