<?php
require_once __DIR__ . '/class/conexion.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cfg(array $configMap, string $sectionName, string $key, string $default = ''): string
{
    return $configMap[$sectionName][$key] ?? $default;
}

$institution = null;
$sections = [];
$sectionConfigsMap = [];
$sectionItemsMap = [];
$categoriesById = [];
$arrMenus = [];
$arrSubs = [];

try {
    $db = (new Conexion())->getConexion();

    $resInstitution = $db->query("SELECT * FROM institucion WHERE estado = 'activo' ORDER BY id_institucion ASC LIMIT 1");
    if ($resInstitution) {
        $institution = $resInstitution->fetch_assoc();
    }

    $resMenus = $db->query("SELECT id_menu, nombre, url, icono, orden FROM menus WHERE estado = 1 ORDER BY orden ASC, id_menu ASC");
    if ($resMenus) {
        $arrMenus = $resMenus->fetch_all(MYSQLI_ASSOC);
        $resMenus->free();
    }

    $resSubs = $db->query("SELECT id_sub_menu, id_menu, nombre, url, icono, orden FROM sub_menus WHERE estado = 1 ORDER BY id_menu ASC, orden ASC, id_sub_menu ASC");
    if ($resSubs) {
        while ($row = $resSubs->fetch_assoc()) {
            $arrSubs[(int) $row['id_menu']][] = $row;
        }
        $resSubs->free();
    }

    $resSections = $db->query("SELECT * FROM seccion WHERE visible = 'si' ORDER BY orden ASC, id_seccion ASC");
    if ($resSections) {
        $sections = $resSections->fetch_all(MYSQLI_ASSOC);
        $resSections->free();
    }

    $resConfigs = $db->query("SELECT sc.*, s.nombre_interno FROM seccion_config sc INNER JOIN seccion s ON s.id_seccion = sc.id_seccion");
    if ($resConfigs) {
        while ($row = $resConfigs->fetch_assoc()) {
            $sectionConfigsMap[$row['nombre_interno']][$row['clave']] = $row['valor'];
        }
        $resConfigs->free();
    }

    $resItems = $db->query("SELECT si.*, s.nombre_interno
        FROM seccion_item si
        INNER JOIN seccion s ON s.id_seccion = si.id_seccion
        WHERE si.visible = 'si'
        ORDER BY s.orden ASC, si.orden ASC, si.id_item ASC");
    if ($resItems) {
        while ($row = $resItems->fetch_assoc()) {
            $sectionItemsMap[$row['nombre_interno']][] = $row;
        }
        $resItems->free();
    }

    $resTableCheck = $db->query("SHOW TABLES LIKE 'categoria_noticia'");
    if ($resTableCheck && $resTableCheck->num_rows > 0) {
        $resCategories = $db->query("SELECT * FROM categoria_noticia ORDER BY nombre ASC");
        if ($resCategories) {
            while ($row = $resCategories->fetch_assoc()) {
                $categoriesById[(int) $row['id_categoria']] = $row;
            }
            $resCategories->free();
        }
    }
} catch (RuntimeException $e) {
    error_log('index.php: ' . $e->getMessage());
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
        $sectionName = $section['nombre_interno'];
        switch ($sectionName) {
            case 'topbar':
                include __DIR__ . '/componentes/topbar.php';
                break;
            case 'header_principal':
                include __DIR__ . '/componentes/header_principal.php';
                break;
            case 'hero_principal':
                include __DIR__ . '/componentes/hero_principal.php';
                break;
            case 'noticias_home':
                include __DIR__ . '/componentes/noticias_home.php';
                break;
            case 'faq_home':
                include __DIR__ . '/componentes/faq_home.php';
                break;
            case 'about_home':
                include __DIR__ . '/componentes/about_home.php';
                break;
        }
        ?>
    <?php endforeach; ?>

    <footer class="sp-footer">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <div class="logo-footer mb-3">
                        <img src="<?= e($institution['logo_footer'] ?? $institution['logo_header'] ?? 'assets/images/logo/logo-light.svg') ?>" alt="<?= e($institution['nombre'] ?? 'Colegio San Pablo') ?>" onerror="this.src='assets/images/logo/logo-light.svg'">
                    </div>
                    <p><?= e($institution['nombre'] ?? 'Colegio San Pablo') ?> acompaña a su comunidad con una propuesta educativa integral y cercana.</p>
                    <div class="social-links mt-3">
                        <?php if (!empty($institution['instagram'])): ?><a href="<?= e($institution['instagram']) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a><?php endif; ?>
                        <?php if (!empty($institution['facebook'])): ?><a href="<?= e($institution['facebook']) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                        <?php if (!empty($institution['youtube'])): ?><a href="<?= e($institution['youtube']) ?>" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a><?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h5>Menú rápido</h5>
                    <ul class="list-unstyled mt-3">
                        <?php foreach ($arrMenus as $menu): ?>
                            <li class="mb-2"><a href="<?= e($menu['url'] ?: '#') ?>"><i class="fas fa-chevron-right me-2"></i><?= e($menu['nombre']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5>Contacto</h5>
                    <ul class="list-unstyled mt-3">
                        <?php if (!empty($institution['direccion'])): ?><li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><?= e($institution['direccion']) ?></li><?php endif; ?>
                        <?php if (!empty($institution['telefono'])): ?><li class="mb-2"><i class="fas fa-phone me-2"></i><?= e($institution['telefono']) ?></li><?php endif; ?>
                        <?php if (!empty($institution['email'])): ?><li class="mb-2"><i class="fas fa-envelope me-2"></i><a href="mailto:<?= e($institution['email']) ?>"><?= e($institution['email']) ?></a></li><?php endif; ?>
                        <?php if (!empty($institution['dominio'])): ?><li class="mb-2"><i class="fas fa-globe me-2"></i><?= e($institution['dominio']) ?></li><?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="sp-footer-colorband"></div>
    </footer>

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
                anchor.addEventListener('click', function (e) {
                    var selector = this.getAttribute('href');
                    if (!selector || selector === '#') {
                        return;
                    }
                    var target = document.querySelector(selector);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        });
    </script>
</body>
</html>
