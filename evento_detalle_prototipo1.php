<?php
require_once __DIR__ . '/includes/cms_helpers.php';

$db = cms_get_connection();
$site = cms_get_site_data($db);

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return cms_e($value ?? '');
    }
}

function sp_db_quote($db, string $value): string
{
    if ($db instanceof mysqli) {
        return "'" . $db->real_escape_string($value) . "'";
    }
    if ($db instanceof PDO) {
        return $db->quote($value);
    }
    return "'" . addslashes($value) . "'";
}

function sp_db_query($db, string $sql)
{
    if ($db instanceof mysqli) {
        return $db->query($sql);
    }
    if ($db instanceof PDO) {
        return $db->query($sql);
    }
    if (is_object($db) && method_exists($db, 'query')) {
        return $db->query($sql);
    }
    return false;
}

function sp_fetch_all($result): array
{
    if (!$result) {
        return [];
    }
    if ($result instanceof mysqli_result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    if ($result instanceof PDOStatement) {
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
    if (is_object($result) && method_exists($result, 'fetch_all')) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function sp_table_exists($db, string $table): bool
{
    $tableQuoted = sp_db_quote($db, $table);
    $sql = "SHOW TABLES LIKE {$tableQuoted}";
    $result = sp_db_query($db, $sql);
    return count(sp_fetch_all($result)) > 0;
}

function sp_file_exists_public(?string $path): bool
{
    if (!$path) return false;
    if (preg_match('/^https?:\/\//i', $path)) return true;
    $clean = ltrim($path, '/');
    return is_file(__DIR__ . '/' . $clean);
}

function sp_public_asset(?string $path, string $fallback = 'assets/images/event/event-details.jpg'): string
{
    if ($path && sp_file_exists_public($path)) {
        return $path;
    }
    return $fallback;
}

function sp_format_date(?string $date): string
{
    if (!$date || $date === '0000-00-00') return 'Por confirmar';
    $ts = strtotime($date);
    if (!$ts) return $date;
    $dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    return $dias[(int)date('w', $ts)] . ' ' . date('d', $ts) . ' de ' . $meses[(int)date('n', $ts) - 1] . ' de ' . date('Y', $ts);
}

function sp_format_short_date(?string $date): string
{
    if (!$date || $date === '0000-00-00') return 'Por confirmar';
    $ts = strtotime($date);
    return $ts ? date('d-m-Y', $ts) : $date;
}

function sp_format_hour(?string $hour): string
{
    $hour = trim((string)$hour);
    if ($hour === '' || $hour === '00:00:00') return '';
    return substr($hour, 0, 5) . ' hrs.';
}

function sp_youtube_embed(string $url): string
{
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]+)/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return $url;
}

function sp_get_event_media($db, int $idEvento): array
{
    if ($idEvento <= 0 || !sp_table_exists($db, 'evento_media')) {
        return [];
    }

    $sql = "SELECT * FROM evento_media 
            WHERE id_evento = {$idEvento} AND visible = 1
            ORDER BY portada DESC, orden ASC, id_media ASC";
    return sp_fetch_all(sp_db_query($db, $sql));
}

$idEvento = (int) ($_GET['id_evento'] ?? 0);
$idItem = (int) ($_GET['id_item'] ?? 0);

$evento = null;
$legacy = false;

if ($idEvento > 0) {
    $evento = cms_get_event($db, $idEvento);
} elseif ($idItem > 0 && function_exists('cms_get_section_item')) {
    $item = cms_get_section_item($db, $idItem);
    if ($item) {
        $legacy = true;
        $evento = [
            'id_evento' => 0,
            'titulo' => $item['titulo'] ?? 'Evento',
            'descripcion_corta' => $item['descripcion'] ?? '',
            'descripcion' => $item['descripcion'] ?? '',
            'fecha_inicio' => $item['fecha_publicacion'] ?? '',
            'fecha_termino' => $item['fecha_publicacion'] ?? '',
            'hora_inicio' => $item['subtitulo'] ?? '',
            'hora_termino' => '',
            'ubicacion' => '',
            'categoria' => $item['etiqueta'] ?? 'Institucional',
            'color' => '#2563EB',
            'imagen' => $item['imagen'] ?? '',
            'archivo_adjunto' => '',
            'visible' => ($item['visible'] ?? 'si') === 'si' ? 1 : 0,
            'estado' => 'publicado',
        ];
    }
}

$title = $evento['titulo'] ?? 'Evento no encontrado';
$category = $evento['categoria'] ?? 'Institucional';
$categoryColor = $evento['color'] ?? ($site['institution']['color_primario'] ?? '#2563EB');
$dateStart = $evento['fecha_inicio'] ?? '';
$dateEnd = $evento['fecha_termino'] ?? $dateStart;
$timeStart = sp_format_hour($evento['hora_inicio'] ?? '');
$timeEnd = sp_format_hour($evento['hora_termino'] ?? '');
$hourText = $timeStart ? ($timeStart . ($timeEnd ? ' - ' . $timeEnd : '')) : 'Por confirmar';
$location = $evento['ubicacion'] ?? 'Por confirmar';
$shortDescription = trim((string)($evento['descripcion_corta'] ?? ''));
$description = trim((string)($evento['descripcion'] ?? ''));
$attachment = trim((string)($evento['archivo_adjunto'] ?? ''));

$primaryColor = $site['institution']['color_primario'] ?? '#2563EB';
$secondaryColor = $site['institution']['color_secundario'] ?? '#E9A629';
$tertiaryColor = $site['institution']['color_terciario'] ?? '#222222';
$logoHeader = $site['institution']['logo_header'] ?? 'assets/images/logo/logo.svg';
$logoFooter = $site['institution']['logo_footer'] ?? ($site['institution']['logo_header'] ?? 'assets/images/logo/logo.svg');
$fallbackImage = 'assets/images/event/event-details.jpg';
$mainImage = sp_public_asset($evento['imagen'] ?? '', $fallbackImage);

$media = $idEvento > 0 ? sp_get_event_media($db, $idEvento) : [];
$images = [];
$videos = [];

foreach ($media as $m) {
    $type = $m['tipo'] ?? 'imagen';
    if ($type === 'imagen') {
        $src = $m['archivo'] ?? $m['url'] ?? '';
        if ($src !== '') {
            $images[] = $m + ['src' => sp_public_asset($src, $fallbackImage)];
        }
    } elseif (in_array($type, ['video', 'youtube'], true)) {
        $src = $m['archivo'] ?? $m['url'] ?? '';
        if ($src !== '') {
            $videos[] = $m + ['src' => $src];
        }
    }
}

if (empty($images)) {
    $images[] = [
        'id_media' => 0,
        'src' => $mainImage,
        'titulo' => $title,
        'descripcion' => $shortDescription,
        'tipo' => 'imagen'
    ];
}

$validEvent = $evento && (int)($evento['visible'] ?? 1) === 1 && (($evento['estado'] ?? 'publicado') !== 'cancelado');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | Colegio San Pablo</title>
    <link rel="shortcut icon" href="<?= e($site['institution']['favicon'] ?? 'assets/images/icono_ppt.png') ?>">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/colegiosanpablo.css">
    <style>
        :root{
            --sp-primary: <?= e($primaryColor) ?>;
            --sp-secondary: <?= e($secondaryColor) ?>;
            --sp-tertiary: <?= e($tertiaryColor) ?>;
            --sp-soft: #f3f7fb;
            --sp-text: #132238;
            --sp-muted: #64748b;
            --sp-radius: 22px;
        }
        body{background:#f3f7fb;color:var(--sp-text)}
        .sp-colorband,.sp-footer-colorband{height:6px;background:linear-gradient(90deg,var(--sp-primary),var(--sp-secondary),#f97316,#ef4444)}
        .sp-header{background:#fff;box-shadow:0 10px 35px rgba(15,23,42,.08);position:relative;z-index:10}
        .sp-logo img{max-height:54px;width:auto}.sp-nav ul{list-style:none;display:flex;gap:24px;margin:0;padding:0}.sp-nav li{position:relative}.sp-nav a{font-size:13px;font-weight:700;color:#243247;text-decoration:none}.sp-nav .dropdown{display:none;position:absolute;background:#fff;box-shadow:0 16px 40px rgba(15,23,42,.12);border-radius:14px;min-width:210px;padding:12px;top:100%;left:0}.sp-nav li:hover>.dropdown{display:block}.sp-nav .dropdown li{margin:6px 0}
        .event-hero{position:relative;min-height:430px;background-image:linear-gradient(90deg,rgba(8,16,32,.80),rgba(8,16,32,.48),rgba(8,16,32,.20)),url('<?= e($mainImage) ?>');background-size:cover;background-position:center;display:flex;align-items:flex-end;overflow:hidden}
        .event-hero:after{content:"";position:absolute;inset:auto 0 0 0;height:110px;background:linear-gradient(180deg,transparent,#f3f7fb)}
        .event-hero__content{position:relative;z-index:2;padding:90px 0 120px;color:#fff}.event-badge{display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:9px 15px;font-size:13px;font-weight:800;background:<?= e($categoryColor ?: $primaryColor) ?>;box-shadow:0 12px 30px rgba(0,0,0,.18)}
        .event-hero h1{font-size:clamp(34px,5vw,68px);line-height:1.03;font-weight:900;color:#fff;margin:20px 0 16px;letter-spacing:-.04em}.event-hero p{max-width:680px;font-size:18px;line-height:1.65;color:rgba(255,255,255,.92)}
        .event-quick{display:flex;flex-wrap:wrap;gap:14px;margin-top:28px}.event-quick__item{display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:18px;background:rgba(0,0,0,.32);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.16);font-weight:800;color:#fff}.event-quick__item i{font-size:20px;color:#fff}
        .event-page{margin-top:-80px;position:relative;z-index:3;padding-bottom:90px}.event-card{background:#fff;border:1px solid rgba(148,163,184,.18);border-radius:var(--sp-radius);box-shadow:0 22px 60px rgba(15,23,42,.08)}
        .event-card__body{padding:28px}.event-section-title{font-size:24px;font-weight:900;letter-spacing:-.02em;margin-bottom:18px;color:#0f172a}.event-text{font-size:16px;line-height:1.8;color:#334155}.event-sidebar{position:sticky;top:24px}.event-info-list{display:grid;gap:16px;margin:0;padding:0;list-style:none}.event-info-list li{display:flex;gap:14px;padding-bottom:16px;border-bottom:1px solid #e8eef6}.event-info-list i{width:36px;height:36px;border-radius:12px;background:rgba(37,99,235,.10);color:var(--sp-primary);display:flex;align-items:center;justify-content:center}.event-info-list strong{display:block;font-size:13px;color:#64748b;margin-bottom:2px}.event-info-list span{font-weight:800;color:#0f172a}
        .event-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:13px 18px;border-radius:14px;border:0;background:linear-gradient(90deg,var(--sp-primary),var(--sp-secondary));color:#fff;font-weight:900;text-decoration:none;box-shadow:0 14px 30px rgba(37,99,235,.18)}.event-btn:hover{color:#fff;transform:translateY(-1px)}.event-btn-light{background:#fff;color:var(--sp-primary);border:1px solid #dbe6f3;box-shadow:none}.event-btn-light:hover{color:var(--sp-primary);background:#f8fbff}
        .featured-swiper{border-radius:20px;overflow:hidden;background:#e2e8f0}.featured-swiper .swiper-slide img{width:100%;height:370px;object-fit:cover;display:block}.swiper-button-next,.swiper-button-prev{width:44px;height:44px;border-radius:999px;background:rgba(15,23,42,.72);color:#fff}.swiper-button-next:after,.swiper-button-prev:after{font-size:16px;font-weight:900}.swiper-pagination-bullet-active{background:var(--sp-primary)}
        .gallery-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.gallery-item{position:relative;border-radius:18px;overflow:hidden;background:#e2e8f0;min-height:130px;box-shadow:0 10px 24px rgba(15,23,42,.08)}.gallery-item img{width:100%;height:150px;object-fit:cover;display:block;transition:.25s}.gallery-item:hover img{transform:scale(1.04)}.gallery-item:after{content:"\f00e";font-family:"Font Awesome 6 Pro","Font Awesome 6 Free";font-weight:900;position:absolute;inset:0;background:rgba(15,23,42,.35);color:#fff;display:flex;align-items:center;justify-content:center;opacity:0;transition:.25s}.gallery-item:hover:after{opacity:1}
        .video-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}.video-card{border-radius:18px;overflow:hidden;background:#0f172a;box-shadow:0 12px 30px rgba(15,23,42,.12)}.video-card video,.video-card iframe{width:100%;height:230px;display:block;border:0}.video-card__caption{background:#fff;padding:12px 14px;font-size:14px;font-weight:800;color:#0f172a}
        .share-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.share-btn{width:42px;height:42px;border-radius:999px;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#fff;font-weight:900}.share-wa{background:#22c55e}.share-fb{background:#2563eb}.share-x{background:#111827}.share-copy{background:#64748b}
        .sp-footer{background:#252525;color:#fff;padding-top:52px}.sp-footer img{max-height:64px}.sp-footer p,.sp-footer li{color:#d1d5db}.sp-footer ul{padding:0;list-style:none}.sp-footer li{margin-bottom:10px}.sp-footer i{margin-right:10px;color:var(--sp-secondary)}.sp-footer-bottom{padding:18px 0;background:#1f1f1f;color:#aaa;font-size:13px;text-align:center}
        @media(max-width:991px){.sp-nav{display:none}.event-hero{min-height:520px}.event-page{margin-top:-60px}.gallery-grid{grid-template-columns:repeat(2,1fr)}.video-grid{grid-template-columns:1fr}.featured-swiper .swiper-slide img{height:270px}.event-sidebar{position:static}}
        @media(max-width:575px){.event-card__body{padding:20px}.event-quick__item{width:100%}.gallery-grid{grid-template-columns:1fr}.event-hero__content{padding-bottom:100px}}
    </style>
</head>
<body>
    <div class="sp-colorband"></div>

    <header class="sp-header">
        <div class="container-fluid px-4 px-lg-5">
            <div class="d-flex align-items-center justify-content-between">
                <div class="sp-logo py-2">
                    <a href="index_1.php">
                        <img src="<?= e($logoHeader) ?>" alt="Colegio San Pablo" onerror="this.src='assets/images/logo/logo.svg'">
                    </a>
                </div>
                <nav class="sp-nav">
                    <ul>
                        <?php foreach (($site['menus'] ?? []) as $i => $menu): ?>
                            <?php $idMenu = (int) $menu['id_menu']; $hasSubs = !empty($site['subs'][$idMenu]); ?>
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

    <?php if (!$validEvent): ?>
        <section class="py-5">
            <div class="container">
                <div class="event-card"><div class="event-card__body text-center py-5">
                    <h1 class="event-section-title">Evento no encontrado</h1>
                    <p class="event-text">No se encontró el evento solicitado o no se encuentra disponible.</p>
                    <a href="index_1.php#calendario-eventos-home" class="event-btn mt-3"><i class="fa-solid fa-arrow-left"></i> Volver a eventos</a>
                </div></div>
            </div>
        </section>
    <?php else: ?>
        <section class="event-hero">
            <div class="container">
                <div class="event-hero__content">
                    <span class="event-badge"><i class="fa-solid fa-tag"></i><?= e($category) ?></span>
                    <h1><?= e($title) ?></h1>
                    <?php if ($shortDescription !== ''): ?><p><?= e($shortDescription) ?></p><?php endif; ?>
                    <div class="event-quick">
                        <div class="event-quick__item"><i class="fa-regular fa-calendar"></i><span><?= e(sp_format_date($dateStart)) ?></span></div>
                        <div class="event-quick__item"><i class="fa-regular fa-clock"></i><span><?= e($hourText) ?></span></div>
                        <div class="event-quick__item"><i class="fa-solid fa-location-dot"></i><span><?= e($location ?: 'Por confirmar') ?></span></div>
                    </div>
                </div>
            </div>
        </section>

        <main class="event-page">
            <div class="container">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-8">
                        <div class="event-card mb-4">
                            <div class="event-card__body">
                                <h2 class="event-section-title">Sobre el evento</h2>
                                <?php if ($shortDescription !== ''): ?><p class="event-text fw-bold"><?= e($shortDescription) ?></p><?php endif; ?>
                                <?php if ($description !== ''): ?><div class="event-text"><?= nl2br(e($description)) ?></div><?php endif; ?>
                                <?php if ($shortDescription === '' && $description === ''): ?><p class="event-text text-muted">Pronto se agregará más información de este evento.</p><?php endif; ?>
                            </div>
                        </div>

                        <div class="event-card mb-4">
                            <div class="event-card__body">
                                <h2 class="event-section-title">Imágenes destacadas</h2>
                                <div class="swiper featured-swiper">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($images as $img): ?>
                                            <div class="swiper-slide">
                                                <img src="<?= e($img['src']) ?>" alt="<?= e($img['titulo'] ?? $title) ?>" onerror="this.src='<?= e($fallbackImage) ?>'">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($images) > 1): ?>
                                        <div class="swiper-button-prev"></div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-pagination"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="event-card mb-4">
                            <div class="event-card__body">
                                <h2 class="event-section-title">Galería del evento</h2>
                                <div class="gallery-grid">
                                    <?php foreach ($images as $img): ?>
                                        <a href="<?= e($img['src']) ?>" class="gallery-item image-popup">
                                            <img src="<?= e($img['src']) ?>" alt="<?= e($img['titulo'] ?? $title) ?>" onerror="this.src='<?= e($fallbackImage) ?>'">
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($videos)): ?>
                            <div class="event-card mb-4">
                                <div class="event-card__body">
                                    <h2 class="event-section-title">Videos del evento</h2>
                                    <div class="video-grid">
                                        <?php foreach ($videos as $video): ?>
                                            <div class="video-card">
                                                <?php if (($video['tipo'] ?? '') === 'youtube'): ?>
                                                    <iframe src="<?= e(sp_youtube_embed($video['src'])) ?>" allowfullscreen loading="lazy"></iframe>
                                                <?php else: ?>
                                                    <video controls preload="metadata">
                                                        <source src="<?= e($video['src']) ?>">
                                                    </video>
                                                <?php endif; ?>
                                                <div class="video-card__caption"><?= e($video['titulo'] ?? 'Video del evento') ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="event-card">
                            <div class="event-card__body">
                                <h2 class="event-section-title fs-5 mb-3">Compartir evento</h2>
                                <div class="share-row">
                                    <?php $currentUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? ''); ?>
                                    <a class="share-btn share-wa" target="_blank" rel="noopener" href="https://wa.me/?text=<?= urlencode($title . ' - ' . $currentUrl) ?>"><i class="fa-brands fa-whatsapp"></i></a>
                                    <a class="share-btn share-fb" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>"><i class="fa-brands fa-facebook-f"></i></a>
                                    <a class="share-btn share-x" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text=<?= urlencode($title) ?>&url=<?= urlencode($currentUrl) ?>"><i class="fa-brands fa-x-twitter"></i></a>
                                    <button type="button" class="share-btn share-copy border-0" onclick="navigator.clipboard.writeText(window.location.href)"><i class="fa-solid fa-link"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <aside class="event-card event-sidebar">
                            <div class="event-card__body">
                                <h2 class="event-section-title fs-4">Información del evento</h2>
                                <ul class="event-info-list">
                                    <li><i class="fa-regular fa-calendar"></i><div><strong>Fecha</strong><span><?= e(sp_format_short_date($dateStart)) ?><?= $dateEnd && $dateEnd !== $dateStart ? ' al ' . e(sp_format_short_date($dateEnd)) : '' ?></span></div></li>
                                    <li><i class="fa-regular fa-clock"></i><div><strong>Hora</strong><span><?= e($hourText) ?></span></div></li>
                                    <li><i class="fa-solid fa-location-dot"></i><div><strong>Ubicación</strong><span><?= e($location ?: 'Por confirmar') ?></span></div></li>
                                    <li><i class="fa-solid fa-tag"></i><div><strong>Categoría</strong><span><?= e($category) ?></span></div></li>
                                </ul>
                                <?php if ($attachment !== ''): ?>
                                    <a href="<?= e($attachment) ?>" class="event-btn event-btn-light w-100 mt-4" target="_blank" rel="noopener">Ver archivo adjunto <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                <?php endif; ?>
                                <a href="index_1.php#calendario-eventos-home" class="event-btn w-100 mt-3"><i class="fa-solid fa-arrow-left"></i> Volver a eventos</a>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </main>
    <?php endif; ?>

    <footer class="sp-footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-5">
                    <div class="logo-footer mb-3"><img src="<?= e($logoFooter) ?>" alt="Colegio San Pablo" onerror="this.src='assets/images/logo/logo.svg'"></div>
                    <p><?= e($site['institution']['nombre'] ?? 'Colegio San Pablo') ?></p>
                </div>
                <div class="col-lg-4">
                    <h5>Contacto</h5>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i><?= e($site['institution']['direccion'] ?? '') ?></li>
                        <li><i class="fas fa-phone"></i><?= e($site['institution']['telefono'] ?? '') ?></li>
                        <li><i class="fas fa-envelope"></i><?= e($site['institution']['email'] ?? '') ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="sp-footer-colorband mt-4"></div>
        <div class="sp-footer-bottom"><div class="container">© <?= date('Y') ?> Colegio San Pablo</div></div>
    </footer>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script src="assets/js/magnific-popup.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (document.querySelector('.featured-swiper')) {
                new Swiper('.featured-swiper', {
                    loop: <?= count($images) > 1 ? 'true' : 'false' ?>,
                    slidesPerView: 1,
                    spaceBetween: 0,
                    pagination: { el: '.swiper-pagination', clickable: true },
                    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                });
            }

            if (window.jQuery && jQuery.fn.magnificPopup) {
                jQuery('.image-popup').magnificPopup({
                    type: 'image',
                    gallery: { enabled: true },
                    mainClass: 'mfp-fade'
                });
            }
        });
    </script>
</body>
</html>
