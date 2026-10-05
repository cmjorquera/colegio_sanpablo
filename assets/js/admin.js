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
})();
