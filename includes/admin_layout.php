<?php

function admin_render_layout_start(array $options = []): void
{
    $title = $options['title'] ?? 'Panel CMS';
    $pageTitle = $options['page_title'] ?? 'Administrador del sitio';
    $breadcrumb = $options['breadcrumb'] ?? 'Panel general';
    $activePanel = $options['active_panel'] ?? 'dashboard';
    $headerActions = $options['header_actions'] ?? '';
    $extraHead = $options['extra_head'] ?? '';
    $institutionName = trim((string) ($options['institution_name'] ?? 'Institución activa'));
    $institutionShortName = trim((string) ($options['institution_short_name'] ?? $institutionName));
    $institutionLogo = trim((string) ($options['institution_logo'] ?? ''));
    $adminName = trim((string) ($options['admin_name'] ?? ($_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador')));
    $adminInitial = strtoupper(substr($adminName !== '' ? $adminName : 'A', 0, 1));

    $navItems = [
        ['panel' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi bi-grid-1x2-fill', 'href' => 'admin.php?panel=dashboard'],
        ['panel' => 'contenedores', 'label' => 'Contenedores del sitio', 'icon' => 'bi bi-window-stack', 'href' => 'admin.php?panel=contenedores'],
        ['panel' => 'menus', 'label' => 'Menú principal', 'icon' => 'bi bi-list-nested', 'href' => 'admin.php?panel=menus'],
        ['panel' => 'submenus', 'label' => 'Submenús', 'icon' => 'bi bi-diagram-3', 'href' => 'admin.php?panel=submenus'],
        ['panel' => 'configuracion', 'label' => 'Configuración institucional', 'icon' => 'bi bi-sliders2-vertical', 'href' => 'admin.php?panel=configuracion'],
    ];
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= cms_e($title) ?></title>
    <link rel="shortcut icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --sp-primary: #3558d5;
            --sp-primary-strong: #2847b5;
            --sp-primary-soft: #edf2ff;
            --sp-secondary: #162338;
            --sp-accent: #2ec5a1;
            --sp-dark: #0d1526;
            --sp-border: #dfe5f2;
            --sp-muted: #72809a;
            --sp-bg: #eef3f9;
            --sp-card: #ffffff;
            --sp-sidebar: #121c34;
            --sp-sidebar-2: #182543;
            --sp-sidebar-border: rgba(255, 255, 255, 0.08);
            --sp-shadow: 0 20px 60px rgba(9, 20, 48, 0.10);
            --sp-shadow-soft: 0 12px 30px rgba(18, 35, 68, 0.08);
        }
        * { box-sizing: border-box; }
        body,
        body.admin-panel {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            background:
                radial-gradient(circle at top left, rgba(53, 88, 213, 0.10), transparent 24%),
                radial-gradient(circle at top right, rgba(46, 197, 161, 0.08), transparent 22%),
                var(--sp-bg);
            color: var(--sp-secondary);
        }
        .admin-shell { display: flex; min-height: 100vh; position: relative; }
        .admin-sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(7, 15, 29, 0.45);
            opacity: 0;
            visibility: hidden;
            transition: .25s ease;
            z-index: 1035;
        }
        .admin-sidebar {
            width: 118px;
            background: linear-gradient(180deg, var(--sp-sidebar) 0%, var(--sp-sidebar-2) 100%);
            color: #fff;
            padding: 18px 14px 22px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 16px 0 40px rgba(8, 16, 32, 0.18);
            z-index: 1040;
            transition: width .25s ease, transform .25s ease;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }
        .admin-content {
            flex: 1;
            min-width: 0;
            padding: 18px 20px 24px;
            transition: .25s ease;
        }
        .brand-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 12px 8px 20px;
            margin-bottom: 18px;
            border-bottom: 1px solid var(--sp-sidebar-border);
        }
        .brand-icon {
            width: 68px;
            height: 68px;
            border-radius: 24px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #27d5a7, #3fbe84);
            color: #fff;
            font-size: 1.5rem;
            flex-shrink: 0;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.25), 0 16px 32px rgba(39, 213, 167, 0.25);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.18);
        }
        .brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #fff;
        }
        .brand-copy {
            text-align: center;
            padding: 0 4px;
        }
        .brand-copy h1 {
            font-size: 0.95rem;
            margin: 0;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }
        .brand-copy p {
            margin: 4px 0 0;
            color: rgba(255, 255, 255, 0.60);
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .admin-nav {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            margin-top: 4px;
        }
        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.82);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 60px;
            padding: 0;
            border-radius: 20px;
            font-weight: 500;
            transition: .25s ease;
            white-space: nowrap;
            position: relative;
            border: 1px solid transparent;
        }
        .admin-sidebar .nav-link i { font-size: 1.25rem; }
        .admin-sidebar .nav-link span {
            position: absolute;
            left: 84px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(10, 18, 34, 0.92);
            color: #fff;
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 0.82rem;
            line-height: 1;
            opacity: 0;
            visibility: hidden;
            transition: .18s ease;
            box-shadow: var(--sp-shadow-soft);
            pointer-events: none;
        }
        .admin-sidebar .nav-link:hover span,
        .admin-shell.sidebar-collapsed .admin-sidebar .nav-link span {
            opacity: 1;
            visibility: visible;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: linear-gradient(180deg, rgba(46, 197, 161, 0.18), rgba(53, 88, 213, 0.14));
            border-color: rgba(46, 197, 161, 0.18);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
        }
        .admin-sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: -14px;
            top: 10px;
            bottom: 10px;
            width: 4px;
            border-radius: 999px;
            background: linear-gradient(180deg, #27d5a7, #4d6fff);
        }
        .admin-sidebar-footer {
            margin-top: auto;
            padding-top: 18px;
            border-top: 1px solid var(--sp-sidebar-border);
            display: flex;
            justify-content: center;
        }
        .admin-shell.sidebar-collapsed .admin-sidebar {
            width: 308px;
            align-items: stretch;
        }
        .admin-shell.sidebar-collapsed .brand-box {
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            padding: 14px;
            gap: 14px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--sp-sidebar-border);
            border-radius: 24px;
        }
        .admin-shell.sidebar-collapsed .brand-copy {
            text-align: left;
            display: block;
        }
        .admin-shell.sidebar-collapsed .admin-nav {
            align-items: stretch;
        }
        .admin-shell.sidebar-collapsed .admin-sidebar .nav-link {
            width: 100%;
            justify-content: flex-start;
            gap: 14px;
            padding: 0 18px;
        }
        .admin-shell.sidebar-collapsed .admin-sidebar .nav-link span {
            position: static;
            transform: none;
            background: transparent;
            padding: 0;
            border-radius: 0;
            opacity: 1;
            visibility: visible;
            box-shadow: none;
        }
        .admin-topbar,
        .section-card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(255, 255, 255, 0.72);
            box-shadow: var(--sp-shadow);
            border-radius: 28px;
        }
        .admin-topbar {
            padding: 14px 18px;
            margin-bottom: 18px;
            border-radius: 24px;
        }
        .admin-topbar h2 { margin: 0; font-size: 1.35rem; font-weight: 700; color: var(--sp-dark); }
        .crumb { color: var(--sp-muted); font-size: 0.84rem; }
        .topbar-search {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 320px;
            max-width: 420px;
            flex: 1;
            padding: 0 14px;
            min-height: 46px;
            border-radius: 14px;
            border: 1px solid var(--sp-border);
            background: #f8fbff;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
        }
        .topbar-search i { color: var(--sp-primary); font-size: 1rem; }
        .topbar-search input {
            border: 0;
            outline: none;
            background: transparent;
            width: 100%;
            color: var(--sp-secondary);
            font-size: 0.95rem;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }
        .topbar-icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--sp-primary);
            background: transparent;
            transition: .2s ease;
        }
        .topbar-icon-btn:hover {
            background: var(--sp-primary-soft);
            border-color: rgba(53, 88, 213, 0.10);
            color: var(--sp-primary-strong);
        }
        .topbar-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-left: 10px;
            border-left: 1px solid var(--sp-border);
            min-width: 0;
        }
        .topbar-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--sp-primary), #53a4ff);
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(53, 88, 213, 0.22);
            overflow: hidden;
        }
        .topbar-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #fff;
        }
        .topbar-profile-copy {
            min-width: 0;
        }
        .topbar-profile-copy strong {
            display: block;
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.92rem;
            color: var(--sp-primary-strong);
        }
        .topbar-profile-copy span {
            display: block;
            font-size: 0.75rem;
            color: var(--sp-muted);
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .section-card { padding: 16px; margin-bottom: 18px; }
        .section-head { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 14px; flex-wrap: wrap; }
        .section-head h3 { font-size: 1.05rem; margin: 0; color: var(--sp-dark); font-weight: 700; }
        .section-head p { margin: 4px 0 0; color: var(--sp-muted); font-size: 0.9rem; }
        .metric-card {
            position: relative;
            overflow: hidden;
            min-height: 148px;
            border-radius: 20px;
            padding: 18px;
            color: #fff;
            box-shadow: var(--sp-shadow);
        }
        .metric-green { background: linear-gradient(135deg, #1b6d88, #3bbfb7); }
        .metric-blue { background: linear-gradient(135deg, #3558d5, #5b89ff); }
        .metric-gold { background: linear-gradient(135deg, #1b8777, #35c28d); }
        .metric-card .big-number { font-size: 1.7rem; font-weight: 800; line-height: 1; margin-bottom: 8px; }
        .table-modern thead th {
            background: #f7f9fc;
            color: var(--sp-secondary);
            border-bottom: 1px solid var(--sp-border);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 12px 12px;
            white-space: nowrap;
        }
        .table-modern tbody td { vertical-align: middle; padding: 12px 12px; border-color: #edf2f7; }
        .table-modern tbody tr {
            height: auto;
        }
        .table-modern td strong {
            font-size: 1rem;
            line-height: 1.25;
        }
        .table-modern .text-muted {
            font-size: 0.92rem;
            line-height: 1.45;
        }
        .table-modern td .text-muted {
            max-width: 760px;
        }
        .cell-actions {
            white-space: nowrap;
            width: 1%;
        }
        .table-actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
        }
        .table-actions .btn,
        .table-actions a.btn {
            width: 44px;
            min-width: 44px;
            height: 38px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }
        .table-actions .btn i,
        .table-actions a.btn i {
            margin: 0;
            font-size: 0.95rem;
        }
        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .btn {
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 12px;
        }
        .badge-soft.success { background: #e8fbf2; color: #1b8f67; }
        .badge-soft.warning { background: #fff6e0; color: #b7791f; }
        .badge-soft.dark { background: #eef2f7; color: #344256; }
        .btn-soft { background: var(--sp-primary-soft); color: var(--sp-primary); border: none; }
        .btn-soft:hover { background: #dfe8ff; color: var(--sp-primary-strong); }
        .btn-premium {
            background: linear-gradient(135deg, var(--sp-primary), #4d6fff);
            color: #fff;
            border: none;
        }
        .btn-premium:hover { color: #fff; }
        .btn-admin-action {
            min-height: 38px;
            padding: 6px 14px;
            border-radius: 12px;
            border: 1px solid rgba(53, 88, 213, 0.10);
            background: linear-gradient(135deg, var(--sp-primary), #4d6fff);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(53, 88, 213, 0.18);
        }
        .btn-admin-action:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(53, 88, 213, 0.22);
        }
        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid var(--sp-border);
            min-height: 0;
            height: auto;
            font-size: 14px;
            padding: 6px 10px;
            color: var(--sp-secondary);
            box-shadow: none;
        }
        .form-control:focus, .form-select:focus {
            border-color: rgba(53, 88, 213, 0.38);
            box-shadow: 0 0 0 0.25rem rgba(53, 88, 213, 0.12);
        }
        textarea.form-control { min-height: 96px; }
        .form-label { margin-bottom: 0.35rem; font-size: 0.9rem; }
        .card,
        .modal-body { padding: 16px; }
        .modal-title { font-size: 18px; }
        .modal-dialog { max-width: 900px; }
        .modal-body .row { margin-bottom: 10px; }
        .modal-body .col-md-6 { padding-bottom: 10px; }
        .modal-body input,
        .modal-body textarea,
        .modal-body select { font-size: 14px; }
        .modal-content {
            border-radius: 20px;
            border: 1px solid rgba(53, 88, 213, 0.08);
            box-shadow: var(--sp-shadow);
        }
        .modal-backdrop.show {
            opacity: 0.42;
            backdrop-filter: blur(3px);
        }
        .modal-header {
            padding: 14px 16px;
            border-bottom: 1px solid #e7edf7;
            background: linear-gradient(180deg, rgba(248, 251, 255, 0.98), rgba(242, 247, 255, 0.94));
        }
        .modal-header .btn-close {
            transform: scale(0.88);
            opacity: 0.72;
        }
        .modal-header .btn-close:hover {
            opacity: 1;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            padding: 14px 16px;
            border-top: 1px solid #e7edf7;
            background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(247,250,255,0.96));
        }
        .modal-footer .btn {
            width: auto;
            margin: 0;
        }
        .modal-body .field-card:last-child,
        .modal-body .row:last-child {
            margin-bottom: 0;
        }
        #previewModal .modal-dialog {
            max-width: 1120px;
        }
        #previewModal .modal-header {
            background: linear-gradient(135deg, rgba(53, 88, 213, 0.10), rgba(46, 197, 161, 0.08));
        }
        #previewModal .modal-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--sp-dark);
        }
        #previewModal .modal-body {
            padding: 0;
            background: #f6f9fd;
        }
        .botones-modal {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .botones-modal .btn { width: auto; }
        .d-flex.gap-2.flex-wrap > .btn,
        .d-flex.gap-2.flex-wrap > a.btn,
        .d-flex.gap-2 > .btn,
        .d-flex.gap-2 > a.btn {
            width: auto;
            flex: 0 0 auto;
        }
        .mobile-sidebar-toggle { display: inline-flex; }
        @media (max-width: 1199px) {
            .admin-sidebar {
                width: 290px;
                position: fixed;
                left: 0;
                top: 0;
                transform: translateX(-100%);
            }
            .admin-shell.sidebar-open .admin-sidebar { transform: translateX(0); }
            .admin-shell.sidebar-open .admin-sidebar-overlay {
                opacity: 1;
                visibility: visible;
            }
            .admin-content { width: 100%; padding: 16px; }
            .admin-topbar > .d-flex { align-items: stretch !important; }
            .topbar-search { order: 3; min-width: 100%; max-width: none; }
            .topbar-right { width: 100%; justify-content: flex-end; }
            .admin-shell.sidebar-collapsed .admin-sidebar { width: 290px; }
            .admin-shell.sidebar-collapsed .brand-box {
                flex-direction: row;
                background: rgba(255,255,255,0.04);
            }
        }
        @media (max-width: 767px) {
            .admin-topbar h2 { font-size: 1.25rem; }
            .section-card { padding: 14px; }
            .modal-dialog { margin: 0.65rem; }
            .table-modern td .text-muted {
                max-width: none;
            }
            .topbar-right {
                gap: 6px;
                flex-wrap: wrap;
            }
            .topbar-profile {
                width: 100%;
                justify-content: flex-end;
                border-left: 0;
                padding-left: 0;
            }
        }
    </style>
    <?= $extraHead ?>
</head>
<body class="admin-panel">
    <div class="admin-shell" id="adminShell">
        <div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="brand-box">
                <div class="brand-icon">
                    <?php if ($institutionLogo !== ''): ?>
                        <img src="<?= cms_e($institutionLogo) ?>" alt="<?= cms_e($institutionShortName) ?>" onerror="this.style.display='none'; this.parentNode.classList.add('brand-icon-fallback'); this.parentNode.innerHTML='<i class=&quot;bi bi-building&quot;></i>';">
                    <?php else: ?>
                        <i class="bi bi-building"></i>
                    <?php endif; ?>
                </div>
                <div class="brand-copy">
                    <h1><?= cms_e($institutionShortName) ?></h1>
                    <p>Panel institucional</p>
                </div>
            </div>

            <nav class="nav admin-nav">
                <?php foreach ($navItems as $item): ?>
                    <a class="nav-link <?= $activePanel === $item['panel'] ? 'active' : '' ?>" href="<?= cms_e($item['href']) ?>">
                        <i class="<?= cms_e($item['icon']) ?>"></i>
                        <span><?= cms_e($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="admin-sidebar-footer">
                <a class="nav-link" href="index.php" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>Vista del sitio</span>
                </a>
            </div>
        </aside>

        <main class="admin-content">
            <header class="admin-topbar">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-soft mobile-sidebar-toggle" id="toggleSidebar" type="button">
                            <i class="bi bi-list"></i>
                        </button>
                        <div>
                            <h2><?= cms_e($pageTitle) ?></h2>
                            <div class="crumb"><?= cms_e($breadcrumb) ?></div>
                        </div>
                    </div>
                    <label class="topbar-search" aria-label="Buscar">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Buscar (Ctrl + B)">
                    </label>
                    <div class="topbar-right">
                        <button class="topbar-icon-btn" type="button" title="Institución">
                            <i class="bi bi-buildings"></i>
                        </button>
                        <button class="topbar-icon-btn" type="button" title="Vista">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="topbar-icon-btn" type="button" title="Notificaciones">
                            <i class="bi bi-bell"></i>
                        </button>
                        <button class="topbar-icon-btn" type="button" title="Ayuda">
                            <i class="bi bi-question-circle"></i>
                        </button>
                        <?= $headerActions ?>
                        <div class="topbar-profile">
                            <div class="topbar-avatar">
                                <?php if ($institutionLogo !== ''): ?>
                                    <img src="<?= cms_e($institutionLogo) ?>" alt="<?= cms_e($institutionShortName) ?>" onerror="this.remove();">
                                <?php else: ?>
                                    <?= cms_e($adminInitial) ?>
                                <?php endif; ?>
                            </div>
                            <div class="topbar-profile-copy">
                                <strong><?= cms_e($adminName) ?></strong>
                                <span><?= cms_e($institutionName) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
<?php
}

function admin_render_layout_end(array $options = []): void
{
    $extraScripts = $options['extra_scripts'] ?? '';
    ?>
        </main>
    </div>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        (function () {
            var shell = document.getElementById('adminShell');
            var toggle = document.getElementById('toggleSidebar');
            var overlay = document.getElementById('adminSidebarOverlay');

            if (!shell || !toggle) {
                return;
            }

            function isMobile() {
                return window.innerWidth <= 1199;
            }

            function closeMobileSidebar() {
                shell.classList.remove('sidebar-open');
            }

            toggle.addEventListener('click', function () {
                if (isMobile()) {
                    shell.classList.toggle('sidebar-open');
                    return;
                }
                shell.classList.toggle('sidebar-collapsed');
            });

            if (overlay) {
                overlay.addEventListener('click', closeMobileSidebar);
            }

            window.addEventListener('resize', function () {
                if (!isMobile()) {
                    closeMobileSidebar();
                }
            });
        })();
    </script>
    <?= $extraScripts ?>
</body>
</html>
<?php
}
