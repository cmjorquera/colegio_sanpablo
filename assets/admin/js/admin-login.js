(function () {
    'use strict';
    var form = document.getElementById('admin-access-form');
    if (!form) return;

    var password = document.getElementById('admin-clave');
    var toggle = document.getElementById('admin-password-toggle');
    var alertBox = document.getElementById('admin-access-alert');
    var submit = document.getElementById('admin-access-submit');
    var logo = document.getElementById('institution-logo');
    logo.addEventListener('error', function () {
        if (logo.getAttribute('src') !== 'assets/images/logo/logo.svg') {
            logo.src = 'assets/images/logo/logo.svg';
        }
    });

    toggle.addEventListener('click', function () {
        var visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        toggle.setAttribute('aria-pressed', String(visible));
        toggle.querySelector('.admin-eye-slash').toggleAttribute('hidden', !visible);
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (submit.disabled) return;
        alertBox.hidden = true;
        submit.disabled = true;
        form.setAttribute('aria-busy', 'true');
        submit.textContent = 'Validando acceso…';
        try {
            var response = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            });
            var data = await response.json();
            if (response.ok && data.ok) {
                window.location.assign(data.redirect || 'admin.php');
                return;
            }
            alertBox.textContent = data.msg || 'No fue posible iniciar sesión. Intenta nuevamente.';
            alertBox.hidden = false;
        } catch (error) {
            alertBox.textContent = 'No fue posible validar el acceso. Intenta nuevamente.';
            alertBox.hidden = false;
        } finally {
            submit.disabled = false;
            form.removeAttribute('aria-busy');
            submit.textContent = 'Ingresar al panel';
        }
    });
})();
