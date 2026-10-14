<?php
// Vista del acceso: el procesamiento de credenciales permanece en login_check.php.
$institution = [];
try {
    $loginDb = cms_get_connection();
    $loginInstitutionId = cms_get_institution_id($loginDb);
    $result = $loginDb->query('SELECT * FROM institucion WHERE id_institucion = ' . $loginInstitutionId . ' LIMIT 1');
    $institution = $result ? ($result->fetch_assoc() ?: []) : [];
} catch (Throwable $exception) {
    error_log('admin.php: no fue posible cargar la identidad institucional del login.');
}

$nombreInstitucion = trim((string) ($institution['nombre'] ?? '')) ?: 'Colegio San Pablo';
$logoInstitucion = trim((string) ($institution['logo_header'] ?? '')) ?: 'assets/images/logo/logo.svg';
$loginColors = [];
foreach (['primario' => '#F0A000', 'secundario' => '#EF6C00', 'terciario' => '#1976D2', 'cuaternario' => '#E53935'] as $key => $fallback) {
    $value = trim((string) ($institution['color_' . $key] ?? ''));
    $loginColors[$key] = preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value) ? $value : $fallback;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Administración | <?= cms_e($nombreInstitucion) ?></title>
    <link rel="stylesheet" href="assets/admin/css/admin-login.css">
</head>
<body class="admin-login" style="<?php foreach ($loginColors as $key => $color): ?>--login-<?= $key ?>:<?= cms_e($color) ?>;<?php endforeach; ?>">
    <main class="admin-login-shell">
        <section class="admin-login-brand" aria-labelledby="institution-title">
            <div class="admin-login-brand-top">
                <img class="admin-login-logo" src="<?= cms_e($logoInstitucion) ?>" alt="<?= cms_e($nombreInstitucion) ?>" id="institution-logo">
                <span class="admin-login-brand-kicker">Gestión institucional</span>
            </div>
            <div class="admin-login-intro">
                <p class="admin-login-eyebrow"><span class="admin-login-status-dot"></span> Plataforma de administración educativa</p>
                <h1 id="institution-title"><?= cms_e($nombreInstitucion) ?></h1>
                <p>Un espacio seguro para administrar contenidos, comunicaciones y la presencia digital de nuestra comunidad educativa.</p>
                <div class="admin-login-brand-features" aria-label="Funciones de la plataforma">
                    <span>Contenidos</span><span>Comunicaciones</span><span>Comunidad</span>
                </div>
            </div>
            <div class="admin-login-brand-bottom">
                <div class="admin-login-colorband" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                <p>Una experiencia digital pensada para instituciones educativas.</p>
            </div>
        </section>
        <section class="admin-login-content" aria-labelledby="login-title">
            <div class="admin-login-content-inner">
                <div class="admin-login-platform" aria-label="Información de la plataforma">
                    <div class="admin-login-platform-mark" aria-hidden="true">
                        <span class="admin-login-platform-symbol">W</span>
                    </div>
                    <div class="admin-login-platform-copy">
                        <span class="admin-login-platform-eyebrow">Tecnología y desarrollo</span>
                        <a href="https://webnia.cl/" target="_blank" rel="noopener noreferrer" class="admin-login-platform-name" aria-label="Visitar Webnia">webnia<span>.cl</span> <span aria-hidden="true">↗</span></a>
                        <p>Plataforma adaptable a distintas instituciones educativas.</p>
                    </div>
                </div>
                <div class="admin-login-card">
                    <p class="admin-login-eyebrow">Acceso institucional</p>
                    <h2 id="login-title">Bienvenido de nuevo</h2>
                    <p class="admin-login-subtitle">Ingresa tus credenciales para acceder al panel administrativo.</p>
                    <form id="admin-access-form" action="login_check.php" method="post">
                        <div id="admin-access-alert" class="admin-login-alert" role="alert" hidden></div>
                        <div class="admin-login-field">
                            <label for="admin-usuario">Usuario o correo electrónico</label>
                            <input id="admin-usuario" name="usuario" type="text" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Tu usuario o correo" required>
                        </div>
                        <div class="admin-login-field">
                            <label for="admin-clave">Contraseña</label>
                            <div class="admin-login-password">
                                <input id="admin-clave" name="clave" type="password" autocomplete="current-password" placeholder="Ingresa tu contraseña" required>
                                <button id="admin-password-toggle" type="button" aria-label="Mostrar contraseña" aria-pressed="false" aria-controls="admin-clave">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7-3.5 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/><path class="admin-eye-slash" d="m3 3 18 18" hidden/></svg>
                                </button>
                            </div>
                        </div>
                        <button id="admin-access-submit" class="admin-login-submit" type="submit">Ingresar al panel <span aria-hidden="true">→</span></button>
                    </form>
                    <div class="admin-login-card-footer"><span aria-hidden="true">●</span> Acceso exclusivo para personal autorizado</div>
                    <noscript><p>Activa JavaScript en tu navegador para iniciar sesión desde este formulario.</p></noscript>
                </div>
                <div class="admin-login-ecosystem">
                    <span class="admin-login-ecosystem-label">Diseñado para entornos educativos</span>
                    <div class="admin-login-ecosystem-tags" aria-label="Tipos de instituciones compatibles">
                        <span>◈ Colegios</span><span>▦ Liceos</span><span>◇ Redes educativas</span>
                    </div>
                    <p>Desarrollado por <a href="https://webnia.cl/" target="_blank" rel="noopener noreferrer">Webnia.cl</a> · <?= date('Y') ?></p>
                </div>
            </div>
        </section>
    </main>
    <script src="assets/admin/js/admin-login.js" defer></script>
</body>
</html>
