<?php

function admin_nav_items(): array
{
    return [
        'dashboard' => ['href' => 'admin_1.php?panel=dashboard', 'icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard'],
        'contenedores' => ['href' => 'admin_1.php?panel=contenedores', 'icon' => 'bi-layout-text-window-reverse', 'label' => 'Contenedores'],
        'menus' => ['href' => 'admin_1.php?panel=menus', 'icon' => 'bi-list-nested', 'label' => 'Menús'],
        'submenus' => ['href' => 'admin_1.php?panel=submenus', 'icon' => 'bi-diagram-3', 'label' => 'Submenús'],
        'configuracion' => ['href' => 'admin_1.php?panel=configuracion', 'icon' => 'bi-sliders2-vertical', 'label' => 'Configuración'],
        'auditoria' => ['href' => 'auditoria_log.php', 'icon' => 'bi-clock-history', 'label' => 'Auditoría / Logs'],
    ];
}

function admin_render_layout_start(array $options = []): void
{
    $title = $options['title'] ?? 'Panel CMS';
    $pageTitle = $options['page_title'] ?? 'Panel CMS';
    $breadcrumb = $options['breadcrumb'] ?? '';
    $activePanel = $options['active_panel'] ?? '';
    $institutionName = $options['institution_name'] ?? 'Colegio San Pablo';
    $institutionShortName = $options['institution_short_name'] ?? $institutionName;
    $adminName = $options['admin_name'] ?? 'Administrador';
    $headerActions = $options['header_actions'] ?? '';
    $extraHead = $options['extra_head'] ?? '';
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= cms_e($title) ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --sp-primary: #1f8f6b;
            --sp-primary-soft: #e8f7f1;
            --sp-secondary: #12324a;
            --sp-muted: #72809a;
            --sp-dark: #162338;
            --sp-border: #dbe4ef;
            --sp-bg: #f4f7fb;
            --sp-card: #ffffff;
            --sp-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            --sp-radius: 22px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--sp-bg);
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
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(31, 143, 107, 0.25), rgba(31, 143, 107, 0.08));
            color: #fff;
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
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            background: #42d392;
            box-shadow: 0 0 0 6px rgba(66, 211, 146, 0.15);
            margin-right: 10px;
        }
        .content-area { flex: 1; padding: 28px; min-width: 0; }
        .topbar {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: var(--sp-shadow);
            border-radius: 28px;
            padding: 18px 22px;
            margin-bottom: 24px;
        }
        .topbar h2 { margin: 0; font-size: 1.65rem; font-weight: 700; color: var(--sp-dark); }
        .crumb { color: var(--sp-muted); font-size: 0.92rem; }
        .topbar-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: flex-end; }
        .btn-soft { background: var(--sp-primary-soft); color: var(--sp-primary); border: none; }
        .btn-soft:hover { background: #d7f1e6; color: #136c51; }
        .btn-premium {
            background: linear-gradient(135deg, var(--sp-primary), #27b785);
            color: #fff;
            border: none;
            box-shadow: 0 14px 28px rgba(31, 143, 107, 0.25);
        }
        .btn-premium:hover { color: #fff; transform: translateY(-1px); }
        .btn-admin-action { background: #12324a; color: #fff; border: none; }
        .btn-admin-action:hover { background: #1b496b; color: #fff; }
        .user-pill {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, var(--sp-secondary), #274f70);
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
        .section-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
        .metric-card { border-radius: var(--sp-radius); padding: 22px; min-height: 170px; color: #fff; box-shadow: var(--sp-shadow); }
        .metric-card .big-number { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 8px; }
        .metric-card h5 { margin: 0 0 8px; font-weight: 700; }
        .metric-card p { margin: 0; color: rgba(255,255,255,.78); }
        .metric-green { background: linear-gradient(135deg, #1f8f6b, #34c38f); }
        .metric-blue { background: linear-gradient(135deg, #215b8f, #3e8be4); }
        .metric-gold { background: linear-gradient(135deg, #ba7d10, #f4b942); }
        .table-modern { margin: 0; }
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
        .badge-soft { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; font-size: 0.84rem; font-weight: 600; }
        .badge-soft.success { background: #e8fbf2; color: #1b8f67; }
        .badge-soft.warning { background: #fff6e0; color: #b7791f; }
        .badge-soft.dark { background: #eef2f7; color: #344256; }
        .table-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .form-label { font-weight: 600; color: var(--sp-secondary); margin-bottom: 8px; }
        .form-control, .form-select { border-radius: 14px; border: 1px solid var(--sp-border); min-height: 44px; padding: 10px 12px; }
        textarea.form-control { min-height: 120px; }
        .mobile-sidebar-toggle { display: none; }
        @media (max-width: 1199px) {
            .sidebar { position: fixed; left: 0; top: 0; transform: translateX(-100%); transition: .3s ease; }
            .sidebar.show { transform: translateX(0); }
            .mobile-sidebar-toggle { display: inline-flex; }
            .content-area { width: 100%; padding: 18px; }
        }
        @media (max-width: 767px) {
            .topbar-actions { justify-content: start; }
            .section-card { padding: 18px; }
        }
    </style>
    <?= $extraHead ?>
</head>
<body>
    <div class="admin-shell">
        <aside class="sidebar" id="adminSidebar">
            <div class="brand-box">
                <div class="brand-icon"><i class="bi bi-building"></i></div>
                <div>
                    <h1><?= cms_e($institutionShortName) ?></h1>
                    <p>Panel institucional</p>
                </div>
            </div>
            <nav class="nav flex-column">
                <?php foreach (admin_nav_items() as $key => $item): ?>
                    <a class="nav-link <?= $activePanel === $key ? 'active' : '' ?>" href="<?= cms_e($item['href']) ?>">
                        <i class="bi <?= cms_e($item['icon']) ?>"></i><?= cms_e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-footer">
                <div class="d-flex align-items-center mb-2"><span class="status-dot"></span><strong>Sistema activo</strong></div>
                <small><?= cms_e($institutionName) ?></small>
            </div>
        </aside>
        <main class="content-area">
            <header class="topbar">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-soft mobile-sidebar-toggle" id="toggleSidebar" type="button"><i class="bi bi-list"></i></button>
                            <div>
                                <h2><?= cms_e($pageTitle) ?></h2>
                                <div class="crumb"><?= cms_e($breadcrumb) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="topbar-actions">
                            <?= $headerActions ?>
                            <div class="user-pill"><?= cms_e(strtoupper(substr(trim($adminName) !== '' ? trim($adminName) : 'AD', 0, 2))) ?></div>
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
        document.addEventListener('DOMContentLoaded', function () {
            var sidebar = document.getElementById('adminSidebar');
            var toggle = document.getElementById('toggleSidebar');
            if (toggle && sidebar) {
                toggle.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                });
            }
        });
    </script>
    <?= $extraScripts ?>
</body>
</html>
    <?php
}
