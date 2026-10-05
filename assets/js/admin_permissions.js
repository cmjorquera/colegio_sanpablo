(function () {
    function setRowChecks(row, checked) {
        row.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
            if (!input.disabled) {
                input.checked = checked;
            }
        });
    }

    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-perm-row-toggle]');
        if (!toggle) {
            return;
        }
        var row = toggle.closest('tr');
        if (!row) {
            return;
        }
        var anyUnchecked = Array.from(row.querySelectorAll('input[type="checkbox"]')).some(function (input) {
            return !input.disabled && !input.checked;
        });
        setRowChecks(row, anyUnchecked);
    });
})();
