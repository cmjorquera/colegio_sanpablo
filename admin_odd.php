<?php
/**
 * Panel de administración — Colegio San Pablo
 */
session_start();
if (empty($_SESSION['admin_logged'])) {
    header('Location: colegiosanpablo.php');
    exit;
}

require_once __DIR__ . '/class/conexion.php';

// Cargar menús para la tabla inicial (SSR)
$menuRows = [];
try {
    $db  = (new Conexion())->getConexion();
    $res = $db->query("SELECT * FROM menus ORDER BY orden ASC");
    if ($res) { $menuRows = $res->fetch_all(MYSQLI_ASSOC); }
} catch (RuntimeException $e) { /* silencioso */ }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel San Pablo | Administracion</title>
    <link rel="shortcut icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --sp-primary: #1f8f6b;
            --sp-primary-soft: #eaf7f2;
            --sp-secondary: #12324a;
            --sp-accent: #f4b942;
            --sp-dark: #0f172a;
            --sp-dark-2: #172033;
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
        a { text-decoration: none; }
        .admin-shell { display: flex; min-height: 100vh; }
        .sidebar {
            width: 300px;
            background: linear-gradient(180deg, #0d1527 0%, #111b31 50%, #0e1629 100%);
            color: #fff;
            padding: 24px 18px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 12px 0 35px rgba(15, 23, 42, 0.18);
            z-index: 1040;
        }
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
            box-shadow: 0 12px 25px rgba(31, 143, 107, 0.35);
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
        .sidebar .nav-link i { font-size: 1.1rem; width: 22px; text-align: center; }
        .sidebar-footer {
            margin-top: 28px;
            padding: 18px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .sidebar-footer small { color: rgba(255, 255, 255, 0.65); }
        .sidebar-footer .status-dot {
            width: 10px; height: 10px; border-radius: 50%; display: inline-block;
            background: #42d392; box-shadow: 0 0 0 6px rgba(66, 211, 146, 0.15); margin-right: 10px;
        }
        .content-area { flex: 1; padding: 28px; min-width: 0; }
        .topbar {
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: var(--sp-shadow);
            border-radius: 28px;
            padding: 18px 22px;
            margin-bottom: 24px;
        }
        .topbar h2 { margin: 0; font-size: 1.65rem; font-weight: 700; color: var(--sp-dark); }
        .crumb { color: var(--sp-muted); font-size: 0.92rem; }
        .topbar-actions {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: flex-end;
        }
        .btn-soft { background: var(--sp-primary-soft); color: var(--sp-primary); border: none; }
        .btn-soft:hover { background: #d7f1e6; color: #136c51; }
        .btn-premium {
            background: linear-gradient(135deg, var(--sp-primary), #27b785);
            color: #fff; border: none; box-shadow: 0 14px 28px rgba(31, 143, 107, 0.25);
        }
        .btn-premium:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 18px 30px rgba(31, 143, 107, 0.32); }
        .user-pill {
            width: 46px; height: 46px; border-radius: 50%; display: grid; place-items: center; font-weight: 700;
            color: #fff; background: linear-gradient(135deg, var(--sp-secondary), #274f70);
            box-shadow: 0 12px 24px rgba(18, 50, 74, 0.22);
        }
        .section-card {
            background: var(--sp-card);
            border-radius: var(--sp-radius);
            border: 1px solid rgba(219, 228, 239, 0.9);
            box-shadow: var(--sp-shadow);
            padding: 22px;
            margin-bottom: 24px;
        }
        .section-card h3 { font-size: 1.2rem; margin: 0; color: var(--sp-dark); font-weight: 700; }
        .section-card .section-subtitle { color: var(--sp-muted); font-size: 0.95rem; margin-top: 6px; }
        .section-head {
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            margin-bottom: 20px; flex-wrap: wrap;
        }
        .metric-card { position: relative; overflow: hidden; min-height: 170px; }
        .metric-card::after {
            content: ""; position: absolute; inset: auto -24px -34px auto; width: 120px; height: 120px;
            border-radius: 28px; background: rgba(31, 143, 107, 0.07); transform: rotate(22deg);
        }
        .metric-icon {
            width: 56px; height: 56px; border-radius: 18px; display: grid; place-items: center;
            color: #fff; font-size: 1.35rem; margin-bottom: 20px;
        }
        .metric-green { background: linear-gradient(135deg, #1f8f6b, #34c38f); }
        .metric-blue { background: linear-gradient(135deg, #215b8f, #3e8be4); }
        .metric-gold { background: linear-gradient(135deg, #ba7d10, #f4b942); }
        .metric-dark { background: linear-gradient(135deg, #1a2238, #2d3654); }
        .metric-card .big-number { font-size: 2rem; font-weight: 800; color: var(--sp-dark); line-height: 1; margin-bottom: 8px; }
        .metric-card p { color: var(--sp-muted); margin-bottom: 0; }
        .glass-tabs {
            padding: 8px; border-radius: 22px; background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(219, 228, 239, 0.9); box-shadow: var(--sp-shadow);
            margin-bottom: 24px; overflow-x: auto; white-space: nowrap;
        }
        .glass-tabs .nav-link { border: none; color: var(--sp-muted); border-radius: 16px; padding: 12px 16px; font-weight: 600; }
        .glass-tabs .nav-link.active {
            background: linear-gradient(135deg, var(--sp-primary), #27b785);
            color: #fff; box-shadow: 0 14px 24px rgba(31, 143, 107, 0.24);
        }
        .table-modern { margin: 0; }
        .table-modern thead th {
            background: #f7f9fc; color: var(--sp-secondary); border-bottom: 1px solid var(--sp-border);
            font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 16px 14px; white-space: nowrap;
        }
        .table-modern tbody td { vertical-align: middle; padding: 16px 14px; border-color: #edf2f7; }
        .table-modern tbody tr:hover { background: #fbfdff; }
        .badge-soft {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px;
            font-size: 0.84rem; font-weight: 600;
        }
        .badge-soft.success { background: #e8fbf2; color: #1b8f67; }
        .badge-soft.warning { background: #fff6e0; color: #b7791f; }
        .badge-soft.dark { background: #eef2f7; color: #344256; }
        .icon-btn {
            width: 38px; height: 38px; border-radius: 12px; display: inline-grid; place-items: center;
            border: none; margin-right: 6px; transition: .2s ease;
        }
        .icon-btn:hover { transform: translateY(-1px); }
        .icon-edit { background: #e8f1ff; color: #2268cb; }
        .icon-delete { background: #ffe9ea; color: #d94b58; }
        .icon-view { background: #edf8f4; color: #1f8f6b; }
        .form-label { font-weight: 600; color: var(--sp-secondary); margin-bottom: 8px; }
        .form-control, .form-select {
            border-radius: 16px; border: 1px solid var(--sp-border); min-height: 48px;
            padding: 12px 14px; color: var(--sp-secondary); box-shadow: none;
        }
        .form-control:focus, .form-select:focus {
            border-color: rgba(31, 143, 107, 0.45);
            box-shadow: 0 0 0 0.25rem rgba(31, 143, 107, 0.12);
        }
        textarea.form-control { min-height: 120px; }
        .switch-field {
            display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 16px;
            border-radius: 16px; border: 1px solid var(--sp-border); background: #fbfcfe;
        }
        .stat-mini { padding: 16px; border-radius: 18px; background: #f8fafc; border: 1px dashed #d9e3ee; }
        .slide-card, .container-tile, .image-card, .footer-preview {
            border: 1px solid var(--sp-border); border-radius: 22px; background: #fff; overflow: hidden; transition: .25s ease;
        }
        .slide-card:hover, .container-tile:hover, .image-card:hover { transform: translateY(-4px); box-shadow: 0 18px 28px rgba(15, 23, 42, 0.08); }
        .slide-thumb { height: 170px; background-size: cover; background-position: center; position: relative; }
        .slide-thumb::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(18, 50, 74, 0.08), rgba(18, 50, 74, 0.44)); }
        .slide-content { padding: 18px; }
        .dropzone-mock {
            border: 2px dashed #c6d4e4; border-radius: 22px; padding: 28px 18px; text-align: center;
            background: linear-gradient(180deg, rgba(31, 143, 107, 0.04), rgba(31, 143, 107, 0.01));
            color: var(--sp-muted);
        }
        .dropzone-mock i { font-size: 2rem; color: var(--sp-primary); margin-bottom: 12px; display: inline-block; }
        .container-tile .tile-head {
            padding: 18px; border-bottom: 1px solid #edf2f7; display: flex; justify-content: space-between; align-items: start; gap: 12px;
        }
        .container-tile .tile-body { padding: 18px; }
        .handle { width: 42px; height: 42px; border-radius: 14px; display: grid; place-items: center; background: #f4f7fb; color: var(--sp-muted); }
        .preview-footer-box {
            background: linear-gradient(135deg, #10263d, #1b3551); color: #fff; border-radius: 24px; padding: 24px; height: 100%;
        }
        .preview-footer-box .social-bubbles a {
            width: 40px; height: 40px; border-radius: 50%; display: inline-grid; place-items: center;
            background: rgba(255, 255, 255, 0.12); color: #fff; margin-right: 8px;
        }
        .image-card .image-preview { height: 180px; background-size: cover; background-position: center; }
        .image-card .image-meta { padding: 16px; }
        .config-palette { display: flex; gap: 12px; flex-wrap: wrap; }
        .palette-item {
            width: 56px; height: 56px; border-radius: 18px; border: 4px solid #fff;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
        }
        .alert-mock {
            border: none; border-radius: 18px; padding: 14px 16px; display: flex; align-items: center;
            gap: 12px; font-weight: 500; margin-bottom: 20px;
        }
        .alert-mock.success { background: #ebfbf2; color: #1d7d5b; }
        .alert-mock.warning { background: #fff7e7; color: #9a6a15; }
        .mobile-sidebar-toggle { display: none; }
        @media (max-width: 1199px) {
            .sidebar { position: fixed; left: 0; top: 0; transform: translateX(-100%); transition: .3s ease; }
            .sidebar.show { transform: translateX(0); }
            .mobile-sidebar-toggle { display: inline-flex; }
            .content-area { width: 100%; padding: 18px; }
        }
        @media (max-width: 767px) {
            .topbar { padding: 18px; }
            .topbar-actions { justify-content: start; }
            .section-card { padding: 18px; }
            .metric-card { min-height: auto; }
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
                    <p>Gestión visual institucional</p>
                </div>
            </div>

            <nav class="nav flex-column">
                <a class="nav-link active" href="#dashboard"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                <a class="nav-link" href="#menus"><i class="bi bi-list-nested"></i> Administrar Menús</a>
                <a class="nav-link" href="#submenus"><i class="bi bi-diagram-3"></i> Administrar Submenús</a>
                <a class="nav-link" href="#carrusel"><i class="bi bi-images"></i> Administrar Carrusel</a>
                <a class="nav-link" href="#contenedores"><i class="bi bi-layout-text-window-reverse"></i> Administrar Contenedores</a>
                <a class="nav-link" href="#footer"><i class="bi bi-columns-gap"></i> Administrar Footer</a>
                <a class="nav-link" href="#imagenes"><i class="bi bi-image"></i> Administrar Imágenes</a>
                <a class="nav-link" href="#configuracion"><i class="bi bi-sliders2-vertical"></i> Configuración General</a>
            </nav>

            <div class="sidebar-footer">
                <div class="d-flex align-items-center mb-2">
                    <span class="status-dot"></span>
                    <strong>Sistema listo</strong>
                </div>
                <small>Interfaz visual preparada para conectar formularios, tablas, imágenes y acciones administrativas.</small>
            </div>
        </aside>

        <main class="content-area">
            <header class="topbar">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-soft mobile-sidebar-toggle" id="toggleSidebar" type="button">
                                <i class="bi bi-list"></i>
                            </button>
                            <div>
                                <h2>Administrador del Sitio Web</h2>
                                <div class="crumb">Inicio / Panel / Gestión principal del sitio institucional</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="topbar-actions">
                            <a href="index.php" class="btn btn-soft">
                                <i class="bi bi-box-arrow-up-right me-2"></i>Ver sitio
                            </a>
                            <button class="btn btn-premium" type="button" data-bs-toggle="modal" data-bs-target="#saveModal">
                                <i class="bi bi-check2-circle me-2"></i>Guardar cambios
                            </button>
                            <div class="user-pill">SP</div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="alert-mock success">
                <i class="bi bi-check-circle-fill"></i>
                <span>Panel listo para administrar contenido visual del sitio. Cada módulo ya cuenta con datos de ejemplo y formularios preparados para integración futura.</span>
            </div>

            <div class="glass-tabs">
                <ul class="nav nav-pills flex-nowrap gap-2" id="adminTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#menus">Menús</a></li>
                    <li class="nav-item"><a class="nav-link" href="#submenus">Submenús</a></li>
                    <li class="nav-item"><a class="nav-link" href="#carrusel">Carrusel</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contenedores">Contenedores</a></li>
                    <li class="nav-item"><a class="nav-link" href="#footer">Footer</a></li>
                    <li class="nav-item"><a class="nav-link" href="#imagenes">Imágenes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#configuracion">Configuración</a></li>
                </ul>
            </div>

            <section id="dashboard" class="mb-4">
                <div class="row g-4">
                    <div class="col-md-6 col-xl-3"><div class="section-card metric-card h-100"><div class="metric-icon metric-green"><i class="bi bi-menu-button-wide-fill"></i></div><div class="big-number">08</div><h5>Menús creados</h5><p>Secciones principales visibles en la navegación institucional.</p></div></div>
                    <div class="col-md-6 col-xl-3"><div class="section-card metric-card h-100"><div class="metric-icon metric-blue"><i class="bi bi-bezier2"></i></div><div class="big-number">17</div><h5>Submenús creados</h5><p>Rutas internas organizadas para una mejor experiencia de navegación.</p></div></div>
                    <div class="col-md-6 col-xl-3"><div class="section-card metric-card h-100"><div class="metric-icon metric-gold"><i class="bi bi-image-alt"></i></div><div class="big-number">05</div><h5>Slides del carrusel</h5><p>Contenido visual destacado listo para portada y campañas estacionales.</p></div></div>
                    <div class="col-md-6 col-xl-3"><div class="section-card metric-card h-100"><div class="metric-icon metric-dark"><i class="bi bi-window-stack"></i></div><div class="big-number">07</div><h5>Contenedores activos</h5><p>Bloques visibles en el home con orden y estado personalizable.</p></div></div>
                </div>
            </section>

            <section id="menus" class="section-card">
                <div class="section-head">
                    <div>
                        <h3>Administrar Menús Principales</h3>
                        <div class="section-subtitle">Gestiona la estructura de navegación principal del sitio institucional.</div>
                    </div>
                    <button class="btn btn-soft" type="button" id="btnNuevoMenu">
                        <i class="bi bi-plus-circle me-2"></i>Agregar Menú
                    </button>
                </div>

                <!-- Alerta CRUD menús -->
                <div id="menuAlert" class="alert d-none mb-3" role="alert"></div>

                <div class="row g-4">
                    <!-- ── TABLA ── -->
                    <div class="col-xl-8">
                        <div class="table-responsive">
                            <table class="table table-modern align-middle" id="tablaMenus">
                                <thead>
                                    <tr>
                                        <th style="width:60px">Orden</th>
                                        <th>Nombre del menú</th>
                                        <th>URL</th>
                                        <th>Ícono</th>
                                        <th>Estado</th>
                                        <th style="width:120px">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyMenus">
                                <?php foreach ($menuRows as $m):
                                    $activo = (int)$m['estado'] === 1;
                                ?>
                                <tr id="fila-menu-<?= $m['id_menu'] ?>">
                                    <td><?= (int)$m['orden'] ?></td>
                                    <td><strong><?= htmlspecialchars($m['nombre']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($m['url'] ?? '') ?></code></td>
                                    <td><?php if($m['icono']): ?><i class="<?= htmlspecialchars($m['icono']) ?>"></i><?php endif; ?></td>
                                    <td>
                                        <?php if ($activo): ?>
                                            <span class="badge-soft success"><i class="bi bi-check-circle-fill"></i> Activo</span>
                                        <?php else: ?>
                                            <span class="badge-soft warning"><i class="bi bi-pause-circle-fill"></i> Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="icon-btn icon-edit"   type="button" title="Editar"
                                                onclick="editarMenu(<?= $m['id_menu'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="icon-btn <?= $activo ? 'icon-view' : 'icon-edit' ?>" type="button"
                                                title="<?= $activo ? 'Desactivar' : 'Activar' ?>"
                                                onclick="toggleMenu(<?= $m['id_menu'] ?>, this)">
                                            <i class="bi bi-<?= $activo ? 'toggle-on' : 'toggle-off' ?>"></i>
                                        </button>
                                        <button class="icon-btn icon-delete" type="button" title="Eliminar"
                                                onclick="eliminarMenu(<?= $m['id_menu'] ?>, '<?= htmlspecialchars($m['nombre'], ENT_QUOTES) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($menuRows)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No hay menús registrados.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ── FORMULARIO ── -->
                    <div class="col-xl-4">
                        <div class="section-card h-100 mb-0">
                            <div class="section-head">
                                <div>
                                    <h3 id="formMenuTitulo">Nuevo Menú</h3>
                                    <div class="section-subtitle" id="formMenuSub">Completa los campos para crear un menú.</div>
                                </div>
                            </div>
                            <form id="formMenu" novalidate>
                                <input type="hidden" id="fmId" name="id_menu" value="0">

                                <div class="mb-3">
                                    <label class="form-label">Nombre del menú</label>
                                    <input type="text" class="form-control" id="fmNombre" name="nombre"
                                           placeholder="Ej: Institucional" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">URL</label>
                                    <input type="text" class="form-control" id="fmUrl" name="url"
                                           placeholder="/institucional.php">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ícono <small class="text-muted">(clase Bootstrap Icons)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i id="fmIconoPreview" class="bi bi-list"></i></span>
                                        <input type="text" class="form-control" id="fmIcono" name="icono"
                                               placeholder="bi bi-building"
                                               oninput="document.getElementById('fmIconoPreview').className='bi '+this.value.replace('bi ','bi ')">
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Orden</label>
                                        <input type="number" class="form-control" id="fmOrden" name="orden"
                                               value="<?= count($menuRows) + 1 ?>" min="1">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Estado</label>
                                        <select class="form-select" id="fmEstado" name="estado">
                                            <option value="1">Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-4 d-flex gap-2">
                                    <button class="btn btn-premium flex-fill" type="submit" id="btnGuardarMenu">
                                        <i class="bi bi-save me-2"></i>Guardar menú
                                    </button>
                                    <button class="btn btn-soft" type="button" id="btnCancelarMenu" style="display:none!important">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section id="submenus" class="section-card">
                <div class="section-head">
                    <div>
                        <h3>Administrar Submenús</h3>
                        <div class="section-subtitle">Organiza la navegación secundaria y dependiente de cada menú padre.</div>
                    </div>
                    <button class="btn btn-soft" type="button" data-bs-toggle="modal" data-bs-target="#submenuModal">
                        <i class="bi bi-node-plus me-2"></i>Agregar Submenú
                    </button>
                </div>
                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="mb-3">
                            <label class="form-label">Seleccionar menú padre</label>
                            <select class="form-select">
                                <option>Institucional</option>
                                <option>Niveles Educativos</option>
                                <option>Noticias</option>
                                <option>Contacto</option>
                            </select>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-modern align-middle">
                                <thead>
                                    <tr>
                                        <th>Orden</th>
                                        <th>Nombre</th>
                                        <th>URL</th>
                                        <th>Menú padre</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>1</td><td><strong>Historia</strong></td><td>/institucional/historia.php</td><td>Institucional</td><td><span class="badge-soft success"><i class="bi bi-check-circle-fill"></i>Activo</span></td><td><button class="icon-btn icon-edit" type="button"><i class="bi bi-pencil"></i></button><button class="icon-btn icon-delete" type="button"><i class="bi bi-trash"></i></button></td></tr>
                                    <tr><td>2</td><td><strong>Misión y Visión</strong></td><td>/institucional/mision-vision.php</td><td>Institucional</td><td><span class="badge-soft success"><i class="bi bi-check-circle-fill"></i>Activo</span></td><td><button class="icon-btn icon-edit" type="button"><i class="bi bi-pencil"></i></button><button class="icon-btn icon-delete" type="button"><i class="bi bi-trash"></i></button></td></tr>
                                    <tr><td>3</td><td><strong>Equipo Directivo</strong></td><td>/institucional/directivos.php</td><td>Institucional</td><td><span class="badge-soft warning"><i class="bi bi-pause-circle-fill"></i>Inactivo</span></td><td><button class="icon-btn icon-edit" type="button"><i class="bi bi-pencil"></i></button><button class="icon-btn icon-delete" type="button"><i class="bi bi-trash"></i></button></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="section-card h-100 mb-0">
                            <h3>Formulario de Submenú</h3>
                            <div class="section-subtitle mb-3">Crea o edita la información visible de cada enlace secundario.</div>
                            <form>
                                <div class="mb-3"><label class="form-label">Nombre submenú</label><input type="text" class="form-control" value="Misión y Visión"></div>
                                <div class="mb-3"><label class="form-label">URL</label><input type="text" class="form-control" value="/institucional/mision-vision.php"></div>
                                <div class="mb-3"><label class="form-label">Menú padre</label><select class="form-select"><option>Institucional</option><option>Niveles Educativos</option><option>Noticias</option></select></div>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Orden</label><input type="number" class="form-control" value="2"></div>
                                    <div class="col-md-6"><label class="form-label">Estado</label><select class="form-select"><option>Activo</option><option>Inactivo</option></select></div>
                                </div>
                                <div class="mt-4"><button class="btn btn-premium w-100" type="button"><i class="bi bi-save me-2"></i>Guardar submenú</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section id="carrusel" class="section-card">
                <div class="section-head">
                    <div>
                        <h3>Administrar Carrusel Principal</h3>
                        <div class="section-subtitle">Controla imágenes, textos, orden, overlays y llamadas a la acción del slider principal.</div>
                    </div>
                    <button class="btn btn-soft" type="button" data-bs-toggle="modal" data-bs-target="#slideModal">
                        <i class="bi bi-plus-square me-2"></i>Agregar Slide
                    </button>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-4">
                        <div class="slide-card">
                            <div class="slide-thumb" style="background-image:url('https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=1200&q=80');"></div>
                            <div class="slide-content">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="badge-soft success">Slide activo</div>
                                        <h5 class="mt-3 mb-1">Educación con propósito</h5>
                                        <small class="text-muted">Orden 1</small>
                                    </div>
                                    <div class="handle"><i class="bi bi-grip-vertical"></i></div>
                                </div>
                                <p class="text-muted mb-3">Formamos estudiantes con excelencia académica, valores y compromiso con su comunidad.</p>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge-soft dark">Botón 1: Admisión 2026</span>
                                    <span class="badge-soft dark">Botón 2: Conócenos</span>
                                </div>
                                <div><button class="icon-btn icon-edit" type="button"><i class="bi bi-pencil"></i></button><button class="icon-btn icon-delete" type="button"><i class="bi bi-trash"></i></button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="slide-card">
                            <div class="slide-thumb" style="background-image:url('https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80');"></div>
                            <div class="slide-content">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="badge-soft success">Slide activo</div>
                                        <h5 class="mt-3 mb-1">Innovación y comunidad</h5>
                                        <small class="text-muted">Orden 2</small>
                                    </div>
                                    <div class="handle"><i class="bi bi-grip-vertical"></i></div>
                                </div>
                                <p class="text-muted mb-3">Espacios modernos, ambiente seguro y experiencias pedagógicas significativas para cada etapa.</p>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge-soft dark">Botón 1: Proyecto Educativo</span>
                                    <span class="badge-soft dark">Botón 2: Equipo docente</span>
                                </div>
                                <div><button class="icon-btn icon-edit" type="button"><i class="bi bi-pencil"></i></button><button class="icon-btn icon-delete" type="button"><i class="bi bi-trash"></i></button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="slide-card">
                            <div class="slide-thumb" style="background-image:url('https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&fit=crop&w=1200&q=80');"></div>
                            <div class="slide-content">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="badge-soft warning">Borrador</div>
                                        <h5 class="mt-3 mb-1">Talento que trasciende</h5>
                                        <small class="text-muted">Orden 3</small>
                                    </div>
                                    <div class="handle"><i class="bi bi-grip-vertical"></i></div>
                                </div>
                                <p class="text-muted mb-3">Un panel listo para programar campañas visuales, admisión, eventos y llamados institucionales.</p>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge-soft dark">Botón 1: Vida escolar</span>
                                    <span class="badge-soft dark">Botón 2: Noticias</span>
                                </div>
                                <div><button class="icon-btn icon-edit" type="button"><i class="bi bi-pencil"></i></button><button class="icon-btn icon-delete" type="button"><i class="bi bi-trash"></i></button></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-7">
                        <div class="section-card h-100 mb-0">
                            <h3>Formulario de Slide</h3>
                            <div class="section-subtitle mb-3">Crea o ajusta el contenido completo de un slide del carrusel principal.</div>
                            <form>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Título principal</label><input type="text" class="form-control" value="Educación con propósito"></div>
                                    <div class="col-md-6"><label class="form-label">Subtítulo</label><input type="text" class="form-control" value="Admisión 2026 abierta"></div>
                                    <div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control">Formamos estudiantes con excelencia académica, contención, liderazgo y visión integral para los desafíos del futuro.</textarea></div>
                                    <div class="col-md-6"><label class="form-label">Texto botón 1</label><input type="text" class="form-control" value="Postular ahora"></div>
                                    <div class="col-md-6"><label class="form-label">Link botón 1</label><input type="text" class="form-control" value="/admision.php"></div>
                                    <div class="col-md-6"><label class="form-label">Texto botón 2</label><input type="text" class="form-control" value="Conócenos"></div>
                                    <div class="col-md-6"><label class="form-label">Link botón 2</label><input type="text" class="form-control" value="/institucional.php"></div>
                                    <div class="col-md-6"><label class="form-label">Imagen destacada</label><input type="text" class="form-control" value="assets/images/slides/slide-01.jpg"></div>
                                    <div class="col-md-3"><label class="form-label">Orden</label><input type="number" class="form-control" value="1"></div>
                                    <div class="col-md-3"><label class="form-label">Estado</label><select class="form-select"><option>Activo</option><option>Borrador</option><option>Inactivo</option></select></div>
                                    <div class="col-12">
                                        <div class="switch-field">
                                            <div>
                                                <strong>Mostrar overlay oscuro</strong>
                                                <div class="text-muted small">Usar una capa semitransparente sobre la imagen de fondo del slide.</div>
                                            </div>
                                            <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" checked></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4"><button class="btn btn-premium" type="button"><i class="bi bi-save me-2"></i>Guardar slide</button></div>
                            </form>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="section-card h-100 mb-0">
                            <h3>Subir imagen</h3>
                            <div class="section-subtitle mb-3">Zona visual de carga tipo drag &amp; drop.</div>
                            <div class="dropzone-mock mb-3">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <h5>Arrastra una imagen aquí</h5>
                                <p class="mb-2">o selecciona un archivo desde tu equipo</p>
                                <button class="btn btn-soft" type="button">Seleccionar imagen</button>
                            </div>
                            <div class="stat-mini">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <strong>slide-portada-2026.jpg</strong>
                                        <div class="text-muted small">1920x840 px · 1.8 MB</div>
                                    </div>
                                    <span class="badge-soft success">Listo</span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="slide-card">
                                    <div class="slide-thumb" style="background-image:url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1200&q=80');"></div>
                                    <div class="slide-content">
                                        <strong>Vista previa del slide</strong>
                                        <p class="text-muted mb-0 mt-2">Esta tarjeta permite simular cómo se vería el banner una vez conectado al frontend real del sitio.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contenedores" class="section-card">
                <div class="section-head">
                    <div>
                        <h3>Administrar Contenedores del Home</h3>
                        <div class="section-subtitle">Activa, oculta, ordena y edita visualmente cada bloque principal de la portada.</div>
                    </div>
                    <button class="btn btn-soft" type="button"><i class="bi bi-plus-lg me-2"></i>Nuevo contenedor</button>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-4"><div class="container-tile"><div class="tile-head"><div><h5 class="mb-1">Header</h5><div class="text-muted small">ID: header_principal</div></div><div class="handle"><i class="bi bi-grid-3x2-gap"></i></div></div><div class="tile-body"><div class="d-flex justify-content-between mb-3"><span class="badge-soft success">Visible</span><span class="badge-soft dark">Orden 1</span></div><div class="d-flex gap-2"><button class="btn btn-soft btn-sm">Editar</button><button class="btn btn-outline-secondary btn-sm">Ocultar</button></div></div></div></div>
                    <div class="col-md-6 col-xl-4"><div class="container-tile"><div class="tile-head"><div><h5 class="mb-1">Carrusel</h5><div class="text-muted small">ID: home_slider</div></div><div class="handle"><i class="bi bi-grid-3x2-gap"></i></div></div><div class="tile-body"><div class="d-flex justify-content-between mb-3"><span class="badge-soft success">Visible</span><span class="badge-soft dark">Orden 2</span></div><div class="d-flex gap-2"><button class="btn btn-soft btn-sm">Editar</button><button class="btn btn-outline-secondary btn-sm">Ocultar</button></div></div></div></div>
                    <div class="col-md-6 col-xl-4"><div class="container-tile"><div class="tile-head"><div><h5 class="mb-1">Bienvenida</h5><div class="text-muted small">ID: bienvenida_home</div></div><div class="handle"><i class="bi bi-grid-3x2-gap"></i></div></div><div class="tile-body"><div class="d-flex justify-content-between mb-3"><span class="badge-soft success">Visible</span><span class="badge-soft dark">Orden 3</span></div><div class="d-flex gap-2"><button class="btn btn-soft btn-sm">Editar</button><button class="btn btn-outline-secondary btn-sm">Ocultar</button></div></div></div></div>
                    <div class="col-md-6 col-xl-4"><div class="container-tile"><div class="tile-head"><div><h5 class="mb-1">Institucional</h5><div class="text-muted small">ID: bloque_institucional</div></div><div class="handle"><i class="bi bi-grid-3x2-gap"></i></div></div><div class="tile-body"><div class="d-flex justify-content-between mb-3"><span class="badge-soft success">Visible</span><span class="badge-soft dark">Orden 4</span></div><div class="d-flex gap-2"><button class="btn btn-soft btn-sm">Editar</button><button class="btn btn-outline-secondary btn-sm">Ocultar</button></div></div></div></div>
                    <div class="col-md-6 col-xl-4"><div class="container-tile"><div class="tile-head"><div><h5 class="mb-1">Niveles educativos</h5><div class="text-muted small">ID: niveles_home</div></div><div class="handle"><i class="bi bi-grid-3x2-gap"></i></div></div><div class="tile-body"><div class="d-flex justify-content-between mb-3"><span class="badge-soft success">Visible</span><span class="badge-soft dark">Orden 5</span></div><div class="d-flex gap-2"><button class="btn btn-soft btn-sm">Editar</button><button class="btn btn-outline-secondary btn-sm">Ocultar</button></div></div></div></div>
                    <div class="col-md-6 col-xl-4"><div class="container-tile"><div class="tile-head"><div><h5 class="mb-1">Noticias</h5><div class="text-muted small">ID: noticias_home</div></div><div class="handle"><i class="bi bi-grid-3x2-gap"></i></div></div><div class="tile-body"><div class="d-flex justify-content-between mb-3"><span class="badge-soft warning">Oculto</span><span class="badge-soft dark">Orden 6</span></div><div class="d-flex gap-2"><button class="btn btn-soft btn-sm">Editar</button><button class="btn btn-outline-success btn-sm">Mostrar</button></div></div></div></div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="section-card h-100 mb-0">
                            <h3>Editor de contenedor</h3>
                            <div class="section-subtitle mb-3">Formulario visual para editar bloques del home.</div>
                            <form>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Nombre del contenedor</label><input type="text" class="form-control" value="Bienvenida"></div>
                                    <div class="col-md-6"><label class="form-label">Identificador interno</label><input type="text" class="form-control" value="bienvenida_home"></div>
                                    <div class="col-md-6"><label class="form-label">Título</label><input type="text" class="form-control" value="Bienvenidos a San Pablo"></div>
                                    <div class="col-md-6"><label class="form-label">Subtítulo</label><input type="text" class="form-control" value="Comunidad, valores y excelencia"></div>
                                    <div class="col-12"><label class="form-label">Descripción corta</label><textarea class="form-control">Una presentación institucional que resume nuestro proyecto educativo, identidad y propuesta de valor para familias y estudiantes.</textarea></div>
                                    <div class="col-md-6"><label class="form-label">Imagen principal</label><input type="text" class="form-control" value="assets/images/home/bienvenida.jpg"></div>
                                    <div class="col-md-3"><label class="form-label">Botón</label><input type="text" class="form-control" value="Saber más"></div>
                                    <div class="col-md-3"><label class="form-label">Link botón</label><input type="text" class="form-control" value="/institucional.php"></div>
                                    <div class="col-md-4"><label class="form-label">Color de fondo</label><input type="text" class="form-control" value="#F4F7FB"></div>
                                    <div class="col-md-4"><label class="form-label">Orden</label><input type="number" class="form-control" value="3"></div>
                                    <div class="col-md-4"><label class="form-label">Visible</label><select class="form-select"><option>Sí</option><option>No</option></select></div>
                                </div>
                                <div class="mt-4"><button class="btn btn-premium" type="button"><i class="bi bi-save me-2"></i>Guardar cambios</button></div>
                            </form>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="section-card h-100 mb-0">
                            <h3>Previsualización rápida</h3>
                            <div class="section-subtitle mb-3">Referencia visual del bloque seleccionado.</div>
                            <div class="slide-card">
                                <div class="slide-thumb" style="background-image:url('https://images.unsplash.com/photo-1497486751825-1233686d5d80?auto=format&fit=crop&w=1200&q=80'); height:220px;"></div>
                                <div class="slide-content">
                                    <small class="text-uppercase text-muted fw-semibold">Bienvenida</small>
                                    <h5 class="mt-2">Bienvenidos a San Pablo</h5>
                                    <p class="text-muted">Comunidad, valores y excelencia como base para una educación integral y transformadora.</p>
                                    <button class="btn btn-soft btn-sm" type="button">Saber más</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="footer" class="section-card">
                <div class="section-head">
                    <div>
                        <h3>Administrar Footer</h3>
                        <div class="section-subtitle">Edita textos, redes, direcciones, botones y copyright del pie de página.</div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-xl-5">
                        <div class="preview-footer-box">
                            <small class="text-uppercase text-white-50">Previsualización visual</small>
                            <h4 class="mt-2">Síguenos en nuestras redes</h4>
                            <p class="text-white-50">Mantente al día con novedades, actividades y comunicados del Colegio San Pablo.</p>
                            <div class="social-bubbles mb-4">
                                <a href="#0"><i class="bi bi-facebook"></i></a>
                                <a href="#0"><i class="bi bi-twitter-x"></i></a>
                                <a href="#0"><i class="bi bi-instagram"></i></a>
                                <a href="#0"><i class="bi bi-google"></i></a>
                            </div>
                            <div class="mb-3"><strong>Dirección</strong><div class="text-white-50">Av. Principal 6391</div><div class="text-white-50">Celina, Campus Institucional</div><div class="text-white-50">Santiago, Chile</div></div>
                            <div class="mb-3"><strong>Sitio web</strong><div class="text-white-50">www.colegiosanpablo.cl</div></div>
                            <div class="d-flex flex-wrap gap-2 mb-4"><span class="badge text-bg-light">Admisión</span><span class="badge text-bg-light">Noticias</span><span class="badge text-bg-light">Contacto</span><span class="badge text-bg-light">Comunidad</span></div>
                            <small class="text-white-50">© 2026 Colegio San Pablo. Todos los derechos reservados.</small>
                        </div>
                    </div>
                    <div class="col-xl-7">
                        <div class="section-card h-100 mb-0">
                            <form>
                                <div class="row g-3">
                                    <div class="col-12"><label class="form-label">Texto redes sociales</label><input type="text" class="form-control" value="Síguenos en nuestras redes"></div>
                                    <div class="col-md-6"><label class="form-label">Link Facebook</label><input type="text" class="form-control" value="https://facebook.com/colegiosanpablo"></div>
                                    <div class="col-md-6"><label class="form-label">Link Twitter</label><input type="text" class="form-control" value="https://twitter.com/colegiospablo"></div>
                                    <div class="col-md-6"><label class="form-label">Link Instagram</label><input type="text" class="form-control" value="https://instagram.com/colegiosanpablo"></div>
                                    <div class="col-md-6"><label class="form-label">Link Google</label><input type="text" class="form-control" value="https://google.com"></div>
                                    <div class="col-md-4"><label class="form-label">Dirección 1</label><input type="text" class="form-control" value="Av. Principal 6391"></div>
                                    <div class="col-md-4"><label class="form-label">Dirección 2</label><input type="text" class="form-control" value="Celina, Campus Institucional"></div>
                                    <div class="col-md-4"><label class="form-label">Dirección 3</label><input type="text" class="form-control" value="Santiago, Chile"></div>
                                    <div class="col-md-6"><label class="form-label">Texto sitio web</label><input type="text" class="form-control" value="www.colegiosanpablo.cl"></div>
                                    <div class="col-md-6"><label class="form-label">Copyright</label><input type="text" class="form-control" value="© 2026 Colegio San Pablo. Todos los derechos reservados."></div>
                                    <div class="col-md-3"><label class="form-label">Botón 1 nombre</label><input type="text" class="form-control" value="Admisión"></div>
                                    <div class="col-md-3"><label class="form-label">Botón 2 nombre</label><input type="text" class="form-control" value="Noticias"></div>
                                    <div class="col-md-3"><label class="form-label">Botón 3 nombre</label><input type="text" class="form-control" value="Contacto"></div>
                                    <div class="col-md-3"><label class="form-label">Botón 4 nombre</label><input type="text" class="form-control" value="Comunidad"></div>
                                </div>
                                <div class="mt-4"><button class="btn btn-premium" type="button"><i class="bi bi-save me-2"></i>Guardar footer</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section id="imagenes" class="section-card">
                <div class="section-head">
                    <div>
                        <h3>Administrar Imágenes</h3>
                        <div class="section-subtitle">Galería visual tipo media manager para centralizar archivos del sitio.</div>
                    </div>
                    <button class="btn btn-soft" type="button" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-cloud-upload me-2"></i>Subir imagen
                    </button>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-xl-4"><input type="text" class="form-control" placeholder="Buscar imagen por nombre..."></div>
                    <div class="col-md-3 col-xl-2"><select class="form-select"><option>Todos los tipos</option><option>Banner</option><option>Noticias</option><option>Galería</option><option>Logos</option></select></div>
                    <div class="col-md-3 col-xl-2"><select class="form-select"><option>Más recientes</option><option>Más antiguos</option><option>Nombre A-Z</option></select></div>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-xl-3"><div class="image-card"><div class="image-preview" style="background-image:url('https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=900&q=80');"></div><div class="image-meta"><h6 class="mb-1">slide-portada-01.jpg</h6><div class="text-muted small mb-3">Banner principal · 1.8 MB</div><div class="d-flex gap-2"><button class="btn btn-soft btn-sm flex-fill">Copiar ruta</button><button class="btn btn-outline-danger btn-sm">Eliminar</button></div></div></div></div>
                    <div class="col-md-6 col-xl-3"><div class="image-card"><div class="image-preview" style="background-image:url('https://images.unsplash.com/photo-1511629091441-ee46146481b6?auto=format&fit=crop&w=900&q=80');"></div><div class="image-meta"><h6 class="mb-1">nivel-basica.jpg</h6><div class="text-muted small mb-3">Niveles educativos · 932 KB</div><div class="d-flex gap-2"><button class="btn btn-soft btn-sm flex-fill">Copiar ruta</button><button class="btn btn-outline-danger btn-sm">Eliminar</button></div></div></div></div>
                    <div class="col-md-6 col-xl-3"><div class="image-card"><div class="image-preview" style="background-image:url('https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=900&q=80');"></div><div class="image-meta"><h6 class="mb-1">noticia-feria-ciencias.png</h6><div class="text-muted small mb-3">Noticias · 640 KB</div><div class="d-flex gap-2"><button class="btn btn-soft btn-sm flex-fill">Copiar ruta</button><button class="btn btn-outline-danger btn-sm">Eliminar</button></div></div></div></div>
                    <div class="col-md-6 col-xl-3"><div class="image-card"><div class="image-preview" style="background-image:url('https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=900&q=80');"></div><div class="image-meta"><h6 class="mb-1">logo-footer.svg</h6><div class="text-muted small mb-3">Logos · 120 KB</div><div class="d-flex gap-2"><button class="btn btn-soft btn-sm flex-fill">Copiar ruta</button><button class="btn btn-outline-danger btn-sm">Eliminar</button></div></div></div></div>
                </div>
            </section>

            <section id="configuracion" class="section-card">
                <div class="section-head">
                    <div>
                        <h3>Configuración General</h3>
                        <div class="section-subtitle">Parámetros globales del sitio, marca institucional, contacto y paleta visual.</div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-xl-8">
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Nombre del sitio</label><input type="text" class="form-control" value="Colegio San Pablo"></div>
                                <div class="col-md-6"><label class="form-label">Correo de contacto</label><input type="email" class="form-control" value="info@colegiospablo.cl"></div>
                                <div class="col-md-6"><label class="form-label">Logo</label><input type="text" class="form-control" value="assets/images/logo/logo.svg"></div>
                                <div class="col-md-6"><label class="form-label">Favicon</label><input type="text" class="form-control" value="assets/images/favicon.png"></div>
                                <div class="col-md-6"><label class="form-label">Teléfono</label><input type="text" class="form-control" value="+56 2 2456 7812"></div>
                                <div class="col-md-6"><label class="form-label">Dirección</label><input type="text" class="form-control" value="Av. Principal 6391, Celina"></div>
                                <div class="col-md-6"><label class="form-label">Color principal</label><input type="text" class="form-control" value="#1F8F6B"></div>
                                <div class="col-md-6"><label class="form-label">Color secundario</label><input type="text" class="form-control" value="#12324A"></div>
                                <div class="col-md-6"><label class="form-label">Facebook</label><input type="text" class="form-control" value="https://facebook.com/colegiosanpablo"></div>
                                <div class="col-md-6"><label class="form-label">Instagram</label><input type="text" class="form-control" value="https://instagram.com/colegiosanpablo"></div>
                            </div>
                            <div class="mt-4"><button class="btn btn-premium" type="button"><i class="bi bi-save me-2"></i>Guardar configuración</button></div>
                        </form>
                    </div>
                    <div class="col-xl-4">
                        <div class="section-card h-100 mb-0">
                            <h3>Paleta institucional</h3>
                            <div class="section-subtitle mb-3">Vista visual de colores sugeridos para la marca.</div>
                            <div class="config-palette mb-3">
                                <div class="palette-item" style="background:#1F8F6B;"></div>
                                <div class="palette-item" style="background:#12324A;"></div>
                                <div class="palette-item" style="background:#F4B942;"></div>
                                <div class="palette-item" style="background:#F4F7FB;"></div>
                            </div>
                            <div class="stat-mini mb-3"><strong>Estado visual del sistema</strong><div class="text-muted small mt-1">Diseño consistente, formularios completos y componentes listos para integración.</div></div>
                            <div class="alert-mock warning mb-0"><i class="bi bi-lightbulb-fill"></i><span>Recomendación: conectar después esta sección con una tabla `configuracion` para centralizar logo, datos de contacto y colores globales.</span></div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div class="modal fade" id="menuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:24px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Agregar Menú</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" placeholder="Ej: Admisión"></div>
                    <div class="mb-3"><label class="form-label">URL</label><input type="text" class="form-control" placeholder="/admision.php"></div>
                    <div class="mb-3"><label class="form-label">Ícono</label><input type="text" class="form-control" placeholder="bi bi-stars"></div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-premium" type="button">Guardar menú</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="submenuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:24px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Agregar Submenú</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" placeholder="Ej: Proyecto Educativo"></div>
                    <div class="mb-3"><label class="form-label">Menú padre</label><select class="form-select"><option>Institucional</option><option>Niveles Educativos</option></select></div>
                    <div class="mb-3"><label class="form-label">URL</label><input type="text" class="form-control" placeholder="/institucional/proyecto.php"></div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-premium" type="button">Guardar submenú</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="slideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:24px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Agregar Slide</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Título</label><input type="text" class="form-control" placeholder="Nueva experiencia educativa"></div>
                        <div class="col-md-6"><label class="form-label">Subtítulo</label><input type="text" class="form-control" placeholder="Matrículas abiertas"></div>
                        <div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control" placeholder="Descripción del slide"></textarea></div>
                        <div class="col-12"><div class="dropzone-mock"><i class="bi bi-image"></i><div>Zona visual para subir imagen del slide</div></div></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-premium" type="button">Guardar slide</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:24px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Subir imagen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="dropzone-mock">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <h5>Arrastra o selecciona un archivo</h5>
                        <p class="mb-0">Este componente es visual y queda listo para conectarlo después con un uploader real.</p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-light" data-bs-dismiss="modal" type="button">Cerrar</button>
                    <button class="btn btn-premium" type="button">Simular carga</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="saveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:24px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Cambios preparados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert-mock success mb-0">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Esta es una confirmación visual. Más adelante aquí puedes conectar guardado real con PHP, AJAX o fetch.</span>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-premium" data-bs-dismiss="modal" type="button">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('adminSidebar');
            const toggleSidebar = document.getElementById('toggleSidebar');
            const navLinks = document.querySelectorAll('.sidebar .nav-link, .glass-tabs .nav-link');
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
            const tabLinks = document.querySelectorAll('.glass-tabs .nav-link');

            if (toggleSidebar) {
                toggleSidebar.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                });
            }

            navLinks.forEach(function (link) {
                link.addEventListener('click', function (event) {
                    const target = this.getAttribute('href');
                    if (!target || !target.startsWith('#')) {
                        return;
                    }
                    event.preventDefault();
                    document.querySelector(target)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    sidebarLinks.forEach(function (item) { item.classList.toggle('active', item.getAttribute('href') === target); });
                    tabLinks.forEach(function (item) { item.classList.toggle('active', item.getAttribute('href') === target); });
                    if (window.innerWidth < 1200) {
                        sidebar.classList.remove('show');
                    }
                });
            });
        });
    </script>

    <!-- ── MANTENEDOR DE MENÚS ── -->
    <script>
    (function () {

        const API     = 'api/menus.php';
        const tbody   = document.getElementById('tbodyMenus');
        const form    = document.getElementById('formMenu');
        const alertEl = document.getElementById('menuAlert');
        const btnCancelar = document.getElementById('btnCancelarMenu');
        const btnNuevo    = document.getElementById('btnNuevoMenu');

        /* ── Toast / alerta ── */
        function showAlert(msg, tipo) {
            alertEl.className = 'alert alert-' + (tipo === 'ok' ? 'success' : 'danger') + ' mb-3';
            alertEl.textContent = msg;
            alertEl.classList.remove('d-none');
            clearTimeout(alertEl._t);
            alertEl._t = setTimeout(function() { alertEl.classList.add('d-none'); }, 3500);
        }

        /* ── Genera una fila HTML ── */
        function filaHtml(m) {
            var activo = parseInt(m.estado) === 1;
            return '<tr id="fila-menu-' + m.id_menu + '">' +
                '<td>' + m.orden + '</td>' +
                '<td><strong>' + esc(m.nombre) + '</strong></td>' +
                '<td><code>' + esc(m.url || '') + '</code></td>' +
                '<td>' + (m.icono ? '<i class="' + esc(m.icono) + '"></i>' : '') + '</td>' +
                '<td>' + (activo
                    ? '<span class="badge-soft success"><i class="bi bi-check-circle-fill"></i> Activo</span>'
                    : '<span class="badge-soft warning"><i class="bi bi-pause-circle-fill"></i> Inactivo</span>') + '</td>' +
                '<td>' +
                    '<button class="icon-btn icon-edit"   title="Editar"    onclick="editarMenu(' + m.id_menu + ')"><i class="bi bi-pencil"></i></button>' +
                    '<button class="icon-btn ' + (activo ? 'icon-view' : 'icon-edit') + '" title="' + (activo ? 'Desactivar' : 'Activar') + '" onclick="toggleMenu(' + m.id_menu + ', this)"><i class="bi bi-' + (activo ? 'toggle-on' : 'toggle-off') + '"></i></button>' +
                    '<button class="icon-btn icon-delete" title="Eliminar"  onclick="eliminarMenu(' + m.id_menu + ', \'' + esc(m.nombre) + '\')"><i class="bi bi-trash"></i></button>' +
                '</td>' +
            '</tr>';
        }

        function esc(s) {
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
        }

        /* ── Limpiar / preparar form para NUEVO ── */
        function resetForm() {
            form.reset();
            document.getElementById('fmId').value = '0';
            document.getElementById('formMenuTitulo').textContent = 'Nuevo Menú';
            document.getElementById('formMenuSub').textContent    = 'Completa los campos para crear un menú.';
            document.getElementById('fmIconoPreview').className   = 'bi bi-list';
            btnCancelar.style.setProperty('display', 'none', 'important');
        }

        /* ── Botón "Agregar Menú" ── */
        btnNuevo.addEventListener('click', resetForm);

        /* ── Botón cancelar edición ── */
        btnCancelar.addEventListener('click', resetForm);

        /* ── EDITAR: rellena el formulario ── */
        window.editarMenu = function(id) {
            var fila = document.getElementById('fila-menu-' + id);
            if (!fila) return;
            var celdas = fila.querySelectorAll('td');

            document.getElementById('fmId').value      = id;
            document.getElementById('fmOrden').value   = celdas[0].textContent.trim();
            document.getElementById('fmNombre').value  = celdas[1].querySelector('strong').textContent.trim();
            document.getElementById('fmUrl').value     = celdas[2].querySelector('code') ? celdas[2].querySelector('code').textContent.trim() : '';
            var iconoEl = celdas[3].querySelector('i');
            var iconoCls = iconoEl ? iconoEl.className : '';
            document.getElementById('fmIcono').value   = iconoCls;
            document.getElementById('fmIconoPreview').className = iconoCls || 'bi bi-list';
            document.getElementById('fmEstado').value  = celdas[4].querySelector('.success') ? '1' : '0';

            document.getElementById('formMenuTitulo').textContent = 'Editar Menú';
            document.getElementById('formMenuSub').textContent    = 'Modifica los campos y guarda.';
            btnCancelar.style.removeProperty('display');

            // Scroll al formulario en móvil
            document.getElementById('formMenu').scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        /* ── TOGGLE estado ── */
        window.toggleMenu = function(id, btn) {
            var body = 'action=toggle&id_menu=' + id;
            fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.ok) { showAlert(data.msg, 'error'); return; }
                // Recargar la fila completa desde la API
                recargarTabla();
                showAlert('Estado actualizado', 'ok');
            })
            .catch(function() { showAlert('Error de conexión', 'error'); });
        };

        /* ── ELIMINAR ── */
        window.eliminarMenu = function(id, nombre) {
            if (!confirm('¿Eliminar el menú "' + nombre + '"?\nEsta acción no se puede deshacer.')) return;
            fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete&id_menu=' + id
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) {
                    var fila = document.getElementById('fila-menu-' + id);
                    if (fila) fila.remove();
                    showAlert('Menú eliminado', 'ok');
                    if (tbody.querySelectorAll('tr').length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay menús registrados.</td></tr>';
                    }
                    resetForm();
                } else {
                    showAlert(data.msg, 'error');
                }
            })
            .catch(function() { showAlert('Error de conexión', 'error'); });
        };

        /* ── Recargar tabla completa ── */
        function recargarTabla() {
            fetch(API + '?action=list')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.ok) return;
                tbody.innerHTML = data.data.length
                    ? data.data.map(filaHtml).join('')
                    : '<tr><td colspan="6" class="text-center text-muted py-4">No hay menús registrados.</td></tr>';
            });
        }

        /* ── GUARDAR (crear o actualizar) ── */
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('btnGuardarMenu');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            var params = new URLSearchParams(new FormData(form));
            params.set('action', 'save');

            fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-save me-2"></i>Guardar menú';
                if (data.ok) {
                    showAlert(data.msg, 'ok');
                    recargarTabla();
                    resetForm();
                } else {
                    showAlert(data.msg, 'error');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-save me-2"></i>Guardar menú';
                showAlert('Error de conexión', 'error');
            });
        });

    })();
    </script>
</body>
</html>
