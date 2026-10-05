<?php
require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/biblioteca_helpers.php';
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
$library = null;
$libraryUnavailable = false;
try {
    $db = cms_get_connection();
    $site = cms_get_site_data($db, true);
    $institution = $site['institution'];
    $sectionConfigsMap = $site['configs'];
    $sectionItemsMap = $site['items'];
    $arrMenus = $site['menus'];
    $arrSubs = $site['subs'];
    // Normalize this view's copies only; stored URLs and other pages stay unchanged.
    foreach ($arrMenus as &$menuLink) {
        $menuLink['url'] = cms_biblioteca_current_url($menuLink['url'] ?? '');
    }
    unset($menuLink);
    foreach ($arrSubs as &$submenuLinks) {
        foreach ($submenuLinks as &$submenuLink) {
            $submenuLink['url'] = cms_biblioteca_current_url($submenuLink['url'] ?? '');
        }
        unset($submenuLink);
    }
    unset($submenuLinks);
    foreach ($arrMenus as $menu) {
        if (cms_is_biblioteca_menu($menu)) {
            $library = cms_biblioteca_view($menu, cms_biblioteca_load_pages($db, $menu, $arrSubs[(int) $menu['id_menu']] ?? []));
            break;
        }
    }
} catch (Throwable $exception) {
    $libraryUnavailable = true;
    error_log('biblioteca.php: no fue posible cargar la biblioteca.');
}
if ($library === null) {
    http_response_code($libraryUnavailable ? 503 : 404);
}
$libraryTitle = $library['title'] ?? 'Biblioteca';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($libraryTitle) ?> | <?= e($institution['nombre'] ?? 'Colegio San Pablo') ?></title>
    <link rel="shortcut icon" href="<?= e($institution['favicon'] ?? '/assets/images/icono_ppt.png') ?>">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/pages/colegiosanpablo.css">
    <link rel="stylesheet" href="/assets/css/pages/biblioteca.css">
</head>
<body class="sp-library-page">
    <?php include __DIR__ . '/componentes/header.php'; ?>
    <main id="biblioteca-contenido" style="<?= e(cms_public_institution_theme($institution ?? [])) ?>">
        <?php if ($library !== null): ?>
            <section class="sp-library-hero" aria-labelledby="biblioteca-titulo">
                <img class="sp-library-hero__image" src="<?= e($library['hero']) ?>" alt="" fetchpriority="high">
                <div class="container sp-library-hero__content">
                    <nav class="sp-library-breadcrumb" aria-label="Ruta de navegación">
                        <a href="/">Inicio</a><span aria-hidden="true">›</span><span aria-current="page"><?= e($libraryTitle) ?></span>
                    </nav>
                    <span class="sp-library-eyebrow"><?= e($libraryTitle) ?></span>
                    <h1 id="biblioteca-titulo"><?= e($libraryTitle) ?></h1>
                    <?php if ($library['subtitle'] !== ''): ?><p><?= e($library['subtitle']) ?></p><?php endif; ?>
                </div>
            </section>
            <div class="container sp-library-layout">
                <?php if ($library['sections']): ?>
                    <aside class="sp-library-index" aria-label="Secciones de Biblioteca">
                        <div class="sp-library-index__desktop">
                            <h2><?= e($libraryTitle) ?></h2>
                            <nav>
                                <?php foreach ($library['sections'] as $entry): ?>
                                    <a href="/biblioteca#<?= e($entry['anchor']) ?>" data-library-link><?= e($entry['label']) ?><span aria-hidden="true">↗</span></a>
                                <?php endforeach; ?>
                            </nav>
                        </div>
                        <details class="sp-library-index__mobile">
                            <summary>Secciones <span aria-hidden="true">⌄</span></summary>
                            <nav>
                                <?php foreach ($library['sections'] as $entry): ?>
                                    <a href="/biblioteca#<?= e($entry['anchor']) ?>" data-library-link><?= e($entry['label']) ?></a>
                                <?php endforeach; ?>
                            </nav>
                        </details>
                    </aside>
                <?php endif; ?>
                <div class="sp-library-sections">
                    <?php foreach ($library['sections'] as $entry): ?>
                        <?php $entryHasMedia = $entry['images'] || $entry['videos']; ?>
                        <section class="sp-library-section<?= $entryHasMedia ? ' sp-library-section--media' : '' ?>" id="<?= e($entry['anchor']) ?>" data-library-section aria-labelledby="<?= e($entry['anchor']) ?>-titulo">
                            <div class="sp-library-section__text">
                                <span class="sp-library-section__label"><?= e($entry['label']) ?></span>
                                <h2 id="<?= e($entry['anchor']) ?>-titulo"><?= e($entry['title']) ?></h2>
                                <?php if ($entry['subtitle'] !== ''): ?><p class="sp-library-section__intro"><?= e($entry['subtitle']) ?></p><?php endif; ?>
                                <div class="sp-library-prose"><?= $entry['content'] ?></div>
                                <?php if ($entry['button_text'] !== '' && $entry['button_url'] !== '' && !preg_match('~^(?:javascript|data):~i', $entry['button_url'])): ?>
                                    <a class="sp-library-button" href="<?= e($entry['button_url']) ?>"><?= e($entry['button_text']) ?><span aria-hidden="true">↗</span></a>
                                <?php endif; ?>
                            </div>
                            <?php if ($entryHasMedia): ?>
                                <div class="sp-library-section__media">
                                    <?php foreach ($entry['images'] as $image): ?>
                                        <figure>
                                            <a href="<?= e($image['src']) ?>" target="_blank" rel="noopener"><img src="<?= e($image['src']) ?>" alt="<?= e($image['title']) ?>" loading="lazy"></a>
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
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                    <?php if ($library['gallery']): ?>
                        <section class="sp-library-gallery" aria-labelledby="biblioteca-galeria-titulo">
                            <h2 id="biblioteca-galeria-titulo">Galería de la Biblioteca</h2>
                            <div class="sp-library-gallery__grid">
                                <?php foreach ($library['gallery'] as $image): ?>
                                    <figure><a href="<?= e($image['src']) ?>" target="_blank" rel="noopener"><img src="<?= e($image['src']) ?>" alt="<?= e($image['title']) ?>" loading="lazy"></a><figcaption><?= e($image['title']) ?><?php if ($image['description'] !== ''): ?><span><?= e($image['description']) ?></span><?php endif; ?></figcaption></figure>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="container sp-library-empty">
                <h1><?= $libraryUnavailable ? 'Biblioteca no disponible temporalmente' : 'Página no encontrada' ?></h1>
                <a href="/">Volver al inicio</a>
            </div>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/componentes/footer_principal.php'; ?>
    <script src="/assets/js/bootstrap.min.js"></script>
    <script src="/assets/js/biblioteca.js" defer></script>
</body>
</html>
