<?php
require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/pagina_menu_helpers.php';
require_once __DIR__ . '/includes/public_theme.php';

if (!function_exists('e')) {
    function e(?string $value): string { return cms_e($value); }
}
if (!function_exists('cfg')) {
    function cfg(array $map, string $sectionName, string $key, string $default = ''): string
    {
        return cms_cfg($map, $sectionName, $key, $default);
    }
}

$institution = null;
$sectionConfigsMap = $sectionItemsMap = $arrMenus = $arrSubs = [];
$menuPage = null;
$menuPageUnavailable = false;
try {
    $db = cms_get_connection();
    $site = cms_get_site_data($db, true);
    $institution = $site['institution'];
    $sectionConfigsMap = $site['configs'];
    $sectionItemsMap = $site['items'];
    $arrMenus = $site['menus'];
    $arrSubs = $site['subs'];
    $resolved = cms_menu_page_resolve($arrMenus, $arrSubs, $_GET['route_slug'] ?? $_GET['slug'] ?? null, $_GET['route_id'] ?? $_GET['id'] ?? null);
    if ($resolved !== null) {
        $menu = $resolved['menu'];
        $menuPageUrl = $resolved['url'];
        $menuPage = cms_menu_page_view($menu, cms_menu_page_load_pages($db, $menu));
        if (!$menuPage['sections']) { $menuPage = null; }
        else { $menuPage['navigation'] = cms_menu_page_navigation($menuPage, $arrSubs[(int) $menu['id_menu']] ?? [], $menuPageUrl); }
    }
} catch (Throwable $exception) {
    $menuPageUnavailable = true;
    error_log('pagina_menu.php: no fue posible cargar la página temática.');
}
if ($menuPage === null) {
    http_response_code($menuPageUnavailable ? 503 : 404);
}
$menuPageTitle = $menuPage['title'] ?? 'Página no encontrada';
$menuPageHasHistory = $menuPage && array_filter($menuPage['sections'], static fn(array $entry): bool => !empty($entry['history']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($menuPageTitle) ?> | <?= e($institution['nombre'] ?? 'Colegio San Pablo') ?></title>
    <link rel="shortcut icon" href="<?= e($institution['favicon'] ?? '/assets/images/icono_ppt.png') ?>">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/pages/colegiosanpablo.css">
    <link rel="stylesheet" href="/assets/css/pages/pagina-menu.css">
    <?php if ($menuPageHasHistory): ?><link rel="stylesheet" href="/assets/css/submenu_historia_publica.css"><?php endif; ?>
</head>
<body class="sp-menu-page">
    <?php include __DIR__ . '/componentes/header.php'; ?>
    <main id="pagina-menu-contenido" data-menu-page-url="<?= e($menuPageUrl ?? '') ?>" data-menu-theme="<?= e(cms_public_institution_theme($institution ?? [])) ?>">
        <?php if ($menuPage !== null): ?>
            <section class="sp-menu-hero<?= $menuPage['hero']['type'] === 'embed' ? ' sp-menu-hero--embed' : '' ?>" aria-labelledby="pagina-menu-titulo">
                <?php $headerMedia = $menuPage['hero']; ?>
                <?php $headerImageSrc = $headerMedia['type'] === 'imagen' ? $headerMedia['src'] : $headerMedia['fallback']; ?>
                <?php if ($headerImageSrc !== ''): ?>
                    <img class="sp-menu-hero__image" src="<?= e($headerImageSrc) ?>" data-menu-hero-image data-fallback="<?= e($headerMedia['fallback']) ?>" alt="" fetchpriority="high">
                <?php endif; ?>
                <?php if ($headerMedia['type'] === 'video'): ?>
                    <video class="sp-menu-hero__video" src="<?= e($headerMedia['src']) ?>" poster="<?= e($headerMedia['fallback']) ?>" autoplay muted loop playsinline preload="metadata" aria-label="Video de cabecera de <?= e($menuPageTitle) ?>" data-menu-hero-video></video>
                    <button type="button" class="sp-menu-hero__video-toggle" data-menu-video-toggle aria-label="Pausar video de cabecera">Pausar video</button>
                <?php elseif ($headerMedia['type'] === 'embed'): ?>
                    <iframe class="sp-menu-hero__embed" src="<?= e($headerMedia['src']) ?>" title="Video de cabecera de <?= e($menuPageTitle) ?>" allow="encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
                <?php endif; ?>
                <div class="container sp-menu-hero__content">
                    <nav class="sp-menu-breadcrumb" aria-label="Ruta de navegación">
                        <a href="/">Inicio</a><span aria-hidden="true">›</span><span aria-current="page"><?= e($menuPageTitle) ?></span>
                    </nav>
                    <span class="sp-menu-eyebrow"><?= e($menuPageTitle) ?></span>
                    <h1 id="pagina-menu-titulo"><?= e($menuPageTitle) ?></h1>
                    <?php if ($menuPage['subtitle'] !== ''): ?><p><?= e($menuPage['subtitle']) ?></p><?php endif; ?>
                </div>
            </section>
            <div class="container sp-menu-layout">
                <?php if ($menuPage['sections']): ?>
                    <aside class="sp-menu-index" aria-label="Secciones de <?= e($menuPageTitle) ?>">
                        <div class="sp-menu-index__desktop">
                            <h2><?= e($menuPageTitle) ?></h2>
                            <nav>
                                <?php foreach ($menuPage['navigation'] as $navigationEntry): ?>
                                    <a href="<?= e($navigationEntry['url']) ?>"<?= $navigationEntry['internal'] ? ' data-menu-link' : '' ?>><?= e($navigationEntry['label']) ?><span aria-hidden="true">↗</span></a>
                                <?php endforeach; ?>
                            </nav>
                        </div>
                        <details class="sp-menu-index__mobile">
                            <summary>Secciones <span aria-hidden="true">⌄</span></summary>
                            <nav>
                                <?php foreach ($menuPage['navigation'] as $navigationEntry): ?>
                                    <a href="<?= e($navigationEntry['url']) ?>"<?= $navigationEntry['internal'] ? ' data-menu-link' : '' ?>><?= e($navigationEntry['label']) ?></a>
                                <?php endforeach; ?>
                            </nav>
                        </details>
                    </aside>
                <?php endif; ?>
                <div class="sp-menu-sections">
                    <?php foreach ($menuPage['sections'] as $entry): ?>
                        <?php $entryHasMedia = $entry['images'] || $entry['videos'] || $entry['gallery']; ?>
                        <section class="sp-menu-section<?= $entryHasMedia ? ' sp-menu-section--media' : '' ?>" id="<?= e($entry['anchor']) ?>" data-menu-section data-submenu-id="<?= (int) $entry['id'] ?>" aria-labelledby="<?= e($entry['anchor']) ?>-titulo">
                            <div class="sp-menu-section__text">
                                <span class="sp-menu-section__label"><?= e($entry['label']) ?></span>
                                <h2 id="<?= e($entry['anchor']) ?>-titulo"><?= e($entry['title']) ?></h2>
                                <?php if ($entry['subtitle'] !== ''): ?><p class="sp-menu-section__intro"><?= e($entry['subtitle']) ?></p><?php endif; ?>
                                <div class="sp-menu-prose"><?= $entry['content'] ?></div>
                                <?php if ($entry['button_text'] !== '' && $entry['button_url'] !== '' && !preg_match('~^(?:javascript|data):~i', $entry['button_url'])): ?>
                                    <a class="sp-menu-button" href="<?= e($entry['button_url']) ?>"><?= e($entry['button_text']) ?><span aria-hidden="true">↗</span></a>
                                <?php endif; ?>
                            </div>
                            <?php if ($entryHasMedia): ?>
                                <div class="sp-menu-section__media">
                                    <?php foreach ($entry['images'] as $image): ?>
                                        <?php $imageSize = cms_menu_page_image_size($image['src']); ?>
                                        <figure>
                                            <a href="<?= e($image['src']) ?>" target="_blank" rel="noopener"><img src="<?= e($image['src']) ?>" alt="<?= e($image['title']) ?>"<?= $imageSize ? ' width="' . $imageSize['width'] . '" height="' . $imageSize['height'] . '"' : '' ?> loading="lazy"></a>
                                            <?php if ($image['description'] !== ''): ?><figcaption><?= e($image['description']) ?></figcaption><?php endif; ?>
                                        </figure>
                                    <?php endforeach; ?>
                                    <?php foreach ($entry['videos'] as $video): ?>
                                        <figure>
                                            <?php if ($video['type'] === 'video'): ?>
                                                <video controls preload="metadata" src="<?= e($video['src']) ?>" aria-label="<?= e($video['title']) ?>"></video>
                                            <?php else: ?>
                                                <iframe src="<?= e($video['src']) ?>" title="<?= e($video['title']) ?>" loading="lazy" allow="encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
                                            <?php endif; ?>
                                            <?php if ($video['description'] !== ''): ?><figcaption><?= e($video['description']) ?></figcaption><?php endif; ?>
                                        </figure>
                                    <?php endforeach; ?>
                                    <?php
                                    $menuGalleryImages = $entry['gallery'];
                                    $menuGalleryId = $entry['anchor'] . '-galeria';
                                    $menuGalleryLabel = 'Galería de ' . $entry['label'];
                                    include __DIR__ . '/componentes/pagina_menu_galeria.php';
                                    ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($entry['history']): ?>
                                <div class="sp-menu-section__history">
                                    <?php $historyItems = $entry['history']; $historyTitleId = $entry['anchor'] . '-historia'; $historyTitle = $entry['title']; include __DIR__ . '/componentes/submenu_historia_visual.php'; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                    <?php if ($menuPage['gallery']): ?>
                        <section class="sp-menu-gallery" aria-labelledby="pagina-menu-galeria-titulo">
                            <h2 id="pagina-menu-galeria-titulo">Galería de <?= e($menuPageTitle) ?></h2>
                            <?php
                            $menuGalleryImages = $menuPage['gallery'];
                            $menuGalleryId = 'pagina-menu-galeria-general';
                            $menuGalleryLabel = 'Galería de ' . $menuPageTitle;
                            include __DIR__ . '/componentes/pagina_menu_galeria.php';
                            ?>
                        </section>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="container sp-menu-empty">
                <h1><?= $menuPageUnavailable ? 'Página no disponible temporalmente' : 'Página no encontrada' ?></h1>
                <a href="/">Volver al inicio</a>
            </div>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/componentes/footer_principal.php'; ?>
    <script src="/assets/js/bootstrap.min.js"></script>
    <script src="/assets/js/pagina-menu.js" defer></script>
    <?php if ($menuPageHasHistory): ?><script src="/assets/js/submenu_historia_publica.js" defer></script><?php endif; ?>
</body>
</html>
