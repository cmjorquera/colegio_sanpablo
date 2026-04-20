<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: colegiosanpablo.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';

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
            cms_toggle_section_visibility($db, $sectionId);
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
            cms_redirect('admin.php?panel=contenedores');
        }

        if ($action === 'guardar_menu') {
            cms_save_menu($db, $_POST);
            cms_set_flash('success', 'El menú fue guardado correctamente.');
            cms_redirect('admin.php?panel=menus');
        }

        if ($action === 'toggle_menu') {
            cms_toggle_menu($db, (int) ($_POST['id_menu'] ?? 0));
            cms_set_flash('success', 'El estado del menú fue actualizado.');
            cms_redirect('admin.php?panel=menus');
        }

        if ($action === 'guardar_submenu') {
            cms_save_submenu($db, $_POST);
            cms_set_flash('success', 'El submenú fue guardado correctamente.');
            cms_redirect('admin.php?panel=submenus');
        }

        if ($action === 'toggle_submenu') {
            cms_toggle_submenu($db, (int) ($_POST['id_sub_menu'] ?? 0));
            cms_set_flash('success', 'El estado del submenú fue actualizado.');
            cms_redirect('admin.php?panel=submenus');
        }

        if ($action === 'guardar_institucion') {
            cms_save_institution($db, $institutionId, $_POST);
            cms_set_flash('success', 'La configuración institucional fue actualizada.');
            cms_redirect('admin.php?panel=configuracion');
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
$editingMenu = isset($_GET['menu']) ? cms_get_menu($db, (int) $_GET['menu']) : null;
$editingSubmenu = isset($_GET['submenu']) ? cms_get_submenu($db, (int) $_GET['submenu']) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel CMS | Colegio San Pablo</title>
    <link rel="shortcut icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --sp-primary: #1f8f6b;
            --sp-primary-soft: #eaf7f2;
            --sp-secondary: #12324a;
            --sp-accent: #f4b942;
            --sp-dark: #0f172a;
            --sp-border: #dbe4ef;
            --sp-muted: #6c7a89;
            --sp-bg: #f4f7fb;
            --sp-card: #ffffff;
            --sp-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            --sp-radius: 22px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(31, 143, 107, 0.10), transparent 22%),
                radial-gradient(circle at top right, rgba(244, 185, 66, 0.10), transparent 20%),
                var(--sp-bg);
            color: var(--sp-secondary);
        }
        .admin-shell { display: flex; min-height: 100vh; }
        .sidebar {
            width: 290px;
            background: linear-gradient(180deg, #0d1527 0%, #111b31 50%, #0e1629 100%);
            color: #fff;
            padding: 24px 18px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 12px 0 35px rgba(15, 23, 42, 0.18);
            z-index: 1040;
            transition: .3s ease;
        }
        .sidebar.show { transform: translateX(0); }
        .brand-box {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 28px;
        }
        .brand-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--sp-primary), #2bc48e);
            color: #fff;
            font-size: 1.4rem;
        }
        .brand-box h1 { font-size: 1.05rem; margin: 0; font-weight: 700; }
        .brand-box p { margin: 2px 0 0; color: rgba(255, 255, 255, 0.65); font-size: 0.87rem; }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.78);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 16px;
            font-weight: 500;
            margin-bottom: 8px;
            transition: .25s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(31, 143, 107, 0.25), rgba(31, 143, 107, 0.08));
            color: #fff;
            transform: translateX(4px);
        }
        .content-area { flex: 1; padding: 28px; min-width: 0; }
        .topbar, .section-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: var(--sp-shadow);
            border-radius: 28px;
        }
        .topbar { padding: 18px 22px; margin-bottom: 24px; }
        .topbar h2 { margin: 0; font-size: 1.65rem; font-weight: 700; color: var(--sp-dark); }
        .crumb { color: var(--sp-muted); font-size: 0.92rem; }
        .section-card { padding: 22px; margin-bottom: 24px; }
        .section-head { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 18px; flex-wrap: wrap; }
        .section-head h3 { font-size: 1.2rem; margin: 0; color: var(--sp-dark); font-weight: 700; }
        .section-head p { margin: 6px 0 0; color: var(--sp-muted); }
        .metric-card {
            position: relative;
            overflow: hidden;
            min-height: 170px;
            border-radius: 22px;
            padding: 22px;
            color: #fff;
            box-shadow: var(--sp-shadow);
        }
        .metric-green { background: linear-gradient(135deg, #1f8f6b, #34c38f); }
        .metric-blue { background: linear-gradient(135deg, #215b8f, #3e8be4); }
        .metric-gold { background: linear-gradient(135deg, #ba7d10, #f4b942); }
        .metric-card .big-number { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 8px; }
        .table-modern thead th {
            background: #f7f9fc;
            color: var(--sp-secondary);
            border-bottom: 1px solid var(--sp-border);
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 16px 14px;
            white-space: nowrap;
        }
        .table-modern tbody td { vertical-align: middle; padding: 16px 14px; border-color: #edf2f7; }
        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 600;
        }
        .badge-soft.success { background: #e8fbf2; color: #1b8f67; }
        .badge-soft.warning { background: #fff6e0; color: #b7791f; }
        .badge-soft.dark { background: #eef2f7; color: #344256; }
        .btn-soft { background: var(--sp-primary-soft); color: var(--sp-primary); border: none; }
        .btn-soft:hover { background: #d7f1e6; color: #136c51; }
        .btn-premium {
            background: linear-gradient(135deg, var(--sp-primary), #27b785);
            color: #fff; border: none;
        }
        .btn-premium:hover { color: #fff; }
        .form-control, .form-select {
            border-radius: 16px;
            border: 1px solid var(--sp-border);
            min-height: 48px;
            padding: 12px 14px;
            color: var(--sp-secondary);
            box-shadow: none;
        }
        .form-control:focus, .form-select:focus {
            border-color: rgba(31, 143, 107, 0.45);
            box-shadow: 0 0 0 0.25rem rgba(31, 143, 107, 0.12);
        }
        .mobile-sidebar-toggle { display: none; }
        @media (max-width: 1199px) {
            .sidebar { position: fixed; left: 0; top: 0; transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .mobile-sidebar-toggle { display: inline-flex; }
            .content-area { width: 100%; padding: 18px; }
        }
    </style>
</head>
<body>
    <div class="admin-shell">
        <aside class="sidebar" id="adminSidebar">
            <div class="brand-box">
                <div class="brand-icon"><i class="bi bi-building"></i></div>
                <div>
                    <h1>Panel San Pablo</h1>
                    <p>CMS institucional modular</p>
                </div>
            </div>

            <nav class="nav flex-column">
                <a class="nav-link <?= $panel === 'dashboard' ? 'active' : '' ?>" href="admin.php?panel=dashboard"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                <a class="nav-link <?= $panel === 'contenedores' ? 'active' : '' ?>" href="admin.php?panel=contenedores"><i class="bi bi-window-stack"></i> Contenedores del sitio</a>
                <a class="nav-link <?= $panel === 'menus' ? 'active' : '' ?>" href="admin.php?panel=menus"><i class="bi bi-list-nested"></i> Menú principal</a>
                <a class="nav-link <?= $panel === 'submenus' ? 'active' : '' ?>" href="admin.php?panel=submenus"><i class="bi bi-diagram-3"></i> Submenús</a>
                <a class="nav-link <?= $panel === 'configuracion' ? 'active' : '' ?>" href="admin.php?panel=configuracion"><i class="bi bi-sliders2-vertical"></i> Configuración institucional</a>
                <a class="nav-link" href="index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Vista del sitio</a>
            </nav>
        </aside>

        <main class="content-area">
            <header class="topbar">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-soft mobile-sidebar-toggle" id="toggleSidebar" type="button">
                            <i class="bi bi-list"></i>
                        </button>
                        <div>
                            <h2>Administrador del sitio</h2>
                            <div class="crumb">Panel general · Colegio San Pablo</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="index.php" target="_blank" class="btn btn-soft"><i class="bi bi-eye me-2"></i>Ver sitio</a>
                    </div>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="alert alert-<?= cms_e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= cms_e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($panel === 'dashboard'): ?>
                <div class="row g-4">
                    <div class="col-md-6 col-xl-3"><div class="metric-card metric-green"><div class="big-number"><?= count($sections) ?></div><h5>Contenedores</h5><p>Bloques registrados y sincronizados desde <code>seccion</code>.</p></div></div>
                    <div class="col-md-6 col-xl-3"><div class="metric-card metric-blue"><div class="big-number"><?= count($menus) ?></div><h5>Menús</h5><p>Navegación principal usando tablas reales.</p></div></div>
                    <div class="col-md-6 col-xl-3"><div class="metric-card metric-gold"><div class="big-number"><?= count($submenus) ?></div><h5>Submenús</h5><p>Enlaces secundarios del sitio institucional.</p></div></div>
                    <div class="col-md-6 col-xl-3"><div class="metric-card" style="background:linear-gradient(135deg,#1a2238,#2d3654)"><div class="big-number"><?= count(array_filter($sections, static fn($section) => ($section['visible'] ?? '') === 'si')) ?></div><h5>Visibles</h5><p>Contenedores activos en el frontend.</p></div></div>
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
                                    <th>Nombre interno</th>
                                    <th>Nombre admin</th>
                                    <th>Observación</th>
                                    <th>Tipo</th>
                                    <th>Visible</th>
                                    <th>Items</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sections as $section): ?>
                                    <tr>
                                        <td><?= (int) $section['orden'] ?></td>
                                        <td><code><?= cms_e($section['nombre_interno']) ?></code></td>
                                        <td><?= cms_e($section['titulo_admin']) ?></td>
                                        <td>
                                            <div class="text-muted" style="min-width:280px; white-space:normal;">
                                                <?= cms_e($section['observacion'] ?? '') ?>
                                            </div>
                                        </td>
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
                                        <td><?= (int) $section['total_items'] ?></td>
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <a class="btn btn-sm btn-soft" href="editar_contenedor.php?id=<?= (int) $section['id_seccion'] ?>">Ver</a>
                                                <a class="btn btn-sm btn-premium" href="editar_contenedor.php?id=<?= (int) $section['id_seccion'] ?>&modo=editar">Editar</a>
                                                <a class="btn btn-sm btn-outline-secondary" href="preview_contenedor.php?id=<?= (int) $section['id_seccion'] ?>" target="_blank">Visualizar</a>
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
                                                <td><a class="btn btn-sm btn-outline-secondary" href="admin.php?panel=menus&menu=<?= (int) $menu['id_menu'] ?>">Editar</a></td>
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
                                        <?php if ($editingMenu): ?><a class="btn btn-soft" href="admin.php?panel=menus">Cancelar</a><?php endif; ?>
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
                                                <td><a class="btn btn-sm btn-outline-secondary" href="admin.php?panel=submenus&submenu=<?= (int) $submenu['id_sub_menu'] ?>">Editar</a></td>
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
                                        <?php if ($editingSubmenu): ?><a class="btn btn-soft" href="admin.php?panel=submenus">Cancelar</a><?php endif; ?>
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
        </main>
    </div>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
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

            $('#toggleSidebar').on('click', function () {
                $('#adminSidebar').toggleClass('show');
            });

            $('.js-toggle-seccion').on('change', function () {
                var checkbox = this;
                var form = checkbox.closest('.js-toggle-seccion-form');
                var label = form.querySelector('.js-toggle-label');
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
        });
    </script>
</body>
</html>
