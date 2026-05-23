<?php
require_once __DIR__ . '/includes/cms_helpers.php';

$db = cms_get_connection();
$site = cms_get_site_data($db);
$idEvento = (int) ($_GET['id_evento'] ?? 0);
$evento = $idEvento > 0 ? cms_get_event($db, $idEvento) : null;

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return cms_e($value);
    }
}

$title = $evento['titulo'] ?? 'Evento no encontrado';
$image = $evento['imagen'] ?? ($evento['imagen_principal'] ?? 'assets/images/event/event-details.jpg');
$category = $evento['categoria'] ?? 'Institucional';
$dateStart = $evento['fecha_inicio'] ?? '';
$dateEnd = $evento['fecha_termino'] ?? $dateStart;
$timeStart = substr((string) ($evento['hora_inicio'] ?? ''), 0, 5);
$timeEnd = substr((string) ($evento['hora_termino'] ?? ''), 0, 5);
$location = $evento['ubicacion'] ?? '';
$shortDescription = $evento['descripcion_corta'] ?? '';
$description = $evento['descripcion'] ?? '';
$attachment = $evento['archivo_adjunto'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | Colegio San Pablo</title>
    <link rel="shortcut icon" href="assets/images/icono_ppt.png">
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

    <header class="sp-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between">
                <div class="sp-logo py-2">
                    <a href="index_1.php">
                        <img src="<?= e($site['institution']['logo_header'] ?? 'assets/images/logo/logo.svg') ?>" alt="Colegio San Pablo" onerror="this.src='assets/images/logo/logo.svg'">
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

    <section class="banner-inner-area sub-bg bg-image" data-background="assets/images/bg/banner-inner-bg.png" style="background-image:url('assets/images/bg/banner-inner-bg.png');">
        <div class="container">
            <div class="banner-inner__content">
                <h1><?= e($evento ? $title : 'Evento no encontrado') ?></h1>
                <ul>
                    <li><a href="index_1.php">Inicio</a></li>
                    <li><i class="fa-regular fa-angle-right"></i></li>
                    <li>Detalle del evento</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="event-details-area pt-120 pb-120">
        <div class="container">
            <?php if (!$evento): ?>
                <div class="alert alert-warning">No se encontró el evento solicitado.</div>
                <a href="index_1.php#calendario-eventos-home" class="btn-ver-mas">Volver al calendario</a>
            <?php else: ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="event-details__item-left">
                            <div class="image">
                                <img src="<?= e($image) ?>" alt="<?= e($title) ?>">
                                <span class="tag"><?= e($category) ?></span>
                            </div>
                            <h3 class="fs-30 mt-40 mb-30"><?= e($title) ?></h3>
                            <?php if ($shortDescription !== ''): ?><p class="mb-20"><?= e($shortDescription) ?></p><?php endif; ?>
                            <?php if ($description !== ''): ?><p><?= nl2br(e($description)) ?></p><?php endif; ?>

                            <h3 class="fs-30 mb-20 mt-30">Galería del evento</h3>
                            <p class="text-muted">Galería del evento - pendiente de implementar.</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="event-details__item-right">
                            <div class="item mb-30">
                                <h3>Datos del evento</h3>
                                <ul>
                                    <li><i class="fa-regular fa-calendar me-2 text-success"></i><p><strong>Fecha:</strong> <?= e($dateStart) ?><?= $dateEnd && $dateEnd !== $dateStart ? ' al ' . e($dateEnd) : '' ?></p></li>
                                    <li><i class="fa-regular fa-clock me-2 text-success"></i><p><strong>Hora:</strong> <?= e($timeStart ?: 'Por confirmar') ?><?= $timeEnd ? ' - ' . e($timeEnd) : '' ?></p></li>
                                    <li><i class="fa-solid fa-location-dot me-2 text-success"></i><p><strong>Ubicación:</strong> <?= e($location ?: 'Por confirmar') ?></p></li>
                                    <li><i class="fa-solid fa-tag me-2 text-success"></i><p><strong>Categoría:</strong> <?= e($category) ?></p></li>
                                </ul>
                                <?php if ($attachment !== ''): ?>
                                    <a href="<?= e($attachment) ?>" class="btn-one-light d-block text-center mb-3" target="_blank" rel="noopener">Ver archivo adjunto<i class="fa-light fa-arrow-right-long"></i></a>
                                <?php endif; ?>
                                <a href="index_1.php#calendario-eventos-home" class="btn-one">Volver a eventos<i class="fa-light fa-arrow-right-long"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="sp-footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-5">
                    <div class="logo-footer"><img src="<?= e($site['institution']['logo_footer'] ?? ($site['institution']['logo_header'] ?? 'assets/images/logo/logo.svg')) ?>" alt="Colegio San Pablo"></div>
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
