<?php
require_once __DIR__ . '/includes/cms_helpers.php';

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return cms_e($value);
    }
}

if (!function_exists('cfg')) {
    function cfg(array $configMap, string $sectionName, string $key, string $default = ''): string
    {
        return cms_cfg($configMap, $sectionName, $key, $default);
    }
}

if (!function_exists('sp_public_hex_color')) {
    function sp_public_hex_color(?string $value, string $fallback): string
    {
        $value = trim((string) $value);
        return preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value) ? $value : $fallback;
    }
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
        $sectionEstado = strtolower(trim((string) ($section['estado'] ?? 'activo')));
        if (($section['visible'] ?? 'no') === 'si' && $sectionEstado === 'activo') {
            $sections[] = $section;
        }
    }
} catch (Throwable $exception) {
    error_log('index.php: ' . $exception->getMessage());
}

$colorPrimario = sp_public_hex_color($institution['color_primario'] ?? null, '#F0A000');
$colorSecundario = sp_public_hex_color($institution['color_secundario'] ?? null, '#EF6C00');
$colorTerciario = sp_public_hex_color($institution['color_terciario'] ?? null, '#1976D2');
$colorCuaternario = sp_public_hex_color($institution['color_cuaternario'] ?? null, '#E53935');
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
    <style>
        :root {
            --sp-db-primary: <?= e($colorPrimario) ?>;
            --sp-db-secondary: <?= e($colorSecundario) ?>;
            --sp-db-tertiary: <?= e($colorTerciario) ?>;
            --sp-db-quaternary: <?= e($colorCuaternario) ?>;
        }
        .sp-colorband,
        .sp-footer-colorband,
        .footer-color-bar {
            background: linear-gradient(to right,
                var(--sp-db-primary) 0%,
                var(--sp-db-primary) 25%,
                var(--sp-db-secondary) 25%,
                var(--sp-db-secondary) 50%,
                var(--sp-db-tertiary) 50%,
                var(--sp-db-tertiary) 75%,
                var(--sp-db-quaternary) 75%,
                var(--sp-db-quaternary) 100%
            );
        }
        .sp-btn-matricula {
            background: var(--sp-db-primary) !important;
        }
        .sp-btn-matricula:hover {
            background: var(--sp-db-primary) !important;
            filter: brightness(.94);
        }
        .sp-floating-comunicados {
            position: fixed;
            right: max(18px, env(safe-area-inset-right));
            bottom: max(22px, env(safe-area-inset-bottom));
            z-index: 1045;
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            width: 58px;
            min-width: 58px;
            height: 58px;
            padding: 0 17px;
            border-radius: 999px;
            color: #fff;
            background: var(--sp-db-primary);
            box-shadow: 0 14px 34px color-mix(in srgb, var(--sp-db-primary) 32%, transparent);
            font-family: "Poppins", sans-serif;
            font-weight: 800;
            line-height: 1;
            text-decoration: none;
            transition: width .22s ease, transform .2s ease, box-shadow .2s ease, padding .2s ease;
        }
        .sp-floating-cantina {
            bottom: calc(max(22px, env(safe-area-inset-bottom)) + 72px);
        }
        .sp-floating-comunicados:hover,
        .sp-floating-comunicados:focus {
            color: #fff;
            width: 228px;
            transform: translateY(-2px);
            box-shadow: 0 18px 42px color-mix(in srgb, var(--sp-db-primary) 38%, transparent);
            text-decoration: none;
        }
        .sp-floating-comunicados__icon {
            width: 24px;
            height: 24px;
            display: inline-grid;
            place-items: center;
            flex: 0 0 24px;
            font-size: 1rem;
        }
        .sp-floating-comunicados__label {
            white-space: nowrap;
            overflow: hidden;
            max-width: 0;
            opacity: 0;
            transition: max-width .22s ease, opacity .18s ease, margin .18s ease;
        }
        .sp-floating-comunicados:hover .sp-floating-comunicados__label,
        .sp-floating-comunicados:focus .sp-floating-comunicados__label,
        .sp-floating-comunicados:focus-visible .sp-floating-comunicados__label {
            max-width: 150px;
            opacity: 1;
            margin-left: 2px;
        }
        @media (max-width: 767px) {
            .sp-floating-comunicados {
                right: max(14px, env(safe-area-inset-right));
                bottom: max(16px, env(safe-area-inset-bottom));
                width: 54px;
                min-width: 54px;
                height: 54px;
                padding: 0 15px;
                justify-content: center;
            }
            .sp-floating-cantina {
                bottom: calc(max(16px, env(safe-area-inset-bottom)) + 66px);
            }
            .sp-floating-comunicados__label {
                max-width: 0;
                opacity: 0;
                margin-left: 0;
            }
            .sp-floating-comunicados:hover,
            .sp-floating-comunicados:focus,
            .sp-floating-comunicados:focus-visible {
                width: 196px;
                padding-left: 18px;
                padding-right: 18px;
                justify-content: flex-end;
            }
            .sp-floating-comunicados:hover .sp-floating-comunicados__label,
            .sp-floating-comunicados:focus .sp-floating-comunicados__label,
            .sp-floating-comunicados:focus-visible .sp-floating-comunicados__label {
                max-width: 140px;
                opacity: 1;
                margin-left: 2px;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .sp-floating-comunicados,
            .sp-floating-comunicados__label {
                transition: none;
            }
        }
    </style>
</head>
<body>

    <div class="sp-colorband"></div>

    <?php foreach ($sections as $section): ?>
        <?php
        $sectionName = $section['nombre_interno'] ?? '';

        $component = cms_get_component_path($sectionName);

        if ($component) {
            include $component;
        }
        ?>
    <?php endforeach; ?>

    <a class="sp-floating-comunicados sp-floating-cantina" href="https://mi.sanpablo.edu.uy/cantina" target="_blank" rel="noopener" aria-label="Ver menu de cantina">
        <span class="sp-floating-comunicados__icon" aria-hidden="true"><i class="fas fa-utensils"></i></span>
        <span class="sp-floating-comunicados__label">Menú</span>
    </a>

    <a class="sp-floating-comunicados" href="comunicados.php" aria-label="Ver comunicados">
        <span class="sp-floating-comunicados__icon" aria-hidden="true"><i class="fas fa-bullhorn"></i></span>
        <span class="sp-floating-comunicados__label">Comunicados</span>
    </a>

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
