<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
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
                    'updated_at_label' => date('d-m-Y H:i'),
                    'updated_by' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
                ]);
                exit;
            }
            cms_set_flash('success', 'La visibilidad del contenedor fue actualizada.');
            cms_redirect('admin.php?panel=contenedores');
        }

        if ($action === 'guardar_menu') {
            $idMenuAudit = (int) ($_POST['id_menu'] ?? 0);
            $datosAntes = $idMenuAudit > 0 ? obtenerRegistroAuditoria($db, 'menus', 'id_menu', $idMenuAudit) : null;
            $savedMenuId = cms_save_menu($db, $_POST);
            $datosDespues = obtenerRegistroAuditoria($db, 'menus', 'id_menu', $savedMenuId);
            registrarAuditoria($db, 'Menú principal', 'menus', $savedMenuId, $idMenuAudit > 0 ? 'editar' : 'crear', $idMenuAudit > 0 ? 'Se modificó un menú principal' : 'Se creó un menú principal', $datosAntes, $datosDespues);
            cms_set_flash('success', 'El menú fue guardado correctamente.');
            cms_redirect('admin.php?panel=menus');
        }

        if ($action === 'toggle_menu') {
            $idMenuAudit = (int) ($_POST['id_menu'] ?? 0);
            $datosAntes = obtenerRegistroAuditoria($db, 'menus', 'id_menu', $idMenuAudit);
            cms_toggle_menu($db, $idMenuAudit);
            $datosDespues = obtenerRegistroAuditoria($db, 'menus', 'id_menu', $idMenuAudit);
            $accionAudit = (int) ($datosDespues['estado'] ?? 0) === 1 ? 'activar' : 'desactivar';
            registrarAuditoria($db, 'Menú principal', 'menus', $idMenuAudit, $accionAudit, 'Se cambió el estado de un menú principal', $datosAntes, $datosDespues);
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['ok' => true, 'estado' => (int) ($datosDespues['estado'] ?? 0)]);
                exit;
            }
            cms_set_flash('success', 'El estado del menú fue actualizado.');
            cms_redirect('admin.php?panel=menus');
        }

        if ($action === 'eliminar_menu') {
            $idMenuAudit = (int) ($_POST['id_menu'] ?? 0);
            $datosAntes = obtenerRegistroAuditoria($db, 'menus', 'id_menu', $idMenuAudit);
            cms_delete_menu($db, $idMenuAudit);
            registrarAuditoria($db, 'Menú principal', 'menus', $idMenuAudit, 'eliminar', 'Se eliminó un menú principal junto a sus submenús asociados', $datosAntes, null);
            cms_redirect('admin.php?panel=menus&saved=menu_deleted');
        }

        if ($action === 'guardar_submenu') {
            $idSubMenuAudit = (int) ($_POST['id_sub_menu'] ?? 0);
            $datosAntes = $idSubMenuAudit > 0 ? obtenerRegistroAuditoria($db, 'sub_menus', 'id_sub_menu', $idSubMenuAudit) : null;
            $savedSubMenuId = cms_save_submenu($db, $_POST);
            $datosDespues = obtenerRegistroAuditoria($db, 'sub_menus', 'id_sub_menu', $savedSubMenuId);
            registrarAuditoria($db, 'Submenús', 'sub_menus', $savedSubMenuId, $idSubMenuAudit > 0 ? 'editar' : 'crear', $idSubMenuAudit > 0 ? 'Se modificó un submenú' : 'Se creó un submenú', $datosAntes, $datosDespues);
            $returnPanel = in_array($_POST['return_panel'] ?? '', ['menus', 'submenus'], true) ? $_POST['return_panel'] : 'submenus';
            $savedStatus = $idSubMenuAudit > 0 ? 'submenu_updated' : 'submenu_created';
            cms_redirect('admin.php?panel=' . $returnPanel . '&saved=' . $savedStatus);
        }

        if ($action === 'toggle_submenu') {
            $idSubMenuAudit = (int) ($_POST['id_sub_menu'] ?? 0);
            $datosAntes = obtenerRegistroAuditoria($db, 'sub_menus', 'id_sub_menu', $idSubMenuAudit);
            cms_toggle_submenu($db, $idSubMenuAudit);
            $datosDespues = obtenerRegistroAuditoria($db, 'sub_menus', 'id_sub_menu', $idSubMenuAudit);
            $accionAudit = (int) ($datosDespues['estado'] ?? 0) === 1 ? 'activar' : 'desactivar';
            registrarAuditoria($db, 'Submenús', 'sub_menus', $idSubMenuAudit, $accionAudit, 'Se cambió el estado de un submenú', $datosAntes, $datosDespues);
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['ok' => true, 'estado' => (int) ($datosDespues['estado'] ?? 0)]);
                exit;
            }
            cms_set_flash('success', 'El estado del submenú fue actualizado.');
            cms_redirect('admin.php?panel=menus');
        }

        if ($action === 'reorder_menus') {
            $ids = array_map('intval', (array) ($_POST['items'] ?? []));
            cms_reorder_menus($db, $ids);
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['ok' => true]);
                exit;
            }
            cms_redirect('admin.php?panel=menus');
        }

        if ($action === 'reorder_submenus') {
            $ids = array_map('intval', (array) ($_POST['items'] ?? []));
            cms_reorder_submenus($db, $ids);
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['ok' => true]);
                exit;
            }
            cms_redirect('admin.php?panel=submenus');
        }

        if ($action === 'guardar_institucion') {
            cms_save_institution($db, $institutionId, $_POST);
            cms_redirect('admin.php?panel=configuracion&saved=config');
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
    cms_redirect('admin.php?panel=' . urlencode($panel));
}

$flash = cms_get_flash();
$site = cms_get_site_data($db);
$sections = cms_list_sections_admin($db, $institutionId);
$institution = $site['institution'];
$menus = cms_list_menus($db);
$submenus = cms_list_submenus($db);
$submenusByMenu = [];
foreach ($submenus as $_sub) {
    $submenusByMenu[$_sub['id_menu']][] = $_sub;
}
unset($_sub);
$editingMenu = isset($_GET['menu']) ? cms_get_menu($db, (int) $_GET['menu']) : null;
$editingSubmenu = isset($_GET['submenu']) ? cms_get_submenu($db, (int) $_GET['submenu']) : null;
$visibleSections = array_values(array_filter($sections, static fn($section) => ($section['visible'] ?? '') === 'si'));
$activeSections = array_values(array_filter($sections, static fn($section) => ($section['estado'] ?? 'activo') === 'activo'));
$hiddenSections = array_values(array_filter($sections, static fn($section) => ($section['visible'] ?? '') === 'no'));
$lastSectionUpdate = null;
foreach ($sections as $sectionUpdate) {
    $candidate = trim((string) ($sectionUpdate['actualizado_en'] ?? ''));
    if ($candidate !== '' && ($lastSectionUpdate === null || strtotime($candidate) > strtotime($lastSectionUpdate))) {
        $lastSectionUpdate = $candidate;
    }
}
unset($sectionUpdate);
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
    'color_primario' => $institution['color_primario'] ?? '',
    'color_secundario' => $institution['color_secundario'] ?? '',
    'color_terciario' => $institution['color_terciario'] ?? '',
    'color_cuaternario' => $institution['color_cuaternario'] ?? '',
    'admin_name' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'header_actions' => '',
    'extra_head' => <<<'HTML'
    <style>
        /* Dashboard */
        .dashboard-grid { display: grid; gap: 20px; }
        .dashboard-metrics { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 16px; }
        .dash-panel {
            background: var(--adm-card);
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius);
            box-shadow: var(--adm-shadow-sm);
            padding: 18px;
            height: 100%;
        }
        .dash-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
        .dash-panel-title { display: flex; align-items: center; gap: 8px; font-size: .95rem; font-weight: 700; color: var(--adm-text); margin: 0; }
        .dash-panel-title i { color: var(--adm-primary); }
        .dash-event-list, .dash-list { display: flex; flex-direction: column; gap: 10px; }
        .dash-event {
            display: grid;
            grid-template-columns: 52px minmax(0,1fr) auto;
            align-items: center;
            gap: 12px;
            padding: 10px;
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius-sm);
            color: inherit;
            transition: background var(--adm-transition);
        }
        .dash-event:hover { background: #f8fafc; }
        .dash-event-date {
            height: 52px; border-radius: 10px;
            display: grid; place-items: center;
            background: linear-gradient(135deg,var(--adm-primary),var(--adm-secondary));
            color: #fff; font-weight: 800; line-height: 1; font-size: .95rem;
        }
        .dash-event-date small { display: block; font-size: .62rem; margin-top: 3px; font-weight: 600; }
        .dash-event strong, .dash-list strong { display: block; color: var(--adm-text); font-weight: 600; font-size: .87rem; }
        .dash-event span,   .dash-list span   { display: block; color: var(--adm-muted); font-size: .78rem; margin-top: 2px; }
        .dash-actions { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 10px; }
        .dash-action {
            min-height: 80px; border-radius: var(--adm-radius);
            display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 6px;
            color: var(--adm-text-2); background: #f8fafc; border: 1px solid var(--adm-border);
            font-size: .82rem; font-weight: 600; text-align: center;
            transition: box-shadow var(--adm-transition), background var(--adm-transition);
        }
        .dash-action:hover { background: var(--adm-primary-soft); border-color: var(--adm-primary); color: var(--adm-primary); }
        .dash-action i {
            width: 38px; height: 38px; border-radius: 10px;
            display: grid; place-items: center;
            background: linear-gradient(135deg,var(--adm-primary),var(--adm-accent));
            color: #fff; font-size: 1.1rem;
        }
        .dash-home-row {
            display: grid;
            grid-template-columns: 20px minmax(0,1fr) auto auto;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--adm-border);
        }
        .dash-home-row:last-child { border-bottom: 0; }
        .dash-mini-calendar { display: grid; grid-template-columns: repeat(7,1fr); gap: 6px; text-align: center; }
        .dash-mini-calendar span { color: var(--adm-muted); font-size: .72rem; font-weight: 700; padding: 2px 0; }
        .dash-mini-calendar b {
            min-height: 30px; border-radius: 999px;
            display: grid; place-items: center;
            color: var(--adm-text); font-size: .8rem; font-weight: 400;
        }
        .dash-mini-calendar b.has-event { background: var(--adm-primary-soft); color: var(--adm-primary); font-weight: 700; }
        .dash-mini-calendar b.today { background: linear-gradient(135deg,var(--adm-primary),var(--adm-accent)); color: #fff; font-weight: 700; }
        .cms-summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 18px; }
        .cms-order-cell { display: flex; align-items: center; gap: 10px; min-width: 112px; }
        .cms-drag-handle { width: 32px; height: 32px; border: 1px solid var(--adm-border); border-radius: 8px; display: inline-grid; place-items: center; color: var(--adm-muted); background: #fff; cursor: grab; }
        .cms-drag-handle:hover { color: var(--adm-primary); border-color: var(--adm-primary); background: var(--adm-primary-soft); }
        .cms-fixed-lock { width: 32px; height: 32px; border-radius: 8px; display: inline-grid; place-items: center; color: var(--adm-muted); background: #f8fafc; border: 1px solid var(--adm-border); }
        .cms-row-fixed { background: linear-gradient(90deg, rgba(248,250,252,.95), rgba(255,255,255,.95)); }
        .cms-row-movable.sortable-ghost { opacity: .35; }
        .cms-row-movable.sortable-chosen { box-shadow: 0 12px 28px rgba(15, 23, 42, .14); }
        .cms-last-update strong { display: block; color: var(--adm-text); font-size: .84rem; font-weight: 700; }
        .cms-last-update span { display: block; color: var(--adm-muted); font-size: .75rem; margin-top: 2px; }
        @media (max-width: 1399px) { .dashboard-metrics { grid-template-columns: repeat(2,minmax(0,1fr)); } }
        @media (max-width: 1199px) { .cms-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) { .dashboard-metrics, .dash-actions, .cms-summary-grid { grid-template-columns: 1fr; } }
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
            <div class="stat-card"><div class="stat-icon green"><i class="bi bi-layout-text-window-reverse"></i></div><div class="stat-body"><strong><?= count($sections) ?></strong><span>Contenedores</span><small><?= count($visibleSections) ?> visibles</small></div></div>
            <div class="stat-card"><div class="stat-icon blue"><i class="bi bi-list-nested"></i></div><div class="stat-body"><strong><?= count($menus) ?></strong><span>Menús</span><small>Activos en navegación</small></div></div>
            <div class="stat-card"><div class="stat-icon amber"><i class="bi bi-diagram-3"></i></div><div class="stat-body"><strong><?= count($submenus) ?></strong><span>Submenús</span><small>Enlaces secundarios</small></div></div>
            <div class="stat-card"><div class="stat-icon purple"><i class="bi bi-calendar-event"></i></div><div class="stat-body"><strong><?= count($eventsThisMonth) ?></strong><span>Eventos este mes</span><small>Publicados y visibles</small></div></div>
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
                        <a class="btn btn-sm btn-soft" href="index.php#calendario-eventos-home" target="_blank"><i class="bi bi-box-arrow-up-right"></i></a>
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
                        <a class="dash-action" href="admin.php?panel=menus"><i class="bi bi-list-nested"></i>Menús</a>
                        <a class="dash-action" href="admin.php?panel=configuracion"><i class="bi bi-sliders"></i>Ajustes</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-6">
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <h3 class="dash-panel-title"><i class="bi bi-house-check"></i>Contenedores del home</h3>
                        <a class="btn btn-sm btn-soft" href="admin.php?panel=contenedores">Ver todos</a>
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
    <div class="cms-summary-grid">
        <div class="stat-card"><div class="stat-icon green"><i class="bi bi-check2-circle"></i></div><div class="stat-body"><strong id="cmsTotalActive"><?= count($activeSections) ?></strong><span>Activos</span><small>Estado activo</small></div></div>
        <div class="stat-card"><div class="stat-icon blue"><i class="bi bi-eye"></i></div><div class="stat-body"><strong id="cmsTotalVisible"><?= count($visibleSections) ?></strong><span>Visibles</span><small>Renderizan en index.php</small></div></div>
        <div class="stat-card"><div class="stat-icon amber"><i class="bi bi-eye-slash"></i></div><div class="stat-body"><strong id="cmsTotalHidden"><?= count($hiddenSections) ?></strong><span>Ocultos</span><small>Visible = no</small></div></div>
        <div class="stat-card"><div class="stat-icon purple"><i class="bi bi-clock-history"></i></div><div class="stat-body"><strong id="cmsLastUpdate" style="font-size:1.05rem;"><?= $lastSectionUpdate ? cms_e(date('d-m-Y H:i', strtotime($lastSectionUpdate))) : 'Sin registro' ?></strong><span>Última actualización</span><small>Registro general</small></div></div>
    </div>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Contenedores del sitio</h3>
                <p>Panel general de bloques de index.php. Los contenedores fijos permanecen bloqueados.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle" id="contenedoresTable">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Contenedor</th>
                        <th>Qué controla</th>
                        <th>Tipo</th>
                        <th>Última modificación</th>
                        <th>Visible</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sections as $section): ?>
                        <?php
                            $sectionName = (string) ($section['nombre_interno'] ?? '');
                            $isMovableSection = cms_section_is_movable($sectionName);
                            $isFixedSection = cms_section_is_fixed($sectionName);
                            $updatedAt = trim((string) ($section['actualizado_en'] ?? ''));
                            $updatedUser = trim((string) (($section['actualizado_por_nombre'] ?? '') . ' ' . ($section['actualizado_por_apellido'] ?? '')));
                            if ($updatedUser === '') {
                                $updatedUser = (string) ($section['actualizado_por_usuario'] ?? $section['actualizado_por_email'] ?? '');
                            }
                        ?>
                        <tr class="<?= $isMovableSection ? 'cms-row-movable' : 'cms-row-fixed' ?>" data-id="<?= (int) $section['id_seccion'] ?>" data-order="<?= (int) $section['orden'] ?>" data-fixed="<?= $isFixedSection ? '1' : '0' ?>" data-movable="<?= $isMovableSection ? '1' : '0' ?>">
                            <td class="js-section-updated-cell">
                                <div class="cms-order-cell">
                                    <?php if ($isMovableSection): ?>
                                        <span class="cms-drag-handle" title="Arrastrar para ordenar"><i class="bi bi-grip-vertical"></i></span>
                                    <?php else: ?>
                                        <span class="cms-fixed-lock" title="Contenedor fijo"><i class="bi bi-lock-fill"></i></span>
                                    <?php endif; ?>
                                    <span><?= (int) $section['orden'] ?></span>
                                    <?php if (!$isMovableSection): ?>
                                        <span class="badge-soft dark">Fijo</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <strong><?= cms_e($section['titulo_admin']) ?></strong>
                                <div><code style="font-size:.76rem;color:var(--adm-muted);"><?= cms_e($sectionName) ?></code></div>
                            </td>
                            <td><div class="text-muted" style="min-width:280px; white-space:normal;"><?= cms_e($section['observacion'] ?? '') ?></div></td>
                            <td><span class="badge-soft dark"><?= cms_e($section['tipo_seccion']) ?></span></td>
                            <td>
                                <div class="cms-last-update">
                                    <?php if ($updatedAt !== ''): ?>
                                        <strong><?= cms_e(date('d-m-Y H:i', strtotime($updatedAt))) ?></strong>
                                        <span><?= $updatedUser !== '' ? cms_e($updatedUser) : 'Usuario no registrado' ?></span>
                                    <?php else: ?>
                                        <strong>Sin registro</strong>
                                        <span>Sin modificación real</span>
                                    <?php endif; ?>
                                </div>
                            </td>
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
                                    <button type="button" class="btn-icon preview js-preview-btn" title="Vista previa" data-preview-title="<?= cms_e($section['titulo_admin']) ?>" data-preview-url="preview_contenedor.php?id=<?= (int) $section['id_seccion'] ?>&embed=1">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a class="btn-icon edit" href="editar_contenedor.php?id=<?= (int) $section['id_seccion'] ?>&modo=editar" title="Editar">
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
<style>
.mnu-list { display:flex; flex-direction:column; gap:6px; }
.mnu-card { border:1px solid var(--adm-border); border-radius:10px; background:#fff; overflow:hidden; transition:box-shadow .15s; }
.mnu-card:hover { box-shadow:0 2px 10px rgba(0,0,0,.07); }
.mnu-card.sortable-ghost { opacity:.28; border:2px dashed var(--adm-primary); }
.mnu-card.sortable-chosen { box-shadow:0 10px 28px rgba(0,0,0,.12); }
.mnu-row { display:grid; grid-template-columns:36px 1fr 180px 90px 130px 38px 38px 54px; align-items:center; padding:10px 14px; gap:10px; min-height:52px; }
.mnu-expand-btn { border:none; background:none; padding:5px 8px; border-radius:7px; cursor:pointer; color:var(--adm-muted); display:flex; align-items:center; gap:5px; transition:background .15s; }
.mnu-expand-btn:hover { background:var(--adm-surface); color:var(--adm-text); }
.mnu-expand-btn .expand-icon { font-size:.78rem; transition:transform .22s ease; }
.mnu-expand-btn[aria-expanded="true"] .expand-icon { transform:rotate(180deg); }
.mnu-sub-area { border-top:1px solid var(--adm-border); background:#f9fafb; border-left:3px solid var(--adm-primary); padding-left:42px; }
.mnu-sub-table { width:100%; border-collapse:collapse; }
.mnu-sub-table tr { border-bottom:1px solid var(--adm-border); }
.mnu-sub-table tr:last-child { border-bottom:none; }
.mnu-sub-table td { padding:7px 12px; font-size:.84rem; vertical-align:middle; }
.mnu-sub-table .sub-drag { width:36px; padding-left:8px; }
.mnu-sub-table .sub-name { font-weight:600; min-width:130px; }
.mnu-sub-table .sub-url { }
.mnu-sub-table .sub-toggle { width:130px; }
.mnu-sub-table .sub-edit { width:38px; }
.mnu-sub-footer { padding:8px 14px 10px 8px; }
</style>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Menú principal</h3>
                <p>Arrastrá <i class="bi bi-grip-vertical"></i> para reordenar. Expandí cada menú para ver y gestionar sus submenús.</p>
            </div>
            <div>
                <button class="btn btn-premium" onclick="abrirModalMenu(null)"><i class="bi bi-plus-lg"></i> Nuevo menú</button>
            </div>
        </div>
        <div class="mnu-list" id="menusSortableList">
            <?php foreach ($menus as $menu): ?>
                <?php $menuSubs = $submenusByMenu[$menu['id_menu']] ?? []; $subCount = count($menuSubs); ?>
                <div class="mnu-card" data-id="<?= (int) $menu['id_menu'] ?>">
                    <div class="mnu-row">
                        <div><i class="bi bi-grip-vertical menu-drag-handle" style="cursor:grab;color:var(--adm-muted);font-size:1.15rem;"></i></div>
                        <div style="font-weight:600;font-size:.93rem;"><?= cms_e($menu['nombre']) ?></div>
                        <div><code style="font-size:.78rem;color:var(--adm-muted);"><?= cms_e($menu['url']) ?: '—' ?></code></div>
                        <div style="font-size:.82rem;color:var(--adm-muted);"><?= cms_e($menu['icono']) ?: '—' ?></div>
                        <div>
                            <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                <input class="form-check-input js-menu-toggle" type="checkbox" role="switch"
                                    data-id="<?= (int) $menu['id_menu'] ?>"
                                    data-nombre="<?= cms_e($menu['nombre']) ?>"
                                    <?= (int) $menu['estado'] === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" style="font-size:.84rem;"><?= (int) $menu['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                            </div>
                        </div>
                        <div>
                            <button class="btn-icon edit" title="Editar menú"
                                data-id="<?= (int) $menu['id_menu'] ?>"
                                data-nombre="<?= cms_e($menu['nombre']) ?>"
                                data-url="<?= cms_e($menu['url']) ?>"
                                data-icono="<?= cms_e($menu['icono']) ?>"
                                data-estado="<?= (int) $menu['estado'] ?>"
                                onclick="abrirModalMenu(this)">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </div>
                        <div>
                            <button type="button" class="btn-icon delete js-menu-delete" title="Eliminar menú"
                                data-id="<?= (int) $menu['id_menu'] ?>"
                                data-nombre="<?= cms_e($menu['nombre']) ?>"
                                data-sub-count="<?= (int) $subCount ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div>
                            <button class="mnu-expand-btn collapsed"
                                data-bs-toggle="collapse"
                                data-bs-target="#menuSubs-<?= (int) $menu['id_menu'] ?>"
                                aria-expanded="false">
                                <?php if ($subCount > 0): ?>
                                <span class="badge rounded-pill" style="background:var(--adm-primary);color:#fff;font-size:.68rem;padding:2px 6px;"><?= $subCount ?></span>
                                <?php endif; ?>
                                <i class="bi bi-chevron-down expand-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="collapse mnu-sub-area" id="menuSubs-<?= (int) $menu['id_menu'] ?>">
                        <?php if (!empty($menuSubs)): ?>
                        <table class="mnu-sub-table">
                            <tbody class="submenusSortableTbody" data-id-menu="<?= (int) $menu['id_menu'] ?>">
                                <?php foreach ($menuSubs as $sub): ?>
                                <tr data-id="<?= (int) $sub['id_sub_menu'] ?>">
                                    <td class="sub-drag"><i class="bi bi-grip-vertical sub-drag-handle" style="cursor:grab;color:var(--adm-muted);font-size:1rem;"></i></td>
                                    <td class="sub-name"><?= cms_e($sub['nombre']) ?></td>
                                    <td class="sub-url"><code style="font-size:.78rem;color:var(--adm-muted);"><?= cms_e($sub['url']) ?: '—' ?></code></td>
                                    <td class="sub-toggle">
                                        <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                            <input class="form-check-input js-submenu-toggle" type="checkbox" role="switch"
                                                data-id="<?= (int) $sub['id_sub_menu'] ?>"
                                                data-nombre="<?= cms_e($sub['nombre']) ?>"
                                                <?= (int) $sub['estado'] === 1 ? 'checked' : '' ?>>
                                            <label class="form-check-label" style="font-size:.82rem;"><?= (int) $sub['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                                        </div>
                                    </td>
                                    <td class="sub-edit">
                                        <button class="btn-icon edit" title="Editar submenú"
                                            data-id="<?= (int) $sub['id_sub_menu'] ?>"
                                            data-id-menu="<?= (int) $sub['id_menu'] ?>"
                                            data-nombre="<?= cms_e($sub['nombre']) ?>"
                                            data-url="<?= cms_e($sub['url']) ?>"
                                            data-icono="<?= cms_e($sub['icono']) ?>"
                                            data-estado="<?= (int) $sub['estado'] ?>"
                                            onclick="abrirModalSubmenu(this)">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                        <div class="mnu-sub-footer">
                            <button class="btn btn-soft btn-sm d-inline-flex align-items-center gap-1" onclick="abrirModalSubmenu(null, <?= (int) $menu['id_menu'] ?>)">
                                <i class="bi bi-plus"></i> Agregar submenú a <strong style="margin-left:3px;"><?= cms_e($menu['nombre']) ?></strong>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Modal Menú -->
    <div class="modal fade" id="modalMenu" tabindex="-1" aria-labelledby="modalMenuLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" id="formModalMenu">
                    <input type="hidden" name="accion" value="guardar_menu">
                    <input type="hidden" name="id_menu" id="modalMenuId" value="0">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalMenuLabel">Menú</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input class="form-control" name="nombre" id="modalMenuNombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input class="form-control" name="url" id="modalMenuUrl" placeholder="#">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ícono <small class="text-muted">(clase Bootstrap Icons, ej: bi-home)</small></label>
                            <input class="form-control" name="icono" id="modalMenuIcono" placeholder="bi-house">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="estado" id="modalMenuEstado">
                            <label class="form-check-label" for="modalMenuEstado">Activo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-premium">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Submenú -->
    <div class="modal fade" id="modalSubmenu" tabindex="-1" aria-labelledby="modalSubmenuLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" id="formModalSubmenu">
                    <input type="hidden" name="accion" value="guardar_submenu">
                    <input type="hidden" name="return_panel" value="menus">
                    <input type="hidden" name="id_sub_menu" id="modalSubmenuId" value="0">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSubmenuLabel">Submenú</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Menú padre <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_menu" id="modalSubmenuIdMenu" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($menus as $menu): ?>
                                    <option value="<?= (int) $menu['id_menu'] ?>"><?= cms_e($menu['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input class="form-control" name="nombre" id="modalSubmenuNombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input class="form-control" name="url" id="modalSubmenuUrl" placeholder="#">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ícono <small class="text-muted">(clase Bootstrap Icons)</small></label>
                            <input class="form-control" name="icono" id="modalSubmenuIcono" placeholder="bi-file-text">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="estado" id="modalSubmenuEstado">
                            <label class="form-check-label" for="modalSubmenuEstado">Activo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-premium">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php elseif ($panel === 'submenus'): ?>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Submenús</h3>
                <p>Arrastrá <i class="bi bi-grip-vertical"></i> dentro de cada grupo para reordenar. Usá <i class="bi bi-pencil-square"></i> para editar.</p>
            </div>
            <div>
                <button class="btn btn-premium" onclick="abrirModalSubmenu(null)"><i class="bi bi-plus-lg"></i> Nuevo submenú</button>
            </div>
        </div>

        <?php foreach ($menus as $menuPadre): ?>
            <?php if (empty($submenusByMenu[$menuPadre['id_menu']])): continue; endif; ?>
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2" style="border-bottom:2px solid var(--adm-border);padding-bottom:6px;">
                    <i class="bi bi-list-nested" style="color:var(--adm-primary);font-size:1rem;"></i>
                    <strong style="font-size:.9rem;letter-spacing:.03em;"><?= cms_e($menuPadre['nombre']) ?></strong>
                    <span class="badge" style="background:var(--adm-primary);color:#fff;font-size:.7rem;"><?= count($submenusByMenu[$menuPadre['id_menu']]) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:38px"></th>
                                <th>Nombre</th>
                                <th>URL</th>
                                <th>Ícono</th>
                                <th>Activo</th>
                                <th style="width:56px">Editar</th>
                            </tr>
                        </thead>
                        <tbody class="submenusSortableTbody" data-id-menu="<?= (int) $menuPadre['id_menu'] ?>">
                            <?php foreach ($submenusByMenu[$menuPadre['id_menu']] as $submenu): ?>
                                <tr data-id="<?= (int) $submenu['id_sub_menu'] ?>">
                                    <td><i class="bi bi-grip-vertical drag-handle" style="cursor:grab;color:var(--adm-muted);font-size:1.15rem;"></i></td>
                                    <td><strong><?= cms_e($submenu['nombre']) ?></strong></td>
                                    <td><code><?= cms_e($submenu['url']) ?></code></td>
                                    <td><?= cms_e($submenu['icono']) ?></td>
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
                                    <td>
                                        <button class="btn-icon edit" title="Editar"
                                            data-id="<?= (int) $submenu['id_sub_menu'] ?>"
                                            data-id-menu="<?= (int) $submenu['id_menu'] ?>"
                                            data-nombre="<?= cms_e($submenu['nombre']) ?>"
                                            data-url="<?= cms_e($submenu['url']) ?>"
                                            data-icono="<?= cms_e($submenu['icono']) ?>"
                                            data-estado="<?= (int) $submenu['estado'] ?>"
                                            onclick="abrirModalSubmenu(this)">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Modal Submenú -->
    <div class="modal fade" id="modalSubmenu" tabindex="-1" aria-labelledby="modalSubmenuLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" id="formModalSubmenu">
                    <input type="hidden" name="accion" value="guardar_submenu">
                    <input type="hidden" name="id_sub_menu" id="modalSubmenuId" value="0">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSubmenuLabel">Submenú</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Menú padre <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_menu" id="modalSubmenuIdMenu" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($menus as $menu): ?>
                                    <option value="<?= (int) $menu['id_menu'] ?>"><?= cms_e($menu['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input class="form-control" name="nombre" id="modalSubmenuNombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input class="form-control" name="url" id="modalSubmenuUrl" placeholder="#">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ícono <small class="text-muted">(clase Bootstrap Icons)</small></label>
                            <input class="form-control" name="icono" id="modalSubmenuIcono" placeholder="bi-file-text">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="estado" id="modalSubmenuEstado">
                            <label class="form-check-label" for="modalSubmenuEstado">Activo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-premium">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php elseif ($panel === 'configuracion'): ?>
<style>
.cfg-group { border: 1px solid var(--adm-border); border-radius: 12px; background: #fff; padding: 18px 20px; margin-bottom: 18px; }
.cfg-group-title { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--adm-muted); margin-bottom: 14px; display: flex; align-items: center; gap: 7px; }
.color-field { display: flex; align-items: center; gap: 10px; }
.color-field input[type="color"] { width: 44px; height: 44px; padding: 2px; border: 1px solid var(--adm-border); border-radius: 10px; cursor: pointer; flex-shrink: 0; background: #fff; }
.color-field .form-control { font-family: monospace; font-size: .85rem; flex: 1; }
.img-preview-wrap { position: relative; width: 100%; min-height: 72px; background: #f8fafc; border: 1px solid var(--adm-border); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; overflow: hidden; }
.img-preview-wrap img { max-height: 72px; max-width: 100%; object-fit: contain; display: block; }
.img-preview-placeholder { color: var(--adm-muted); font-size: .8rem; padding: 12px; text-align: center; }
.favicon-preview-wrap { width: 48px; height: 48px; background: #f8fafc; border: 1px solid var(--adm-border); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; overflow: hidden; }
.favicon-preview-wrap img { width: 32px; height: 32px; object-fit: contain; }
.qv-logo { max-height: 56px; max-width: 160px; object-fit: contain; margin-bottom: 10px; display: block; }
.qv-color { width: 44px; height: 44px; border-radius: 12px; border: 2px solid rgba(0,0,0,.06); flex-shrink: 0; }
</style>
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

                <div class="cfg-group">
                    <div class="cfg-group-title"><i class="bi bi-building"></i>Identidad</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nombre del sitio</label><input class="form-control" name="nombre" value="<?= cms_e($institution['nombre'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Nombre corto</label><input class="form-control" name="nombre_corto" value="<?= cms_e($institution['nombre_corto'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label">Eslogan</label><input class="form-control" name="eslogan" value="<?= cms_e($institution['eslogan'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label">Descripción corta</label><input class="form-control" name="descripcion_corta" value="<?= cms_e($institution['descripcion_corta'] ?? '') ?>"></div>
                    </div>
                </div>

                <div class="cfg-group">
                    <div class="cfg-group-title"><i class="bi bi-telephone"></i>Contacto</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" value="<?= cms_e($institution['telefono'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">WhatsApp</label><input class="form-control" name="whatsapp" placeholder="+598 9..." value="<?= cms_e($institution['whatsapp'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Correo contacto</label><input class="form-control" name="email" value="<?= cms_e($institution['email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Correo soporte</label><input class="form-control" name="email_soporte" value="<?= cms_e($institution['email_soporte'] ?? '') ?>"></div>
                        <div class="col-md-8"><label class="form-label">Dirección</label><input class="form-control" name="direccion" value="<?= cms_e($institution['direccion'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Ciudad</label><input class="form-control" name="ciudad" value="<?= cms_e($institution['ciudad'] ?? '') ?>"></div>
                    </div>
                </div>

                <div class="cfg-group">
                    <div class="cfg-group-title"><i class="bi bi-share"></i>Redes sociales</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label"><i class="bi bi-facebook me-1"></i>Facebook</label><input class="form-control" name="facebook" placeholder="https://facebook.com/..." value="<?= cms_e($institution['facebook'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label"><i class="bi bi-instagram me-1"></i>Instagram</label><input class="form-control" name="instagram" placeholder="https://instagram.com/..." value="<?= cms_e($institution['instagram'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label"><i class="bi bi-youtube me-1"></i>YouTube</label><input class="form-control" name="youtube" placeholder="https://youtube.com/..." value="<?= cms_e($institution['youtube'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label"><i class="bi bi-linkedin me-1"></i>LinkedIn</label><input class="form-control" name="linkedin" placeholder="https://linkedin.com/..." value="<?= cms_e($institution['linkedin'] ?? '') ?>"></div>
                    </div>
                </div>

                <div class="cfg-group">
                    <div class="cfg-group-title"><i class="bi bi-palette"></i>Colores institucionales</div>
                    <div class="row g-3">
                        <?php
                        $colorFields = [
                            ['color_primario',    'Color principal',    '#F0A000'],
                            ['color_secundario',  'Color secundario',   '#EF6C00'],
                            ['color_terciario',   'Color terciario',    '#1976D2'],
                            ['color_cuaternario', 'Color cuaternario',  '#E53935'],
                        ];
                        foreach ($colorFields as [$cName, $cLabel, $cDefault]):
                            $cVal = cms_e($institution[$cName] ?? $cDefault);
                        ?>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label"><?= cms_e($cLabel) ?></label>
                            <div class="color-field">
                                <input type="color" class="js-color-picker" value="<?= $cVal ?>" data-target="<?= cms_e($cName) ?>">
                                <input class="form-control" name="<?= cms_e($cName) ?>" id="<?= cms_e($cName) ?>" value="<?= $cVal ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="cfg-group">
                    <div class="cfg-group-title"><i class="bi bi-images"></i>Imágenes</div>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Logo principal (header)</label>
                            <div class="img-preview-wrap" id="previewLogoHeader">
                                <?php if (!empty($institution['logo_header'])): ?>
                                    <img src="<?= cms_e($institution['logo_header']) ?>" alt="Logo actual">
                                <?php else: ?>
                                    <div class="img-preview-placeholder"><i class="bi bi-image me-1"></i>Sin logo</div>
                                <?php endif; ?>
                            </div>
                            <input class="form-control" type="file" name="logo_header" accept="image/*" data-preview="#previewLogoHeader">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Logo footer</label>
                            <div class="img-preview-wrap" id="previewLogoFooter">
                                <?php if (!empty($institution['logo_footer'])): ?>
                                    <img src="<?= cms_e($institution['logo_footer']) ?>" alt="Logo footer">
                                <?php else: ?>
                                    <div class="img-preview-placeholder"><i class="bi bi-image me-1"></i>Sin logo</div>
                                <?php endif; ?>
                            </div>
                            <input class="form-control" type="file" name="logo_footer" accept="image/*" data-preview="#previewLogoFooter">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Favicon</label>
                            <div class="favicon-preview-wrap" id="previewFavicon">
                                <?php if (!empty($institution['favicon'])): ?>
                                    <img src="<?= cms_e($institution['favicon']) ?>" alt="Favicon">
                                <?php else: ?>
                                    <i class="bi bi-globe text-muted"></i>
                                <?php endif; ?>
                            </div>
                            <input class="form-control" type="file" name="favicon" accept="image/*,.ico" data-preview="#previewFavicon">
                            <div class="form-text">Se muestra en la pestaña del navegador.</div>
                        </div>
                    </div>
                </div>

                <div class="cfg-group">
                    <div class="cfg-group-title"><i class="bi bi-search"></i>SEO y pie de página</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Título SEO (meta_title)</label><input class="form-control" name="meta_title" value="<?= cms_e($institution['meta_title'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Meta descripción</label><input class="form-control" name="meta_description" value="<?= cms_e($institution['meta_description'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Texto del footer</label><input class="form-control" name="texto_footer" value="<?= cms_e($institution['texto_footer'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Copyright</label><input class="form-control" name="copyright" value="<?= cms_e($institution['copyright'] ?? '') ?>"></div>
                    </div>
                </div>

                <div class="mt-2">
                    <button class="btn btn-premium" type="submit"><i class="bi bi-save me-2"></i>Guardar configuración</button>
                </div>
            </form>
        </div>

        <div class="col-xl-4">
            <div class="section-card mb-0" style="position:sticky;top:80px;">
                <h3 class="mb-1">Vista rápida</h3>
                <p class="text-muted" style="font-size:.82rem;">Identidad institucional actual.</p>
                <?php if (!empty($institution['logo_header'])): ?>
                    <img src="<?= cms_e($institution['logo_header']) ?>" alt="Logo" class="qv-logo mb-3">
                    <hr class="my-3">
                <?php endif; ?>
                <div class="mb-1" style="font-weight:800;font-size:1rem;"><?= cms_e($institution['nombre'] ?? '') ?></div>
                <?php if (!empty($institution['eslogan'])): ?>
                    <div class="mb-2 text-muted" style="font-size:.82rem;font-style:italic;"><?= cms_e($institution['eslogan']) ?></div>
                <?php endif; ?>
                <div class="mb-1 text-muted" style="font-size:.83rem;"><i class="bi bi-envelope me-1"></i><?= cms_e($institution['email'] ?? '') ?></div>
                <div class="mb-3 text-muted" style="font-size:.83rem;"><i class="bi bi-telephone me-1"></i><?= cms_e($institution['telefono'] ?? '') ?></div>
                <div class="mb-2" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--adm-muted);">Colores</div>
                <div class="d-flex gap-2 flex-wrap mb-3" id="qvColors">
                    <?php foreach ($colorFields as [$cn, $cl, $cd]): ?>
                        <?php $cv = $institution[$cn] ?? $cd; ?>
                        <div class="qv-color" style="background:<?= cms_e($cv) ?>;" title="<?= cms_e($cl) ?>: <?= cms_e($cv) ?>"></div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($institution['favicon'])): ?>
                    <div class="mb-2" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--adm-muted);">Favicon</div>
                    <img src="<?= cms_e($institution['favicon']) ?>" alt="Favicon" style="width:32px;height:32px;border-radius:6px;object-fit:contain;border:1px solid var(--adm-border);">
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<script>
(function () {
    /* Color picker ↔ text input sync */
    document.querySelectorAll('.js-color-picker').forEach(function (picker) {
        var targetId = picker.dataset.target;
        var textInput = document.getElementById(targetId);
        if (!textInput) { return; }
        picker.addEventListener('input', function () { textInput.value = picker.value; updateQvColor(targetId, picker.value); });
        textInput.addEventListener('input', function () {
            if (/^#[0-9a-fA-F]{6}$/.test(textInput.value)) {
                picker.value = textInput.value;
                updateQvColor(targetId, textInput.value);
            }
        });
    });

    function updateQvColor(name, color) {
        var idx = ['color_primario','color_secundario','color_terciario','color_cuaternario'].indexOf(name);
        if (idx < 0) { return; }
        var swatches = document.querySelectorAll('#qvColors .qv-color');
        if (swatches[idx]) { swatches[idx].style.background = color; }
    }

    /* File input → image preview */
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            var previewSel = input.dataset.preview;
            var wrap = document.querySelector(previewSel);
            if (!wrap || !input.files || !input.files[0]) { return; }
            var reader = new FileReader();
            reader.onload = function (e) {
                wrap.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-height:72px;max-width:100%;object-fit:contain;">';
            };
            reader.readAsDataURL(input.files[0]);
        });
    });
})();
</script>
<?php endif; ?>

<?php
admin_render_layout_end([
    'extra_scripts' => <<<'HTML'
    <script>
        $(function () {
            var urlParams = new URLSearchParams(window.location.search);
            var savedParam = urlParams.get('saved');
            var activePanel = urlParams.get('panel') || 'contenedores';

            if (savedParam === 'config') {
                window.history.replaceState({}, document.title, window.location.pathname + '?panel=configuracion');
                adminNotify({ title: 'Configuración guardada', msg: 'La configuración institucional fue actualizada correctamente.', type: 'info' });
            }
            if (savedParam === 'submenu_created' || savedParam === 'submenu_updated') {
                window.history.replaceState({}, document.title, window.location.pathname + '?panel=' + encodeURIComponent(activePanel));
                adminNotify({
                    title: savedParam === 'submenu_created' ? 'Sub-menú creado' : 'Sub-menú actualizado',
                    msg: savedParam === 'submenu_created' ? 'El sub-menú fue creado correctamente.' : 'El sub-menú fue actualizado correctamente.',
                    type: 'info'
                });
            }
            if (savedParam === 'menu_deleted') {
                window.history.replaceState({}, document.title, window.location.pathname + '?panel=menus');
                adminNotify({
                    title: 'Menú eliminado',
                    msg: 'El menú y sus submenús asociados fueron eliminados.',
                    type: 'info'
                });
            }

            var dtConfig = {
                pageLength: 10,
                order: [[0, 'asc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
            };
            if ($('#contenedoresTable').length) {
                $('#contenedoresTable').DataTable(Object.assign({}, dtConfig, {
                    pageLength: 25,
                    paging: false,
                    ordering: false,
                    info: false
                }));
            }

            function updateContenedoresSummary(data) {
                var toggles = Array.from(document.querySelectorAll('.js-toggle-seccion'));
                var visible = toggles.filter(function (input) { return input.checked; }).length;
                var hidden = toggles.length - visible;
                var visibleEl = document.getElementById('cmsTotalVisible');
                var hiddenEl = document.getElementById('cmsTotalHidden');
                var lastEl = document.getElementById('cmsLastUpdate');

                if (visibleEl) { visibleEl.textContent = String(visible); }
                if (hiddenEl) { hiddenEl.textContent = String(hidden); }
                if (lastEl && data && data.updated_at_label) { lastEl.textContent = data.updated_at_label; }
            }

            $('.js-toggle-seccion').on('change', function () {
                var checkbox = this;
                var form = checkbox.closest('.js-toggle-seccion-form');
                var label = form.querySelector('.js-toggle-label');
                var row = checkbox.closest('tr');
                var formData = new FormData(form);
                var previousState = !checkbox.checked;

                checkbox.disabled = true;

                fetch('admin.php?panel=contenedores', {
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
                        updateContenedoresSummary(data);
                        if (row && data.updated_at_label) {
                            var updateCell = row.querySelector('.js-section-updated-cell .cms-last-update');
                            if (updateCell) {
                                updateCell.innerHTML = '<strong></strong><span></span>';
                                updateCell.querySelector('strong').textContent = data.updated_at_label;
                                updateCell.querySelector('span').textContent = data.updated_by || 'Usuario no registrado';
                            }
                        }
                    })
                    .catch(function (error) {
                        checkbox.checked = previousState;
                        label.textContent = previousState ? 'Activo' : 'Oculto';
                        adminConfirm({ title: 'Error', msg: error.message, type: 'danger', btnText: 'OK', onConfirm: function(){} });
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

            var previewModalEl = document.getElementById('previewModal');
            if (previewModalEl) {
                previewModalEl.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('previewFrame').src = 'about:blank';
                });
            }

            // --- Drag & drop contenedores movibles ---
            var contenedoresBody = document.querySelector('#contenedoresTable tbody');
            if (contenedoresBody && typeof Sortable !== 'undefined') {
                var contOrderTimeout = null;
                Sortable.create(contenedoresBody, {
                    animation: 160,
                    handle: '.cms-drag-handle',
                    draggable: '.cms-row-movable',
                    filter: '.cms-row-fixed',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onMove: function (evt) {
                        return !(evt.related && evt.related.classList.contains('cms-row-fixed'));
                    },
                    onEnd: function () {
                        clearTimeout(contOrderTimeout);
                        contOrderTimeout = setTimeout(function () {
                            var movableRows = Array.from(contenedoresBody.querySelectorAll('.cms-row-movable'));
                            var orderSlots = movableRows
                                .map(function (row) { return parseInt(row.getAttribute('data-order') || '0', 10); })
                                .filter(function (order) { return order > 0; })
                                .sort(function (a, b) { return a - b; });

                            var items = movableRows.map(function (row, index) {
                                return {
                                    id_seccion: parseInt(row.getAttribute('data-id') || '0', 10),
                                    orden: orderSlots[index] || (index + 1)
                                };
                            }).filter(function (item) { return item.id_seccion > 0; });

                            fetch('ajax/guardar_orden_contenedores.php', {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({ items: items })
                            })
                                .then(function (r) {
                                    return r.json().then(function (data) {
                                        if (!r.ok || !data.ok) {
                                            throw new Error(data.message || 'No se pudo guardar el nuevo orden.');
                                        }
                                        return data;
                                    });
                                })
                                .then(function () {
                                    movableRows.forEach(function (row, index) {
                                        var nextOrder = orderSlots[index] || (index + 1);
                                        row.setAttribute('data-order', String(nextOrder));
                                        var orderText = row.querySelector('.cms-order-cell > span:nth-child(2)');
                                        if (orderText) {
                                            orderText.textContent = String(nextOrder);
                                        }
                                    });
                                    adminNotify({ title: 'Orden guardado', msg: 'Los contenedores movibles fueron reordenados.', type: 'info', autoClose: 1800 });
                                })
                                .catch(function (error) {
                                    adminConfirm({ title: 'Error', msg: error.message, type: 'danger', btnText: 'OK', onConfirm: function(){} });
                                });
                        }, 350);
                    }
                });
            }

            // --- Drag & drop menús (cards) ---
            var menusListEl = document.getElementById('menusSortableList');
            if (menusListEl && typeof Sortable !== 'undefined') {
                var menusTimeout = null;
                Sortable.create(menusListEl, {
                    animation: 160,
                    handle: '.menu-drag-handle',
                    draggable: '.mnu-card',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onStart: function () {
                        document.querySelectorAll('[id^="menuSubs-"].show').forEach(function (el) {
                            bootstrap.Collapse.getOrCreateInstance(el).hide();
                        });
                    },
                    onEnd: function () {
                        clearTimeout(menusTimeout);
                        menusTimeout = setTimeout(function () {
                            var ids = Array.from(menusListEl.querySelectorAll('.mnu-card')).map(function (c) { return c.dataset.id; }).filter(Boolean);
                            var fd = new FormData();
                            fd.append('accion', 'reorder_menus');
                            ids.forEach(function (id) { fd.append('items[]', id); });
                            fetch('admin.php?panel=menus', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                .then(function (r) { return r.json(); })
                                .then(function (d) { if (d.ok) { adminNotify({ title: 'Orden guardado', msg: 'El nuevo orden del menú fue guardado.', type: 'info', autoClose: 1800 }); } })
                                .catch(function () {});
                        }, 400);
                    }
                });
            }

            // --- Drag & drop submenús (por grupo, dentro de cada acordeón) ---
            if (typeof Sortable !== 'undefined') {
                document.querySelectorAll('.submenusSortableTbody').forEach(function (tbody) {
                    var subTimeout = null;
                    Sortable.create(tbody, {
                        animation: 160,
                        handle: '.sub-drag-handle',
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        onEnd: function () {
                            clearTimeout(subTimeout);
                            subTimeout = setTimeout(function () {
                                var ids = Array.from(tbody.querySelectorAll('tr')).map(function (tr) { return tr.dataset.id; }).filter(Boolean);
                                var fd = new FormData();
                                fd.append('accion', 'reorder_submenus');
                                ids.forEach(function (id) { fd.append('items[]', id); });
                                fetch('admin.php?panel=menus', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                    .then(function (r) { return r.json(); })
                                    .then(function (d) { if (d.ok) { adminNotify({ title: 'Orden guardado', msg: 'El nuevo orden fue guardado.', type: 'info', autoClose: 1800 }); } })
                                    .catch(function () {});
                            }, 400);
                        }
                    });
                });
            }

            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.js-menu-delete');
                if (!btn) { return; }

                var idMenu = btn.dataset.id || '';
                var nombre = btn.dataset.nombre || 'este menú';
                var subCount = parseInt(btn.dataset.subCount || '0', 10);
                var msg = '¿Eliminar el menú «' + nombre + '»?';
                if (subCount > 0) {
                    msg += ' También se eliminarán ' + subCount + ' submenú' + (subCount === 1 ? '' : 's') + ' asociado' + (subCount === 1 ? '' : 's') + '.';
                }

                adminConfirm({
                    title: 'Eliminar menú',
                    msg: msg,
                    type: 'danger',
                    btnText: 'Eliminar',
                    onConfirm: function () {
                        var form = document.createElement('form');
                        form.method = 'post';
                        form.action = 'admin.php?panel=menus';
                        form.innerHTML = '<input type="hidden" name="accion" value="eliminar_menu"><input type="hidden" name="id_menu" value="' + idMenu.replace(/"/g, '&quot;') + '">';
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        // --- Toggle menú/submenú con confirmación AJAX ---
        (function () {
            var confirmModalEl = document.getElementById('confirmModal');

            document.addEventListener('change', function (e) {
                var el = e.target;
                var isMenu = el.classList.contains('js-menu-toggle');
                var isSub  = el.classList.contains('js-submenu-toggle');
                if (!isMenu && !isSub) { return; }

                var willActivate = el.checked;
                var label = el.closest('.form-check') && el.closest('.form-check').querySelector('.form-check-label');

                // Revertir hasta confirmar
                el.checked  = !willActivate;
                el.disabled = true;

                // Re-habilitar si cancela (modal se cierra sin confirmar)
                var onHide = function () {
                    confirmModalEl.removeEventListener('hidden.bs.modal', onHide);
                    el.disabled = false;
                };
                confirmModalEl.addEventListener('hidden.bs.modal', onHide);

                adminConfirm({
                    title: willActivate ? 'Activar' : 'Desactivar',
                    msg: (willActivate ? 'Activar' : 'Desactivar') + ' ' + (isMenu ? 'el menú' : 'el submenú') + ' «' + (el.dataset.nombre || '') + '»?',
                    type: 'info',
                    btnText: willActivate ? 'Activar' : 'Desactivar',
                    onConfirm: function () {
                        confirmModalEl.removeEventListener('hidden.bs.modal', onHide);

                        var fd = new FormData();
                        fd.append('accion', isMenu ? 'toggle_menu' : 'toggle_submenu');
                        fd.append(isMenu ? 'id_menu' : 'id_sub_menu', el.dataset.id);

                        fetch('admin.php', {
                            method: 'POST',
                            body: fd,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.ok) {
                                var active = data.estado === 1;
                                el.checked = active;
                                if (label) { label.textContent = active ? 'Activo' : 'Inactivo'; }
                                adminNotify({ title: 'Estado actualizado', msg: 'El cambio fue guardado.', type: 'info', autoClose: 1800 });
                            }
                        })
                        .catch(function () {})
                        .finally(function () { el.disabled = false; });
                    }
                });
            });
        })();

        function abrirModalMenu(btn) {
            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMenu'));
            var titulo = document.getElementById('modalMenuLabel');
            var idInput = document.getElementById('modalMenuId');
            var nombreInput = document.getElementById('modalMenuNombre');
            var urlInput = document.getElementById('modalMenuUrl');
            var iconoInput = document.getElementById('modalMenuIcono');
            var estadoCheck = document.getElementById('modalMenuEstado');

            if (btn) {
                titulo.textContent = 'Editar menú';
                idInput.value = btn.dataset.id || '0';
                nombreInput.value = btn.dataset.nombre || '';
                urlInput.value = btn.dataset.url || '';
                iconoInput.value = btn.dataset.icono || '';
                estadoCheck.checked = btn.dataset.estado === '1';
            } else {
                titulo.textContent = 'Nuevo menú';
                idInput.value = '0';
                nombreInput.value = '';
                urlInput.value = '';
                iconoInput.value = '';
                estadoCheck.checked = true;
            }
            modal.show();
        }

        function abrirModalSubmenu(btn, defaultIdMenu) {
            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSubmenu'));
            var titulo = document.getElementById('modalSubmenuLabel');
            var idInput = document.getElementById('modalSubmenuId');
            var idMenuSelect = document.getElementById('modalSubmenuIdMenu');
            var nombreInput = document.getElementById('modalSubmenuNombre');
            var urlInput = document.getElementById('modalSubmenuUrl');
            var iconoInput = document.getElementById('modalSubmenuIcono');
            var estadoCheck = document.getElementById('modalSubmenuEstado');

            if (btn) {
                titulo.textContent = 'Editar submenú';
                idInput.value = btn.dataset.id || '0';
                idMenuSelect.value = btn.dataset.idMenu || '';
                nombreInput.value = btn.dataset.nombre || '';
                urlInput.value = btn.dataset.url || '';
                iconoInput.value = btn.dataset.icono || '';
                estadoCheck.checked = btn.dataset.estado === '1';
            } else {
                titulo.textContent = 'Nuevo submenú';
                idInput.value = '0';
                idMenuSelect.value = defaultIdMenu ? String(defaultIdMenu) : '';
                nombreInput.value = '';
                urlInput.value = '';
                iconoInput.value = '';
                estadoCheck.checked = true;
            }
            modal.show();
        }
    </script>
HTML,
]);
