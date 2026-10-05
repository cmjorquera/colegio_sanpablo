<?php
session_start();

require_once __DIR__ . '/includes/admin_permissions.php';

$sesionAdminValida = false;
try {
    $sesionAdminValida = !empty($_SESSION['admin_logged']) && admin_cuenta_tiene_acceso(admin_usuario_actual());
} catch (Throwable $exception) {
    error_log('admin.php: no fue posible validar la sesion administrativa.');
}

if (!$sesionAdminValida) {
    require __DIR__ . '/includes/admin_login.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['panel'])) {
    header('Location: admin.php?panel=contenedores');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_once __DIR__ . '/includes/funciones_auditoria.php';

$db = cms_get_connection();
$institutionId = cms_get_institution_id($db);
cms_sync_sections($db, $institutionId);

$panel = $_GET['panel'] ?? 'contenedores';
if ($panel === 'calendario') {
    cms_redirect('admin_calendario.php');
}
if ($panel === 'permisos') {
    cms_redirect('admin_permisos.php');
}
$sectionId = isset($_GET['section']) ? (int) $_GET['section'] : 0;
$panelPermissionMap = [
    'dashboard' => 'dashboard',
    'contenedores' => 'contenedores',
    'menus' => 'menus_publicos',
    'submenus' => 'submenus_publicos',
    'configuracion' => 'datos_institucionales',
    'noticias' => 'noticias',
    'logos-colores' => 'logos_colores',
    'redes-sociales' => 'redes_sociales',
    'perfiles' => 'perfiles',
    'configuracion-sistema' => 'configuracion_general',
];
$activePermissionKey = $panelPermissionMap[$panel] ?? 'contenedores';

try {
    admin_requerir_permiso($activePermissionKey, 'ver');
} catch (Throwable $e) {
    cms_set_flash('danger', $e->getMessage());
    cms_redirect('admin.php?panel=dashboard');
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['accion'] ?? '';
        $sectionId = (int) ($_POST['id_seccion'] ?? $sectionId);
        $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

        $permissionChecks = [
            'toggle_seccion' => ['contenedores', 'editar'],
            'guardar_menu' => ['menus_publicos', ((int) ($_POST['id_menu'] ?? 0) > 0 ? 'editar' : 'crear')],
            'toggle_menu' => ['menus_publicos', 'editar'],
            'eliminar_menu' => ['menus_publicos', 'eliminar'],
            'guardar_submenu' => ['submenus_publicos', ((int) ($_POST['id_sub_menu'] ?? 0) > 0 ? 'editar' : 'crear')],
            'toggle_submenu' => ['submenus_publicos', 'editar'],
            'reorder_menus' => ['menus_publicos', 'editar'],
            'reorder_submenus' => ['submenus_publicos', 'editar'],
            'reorder_submenu_media' => ['submenus_publicos', 'editar'],
            'add_submenu_media' => ['submenus_publicos', 'editar'],
            'delete_submenu_media' => ['submenus_publicos', 'editar'],
            'toggle_submenu_media' => ['submenus_publicos', 'editar'],
            'guardar_institucion' => ['datos_institucionales', 'editar'],
        ];
        if (isset($permissionChecks[$action])) {
            [$permissionKey, $permissionAction] = $permissionChecks[$action];
            admin_requerir_permiso($permissionKey, $permissionAction);
        }

        if ($action === 'toggle_seccion' && $sectionId > 0) {
            $datosAntes = obtenerRegistroAuditoria($db, 'seccion', 'id_seccion', $sectionId);
            $nextVisible = ((string) ($_POST['visible'] ?? '') === 'si') ? 'si' : 'no';
            cms_set_section_visibility($db, $sectionId, $nextVisible);
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
            if ($isAjax) {
                $savedSubmenu = cms_get_submenu($db, $savedSubMenuId) ?? [];
                $savedSubmenu['pagina_media'] = cms_list_submenu_page_media($db, $savedSubMenuId);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'ok' => true,
                    'message' => 'Cambios guardados correctamente',
                    'submenu' => $savedSubmenu,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            $returnPanel = in_array($_POST['return_panel'] ?? '', ['menus', 'submenus'], true) ? $_POST['return_panel'] : 'submenus';
            $savedStatus = $idSubMenuAudit > 0 ? 'submenu_updated' : 'submenu_created';
            $activeSubmenuTab = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($_POST['submenu_active_tab'] ?? 'submenuTabDatos'));
            cms_redirect('admin.php?panel=' . $returnPanel . '&saved=' . $savedStatus . '&submenu=' . $savedSubMenuId . '&keep_submenu=1&submenu_tab=' . $activeSubmenuTab);
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

        if ($action === 'reorder_submenu_media') {
            $idSubMenuMedia = (int) ($_POST['id_sub_menu'] ?? 0);
            $ids = array_map('intval', (array) ($_POST['items'] ?? []));
            if ($idSubMenuMedia <= 0) {
                throw new RuntimeException('No se pudo identificar el submenú.');
            }
            cms_reorder_submenu_page_media($db, $idSubMenuMedia, $ids);
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['ok' => true]);
                exit;
            }
            cms_redirect('admin.php?panel=submenus');
        }

        if ($action === 'add_submenu_media') {
            $idSubMenuMedia = (int) ($_POST['id_sub_menu'] ?? 0);
            $media = cms_add_submenu_gallery_image($db, $idSubMenuMedia, $_POST);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => true, 'media' => $media], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        if ($action === 'delete_submenu_media') {
            $idSubMenuMedia = (int) ($_POST['id_sub_menu'] ?? 0);
            $idMedia = (int) ($_POST['id_media'] ?? 0);
            cms_delete_submenu_gallery_media($db, $idSubMenuMedia, $idMedia);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => true, 'id_media' => $idMedia], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($action === 'toggle_submenu_media') {
            $idSubMenuMedia = (int) ($_POST['id_sub_menu'] ?? 0);
            $idMedia = (int) ($_POST['id_media'] ?? 0);
            $visible = cms_toggle_submenu_gallery_media($db, $idSubMenuMedia, $idMedia);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => true, 'id_media' => $idMedia, 'visible' => $visible], JSON_UNESCAPED_UNICODE);
            exit;
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
$menuSummary = [
    'total_menus' => count($menus),
    'total_submenus' => count($submenus),
    'total_active' => 0,
    'last_update' => null,
    'last_user' => '',
];
foreach ($menus as $menuSummaryRow) {
    if ((int) ($menuSummaryRow['estado'] ?? 0) === 1) {
        $menuSummary['total_active']++;
    }
    $updatedCandidate = trim((string) ($menuSummaryRow['actualizado_en'] ?? ''));
    $createdCandidate = trim((string) ($menuSummaryRow['fecha_creacion'] ?? ''));
    $candidate = $updatedCandidate !== '' ? $updatedCandidate : $createdCandidate;
    if ($candidate !== '' && ($menuSummary['last_update'] === null || strtotime($candidate) > strtotime((string) $menuSummary['last_update']))) {
        $menuSummary['last_update'] = $candidate;
        $menuUser = trim((string) (($menuSummaryRow['actualizado_por_nombre'] ?? '') . ' ' . ($menuSummaryRow['actualizado_por_apellido'] ?? '')));
        if ($menuUser === '') {
            $menuUser = (string) ($menuSummaryRow['actualizado_por_usuario'] ?? $menuSummaryRow['actualizado_por_email'] ?? '');
        }
        $menuSummary['last_user'] = $menuUser;
    }
}
unset($menuSummaryRow);
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
$adminPanelPermissions = [
    'contenedores' => [
        'crear' => admin_tiene_permiso('contenedores', 'crear'),
        'editar' => admin_tiene_permiso('contenedores', 'editar'),
        'eliminar' => admin_tiene_permiso('contenedores', 'eliminar'),
    ],
    'menus' => [
        'crear' => admin_tiene_permiso('menus_publicos', 'crear'),
        'editar' => admin_tiene_permiso('menus_publicos', 'editar'),
        'eliminar' => admin_tiene_permiso('menus_publicos', 'eliminar'),
    ],
    'submenus' => [
        'crear' => admin_tiene_permiso('submenus_publicos', 'crear'),
        'editar' => admin_tiene_permiso('submenus_publicos', 'editar'),
        'eliminar' => admin_tiene_permiso('submenus_publicos', 'eliminar'),
    ],
    'configuracion' => [
        'editar' => admin_tiene_permiso('datos_institucionales', 'editar'),
    ],
];
$adminPanelPermissionsJson = json_encode($adminPanelPermissions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

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
    'extra_head' => '<link rel="stylesheet" href="assets/css/admin_dashboard.css"><link rel="stylesheet" href="assets/css/submenu_historia_admin.css">',
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
        <div class="table-responsive cms-list-shell">
            <table class="table table-modern cms-list-table align-middle" id="contenedoresTable">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Contenedor</th>
                        <th>Qué controla</th>
                        <th>Estado</th>
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
                            $sectionIcon = trim((string) ($section['icono_admin'] ?? ''));
                            if ($sectionIcon === '') {
                                $sectionIcon = 'bi-layout-text-window';
                            }
                            $updatedAt = trim((string) ($section['actualizado_en'] ?? ''));
                            $updatedUser = trim((string) (($section['actualizado_por_nombre'] ?? '') . ' ' . ($section['actualizado_por_apellido'] ?? '')));
                            if ($updatedUser === '') {
                                $updatedUser = (string) ($section['actualizado_por_usuario'] ?? $section['actualizado_por_email'] ?? '');
                            }
                            $sectionStatus = strtolower(trim((string) ($section['estado'] ?? 'activo')));
                            $statusLabels = [
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                                'borrador' => 'Borrador',
                            ];
                            $statusClasses = [
                                'activo' => 'success',
                                'inactivo' => 'dark',
                                'borrador' => 'warning',
                            ];
                            $statusLabel = $statusLabels[$sectionStatus] ?? ucfirst($sectionStatus !== '' ? $sectionStatus : 'activo');
                            $statusClass = $statusClasses[$sectionStatus] ?? 'dark';
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
                                <div class="cms-item-title">
                                    <span class="cms-item-icon"><i class="bi <?= cms_e($sectionIcon) ?>"></i></span>
                                    <span class="cms-item-copy">
                                        <strong><?= cms_e($section['titulo_admin']) ?></strong>
                                        <code><?= cms_e($sectionName) ?></code>
                                    </span>
                                </div>
                            </td>
                            <td><div class="cms-muted-text"><?= cms_e($section['observacion'] ?? '') ?></div></td>
                            <td><span class="badge-soft <?= cms_e($statusClass) ?>"><?= cms_e($statusLabel) ?></span></td>
                            <td>
                                <div class="cms-last-update">
                                    <?php if ($updatedAt !== ''): ?>
                                        <strong><i class="bi bi-calendar3"></i><?= cms_e(date('d-m-Y H:i', strtotime($updatedAt))) ?></strong>
                                        <span><i class="bi bi-person"></i><?= $updatedUser !== '' ? cms_e($updatedUser) : 'Usuario no registrado' ?></span>
                                    <?php else: ?>
                                        <strong><i class="bi bi-calendar3"></i>Sin registro</strong>
                                        <span><i class="bi bi-person"></i>Sin modificación real</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <form method="post" class="m-0 js-toggle-seccion-form">
                                    <input type="hidden" name="accion" value="toggle_seccion">
                                    <input type="hidden" name="id_seccion" value="<?= (int) $section['id_seccion'] ?>">
                                    <input type="hidden" name="visible" class="js-toggle-seccion-value" value="<?= ($section['visible'] ?? '') === 'si' ? 'si' : 'no' ?>">
                                    <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                        <input class="form-check-input js-toggle-seccion" type="checkbox" role="switch" <?= ($section['visible'] ?? '') === 'si' ? 'checked' : '' ?><?= $adminPanelPermissions['contenedores']['editar'] ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>>
                                        <label class="form-check-label js-toggle-label"><?= ($section['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?></label>
                                    </div>
                                </form>
                            </td>
                            <td class="cell-actions">
                                <div class="cms-row-actions">
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

    <div class="cms-summary-grid">
        <div class="stat-card"><div class="stat-icon blue"><i class="bi bi-list-ul"></i></div><div class="stat-body"><strong><?= (int) $menuSummary['total_menus'] ?></strong><span>Menús principales</span><small>Total de menús configurados</small></div></div>
        <div class="stat-card"><div class="stat-icon green"><i class="bi bi-diagram-3"></i></div><div class="stat-body"><strong><?= (int) $menuSummary['total_submenus'] ?></strong><span>Submenús</span><small>Total de todos los submenús</small></div></div>
        <div class="stat-card"><div class="stat-icon amber"><i class="bi bi-check-circle"></i></div><div class="stat-body"><strong><?= (int) $menuSummary['total_active'] ?></strong><span>Menús activos</span><small>Menús visibles en el sitio</small></div></div>
        <div class="stat-card"><div class="stat-icon purple"><i class="bi bi-calendar3"></i></div><div class="stat-body"><strong style="font-size:1.05rem;"><?= $menuSummary['last_update'] ? cms_e(date('d/m/Y H:i', strtotime((string) $menuSummary['last_update']))) : 'Sin registro' ?></strong><span>Última modificación general</span><small><?= $menuSummary['last_user'] !== '' ? 'Por ' . cms_e($menuSummary['last_user']) : 'Sin usuario registrado' ?></small></div></div>
    </div>
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
            <div class="mnu-head" aria-hidden="true">
                <span></span>
                <span>Orden</span>
                <span>Menú</span>
                <span>Submenús</span>
                <span>Estado</span>
                <span>Última modificación</span>
                <span>Acciones</span>
                <span></span>
            </div>
            <?php foreach ($menus as $menu): ?>
                <?php
                    $menuSubs = $submenusByMenu[$menu['id_menu']] ?? [];
                    $subCount = (int) ($menu['total_submenus'] ?? count($menuSubs));
                    $menuName = (string) ($menu['nombre'] ?? '');
                    $menuIcon = cms_menu_icon_class($menuName, $menu['icono'] ?? '');
                    $menuUrl = cms_menu_display_url($menuName, $menu['url'] ?? '');
                    $menuUpdatedAt = trim((string) ($menu['actualizado_en'] ?? ''));
                    $menuUpdatedUser = trim((string) (($menu['actualizado_por_nombre'] ?? '') . ' ' . ($menu['actualizado_por_apellido'] ?? '')));
                    if ($menuUpdatedUser === '') {
                        $menuUpdatedUser = (string) ($menu['actualizado_por_usuario'] ?? $menu['actualizado_por_email'] ?? '');
                    }
                ?>
                <div class="mnu-card" data-id="<?= (int) $menu['id_menu'] ?>">
                    <div class="mnu-row">
                        <div><i class="bi bi-grip-vertical menu-drag-handle" style="cursor:grab;color:var(--adm-muted);font-size:1.15rem;"></i></div>
                        <div class="mnu-order"><?= (int) $menu['orden'] ?></div>
                        <div class="mnu-name">
                            <span class="mnu-icon"><i class="<?= cms_e($menuIcon) ?>"></i></span>
                            <span><?= cms_e($menuName) ?></span>
                        </div>
                        <div class="mnu-submenus">
                            <span class="mnu-sub-badge"><i class="bi bi-folder2-open"></i><?= $subCount ?> submenú<?= $subCount === 1 ? '' : 's' ?></span>
                        </div>
                        <div class="mnu-state">
                            <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                <input class="form-check-input js-menu-toggle" type="checkbox" role="switch"
                                    data-id="<?= (int) $menu['id_menu'] ?>"
                                    data-nombre="<?= cms_e($menuName) ?>"
                                    <?= (int) $menu['estado'] === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" style="font-size:.84rem;"><?= (int) $menu['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                            </div>
                        </div>
                        <div class="mnu-updated">
                            <?php if ($menuUpdatedAt !== ''): ?>
                                <strong><i class="bi bi-calendar3"></i><?= cms_e(date('d/m/Y H:i', strtotime($menuUpdatedAt))) ?></strong>
                                <span><i class="bi bi-person"></i><?= $menuUpdatedUser !== '' ? cms_e($menuUpdatedUser) : 'Usuario no registrado' ?></span>
                            <?php else: ?>
                                <strong><i class="bi bi-calendar3"></i>Sin registro</strong>
                                <span><i class="bi bi-person"></i>Sin modificación real</span>
                            <?php endif; ?>
                        </div>
                        <div class="mnu-actions">
                            <button class="btn-icon edit" title="Editar menú"
                                data-id="<?= (int) $menu['id_menu'] ?>"
                                data-nombre="<?= cms_e($menuName) ?>"
                                data-url="<?= cms_e($menu['url']) ?>"
                                data-icono="<?= cms_e($menu['icono']) ?>"
                                data-estado="<?= (int) $menu['estado'] ?>"
                                onclick="abrirModalMenu(this)">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn-icon delete js-menu-delete" title="Eliminar menú"
                                data-id="<?= (int) $menu['id_menu'] ?>"
                                data-nombre="<?= cms_e($menuName) ?>"
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
                            <thead><tr><th></th><th>Submenú</th><th>Contenido</th><th>Multimedia</th><th>Estado</th><th>Última modificación</th><th>Acciones</th></tr></thead>
                            <tbody class="submenusSortableTbody" data-id-menu="<?= (int) $menu['id_menu'] ?>">
                                <?php foreach ($menuSubs as $sub): ?>
                                <?php
                                    $contentState = (string) ($sub['estado_contenido'] ?? 'vacio');
                                    $contentLabels = ['completo'=>'Completo','incompleto'=>'Incompleto','vacio'=>'Vacío','externo'=>'Enlace externo'];
                                    $imageCount = (int) ($sub['media_imagenes'] ?? 0);
                                    $videoCount = (int) ($sub['media_videos'] ?? 0);
                                    $mediaParts = [];
                                    if (!empty($sub['media_tiene_hero'])) { $mediaParts[] = 'Hero'; }
                                    if ($imageCount > 0) { $mediaParts[] = $imageCount . ' imagen' . ($imageCount === 1 ? '' : 'es'); }
                                    if ($videoCount > 0) { $mediaParts[] = $videoCount . ' video' . ($videoCount === 1 ? '' : 's'); }
                                    $mediaSummary = $mediaParts ? implode(' · ', $mediaParts) : 'Sin multimedia';
                                    $subUpdatedAt = trim((string) ($sub['editorial_actualizado_en'] ?? ''));
                                    $subUpdatedUser = trim((string) ($sub['editorial_actualizado_usuario'] ?? ''));
                                ?>
                                <tr data-id="<?= (int) $sub['id_sub_menu'] ?>">
                                    <td class="sub-drag"><i class="bi bi-grip-vertical sub-drag-handle" style="cursor:grab;color:var(--adm-muted);font-size:1rem;"></i></td>
                                    <td class="sub-name"><?= cms_e($sub['nombre']) ?><?php if ($contentState === 'externo'): ?><span class="submenu-external-badge">Enlace externo</span><?php endif; ?></td>
                                    <td><span class="submenu-content-badge is-<?= cms_e($contentState) ?>"><?= cms_e($contentLabels[$contentState] ?? 'Vacío') ?></span></td>
                                    <td class="submenu-media-summary"><?= cms_e($mediaSummary) ?></td>
                                    <td class="sub-toggle">
                                        <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                            <input class="form-check-input js-submenu-toggle" type="checkbox" role="switch"
                                                data-id="<?= (int) $sub['id_sub_menu'] ?>"
                                                data-nombre="<?= cms_e($sub['nombre']) ?>"
                                                <?= (int) $sub['estado'] === 1 ? 'checked' : '' ?>>
                                            <label class="form-check-label" style="font-size:.82rem;"><?= (int) $sub['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                                        </div>
                                    </td>
                                    <td class="submenu-updated"><?php if ($subUpdatedAt !== ''): ?><strong><?= cms_e(date('d/m/Y H:i', strtotime($subUpdatedAt))) ?></strong><span><?= $subUpdatedUser !== '' ? cms_e($subUpdatedUser) : 'Usuario no registrado' ?></span><?php else: ?><span>Sin modificación</span><?php endif; ?></td>
                                    <td class="sub-edit">
                                        <button class="btn-icon edit" title="Editar submenú"
                                            data-id="<?= (int) $sub['id_sub_menu'] ?>"
                                            data-id-menu="<?= (int) $sub['id_menu'] ?>"
                                            data-nombre="<?= cms_e($sub['nombre']) ?>"
                                            data-url="<?= cms_e($sub['url']) ?>"
                                            data-icono="<?= cms_e($sub['icono']) ?>"
                                            data-estado="<?= (int) $sub['estado'] ?>"
                                            data-pagina-titulo="<?= cms_e($sub['pagina_titulo'] ?? '') ?>"
                                            data-pagina-bajada="<?= cms_e($sub['pagina_bajada'] ?? '') ?>"
                                            data-pagina-contenido="<?= cms_e($sub['pagina_contenido'] ?? '') ?>"
                                            data-pagina-imagen-hero="<?= cms_e($sub['pagina_imagen_hero'] ?? '') ?>"
                                            data-pagina-hero-video-url="<?= cms_e($sub['pagina_hero_video_url'] ?? '') ?>"
                                            data-pagina-hero-video-archivo="<?= cms_e($sub['pagina_hero_video_archivo'] ?? '') ?>"
                                            data-pagina-imagen-secundaria="<?= cms_e($sub['pagina_imagen_secundaria'] ?? '') ?>"
                                            data-pagina-video-url="<?= cms_e($sub['pagina_video_url'] ?? '') ?>"
                                            data-pagina-video-archivo="<?= cms_e($sub['pagina_video_archivo'] ?? '') ?>"
                                            data-pagina-boton-texto="<?= cms_e($sub['pagina_boton_texto'] ?? '') ?>"
                                            data-pagina-boton-url="<?= cms_e($sub['pagina_boton_url'] ?? '') ?>"
                                            data-pagina-meta-title="<?= cms_e($sub['pagina_meta_title'] ?? '') ?>"
                                            data-pagina-meta-description="<?= cms_e($sub['pagina_meta_description'] ?? '') ?>"
                                            data-pagina-media="<?= cms_e(json_encode($sub['pagina_media'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
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
        <div class="modal-dialog modal-dialog-centered modal-xl">
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

    <!-- Offcanvas Submenú -->
    <div class="offcanvas offcanvas-bottom submenu-editor-canvas" id="modalSubmenu" tabindex="-1" aria-labelledby="modalSubmenuLabel">
                <form method="post" id="formModalSubmenu" enctype="multipart/form-data" class="submenu-editor-form">
                    <input type="hidden" name="accion" value="guardar_submenu">
                    <input type="hidden" name="return_panel" value="menus">
                    <input type="hidden" name="id_sub_menu" id="modalSubmenuId" value="0">
                    <input type="hidden" name="submenu_active_tab" id="modalSubmenuActiveTab" value="submenuTabDatos">
                    <div class="offcanvas-header submenu-editor-header">
                        <h5 class="modal-title" id="modalSubmenuLabel">Submenú</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                    </div>
                    <div class="offcanvas-body submenu-editor-body">
                        <ul class="submenu-editor-tabs" role="tablist">
                            <li role="presentation"><button class="active" type="button" data-bs-toggle="tab" data-bs-target="#submenuTabDatos" role="tab">Datos</button></li>
                            <li role="presentation"><button type="button" data-bs-toggle="tab" data-bs-target="#submenuTabContenido" role="tab">Página</button></li>
                            <li role="presentation"><button type="button" data-bs-toggle="tab" data-bs-target="#submenuTabMedia" role="tab">Multimedia</button></li>
                        </ul>
                        <input type="hidden" name="pagina_meta_title" id="modalPaginaMetaTitle">
                        <input type="hidden" name="pagina_meta_description" id="modalPaginaMetaDescription">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="submenuTabDatos" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Menú padre <span class="text-danger">*</span></label>
                                        <select class="form-select" name="id_menu" id="modalSubmenuIdMenu" required>
                                            <option value="">Seleccione</option>
                                            <?php foreach ($menus as $menu): ?>
                                                <option value="<?= (int) $menu['id_menu'] ?>"><?= cms_e($menu['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input class="form-control" name="nombre" id="modalSubmenuNombre" required>
                                    </div>
                                    <input type="hidden" name="url" id="modalSubmenuUrl">
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="estado" id="modalSubmenuEstado">
                                            <label class="form-check-label" for="modalSubmenuEstado">Activo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="submenuTabContenido" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-lg-5">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Título de página</label>
                                                <input class="form-control" name="pagina_titulo" id="modalPaginaTitulo" placeholder="Si queda vacío usa el nombre del submenú">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Bajada</label>
                                                <input class="form-control" name="pagina_bajada" id="modalPaginaBajada" placeholder="Resumen breve para el hero">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Texto del botón</label>
                                                <input class="form-control" name="pagina_boton_texto" id="modalPaginaBotonTexto" placeholder="Opcional">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">URL del botón</label>
                                                <input class="form-control" name="pagina_boton_url" id="modalPaginaBotonUrl" placeholder="#contacto">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Contenido</label>
                                        <textarea class="form-control h-100 js-submenu-editor" name="pagina_contenido" id="modalPaginaContenido" rows="12" placeholder="Texto principal de la página"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="submenuTabMedia" role="tabpanel">
                                <div class="submenu-media-grid">
                                    <div class="submenu-media-card submenu-media-primary">
                                        <label class="form-label">Imagen/video principal</label>
                                        <div class="submenu-hero-mode">
                                            <label><input type="radio" name="pagina_hero_tipo" value="imagen" checked><span><i class="bi bi-image"></i> Imagen</span></label>
                                            <label><input type="radio" name="pagina_hero_tipo" value="video"><span><i class="bi bi-play-btn"></i> Video</span></label>
                                        </div>
                                        <div data-hero-panel="imagen">
                                            <div class="submenu-media-preview" id="modalPaginaHeroPreview">Sin imagen hero</div>
                                            <input class="form-control js-submenu-image-preview" type="file" name="pagina_imagen_hero" accept="image/*" data-preview-target="modalPaginaHeroPreview">
                                            <small class="submenu-media-note" id="modalPaginaHeroActual"></small>
                                            <label class="submenu-media-danger js-delete-hero-image"><input class="form-check-input" type="checkbox" name="delete_pagina_imagen_hero" value="1"> Eliminar imagen principal</label>
                                        </div>
                                        <div data-hero-panel="video">
                                            <div class="submenu-media-preview" id="modalPaginaHeroVideoPreview"><span>Sin video principal</span></div>
                                            <label class="form-label">URL video hero</label>
                                            <input class="form-control mb-2" name="pagina_hero_video_url" id="modalPaginaHeroVideoUrl" placeholder="YouTube, Vimeo o URL directa">
                                            <label class="form-label">Video hero local</label>
                                            <input class="form-control" type="file" name="pagina_hero_video_archivo" accept="video/mp4,video/webm,video/quicktime">
                                            <small class="submenu-media-note" id="modalPaginaHeroVideoActual"></small>
                                            <label class="submenu-media-danger js-delete-hero-video"><input class="form-check-input" type="checkbox" name="delete_pagina_hero_video" value="1"> Eliminar video principal</label>
                                        </div>
                                    </div>
                                    <div class="submenu-media-card submenu-media-gallery">
                                        <div class="small fw-semibold mb-2">Galería</div>
                                        <div class="submenu-gallery-add">
                                            <div class="submenu-media-preview" id="modalPaginaGaleriaNuevaPreview">Sin imagen seleccionada</div>
                                            <div class="submenu-gallery-add-fields">
                                                <input class="form-control js-submenu-image-preview" type="file" name="pagina_galeria_imagen" accept="image/*" data-preview-target="modalPaginaGaleriaNuevaPreview">
                                                <input class="form-control" name="pagina_galeria_titulo" placeholder="Título opcional">
                                                <button type="button" class="btn btn-premium js-submenu-gallery-add"><i class="bi bi-plus-lg"></i> Agregar</button>
                                            </div>
                                        </div>
                                        <div class="small fw-semibold mt-3 mb-2">Galería actual</div>
                                        <div id="modalPaginaMediaActual" class="submenu-gallery-preview"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="submenu-editor-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="offcanvas">Cancelar</button>
                        <button type="submit" class="btn btn-premium">Guardar</button>
                    </div>
                </form>
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
                                <th>Submenú</th><th>Contenido</th><th>Multimedia</th><th>Estado</th><th>Última modificación</th><th style="width:56px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="submenusSortableTbody" data-id-menu="<?= (int) $menuPadre['id_menu'] ?>">
                            <?php foreach ($submenusByMenu[$menuPadre['id_menu']] as $submenu): ?>
                                <?php
                                    $contentState = (string) ($submenu['estado_contenido'] ?? 'vacio');
                                    $contentLabels = ['completo'=>'Completo','incompleto'=>'Incompleto','vacio'=>'Vacío','externo'=>'Enlace externo'];
                                    $mediaParts=[]; $imageCount=(int)($submenu['media_imagenes']??0); $videoCount=(int)($submenu['media_videos']??0);
                                    if(!empty($submenu['media_tiene_hero'])){$mediaParts[]='Hero';}
                                    if($imageCount){$mediaParts[]=$imageCount.' imagen'.($imageCount===1?'':'es');}
                                    if($videoCount){$mediaParts[]=$videoCount.' video'.($videoCount===1?'':'s');}
                                    $subUpdatedAt=trim((string)($submenu['editorial_actualizado_en']??''));
                                ?>
                                <tr data-id="<?= (int) $submenu['id_sub_menu'] ?>">
                                    <td><i class="bi bi-grip-vertical drag-handle" style="cursor:grab;color:var(--adm-muted);font-size:1.15rem;"></i></td>
                                    <td class="sub-name"><strong><?= cms_e($submenu['nombre']) ?></strong><?php if($contentState==='externo'): ?><span class="submenu-external-badge">Enlace externo</span><?php endif; ?></td>
                                    <td><span class="submenu-content-badge is-<?= cms_e($contentState) ?>"><?= cms_e($contentLabels[$contentState]??'Vacío') ?></span></td>
                                    <td class="submenu-media-summary"><?= cms_e($mediaParts?implode(' · ',$mediaParts):'Sin multimedia') ?></td>
                                    <td class="sub-toggle">
                                            <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                                <input class="form-check-input js-submenu-toggle" data-id="<?= (int)$submenu['id_sub_menu'] ?>" data-nombre="<?= cms_e($submenu['nombre']) ?>" type="checkbox" role="switch" <?= (int) $submenu['estado'] === 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label"><?= (int) $submenu['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                                            </div>
                                    </td>
                                    <td class="submenu-updated"><?php if($subUpdatedAt!==''): ?><strong><?= cms_e(date('d/m/Y H:i',strtotime($subUpdatedAt))) ?></strong><span><?= cms_e($submenu['editorial_actualizado_usuario']??'Usuario no registrado') ?></span><?php else: ?><span>Sin modificación</span><?php endif; ?></td>
                                    <td>
                                        <button class="btn-icon edit" title="Editar"
                                            data-id="<?= (int) $submenu['id_sub_menu'] ?>"
                                            data-id-menu="<?= (int) $submenu['id_menu'] ?>"
                                            data-nombre="<?= cms_e($submenu['nombre']) ?>"
                                            data-url="<?= cms_e($submenu['url']) ?>"
                                            data-icono="<?= cms_e($submenu['icono']) ?>"
                                            data-estado="<?= (int) $submenu['estado'] ?>"
                                            data-pagina-titulo="<?= cms_e($submenu['pagina_titulo'] ?? '') ?>"
                                            data-pagina-bajada="<?= cms_e($submenu['pagina_bajada'] ?? '') ?>"
                                            data-pagina-contenido="<?= cms_e($submenu['pagina_contenido'] ?? '') ?>"
                                            data-pagina-imagen-hero="<?= cms_e($submenu['pagina_imagen_hero'] ?? '') ?>"
                                            data-pagina-hero-video-url="<?= cms_e($submenu['pagina_hero_video_url'] ?? '') ?>"
                                            data-pagina-hero-video-archivo="<?= cms_e($submenu['pagina_hero_video_archivo'] ?? '') ?>"
                                            data-pagina-imagen-secundaria="<?= cms_e($submenu['pagina_imagen_secundaria'] ?? '') ?>"
                                            data-pagina-video-url="<?= cms_e($submenu['pagina_video_url'] ?? '') ?>"
                                            data-pagina-video-archivo="<?= cms_e($submenu['pagina_video_archivo'] ?? '') ?>"
                                            data-pagina-boton-texto="<?= cms_e($submenu['pagina_boton_texto'] ?? '') ?>"
                                            data-pagina-boton-url="<?= cms_e($submenu['pagina_boton_url'] ?? '') ?>"
                                            data-pagina-meta-title="<?= cms_e($submenu['pagina_meta_title'] ?? '') ?>"
                                            data-pagina-meta-description="<?= cms_e($submenu['pagina_meta_description'] ?? '') ?>"
                                            data-pagina-media="<?= cms_e(json_encode($submenu['pagina_media'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
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

    <!-- Offcanvas Submenú -->
    <div class="offcanvas offcanvas-bottom submenu-editor-canvas" id="modalSubmenu" tabindex="-1" aria-labelledby="modalSubmenuLabel">
                <form method="post" id="formModalSubmenu" enctype="multipart/form-data" class="submenu-editor-form">
                    <input type="hidden" name="accion" value="guardar_submenu">
                    <input type="hidden" name="id_sub_menu" id="modalSubmenuId" value="0">
                    <input type="hidden" name="submenu_active_tab" id="modalSubmenuActiveTab" value="submenuTabDatos">
                    <div class="offcanvas-header submenu-editor-header">
                        <h5 class="modal-title" id="modalSubmenuLabel">Submenú</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                    </div>
                    <div class="offcanvas-body submenu-editor-body">
                        <ul class="submenu-editor-tabs" role="tablist">
                            <li role="presentation"><button class="active" type="button" data-bs-toggle="tab" data-bs-target="#submenuTabDatos" role="tab">Datos</button></li>
                            <li role="presentation"><button type="button" data-bs-toggle="tab" data-bs-target="#submenuTabContenido" role="tab">Página</button></li>
                            <li role="presentation"><button type="button" data-bs-toggle="tab" data-bs-target="#submenuTabMedia" role="tab">Multimedia</button></li>
                        </ul>
                        <input type="hidden" name="pagina_meta_title" id="modalPaginaMetaTitle">
                        <input type="hidden" name="pagina_meta_description" id="modalPaginaMetaDescription">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="submenuTabDatos" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Menú padre <span class="text-danger">*</span></label>
                                        <select class="form-select" name="id_menu" id="modalSubmenuIdMenu" required>
                                            <option value="">Seleccione</option>
                                            <?php foreach ($menus as $menu): ?>
                                                <option value="<?= (int) $menu['id_menu'] ?>"><?= cms_e($menu['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input class="form-control" name="nombre" id="modalSubmenuNombre" required>
                                    </div>
                                    <input type="hidden" name="url" id="modalSubmenuUrl">
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="estado" id="modalSubmenuEstado">
                                            <label class="form-check-label" for="modalSubmenuEstado">Activo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="submenuTabContenido" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-lg-5">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Título de página</label>
                                                <input class="form-control" name="pagina_titulo" id="modalPaginaTitulo" placeholder="Si queda vacío usa el nombre del submenú">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Bajada</label>
                                                <input class="form-control" name="pagina_bajada" id="modalPaginaBajada" placeholder="Resumen breve para el hero">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Texto del botón</label>
                                                <input class="form-control" name="pagina_boton_texto" id="modalPaginaBotonTexto" placeholder="Opcional">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">URL del botón</label>
                                                <input class="form-control" name="pagina_boton_url" id="modalPaginaBotonUrl" placeholder="#contacto">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Contenido</label>
                                        <textarea class="form-control h-100 js-submenu-editor" name="pagina_contenido" id="modalPaginaContenido" rows="12" placeholder="Texto principal de la página"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="submenuTabMedia" role="tabpanel">
                                <div class="submenu-media-grid">
                                    <div class="submenu-media-card submenu-media-primary">
                                        <label class="form-label">Imagen/video principal</label>
                                        <div class="submenu-hero-mode">
                                            <label><input type="radio" name="pagina_hero_tipo" value="imagen" checked><span><i class="bi bi-image"></i> Imagen</span></label>
                                            <label><input type="radio" name="pagina_hero_tipo" value="video"><span><i class="bi bi-play-btn"></i> Video</span></label>
                                        </div>
                                        <div data-hero-panel="imagen">
                                            <div class="submenu-media-preview" id="modalPaginaHeroPreview">Sin imagen hero</div>
                                            <input class="form-control js-submenu-image-preview" type="file" name="pagina_imagen_hero" accept="image/*" data-preview-target="modalPaginaHeroPreview">
                                            <small class="submenu-media-note" id="modalPaginaHeroActual"></small>
                                            <label class="submenu-media-danger js-delete-hero-image"><input class="form-check-input" type="checkbox" name="delete_pagina_imagen_hero" value="1"> Eliminar imagen principal</label>
                                        </div>
                                        <div data-hero-panel="video">
                                            <div class="submenu-media-preview" id="modalPaginaHeroVideoPreview"><span>Sin video principal</span></div>
                                            <label class="form-label">URL video hero</label>
                                            <input class="form-control mb-2" name="pagina_hero_video_url" id="modalPaginaHeroVideoUrl" placeholder="YouTube, Vimeo o URL directa">
                                            <label class="form-label">Video hero local</label>
                                            <input class="form-control" type="file" name="pagina_hero_video_archivo" accept="video/mp4,video/webm,video/quicktime">
                                            <small class="submenu-media-note" id="modalPaginaHeroVideoActual"></small>
                                            <label class="submenu-media-danger js-delete-hero-video"><input class="form-check-input" type="checkbox" name="delete_pagina_hero_video" value="1"> Eliminar video principal</label>
                                        </div>
                                    </div>
                                    <div class="submenu-media-card submenu-media-gallery">
                                        <div class="small fw-semibold mb-2">Galería</div>
                                        <div class="submenu-gallery-add">
                                            <div class="submenu-media-preview" id="modalPaginaGaleriaNuevaPreview">Sin imagen seleccionada</div>
                                            <div class="submenu-gallery-add-fields">
                                                <input class="form-control js-submenu-image-preview" type="file" name="pagina_galeria_imagen" accept="image/*" data-preview-target="modalPaginaGaleriaNuevaPreview">
                                                <input class="form-control" name="pagina_galeria_titulo" placeholder="Título opcional">
                                                <button type="button" class="btn btn-premium js-submenu-gallery-add"><i class="bi bi-plus-lg"></i> Agregar</button>
                                            </div>
                                        </div>
                                        <div class="small fw-semibold mt-3 mb-2">Galería actual</div>
                                        <div id="modalPaginaMediaActual" class="submenu-gallery-preview"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="submenu-editor-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="offcanvas">Cancelar</button>
                        <button type="submit" class="btn btn-premium">Guardar</button>
                    </div>
                </form>
    </div>
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

<script>
(function () {
    var permissions = <?= $adminPanelPermissionsJson ?: '{}' ?>;
    var deniedMessage = <?= json_encode(admin_permiso_denegado_mensaje('editar'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var actionMap = {
        toggle_seccion: ['contenedores', 'editar'],
        guardar_menu: ['menus', 'crear'],
        toggle_menu: ['menus', 'editar'],
        eliminar_menu: ['menus', 'eliminar'],
        guardar_submenu: ['submenus', 'crear'],
        toggle_submenu: ['submenus', 'editar'],
        reorder_menus: ['menus', 'editar'],
        reorder_submenus: ['submenus', 'editar'],
        reorder_submenu_media: ['submenus', 'editar'],
        add_submenu_media: ['submenus', 'editar'],
        delete_submenu_media: ['submenus', 'editar'],
        toggle_submenu_media: ['submenus', 'editar'],
        guardar_institucion: ['configuracion', 'editar']
    };

    function can(moduleKey, action) {
        return Boolean(permissions[moduleKey] && permissions[moduleKey][action]);
    }

    function resolvePair(form, actionValue) {
        var pair = actionMap[actionValue];
        if (!pair) { return null; }
        if (actionValue === 'guardar_menu') {
            var idMenu = parseInt((form.querySelector('input[name="id_menu"]') || {}).value || '0', 10);
            return ['menus', idMenu > 0 ? 'editar' : 'crear'];
        }
        if (actionValue === 'guardar_submenu') {
            var idSubmenu = parseInt((form.querySelector('input[name="id_sub_menu"]') || {}).value || '0', 10);
            return ['submenus', idSubmenu > 0 ? 'editar' : 'crear'];
        }
        return pair;
    }

    document.querySelectorAll('form').forEach(function (form) {
        var actionInput = form.querySelector('input[name="accion"]');
        if (!actionInput || !actionMap[actionInput.value]) { return; }
        var initialPair = resolvePair(form, actionInput.value);
        var maySubmit = initialPair && can(initialPair[0], initialPair[1]);
        if (!maySubmit && !['guardar_menu', 'guardar_submenu'].includes(actionInput.value)) {
            form.dataset.adminDeniedForm = '1';
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
                button.setAttribute('data-admin-denied', deniedMessage);
                button.classList.add('is-disabled');
            });
        }
        form.addEventListener('submit', function (event) {
            var pair = resolvePair(form, actionInput.value);
            if (!pair || can(pair[0], pair[1])) { return; }
            event.preventDefault();
            if (window.Swal) {
                Swal.fire({ icon: 'warning', title: 'Permiso requerido', text: deniedMessage, confirmButtonColor: '#2b64d8' });
            } else if (window.adminNotify) {
                adminNotify({ title: 'Permiso requerido', msg: deniedMessage, type: 'warning' });
            } else {
                alert(deniedMessage);
            }
        });
    });
})();
</script>

<?php
admin_render_layout_end([
    'extra_scripts' => <<<'HTML'
    <script>
        $(function () {
            var urlParams = new URLSearchParams(window.location.search);
            var savedParam = urlParams.get('saved');
            var activePanel = urlParams.get('panel') || 'contenedores';
            var savedSubmenuId = urlParams.get('submenu') || '';
            var keepSubmenuOpen = urlParams.get('keep_submenu') === '1';
            var savedSubmenuTab = urlParams.get('submenu_tab') || 'submenuTabDatos';

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
                if (keepSubmenuOpen && savedSubmenuId) {
                    window.setTimeout(function () {
                        var safeSubmenuId = savedSubmenuId.replace(/[^0-9]/g, '');
                        var editButton = safeSubmenuId ? document.querySelector('[onclick^="abrirModalSubmenu"][data-id="' + safeSubmenuId + '"]') : null;
                        if (editButton) {
                            abrirModalSubmenu(editButton, null, savedSubmenuTab);
                        }
                    }, 150);
                }
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
                    pageLength: 5,
                    lengthChange: false,
                    paging: true,
                    ordering: false,
                    info: true,
                    language: {
                        emptyTable: 'No hay contenedores para mostrar',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ contenedores',
                        infoEmpty: 'Mostrando 0 a 0 de 0 contenedores',
                        infoFiltered: '(filtrado de _MAX_ contenedores)',
                        loadingRecords: 'Cargando...',
                        processing: 'Procesando...',
                        search: 'Buscar:',
                        zeroRecords: 'No se encontraron contenedores',
                        paginate: {
                            previous: 'Anterior',
                            next: 'Siguiente'
                        }
                    }
                }));
            }

            function updateContenedoresSummary(data) {
                var toggles = [];
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#contenedoresTable')) {
                    toggles = Array.from($('#contenedoresTable').DataTable().rows().nodes()).map(function (row) {
                        return row.querySelector('.js-toggle-seccion');
                    }).filter(Boolean);
                } else {
                    toggles = Array.from(document.querySelectorAll('.js-toggle-seccion'));
                }
                var visible = toggles.filter(function (input) { return input.checked; }).length;
                var hidden = toggles.length - visible;
                var visibleEl = document.getElementById('cmsTotalVisible');
                var hiddenEl = document.getElementById('cmsTotalHidden');
                var lastEl = document.getElementById('cmsLastUpdate');

                if (visibleEl) { visibleEl.textContent = String(visible); }
                if (hiddenEl) { hiddenEl.textContent = String(hidden); }
                if (lastEl && data && data.updated_at_label) { lastEl.textContent = data.updated_at_label; }
            }

            $(document).off('change.cmsToggleSeccion', '.js-toggle-seccion').on('change.cmsToggleSeccion', '.js-toggle-seccion', function () {
                var checkbox = this;
                var form = checkbox.closest('.js-toggle-seccion-form');
                var label = form.querySelector('.js-toggle-label');
                var row = checkbox.closest('tr');
                var hiddenVisible = form.querySelector('.js-toggle-seccion-value');
                var previousState = !checkbox.checked;
                var desiredVisible = checkbox.checked ? 'si' : 'no';
                if (hiddenVisible) {
                    hiddenVisible.value = desiredVisible;
                }
                var formData = new FormData(form);

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
                        if (hiddenVisible) {
                            hiddenVisible.value = data.visible === 'si' ? 'si' : 'no';
                        }
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
                        if (hiddenVisible) {
                            hiddenVisible.value = previousState ? 'si' : 'no';
                        }
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
    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="assets/js/submenu_historia_admin.js"></script>
    <script>
        if (window.CKEDITOR) {
            CKEDITOR.config.versionCheck = false;
        }

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

        function abrirModalSubmenu(btn, defaultIdMenu, preferredTabId) {
            var submenuEditor = document.getElementById('modalSubmenu');
            var modal = bootstrap.Offcanvas.getOrCreateInstance(submenuEditor);
            var titulo = document.getElementById('modalSubmenuLabel');
            var idInput = document.getElementById('modalSubmenuId');
            var activeTabInput = document.getElementById('modalSubmenuActiveTab');
            var idMenuSelect = document.getElementById('modalSubmenuIdMenu');
            var nombreInput = document.getElementById('modalSubmenuNombre');
            var urlInput = document.getElementById('modalSubmenuUrl');
            var estadoCheck = document.getElementById('modalSubmenuEstado');
            var pageFields = {
                titulo: document.getElementById('modalPaginaTitulo'),
                bajada: document.getElementById('modalPaginaBajada'),
                contenido: document.getElementById('modalPaginaContenido'),
                videoUrl: document.getElementById('modalPaginaVideoUrl'),
                botonTexto: document.getElementById('modalPaginaBotonTexto'),
                botonUrl: document.getElementById('modalPaginaBotonUrl'),
                metaTitle: document.getElementById('modalPaginaMetaTitle'),
                metaDescription: document.getElementById('modalPaginaMetaDescription'),
                heroActual: document.getElementById('modalPaginaHeroActual'),
                heroPreview: document.getElementById('modalPaginaHeroPreview'),
                heroVideoUrl: document.getElementById('modalPaginaHeroVideoUrl'),
                heroVideoPreview: document.getElementById('modalPaginaHeroVideoPreview'),
                heroVideoActual: document.getElementById('modalPaginaHeroVideoActual'),
                secundariaActual: document.getElementById('modalPaginaSecundariaActual'),
                secundariaPreview: document.getElementById('modalPaginaSecundariaPreview'),
                videoActual: document.getElementById('modalPaginaVideoActual'),
                galeriaNuevaPreview: document.getElementById('modalPaginaGaleriaNuevaPreview'),
                mediaActual: document.getElementById('modalPaginaMediaActual')
            };

            if (submenuEditor) {
                submenuEditor.querySelectorAll('input[type="file"]').forEach(function (fileInput) {
                    fileInput.value = '';
                });
                submenuEditor.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
                    checkbox.checked = false;
                });
            }

            function getSubmenuEditor() {
                return window.CKEDITOR && CKEDITOR.instances.modalPaginaContenido
                    ? CKEDITOR.instances.modalPaginaContenido
                    : null;
            }

            function setSubmenuEditorData(value) {
                var editor = getSubmenuEditor();
                if (editor) {
                    editor.setData(value || '');
                }
            }

            function setField(field, value) {
                if (field) {
                    field.value = value || '';
                    if (field.id === 'modalPaginaContenido') {
                        setSubmenuEditorData(value || '');
                    }
                }
            }

            function setCurrentText(field, label, value) {
                if (field) { field.textContent = value ? label + ': ' + value : ''; }
            }

            function renderImageBox(field, value, emptyText) {
                if (!field) { return; }
                var src = value || '';
                if (!src) {
                    field.innerHTML = '<span>' + emptyText + '</span>';
                    return;
                }
                field.innerHTML = '<img src="' + src.replace(/"/g, '&quot;') + '" alt="">';
            }

            function renderVideoBox(field, url, archivo) {
                if (!field) { return; }
                var source = archivo || url || '';
                if (!source) { field.innerHTML = '<span>Sin video principal</span>'; return; }
                var youtube = String(url || '').match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{6,})/);
                if (youtube) {
                    field.innerHTML = '<iframe src="https://www.youtube.com/embed/' + youtube[1] + '" title="Vista previa de video" loading="lazy" allowfullscreen></iframe>';
                } else {
                    field.innerHTML = '<video src="' + escapeHtml(source) + '" controls preload="metadata"></video>';
                }
            }

            function syncHeroMode(mode, clearInactive) {
                var selectedMode = mode === 'video' ? 'video' : 'imagen';
                if (!submenuEditor) { return; }
                submenuEditor.querySelectorAll('input[name="pagina_hero_tipo"]').forEach(function (radio) {
                    radio.checked = radio.value === selectedMode;
                });
                submenuEditor.querySelectorAll('[data-hero-panel]').forEach(function (panel) {
                    var isActive = panel.getAttribute('data-hero-panel') === selectedMode;
                    panel.style.display = isActive ? '' : 'none';
                    panel.querySelectorAll('input, textarea, select').forEach(function (field) {
                        if (field.name === 'pagina_hero_tipo') { return; }
                        field.disabled = !isActive;
                    });
                });
                if (!clearInactive) { return; }
                if (selectedMode === 'video') {
                    var heroImageInput = submenuEditor.querySelector('input[name="pagina_imagen_hero"]');
                    if (heroImageInput) { heroImageInput.value = ''; }
                    renderImageBox(pageFields.heroPreview, '', 'Sin imagen principal');
                    setCurrentText(pageFields.heroActual, '', '');
                } else {
                    if (pageFields.heroVideoUrl) { pageFields.heroVideoUrl.value = ''; }
                    var heroVideoFile = submenuEditor.querySelector('input[name="pagina_hero_video_archivo"]');
                    if (heroVideoFile) { heroVideoFile.value = ''; }
                    setCurrentText(pageFields.heroVideoActual, '', '');
                }
            }

            function setHeroMode(mode) {
                syncHeroMode(mode, false);
            }

            function escapeHtml(value) {
                return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function initSubmenuGallerySortable() {
                if (!pageFields.mediaActual || typeof Sortable === 'undefined') { return; }
                if (pageFields.mediaActual._sortableInstance) {
                    pageFields.mediaActual._sortableInstance.destroy();
                }
                pageFields.mediaActual._sortableInstance = Sortable.create(pageFields.mediaActual, {
                    animation: 180,
                    draggable: '.submenu-gallery-card',
                    filter: 'input,button,.dropdown-menu,.submenu-gallery-card-tools',
                    preventOnFilter: false,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onEnd: function () {
                        var idSubmenu = idInput ? idInput.value : '0';
                        if (!idSubmenu || idSubmenu === '0') { return; }
                        var ids = Array.from(pageFields.mediaActual.querySelectorAll('.submenu-gallery-card'))
                            .map(function (card) { return card.getAttribute('data-id'); })
                            .filter(Boolean);
                        var fd = new FormData();
                        fd.append('accion', 'reorder_submenu_media');
                        fd.append('id_sub_menu', idSubmenu);
                        ids.forEach(function (id) { fd.append('items[]', id); });
                        fetch('admin.php?panel=' + encodeURIComponent(activePanel), {
                            method: 'POST',
                            body: fd,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.ok) {
                                adminNotify({ title: 'Orden guardado', msg: 'El orden de la galería fue actualizado.', type: 'info', autoClose: 1800 });
                            }
                        })
                        .catch(function () {});
                    }
                });
            }

            function renderMedia(raw) {
                if (!pageFields.mediaActual) { return; }
                window.submenuRenderMedia = renderMedia;
                if (pageFields.mediaActual._sortableInstance) {
                    pageFields.mediaActual._sortableInstance.destroy();
                    pageFields.mediaActual._sortableInstance = null;
                }
                pageFields.mediaActual.innerHTML = '';
                var media = [];
                try { media = raw ? JSON.parse(raw) : []; } catch (e) { media = []; }
                window.submenuCurrentMedia = media;
                var currentSubmenuId = idInput ? String(idInput.value || '') : '';
                var currentEditButton = currentSubmenuId ? document.querySelector('[onclick^="abrirModalSubmenu"][data-id="' + currentSubmenuId.replace(/[^0-9]/g, '') + '"]') : null;
                if (currentEditButton) {
                    currentEditButton.dataset.paginaMedia = JSON.stringify(media);
                    var summaryCell=currentEditButton.closest('tr') ? currentEditButton.closest('tr').querySelector('.submenu-media-summary') : null;
                    if(summaryCell){
                        var visible=media.filter(function(item){return String(item.visible)!=='0';});
                        var images=visible.filter(function(item){return item.tipo==='imagen';}).length;
                        var videos=visible.filter(function(item){return item.tipo==='video'||item.tipo==='youtube';}).length;
                        var parts=[]; if(currentEditButton.dataset.paginaImagenHero||currentEditButton.dataset.paginaHeroVideoUrl||currentEditButton.dataset.paginaHeroVideoArchivo){parts.push('Hero');}
                        if(images){parts.push(images+' imagen'+(images===1?'':'es'));} if(videos){parts.push(videos+' video'+(videos===1?'':'s'));}
                        summaryCell.textContent=parts.length?parts.join(' · '):'Sin multimedia';
                    }
                }
                if (!media.length) {
                    pageFields.mediaActual.innerHTML = '<span class="text-muted small">Sin imágenes de galería.</span>';
                    return;
                }
                media.forEach(function (item) {
                    var idMedia = String(item.id_media || '');
                    var archivo = String(item.archivo || item.url || '');
                    var shortFile = archivo.split(/[\\/]/).pop() || 'Imagen';
                    var titulo = String(item.titulo || shortFile);
                    var isVisible = String(item.visible || '1') !== '0';
                    var wrap = document.createElement('div');
                    wrap.className = 'submenu-gallery-card';
                    wrap.setAttribute('data-id', idMedia);
                    var imageHtml = archivo ? '<img src="' + archivo.replace(/"/g, '&quot;') + '" alt="">' : '<div class="submenu-media-preview mb-0">Sin imagen</div>';
                    wrap.innerHTML =
                        imageHtml +
                        '<span class="submenu-gallery-drag" title="Arrastrar para ordenar"><i class="bi bi-grip-vertical"></i></span>' +
                        '<div class="dropdown submenu-gallery-menu">' +
                        '<button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones">' +
                        '<i class="bi bi-three-dots-vertical"></i>' +
                        '</button>' +
                        '<div class="dropdown-menu dropdown-menu-end shadow-sm">' +
                        '<button type="button" class="dropdown-item js-submenu-gallery-toggle"><i class="bi bi-eye me-2"></i>' + (isVisible ? 'Desactivar' : 'Activar') + '</button>' +
                        '<button type="button" class="dropdown-item text-danger js-submenu-gallery-delete"><i class="bi bi-trash me-2"></i>Eliminar</button>' +
                        '</div>' +
                        '</div>' +
                        '<span class="submenu-gallery-title" title="' + escapeHtml(archivo) + '">' + escapeHtml(titulo) + '</span>' +
                        '<span class="submenu-gallery-state' + (isVisible ? '' : ' is-hidden') + '">' + (isVisible ? 'Activo' : 'Oculto') + '</span>' +
                        '';
                    pageFields.mediaActual.appendChild(wrap);
                });
                initSubmenuGallerySortable();
            }

            if (btn) {
                titulo.textContent = 'Editar submenú';
                idInput.value = btn.dataset.id || '0';
                idMenuSelect.value = btn.dataset.idMenu || '';
                nombreInput.value = btn.dataset.nombre || '';
                urlInput.value = btn.dataset.url || '';
                estadoCheck.checked = btn.dataset.estado === '1';
                setField(pageFields.titulo, btn.dataset.paginaTitulo || '');
                setField(pageFields.bajada, btn.dataset.paginaBajada || '');
                setField(pageFields.contenido, btn.dataset.paginaContenido || '');
                setField(pageFields.heroVideoUrl, btn.dataset.paginaHeroVideoUrl || '');
                setField(pageFields.videoUrl, btn.dataset.paginaVideoUrl || '');
                setField(pageFields.botonTexto, btn.dataset.paginaBotonTexto || '');
                setField(pageFields.botonUrl, btn.dataset.paginaBotonUrl || '');
                setField(pageFields.metaTitle, btn.dataset.paginaMetaTitle || '');
                setField(pageFields.metaDescription, btn.dataset.paginaMetaDescription || '');
                setHeroMode((btn.dataset.paginaHeroVideoUrl || btn.dataset.paginaHeroVideoArchivo) ? 'video' : 'imagen');
                setCurrentText(pageFields.heroActual, 'Actual', btn.dataset.paginaImagenHero || '');
                renderImageBox(pageFields.heroPreview, btn.dataset.paginaImagenHero || '', 'Sin imagen hero');
                setCurrentText(pageFields.heroVideoActual, 'Actual', btn.dataset.paginaHeroVideoArchivo || '');
                renderVideoBox(pageFields.heroVideoPreview, btn.dataset.paginaHeroVideoUrl || '', btn.dataset.paginaHeroVideoArchivo || '');
                setCurrentText(pageFields.secundariaActual, 'Actual', btn.dataset.paginaImagenSecundaria || '');
                renderImageBox(pageFields.secundariaPreview, btn.dataset.paginaImagenSecundaria || '', 'Sin imagen secundaria');
                setCurrentText(pageFields.videoActual, 'Actual', btn.dataset.paginaVideoArchivo || '');
                renderImageBox(pageFields.galeriaNuevaPreview, '', 'Sin imagen seleccionada');
                renderMedia(btn.dataset.paginaMedia || '[]');
                var deleteHeroImage = submenuEditor.querySelector('.js-delete-hero-image');
                var deleteHeroVideo = submenuEditor.querySelector('.js-delete-hero-video');
                if(deleteHeroImage){deleteHeroImage.style.display=(btn.dataset.paginaImagenHero||'')?'inline-flex':'none';}
                if(deleteHeroVideo){deleteHeroVideo.style.display=(btn.dataset.paginaHeroVideoUrl||btn.dataset.paginaHeroVideoArchivo||'')?'inline-flex':'none';}
            } else {
                titulo.textContent = 'Nuevo submenú';
                idInput.value = '0';
                idMenuSelect.value = defaultIdMenu ? String(defaultIdMenu) : '';
                nombreInput.value = '';
                urlInput.value = '';
                estadoCheck.checked = true;
                setField(pageFields.titulo, '');
                setField(pageFields.bajada, '');
                setField(pageFields.contenido, '');
                setField(pageFields.heroVideoUrl, '');
                setField(pageFields.videoUrl, '');
                setField(pageFields.botonTexto, '');
                setField(pageFields.botonUrl, '');
                setField(pageFields.metaTitle, '');
                setField(pageFields.metaDescription, '');
                setHeroMode('imagen');
                setCurrentText(pageFields.heroActual, '', '');
                renderImageBox(pageFields.heroPreview, '', 'Sin imagen hero');
                setCurrentText(pageFields.heroVideoActual, '', '');
                renderVideoBox(pageFields.heroVideoPreview, '', '');
                setCurrentText(pageFields.secundariaActual, '', '');
                renderImageBox(pageFields.secundariaPreview, '', 'Sin imagen secundaria');
                setCurrentText(pageFields.videoActual, '', '');
                renderImageBox(pageFields.galeriaNuevaPreview, '', 'Sin imagen seleccionada');
                renderMedia('[]');
                submenuEditor.querySelectorAll('.js-delete-hero-image,.js-delete-hero-video').forEach(function(label){label.style.display='none';});
            }
            var targetTabId = preferredTabId || 'submenuTabDatos';
            var targetTab = submenuEditor ? submenuEditor.querySelector('[data-bs-target="#' + targetTabId.replace(/[^A-Za-z0-9_-]/g, '') + '"]') : null;
            var firstTab = targetTab || (submenuEditor ? submenuEditor.querySelector('[data-bs-target="#submenuTabDatos"]') : null);
            if (activeTabInput) {
                activeTabInput.value = firstTab ? (firstTab.getAttribute('data-bs-target') || '#submenuTabDatos').replace('#', '') : 'submenuTabDatos';
            }
            if (firstTab && bootstrap.Tab) {
                bootstrap.Tab.getOrCreateInstance(firstTab).show();
            }
            if (window.submenuHistoriaConfigure) {
                window.submenuHistoriaConfigure(idInput ? idInput.value : '0');
            }
            modal.show();
        }

        document.querySelectorAll('#modalSubmenu [data-bs-toggle="tab"]').forEach(function (tabButton) {
            tabButton.addEventListener('shown.bs.tab', function (event) {
                var activeTabInput = document.getElementById('modalSubmenuActiveTab');
                if (activeTabInput) {
                    activeTabInput.value = (event.target.getAttribute('data-bs-target') || '#submenuTabDatos').replace('#', '');
                }
            });
        });

        var submenuForm = document.getElementById('formModalSubmenu');
        if (submenuForm) {
            submenuForm.addEventListener('submit', function (event) {
                event.preventDefault();
                Object.keys(window.CKEDITOR && CKEDITOR.instances ? CKEDITOR.instances : {}).forEach(function (key) {
                    CKEDITOR.instances[key].updateElement();
                });
                var submitButton = submenuForm.querySelector('button[type="submit"]');
                if (!submitButton || submitButton.disabled) { return; }
                var originalHtml = submitButton.innerHTML;
                var body = document.querySelector('#modalSubmenu .submenu-editor-body');
                var scrollTop = body ? body.scrollTop : 0;
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Guardando...';
                fetch(submenuForm.action || ('admin.php?panel=' + encodeURIComponent(activePanel)), {
                    method: 'POST',
                    body: new FormData(submenuForm),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (response) {
                    return response.json().catch(function () { throw new Error('El servidor devolvió una respuesta inválida.'); }).then(function (data) {
                        if (!response.ok || !data.ok) { throw new Error(data.message || 'No se pudieron guardar los cambios.'); }
                        return data;
                    });
                })
                .then(function (data) {
                    var item = data.submenu || {};
                    var id = String(item.id_sub_menu || document.getElementById('modalSubmenuId').value || '');
                    document.getElementById('modalSubmenuId').value = id;
                    var editButton = document.querySelector('[onclick^="abrirModalSubmenu"][data-id="' + id.replace(/[^0-9]/g, '') + '"]');
                    if (editButton) {
                        var map = { nombre:'nombre', id_menu:'idMenu', url:'url', estado:'estado', pagina_titulo:'paginaTitulo', pagina_bajada:'paginaBajada', pagina_contenido:'paginaContenido', pagina_imagen_hero:'paginaImagenHero', pagina_hero_video_url:'paginaHeroVideoUrl', pagina_hero_video_archivo:'paginaHeroVideoArchivo', pagina_boton_texto:'paginaBotonTexto', pagina_boton_url:'paginaBotonUrl', pagina_meta_title:'paginaMetaTitle', pagina_meta_description:'paginaMetaDescription' };
                        Object.keys(map).forEach(function (key) { editButton.dataset[map[key]] = item[key] == null ? '' : String(item[key]); });
                        editButton.dataset.paginaMedia = JSON.stringify(item.pagina_media || []);
                        var row = editButton.closest('tr');
                        var nameCell = row ? row.querySelector('.sub-name') : null;
                        if (nameCell) { nameCell.textContent = item.nombre || ''; }
                        var stateToggle = row ? row.querySelector('.js-submenu-toggle') : null;
                        if (stateToggle) {
                            stateToggle.checked = String(item.estado) === '1';
                            var stateLabel = stateToggle.closest('.form-check') ? stateToggle.closest('.form-check').querySelector('.form-check-label') : null;
                            if (stateLabel) { stateLabel.textContent = stateToggle.checked ? 'Activo' : 'Inactivo'; }
                        }
                        var contentBadge = row ? row.querySelector('.submenu-content-badge') : null;
                        if (contentBadge) {
                            var external = /^https?:\/\//i.test(String(item.url || '')) && String(item.url || '').indexOf('pagina_submenu.php') === -1;
                            var filled = [item.pagina_titulo,item.pagina_bajada,String(item.pagina_contenido || '').replace(/<[^>]*>/g,'').trim()].filter(function(value){return String(value || '').trim() !== '';}).length;
                            var contentState = external ? 'externo' : (filled === 3 ? 'completo' : (filled === 0 ? 'vacio' : 'incompleto'));
                            var labels = {externo:'Enlace externo',completo:'Completo',incompleto:'Incompleto',vacio:'Vacío'};
                            contentBadge.className='submenu-content-badge is-' + contentState;
                            contentBadge.textContent=labels[contentState];
                        }
                        var mediaSummaryCell = row ? row.querySelector('.submenu-media-summary') : null;
                        if (mediaSummaryCell) {
                            var visibleMedia=(item.pagina_media || []).filter(function(media){return String(media.visible) !== '0';});
                            var images=visibleMedia.filter(function(media){return media.tipo === 'imagen';}).length;
                            var videos=visibleMedia.filter(function(media){return media.tipo === 'video' || media.tipo === 'youtube';}).length;
                            var parts=[]; if(item.pagina_imagen_hero || item.pagina_hero_video_url || item.pagina_hero_video_archivo){parts.push('Hero');}
                            if(images){parts.push(images + ' imagen' + (images===1?'':'es'));} if(videos){parts.push(videos + ' video' + (videos===1?'':'s'));}
                            mediaSummaryCell.textContent=parts.length?parts.join(' · '):'Sin multimedia';
                        }
                    }
                    if (window.submenuRenderMedia) { window.submenuRenderMedia(JSON.stringify(item.pagina_media || [])); }
                    submenuForm.querySelectorAll('input[type="file"]').forEach(function (input) { input.value = ''; });
                    adminNotify({ title: 'Guardado', msg: data.message || 'Cambios guardados correctamente', type: 'info', autoClose: 2200 });
                })
                .catch(function (error) {
                    adminNotify({ title: 'No se pudo guardar', msg: error.message, type: 'warning' });
                })
                .finally(function () {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalHtml;
                    if (body) { body.scrollTop = scrollTop; }
                });
            });
        }

        if (window.CKEDITOR) {
            document.querySelectorAll('textarea.js-submenu-editor').forEach(function (textarea) {
                if (textarea.dataset.ckeditorReady === '1') {
                    return;
                }
                textarea.dataset.ckeditorReady = '1';
                CKEDITOR.replace(textarea.id, {
                    height: 330,
                    versionCheck: false,
                    removePlugins: 'elementspath,image,flash,iframe,forms,smiley,specialchar,about',
                    resize_enabled: false,
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'RemoveFormat'] },
                        { name: 'paragraph', items: ['BulletedList', 'NumberedList', 'Blockquote'] },
                        { name: 'links', items: ['Link', 'Unlink'] },
                        { name: 'colors', items: ['TextColor'] },
                        { name: 'undo', items: ['Undo', 'Redo'] }
                    ]
                });
            });

            document.querySelectorAll('#modalSubmenu form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    Object.keys(CKEDITOR.instances).forEach(function (key) {
                        CKEDITOR.instances[key].updateElement();
                    });
                });
            });
        }

        document.querySelectorAll('#modalSubmenu input[name="pagina_hero_tipo"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var submenuEditor = document.getElementById('modalSubmenu');
                var selectedMode = radio.value === 'video' ? 'video' : 'imagen';
                if (!submenuEditor) { return; }
                submenuEditor.querySelectorAll('[data-hero-panel]').forEach(function (panel) {
                    var isActive = panel.getAttribute('data-hero-panel') === selectedMode;
                    panel.style.display = isActive ? '' : 'none';
                    panel.querySelectorAll('input, textarea, select').forEach(function (field) {
                        field.disabled = !isActive;
                    });
                });
                if (selectedMode === 'video') {
                    var heroPreview = document.getElementById('modalPaginaHeroPreview');
                    if (heroPreview) { heroPreview.innerHTML = '<span>Sin imagen principal</span>'; }
                    var heroActual = document.getElementById('modalPaginaHeroActual');
                    if (heroActual) { heroActual.textContent = ''; }
                    var heroImageInput = submenuEditor.querySelector('input[name="pagina_imagen_hero"]');
                    if (heroImageInput) { heroImageInput.value = ''; }
                } else {
                    var heroVideoUrl = document.getElementById('modalPaginaHeroVideoUrl');
                    if (heroVideoUrl) { heroVideoUrl.value = ''; }
                    var heroVideoActual = document.getElementById('modalPaginaHeroVideoActual');
                    if (heroVideoActual) { heroVideoActual.textContent = ''; }
                    var heroVideoFile = submenuEditor.querySelector('input[name="pagina_hero_video_archivo"]');
                    if (heroVideoFile) { heroVideoFile.value = ''; }
                }
            });
        });

        function submenuMediaRequest(action, values, fileInput) {
            var idSubmenu = document.getElementById('modalSubmenuId').value || '0';
            if (idSubmenu === '0') { return Promise.reject(new Error('Guarda primero el submenú antes de administrar su galería.')); }
            var fd = new FormData();
            fd.append('accion', action);
            fd.append('id_sub_menu', idSubmenu);
            Object.keys(values || {}).forEach(function(key){ fd.append(key, values[key]); });
            if (fileInput && fileInput.files && fileInput.files[0]) { fd.append('pagina_galeria_imagen', fileInput.files[0]); }
            return fetch('admin.php?panel=' + encodeURIComponent(activePanel), { method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'} })
                .then(function(response){ return response.json().then(function(data){ if(!response.ok || !data.ok){ throw new Error(data.message || 'No se pudo actualizar la galería.'); } return data; }); });
        }

        function showSubmenuMediaError(error) {
            adminNotify({ title:'Galería', msg:error.message || 'No se pudo actualizar la galería.', type:'warning' });
        }

        document.addEventListener('click', function(event){
            var addButton = event.target.closest('.js-submenu-gallery-add');
            if (!addButton) { return; }
            var galleryCard = addButton.closest('.submenu-media-gallery');
            var fileInput = galleryCard ? galleryCard.querySelector('input[name="pagina_galeria_imagen"]') : null;
            var titleInput = galleryCard ? galleryCard.querySelector('input[name="pagina_galeria_titulo"]') : null;
            if (!fileInput || !fileInput.files || !fileInput.files[0]) { showSubmenuMediaError(new Error('Selecciona una imagen para agregar.')); return; }
            var original = addButton.innerHTML;
            addButton.disabled = true;
            addButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Agregando...';
            submenuMediaRequest('add_submenu_media', { pagina_galeria_titulo:titleInput ? titleInput.value : '' }, fileInput)
                .then(function(data){
                    window.submenuCurrentMedia = window.submenuCurrentMedia || [];
                    window.submenuCurrentMedia.push(data.media);
                    if(window.submenuRenderMedia){ window.submenuRenderMedia(JSON.stringify(window.submenuCurrentMedia)); }
                    fileInput.value=''; if(titleInput){titleInput.value='';}
                    var preview = document.getElementById('modalPaginaGaleriaNuevaPreview');
                    if(preview){preview.innerHTML='<span>Sin imagen seleccionada</span>';}
                    adminNotify({ title:'Galería', msg:'Imagen agregada correctamente.', type:'info', autoClose:1800 });
                }).catch(showSubmenuMediaError).finally(function(){ addButton.disabled=false; addButton.innerHTML=original; });
        });

        document.addEventListener('click', function (event) {
            var deleteBtn = event.target.closest('.js-submenu-gallery-delete');
            if (deleteBtn) {
                var deleteCard = deleteBtn.closest('.submenu-gallery-card');
                var idMedia = deleteCard ? deleteCard.getAttribute('data-id') : '';
                var confirmDelete = window.Swal ? Swal.fire({ title:'Eliminar imagen', text:'¿Desea eliminar esta imagen de la galería?', icon:'warning', showCancelButton:true, confirmButtonText:'Eliminar', cancelButtonText:'Cancelar', confirmButtonColor:'#dc2626' }).then(function(r){ return r.isConfirmed; }) : Promise.resolve(window.confirm('¿Desea eliminar esta imagen de la galería?'));
                confirmDelete.then(function (confirmed) {
                    if (!confirmed) { return; }
                    return submenuMediaRequest('delete_submenu_media', { id_media:idMedia }).then(function () {
                        window.submenuCurrentMedia = (window.submenuCurrentMedia || []).filter(function(item){ return String(item.id_media) !== String(idMedia); });
                        if (window.submenuRenderMedia) { window.submenuRenderMedia(JSON.stringify(window.submenuCurrentMedia)); }
                        adminNotify({ title:'Galería', msg:'Imagen eliminada correctamente.', type:'info', autoClose:1800 });
                    });
                }).catch(showSubmenuMediaError);
                return;
            }
            var toggleBtn = event.target.closest('.js-submenu-gallery-toggle');
            if (toggleBtn) {
                var toggleCard = toggleBtn.closest('.submenu-gallery-card');
                var toggleId = toggleCard ? toggleCard.getAttribute('data-id') : '';
                submenuMediaRequest('toggle_submenu_media', { id_media:toggleId }).then(function(data){
                    (window.submenuCurrentMedia || []).forEach(function(item){ if(String(item.id_media) === String(toggleId)){ item.visible=data.visible; } });
                    if (window.submenuRenderMedia) { window.submenuRenderMedia(JSON.stringify(window.submenuCurrentMedia || [])); }
                }).catch(showSubmenuMediaError);
            }
        });

        document.querySelectorAll('#modalSubmenu .js-submenu-image-preview').forEach(function (input) {
            input.addEventListener('change', function () {
                var target = document.getElementById(input.getAttribute('data-preview-target') || '');
                if (!target) { return; }
                var file = input.files && input.files[0] ? input.files[0] : null;
                if (!file) {
                    target.innerHTML = '<span>Sin imagen seleccionada</span>';
                    return;
                }
                if (file.type.indexOf('image/') !== 0) {
                    input.value = '';
                    target.innerHTML = '<span>Formato de imagen no válido</span>';
                    adminNotify({ title: 'Archivo no válido', msg: 'Selecciona una imagen JPG, PNG, GIF o WebP.', type: 'warning' });
                    return;
                }
                if (file.size > 10 * 1024 * 1024) {
                    input.value = '';
                    target.innerHTML = '<span>La imagen supera 10 MB</span>';
                    adminNotify({ title: 'Archivo demasiado grande', msg: 'La imagen no puede superar 10 MB.', type: 'warning' });
                    return;
                }
                var url = URL.createObjectURL(file);
                target.innerHTML = '<img src="' + url + '" alt="">';
                var img = target.querySelector('img');
                if (img) {
                    img.onload = function () { URL.revokeObjectURL(url); };
                }
            });
        });

        document.querySelectorAll('#modalSubmenu input[type="file"][accept*="video"]').forEach(function (input) {
            input.addEventListener('change', function () {
                var file = input.files && input.files[0] ? input.files[0] : null;
                if (!file) { return; }
                if (!/\.(mp4|webm|mov|m4v)$/i.test(file.name)) {
                    input.value = '';
                    adminNotify({ title: 'Video no válido', msg: 'Usa un archivo MP4, WebM, MOV o M4V.', type: 'warning' });
                }
            });
        });
    </script>
HTML,
]);


