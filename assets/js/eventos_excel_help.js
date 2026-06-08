(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var help = document.getElementById('eventosExcelHelp');
        if (!help || typeof bootstrap === 'undefined') {
            return;
        }

        document.querySelectorAll('[data-eventos-help]').forEach(function (button) {
            button.addEventListener('click', function () {
                bootstrap.Offcanvas.getOrCreateInstance(help).show();
            });
        });
    });
})();
