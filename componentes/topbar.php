<?php
$email = trim((string) ($institution['email'] ?? ''));
$telefono = trim((string) ($institution['telefono'] ?? ''));
$direccion = trim((string) ($institution['direccion'] ?? ''));
$mostrarDireccion = cfg($sectionConfigsMap, 'topbar', 'mostrar_direccion', 'si') === 'si';
$mostrarTelefono = cfg($sectionConfigsMap, 'topbar', 'mostrar_telefono', 'si') === 'si';
$mostrarEmail = cfg($sectionConfigsMap, 'topbar', 'mostrar_email', 'si') === 'si';
$mostrarRedes = cfg($sectionConfigsMap, 'topbar', 'mostrar_redes', 'si') === 'si';
$mostrarBotonIngresar = cfg($sectionConfigsMap, 'topbar', 'mostrar_boton_ingresar', 'si') === 'si';
$textoBotonIngresar = trim(cfg($sectionConfigsMap, 'topbar', 'texto_boton_ingresar', 'Ingresar'));
$textoBotonIngresar = $textoBotonIngresar !== '' ? $textoBotonIngresar : 'Ingresar';
$colorPrimario = trim((string) ($institution['color_primario'] ?? '')) ?: '#2563EB';
$colorSecundario = trim((string) ($institution['color_secundario'] ?? '')) ?: '#E9A629';
$colorTerciario = trim((string) ($institution['color_terciario'] ?? '')) ?: '#222222';
$topbarGradient = 'linear-gradient(90deg, ' . $colorPrimario . ', ' . $colorSecundario . ', ' . $colorTerciario . ')';
$redesTopbar = array_values(array_filter(
    $sectionItemsMap['topbar'] ?? [],
    static fn(array $item): bool => ($item['etiqueta'] ?? '') === 'red_social'
));
$redesTopbar = array_slice($redesTopbar, 0, 4);

$contactos = [];
if ($mostrarDireccion && $direccion !== '') {
    $contactos[] = [
        'icono' => 'fas fa-map-marker-alt',
        'contenido' => e($direccion),
    ];
}
if ($mostrarTelefono && $telefono !== '') {
    $contactos[] = [
        'icono' => 'fas fa-phone',
        'contenido' => e($telefono),
    ];
}
if ($mostrarEmail && $email !== '') {
    $contactos[] = [
        'icono' => 'fas fa-envelope',
        'contenido' => '<a href="mailto:' . e($email) . '">' . e($email) . '</a>',
    ];
}
?>
<div class="sp-topbar d-none d-md-block" id="topbar" style=" background: var(--sp-negro);color: #e0e0e0;padding: 8px 0;   font-size: 13px;">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center flex-wrap">
                <?php foreach ($contactos as $index => $contacto): ?>
                    <?php if ($index > 0): ?><span class="sep">|</span><?php endif; ?>
                    <span class="d-inline-flex align-items-center">
                        <i class="<?= e($contacto['icono']) ?> me-2" style="color:<?= e($colorSecundario) ?>"></i><?= $contacto['contenido'] ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <?php if ($mostrarBotonIngresar): ?>
                    <a href="#" class="sp-login-btn" data-bs-toggle="modal" data-bs-target="#modalLogin" title="Ingresar al sistema">
                        <i class="fas fa-sign-in-alt"></i>
                        <span><?= e($textoBotonIngresar) ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($mostrarRedes && $redesTopbar): ?>
                    <?php if ($mostrarBotonIngresar): ?><span class="sep">|</span><?php endif; ?>
                    <div class="d-flex align-items-center gap-2">
                        <?php foreach ($redesTopbar as $red): ?>
                            <?php $urlRed = trim((string) ($red['descripcion'] ?? '')); ?>
                            <?php if ($urlRed === '') { continue; } ?>
                            <a href="<?= e($urlRed) ?>" target="_blank" rel="noopener" title="<?= e($red['titulo'] ?? 'Red social') ?>" aria-label="<?= e($red['titulo'] ?? 'Red social') ?>" 
                            class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:32px;height:32px;background:rgba(255,255,255,.14); color:#e0e0e0;;">
                                <i class="<?= e($red['icono'] ?: 'fas fa-link') ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sp-modal-login">
            <div class="sp-modal-colorband"></div>
            <div class="modal-body p-0">
                <div class="sp-modal-header">
                    <button type="button" class="sp-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="sp-modal-logo">
                        <i class="fas fa-school"></i>
                    </div>
                    <h5 id="modalLoginLabel">Mi Colegio San Pablo</h5>
                    <p>Elige el área a la que deseas ingresar:</p>
                </div>

                <div class="sp-modal-areas">
                    <a href="#" class="sp-area-btn sp-area-admin" onclick="return modalEntradaAdministrador(event);">
                        <i class="fas fa-lock"></i>
                        <span>Administrador</span>
                        <i class="fas fa-chevron-right sp-area-arrow"></i>
                    </a>

                    <a href="#" class="sp-area-btn" data-bs-dismiss="modal">
                        <i class="fas fa-user-graduate"></i>
                        <span>Área Alumnos</span>
                        <i class="fas fa-chevron-right sp-area-arrow"></i>
                    </a>

                    <a href="#" class="sp-area-btn" data-bs-dismiss="modal">
                        <i class="fas fa-users"></i>
                        <span>Área Padres</span>
                        <i class="fas fa-chevron-right sp-area-arrow"></i>
                    </a>

                    <a href="#" class="sp-area-btn" data-bs-dismiss="modal">
                        <i class="fas fa-briefcase"></i>
                        <span>Área Funcionario</span>
                        <i class="fas fa-chevron-right sp-area-arrow"></i>
                    </a>

                    <a href="#" class="sp-area-btn" data-bs-dismiss="modal">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Área Docentes</span>
                        <i class="fas fa-chevron-right sp-area-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAdminLogin" tabindex="-1" aria-labelledby="modalAdminLoginLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content sp-modal-login">
            <div class="sp-modal-colorband"></div>
            <div class="modal-body p-0">
                <div class="sp-modal-header">
                    <button type="button" class="sp-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="sp-modal-logo" style="background:rgba(206,53,73,.12);border-color:rgba(206,53,73,.35)">
                        <i class="fas fa-shield-alt" style="color:var(--sp-rojo)"></i>
                    </div>
                    <h5 id="modalAdminLoginLabel">Acceso Administrador</h5>
                    <p>Ingresa tu email y clave para continuar</p>
                </div>

                <form id="formAdminLogin" novalidate autocomplete="off">
                    <div class="sp-login-form">
                        <div id="loginAlert" class="sp-login-alert" style="display:none"></div>

                        <div class="sp-field">
                            <label for="loginEmail">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" id="loginEmail" name="email" placeholder="nombre@dominio.cl" autocomplete="username" required>
                        </div>

                        <div class="sp-field">
                            <label for="loginClave">
                                <i class="fas fa-lock"></i> Clave
                            </label>
                            <div class="sp-pass-wrap">
                                <input type="password" id="loginClave" name="clave" placeholder="••••••••" autocomplete="current-password" required>
                                <button type="button" class="sp-toggle-pass" id="adminTogglePass" tabindex="-1" title="Mostrar/ocultar clave">
                                    <i class="fas fa-eye" id="iconEye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="btnLogin" class="sp-btn-login">
                            <span id="btnLoginTxt">
                                <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                            </span>
                            <span id="btnLoginSpinner" style="display:none">
                                <span class="spinner-border spinner-border-sm me-2"></span>Verificando...
                            </span>
                        </button>

                        <div class="text-center mt-3">
                            <a href="#" class="sp-volver-link" onclick="return modalEntradaAdministrador(event, 'modalAdminLogin', 'modalLogin');">
                                <i class="fas fa-arrow-left me-1"></i>Volver
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function modalEntradaAdministrador(event, modalOrigenId = 'modalLogin', modalDestinoId = 'modalAdminLogin') {
    if (event) {
        event.preventDefault();
    }

    var origenEl = document.getElementById(modalOrigenId);
    var destinoEl = document.getElementById(modalDestinoId);

    if (!destinoEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
        return false;
    }

    var abrirDestino = function () {
        bootstrap.Modal.getOrCreateInstance(destinoEl).show();
    };

    if (origenEl) {
        origenEl.addEventListener('hidden.bs.modal', abrirDestino, { once: true });
        bootstrap.Modal.getOrCreateInstance(origenEl).hide();
    } else {
        abrirDestino();
    }

    return false;
}

document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formAdminLogin');
    var modalAdminLogin = document.getElementById('modalAdminLogin');
    var loginAlert = document.getElementById('loginAlert');
    var btnTxt = document.getElementById('btnLoginTxt');
    var btnSpinner = document.getElementById('btnLoginSpinner');
    var btnLogin = document.getElementById('btnLogin');
    var eyeBtn = document.getElementById('adminTogglePass');
    var claveInput = document.getElementById('loginClave');
    var emailInput = document.getElementById('loginEmail');
    var eyeIcon = document.getElementById('iconEye');

    if (!form || !modalAdminLogin || !loginAlert || !btnTxt || !btnSpinner || !btnLogin || !claveInput || !emailInput) {
        return;
    }

    function limpiarEstadoLogin() {
        loginAlert.style.display = 'none';
        loginAlert.className = 'sp-login-alert';
        form.reset();
        emailInput.classList.remove('is-invalid');
        claveInput.classList.remove('is-invalid');
        claveInput.type = 'password';
        if (eyeIcon) {
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    function mostrarAlerta(msg, tipo) {
        loginAlert.innerHTML = '<i class="fas ' + (tipo === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle') + '"></i> ' + msg;
        loginAlert.className = 'sp-login-alert ' + tipo;
        loginAlert.style.display = 'flex';
    }

    function setLoading(loading) {
        btnLogin.disabled = loading;
        btnTxt.style.display = loading ? 'none' : 'inline';
        btnSpinner.style.display = loading ? 'inline' : 'none';
    }

    modalAdminLogin.addEventListener('show.bs.modal', limpiarEstadoLogin);

    if (eyeBtn) {
        eyeBtn.addEventListener('click', function () {
            var mostrar = claveInput.type === 'password';
            claveInput.type = mostrar ? 'text' : 'password';
            if (eyeIcon) {
                eyeIcon.classList.toggle('fa-eye', !mostrar);
                eyeIcon.classList.toggle('fa-eye-slash', mostrar);
            }
        });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var email = emailInput.value.trim();
        var clave = claveInput.value.trim();

        if (!email || !clave) {
            mostrarAlerta('Completa email y clave', 'error');
            if (!email) {
                emailInput.classList.add('is-invalid');
            }
            if (!clave) {
                claveInput.classList.add('is-invalid');
            }
            return;
        }

        setLoading(true);
        loginAlert.style.display = 'none';

        fetch('login_check.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'email=' + encodeURIComponent(email) + '&clave=' + encodeURIComponent(clave)
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                setLoading(false);

                if (data.ok) {
                    mostrarAlerta('Acceso correcto. Redirigiendo...', 'success');
                    setTimeout(function () {
                        window.location.href = data.redirect || 'admin.php';
                    }, 900);
                    return;
                }

                mostrarAlerta(data.msg || 'Email o clave incorrectos', 'error');
                emailInput.classList.add('is-invalid');
                claveInput.classList.add('is-invalid');
            })
            .catch(function () {
                setLoading(false);
                mostrarAlerta('Error de conexión. Intenta nuevamente.', 'error');
            });
    });

    [emailInput, claveInput].forEach(function (input) {
        input.addEventListener('input', function () {
            this.classList.remove('is-invalid');
            loginAlert.style.display = 'none';
        });
    });
});
</script>
