<?php
require_once __DIR__ . '/includes/cms_helpers.php';

function e(?string $value): string
{
    return cms_e($value);
}

function cfg(array $configMap, string $sectionName, string $key, string $default = ''): string
{
    return cms_cfg($configMap, $sectionName, $key, $default);
}

$institution = null;
$sections = [];
$sectionConfigsMap = [];
$sectionItemsMap = [];
$categoriesById = [];
$arrMenus = [];
$arrSubs = [];

try {
    $db = cms_get_connection();
    $site = cms_get_site_data($db);

    $institution = $site['institution'];
    $sectionConfigsMap = $site['configs'];
    $sectionItemsMap = $site['items'];
    $categoriesById = $site['categories'];
    $arrMenus = $site['menus'];
    $arrSubs = $site['subs'];

    foreach ($site['sections'] as $section) {
        if (($section['visible'] ?? 'no') === 'si') {
            $sections[] = $section;
        }
    }
} catch (Throwable $exception) {
    error_log('index.php: ' . $exception->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($institution['nombre'] ?? 'Colegio San Pablo') ?></title>
    <link rel="shortcut icon" href="<?= e($institution['favicon'] ?? 'assets/images/icono_ppt.png') ?>">
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

    <div class="sp-colorband"></div>

    <?php foreach ($sections as $section): ?>
        <?php
        $sectionName = $section['nombre_interno'] ?? '';

        if ($sectionName === 'menu_principal') {
            continue;
        }

        $component = cms_get_component_path($sectionName);

        if ($component) {
            include $component;
        }
        ?>
    <?php endforeach; ?>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/meanmenu.js"></script>
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/magnific-popup.min.js"></script>
    <script src="assets/js/nice-select.min.js"></script>
    <script src="assets/js/parallax.js"></script>
    <script src="assets/js/jquery.waypoints.js"></script>
    <script src="assets/js/script.js"></script>
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

            document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
                anchor.addEventListener('click', function (event) {
                    var selector = this.getAttribute('href');
                    if (!selector || selector === '#') {
                        return;
                    }

                    var target = document.querySelector(selector);
                    if (target) {
                        event.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        });
    </script>
</body>
</html>
