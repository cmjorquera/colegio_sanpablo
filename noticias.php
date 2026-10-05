<?php
require_once __DIR__ . '/includes/cms_helpers.php';

if (!function_exists('e')) {
    function e(?string $value): string { return cms_e($value); }
}

$institution       = null;
$sectionConfigsMap = [];
$sectionItemsMap   = [];
$categoriesById    = [];
$arrMenus          = [];
$arrSubs           = [];
$db                = null;
$site              = [];
$newsByYear        = [];
$years             = [];

try {
    $db   = cms_get_connection();
    $site = cms_get_site_data($db);

    $institution       = $site['institution'];
    $sectionConfigsMap = $site['configs'];
    $sectionItemsMap   = $site['items'];
    $categoriesById    = $site['categories'];
    $arrMenus          = $site['menus'];
    $arrSubs           = $site['subs'];

    $result = $db->query("
        SELECT si.*
        FROM seccion_item si
        INNER JOIN seccion s ON s.id_seccion = si.id_seccion
        WHERE s.nombre_interno = 'noticias_home'
          AND si.visible = 'si'
        ORDER BY COALESCE(si.fecha_publicacion, si.fecha_creacion) DESC, si.orden ASC
    ");
    $allNews = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    foreach ($allNews as $newsRow) {
        $dateSource = $newsRow['fecha_publicacion'] ?: $newsRow['fecha_creacion'];
        $year = $dateSource ? (int) date('Y', strtotime((string) $dateSource)) : (int) date('Y');
        $newsByYear[$year][] = $newsRow;
    }
    krsort($newsByYear);
    $years = array_slice(array_keys($newsByYear), 0, 4);
} catch (Throwable $ex) {
    error_log('noticias.php: ' . $ex->getMessage());
}

$colorPrimario   = $institution['color_primario']   ?? '#2060B0';
$colorSecundario = $institution['color_secundario'] ?? '#E8A030';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticias · <?= e($institution['nombre'] ?? 'Colegio San Pablo') ?></title>
    <link rel="shortcut icon" href="<?= e($institution['favicon'] ?? 'assets/images/icono_ppt.png') ?>">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/colegiosanpablo.css">
    <style>
        .nt-hero {
            background: linear-gradient(135deg, <?= e($colorPrimario) ?> 0%, <?= e($colorSecundario) ?> 100%);
            padding: 72px 0 56px;
            color: #fff;
        }
        .nt-hero h1 { font-size: 38px; font-weight: 800; margin: 0 0 10px; }
        .nt-hero p { font-size: 16px; opacity: .85; margin: 0; max-width: 640px; }
        .nt-breadcrumb { font-size: 13px; opacity: .75; margin-bottom: 14px; }
        .nt-breadcrumb a { color: #fff; text-decoration: none; }
        .nt-breadcrumb a:hover { text-decoration: underline; }
        .nt-body { padding: 48px 0 88px; background: #f8f9fc; min-height: 40vh; }
        .nt-toolbar { display: flex; flex-wrap: wrap; gap: 18px; align-items: center; justify-content: space-between; margin-bottom: 30px; }
        .nt-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
        .nt-tab {
            border: 2px solid #e0e6f0; background: #fff; color: #4d5565;
            font-weight: 700; font-size: 14px; padding: 9px 20px; border-radius: 999px;
            cursor: pointer; transition: .2s ease;
        }
        .nt-tab:hover { border-color: <?= e($colorPrimario) ?>; color: <?= e($colorPrimario) ?>; }
        .nt-tab.is-active { background: <?= e($colorPrimario) ?>; border-color: <?= e($colorPrimario) ?>; color: #fff; }
        .nt-search { position: relative; min-width: 260px; flex: 0 0 auto; }
        .nt-search input {
            width: 100%; border: 2px solid #e0e6f0; border-radius: 999px; padding: 9px 18px 9px 40px;
            font-size: 14px; outline: none; transition: border-color .2s ease;
        }
        .nt-search input:focus { border-color: <?= e($colorPrimario) ?>; }
        .nt-search i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #98a3b5; }
        .nt-panel { display: none; }
        .nt-panel.is-active { display: block; }
        .nt-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 26px;
        }
        .nt-card {
            display: flex; flex-direction: column; background: #fff; border-radius: 18px;
            overflow: hidden; text-decoration: none; color: inherit;
            box-shadow: 0 14px 34px rgba(20, 38, 70, .08); transition: transform .2s ease, box-shadow .2s ease;
            height: 100%;
        }
        .nt-card:hover { transform: translateY(-4px); box-shadow: 0 20px 46px rgba(20, 38, 70, .14); color: inherit; }
        .nt-card-img { height: 180px; overflow: hidden; background: #eef2f7; }
        .nt-card-img img { width: 100%; height: 100%; object-fit: cover; }
        .nt-card-body { padding: 20px 22px 24px; display: flex; flex-direction: column; flex: 1; }
        .nt-card-tag {
            align-self: flex-start; background: #e8f2ff; color: <?= e($colorPrimario) ?>;
            font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em;
            padding: 5px 12px; border-radius: 999px; margin-bottom: 12px;
        }
        .nt-card-date { font-size: 13px; color: #8b98a5; margin-bottom: 8px; }
        .nt-card-date i { margin-right: 6px; }
        .nt-card-title {
            font-size: 18px; font-weight: 700; color: #17233b; margin-bottom: 10px;
            display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden;
        }
        .nt-card-desc {
            font-size: 14px; color: #6d788b; line-height: 1.55; margin-bottom: 16px;
            display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow: hidden;
        }
        .nt-card-cta {
            margin-top: auto; font-size: 14px; font-weight: 700; color: <?= e($colorPrimario) ?>;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .nt-card-cta i { transition: transform .2s ease; }
        .nt-card:hover .nt-card-cta i { transform: translateX(3px); }
        .nt-empty { text-align: center; padding: 70px 20px; color: #8b98a5; }
        .nt-empty i { font-size: 48px; margin-bottom: 16px; display: block; opacity: .4; }
        .nt-empty h3 { font-size: 22px; font-weight: 700; color: #3a4a5c; margin-bottom: 8px; }
        .nt-card.is-search-hidden { display: none; }
        @media (max-width: 575px) {
            .nt-toolbar { flex-direction: column; align-items: stretch; }
            .nt-search { min-width: 0; }
        }
    </style>
</head>
<body>

    <?php
    $headerComponent = cms_get_component_path('header_principal');
    if ($headerComponent) {
        $section = null;
        foreach (($site['sections'] ?? []) as $s) {
            if (($s['nombre_interno'] ?? '') === 'header_principal') { $section = $s; break; }
        }
        if ($section) { include $headerComponent; }
    }
    ?>

    <div class="nt-hero">
        <div class="container">
            <div class="nt-breadcrumb">
                <a href="/">Inicio</a> / Noticias
            </div>
            <h1>Noticias del Colegio</h1>
            <p>Revisa las actividades, logros y novedades de nuestra comunidad educativa.</p>
        </div>
    </div>

    <div class="nt-body">
        <div class="container">
            <?php if (empty($years)): ?>
                <div class="nt-empty">
                    <i class="fa-regular fa-newspaper"></i>
                    <h3>Todavía no hay noticias publicadas</h3>
                    <p>Vuelve a visitarnos pronto para conocer las novedades de nuestra comunidad.</p>
                </div>
            <?php else: ?>
                <div class="nt-toolbar">
                    <div class="nt-tabs" role="tablist" aria-label="Años de noticias">
                        <?php foreach ($years as $index => $year): ?>
                            <button type="button" class="nt-tab<?= $index === 0 ? ' is-active' : '' ?>" data-nt-tab="<?= (int) $year ?>"><?= (int) $year ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="nt-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="ntSearchInput" placeholder="Buscar por título o texto...">
                    </div>
                </div>

                <?php foreach ($years as $index => $year): ?>
                    <?php $yearNews = $newsByYear[$year] ?? []; ?>
                    <div class="nt-panel<?= $index === 0 ? ' is-active' : '' ?>" data-nt-panel="<?= (int) $year ?>">
                        <div class="nt-grid">
                            <?php foreach ($yearNews as $newsItem): ?>
                                <?php
                                $categoria = $newsItem['etiqueta'] ?? '';
                                if (!empty($newsItem['id_categoria']) && isset($categoriesById[(int) $newsItem['id_categoria']])) {
                                    $categoria = $categoriesById[(int) $newsItem['id_categoria']]['nombre'];
                                }
                                $searchText = mb_strtolower(($newsItem['titulo'] ?? '') . ' ' . ($newsItem['descripcion'] ?? ''), 'UTF-8');
                                ?>
                                <a class="nt-card" href="/noticia/<?= (int) ($newsItem['id_item'] ?? 0) ?>" data-nt-search="<?= e($searchText) ?>">
                                    <div class="nt-card-img">
                                        <img src="<?= e(cms_public_image($newsItem['imagen'] ?? '')) ?>" alt="<?= e($newsItem['titulo'] ?? '') ?>" loading="lazy">
                                    </div>
                                    <div class="nt-card-body">
                                        <?php if ($categoria !== ''): ?>
                                            <span class="nt-card-tag"><?= e($categoria) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($newsItem['fecha_publicacion'])): ?>
                                            <div class="nt-card-date"><i class="fas fa-calendar-alt"></i><?= e($newsItem['fecha_publicacion']) ?></div>
                                        <?php endif; ?>
                                        <h3 class="nt-card-title"><?= e($newsItem['titulo'] ?? '') ?></h3>
                                        <?php if (!empty($newsItem['descripcion'])): ?>
                                            <p class="nt-card-desc"><?= e(strip_tags((string) $newsItem['descripcion'])) ?></p>
                                        <?php endif; ?>
                                        <span class="nt-card-cta">Leer noticia <i class="fas fa-arrow-right"></i></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="nt-empty" id="ntSearchEmpty" style="display:none;">
                    <i class="fas fa-search"></i>
                    <h3>No encontramos noticias</h3>
                    <p>Prueba con otra palabra clave.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
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
    <script src="assets/js/meanmenu.js"></script>
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/magnific-popup.min.js"></script>
    <script src="assets/js/nice-select.min.js"></script>
    <script src="assets/js/parallax.js"></script>
    <script src="assets/js/jquery.waypoints.js"></script>
    <script src="assets/js/script.js"></script>
    <?php if (!empty($years)): ?>
    <script>
    (function () {
        var tabs = document.querySelectorAll('[data-nt-tab]');
        var panels = document.querySelectorAll('[data-nt-panel]');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var year = tab.getAttribute('data-nt-tab');
                tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
                panels.forEach(function (p) {
                    p.classList.toggle('is-active', p.getAttribute('data-nt-panel') === year);
                });
            });
        });

        var searchInput = document.getElementById('ntSearchInput');
        var searchEmpty = document.getElementById('ntSearchEmpty');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var term = searchInput.value.trim().toLowerCase();
                var activePanel = document.querySelector('.nt-panel.is-active');
                var cards = activePanel ? activePanel.querySelectorAll('.nt-card') : [];
                var visibleCount = 0;
                cards.forEach(function (card) {
                    var haystack = card.getAttribute('data-nt-search') || '';
                    var matches = term === '' || haystack.indexOf(term) !== -1;
                    card.classList.toggle('is-search-hidden', !matches);
                    if (matches) { visibleCount++; }
                });
                if (searchEmpty) {
                    searchEmpty.style.display = (term !== '' && visibleCount === 0) ? '' : 'none';
                }
            });
        }
    })();
    </script>
    <?php endif; ?>
</body>
</html>
