<?php
require_once __DIR__ . '/includes/cms_helpers.php';

if (!function_exists('e')) {
    function e(?string $value): string { return cms_e($value); }
}
if (!function_exists('cfg')) {
    function cfg(array $configMap, string $sectionName, string $key, string $default = ''): string {
        return cms_cfg($configMap, $sectionName, $key, $default);
    }
}

$institution       = null;
$sectionConfigsMap = [];
$sectionItemsMap   = [];
$categoriesById    = [];
$arrMenus          = [];
$arrSubs           = [];

try {
    $db   = cms_get_connection();
    $site = cms_get_site_data($db);

    $institution       = $site['institution'];
    $sectionConfigsMap = $site['configs'];
    $sectionItemsMap   = $site['items'];
    $categoriesById    = $site['categories'];
    $arrMenus          = $site['menus'];
    $arrSubs           = $site['subs'];
} catch (Throwable $ex) {
    error_log('todas_noticias.php: ' . $ex->getMessage());
}

$colorPrimario  = $institution['color_primario']  ?? '#2060B0';
$colorSecundario = $institution['color_secundario'] ?? '#E8A030';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todas las noticias · <?= e($institution['nombre'] ?? 'Colegio San Pablo') ?></title>
    <link rel="shortcut icon" href="<?= e($institution['favicon'] ?? 'assets/images/icono_ppt.png') ?>">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/colegiosanpablo.css">
    <style>
        .tn-hero {
            background: linear-gradient(135deg, <?= e($colorPrimario) ?> 0%, <?= e($colorSecundario) ?> 100%);
            padding: 72px 0 56px;
            color: #fff;
        }
        .tn-hero h1 {
            font-size: 38px;
            font-weight: 800;
            margin: 0 0 10px;
        }
        .tn-hero p {
            font-size: 16px;
            opacity: .85;
            margin: 0;
        }
        .tn-breadcrumb {
            font-size: 13px;
            opacity: .75;
            margin-bottom: 14px;
        }
        .tn-breadcrumb a { color: #fff; text-decoration: none; }
        .tn-breadcrumb a:hover { text-decoration: underline; }
        .tn-body { padding: 64px 0 80px; background: #f8f9fc; min-height: 40vh; }
        .tn-placeholder {
            text-align: center;
            padding: 60px 20px;
            color: #8b98a5;
        }
        .tn-placeholder i { font-size: 48px; margin-bottom: 16px; display: block; opacity: .4; }
        .tn-placeholder h3 { font-size: 22px; font-weight: 700; color: #3a4a5c; margin-bottom: 8px; }
        .tn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 50px;
            background: <?= e($colorPrimario) ?>;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-top: 24px;
            transition: filter .2s;
        }
        .tn-back:hover { filter: brightness(1.1); color: #fff; }
    </style>
</head>
<body>

    <?php
    // Header del sitio (reutilizamos el componente header)
    $headerComponent = cms_get_component_path('header_principal');
    if ($headerComponent) {
        $section = null;
        foreach (($site['sections'] ?? []) as $s) {
            if (($s['nombre_interno'] ?? '') === 'header_principal') { $section = $s; break; }
        }
        if ($section) { include $headerComponent; }
    }
    ?>

    <!-- Hero de la página -->
    <div class="tn-hero">
        <div class="container">
            <div class="tn-breadcrumb">
                <a href="index.php">Inicio</a> / Noticias
            </div>
            <h1>Todas las noticias</h1>
            <p>Entérate de todo lo que sucede en nuestra institución</p>
        </div>
    </div>

    <!-- Cuerpo — estructura pendiente -->
    <div class="tn-body">
        <div class="container">
            <div class="tn-placeholder">
                <i class="fa-regular fa-newspaper"></i>
                <h3>Próximamente</h3>
                <p>La sección de noticias completa está en construcción.</p>
                <a href="index.php#noticias" class="tn-back">
                    <i class="fa-solid fa-arrow-left"></i> Volver al inicio
                </a>
            </div>
        </div>
    </div>

    <?php
    // Footer del sitio
    $footerComponent = cms_get_component_path('footer_principal');
    if ($footerComponent) {
        $section = null;
        foreach (($site['sections'] ?? []) as $s) {
            if (($s['nombre_interno'] ?? '') === 'footer_principal') { $section = $s; break; }
        }
        if ($section) { include $footerComponent; }
    }
    ?>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
