<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index_1.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_once __DIR__ . '/includes/funciones_auditoria.php';

$db = cms_get_connection();
$institutionId = cms_get_institution_id($db);
cms_sync_sections($db, $institutionId);

$panel = $_GET['panel'] ?? 'contenedores';
$sectionId = isset($_GET['section']) ? (int) $_GET['section'] : 0;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['accion'] ?? '';
        $sectionId = (int) ($_POST['id_seccion'] ?? $sectionId);
        $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

        if ($action === 'toggle_seccion' && $sectionId > 0) {
            $datosAntes = obtenerRegistroAuditoria($db, 'seccion', 'id_seccion', $sectionId);
            cms_toggle_section_visibility($db, $sectionId);
            $datosDespues = obtenerRegistroAuditoria($db, 'seccion', 'id_seccion', $sectionId);
            registrarAuditoria($db, 'Contenedores del sitio', 'seccion', $sectionId, ($datosDespues['visible'] ?? '') === 'si' ? 'activar' : 'ocultar', 'Se cambió la visibilidad de un contenedor', $datosAntes, $datosDespues);
            if ($isAjax) {
                $updatedSection = cms_get_section($db, $sectionId);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'ok' => true,
                    'visible' => $updatedSection['visible'] ?? 'no',
                    'label' => ($updatedSection['visible'] ?? 'no') === 'si' ? 'Activo' : 'Oculto',
                ]);
                exit;
            }
            cms_set_flash('success', 'La visibilidad del contenedor fue actualizada.');
            cms_redirect('admin_1.php?panel=contenedores');
        }

        if ($action === 'guardar_menu') {
            $idMenuAudit = (int) ($_POST['id_menu'] ?? 0);
            $datosAntes = $idMenuAudit > 0 ? obtenerRegistroAuditoria($db, 'menus', 'id_menu', $idMenuAudit) : null;
            $savedMenuId = cms_save_menu($db, $_POST);
            $datosDespues = obtenerRegistroAuditoria($db, 'menus', 'id_menu', $savedMenuId);
            registrarAuditoria($db, 'Menú principal', 'menus', $savedMenuId, $idMenuAudit > 0 ? 'editar' : 'crear', $idMenuAudit > 0 ? 'Se modificó un menú principal' : 'Se creó un menú principal', $datosAntes, $datosDespues);
            cms_set_flash('success', 'El menú fue guardado correctamente.');
            cms_redirect('admin_1.php?panel=menus');
        }

        if ($action === 'toggle_menu') {
            $idMenuAudit = (int) ($_POST['id_menu'] ?? 0);
            $datosAntes = obtenerRegistroAuditoria($db, 'menus', 'id_menu', $idMenuAudit);
            cms_toggle_menu($db, $idMenuAudit);
            $datosDespues = obtenerRegistroAuditoria($db, 'menus', 'id_menu', $idMenuAudit);
            $accionAudit = (int) ($datosDespues['estado'] ?? 0) === 1 ? 'activar' : 'desactivar';
            registrarAuditoria($db, 'Menú principal', 'menus', $idMenuAudit, $accionAudit, 'Se cambió el estado de un menú principal', $datosAntes, $datosDespues);
            cms_set_flash('success', 'El estado del menú fue actualizado.');
            cms_redirect('admin_1.php?panel=menus');
        }

        if ($action === 'guardar_submenu') {
            $idSubMenuAudit = (int) ($_POST['id_sub_menu'] ?? 0);
            $datosAntes = $idSubMenuAudit > 0 ? obtenerRegistroAuditoria($db, 'sub_menus', 'id_sub_menu', $idSubMenuAudit) : null;
            $savedSubMenuId = cms_save_submenu($db, $_POST);
            $datosDespues = obtenerRegistroAuditoria($db, 'sub_menus', 'id_sub_menu', $savedSubMenuId);
            registrarAuditoria($db, 'Submenús', 'sub_menus', $savedSubMenuId, $idSubMenuAudit > 0 ? 'editar' : 'crear', $idSubMenuAudit > 0 ? 'Se modificó un submenú' : 'Se creó un submenú', $datosAntes, $datosDespues);
            cms_set_flash('success', 'El submenú fue guardado correctamente.');
            cms_redirect('admin_1.php?panel=submenus');
        }

        if ($action === 'toggle_submenu') {
            $idSubMenuAudit = (int) ($_POST['id_sub_menu'] ?? 0);
            $datosAntes = obtenerRegistroAuditoria($db, 'sub_menus', 'id_sub_menu', $idSubMenuAudit);
            cms_toggle_submenu($db, $idSubMenuAudit);
            $datosDespues = obtenerRegistroAuditoria($db, 'sub_menus', 'id_sub_menu', $idSubMenuAudit);
            $accionAudit = (int) ($datosDespues['estado'] ?? 0) === 1 ? 'activar' : 'desactivar';
            registrarAuditoria($db, 'Submenús', 'sub_menus', $idSubMenuAudit, $accionAudit, 'Se cambió el estado de un submenú', $datosAntes, $datosDespues);
            cms_set_flash('success', 'El estado del submenú fue actualizado.');
            cms_redirect('admin_1.php?panel=submenus');
        }

        if ($action === 'guardar_institucion') {
            cms_save_institution($db, $institutionId, $_POST);
            cms_set_flash('success', 'La configuración institucional fue actualizada.');
            cms_redirect('admin_1.php?panel=configuracion');
        }
    }
} catch (Throwable $e) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'message' => $e->getMessage(),
        ]);
        exit;
    }
    cms_set_flash('danger', $e->getMessage());
    cms_redirect('admin_1.php?panel=' . urlencode($panel));
}

$flash = cms_get_flash();
$site = cms_get_site_data($db);
$sections = cms_list_sections_admin($db, $institutionId);
$institution = $site['institution'];
$menus = cms_list_menus($db);
$submenus = cms_list_submenus($db);
$editingMenu = isset($_GET['menu']) ? cms_get_menu($db, (int) $_GET['menu']) : null;
$editingSubmenu = isset($_GET['submenu']) ? cms_get_submenu($db, (int) $_GET['submenu']) : null;
$visibleSections = array_values(array_filter($sections, static fn($section) => ($section['visible'] ?? '') === 'si'));
$eventsSectionId = 0;
foreach ($sections as $sectionLookup) {
    if (($sectionLookup['nombre_interno'] ?? '') === 'calendario_eventos_home') {
        $eventsSectionId = (int) ($sectionLookup['id_seccion'] ?? 0);
        break;
    }
}
$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');
$eventsThisMonth = cms_table_exists($db, 'eventos') ? cms_list_public_events($db, $monthStart, $monthEnd, 80) : [];
$upcomingEvents = cms_table_exists($db, 'eventos') ? cms_list_public_events($db, $today, date('Y-m-d', strtotime('+90 days')), 3) : [];
$recentAudit = [];
if (cms_table_exists($db, 'auditoria_log')) {
    $auditResult = $db->query('SELECT modulo, accion, descripcion, fecha_hora FROM auditoria_log ORDER BY fecha_hora DESC LIMIT 5');
    $recentAudit = $auditResult ? $auditResult->fetch_all(MYSQLI_ASSOC) : [];
}

$pageTitles = [
    'dashboard' => ['title' => 'Dashboard', 'crumb' => 'Panel general del CMS'],
    'contenedores' => ['title' => 'Contenedores del sitio', 'crumb' => 'Listado general de bloques visuales'],
    'menus' => ['title' => 'Menú principal', 'crumb' => 'Administración de menus'],
    'submenus' => ['title' => 'Submenús', 'crumb' => 'Administración de sub_menus'],
    'configuracion' => ['title' => 'Configuración institucional', 'crumb' => 'Datos globales del sitio'],
];
$pageMeta = $pageTitles[$panel] ?? $pageTitles['contenedores'];

admin_render_layout_start([
    'title' => 'Panel CMS | Colegio San Pablo',
    'page_title' => $pageMeta['title'],
    'breadcrumb' => $pageMeta['crumb'],
    'active_panel' => $panel,
    'institution_name' => $institution['nombre'] ?? 'Institución activa',
    'institution_short_name' => $institution['nombre_corto'] ?? ($institution['nombre'] ?? 'Institución'),
    'institution_logo' => $institution['logo_header'] ?? '',
    'admin_name' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'header_actions' => '<a href="index_1.php" target="_blank" class="btn btn-soft"><i class="bi bi-eye me-2"></i>Ver sitio</a>',
    'extra_head' => <<<'HTML'
    <style>
        .dashboard-grid { display: grid; gap: 18px; }
        .dashboard-metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
        .dash-metric {
            min-height: 118px;
            border-radius: 22px;
            padding: 18px;
            background: #fff;
            border: 1px solid rgba(219, 228, 239, .9);
            box-shadow: var(--sp-shadow);
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr);
            gap: 14px;
            align-items: center;
        }
        .dash-metric-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 1.6rem;
            box-shadow: 0 14px 28px rgba(15, 23, 42, .16);
        }
        .dash-metric strong { display: block; color: var(--sp-dark); font-size: 2rem; line-height: 1; }
        .dash-metric span { display: block; color: var(--sp-dark); font-weight: 800; margin-top: 5px; }
        .dash-metric small { display: block; color: var(--sp-muted); margin-top: 4px; }
        .dash-green { background: linear-gradient(135deg,#22a568,#54d886); }
        .dash-blue { background: linear-gradient(135deg,#126de1,#53a2ff); }
        .dash-gold { background: linear-gradient(135deg,#f0a315,#ffc24a); }
        .dash-purple { background: linear-gradient(135deg,#7438df,#a95cff); }
        .dash-rose { background: linear-gradient(135deg,#f43f74,#ff6b91); }
        .dash-panel {
            background: #fff;
            border: 1px solid rgba(219, 228, 239, .9);
            border-radius: 22px;
            box-shadow: var(--sp-shadow);
            padding: 18px;
            height: 100%;
        }
        .dash-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .dash-panel-title { display: flex; align-items: center; gap: 10px; color: var(--sp-dark); font-size: 1.05rem; font-weight: 800; margin: 0; }
        .dash-panel-title i { color: var(--sp-primary); }
        .dash-event-list, .dash-list { display: grid; gap: 10px; }
        .dash-event {
            display: grid;
            grid-template-columns: 58px minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
            padding: 10px;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            color: inherit;
        }
        .dash-event-date {
            height: 58px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            color: #fff;
            background: linear-gradient(135deg,#f43f74,#ff7a9c);
            font-weight: 900;
            line-height: 1;
        }
        .dash-event-date small { display: block; font-size: .68rem; margin-top: 4px; }
        .dash-event strong, .dash-list strong { display: block; color: var(--sp-dark); font-weight: 800; }
        .dash-event span, .dash-list span { display: block; color: var(--sp-muted); font-size: .84rem; margin-top: 3px; }
        .dash-actions { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
        .dash-action {
            min-height: 86px;
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 8px;
            color: var(--sp-dark);
            background: #f7fbff;
            border: 1px solid #edf2f7;
            font-weight: 800;
            text-align: center;
        }
        .dash-action i {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(135deg,var(--sp-primary),#27b785);
            font-size: 1.25rem;
        }
        .dash-home-row {
            display: grid;
            grid-template-columns: 24px minmax(0, 1fr) auto auto;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #edf2f7;
        }
        .dash-home-row:last-child { border-bottom: 0; }
        .dash-mini-calendar { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; text-align: center; }
        .dash-mini-calendar span { color: var(--sp-muted); font-size: .78rem; font-weight: 800; }
        .dash-mini-calendar b {
            min-height: 32px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            color: var(--sp-dark);
            font-size: .86rem;
        }
        .dash-mini-calendar b.has-event { background: var(--sp-primary-soft); color: var(--sp-primary); }
        .dash-mini-calendar b.today { background: linear-gradient(135deg,var(--sp-primary),#27b785); color: #fff; }
        @media (max-width: 1399px) { .dashboard-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .dashboard-metrics, .dash-actions { grid-template-columns: 1fr; }
            .dash-metric { grid-template-columns: 52px minmax(0, 1fr); }
            .dash-metric-icon { width: 52px; height: 52px; border-radius: 15px; }
        }
    </style>
HTML,
]);
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= cms_e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= cms_e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($panel === 'dashboard'): ?>
    <?php
    $eventsByDay = [];
    foreach ($eventsThisMonth as $event) {
        $day = (int) date('j', strtotime((string) ($event['fecha_inicio'] ?? $today)));
        $eventsByDay[$day] = true;
    }
    $firstWeekday = (int) date('N', strtotime($monthStart));
    $daysInMonth = (int) date('t');
    $monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    ?>
    <div class="dashboard-grid">
        <div class="dashboard-metrics">
            <div class="dash-metric"><div class="dash-metric-icon dash-green"><i class="bi bi-layout-text-window-reverse"></i></div><div><strong><?= count($sections) ?></strong><span>Contenedores</span><small><?= count($visibleSections) ?> visibles</small></div></div>
            <div class="dash-metric"><div class="dash-metric-icon dash-blue"><i class="bi bi-list-nested"></i></div><div><strong><?= count($menus) ?></strong><span>Menús</span><small>Activos en navegación</small></div></div>
            <div class="dash-metric"><div class="dash-metric-icon dash-gold"><i class="bi bi-diagram-3"></i></div><div><strong><?= count($submenus) ?></strong><span>Submenús</span><small>Enlaces secundarios</small></div></div>
            <div class="dash-metric"><div class="dash-metric-icon dash-purple"><i class="bi bi-calendar-event"></i></div><div><strong><?= count($eventsThisMonth) ?></strong><span>Eventos este mes</span><small>Publicados y visibles</small></div></div>
        </div>

        <div class="row g-4">
            <div class="col-xl-5">
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <h3 class="dash-panel-title"><i class="bi bi-calendar2-week"></i>Próximos eventos</h3>
                        <a class="btn btn-sm btn-soft" href="editar_contenedor.php?id=<?= $eventsSectionId ?>&tab=items">Ver todos</a>
                    </div>
                    <div class="dash-event-list">
                        <?php if ($upcomingEvents): ?>
                            <?php foreach ($upcomingEvents as $event): ?>
                                <?php $eventDate = strtotime((string) ($event['fecha_inicio'] ?? $today)); ?>
                                <a class="dash-event" href="evento_detalle.php?id_evento=<?= (int) ($event['id_evento'] ?? 0) ?>" target="_blank">
                                    <span class="dash-event-date"><?= date('d', $eventDate) ?><small><?= strtoupper(substr($monthNames[(int) date('n', $eventDate) - 1], 0, 3)) ?></small></span>
                                    <span><strong><?= cms_e($event['titulo'] ?? '') ?></strong><span><?= cms_e(($event['categoria'] ?? 'Institucional') . ' · ' . trim((string) ($event['hora_inicio'] ?? ''))) ?></span></span>
                                    <i class="bi bi-arrow-up-right"></i>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted">No hay eventos próximos publicados.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <h3 class="dash-panel-title"><i class="bi bi-calendar3"></i>Calendario</h3>
                        <a class="btn btn-sm btn-soft" href="index_1.php#calendario-eventos-home" target="_blank"><i class="bi bi-box-arrow-up-right"></i></a>
                    </div>
                    <div class="text-center fw-bold mb-3"><?= $monthNames[(int) date('n') - 1] ?> <?= date('Y') ?></div>
                    <div class="dash-mini-calendar">
                        <?php foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dayName): ?><span><?= $dayName ?></span><?php endforeach; ?>
                        <?php for ($blank = 1; $blank < $firstWeekday; $blank++): ?><b></b><?php endfor; ?>
                        <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                            <b class="<?= isset($eventsByDay[$day]) ? 'has-event' : '' ?> <?= $day === (int) date('j') ? 'today' : '' ?>"><?= $day ?></b>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <h3 class="dash-panel-title"><i class="bi bi-lightning-charge"></i>Accesos rápidos</h3>
                    </div>
                    <div class="dash-actions">
                        <a class="dash-action" href="editar_contenedor.php?id=<?= $eventsSectionId ?>&tab=items&modal=evento"><i class="bi bi-calendar-plus"></i>Evento</a>
                        <a class="dash-action" href="admin_1.php?panel=menus"><i class="bi bi-list-nested"></i>Menús</a>
                        <a class="dash-action" href="admin_1.php?panel=configuracion"><i class="bi bi-sliders"></i>Ajustes</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-6">
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <h3 class="dash-panel-title"><i class="bi bi-house-check"></i>Contenedores del home</h3>
                        <a class="btn btn-sm btn-soft" href="admin_1.php?panel=contenedores">Ver todos</a>
                    </div>
                    <?php foreach (array_slice($sections, 0, 7) as $section): ?>
                        <div class="dash-home-row">
                            <i class="bi bi-grip-vertical text-muted"></i>
                            <strong><?= cms_e($section['titulo_admin'] ?? '') ?></strong>
                            <span class="badge-soft <?= ($section['visible'] ?? '') === 'si' ? 'success' : 'warning' ?>"><?= ($section['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?></span>
                            <a class="btn btn-sm btn-outline-secondary" href="editar_contenedor.php?id=<?= (int) $section['id_seccion'] ?>"><i class="bi bi-pencil"></i></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <h3 class="dash-panel-title"><i class="bi bi-activity"></i>Actividad reciente</h3>
                    </div>
                    <div class="dash-list">
                        <?php if ($recentAudit): ?>
                            <?php foreach ($recentAudit as $audit): ?>
                                <div><strong><?= cms_e($audit['modulo'] ?? 'CMS') ?></strong><span><?= cms_e(($audit['accion'] ?? '') . ' · ' . ($audit['descripcion'] ?? '')) ?></span></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted">Sin actividad reciente disponible.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <h3 class="dash-panel-title"><i class="bi bi-shield-check"></i>Estado del sitio</h3>
                    </div>
                    <div class="dash-list">
                        <div><strong>Sitio público</strong><span>Online</span></div>
                        <div><strong>Contenedores activos</strong><span><?= count($visibleSections) ?> / <?= count($sections) ?></span></div>
                        <div><strong>Eventos este mes</strong><span><?= count($eventsThisMonth) ?></span></div>
                        <div><strong>Usuario administrador</strong><span><?= cms_e($_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador') ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($panel === 'contenedores'): ?>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Contenedores del sitio</h3>
                <p>Panel general de bloques. Cada contenedor se edita en su propia página.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle" id="contenedoresTable">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Contenedor</th>
                        <th>Observación</th>
                        <th>Tipo</th>
                        <th>Visible</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sections as $section): ?>
                        <tr>
                            <td><?= (int) $section['orden'] ?></td>
                            <td>
                                <strong><?= cms_e($section['titulo_admin']) ?></strong>
                            </td>
                            <td><div class="text-muted" style="min-width:280px; white-space:normal;"><?= cms_e($section['observacion'] ?? '') ?></div></td>
                            <td><span class="badge-soft dark"><?= cms_e($section['tipo_seccion']) ?></span></td>
                            <td>
                                <form method="post" class="m-0 js-toggle-seccion-form">
                                    <input type="hidden" name="accion" value="toggle_seccion">
                                    <input type="hidden" name="id_seccion" value="<?= (int) $section['id_seccion'] ?>">
                                    <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                        <input class="form-check-input js-toggle-seccion" type="checkbox" role="switch" <?= ($section['visible'] ?? '') === 'si' ? 'checked' : '' ?>>
                                        <label class="form-check-label js-toggle-label"><?= ($section['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?></label>
                                    </div>
                                </form>
                            </td>
                            <td class="cell-actions">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-soft js-preview-btn" data-preview-title="<?= cms_e($section['titulo_admin']) ?>" data-preview-url="preview_contenedor_1.php?id=<?= (int) $section['id_seccion'] ?>&embed=1">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a class="btn btn-admin-action" href="editar_contenedor.php?id=<?= (int) $section['id_seccion'] ?>&modo=editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php elseif ($panel === 'menus'): ?>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Menú principal</h3>
                <p>Gestionado desde <code>menus</code> sin tocar su lógica actual.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="table-responsive">
                    <table class="table table-modern align-middle" id="menusTable">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Nombre</th>
                                <th>URL</th>
                                <th>Ícono</th>
                                <th>Activo</th>
                                <th>Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menus as $menu): ?>
                                <tr>
                                    <td><?= (int) $menu['orden'] ?></td>
                                    <td><strong><?= cms_e($menu['nombre']) ?></strong></td>
                                    <td><code><?= cms_e($menu['url']) ?></code></td>
                                    <td><?= cms_e($menu['icono']) ?></td>
                                    <td>
                                        <form method="post" class="m-0">
                                            <input type="hidden" name="accion" value="toggle_menu">
                                            <input type="hidden" name="id_menu" value="<?= (int) $menu['id_menu'] ?>">
                                            <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                                <input class="form-check-input" type="checkbox" role="switch" <?= (int) $menu['estado'] === 1 ? 'checked' : '' ?> onchange="this.form.submit()">
                                                <label class="form-check-label"><?= (int) $menu['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                                            </div>
                                        </form>
                                    </td>
                                    <td><a class="btn btn-sm btn-outline-secondary" href="admin_1.php?panel=menus&menu=<?= (int) $menu['id_menu'] ?>">Editar</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="section-card mb-0">
                    <h3><?= $editingMenu ? 'Editar menú' : 'Nuevo menú' ?></h3>
                    <div class="text-muted mb-3">Edición rápida del menú principal.</div>
                    <form method="post">
                        <input type="hidden" name="accion" value="guardar_menu">
                        <input type="hidden" name="id_menu" value="<?= (int) ($editingMenu['id_menu'] ?? 0) ?>">
                        <div class="mb-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" value="<?= cms_e($editingMenu['nombre'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">URL</label><input class="form-control" name="url" value="<?= cms_e($editingMenu['url'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">Ícono</label><input class="form-control" name="icono" value="<?= cms_e($editingMenu['icono'] ?? '') ?>"></div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Orden</label><input class="form-control" type="number" name="orden" value="<?= (int) ($editingMenu['orden'] ?? count($menus) + 1) ?>"></div>
                            <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="estado" <?= !isset($editingMenu['estado']) || (int) $editingMenu['estado'] === 1 ? 'checked' : '' ?>><label class="form-check-label">Activo</label></div></div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button class="btn btn-premium flex-fill" type="submit">Guardar</button>
                            <?php if ($editingMenu): ?><a class="btn btn-soft" href="admin_1.php?panel=menus">Cancelar</a><?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php elseif ($panel === 'submenus'): ?>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Submenús</h3>
                <p>Gestionados desde <code>sub_menus</code> sin romper navegación existente.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="table-responsive">
                    <table class="table table-modern align-middle" id="submenusTable">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Nombre</th>
                                <th>Menú padre</th>
                                <th>URL</th>
                                <th>Activo</th>
                                <th>Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submenus as $submenu): ?>
                                <tr>
                                    <td><?= (int) $submenu['orden'] ?></td>
                                    <td><strong><?= cms_e($submenu['nombre']) ?></strong></td>
                                    <td><?= cms_e($submenu['menu_padre']) ?></td>
                                    <td><code><?= cms_e($submenu['url']) ?></code></td>
                                    <td>
                                        <form method="post" class="m-0">
                                            <input type="hidden" name="accion" value="toggle_submenu">
                                            <input type="hidden" name="id_sub_menu" value="<?= (int) $submenu['id_sub_menu'] ?>">
                                            <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                                <input class="form-check-input" type="checkbox" role="switch" <?= (int) $submenu['estado'] === 1 ? 'checked' : '' ?> onchange="this.form.submit()">
                                                <label class="form-check-label"><?= (int) $submenu['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                                            </div>
                                        </form>
                                    </td>
                                    <td><a class="btn btn-sm btn-outline-secondary" href="admin_1.php?panel=submenus&submenu=<?= (int) $submenu['id_sub_menu'] ?>">Editar</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="section-card mb-0">
                    <h3><?= $editingSubmenu ? 'Editar submenú' : 'Nuevo submenú' ?></h3>
                    <div class="text-muted mb-3">Edición rápida de submenús.</div>
                    <form method="post">
                        <input type="hidden" name="accion" value="guardar_submenu">
                        <input type="hidden" name="id_sub_menu" value="<?= (int) ($editingSubmenu['id_sub_menu'] ?? 0) ?>">
                        <div class="mb-3">
                            <label class="form-label">Menú padre</label>
                            <select class="form-select" name="id_menu">
                                <option value="">Seleccione</option>
                                <?php foreach ($menus as $menu): ?>
                                    <option value="<?= (int) $menu['id_menu'] ?>" <?= ((int) ($editingSubmenu['id_menu'] ?? 0) === (int) $menu['id_menu']) ? 'selected' : '' ?>><?= cms_e($menu['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" value="<?= cms_e($editingSubmenu['nombre'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">URL</label><input class="form-control" name="url" value="<?= cms_e($editingSubmenu['url'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">Ícono</label><input class="form-control" name="icono" value="<?= cms_e($editingSubmenu['icono'] ?? '') ?>"></div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Orden</label><input class="form-control" type="number" name="orden" value="<?= (int) ($editingSubmenu['orden'] ?? 1) ?>"></div>
                            <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="estado" <?= !isset($editingSubmenu['estado']) || (int) $editingSubmenu['estado'] === 1 ? 'checked' : '' ?>><label class="form-check-label">Activo</label></div></div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button class="btn btn-premium flex-fill" type="submit">Guardar</button>
                            <?php if ($editingSubmenu): ?><a class="btn btn-soft" href="admin_1.php?panel=submenus">Cancelar</a><?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php elseif ($panel === 'configuracion'): ?>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Configuración institucional</h3>
                <p>Datos globales del sitio desde la tabla <code>institucion</code>.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-xl-8">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="guardar_institucion">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nombre del sitio</label><input class="form-control" name="nombre" value="<?= cms_e($institution['nombre'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Correo contacto</label><input class="form-control" name="email" value="<?= cms_e($institution['email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" value="<?= cms_e($institution['telefono'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Dirección</label><input class="form-control" name="direccion" value="<?= cms_e($institution['direccion'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Color principal</label><input class="form-control" name="color_primario" value="<?= cms_e($institution['color_primario'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Color secundario</label><input class="form-control" name="color_secundario" value="<?= cms_e($institution['color_secundario'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Facebook</label><input class="form-control" name="facebook" value="<?= cms_e($institution['facebook'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Instagram</label><input class="form-control" name="instagram" value="<?= cms_e($institution['instagram'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Logo</label><input class="form-control" type="file" name="logo_header" accept="image/*"></div>
                        <div class="col-md-6"><label class="form-label">Favicon</label><input class="form-control" type="file" name="favicon" accept="image/*,.ico"></div>
                    </div>
                    <div class="mt-4">
                        <button class="btn btn-premium" type="submit"><i class="bi bi-save me-2"></i>Guardar configuración</button>
                    </div>
                </form>
            </div>
            <div class="col-xl-4">
                <div class="section-card mb-0">
                    <h3>Vista rápida</h3>
                    <p class="text-muted">Resumen de identidad institucional.</p>
                    <div class="mb-3"><strong><?= cms_e($institution['nombre'] ?? '') ?></strong></div>
                    <div class="mb-2 text-muted"><?= cms_e($institution['email'] ?? '') ?></div>
                    <div class="mb-3 text-muted"><?= cms_e($institution['telefono'] ?? '') ?></div>
                    <div class="d-flex gap-2">
                        <div style="width:56px;height:56px;border-radius:16px;background:<?= cms_e($institution['color_primario'] ?? '#2563EB') ?>;"></div>
                        <div style="width:56px;height:56px;border-radius:16px;background:<?= cms_e($institution['color_secundario'] ?? '#E9A629') ?>;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:24px; overflow:hidden; border:0;">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Vista previa del contenedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0" style="background:#f8fafc;">
                <iframe id="previewFrame" title="Vista previa del contenedor" style="width:100%; min-height:70vh; border:0; display:block;" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>

<?php
admin_render_layout_end([
    'extra_scripts' => <<<'HTML'
    <script>
        $(function () {
            ['#contenedoresTable', '#menusTable', '#submenusTable'].forEach(function (selector) {
                if ($(selector).length) {
                    $(selector).DataTable({
                        pageLength: 10,
                        order: [[0, 'asc']],
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
                        }
                    });
                }
            });

            $('.js-toggle-seccion').on('change', function () {
                var checkbox = this;
                var form = checkbox.closest('.js-toggle-seccion-form');
                var label = form.querySelector('.js-toggle-label');
                var formData = new FormData(form);
                var previousState = !checkbox.checked;

                checkbox.disabled = true;

                fetch('admin_1.php?panel=contenedores', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('No se pudo actualizar la visibilidad.');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        if (!data.ok) {
                            throw new Error(data.message || 'No se pudo actualizar la visibilidad.');
                        }
                        checkbox.checked = data.visible === 'si';
                        label.textContent = data.label;
                    })
                    .catch(function (error) {
                        checkbox.checked = previousState;
                        label.textContent = previousState ? 'Activo' : 'Oculto';
                        window.alert(error.message);
                    })
                    .finally(function () {
                        checkbox.disabled = false;
                    });
            });

            $('.js-preview-btn').on('click', function () {
                var button = this;
                var title = button.getAttribute('data-preview-title') || 'Vista previa del contenedor';
                var url = button.getAttribute('data-preview-url');
                var modalEl = document.getElementById('previewModal');
                var titleEl = document.getElementById('previewModalLabel');
                var frameEl = document.getElementById('previewFrame');

                titleEl.textContent = 'Vista previa: ' + title;
                frameEl.src = url;
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });

            document.getElementById('previewModal').addEventListener('hidden.bs.modal', function () {
                document.getElementById('previewFrame').src = 'about:blank';
            });
        });
    </script>
HTML,
]);
