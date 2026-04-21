<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: colegiosanpablo.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_layout.php';

$db = cms_get_connection();
$site = cms_get_site_data($db);
$idSeccion = (int) ($_GET['id'] ?? 0);
$embed = isset($_GET['embed']) && $_GET['embed'] === '1';
$section = cms_find_section($site['sections'], $idSeccion);

if (!$section) {
    cms_set_flash('danger', 'No se encontró el contenedor solicitado.');
    cms_redirect('admin.php?panel=contenedores');
}

$institution = $site['institution'];
$sectionConfigsMap = $site['configs'];
$sectionItemsMap = $site['items'];
$categoriesById = $site['categories'];
$arrMenus = $site['menus'];
$arrSubs = $site['subs'];

function e(?string $value): string
{
    return cms_e($value);
}

function cfg(array $map, string $sectionName, string $key, string $default = ''): string
{
    return cms_cfg($map, $sectionName, $key, $default);
}

if ($embed) {
    $component = cms_get_component_path($section['nombre_interno']);
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/meanmenu.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/colegiosanpablo.css">
    <style>
        body { margin: 0; background: #ffffff; }
    </style>
</head>
<body>
    <?php
    if ($component) {
        include $component;
    } else {
        echo '<div class="p-4"><div class="alert alert-warning mb-0">No existe componente para este contenedor todavía.</div></div>';
    }
    ?>
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var carouselEl = document.getElementById('heroCarousel');
            if (carouselEl) {
                new bootstrap.Carousel(carouselEl, {
                    interval: 5000,
                    ride: 'carousel',
                    pause: 'hover',
                    wrap: true
                });
            }
        });
    </script>
</body>
</html>
<?php
    exit;
}

admin_render_layout_start([
    'title' => 'Preview | ' . ($section['titulo_admin'] ?? 'Contenedor'),
    'page_title' => 'Vista previa del contenedor',
    'breadcrumb' => 'Contenedores del sitio / ' . ($section['nombre_interno'] ?? ''),
    'active_panel' => 'contenedores',
    'institution_name' => $institution['nombre'] ?? 'Institución activa',
    'institution_short_name' => $institution['nombre_corto'] ?? ($institution['nombre'] ?? 'Institución'),
    'institution_logo' => $institution['logo_header'] ?? '',
    'admin_name' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'header_actions' => '<a href="editar_contenedor.php?id=' . (int) $idSeccion . '" class="btn btn-soft"><i class="bi bi-pencil-square me-2"></i>Editar</a><a href="' . cms_e(cms_get_preview_target($section['nombre_interno'])) . '" target="_blank" class="btn btn-premium"><i class="bi bi-box-arrow-up-right me-2"></i>Ver en sitio</a>',
    'extra_head' => <<<'HTML'
    <link rel="stylesheet" href="assets/css/meanmenu.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/colegiosanpablo.css">
    <style>
        .preview-shell { overflow: hidden; padding: 0; }
        .preview-toolbar { padding: 22px 22px 0; }
        .preview-stage { background: #fff; border-radius: 0 0 28px 28px; overflow: hidden; }
        .preview-stage .container:first-child,
        .preview-stage .container-fluid:first-child { padding-top: 0; }
    </style>
HTML,
]);
?>

<div class="section-card preview-shell">
    <div class="preview-toolbar">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h3 class="mb-1"><?= cms_e($section['titulo_admin']) ?></h3>
                <div class="text-muted"><code><?= cms_e($section['nombre_interno']) ?></code></div>
            </div>
        </div>
    </div>

    <div class="preview-stage">
        <?php
        $component = cms_get_component_path($section['nombre_interno']);
        if ($component) {
            include $component;
        } else {
            echo '<div class="p-4"><div class="alert alert-warning mb-0">No existe componente para este contenedor todavía.</div></div>';
        }
        ?>
    </div>
</div>

<?php
admin_render_layout_end([
    'extra_scripts' => <<<'HTML'
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var carouselEl = document.getElementById('heroCarousel');
            if (carouselEl) {
                new bootstrap.Carousel(carouselEl, {
                    interval: 5000,
                    ride: 'carousel',
                    pause: 'hover',
                    wrap: true
                });
            }
        });
    </script>
HTML,
]);
