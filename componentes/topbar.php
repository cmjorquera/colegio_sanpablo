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

if (!function_exists('sp_topbar_icon_is_image')) {
    function sp_topbar_icon_is_image(string $icon): bool
    {
        return (bool) preg_match('/\.(png|jpe?g|webp|gif|svg)(\?.*)?$/i', $icon);
    }
}
if (!function_exists('sp_topbar_resolve_social_icon')) {
    function sp_topbar_resolve_social_icon(string $icon, string $source = ''): string
    {
        $icon = trim($icon);
        if ($icon !== '' && sp_topbar_icon_is_image($icon)) {
            return $icon;
        }

        $options = [
            'instagram' => 'assets/redes_sociales/instagram.jpg',
            'insta' => 'assets/redes_sociales/instagram.jpg',
            'facebook' => 'assets/redes_sociales/facebook.jpg',
            'fb' => 'assets/redes_sociales/facebook.jpg',
            'youtube' => 'assets/redes_sociales/youtube.png',
            'youtu.be' => 'assets/redes_sociales/youtube.png',
            'twitter' => 'assets/redes_sociales/twitter.png',
            'x.com' => 'assets/redes_sociales/twitter.png',
            'linkedin' => 'assets/redes_sociales/linkeding.png',
            'linkeding' => 'assets/redes_sociales/linkeding.png',
        ];
        $haystack = strtolower(trim($icon . ' ' . $source));
        foreach ($options as $key => $path) {
            if ($haystack !== '' && strpos($haystack, $key) !== false) {
                return $path;
            }
        }

        return $icon;
    }
}
if (!function_exists('sp_topbar_social_slug')) {
    function sp_topbar_social_slug(string $icon, string $source = ''): string
    {
        $haystack = strtolower(trim($icon . ' ' . $source));
        $map = [
            'instagram' => ['instagram', 'insta'],
            'facebook' => ['facebook', 'fb'],
            'youtube' => ['youtube', 'youtu.be'],
            'twitter' => ['twitter', 'x.com'],
            'linkedin' => ['linkedin', 'linkeding'],
        ];
        foreach ($map as $slug => $keys) {
            foreach ($keys as $key) {
                if ($haystack !== '' && strpos($haystack, $key) !== false) {
                    return $slug;
                }
            }
        }

        return 'generic';
    }
}

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
<style>
    .sp-topbar-socials {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .sp-social-link {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .96);
        color: #fff;
        box-shadow: 0 8px 18px rgba(0, 0, 0, .18);
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        overflow: hidden;
    }
    .sp-social-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(0, 0, 0, .24);
        filter: brightness(1.03);
    }
    .sp-social-link img {
        width: 25px;
        height: 25px;
        display: block;
        object-fit: contain;
    }
    .sp-social-link i {
        font-size: 1rem;
    }
    .sp-social-instagram,
    .sp-social-facebook,
    .sp-social-youtube,
    .sp-social-twitter,
    .sp-social-linkedin { background: rgba(255, 255, 255, .96); }
    .sp-social-generic { background: rgba(255, 255, 255, .16); }
    .sp-social-youtube img,
    .sp-social-instagram img,
    .sp-social-facebook img,
    .sp-social-twitter img,
    .sp-social-linkedin img {
        width: 25px;
        height: 25px;
    }
</style>
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
                    <div class="sp-topbar-socials">
                        <?php foreach ($redesTopbar as $red): ?>
                            <?php $urlRed = trim((string) ($red['descripcion'] ?? '')); ?>
                            <?php if ($urlRed === '') { continue; } ?>
                            <?php $iconoRed = sp_topbar_resolve_social_icon((string) ($red['icono'] ?? ''), (string) (($red['titulo'] ?? '') . ' ' . ($red['descripcion'] ?? ''))); ?>
                            <?php $socialSlug = sp_topbar_social_slug($iconoRed, (string) (($red['titulo'] ?? '') . ' ' . ($red['descripcion'] ?? ''))); ?>
                            <a href="<?= e($urlRed) ?>" target="_blank" rel="noopener" title="<?= e($red['titulo'] ?? 'Red social') ?>" aria-label="<?= e($red['titulo'] ?? 'Red social') ?>" 
                            class="sp-social-link sp-social-<?= e($socialSlug) ?>">
                                <?php if ($iconoRed !== '' && sp_topbar_icon_is_image($iconoRed)): ?>
                                    <img src="<?= e($iconoRed) ?>" alt="<?= e($red['titulo'] ?? 'Red social') ?>">
                                <?php else: ?>
                                    <i class="<?= e($iconoRed ?: 'fas fa-link') ?>"></i>
                                <?php endif; ?>
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

                <div class="sp-modal-areas" id="loginAreaSelector">
                    <a href="#" class="sp-area-btn sp-area-admin" id="showAdminLogin">
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

                <form class="sp-login-form" id="adminLoginForm" style="display:none">
                    <a href="#" class="sp-volver-link" id="backToLoginAreas"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                    <div id="loginAlert" class="sp-login-alert" style="display:none"></div>

                    <div class="sp-field">
                        <label for="loginUsuario"><i class="fas fa-user"></i>Usuario o email</label>
                        <input type="text" id="loginUsuario" name="usuario" placeholder="nombre@dominio.cl" autocomplete="username" required>
                    </div>

                    <div class="sp-field">
                        <label for="loginClave"><i class="fas fa-key"></i>Clave</label>
                        <div class="sp-pass-wrap">
                            <input type="password" id="loginClave" name="clave" placeholder="********" autocomplete="current-password" required>
                            <button type="button" class="sp-toggle-pass" id="toggleLoginPass" aria-label="Mostrar clave">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="btnLogin" class="sp-btn-login">
                        <i class="fas fa-sign-in-alt me-1"></i>Ingresar al administrador
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('modalLogin');
        var areaSelector = document.getElementById('loginAreaSelector');
        var showAdminLogin = document.getElementById('showAdminLogin');
        var adminForm = document.getElementById('adminLoginForm');
        var backButton = document.getElementById('backToLoginAreas');
        var alertBox = document.getElementById('loginAlert');
        var usuarioInput = document.getElementById('loginUsuario');
        var claveInput = document.getElementById('loginClave');
        var togglePass = document.getElementById('toggleLoginPass');
        var submitButton = document.getElementById('btnLogin');

        if (!modal || !areaSelector || !showAdminLogin || !adminForm) {
            return;
        }

        function showAlert(message, type) {
            if (!alertBox) {
                return;
            }
            alertBox.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i>' + message;
            alertBox.className = 'sp-login-alert ' + (type === 'success' ? 'success' : 'error');
            alertBox.style.display = 'flex';
        }

        function clearAlert() {
            if (alertBox) {
                alertBox.style.display = 'none';
                alertBox.className = 'sp-login-alert';
                alertBox.textContent = '';
            }
            if (usuarioInput) {
                usuarioInput.classList.remove('is-invalid');
            }
            if (claveInput) {
                claveInput.classList.remove('is-invalid');
            }
        }

        showAdminLogin.addEventListener('click', function (event) {
            event.preventDefault();
            areaSelector.style.display = 'none';
            adminForm.style.display = 'flex';
            clearAlert();
            setTimeout(function () {
                if (usuarioInput) {
                    usuarioInput.focus();
                }
            }, 80);
        });

        if (backButton) {
            backButton.addEventListener('click', function (event) {
                event.preventDefault();
                adminForm.style.display = 'none';
                areaSelector.style.display = '';
                clearAlert();
            });
        }

        if (togglePass && claveInput) {
            togglePass.addEventListener('click', function () {
                var isPassword = claveInput.type === 'password';
                claveInput.type = isPassword ? 'text' : 'password';
                togglePass.innerHTML = '<i class="fas ' + (isPassword ? 'fa-eye-slash' : 'fa-eye') + '"></i>';
            });
        }

        adminForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAlert();

            var usuario = usuarioInput ? usuarioInput.value.trim() : '';
            var clave = claveInput ? claveInput.value.trim() : '';

            if (!usuario || !clave) {
                if (!usuario && usuarioInput) {
                    usuarioInput.classList.add('is-invalid');
                }
                if (!clave && claveInput) {
                    claveInput.classList.add('is-invalid');
                }
                showAlert('Completa usuario y clave', 'error');
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Validando...';
            }

            fetch('login_check.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'usuario=' + encodeURIComponent(usuario) + '&clave=' + encodeURIComponent(clave)
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.ok) {
                        showAlert('Acceso correcto. Redirigiendo...', 'success');
                        window.location.href = data.redirect || 'admin.php';
                        return;
                    }

                    showAlert(data.msg || 'Usuario o clave incorrectos', 'error');
                    if (claveInput) {
                        claveInput.classList.add('is-invalid');
                        claveInput.focus();
                    }
                })
                .catch(function () {
                    showAlert('No fue posible validar el acceso. Intenta nuevamente.', 'error');
                })
                .finally(function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fas fa-sign-in-alt me-1"></i>Ingresar al administrador';
                    }
                });
        });

        modal.addEventListener('hidden.bs.modal', function () {
            adminForm.reset();
            adminForm.style.display = 'none';
            areaSelector.style.display = '';
            clearAlert();
            if (claveInput) {
                claveInput.type = 'password';
            }
            if (togglePass) {
                togglePass.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    })();
</script>
