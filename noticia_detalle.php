<?php
require_once __DIR__ . '/includes/cms_helpers.php';

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return cms_e($value);
    }
}

$institution = null;
$sectionConfigsMap = [];
$sectionItemsMap = [];
$categoriesById = [];
$arrMenus = [];
$arrSubs = [];
$db = null;
$newsItem = null;
$newsCategory = '';
$relatedNews = [];
$newsId = max(0, (int) ($_GET['id'] ?? 0));

try {
    $db = cms_get_connection();
    $site = cms_get_site_data($db);

    $institution = $site['institution'];
    $sectionConfigsMap = $site['configs'];
    $sectionItemsMap = $site['items'];
    $categoriesById = $site['categories'];
    $arrMenus = $site['menus'];
    $arrSubs = $site['subs'];

    if ($newsId > 0) {
        $stmt = $db->prepare("
            SELECT si.*
            FROM seccion_item si
            INNER JOIN seccion s ON s.id_seccion = si.id_seccion
            WHERE si.id_item = ?
              AND s.nombre_interno = 'noticias_home'
              AND si.visible = 'si'
            LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param('i', $newsId);
            $stmt->execute();
            $result = $stmt->get_result();
            $newsItem = $result ? $result->fetch_assoc() : null;
            $stmt->close();
        }
    }

    if ($newsItem && !empty($newsItem['id_categoria']) && isset($categoriesById[(int) $newsItem['id_categoria']])) {
        $newsCategory = (string) ($categoriesById[(int) $newsItem['id_categoria']]['nombre'] ?? '');
    } elseif ($newsItem) {
        $newsCategory = (string) ($newsItem['etiqueta'] ?? '');
    }

    if ($newsItem) {
        $relatedSql = "
            SELECT si.*
            FROM seccion_item si
            INNER JOIN seccion s ON s.id_seccion = si.id_seccion
            WHERE s.nombre_interno = 'noticias_home'
              AND si.visible = 'si'
              AND si.id_item <> ?
        ";
        if (!empty($newsItem['id_categoria'])) {
            $relatedSql .= ' AND si.id_categoria = ?';
        }
        $relatedSql .= ' ORDER BY si.fecha_publicacion DESC, si.orden ASC LIMIT 3';
        $stmtRelated = $db->prepare($relatedSql);
        if ($stmtRelated) {
            if (!empty($newsItem['id_categoria'])) {
                $relatedCategoryId = (int) $newsItem['id_categoria'];
                $stmtRelated->bind_param('ii', $newsId, $relatedCategoryId);
            } else {
                $stmtRelated->bind_param('i', $newsId);
            }
            $stmtRelated->execute();
            $relatedResult = $stmtRelated->get_result();
            $relatedNews = $relatedResult ? $relatedResult->fetch_all(MYSQLI_ASSOC) : [];
            $stmtRelated->close();
        }
    }
} catch (Throwable $exception) {
    error_log('noticia_detalle.php: ' . $exception->getMessage());
}

$pageTitle = trim((string) ($newsItem['titulo'] ?? 'Noticia'));
$image = trim((string) ($newsItem['imagen'] ?? ''));
if ($image === '') {
    $image = 'assets/images/frontis_01.jpg';
}
$detailGallery = ($newsItem && $db instanceof mysqli) ? array_values(array_filter(cms_get_news_gallery_config($db, (int) ($newsItem['id_seccion'] ?? 0), (int) ($newsItem['id_item'] ?? 0)), static fn(array $item): bool => (int) ($item['visible'] ?? 1) === 1)) : [];
$youtubeEmbed = $newsItem ? cms_youtube_embed_url($newsItem['video_youtube'] ?? '') : '';
$youtubeThumb = '';
$youtubeThumbFallback = '';
if ($youtubeEmbed !== '' && preg_match('~/embed/([A-Za-z0-9_-]+)~', $youtubeEmbed, $youtubeMatches)) {
    $youtubeThumb = 'https://img.youtube.com/vi/' . $youtubeMatches[1] . '/maxresdefault.jpg';
    $youtubeThumbFallback = 'https://img.youtube.com/vi/' . $youtubeMatches[1] . '/hqdefault.jpg';
}
// La columna url guarda el video local subido cuando la noticia no usa un enlace de YouTube.
$localVideo = ($youtubeEmbed === '' && $newsItem) ? trim((string) ($newsItem['url'] ?? '')) : '';
$description = trim((string) ($newsItem['descripcion'] ?? ''));
$descriptionForSplit = preg_replace('/<\/p>\s*<p/i', "</p>\n\n<p", $description) ?? $description;
$paragraphs = array_values(array_filter(preg_split('/\R{2,}/', $descriptionForSplit) ?: [], static fn(string $paragraph): bool => trim(strip_tags($paragraph)) !== ''));
$introText = trim((string) ($paragraphs[0] ?? ''));
$bodyText = trim(implode("\n\n", array_slice($paragraphs, 1)));
if ($bodyText === '' && trim(strip_tags($description)) !== trim(strip_tags($introText))) {
    $bodyText = $description;
}
$introHtml = cms_basic_content_html($introText);
$bodyHtml = cms_basic_content_html($bodyText);
$publicUrl = 'noticia_detalle.php?id=' . $newsId;
$encodedShareTitle = rawurlencode($pageTitle);
$encodedShareUrl = rawurlencode($publicUrl);

$buttonText = trim((string) ($newsItem['boton_1_texto'] ?? ''));
if ($buttonText === '') {
    $buttonText = 'Leer más';
}
$buttonUrl = trim((string) ($newsItem['boton_1_url'] ?? ''));
$isExternalButton = preg_match('/^https?:\/\//i', $buttonUrl) === 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e($institution['nombre'] ?? 'Colegio San Pablo') ?></title>
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
        .sp-news-detail { padding: 54px 0 88px; background: linear-gradient(180deg, #f7f9fc 0%, #fff 24%, #fff 100%); }
        .sp-news-detail-shell { max-width: 1180px; margin: 0 auto; }
        .sp-news-breadcrumb { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 22px; color: #758198; font-size: 14px; }
        .sp-news-breadcrumb a { color: #2060b0; font-weight: 700; text-decoration: none; }
        .sp-news-breadcrumb span { display: inline-flex; align-items: center; gap: 8px; }
        .sp-news-detail-layout { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 34px; align-items: start; }
        .sp-news-detail-card { background: #fff; border: 1px solid #e8edf5; border-radius: 24px; padding: clamp(24px, 4vw, 46px); box-shadow: 0 22px 60px rgba(20, 38, 70, .08); }
        .sp-news-detail-aside { position: sticky; top: 110px; display: grid; gap: 18px; }
        .sp-news-detail-meta { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; color: #6d788b; font-size: 14px; margin-bottom: 18px; }
        .sp-news-detail-tag { display: inline-flex; align-items: center; padding: 8px 14px; border-radius: 999px; background: #e8f2ff; color: #2060b0; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; font-size: 12px; }
        .sp-news-detail-title { color: #111c32; font-size: clamp(34px, 5vw, 58px); line-height: 1.06; margin-bottom: 26px; letter-spacing: 0; }
        .sp-news-detail-image { width: 100%; max-height: 560px; object-fit: cover; border-radius: 22px; box-shadow: 0 24px 70px rgba(15, 35, 70, .16); margin-bottom: 30px; }
        .sp-news-detail-intro { color: #26344f; font-size: clamp(19px, 2.2vw, 23px); line-height: 1.65; font-weight: 600; margin-bottom: 28px; white-space: pre-line; }
        .sp-news-detail-content { color: #4d5565; font-size: 18px; line-height: 1.86; white-space: pre-line; }
        .sp-news-detail-block { margin-top: 34px; }
        .sp-news-detail-block h2 { color: #111c32; font-size: 26px; margin-bottom: 16px; }
        .sp-news-gallery-carousel { border-radius: 20px; overflow: hidden; box-shadow: 0 20px 55px rgba(15, 35, 70, .13); background: #eef2f7; }
        .sp-news-gallery-carousel img { width: 100%; height: min(52vw, 460px); min-height: 260px; object-fit: cover; display: block; }
        .sp-news-share-card, .sp-news-related-card, .sp-news-video-card { background: #fff; border: 1px solid #e8edf5; border-radius: 18px; padding: 20px; box-shadow: 0 16px 38px rgba(20, 38, 70, .07); }
        .sp-news-video-local { width: 100%; border-radius: 14px; display: block; background: #000; }
        .sp-news-share-card h3, .sp-news-related-card h3, .sp-news-video-card h3 { font-size: 18px; color: #111c32; margin-bottom: 14px; }
        .sp-news-share-links { display: flex; flex-wrap: wrap; gap: 10px; }
        .sp-news-share-links a { width: 42px; height: 42px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #eef5ff; color: #2060b0; text-decoration: none; transition: .2s ease; }
        .sp-news-share-links a:hover { background: #2060b0; color: #fff; transform: translateY(-2px); }
        .sp-news-related-list { display: grid; gap: 14px; }
        .sp-news-related-item { display: grid; grid-template-columns: 74px 1fr; gap: 12px; align-items: center; color: inherit; text-decoration: none; }
        .sp-news-related-item img { width: 74px; height: 62px; object-fit: cover; border-radius: 12px; background: #eef2f7; }
        .sp-news-related-item strong { display: -webkit-box; color: #17233b; font-size: 14px; line-height: 1.35; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 3; }
        .sp-news-detail-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 34px; }
        .sp-news-video-trigger { width: 100%; border: 0; padding: 0; border-radius: 14px; overflow: hidden; position: relative; display: block; cursor: pointer; background: #0f172a; box-shadow: 0 16px 34px rgba(15, 23, 42, .2); }
        .sp-news-video-trigger img { width: 100%; aspect-ratio: 16 / 9; display: block; object-fit: cover; opacity: .78; transition: transform .28s ease, opacity .28s ease; }
        .sp-news-video-trigger:hover img { transform: scale(1.04); opacity: .92; }
        .sp-news-video-play { position: absolute; inset: 0; display: grid; place-items: center; color: #111c32; }
        .sp-news-video-play span { width: 62px; height: 62px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, .94); box-shadow: 0 10px 32px rgba(0, 0, 0, .3); }
        .sp-news-video-caption { margin: 12px 0 0; color: #536177; font-size: 14px; line-height: 1.45; }
        .sp-video-modal { position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 22px; opacity: 0; visibility: hidden; transition: opacity .25s ease, visibility .25s ease; }
        .sp-video-modal.is-open { opacity: 1; visibility: visible; }
        .sp-video-modal__backdrop { position: absolute; inset: 0; background: rgba(10, 14, 24, .88); backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px); }
        .sp-video-modal__dialog { position: relative; width: min(1120px, 100%); transform: scale(.96); transition: transform .25s ease; z-index: 1; }
        .sp-video-modal.is-open .sp-video-modal__dialog { transform: scale(1); }
        .sp-video-modal__close { position: absolute; right: 0; top: -54px; width: 42px; height: 42px; border-radius: 50%; border: 1px solid rgba(255,255,255,.25); background: rgba(255,255,255,.12); color: #fff; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
        .sp-video-modal__close:hover { background: rgba(255,255,255,.22); }
        .sp-video-modal__player { position: relative; width: 100%; aspect-ratio: 16 / 9; background: #000; border-radius: 14px; overflow: hidden; box-shadow: 0 30px 80px rgba(0,0,0,.55); }
        .sp-video-modal__player iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; display: block; }
        @media (max-width: 991px) {
            .sp-news-detail-layout { grid-template-columns: 1fr; }
            .sp-news-detail-aside { position: static; }
        }
        @media (max-width: 575px) {
            .sp-news-detail { padding-top: 34px; }
            .sp-news-detail-card { border-radius: 18px; padding: 20px; }
            .sp-news-detail-image { border-radius: 16px; }
            .sp-video-modal__close { top: -48px; }
        }
    </style>
</head>
<body>
    <div class="sp-colorband"></div>
    <?php
    $headerComponent = cms_get_component_path('header_principal');
    if ($headerComponent) {
        include $headerComponent;
    }
    ?>

    <main class="sp-news-detail">
        <div class="container sp-news-detail-shell">
            <nav class="sp-news-breadcrumb" aria-label="Breadcrumb">
                <a href="index.php">Inicio</a>
                <span><i class="fas fa-chevron-right"></i> <a href="index.php#noticias">Noticias</a></span>
                <span><i class="fas fa-chevron-right"></i> <?= e($pageTitle) ?></span>
            </nav>

            <div class="sp-news-detail-layout">
                <article class="sp-news-detail-card">
                <?php if (!$newsItem): ?>
                    <span class="sp-news-detail-tag">Noticias</span>
                    <h1 class="sp-news-detail-title">Noticia no disponible</h1>
                    <p class="sp-news-detail-content">La noticia solicitada no existe o no se encuentra visible.</p>
                    <div class="sp-news-detail-actions">
                        <a class="btn-ver-mas" href="index.php#noticias">Volver a noticias</a>
                    </div>
                <?php else: ?>
                    <span class="sp-news-detail-tag mb-3">Noticias</span>
                    <div class="sp-news-detail-meta">
                        <?php if (!empty($newsItem['fecha_publicacion'])): ?>
                            <span><i class="fas fa-calendar-alt me-2"></i><?= e($newsItem['fecha_publicacion']) ?></span>
                        <?php endif; ?>
                        <?php if ($newsCategory !== ''): ?>
                            <span><i class="fas fa-folder-open me-2"></i><?= e($newsCategory) ?></span>
                        <?php endif; ?>
                    </div>
                    <h1 class="sp-news-detail-title"><?= e($newsItem['titulo'] ?? '') ?></h1>

                    <img class="sp-news-detail-image" src="<?= e($image) ?>" alt="<?= e($newsItem['titulo'] ?? 'Noticia') ?>">

                    <?php if ($introHtml !== ''): ?>
                        <div class="sp-news-detail-intro"><?= $introHtml ?></div>
                    <?php endif; ?>

                    <?php if ($bodyHtml !== ''): ?>
                        <div class="sp-news-detail-content sp-news-detail-block"><?= $bodyHtml ?></div>
                    <?php endif; ?>

                    <?php if ($detailGallery): ?>
                        <section class="sp-news-detail-block">
                            <h2>Galería de imágenes</h2>
                            <div id="newsDetailGallery" class="carousel slide sp-news-gallery-carousel" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($detailGallery as $index => $detailImage): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= e((string) $detailImage['archivo']) ?>" alt="<?= e((string) ($detailImage['titulo'] ?: ($newsItem['titulo'] ?? 'Noticia'))) ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($detailGallery) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#newsDetailGallery" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#newsDetailGallery" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            <?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <div class="sp-news-detail-actions">
                        <a class="btn-ver-mas" href="index.php#noticias">Volver a noticias</a>
                        <?php if ($buttonUrl !== ''): ?>
                            <a class="btn-ver-mas" href="<?= e($buttonUrl) ?>" <?= $isExternalButton ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($buttonText) ?></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                </article>

                <?php if ($newsItem): ?>
                    <aside class="sp-news-detail-aside">
                        <div class="sp-news-share-card">
                            <h3>Compartir noticia</h3>
                            <div class="sp-news-share-links">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= e($encodedShareUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Compartir en Facebook"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://twitter.com/intent/tweet?text=<?= e($encodedShareTitle) ?>&url=<?= e($encodedShareUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Compartir en X"><i class="fab fa-twitter"></i></a>
                                <a href="https://api.whatsapp.com/send?text=<?= e($encodedShareTitle) ?>%20<?= e($encodedShareUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Compartir por WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                <a href="mailto:?subject=<?= e($encodedShareTitle) ?>&body=<?= e($encodedShareUrl) ?>" aria-label="Compartir por correo"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>

                        <?php if ($relatedNews): ?>
                            <div class="sp-news-related-card">
                                <h3>Noticias relacionadas</h3>
                                <div class="sp-news-related-list">
                                    <?php foreach ($relatedNews as $related): ?>
                                        <a class="sp-news-related-item" href="noticia_detalle.php?id=<?= (int) $related['id_item'] ?>">
                                            <img src="<?= e($related['imagen'] ?: 'assets/images/frontis_01.jpg') ?>" alt="<?= e($related['titulo'] ?? 'Noticia relacionada') ?>">
                                            <strong><?= e($related['titulo'] ?? '') ?></strong>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($youtubeEmbed !== ''): ?>
                            <div class="sp-news-video-card">
                                <h3>Video relacionado</h3>
                                <button class="sp-news-video-trigger" type="button" data-video-src="<?= e($youtubeEmbed) ?>" aria-label="Reproducir video relacionado">
                                    <img src="<?= e($youtubeThumb !== '' ? $youtubeThumb : $image) ?>" <?= $youtubeThumbFallback !== '' ? 'onerror="this.onerror=null;this.src=\'' . e($youtubeThumbFallback) . '\';"' : '' ?> alt="<?= e($newsItem['titulo'] ?? 'Video relacionado') ?>">
                                    <span class="sp-news-video-play" aria-hidden="true"><span><i class="fa-solid fa-play"></i></span></span>
                                </button>
                                <p class="sp-news-video-caption"><?= e($newsItem['titulo'] ?? 'Video relacionado') ?></p>
                            </div>
                        <?php elseif ($localVideo !== ''): ?>
                            <div class="sp-news-video-card">
                                <h3>Video relacionado</h3>
                                <video class="sp-news-video-local" controls preload="metadata" src="<?= e($localVideo) ?>"></video>
                            </div>
                        <?php endif; ?>
                    </aside>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php if ($youtubeEmbed !== ''): ?>
        <div class="sp-video-modal" id="newsVideoModal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Reproductor de video">
            <div class="sp-video-modal__backdrop" data-video-close></div>
            <div class="sp-video-modal__dialog">
                <button class="sp-video-modal__close" type="button" data-video-close aria-label="Cerrar video">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="sp-video-modal__player">
                    <iframe id="newsVideoFrame" src="" title="<?= e($newsItem['titulo'] ?? 'Video relacionado') ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php
    $footerComponent = cms_get_component_path('footer_principal');
    if ($footerComponent) {
        include $footerComponent;
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
    <?php if ($youtubeEmbed !== ''): ?>
        <script>
            (function () {
                var modal = document.getElementById('newsVideoModal');
                var frame = document.getElementById('newsVideoFrame');
                var trigger = document.querySelector('.sp-news-video-trigger');
                var closeButtons = document.querySelectorAll('[data-video-close]');
                if (!modal || !frame || !trigger) {
                    return;
                }

                function openVideo() {
                    var src = trigger.getAttribute('data-video-src') || '';
                    frame.src = src + (src.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1&rel=0&modestbranding=1';
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                    var close = modal.querySelector('.sp-video-modal__close');
                    if (close) {
                        close.focus();
                    }
                }

                function closeVideo() {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                    frame.src = '';
                    document.body.style.overflow = '';
                    trigger.focus();
                }

                trigger.addEventListener('click', openVideo);
                closeButtons.forEach(function (button) {
                    button.addEventListener('click', closeVideo);
                });
                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                        closeVideo();
                    }
                });
            })();
        </script>
    <?php endif; ?>
</body>
</html>
