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

        if (in_array($sectionName, ['menu_principal', 'modal_informativo', 'modal_bienvenida'], true)) {
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
        window.addEventListener('load', function () {
            fetch('modal_informativo_payload.php', {
                credentials: 'same-origin',
                cache: 'no-store'
            })
                .then(function (response) {
                    if (response.status === 204) {
                        return null;
                    }
                    if (!response.ok) {
                        throw new Error('Modal informativo no disponible');
                    }
                    return response.text();
                })
                .then(function (html) {
                    if (!html) {
                        return;
                    }

                    var host = document.createElement('div');
                    host.innerHTML = html;
                    document.body.appendChild(host);

                    var el = host.querySelector('#sp-mi');
                    if (!el) {
                        host.remove();
                        return;
                    }

                    var delay = parseInt(el.getAttribute('data-delay') || '0', 10);
                    var mode = el.getAttribute('data-mode') || 'una_vez';
                    var dismissKey = el.getAttribute('data-dismiss-key') || '';
                    var xBtn = host.querySelector('#sp-mi-x');
                    var dBtn = host.querySelector('#sp-mi-dismiss');
                    var bg = host.querySelector('.sp-mi-bg');

                    function setCookie(name, value, seconds) {
                        if (!name) {
                            return;
                        }
                        var cookie = encodeURIComponent(name) + '=' + encodeURIComponent(value) + '; path=/; SameSite=Lax';
                        if (seconds) {
                            cookie += '; Max-Age=' + seconds;
                        }
                        document.cookie = cookie;
                    }

                    function openModal() {
                        el.hidden = false;
                        el.offsetHeight;
                        el.classList.add('sp-mi-open');
                        el.setAttribute('aria-hidden', 'false');
                        document.body.style.overflow = 'hidden';
                    }

                    function closeModal(remove) {
                        el.classList.remove('sp-mi-open');
                        el.setAttribute('aria-hidden', 'true');
                        document.body.style.overflow = '';
                        setTimeout(function () {
                            if (remove) {
                                host.remove();
                            } else {
                                el.hidden = true;
                            }
                        }, 180);
                    }

                    function dismissModal() {
                        setCookie(dismissKey, String(Date.now()), 7 * 86400);
                        closeModal(true);
                    }

                    if (xBtn) {
                        xBtn.addEventListener('click', function () { closeModal(true); });
                    }
                    if (dBtn) {
                        dBtn.addEventListener('click', dismissModal);
                    }
                    if (bg) {
                        bg.addEventListener('click', function () { closeModal(true); });
                    }
                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && el.classList.contains('sp-mi-open')) {
                            closeModal(true);
                        }
                    });

                    setTimeout(openModal, Math.max(0, delay));
                })
                .catch(function () {
                    // El modal es auxiliar; si falla, no debe afectar la pagina.
                });
        });
    </script>
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
