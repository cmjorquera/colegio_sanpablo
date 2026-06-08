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
$newsItem = null;
$newsCategory = '';
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
} catch (Throwable $exception) {
    error_log('noticia_detalle.php: ' . $exception->getMessage());
}

$pageTitle = trim((string) ($newsItem['titulo'] ?? 'Noticia'));
$image = trim((string) ($newsItem['imagen'] ?? ''));
if ($image === '') {
    $image = 'assets/images/frontis_01.jpg';
}

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
        .sp-news-detail { padding: 70px 0 90px; background: #fff; }
        .sp-news-detail-card { max-width: 980px; margin: 0 auto; }
        .sp-news-detail-meta { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; color: #6d788b; font-size: 14px; margin-bottom: 18px; }
        .sp-news-detail-tag { display: inline-flex; align-items: center; padding: 8px 14px; border-radius: 999px; background: #e8f2ff; color: #2060b0; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; font-size: 12px; }
        .sp-news-detail-title { color: #151f33; font-size: clamp(34px, 5vw, 56px); line-height: 1.08; margin-bottom: 26px; }
        .sp-news-detail-image { width: 100%; max-height: 520px; object-fit: cover; border-radius: 24px; box-shadow: 0 24px 70px rgba(15, 35, 70, .14); margin-bottom: 34px; }
        .sp-news-detail-content { color: #4d5565; font-size: 18px; line-height: 1.85; white-space: pre-line; }
        .sp-news-detail-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 34px; }
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
        <div class="container">
            <article class="sp-news-detail-card">
                <?php if (!$newsItem): ?>
                    <span class="section-label">Noticias</span>
                    <h1 class="sp-news-detail-title">Noticia no disponible</h1>
                    <p class="sp-news-detail-content">La noticia solicitada no existe o no se encuentra visible.</p>
                    <div class="sp-news-detail-actions">
                        <a class="btn-ver-mas" href="index.php#noticias">Volver a noticias</a>
                    </div>
                <?php else: ?>
                    <div class="sp-news-detail-meta">
                        <?php if ($newsCategory !== ''): ?>
                            <span class="sp-news-detail-tag"><?= e($newsCategory) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($newsItem['fecha_publicacion'])): ?>
                            <span><i class="fas fa-calendar-alt me-2"></i><?= e($newsItem['fecha_publicacion']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h1 class="sp-news-detail-title"><?= e($newsItem['titulo'] ?? '') ?></h1>
                    <img class="sp-news-detail-image" src="<?= e($image) ?>" alt="<?= e($newsItem['titulo'] ?? 'Noticia') ?>">
                    <div class="sp-news-detail-content"><?= e($newsItem['descripcion'] ?? '') ?></div>
                    <div class="sp-news-detail-actions">
                        <a class="btn-ver-mas" href="index.php#noticias">Volver a noticias</a>
                        <?php if ($buttonUrl !== ''): ?>
                            <a class="btn-ver-mas" href="<?= e($buttonUrl) ?>" <?= $isExternalButton ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($buttonText) ?></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
        </div>
    </main>

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
</body>
</html>
