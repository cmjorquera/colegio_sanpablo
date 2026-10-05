<?php
require_once __DIR__ . '/includes/cms_helpers.php';

$db = cms_get_connection();
$site = cms_get_site_data($db);

$idCalendario = (int) ($_GET['id_calendario'] ?? 0);
$fechaParam = trim((string) ($_GET['fecha'] ?? ''));
$holiday = cms_get_calendar_holiday($db, $idCalendario, $fechaParam);

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return cms_e($value);
    }
}

function feriado_detalle_fecha(?string $fecha): string
{
    if (!$fecha) {
        return 'Fecha no disponible';
    }
    $time = strtotime($fecha);
    return $time ? date('d/m/Y', $time) : $fecha;
}

function feriado_detalle_tipo_label(?string $tipo): string
{
    $labels = [
        'feriado' => 'Feriado nacional',
        'institucional' => 'Feriado institucional',
        'academico' => 'Día académico especial',
        'vacaciones' => 'Vacaciones',
        'suspension' => 'Suspensión de clases',
        'normal' => 'Día especial',
    ];
    $tipo = strtolower(trim((string) $tipo));
    return $labels[$tipo] ?? 'Día especial';
}

$holidayData = $holiday ? [
    'nombre' => (string) ($holiday['nombre_feriado'] ?? 'Feriado'),
    'fecha' => (string) ($holiday['fecha'] ?? ''),
    'dia_semana' => (string) ($holiday['nombre_dia_semana'] ?? ''),
    'descripcion' => trim((string) ($holiday['descripcion_feriado'] ?? '')),
    'tipo' => (string) ($holiday['tipo'] ?? 'feriado'),
    'color' => preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', (string) ($holiday['color'] ?? '')) ? $holiday['color'] : '#C6005A',
] : null;

if ($holidayData && $holidayData['descripcion'] === '') {
    $holidayData['descripcion'] = 'Este día corresponde a un feriado nacional registrado en el calendario institucional.';
}

$institution = $site['institution'] ?? [];
$favicon = $institution['favicon'] ?? 'assets/images/icono_ppt.png';
$primaryColor = $institution['color_primario'] ?? '#1f8f6b';
$secondaryColor = $institution['color_secundario'] ?? '#E9A629';
$pageTitle = $holidayData ? $holidayData['nombre'] : 'Feriado no encontrado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Colegio San Pablo</title>
    <link rel="shortcut icon" href="<?= e($favicon) ?>">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/meanmenu.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/colegiosanpablo.css">
    <style>
        :root {
            --holiday-primary: <?= e($primaryColor) ?>;
            --holiday-secondary: <?= e($secondaryColor) ?>;
            --holiday-color: <?= e($holidayData['color'] ?? '#C6005A') ?>;
        }
        .holiday-detail-page { background: #f5f7fb; }
        .holiday-hero {
            position: relative;
            min-height: 300px;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, var(--holiday-color), var(--holiday-primary));
            overflow: hidden;
        }
        .holiday-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(8, 20, 35, .18);
        }
        .holiday-hero__content {
            position: relative;
            z-index: 1;
            width: 100%;
            padding: 110px 0 56px;
            color: #fff;
        }
        .holiday-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 9px 16px;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.3);
            font-weight: 800;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 18px;
        }
        .holiday-hero h1 {
            color: #fff;
            font-size: clamp(30px, 4.5vw, 52px);
            line-height: 1.12;
            max-width: 820px;
            margin-bottom: 0;
        }
        .holiday-detail-wrap {
            max-width: 860px;
            margin: 0 auto;
            padding: 56px 12px 80px;
        }
        .holiday-card {
            background: #fff;
            border: 1px solid #e4ebf5;
            border-radius: 22px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .08);
            padding: 32px;
        }
        .holiday-info-list {
            display: grid;
            gap: 16px;
            margin: 0 0 26px;
            padding: 0;
            list-style: none;
        }
        .holiday-info-list li {
            display: grid;
            grid-template-columns: 44px 1fr;
            gap: 14px;
            align-items: start;
        }
        .holiday-info-list i {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: color-mix(in srgb, var(--holiday-color) 14%, #fff);
            color: var(--holiday-color);
        }
        .holiday-info-list strong {
            display: block;
            color: #12324a;
            margin-bottom: 2px;
        }
        .holiday-info-list span {
            color: #627188;
        }
        .holiday-body-text {
            color: #42526b;
            line-height: 1.8;
            font-size: 16px;
            padding-top: 6px;
            border-top: 1px solid #eef2f8;
        }
        .holiday-back-btn {
            margin-top: 28px;
            width: 100%;
            min-height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--holiday-primary), var(--holiday-secondary));
            color: #fff;
        }
        .holiday-back-btn:hover { color: #fff; }
        .holiday-missing {
            min-height: 260px;
            display: grid;
            place-items: center;
            text-align: center;
        }
        @media (max-width: 767px) {
            .holiday-hero { min-height: 240px; }
        }
    </style>
</head>
<body class="holiday-detail-page">
    <div class="sp-colorband"></div>

    <header class="sp-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between">
                <div class="sp-logo py-2">
                    <a href="index.php">
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

    <?php if ($holidayData): ?>
        <section class="holiday-hero">
            <div class="holiday-hero__content">
                <div class="container">
                    <span class="holiday-badge"><i class="fa-solid fa-calendar-day"></i>Feriado</span>
                    <h1><?= e($holidayData['nombre']) ?></h1>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <main class="holiday-detail-wrap">
        <?php if (!$holidayData): ?>
            <div class="holiday-card holiday-missing">
                <div>
                    <h2>Feriado no encontrado</h2>
                    <p class="text-muted mb-4">El feriado solicitado no existe o no está disponible en el calendario institucional.</p>
                    <a href="index.php#calendario-eventos-home" class="holiday-back-btn" style="max-width:280px;margin:0 auto;"><i class="fa-light fa-arrow-left-long"></i>Volver al calendario</a>
                </div>
            </div>
        <?php else: ?>
            <div class="holiday-card">
                <ul class="holiday-info-list">
                    <li>
                        <i class="fa-regular fa-calendar"></i>
                        <div><strong>Fecha</strong><span><?= e(feriado_detalle_fecha($holidayData['fecha'])) ?></span></div>
                    </li>
                    <?php if ($holidayData['dia_semana'] !== ''): ?>
                        <li>
                            <i class="fa-regular fa-clock"></i>
                            <div><strong>Día</strong><span><?= e($holidayData['dia_semana']) ?></span></div>
                        </li>
                    <?php endif; ?>
                    <li>
                        <i class="fa-solid fa-tag"></i>
                        <div><strong>Tipo</strong><span><?= e(feriado_detalle_tipo_label($holidayData['tipo'])) ?></span></div>
                    </li>
                </ul>
                <div class="holiday-body-text">
                    <?= nl2br(e($holidayData['descripcion'])) ?>
                </div>
                <a href="index.php#calendario-eventos-home" class="holiday-back-btn"><i class="fa-light fa-arrow-left-long"></i>Volver al calendario</a>
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
    <script src="assets/js/script.js"></script>
</body>
</html>
