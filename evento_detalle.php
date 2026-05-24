<?php
require_once __DIR__ . '/includes/cms_helpers.php';

$db = cms_get_connection();
$site = cms_get_site_data($db);
$idEvento = (int) ($_GET['id_evento'] ?? 0);
$idItem = (int) ($_GET['id_item'] ?? 0);
$sourceType = '';
$rawEvent = null;

if ($idEvento > 0) {
    $rawEvent = cms_get_event($db, $idEvento);
    if ($rawEvent && ((int) ($rawEvent['visible'] ?? 1) !== 1 || in_array((string) ($rawEvent['estado'] ?? 'publicado'), ['cancelado', 'oculto', 'borrador'], true))) {
        $rawEvent = null;
    }
    $sourceType = $rawEvent ? 'eventos' : '';
} elseif ($idItem > 0) {
    $rawEvent = cms_get_item($db, $idItem);
    if ($rawEvent && ($rawEvent['visible'] ?? 'si') !== 'si') {
        $rawEvent = null;
    }
    $sourceType = $rawEvent ? 'seccion_item' : '';
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return cms_e($value);
    }
}

function event_detail_asset(?string $path, string $fallback = 'assets/images/frontis_01.jpg'): string
{
    $path = trim((string) $path);
    if ($path !== '' && !preg_match('/^https?:\/\//i', $path) && is_file(__DIR__ . '/' . ltrim($path, '/'))) {
        return $path;
    }
    if ($path !== '' && preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    return $fallback;
}

function event_detail_asset_exists(?string $path): bool
{
    $path = trim((string) $path);
    if ($path === '') {
        return false;
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return true;
    }
    return is_file(__DIR__ . '/' . ltrim($path, '/'));
}

function event_detail_date(?string $date): string
{
    if (!$date) {
        return 'Por confirmar';
    }
    $time = strtotime($date);
    return $time ? date('d/m/Y', $time) : $date;
}

function event_detail_time(?string $time): string
{
    $time = trim((string) $time);
    return $time !== '' ? substr($time, 0, 5) : '';
}

if ($sourceType === 'eventos') {
    $rawImage = $rawEvent['imagen'] ?? '';
    $event = [
        'title' => $rawEvent['titulo'] ?? '',
        'short_description' => $rawEvent['descripcion_corta'] ?? '',
        'description' => $rawEvent['descripcion'] ?? '',
        'date_start' => $rawEvent['fecha_inicio'] ?? '',
        'date_end' => $rawEvent['fecha_termino'] ?? ($rawEvent['fecha_inicio'] ?? ''),
        'time_start' => event_detail_time($rawEvent['hora_inicio'] ?? ''),
        'time_end' => event_detail_time($rawEvent['hora_termino'] ?? ''),
        'location' => $rawEvent['ubicacion'] ?? '',
        'category' => $rawEvent['categoria'] ?? 'Institucional',
        'color' => $rawEvent['color'] ?? '#1f8f6b',
        'image' => event_detail_asset($rawImage),
        'has_real_image' => event_detail_asset_exists($rawImage),
        'attachment' => $rawEvent['archivo_adjunto'] ?? '',
        'featured' => (int) ($rawEvent['destacado'] ?? 0) === 1,
    ];
} elseif ($sourceType === 'seccion_item') {
    $rawImage = $rawEvent['imagen'] ?? '';
    $event = [
        'title' => $rawEvent['titulo'] ?? 'Evento institucional',
        'short_description' => $rawEvent['subtitulo'] ?? '',
        'description' => $rawEvent['descripcion'] ?? '',
        'date_start' => $rawEvent['fecha_publicacion'] ?? '',
        'date_end' => $rawEvent['fecha_publicacion'] ?? '',
        'time_start' => $rawEvent['subtitulo'] ?? '',
        'time_end' => '',
        'location' => $rawEvent['boton_2_texto'] ?? '',
        'category' => $rawEvent['etiqueta'] ?? 'Institucional',
        'color' => '#1f8f6b',
        'image' => event_detail_asset($rawImage),
        'has_real_image' => event_detail_asset_exists($rawImage),
        'attachment' => '',
        'featured' => false,
    ];
} else {
    $event = [
        'title' => 'Evento no encontrado',
        'short_description' => '',
        'description' => '',
        'date_start' => '',
        'date_end' => '',
        'time_start' => '',
        'time_end' => '',
        'location' => '',
        'category' => 'Evento',
        'color' => '#1f8f6b',
        'image' => event_detail_asset(null),
        'has_real_image' => false,
        'attachment' => '',
        'featured' => false,
    ];
}

$galleryImages = [];
if ($rawEvent && !empty($event['has_real_image'])) {
    $galleryImages[] = $event['image'];
}
$hasVideos = false;
$institution = $site['institution'] ?? [];
$favicon = $institution['favicon'] ?? 'assets/images/icono_ppt.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($event['title']) ?> | Colegio San Pablo</title>
    <link rel="shortcut icon" href="<?= e($favicon) ?>">
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
        .event-detail-page { background: #f5f7fb; }
        .event-hero {
            position: relative;
            min-height: 520px;
            display: flex;
            align-items: flex-end;
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }
        .event-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(8, 20, 35, .30), rgba(8, 20, 35, .82));
        }
        .event-hero__content {
            position: relative;
            z-index: 1;
            width: 100%;
            padding: 120px 0 70px;
            color: #fff;
        }
        .event-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 9px 14px;
            background: rgba(255,255,255,.16);
            border: 1px solid rgba(255,255,255,.26);
            backdrop-filter: blur(8px);
            font-weight: 700;
            margin-bottom: 18px;
        }
        .event-hero h1 {
            color: #fff;
            font-size: clamp(34px, 5vw, 64px);
            line-height: 1.06;
            max-width: 940px;
            margin-bottom: 18px;
        }
        .event-hero p {
            max-width: 760px;
            color: rgba(255,255,255,.88);
            font-size: 18px;
            margin-bottom: 24px;
        }
        .event-quick-data {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .event-quick-data span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 13px;
            border-radius: 14px;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.20);
            color: #fff;
        }
        .event-detail-wrap {
            max-width: 1180px;
            margin: 0 auto;
            padding: 70px 12px;
        }
        .event-card {
            background: #fff;
            border: 1px solid #e4ebf5;
            border-radius: 22px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .08);
            padding: 28px;
            margin-bottom: 24px;
        }
        .event-card h2,
        .event-card h3 {
            color: #12324a;
            margin-bottom: 16px;
        }
        .event-lead {
            color: #42526b;
            font-size: 17px;
            line-height: 1.75;
        }
        .event-body-text {
            color: #5c6b80;
            line-height: 1.85;
        }
        .event-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }
        .event-gallery-item {
            aspect-ratio: 4 / 3;
            border-radius: 18px;
            overflow: hidden;
            background: #eef3f8;
        }
        .event-gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .event-gallery-empty {
            min-height: 180px;
            border: 1px dashed #cbd7e6;
            border-radius: 18px;
            display: grid;
            place-items: center;
            text-align: center;
            color: #72809a;
            background: #f8fbff;
            padding: 22px;
        }
        .event-side-card {
            position: sticky;
            top: 24px;
        }
        .event-info-list {
            display: grid;
            gap: 14px;
            margin: 0 0 24px;
            padding: 0;
            list-style: none;
        }
        .event-info-list li {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 12px;
            align-items: start;
        }
        .event-info-list i {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: #e8f7f1;
            color: #1f8f6b;
        }
        .event-info-list strong {
            display: block;
            color: #12324a;
            margin-bottom: 2px;
        }
        .event-info-list span {
            color: #627188;
        }
        .event-back-btn,
        .event-file-btn {
            width: 100%;
            min-height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 700;
        }
        .event-back-btn {
            background: linear-gradient(135deg, #1f8f6b, #27b785);
            color: #fff;
        }
        .event-back-btn:hover { color: #fff; }
        .event-file-btn {
            background: #eef4fb;
            color: #12324a;
            margin-bottom: 10px;
        }
        .event-missing {
            min-height: 320px;
            display: grid;
            place-items: center;
            text-align: center;
        }
        @media (max-width: 991px) {
            .event-hero { min-height: 460px; }
            .event-side-card { position: static; }
        }
    </style>
</head>
<body class="event-detail-page">
    <div class="sp-colorband"></div>

    <header class="sp-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between">
                <div class="sp-logo py-2">
                    <a href="index_1.php">
                        <img src="<?= e($institution['logo_header'] ?? 'assets/images/logo/logo.svg') ?>" alt="Colegio San Pablo" onerror="this.src='assets/images/logo/logo.svg'">
                    </a>
                </div>
                <nav class="sp-nav">
                    <ul>
                        <?php foreach (($site['menus'] ?? []) as $i => $menu): ?>
                            <?php
                            $idMenu = (int) $menu['id_menu'];
                            $hasSubs = !empty($site['subs'][$idMenu]);
                            ?>
                            <li<?= $i === 0 ? ' class="active"' : '' ?>>
                                <a href="<?= e($menu['url'] ?: '#') ?>"><?= e($menu['nombre']) ?><?= $hasSubs ? ' ▾' : '' ?></a>
                                <?php if ($hasSubs): ?>
                                    <ul class="dropdown">
                                        <?php foreach ($site['subs'][$idMenu] as $sub): ?>
                                            <li><a href="<?= e($sub['url'] ?: '#') ?>"><?= e($sub['nombre']) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <section class="event-hero" style="background-image:url('<?= e($event['image']) ?>');">
        <div class="event-hero__content">
            <div class="container">
                <span class="event-badge" style="--event-color: <?= e($event['color']) ?>;"><i class="fa-solid fa-tag"></i><?= e($event['category']) ?></span>
                <h1><?= e($rawEvent ? $event['title'] : 'Evento no encontrado') ?></h1>
                <?php if ($event['short_description'] !== ''): ?>
                    <p><?= e($event['short_description']) ?></p>
                <?php endif; ?>
                <?php if ($rawEvent): ?>
                    <div class="event-quick-data">
                        <span><i class="fa-regular fa-calendar"></i><?= e(event_detail_date($event['date_start'])) ?><?= $event['date_end'] && $event['date_end'] !== $event['date_start'] ? ' al ' . e(event_detail_date($event['date_end'])) : '' ?></span>
                        <span><i class="fa-regular fa-clock"></i><?= e($event['time_start'] ?: 'Por confirmar') ?><?= $event['time_end'] ? ' - ' . e($event['time_end']) : '' ?></span>
                        <span><i class="fa-solid fa-location-dot"></i><?= e($event['location'] ?: 'Por confirmar') ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <main class="event-detail-wrap">
        <?php if (!$rawEvent): ?>
            <div class="event-card event-missing">
                <div>
                    <h2>Evento no disponible</h2>
                    <p class="text-muted mb-4">El evento solicitado no existe o no se encuentra publicado.</p>
                    <a href="index_1.php#calendario-eventos-home" class="event-back-btn" style="max-width:260px;"><i class="fa-light fa-arrow-left-long"></i>Volver a eventos</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <section class="event-card">
                        <h2>Sobre el evento</h2>
                        <?php if ($event['short_description'] !== ''): ?>
                            <p class="event-lead"><?= e($event['short_description']) ?></p>
                        <?php endif; ?>
                        <?php if ($event['description'] !== ''): ?>
                            <div class="event-body-text"><?= nl2br(e($event['description'])) ?></div>
                        <?php else: ?>
                            <div class="event-body-text text-muted">Pronto se publicará más información sobre esta actividad.</div>
                        <?php endif; ?>
                    </section>

                    <section class="event-card">
                        <h3>Imágenes destacadas</h3>
                        <?php if ($galleryImages): ?>
                            <div id="eventFeaturedCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner rounded-4 overflow-hidden">
                                    <?php foreach ($galleryImages as $index => $image): ?>
                                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                            <img src="<?= e($image) ?>" class="d-block w-100" style="height:420px;object-fit:cover;" alt="<?= e($event['title']) ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="event-gallery-empty">No hay imágenes destacadas para este evento.</div>
                        <?php endif; ?>
                    </section>

                    <section class="event-card">
                        <h3>Galería del evento</h3>
                        <?php if ($galleryImages): ?>
                            <div class="event-gallery-grid">
                                <?php foreach ($galleryImages as $image): ?>
                                    <a class="event-gallery-item image-popup" href="<?= e($image) ?>">
                                        <img src="<?= e($image) ?>" alt="<?= e($event['title']) ?>">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="event-gallery-empty">
                                <div>
                                    <i class="fa-regular fa-images mb-2 d-block fs-2"></i>
                                    Galería preparada para próximas imágenes del evento.
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>

                    <?php if ($hasVideos): ?>
                        <section class="event-card">
                            <h3>Videos del evento</h3>
                            <div class="event-gallery-empty">Sección preparada para videos MP4 o YouTube.</div>
                        </section>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <aside class="event-card event-side-card">
                        <h3>Datos del evento</h3>
                        <ul class="event-info-list">
                            <li><i class="fa-regular fa-calendar"></i><div><strong>Fecha</strong><span><?= e(event_detail_date($event['date_start'])) ?><?= $event['date_end'] && $event['date_end'] !== $event['date_start'] ? ' al ' . e(event_detail_date($event['date_end'])) : '' ?></span></div></li>
                            <li><i class="fa-regular fa-clock"></i><div><strong>Hora</strong><span><?= e($event['time_start'] ?: 'Por confirmar') ?><?= $event['time_end'] ? ' - ' . e($event['time_end']) : '' ?></span></div></li>
                            <li><i class="fa-solid fa-location-dot"></i><div><strong>Ubicación</strong><span><?= e($event['location'] ?: 'Por confirmar') ?></span></div></li>
                            <li><i class="fa-solid fa-tag"></i><div><strong>Categoría</strong><span><?= e($event['category']) ?></span></div></li>
                            <?php if ($event['featured']): ?>
                                <li><i class="fa-solid fa-star"></i><div><strong>Destacado</strong><span>Evento destacado</span></div></li>
                            <?php endif; ?>
                        </ul>
                        <?php if ($event['attachment'] !== ''): ?>
                            <a href="<?= e($event['attachment']) ?>" class="event-file-btn" target="_blank" rel="noopener"><i class="fa-regular fa-file-lines"></i>Ver archivo adjunto</a>
                        <?php endif; ?>
                        <a href="index_1.php#calendario-eventos-home" class="event-back-btn"><i class="fa-light fa-arrow-left-long"></i>Volver a eventos</a>
                    </aside>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="sp-footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-5">
                    <div class="logo-footer"><img src="<?= e($institution['logo_footer'] ?? ($institution['logo_header'] ?? 'assets/images/logo/logo.svg')) ?>" alt="Colegio San Pablo"></div>
                    <p><?= e($institution['nombre'] ?? 'Colegio San Pablo') ?></p>
                </div>
                <div class="col-lg-4">
                    <h5>Contacto</h5>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i><?= e($institution['direccion'] ?? '') ?></li>
                        <li><i class="fas fa-phone"></i><?= e($institution['telefono'] ?? '') ?></li>
                        <li><i class="fas fa-envelope"></i><?= e($institution['email'] ?? '') ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="sp-footer-colorband"></div>
        <div class="sp-footer-bottom"><div class="container">© <?= date('Y') ?> Colegio San Pablo</div></div>
    </footer>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script src="assets/js/magnific-popup.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
