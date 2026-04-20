<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: colegiosanpablo.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';

$db = cms_get_connection();
$site = cms_get_site_data($db);
$idSeccion = (int) ($_GET['id'] ?? 0);
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

function e(?string $value): string { return cms_e($value); }
function cfg(array $map, string $sectionName, string $key, string $default = ''): string { return cms_cfg($map, $sectionName, $key, $default); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview | <?= cms_e($section['titulo_admin']) ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/meanmenu.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/colegiosanpablo.css">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h1 class="h4 mb-1"><?= cms_e($section['titulo_admin']) ?></h1>
                <div class="text-muted"><code><?= cms_e($section['nombre_interno']) ?></code></div>
            </div>
            <div class="d-flex gap-2">
                <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>" class="btn btn-outline-secondary">Editar</a>
                <a href="<?= cms_e(cms_get_preview_target($section['nombre_interno'])) ?>" target="_blank" class="btn btn-success">Ver en sitio</a>
            </div>
        </div>
    </div>

    <?php
    $component = cms_get_component_path($section['nombre_interno']);
    if ($component) {
        include $component;
    } else {
        echo '<div class="container"><div class="alert alert-warning">No existe componente para este contenedor todavía.</div></div>';
    }
    ?>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var carouselEl = document.getElementById('heroCarousel');
            if (carouselEl) {
                new bootstrap.Carousel(carouselEl, { interval: 5000, ride: 'carousel', pause: 'hover', wrap: true });
            }
        });
    </script>
</body>
</html>
