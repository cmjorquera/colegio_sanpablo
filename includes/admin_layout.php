<?php

function admin_render_layout_start(array $options = []): void
{
    $title = $options['title'] ?? 'Panel CMS';
    $pageTitle = $options['page_title'] ?? 'Administrador del sitio';
    $breadcrumb = $options['breadcrumb'] ?? 'Panel general';
    $activePanel = $options['active_panel'] ?? 'dashboard';
    $headerActions = $options['header_actions'] ?? '';
    $extraHead = $options['extra_head'] ?? '';

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
        .admin-shell { display: flex; min-height: 100vh; position: relative; }
        .admin-sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            opacity: 0;
            visibility: hidden;
            transition: .25s ease;
            z-index: 1035;
        }
        .admin-sidebar {
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
            transition: width .25s ease, transform .25s ease;
            flex-shrink: 0;
        }
        .admin-content {
            flex: 1;
            min-width: 0;
            padding: 28px;
            transition: .25s ease;
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
            flex-shrink: 0;
        }
        .brand-copy h1 { font-size: 1.05rem; margin: 0; font-weight: 700; }
        .brand-copy p { margin: 2px 0 0; color: rgba(255, 255, 255, 0.65); font-size: 0.87rem; }
        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.78);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 16px;
            font-weight: 500;
            margin-bottom: 8px;
            transition: .25s ease;
            white-space: nowrap;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(31, 143, 107, 0.25), rgba(31, 143, 107, 0.08));
            color: #fff;
            transform: translateX(4px);
        }
        .admin-shell.sidebar-collapsed .admin-sidebar { width: 96px; }
        .admin-shell.sidebar-collapsed .brand-copy,
        .admin-shell.sidebar-collapsed .nav-link span { display: none; }
        .admin-shell.sidebar-collapsed .brand-box { justify-content: center; }
        .admin-shell.sidebar-collapsed .admin-sidebar .nav-link { justify-content: center; }
        .admin-topbar,
        .section-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: var(--sp-shadow);
            border-radius: 28px;
        }
        .admin-topbar { padding: 18px 22px; margin-bottom: 24px; }
        .admin-topbar h2 { margin: 0; font-size: 1.65rem; font-weight: 700; color: var(--sp-dark); }
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
            color: #fff;
            border: none;
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
        textarea.form-control { min-height: 120px; }
        .mobile-sidebar-toggle { display: inline-flex; }
        @media (max-width: 1199px) {
            .admin-sidebar {
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
            .admin-content { width: 100%; padding: 18px; }
            .admin-shell.sidebar-collapsed .admin-sidebar { width: 290px; }
            .admin-shell.sidebar-collapsed .brand-copy,
            .admin-shell.sidebar-collapsed .nav-link span { display: initial; }
        }
    </style>
    <?= $extraHead ?>
</head>
<body>
    <div class="admin-shell" id="adminShell">
        <div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="brand-box">
                <div class="brand-icon"><i class="bi bi-building"></i></div>
                <div class="brand-copy">
                    <h1>Panel San Pablo</h1>
                    <p>CMS institucional modular</p>
                </div>
            </div>

            <nav class="nav flex-column">
                <?php foreach ($navItems as $item): ?>
                    <a class="nav-link <?= $activePanel === $item['panel'] ? 'active' : '' ?>" href="<?= cms_e($item['href']) ?>">
                        <i class="<?= cms_e($item['icon']) ?>"></i>
                        <span><?= cms_e($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
                <a class="nav-link" href="index.php" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>Vista del sitio</span>
                </a>
            </nav>
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
                    <div class="d-flex gap-2 flex-wrap">
                        <?= $headerActions ?>
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
