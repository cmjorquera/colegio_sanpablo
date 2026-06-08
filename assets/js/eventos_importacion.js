(function () {
    function syncImportButton() {
        var checks = Array.prototype.slice.call(document.querySelectorAll('.event-row-check:not(:disabled)'));
        var selected = checks.filter(function (check) { return check.checked; });
        var submit = document.getElementById('eventImportSubmit');
        var selectAll = document.getElementById('eventSelectAll');

        if (submit) {
            submit.disabled = selected.length === 0;
            submit.classList.toggle('d-none', selected.length === 0);
        }
        if (selectAll) {
            selectAll.checked = checks.length > 0 && selected.length === checks.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < checks.length;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var previewForm = document.getElementById('eventPreviewImportForm');
        if (previewForm) {
            window.setTimeout(function () {
                previewForm.classList.remove('is-loading');
            }, 3000);
        }

        var selectAll = document.getElementById('eventSelectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.event-row-check:not(:disabled)').forEach(function (check) {
                    check.checked = selectAll.checked;
                });
                syncImportButton();
            });
        }

        document.querySelectorAll('.event-row-check').forEach(function (check) {
            check.addEventListener('change', syncImportButton);
        });

        if (window.jQuery && jQuery.fn.DataTable && jQuery('#eventPreviewTable').length) {
            jQuery('#eventPreviewTable').DataTable({
                pageLength: 25,
                order: [[2, 'asc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
            });
        }

        syncImportButton();
    });
})();
