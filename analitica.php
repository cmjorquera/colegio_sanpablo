<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_once __DIR__ . '/includes/funciones_auditoria.php';

$db  = cms_get_connection();
$site = cms_get_site_data($db);
$estadoTabla = auditoriaTablaLista($db);

// ── Helpers ───────────────────────────────────────────────────────────────────

function anl_parse_ua(string $ua): array
{
    $browser = 'Desconocido';
    $os      = 'Desconocido';

    if (str_contains($ua, 'Windows NT 10') || str_contains($ua, 'Windows NT 11')) { $os = 'Windows 10/11'; }
    elseif (str_contains($ua, 'Windows NT 6.3')) { $os = 'Windows 8.1'; }
    elseif (str_contains($ua, 'Windows NT 6.1')) { $os = 'Windows 7'; }
    elseif (str_contains($ua, 'Windows'))         { $os = 'Windows'; }
    elseif (str_contains($ua, 'iPhone'))          { $os = 'iOS (iPhone)'; }
    elseif (str_contains($ua, 'iPad'))            { $os = 'iOS (iPad)'; }
    elseif (str_contains($ua, 'Android'))         { $os = 'Android'; }
    elseif (str_contains($ua, 'Mac OS X'))        { $os = 'macOS'; }
    elseif (str_contains($ua, 'Linux'))           { $os = 'Linux'; }

    if (str_contains($ua, 'Edg/') || str_contains($ua, 'Edge/')) { $browser = 'Edge'; }
    elseif (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera')) { $browser = 'Opera'; }
    elseif (str_contains($ua, 'Chrome/') && !str_contains($ua, 'Chromium')) { $browser = 'Chrome'; }
    elseif (str_contains($ua, 'Firefox/'))  { $browser = 'Firefox'; }
    elseif (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome')) { $browser = 'Safari'; }
    elseif (str_contains($ua, 'MSIE') || str_contains($ua, 'Trident')) { $browser = 'IE'; }

    return ['browser' => $browser, 'os' => $os];
}

function anl_query(mysqli $db, string $sql, string $types = '', array $params = []): array
{
    if ($types === '') {
        $r = $db->query($sql);
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }
    $stmt = $db->prepare($sql);
    if (!$stmt) { return []; }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $r = $stmt->get_result();
    $rows = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function anl_scalar(mysqli $db, string $sql): mixed
{
    $r = $db->query($sql);
    if (!$r) { return null; }
    $row = $r->fetch_row();
    return $row ? $row[0] : null;
}

// ── Queries ───────────────────────────────────────────────────────────────────

$tablaOk = $estadoTabla['ok'];

$kpiLogins      = $tablaOk ? (int) anl_scalar($db, "SELECT COUNT(*) FROM auditoria_log WHERE accion = 'login'") : 0;
$kpiUsuarios7d  = $tablaOk ? (int) anl_scalar($db, "SELECT COUNT(DISTINCT id_usuario) FROM auditoria_log WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND id_usuario IS NOT NULL") : 0;
$kpiIpsUnicas   = $tablaOk ? (int) anl_scalar($db, "SELECT COUNT(DISTINCT ip_usuario) FROM auditoria_log WHERE ip_usuario IS NOT NULL AND ip_usuario != ''") : 0;
$kpiTotalLogs   = $tablaOk ? (int) anl_scalar($db, 'SELECT COUNT(*) FROM auditoria_log') : 0;

$ultimaAccion = [];
if ($tablaOk) {
    $rows = anl_query($db,
        'SELECT a.fecha_hora, a.modulo, a.accion, u.nombre, u.apellido, u.usuario
           FROM auditoria_log a
      LEFT JOIN usuario u ON u.id_usuario = a.id_usuario
          ORDER BY a.id_log DESC LIMIT 1'
    );
    $ultimaAccion = $rows[0] ?? [];
}

$historialAccesos = [];
if ($tablaOk) {
    $historialAccesos = anl_query($db,
        "SELECT a.fecha_hora, a.ip_usuario, a.user_agent, a.accion,
                u.nombre, u.apellido, u.usuario, u.email
           FROM auditoria_log a
      LEFT JOIN usuario u ON u.id_usuario = a.id_usuario
          WHERE a.accion IN ('login','logout')
          ORDER BY a.fecha_hora DESC
          LIMIT 500"
    );
}

$actividadUsuarios = [];
if ($tablaOk) {
    $actividadUsuarios = anl_query($db,
        'SELECT a.id_usuario,
                u.nombre, u.apellido, u.usuario, u.email,
                COUNT(*) AS total_acciones,
                MAX(a.fecha_hora) AS ultima_accion
           FROM auditoria_log a
      LEFT JOIN usuario u ON u.id_usuario = a.id_usuario
          WHERE a.id_usuario IS NOT NULL
          GROUP BY a.id_usuario, u.nombre, u.apellido, u.usuario, u.email
          ORDER BY total_acciones DESC'
    );
}

$ipsRegistradas = [];
if ($tablaOk) {
    $ipsRegistradas = anl_query($db,
        "SELECT ip_usuario, COUNT(*) AS total, MAX(fecha_hora) AS ultima
           FROM auditoria_log
          WHERE ip_usuario IS NOT NULL AND ip_usuario != ''
          GROUP BY ip_usuario
          ORDER BY total DESC
          LIMIT 30"
    );
}

$actividadPorModulo = [];
if ($tablaOk) {
    $actividadPorModulo = anl_query($db,
        "SELECT modulo, COUNT(*) AS total,
                COUNT(DISTINCT id_usuario) AS usuarios_distintos,
                MAX(fecha_hora) AS ultima_vez
           FROM auditoria_log
          WHERE modulo IS NOT NULL AND modulo != ''
          GROUP BY modulo
          ORDER BY total DESC"
    );
}

// Gráfico: actividad diaria últimos 30 días
$graficoDias   = [];
$graficoTotals = [];
if ($tablaOk) {
    $graficoRows = anl_query($db,
        "SELECT DATE(fecha_hora) AS dia, COUNT(*) AS total
           FROM auditoria_log
          WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
          GROUP BY DATE(fecha_hora)
          ORDER BY dia ASC"
    );
    // Rellenar días faltantes con 0
    $diaIndex = [];
    foreach ($graficoRows as $r) {
        $diaIndex[$r['dia']] = (int) $r['total'];
    }
    for ($i = 29; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $graficoDias[]   = date('d/m', strtotime($d));
        $graficoTotals[] = $diaIndex[$d] ?? 0;
    }
}

// ── Render ────────────────────────────────────────────────────────────────────

admin_render_layout_start([
    'title'                => 'Analítica de Accesos | Colegio San Pablo',
    'page_title'           => 'Analítica de Accesos',
    'breadcrumb'           => 'Panel institucional / Analítica',
    'active_panel'         => 'analitica',
    'institution_name'     => $site['institution']['nombre'] ?? 'Institución activa',
    'institution_short_name' => $site['institution']['nombre_corto'] ?? ($site['institution']['nombre'] ?? 'Institución'),
    'institution_logo'     => $site['institution']['logo_header'] ?? '',
    'color_primario'       => $site['institution']['color_primario'] ?? '',
    'color_secundario'     => $site['institution']['color_secundario'] ?? '',
    'color_terciario'      => $site['institution']['color_terciario'] ?? '',
    'color_cuaternario'    => $site['institution']['color_cuaternario'] ?? '',
    'admin_name'           => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'header_actions'       => '<a href="admin.php?panel=contenedores" class="btn btn-soft"><i class="bi bi-arrow-left me-2"></i>Volver al panel</a>',
    'extra_head'           => <<<'HTML'
    <style>
        .anl-kpi-grid { display:grid; grid-template-columns: repeat(4,1fr); gap:16px; margin-bottom:24px; }
        @media(max-width:991px) { .anl-kpi-grid { grid-template-columns: repeat(2,1fr); } }
        @media(max-width:575px) { .anl-kpi-grid { grid-template-columns: 1fr; } }
        .anl-kpi { background:#fff; border:1px solid var(--adm-border); border-radius:14px; padding:20px 22px; display:flex; align-items:center; gap:16px; }
        .anl-kpi-icon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; }
        .anl-kpi-icon.blue  { background:rgba(25,118,210,.12); color:#1976D2; }
        .anl-kpi-icon.green { background:rgba(38,166,91,.12);  color:#26a65b; }
        .anl-kpi-icon.amber { background:rgba(240,160,0,.12);  color:var(--adm-primary); }
        .anl-kpi-icon.purple{ background:rgba(103,58,183,.12); color:#673ab7; }
        .anl-kpi-body strong { display:block; font-size:1.65rem; font-weight:800; color:var(--adm-text); line-height:1; }
        .anl-kpi-body span   { display:block; font-size:.82rem; color:var(--adm-muted); margin-top:4px; }
        .anl-kpi-body small  { display:block; font-size:.75rem; color:var(--adm-muted); }
        .anl-chart-wrap { position:relative; height:280px; }
        .ua-badge { display:inline-flex; align-items:center; gap:4px; font-size:.78rem; background:var(--adm-surface); border:1px solid var(--adm-border); border-radius:6px; padding:2px 8px; }
    </style>
HTML,
]);
?>

<?php if (!$tablaOk): ?>
<div class="alert alert-warning">
    <strong>Analítica no disponible.</strong>
    La tabla <code>auditoria_log</code> no existe o le faltan columnas: <code><?= cms_e(implode(', ', $estadoTabla['missing'])) ?></code>.
</div>
<?php else: ?>

<!-- ── KPIs ─────────────────────────────────────────────────────────────── -->
<div class="anl-kpi-grid">
    <div class="anl-kpi">
        <div class="anl-kpi-icon blue"><i class="bi bi-box-arrow-in-right"></i></div>
        <div class="anl-kpi-body">
            <strong><?= number_format($kpiLogins) ?></strong>
            <span>Inicios de sesión</span>
            <small>Total registrados</small>
        </div>
    </div>
    <div class="anl-kpi">
        <div class="anl-kpi-icon green"><i class="bi bi-people"></i></div>
        <div class="anl-kpi-body">
            <strong><?= $kpiUsuarios7d ?></strong>
            <span>Usuarios activos</span>
            <small>Últimos 7 días</small>
        </div>
    </div>
    <div class="anl-kpi">
        <div class="anl-kpi-icon amber"><i class="bi bi-hdd-network"></i></div>
        <div class="anl-kpi-body">
            <strong><?= $kpiIpsUnicas ?></strong>
            <span>IPs únicas</span>
            <small>Desde el inicio del registro</small>
        </div>
    </div>
    <div class="anl-kpi">
        <div class="anl-kpi-icon purple"><i class="bi bi-journal-text"></i></div>
        <div class="anl-kpi-body">
            <strong><?= number_format($kpiTotalLogs) ?></strong>
            <span>Acciones registradas</span>
            <?php if ($ultimaAccion): ?>
            <small>Última: <?= cms_e(substr($ultimaAccion['fecha_hora'] ?? '', 0, 16)) ?></small>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Gráfico de actividad ──────────────────────────────────────────────── -->
<section class="section-card">
    <div class="section-head">
        <div>
            <h3>Actividad diaria</h3>
            <p class="text-muted mb-0">Número de acciones registradas en los últimos 30 días.</p>
        </div>
    </div>
    <div class="anl-chart-wrap">
        <canvas id="anlChart"></canvas>
    </div>
</section>

<!-- ── Historial de accesos ───────────────────────────────────────────────── -->
<section class="section-card">
    <div class="section-head">
        <div>
            <h3>Historial de accesos</h3>
            <p class="text-muted mb-0"><?= count($historialAccesos) ?> registros de login / logout.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-modern align-middle" id="anlAccesosTable">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>IP</th>
                    <th>Navegador</th>
                    <th>Sistema operativo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historialAccesos as $row):
                    $ua  = (string) ($row['user_agent'] ?? '');
                    $parsed = anl_parse_ua($ua);
                    $nombre = trim((string)($row['nombre'] ?? '') . ' ' . (string)($row['apellido'] ?? ''));
                    $nombre = $nombre !== '' ? $nombre : (string)(($row['usuario'] ?? '') ?: ($row['email'] ?? 'Sistema'));
                ?>
                <tr>
                    <td><?= cms_e($row['fecha_hora'] ?? '') ?></td>
                    <td><strong><?= cms_e($nombre) ?></strong></td>
                    <td>
                        <span class="badge-soft <?= ($row['accion'] ?? '') === 'login' ? 'success' : 'dark' ?>">
                            <?= cms_e($row['accion'] ?? '') ?>
                        </span>
                    </td>
                    <td><code><?= cms_e($row['ip_usuario'] ?? '') ?></code></td>
                    <td><span class="ua-badge"><i class="bi bi-browser-chrome"></i><?= cms_e($parsed['browser']) ?></span></td>
                    <td><span class="ua-badge"><i class="bi bi-pc-display"></i><?= cms_e($parsed['os']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- ── Actividad por usuario + IPs ───────────────────────────────────────── -->
<div class="row g-4">
    <div class="col-xl-6">
        <section class="section-card h-100">
            <div class="section-head">
                <div>
                    <h3>Actividad por usuario</h3>
                    <p class="text-muted mb-0">Total de acciones y última actividad de cada administrador.</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-modern align-middle" id="anlUsuariosTable">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Acciones</th>
                            <th>Última actividad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actividadUsuarios as $row):
                            $nombre = trim((string)($row['nombre'] ?? '') . ' ' . (string)($row['apellido'] ?? ''));
                            $nombre = $nombre !== '' ? $nombre : (string)(($row['usuario'] ?? '') ?: ($row['email'] ?? 'Usuario'));
                        ?>
                        <tr>
                            <td><strong><?= cms_e($nombre) ?></strong><br><small class="text-muted"><?= cms_e($row['usuario'] ?? '') ?></small></td>
                            <td><span class="badge rounded-pill" style="background:var(--adm-primary);color:#fff;"><?= (int)$row['total_acciones'] ?></span></td>
                            <td><?= cms_e(substr($row['ultima_accion'] ?? '', 0, 16)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="col-xl-6">
        <section class="section-card h-100">
            <div class="section-head">
                <div>
                    <h3>IPs registradas</h3>
                    <p class="text-muted mb-0">Direcciones IP que han generado actividad.</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-modern align-middle" id="anlIpsTable">
                    <thead>
                        <tr>
                            <th>IP</th>
                            <th>Acciones</th>
                            <th>Último acceso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ipsRegistradas as $row): ?>
                        <tr>
                            <td><code><?= cms_e($row['ip_usuario'] ?? '') ?></code></td>
                            <td><span class="badge rounded-pill" style="background:var(--adm-secondary);color:#fff;"><?= (int)$row['total'] ?></span></td>
                            <td><?= cms_e(substr($row['ultima'] ?? '', 0, 16)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<!-- ── Acciones por módulo ────────────────────────────────────────────────── -->
<section class="section-card" style="margin-top:24px;">
    <div class="section-head">
        <div>
            <h3>Acciones por módulo</h3>
            <p class="text-muted mb-0">Resumen de uso de cada sección del CMS.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-modern align-middle" id="anlModulosTable">
            <thead>
                <tr>
                    <th>Módulo</th>
                    <th>Total acciones</th>
                    <th>Usuarios distintos</th>
                    <th>Última vez</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($actividadPorModulo as $row): ?>
                <tr>
                    <td><strong><?= cms_e($row['modulo'] ?? '') ?></strong></td>
                    <td><?= number_format((int)$row['total']) ?></td>
                    <td><?= (int)$row['usuarios_distintos'] ?></td>
                    <td><?= cms_e(substr($row['ultima_vez'] ?? '', 0, 16)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php endif; ?>

<?php
$chartDias   = json_encode($graficoDias,   JSON_UNESCAPED_UNICODE);
$chartTotals = json_encode($graficoTotals, JSON_UNESCAPED_UNICODE);

admin_render_layout_end([
    'extra_scripts' => <<<HTML
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
    $(function () {
        var dtCfg = {
            pageLength: 25,
            order: [[0, 'desc']],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
        };
        if ($('#anlAccesosTable').length) { $('#anlAccesosTable').DataTable(dtCfg); }
        if ($('#anlUsuariosTable').length) { $('#anlUsuariosTable').DataTable({ pageLength: 10, order: [[1,'desc']], language: dtCfg.language }); }
        if ($('#anlIpsTable').length)      { $('#anlIpsTable').DataTable({ pageLength: 10, order: [[1,'desc']], language: dtCfg.language }); }
        if ($('#anlModulosTable').length)  { $('#anlModulosTable').DataTable({ pageLength: 15, order: [[1,'desc']], language: dtCfg.language }); }

        var ctxEl = document.getElementById('anlChart');
        if (ctxEl && typeof Chart !== 'undefined') {
            var primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--adm-primary').trim() || '#F0A000';
            var secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--adm-secondary').trim() || '#EF6C00';
            new Chart(ctxEl, {
                type: 'line',
                data: {
                    labels: $chartDias,
                    datasets: [{
                        label: 'Acciones',
                        data: $chartTotals,
                        borderColor: primaryColor,
                        backgroundColor: 'rgba(240,160,0,.10)',
                        borderWidth: 2.5,
                        pointBackgroundColor: primaryColor,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: { grid: { color: '#f0f0f0' }, ticks: { font: { size: 11 } } },
                        y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { precision: 0, font: { size: 11 } } }
                    }
                }
            });
        }
    });
    </script>
HTML,
]);
?>
