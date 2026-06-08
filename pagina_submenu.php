<?php
require_once __DIR__ . '/includes/cms_helpers.php';

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return cms_e($value);
    }
}

function sp_submenu_video_embed(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    if (preg_match('~vimeo\.com/([0-9]+)~', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }

    return '';
}

$idSubMenu = max(0, (int) ($_GET['id'] ?? $_GET['submenu'] ?? 0));
$institution = null;
$sectionConfigsMap = [];
$sectionItemsMap = [];
$arrMenus = [];
$arrSubs = [];
$page = null;

try {
    $db = cms_get_connection();
    $site = cms_get_site_data($db);
    $institution = $site['institution'];
    $sectionConfigsMap = $site['configs'];
    $sectionItemsMap = $site['items'];
    $arrMenus = $site['menus'];
    $arrSubs = $site['subs'];

    if ($idSubMenu > 0) {
        $page = cms_get_public_submenu_page($db, $idSubMenu);
    }
} catch (Throwable $exception) {
    error_log('pagina_submenu.php: ' . $exception->getMessage());
}

if (!$page) {
    http_response_code(404);
}

$title = $page ? trim((string) ($page['pagina_titulo'] ?: $page['nombre'])) : 'Pagina no encontrada';
$menuPadre = $page ? trim((string) ($page['menu_padre'] ?? '')) : '';
$bajada = $page ? trim((string) ($page['pagina_bajada'] ?? '')) : '';
$contenido = $page ? trim((string) ($page['pagina_contenido'] ?? '')) : '';
$hero = $page ? trim((string) ($page['pagina_imagen_hero'] ?? '')) : '';
$hero = $hero !== '' ? $hero : 'assets/images/frontis_01.jpg';
$heroVideoArchivo = $page ? trim((string) ($page['pagina_hero_video_archivo'] ?? '')) : '';
$heroVideoUrl = $page ? trim((string) ($page['pagina_hero_video_url'] ?? '')) : '';
$heroVideoEmbed = sp_submenu_video_embed($heroVideoUrl);
$secundaria = $page ? trim((string) ($page['pagina_imagen_secundaria'] ?? '')) : '';
$videoArchivo = $page ? trim((string) ($page['pagina_video_archivo'] ?? '')) : '';
$videoEmbed = $page ? sp_submenu_video_embed($page['pagina_video_url'] ?? '') : '';
$gallery = array_values(array_filter($page['pagina_media'] ?? [], static fn($m) => ($m['tipo'] ?? '') === 'imagen' && !empty($m['archivo'])));
$carouselImages = [];
if ($secundaria !== '') {
    $carouselImages[] = ['src' => $secundaria, 'alt' => $title];
}
foreach ($gallery as $media) {
    $carouselImages[] = ['src' => (string) $media['archivo'], 'alt' => (string) ($media['titulo'] ?: $title)];
}
$metaTitle = $page ? trim((string) ($page['pagina_meta_title'] ?: $title . ' | ' . ($institution['nombre'] ?? 'Colegio San Pablo'))) : 'Pagina no encontrada';
$metaDescription = $page ? trim((string) ($page['pagina_meta_description'] ?: $bajada)) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($metaTitle) ?></title>
    <?php if ($metaDescription !== ''): ?><meta name="description" content="<?= e($metaDescription) ?>"><?php endif; ?>
    <link rel="shortcut icon" href="<?= e($institution['favicon'] ?? 'assets/images/icono_ppt.png') ?>">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/colegiosanpablo.css">
    <style>
        .sp-subpage-hero {
            min-height: 460px;
            display: flex;
            align-items: end;
            position: relative;
            background: linear-gradient(90deg, rgba(9, 28, 49, .82), rgba(9, 28, 49, .36)), url('<?= e($hero) ?>') center/cover no-repeat;
            color: #fff;
            overflow: hidden;
        }
        .sp-subpage-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(9, 28, 49, .82), rgba(9, 28, 49, .34));
            z-index: 1;
        }
        .sp-subpage-hero-media {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }
        .sp-subpage-hero-media video,
        .sp-subpage-hero-media iframe {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border: 0;
        }
        .sp-subpage-hero-media iframe {
            width: 120%;
            height: 120%;
            margin-left: -10%;
            margin-top: -10%;
            pointer-events: none;
        }
        .sp-subpage-hero .container-fluid {
            position: relative;
            z-index: 2;
            padding: 0 56px 58px;
        }
        .sp-subpage-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border: 1px solid rgba(255,255,255,.32);
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            font-weight: 700;
            font-size: .82rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .sp-subpage-hero h1 {
            max-width: 850px;
            margin: 18px 0 12px;
            font-size: clamp(2.25rem, 5vw, 4.8rem);
            line-height: .98;
            color: #fff;
        }
        .sp-subpage-hero p {
            max-width: 760px;
            color: rgba(255,255,255,.88);
            font-size: 1.12rem;
        }
        .sp-subpage-shell { padding: 64px 0 78px; background: #fff; }
        .sp-subpage-layout {
            align-items: start;
        }
        .sp-subpage-content {
            font-size: 1.06rem;
            line-height: 1.85;
            color: #2c3440;
        }
        .sp-subpage-content p { margin-bottom: 1.1rem; }
        .sp-subpage-carousel {
            position: sticky;
            top: 110px;
        }
        .sp-subpage-carousel .carousel,
        .sp-subpage-carousel .carousel-inner,
        .sp-subpage-carousel .carousel-item {
            border-radius: 8px;
            overflow: hidden;
        }
        .sp-subpage-carousel figure {
            margin: 0;
            aspect-ratio: 4 / 3;
            background: #eef2f7;
        }
        .sp-subpage-carousel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sp-subpage-video {
            margin-top: 46px;
            min-height: 320px;
            border-radius: 8px;
            overflow: hidden;
            background: #111827;
        }
        .sp-subpage-video iframe,
        .sp-subpage-video video { width: 100%; height: 100%; min-height: 320px; display: block; }
        .sp-subpage-empty-media {
            border: 1px dashed #d8e0ea;
            border-radius: 8px;
            padding: 34px 22px;
            color: #64748b;
            background: #f8fafc;
            text-align: center;
        }
        .sp-subpage-empty {
            min-height: 52vh;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 80px 20px;
        }
        @media (max-width: 991px) {
            .sp-subpage-hero .container-fluid { padding: 0 24px 44px; }
            .sp-subpage-carousel { position: static; }
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

    <?php if (!$page): ?>
        <main class="sp-subpage-empty">
            <div>
                <h1>Pagina no encontrada</h1>
                <p class="text-muted">El contenido solicitado no esta disponible.</p>
                <a class="sp-btn-matricula d-inline-flex mt-3" href="index.php">Volver al inicio</a>
            </div>
        </main>
    <?php else: ?>
        <section class="sp-subpage-hero">
            <?php if ($heroVideoEmbed !== ''): ?>
                <div class="sp-subpage-hero-media">
                    <iframe src="<?= e($heroVideoEmbed) ?>?autoplay=1&mute=1&controls=0&loop=1&playsinline=1" title="<?= e($title) ?>" allow="autoplay; fullscreen" loading="lazy"></iframe>
                </div>
            <?php elseif ($heroVideoArchivo !== ''): ?>
                <div class="sp-subpage-hero-media">
                    <video src="<?= e($heroVideoArchivo) ?>" autoplay muted loop playsinline></video>
                </div>
            <?php endif; ?>
            <div class="container-fluid">
                <?php if ($menuPadre !== ''): ?><span class="sp-subpage-kicker"><i class="fas fa-layer-group"></i><?= e($menuPadre) ?></span><?php endif; ?>
                <h1><?= e($title) ?></h1>
                <?php if ($bajada !== ''): ?><p><?= e($bajada) ?></p><?php endif; ?>
                <?php if (!empty($page['pagina_boton_texto']) && !empty($page['pagina_boton_url'])): ?>
                    <a class="sp-btn-matricula d-inline-flex mt-3" href="<?= e($page['pagina_boton_url']) ?>"><?= e($page['pagina_boton_texto']) ?></a>
                <?php endif; ?>
            </div>
        </section>

        <main class="sp-subpage-shell">
            <div class="container">
                <div class="row g-5 sp-subpage-layout">
                    <article class="col-lg-7">
                        <div class="sp-subpage-content">
                            <?php if ($contenido !== ''): ?>
                                <?php foreach (preg_split("/\R{2,}/", $contenido) as $paragraph): ?>
                                    <p><?= nl2br(e($paragraph)) ?></p>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Este contenido esta listo para ser administrado desde el panel del sitio.</p>
                            <?php endif; ?>
                        </div>
                    </article>

                    <aside class="col-lg-5">
                        <div class="sp-subpage-carousel">
                            <?php if (!empty($carouselImages)): ?>
                                <div id="submenuImageCarousel" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php foreach ($carouselImages as $index => $image): ?>
                                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                <figure><img src="<?= e($image['src']) ?>" alt="<?= e($image['alt']) ?>"></figure>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($carouselImages) > 1): ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#submenuImageCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Anterior</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#submenuImageCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Siguiente</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="sp-subpage-empty-media">Agrega imagenes desde el panel para mostrar el carrusel.</div>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>

                <?php if ($videoEmbed !== '' || $videoArchivo !== ''): ?>
                    <div class="sp-subpage-video">
                        <?php if ($videoEmbed !== ''): ?>
                            <iframe src="<?= e($videoEmbed) ?>" title="<?= e($title) ?>" allowfullscreen loading="lazy"></iframe>
                        <?php else: ?>
                            <video src="<?= e($videoArchivo) ?>" controls></video>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                </div>
        </main>
    <?php endif; ?>

    <?php
    $footerComponent = cms_get_component_path('footer_principal');
    if ($footerComponent) {
        include $footerComponent;
    }
    ?>
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
