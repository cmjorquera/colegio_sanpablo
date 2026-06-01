<?php

function admin_nav_items(): array
{
    return [
        'dashboard'    => ['href' => 'admin.php?panel=dashboard',    'icon' => 'bi-grid-1x2',               'label' => 'Dashboard'],
        'contenedores' => ['href' => 'admin.php?panel=contenedores', 'icon' => 'bi-layout-text-window-reverse', 'label' => 'Contenedores'],
        'menus'        => ['href' => 'admin.php?panel=menus',        'icon' => 'bi-list-nested',             'label' => 'Menús'],
        'configuracion'=> ['href' => 'admin.php?panel=configuracion','icon' => 'bi-sliders2-vertical',      'label' => 'Configuración'],
        'auditoria'    => ['href' => 'auditoria_log.php',              'icon' => 'bi-clock-history',          'label' => 'Auditoría / Logs'],
        'analitica'    => ['href' => 'analitica.php',                'icon' => 'bi-bar-chart-line',         'label' => 'Analítica'],
    ];
}

function admin_nav_groups(): array
{
    return [
        'PRINCIPAL'     => ['dashboard', 'contenedores'],
        'GESTIÓN'       => ['menus'],
        'SISTEMA'       => ['configuracion', 'auditoria'],
        'ANALÍTICA'     => ['analitica'],
    ];
}

function admin_valid_hex_color(?string $value, string $fallback): string
{
    $value = trim((string) $value);
    return preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value) ? $value : $fallback;
}

function admin_hex_to_rgb(string $hex): string
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    return hexdec(substr($hex, 0, 2)) . ',' . hexdec(substr($hex, 2, 2)) . ',' . hexdec(substr($hex, 4, 2));
}

function admin_render_layout_start(array $options = []): void
{
    $title              = $options['title']              ?? 'Panel CMS';
    $pageTitle          = $options['page_title']         ?? 'Panel CMS';
    $breadcrumb         = $options['breadcrumb']         ?? '';
    $activePanel        = $options['active_panel']       ?? '';
    $institutionName    = $options['institution_name']   ?? 'Colegio San Pablo';
    $institutionShortName = $options['institution_short_name'] ?? $institutionName;
    $adminName          = $options['admin_name']         ?? 'Administrador';
    $headerActions      = $options['header_actions']     ?? '';
    $extraHead          = $options['extra_head']         ?? '';
    $admPrimary         = admin_valid_hex_color($options['color_primario']   ?? null, '#F0A000');
    $admSecondary       = admin_valid_hex_color($options['color_secundario'] ?? null, '#EF6C00');
    $admTertiary        = admin_valid_hex_color($options['color_terciario']  ?? null, '#1976D2');
    $admQuaternary      = admin_valid_hex_color($options['color_cuaternario'] ?? null, '#E53935');
    $admPrimaryRgb      = admin_hex_to_rgb($admPrimary);
    $admSecondaryRgb    = admin_hex_to_rgb($admSecondary);
    $admTertiaryRgb     = admin_hex_to_rgb($admTertiary);
    $admQuaternaryRgb   = admin_hex_to_rgb($admQuaternary);

    $navItems  = admin_nav_items();
    $navGroups = admin_nav_groups();
    $initials  = strtoupper(substr(trim($adminName) !== '' ? trim($adminName) : 'AD', 0, 2));
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= cms_e($title) ?></title>
    <script>
        if (localStorage.getItem('adminSidebarCollapsed') === '1') {
            document.documentElement.classList.add('adm-sidebar-collapsed');
        }
    </script>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <style>
        /* ─── Variables ─────────────────────────────────────────── */
        :root {
            --adm-sidebar-w:   260px;
            --adm-sidebar-col: 68px;
            --adm-sidebar-bg:  #0f172a;
            --adm-sidebar-2:   #182235;
            --adm-primary:     <?= cms_e($admPrimary) ?>;
            --adm-primary-rgb: <?= cms_e($admPrimaryRgb) ?>;
            --adm-primary-soft: rgba(<?= cms_e($admPrimaryRgb) ?>,.10);
            --adm-secondary:   <?= cms_e($admSecondary) ?>;
            --adm-secondary-rgb: <?= cms_e($admSecondaryRgb) ?>;
            --adm-tertiary:    <?= cms_e($admTertiary) ?>;
            --adm-tertiary-rgb: <?= cms_e($admTertiaryRgb) ?>;
            --adm-danger-brand: <?= cms_e($admQuaternary) ?>;
            --adm-danger-brand-rgb: <?= cms_e($admQuaternaryRgb) ?>;
            --adm-brand-gradient: linear-gradient(90deg, var(--adm-primary) 0%, var(--adm-secondary) 34%, var(--adm-tertiary) 68%, var(--adm-danger-brand) 100%);
            --adm-brand-gradient-soft: linear-gradient(135deg, rgba(var(--adm-primary-rgb),.14), rgba(var(--adm-secondary-rgb),.13), rgba(var(--adm-tertiary-rgb),.12), rgba(var(--adm-danger-brand-rgb),.13));
            --adm-accent:      <?= cms_e($admSecondary) ?>;
            --adm-bg:          #f3f6fa;
            --adm-card:        #ffffff;
            --adm-border:      #e2e8f0;
            --adm-border-dark: #cbd5e1;
            --adm-text:        #0f172a;
            --adm-text-2:      #334155;
            --adm-muted:       #64748b;
            --adm-shadow-sm:   0 1px 3px rgba(0,0,0,.08),0 1px 2px rgba(0,0,0,.05);
            --adm-shadow:      0 4px 16px rgba(0,0,0,.07);
            --adm-shadow-lg:   0 10px 40px rgba(0,0,0,.10);
            --adm-radius:      14px;
            --adm-radius-sm:   8px;
            --adm-radius-lg:   20px;
            --adm-transition:  .22s ease;
        }

        /* ─── Reset / Base ──────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            font-size: .9375rem;
            line-height: 1.5;
            background: var(--adm-bg);
            color: var(--adm-text);
        }
        a { text-decoration: none; color: inherit; }

        /* ─── Shell ─────────────────────────────────────────────── */
        .admin-shell {
            display: flex;
            min-height: 100vh;
        }

        /* ─── Sidebar ───────────────────────────────────────────── */
        .admin-sidebar {
            width: var(--adm-sidebar-w);
            background: var(--adm-sidebar-bg);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
            flex-shrink: 0;
            transition: width var(--adm-transition);
            z-index: 1040;
        }

        /* Brand */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,.06);
            flex-shrink: 0;
            overflow: hidden;
            white-space: nowrap;
        }
        .sidebar-brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--adm-tertiary), var(--adm-primary));
            display: grid;
            place-items: center;
            font-size: 1.3rem;
            color: #fff;
            flex-shrink: 0;
        }
        .sidebar-brand-text { overflow: hidden; }
        .sidebar-brand-name {
            display: block;
            font-weight: 700;
            font-size: .95rem;
            line-height: 1.2;
            color: #fff;
        }
        .sidebar-brand-sub {
            display: block;
            font-size: .75rem;
            color: rgba(255,255,255,.45);
            margin-top: 2px;
        }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 12px 12px 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.1) transparent;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }

        .nav-group { margin-bottom: 6px; }
        .nav-group-label {
            display: block;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            color: rgba(255,255,255,.3);
            text-transform: uppercase;
            padding: 14px 8px 6px;
            white-space: nowrap;
            overflow: hidden;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: var(--adm-radius-sm);
            font-size: .875rem;
            font-weight: 500;
            color: rgba(255,255,255,.6);
            transition: background var(--adm-transition), color var(--adm-transition);
            white-space: nowrap;
            overflow: hidden;
            margin-bottom: 2px;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        .nav-item:hover {
            background: rgba(255,255,255,.07);
            color: rgba(255,255,255,.9);
        }
        .nav-item.active {
            background: rgba(var(--adm-tertiary-rgb),.20);
            color: #fff;
        }
        .nav-item.active .nav-item-icon { color: var(--adm-primary); }
        .nav-item-icon {
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
            color: rgba(255,255,255,.45);
            transition: color var(--adm-transition);
        }
        .nav-item-label { flex: 1; overflow: hidden; text-overflow: ellipsis; }

        /* Bottom actions */
        .sidebar-bottom {
            padding: 10px 12px 14px;
            border-top: 1px solid rgba(255,255,255,.06);
            flex-shrink: 0;
        }
        .sidebar-bottom .nav-item { color: rgba(255,255,255,.45); font-size: .82rem; }
        .sidebar-bottom .nav-item:hover { color: rgba(255,255,255,.75); }

        .sidebar-collapse-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: var(--adm-radius-sm);
            font-size: .82rem;
            font-weight: 500;
            color: rgba(255,255,255,.4);
            background: none;
            border: none;
            width: 100%;
            cursor: pointer;
            transition: background var(--adm-transition), color var(--adm-transition);
            white-space: nowrap;
            overflow: hidden;
        }
        .sidebar-collapse-btn:hover { background: rgba(255,255,255,.06); color: rgba(255,255,255,.7); }
        .sidebar-collapse-btn .collapse-icon { font-size: 1rem; flex-shrink: 0; width: 20px; text-align: center; transition: transform var(--adm-transition); }
        .sidebar-collapse-btn .collapse-label { overflow: hidden; }

        /* ─── Collapsed state ───────────────────────────────────── */
        .admin-shell.collapsed .admin-sidebar,
        .adm-sidebar-collapsed .admin-shell .admin-sidebar { width: var(--adm-sidebar-col); }
        .admin-shell.collapsed .sidebar-brand-text,
        .admin-shell.collapsed .nav-group-label,
        .admin-shell.collapsed .nav-item-label,
        .admin-shell.collapsed .collapse-label,
        .adm-sidebar-collapsed .admin-shell .sidebar-brand-text,
        .adm-sidebar-collapsed .admin-shell .nav-group-label,
        .adm-sidebar-collapsed .admin-shell .nav-item-label,
        .adm-sidebar-collapsed .admin-shell .collapse-label { display: none; }
        .admin-shell.collapsed .sidebar-brand,
        .adm-sidebar-collapsed .admin-shell .sidebar-brand { padding: 20px 12px 16px; justify-content: center; }
        .admin-shell.collapsed .nav-item,
        .adm-sidebar-collapsed .admin-shell .nav-item { justify-content: center; padding: 10px; }
        .admin-shell.collapsed .nav-item-icon,
        .adm-sidebar-collapsed .admin-shell .nav-item-icon { width: auto; font-size: 1.15rem; }
        .admin-shell.collapsed .sidebar-collapse-btn,
        .adm-sidebar-collapsed .admin-shell .sidebar-collapse-btn { justify-content: center; padding: 9px; }
        .admin-shell.collapsed .sidebar-collapse-btn .collapse-icon,
        .adm-sidebar-collapsed .admin-shell .sidebar-collapse-btn .collapse-icon { transform: rotate(180deg); }
        .admin-shell.collapsed .sidebar-bottom .nav-item,
        .adm-sidebar-collapsed .admin-shell .sidebar-bottom .nav-item { justify-content: center; padding: 9px; }

        /* ─── Content area ──────────────────────────────────────── */
        .admin-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        /* ─── Header ────────────────────────────────────────────── */
        .admin-header {
            background: var(--adm-card);
            border-bottom: 1px solid var(--adm-border);
            padding: 0 24px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            flex-shrink: 0;
        }
        .header-left { display: flex; align-items: center; gap: 16px; min-width: 0; }
        .header-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .88rem;
            min-width: 0;
        }
        .header-context { color: var(--adm-muted); font-weight: 500; }
        .header-sep { color: var(--adm-border-dark); }
        .header-page { color: var(--adm-text); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .header-crumb-sub { color: var(--adm-muted); font-size: .8rem; display: none; }

        /* Mobile toggle */
        .header-menu-toggle {
            display: none;
            width: 36px; height: 36px;
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius-sm);
            background: none;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            color: var(--adm-text-2);
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .header-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

        /* User pill */
        .header-user {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .header-user-meta { text-align: right; line-height: 1.2; }
        .header-user-meta strong { display: block; font-size: .85rem; color: var(--adm-text); font-weight: 600; }
        .header-user-meta span { display: block; font-size: .75rem; color: var(--adm-muted); }
        .header-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--adm-tertiary), #0f2f55);
            color: #fff;
            font-size: .8rem;
            font-weight: 700;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        /* ─── Page content ──────────────────────────────────────── */
        .admin-page {
            flex: 1;
            padding: 24px;
        }

        /* Page title row */
        .page-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--adm-text); margin: 0; }
        .page-subtitle { font-size: .85rem; color: var(--adm-muted); margin: 4px 0 0; }

        /* ─── Cards ─────────────────────────────────────────────── */
        .section-card {
            background: var(--adm-card);
            border: 1px solid var(--adm-border);
            border-radius: 10px;
            box-shadow: var(--adm-shadow-sm);
            padding: 20px;
            margin-bottom: 20px;
        }
        .section-card h3 { font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--adm-text); }
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .section-head p { margin: 4px 0 0; color: var(--adm-muted); font-size: .855rem; }

        /* Stat cards (dashboard) */
        .stat-card {
            background: var(--adm-card);
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius);
            box-shadow: var(--adm-shadow-sm);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-icon {
            width: 52px; height: 52px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 1.4rem;
            color: #fff;
            flex-shrink: 0;
        }
        .stat-icon.green  { background: linear-gradient(135deg,var(--adm-primary),var(--adm-secondary)); }
        .stat-icon.blue   { background: linear-gradient(135deg,var(--adm-tertiary),#4fa3f7); }
        .stat-icon.amber  { background: linear-gradient(135deg,var(--adm-secondary),var(--adm-primary)); }
        .stat-icon.purple { background: linear-gradient(135deg,var(--adm-tertiary),var(--adm-primary)); }
        .stat-icon.rose   { background: linear-gradient(135deg,var(--adm-danger-brand),#ff7b6f); }
        .stat-icon.teal   { background: linear-gradient(135deg,var(--adm-tertiary),var(--adm-secondary)); }
        .stat-body strong { display: block; font-size: 1.75rem; font-weight: 800; line-height: 1; color: var(--adm-text); }
        .stat-body span   { display: block; font-size: .875rem; font-weight: 600; color: var(--adm-text-2); margin-top: 4px; }
        .stat-body small  { display: block; font-size: .78rem; color: var(--adm-muted); margin-top: 2px; }

        /* ─── Tables ─────────────────────────────────────────────── */
        .table-responsive {
            border: 1px solid var(--adm-border);
            border-radius: 10px;
            background: var(--adm-card);
            box-shadow: var(--adm-shadow-sm);
        }
        .table-modern { margin: 0; width: 100%; border-collapse: collapse; }
        .table-modern thead th {
            background: #fbfcfe;
            color: var(--adm-muted);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 12px 18px;
            border-bottom: 1px solid var(--adm-border);
            white-space: nowrap;
        }
        .table-modern tbody tr { border-bottom: 1px solid #eef2f7; transition: background var(--adm-transition); }
        .table-modern tbody tr:hover { background: rgba(var(--adm-tertiary-rgb),.035); }
        .table-modern tbody td { padding: 14px 18px; vertical-align: middle; color: #4b5870; font-size: .86rem; }
        .table-modern tbody tr:last-child { border-bottom: 0; }

        /* ─── Badges ─────────────────────────────────────────────── */
        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-soft::before { content:''; width:7px; height:7px; border-radius:50%; flex-shrink:0; }
        .badge-soft.success { background: rgba(var(--adm-tertiary-rgb),.10); color: var(--adm-tertiary); border: 1px solid rgba(var(--adm-tertiary-rgb),.20); }
        .badge-soft.success::before { background: var(--adm-tertiary); }
        .badge-soft.warning { background: rgba(var(--adm-primary-rgb),.13); color: #8a5b00; border: 1px solid rgba(var(--adm-primary-rgb),.24); }
        .badge-soft.warning::before { background: var(--adm-primary); }
        .badge-soft.danger  { background: rgba(var(--adm-danger-brand-rgb),.11); color: var(--adm-danger-brand); border: 1px solid rgba(var(--adm-danger-brand-rgb),.22); }
        .badge-soft.danger::before  { background: var(--adm-danger-brand); }
        .badge-soft.info    { background: rgba(var(--adm-tertiary-rgb),.10); color: var(--adm-tertiary); border: 1px solid rgba(var(--adm-tertiary-rgb),.20); }
        .badge-soft.info::before    { background: var(--adm-tertiary); }
        .badge-soft.dark    { background: #f1f5f9; color: #475569; }
        .badge-soft.dark::before    { background: #94a3b8; }
        .badge-soft.muted   { background: #f8fafc; color: var(--adm-muted); }
        .badge-soft.muted::before   { display:none; }

        /* ─── Buttons ─────────────────────────────────────────────── */
        .btn-soft {
            background:
                linear-gradient(#fff, #fff) padding-box,
                var(--adm-brand-gradient) border-box;
            color: var(--adm-text-2);
            border: 1px solid transparent;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
        }
        .btn-soft:hover {
            background:
                var(--adm-brand-gradient-soft) padding-box,
                var(--adm-brand-gradient) border-box;
            color: var(--adm-text);
        }

        .btn-premium {
            position: relative;
            background: var(--adm-brand-gradient);
            color: #fff;
            border: none;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(var(--adm-primary-rgb),.24);
            overflow: hidden;
        }
        .btn-premium::after {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: inherit;
            background: linear-gradient(180deg, rgba(255,255,255,.18), rgba(255,255,255,0));
            pointer-events: none;
        }
        .btn-premium:hover { color: #fff; filter: brightness(1.04); box-shadow: 0 14px 28px rgba(var(--adm-primary-rgb),.30); transform: translateY(-1px); }

        .btn-admin-action {
            position: relative;
            background: var(--adm-brand-gradient);
            color: #fff;
            border: none;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(var(--adm-primary-rgb),.22);
            overflow: hidden;
        }
        .btn-admin-action::after {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: inherit;
            background: linear-gradient(180deg, rgba(255,255,255,.18), rgba(255,255,255,0));
            pointer-events: none;
        }
        .btn-admin-action:hover { color: #fff; filter: brightness(1.04); box-shadow: 0 14px 28px rgba(var(--adm-primary-rgb),.30); transform: translateY(-1px); }
        .btn-success,
        .btn-outline-success:hover {
            background-color: var(--adm-tertiary) !important;
            border-color: var(--adm-tertiary) !important;
            color: #fff !important;
        }
        .btn-outline-success {
            border-color: rgba(var(--adm-tertiary-rgb),.35) !important;
            color: var(--adm-tertiary) !important;
            background: #fff !important;
        }
        .btn-outline-secondary:hover {
            background-color: var(--adm-tertiary) !important;
            border-color: var(--adm-tertiary) !important;
            color: #fff !important;
        }

        .btn-icon {
            width: 34px; height: 34px;
            border-radius: var(--adm-radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--adm-border);
            background: var(--adm-card);
            color: var(--adm-muted);
            cursor: pointer;
            font-size: .95rem;
            transition: all var(--adm-transition);
            padding: 0;
        }
        .btn-icon:hover { background: var(--adm-bg); color: var(--adm-text); border-color: var(--adm-border-dark); }
        .btn-icon.preview:hover { color: var(--adm-primary); border-color: var(--adm-primary); }
        .btn-icon.edit:hover    { color: var(--adm-tertiary); border-color: var(--adm-tertiary); }
        .btn-icon.delete:hover  { color: var(--adm-danger-brand); border-color: var(--adm-danger-brand); }

        .table-actions { display: flex; gap: 6px; align-items: center; }

        /* ─── Forms ──────────────────────────────────────────────── */
        .form-label { font-size: .83rem; font-weight: 600; color: var(--adm-text-2); margin-bottom: 6px; display: block; }
        .form-control, .form-select {
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius-sm);
            padding: 9px 12px;
            font-size: .875rem;
            color: var(--adm-text);
            background: #fff;
            min-height: 40px;
            transition: border-color var(--adm-transition), box-shadow var(--adm-transition);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--adm-primary);
            box-shadow: 0 0 0 3px rgba(var(--adm-primary-rgb),.12);
            outline: none;
        }
        textarea.form-control { min-height: 110px; resize: vertical; }
        .form-check-input:checked { background-color: var(--adm-primary); border-color: var(--adm-primary); }
        .form-switch .form-check-input { width: 2.2em; }

        /* ─── Event list items ────────────────────────────────────── */
        .event-list { display: flex; flex-direction: column; gap: 10px; }
        .event-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius);
            background: var(--adm-card);
            transition: box-shadow var(--adm-transition);
        }
        .event-item:hover { box-shadow: var(--adm-shadow); }
        .event-date-badge {
            text-align: center;
            min-width: 52px;
            flex-shrink: 0;
        }
        .event-date-badge .month {
            display: block;
            font-size: .68rem;
            font-weight: 700;
            color: var(--adm-primary);
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .event-date-badge .day {
            display: block;
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1;
            color: var(--adm-text);
        }
        .event-body { flex: 1; min-width: 0; }
        .event-body strong { display: block; font-size: .9rem; font-weight: 600; color: var(--adm-text); }
        .event-body span { display: block; font-size: .8rem; color: var(--adm-muted); margin-top: 2px; }
        .event-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .btn-event-action {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 12px; border-radius: var(--adm-radius-sm);
            font-size: .78rem; font-weight: 600;
            border: 1px solid var(--adm-border);
            background: none; color: var(--adm-text-2);
            cursor: pointer; transition: all var(--adm-transition);
        }
        .btn-event-action:hover { background: var(--adm-bg); border-color: var(--adm-border-dark); }

        /* ─── Alerts ─────────────────────────────────────────────── */
        .alert {
            border-radius: var(--adm-radius);
            padding: 14px 18px;
            border: 1px solid transparent;
            margin-bottom: 16px;
            font-size: .875rem;
        }
        .alert-success { background: #dcfce7; border-color: #bbf7d0; color: #166534; }
        .alert-danger  { background: #fee2e2; border-color: #fecaca; color: #991b1b; }
        .alert-warning { background: #fef9c3; border-color: #fde68a; color: #854d0e; }
        .alert-info    { background: #dbeafe; border-color: #bfdbfe; color: #1e40af; }

        /* ─── DataTables overrides ───────────────────────────────── */
        div.dataTables_wrapper {
            font-size: .86rem;
        }
        div.dataTables_wrapper .row:first-child {
            align-items: center;
            padding: 14px 14px 12px;
            border: 1px solid var(--adm-border);
            border-bottom: 0;
            border-radius: 10px 10px 0 0;
            background: #fff;
            margin: 0;
        }
        div.dataTables_wrapper .row:first-child + .row {
            margin: 0;
        }
        div.dataTables_wrapper .dataTables_filter {
            text-align: right;
        }
        div.dataTables_wrapper .dataTables_filter label,
        div.dataTables_wrapper .dataTables_length label {
            color: var(--adm-muted);
            font-size: .82rem;
            font-weight: 600;
        }
        div.dataTables_wrapper .dataTables_filter input,
        div.dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--adm-border);
            border-radius: 8px;
            padding: 7px 11px;
            font-size: .83rem;
            color: var(--adm-text);
            background: #fff;
        }
        div.dataTables_wrapper .dataTables_filter input:focus,
        div.dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--adm-primary);
            box-shadow: 0 0 0 3px rgba(var(--adm-primary-rgb),.12);
            outline: none;
        }
        div.dataTables_wrapper .row:last-child {
            align-items: center;
            padding: 12px 14px;
            border: 1px solid var(--adm-border);
            border-top: 0;
            border-radius: 0 0 10px 10px;
            background: #fff;
            margin: 0;
        }
        div.dataTables_wrapper .dataTables_info,
        div.dataTables_wrapper .dataTables_length { font-size: .82rem; color: var(--adm-muted); }
        .page-item.active .page-link { background: var(--adm-primary); border-color: var(--adm-primary); }
        .page-link {
            color: var(--adm-tertiary);
            border-radius: 8px !important;
            min-width: 34px;
            text-align: center;
            border-color: var(--adm-border);
            margin: 0 2px;
            font-size: .82rem;
        }
        .page-link:hover {
            color: var(--adm-tertiary);
            background: rgba(var(--adm-tertiary-rgb),.07);
            border-color: rgba(var(--adm-tertiary-rgb),.22);
        }

        /* ─── Modals ─────────────────────────────────────────────── */
        .modal-content {
            border: 0;
            border-radius: var(--adm-radius-lg);
            box-shadow: var(--adm-shadow-lg);
            overflow: hidden;
        }
        .modal-header {
            background: var(--adm-card);
            border-bottom: 1px solid var(--adm-border);
            padding: 18px 22px;
        }
        .modal-title { font-size: 1rem; font-weight: 700; color: var(--adm-text); }
        .modal-body { padding: 22px; background: var(--adm-card); }
        .modal-footer {
            padding: 14px 22px;
            background: #f8fafc;
            border-top: 1px solid var(--adm-border);
            gap: 8px;
        }

        /* Confirm modal */
        .modal-confirm-icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 1.8rem;
            margin: 0 auto 16px;
        }
        .modal-confirm-icon.warning { background: #fef9c3; color: #d97706; }
        .modal-confirm-icon.danger  { background: #fee2e2; color: #dc2626; }
        .modal-confirm-icon.info    { background: #dbeafe; color: #1d4ed8; }
        .modal-confirm-title { text-align: center; font-size: 1.1rem; font-weight: 700; color: var(--adm-text); margin: 0 0 8px; }
        .modal-confirm-msg   { text-align: center; font-size: .875rem; color: var(--adm-muted); margin: 0; }

        /* Offcanvas */
        .offcanvas { border: 0; box-shadow: -8px 0 32px rgba(0,0,0,.12); }
        .offcanvas-header { padding: 18px 22px; }
        .offcanvas-body { padding: 22px; }

        /* ─── Responsive ─────────────────────────────────────────── */
        @media (max-width: 1199px) {
            .admin-sidebar {
                position: fixed;
                left: 0; top: 0;
                height: 100vh;
                transform: translateX(-100%);
                transition: transform var(--adm-transition), width var(--adm-transition);
            }
            .admin-sidebar.mobile-open { transform: translateX(0); }
            .admin-shell.collapsed .admin-sidebar,
            .adm-sidebar-collapsed .admin-shell .admin-sidebar { width: var(--adm-sidebar-w); }
            .admin-shell.collapsed .sidebar-brand-text,
            .admin-shell.collapsed .nav-group-label,
            .admin-shell.collapsed .nav-item-label,
            .admin-shell.collapsed .collapse-label,
            .adm-sidebar-collapsed .admin-shell .sidebar-brand-text,
            .adm-sidebar-collapsed .admin-shell .nav-group-label,
            .adm-sidebar-collapsed .admin-shell .nav-item-label,
            .adm-sidebar-collapsed .admin-shell .collapse-label { display: block; }
            .admin-shell.collapsed .sidebar-brand,
            .adm-sidebar-collapsed .admin-shell .sidebar-brand { padding: 20px 18px 16px; justify-content: flex-start; }
            .admin-shell.collapsed .nav-item,
            .adm-sidebar-collapsed .admin-shell .nav-item { justify-content: flex-start; padding: 10px 12px; }
            .admin-shell.collapsed .nav-item-icon,
            .adm-sidebar-collapsed .admin-shell .nav-item-icon { width: 20px; font-size: 1.05rem; }
            .admin-shell.collapsed .sidebar-collapse-btn,
            .adm-sidebar-collapsed .admin-shell .sidebar-collapse-btn { justify-content: flex-start; padding: 9px 12px; }
            .admin-shell.collapsed .sidebar-bottom .nav-item,
            .adm-sidebar-collapsed .admin-shell .sidebar-bottom .nav-item { justify-content: flex-start; padding: 9px 12px; }
            .header-menu-toggle { display: inline-flex; }
            .admin-page { padding: 16px; }
        }
        @media (max-width: 767px) {
            .admin-header { padding: 0 14px; }
            .header-user-meta { display: none; }
            .admin-page { padding: 12px; }
            .section-card { padding: 14px; }
            .stat-card { padding: 14px; }
        }
    </style>
    <?= $extraHead ?>
</head>
<body>
<div class="admin-shell" id="adminShell">

    <!-- ─── Sidebar ─────────────────────────────────────────────── -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon"><i class="bi bi-building"></i></div>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-name"><?= cms_e($institutionShortName) ?></span>
                <span class="sidebar-brand-sub">Panel institucional</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($navGroups as $groupLabel => $groupKeys): ?>
                <div class="nav-group">
                    <span class="nav-group-label"><?= $groupLabel ?></span>
                    <?php foreach ($groupKeys as $key): ?>
                        <?php $item = $navItems[$key] ?? null; if (!$item) continue; ?>
                        <a class="nav-item <?= $activePanel === $key ? 'active' : '' ?>"
                           href="<?= cms_e($item['href']) ?>"
                           title="<?= cms_e($item['label']) ?>">
                            <i class="bi <?= cms_e($item['icon']) ?> nav-item-icon"></i>
                            <span class="nav-item-label"><?= cms_e($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-bottom">
            <a class="nav-item" href="index.php" target="_blank" title="Ver sitio">
                <i class="bi bi-box-arrow-up-right nav-item-icon"></i>
                <span class="nav-item-label">Ver sitio</span>
            </a>
            <a class="nav-item" href="index.php?logout=1" title="Cerrar sesión">
                <i class="bi bi-box-arrow-left nav-item-icon"></i>
                <span class="nav-item-label">Cerrar sesión</span>
            </a>
            <button class="sidebar-collapse-btn" id="toggleSidebar" type="button">
                <i class="bi bi-chevron-left collapse-icon"></i>
                <span class="collapse-label">Colapsar</span>
            </button>
        </div>
    </aside>

    <!-- overlay mobile -->
    <div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1039;" onclick="document.getElementById('adminSidebar').classList.remove('mobile-open');this.style.display='none';"></div>

    <!-- ─── Main ────────────────────────────────────────────────── -->
    <div class="admin-content">
        <header class="admin-header">
            <div class="header-left">
                <button class="header-menu-toggle" id="mobileMenuToggle" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <div class="header-breadcrumb">
                    <span class="header-context">CMS</span>
                    <span class="header-sep">›</span>
                    <span class="header-page"><?= cms_e($pageTitle) ?></span>
                </div>
            </div>
            <div class="header-right">
                <?= $headerActions ?>
                <div class="header-user">
                    <div class="header-user-meta">
                        <strong><?= cms_e($adminName) ?></strong>
                        <span><?= cms_e($_SESSION['admin_rol'] ?? 'Administrador') ?></span>
                    </div>
                    <div class="header-avatar"><?= cms_e($initials) ?></div>
                </div>
            </div>
        </header>

        <div class="admin-page">
    <?php
}

function admin_render_layout_end(array $options = []): void
{
    $extraScripts = $options['extra_scripts'] ?? '';
    ?>
        </div><!-- /.admin-page -->
    </div><!-- /.admin-content -->
</div><!-- /.admin-shell -->

<!-- ─── Modal: Vista previa ───────────────────────────────────── -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="bi bi-eye me-2 text-muted"></i>Vista previa del contenedor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0" style="background:#f8fafc;">
                <iframe id="previewFrame" title="Vista previa" style="width:100%;min-height:72vh;border:0;display:block;" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- ─── Modal: Notificación auto-cierre ───────────────────────── -->
<div class="modal fade" id="notifyModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
        <div class="modal-content" style="border-radius:18px;overflow:hidden;">
            <div class="modal-body" style="padding:32px 28px 22px;text-align:center;">
                <div class="modal-confirm-icon info" id="notifyModalIcon" style="margin-bottom:12px;">
                    <i class="bi bi-check-circle-fill" id="notifyModalIconEl"></i>
                </div>
                <h5 class="modal-confirm-title" id="notifyModalTitle"></h5>
                <p class="modal-confirm-msg" id="notifyModalMsg" style="margin-bottom:16px;"></p>
                <div id="notifyProgress" style="height:3px;border-radius:2px;background:var(--adm-brand-gradient);transform-origin:left;"></div>
            </div>
        </div>
    </div>
</div>

<!-- ─── Modal: Confirmación ───────────────────────────────────── -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-body" style="padding:32px 24px 24px;">
                <div class="modal-confirm-icon warning" id="confirmModalIcon">
                    <i class="bi bi-exclamation-triangle" id="confirmModalIconEl"></i>
                </div>
                <h5 class="modal-confirm-title" id="confirmModalTitle">¿Confirmar acción?</h5>
                <p class="modal-confirm-msg" id="confirmModalMsg">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-premium" id="confirmModalBtn">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- ─── Modal: Formulario rápido ──────────────────────────────── -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel">Editar registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="formModalBody">
                <!-- Contenido dinámico por JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-premium" id="formModalSubmit">
                    <i class="bi bi-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ─── Modal: Detalle / Ver ──────────────────────────────────── -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="bi bi-info-circle me-2 text-muted"></i>Detalle del registro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- Contenido dinámico por JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
(function () {
    var shell   = document.getElementById('adminShell');
    var sidebar = document.getElementById('adminSidebar');
    var toggle  = document.getElementById('toggleSidebar');
    var mobileToggle = document.getElementById('mobileMenuToggle');
    var overlay = document.getElementById('sidebarOverlay');

    /* Restore collapsed state */
    if (shell && localStorage.getItem('adminSidebarCollapsed') === '1') {
        shell.classList.add('collapsed');
        document.documentElement.classList.add('adm-sidebar-collapsed');
    }

    /* Desktop collapse */
    if (toggle) {
        toggle.addEventListener('click', function () {
            if (!shell) return;
            shell.classList.toggle('collapsed');
            var isCollapsed = shell.classList.contains('collapsed');
            document.documentElement.classList.toggle('adm-sidebar-collapsed', isCollapsed);
            localStorage.setItem('adminSidebarCollapsed', isCollapsed ? '1' : '0');
        });
    }

    /* Mobile open */
    if (mobileToggle && sidebar && overlay) {
        mobileToggle.addEventListener('click', function () {
            sidebar.classList.toggle('mobile-open');
            overlay.style.display = sidebar.classList.contains('mobile-open') ? 'block' : 'none';
        });
    }

    /* notifyModal: auto-closing notification (no buttons) */
    window.adminNotify = function (opts) {
        var icon   = document.getElementById('notifyModalIcon');
        var iconEl = document.getElementById('notifyModalIconEl');
        var titleEl= document.getElementById('notifyModalTitle');
        var msgEl  = document.getElementById('notifyModalMsg');
        var bar    = document.getElementById('notifyProgress');

        var type = opts.type || 'info';
        var icons = { success: 'bi-check-circle-fill', info: 'bi-info-circle-fill', danger: 'bi-x-circle-fill' };
        icon.className   = 'modal-confirm-icon ' + type;
        iconEl.className = 'bi ' + (icons[type] || icons.info);
        titleEl.textContent = opts.title || '';
        msgEl.textContent   = opts.msg   || '';

        var delay = opts.autoClose !== undefined ? opts.autoClose : 3000;
        if (bar) {
            bar.style.transition = 'none';
            bar.style.transform  = 'scaleX(1)';
            void bar.offsetWidth;
            bar.style.transition = 'transform ' + (delay / 1000) + 's linear';
            bar.style.transform  = 'scaleX(0)';
        }

        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('notifyModal'));
        modal.show();
        if (delay > 0) { setTimeout(function () { modal.hide(); }, delay); }
    };

    /* confirmModal helper (global) */
    window.adminConfirm = function (opts) {
        /* opts: { title, msg, type('warning'|'danger'|'info'), btnText, onConfirm } */
        var icon   = document.getElementById('confirmModalIcon');
        var iconEl = document.getElementById('confirmModalIconEl');
        var titleEl= document.getElementById('confirmModalTitle');
        var msgEl  = document.getElementById('confirmModalMsg');
        var btnEl  = document.getElementById('confirmModalBtn');

        icon.className   = 'modal-confirm-icon ' + (opts.type || 'warning');
        var icons = { warning:'bi-exclamation-triangle', danger:'bi-trash3', info:'bi-info-circle' };
        iconEl.className = 'bi ' + (icons[opts.type] || icons.warning);
        titleEl.textContent = opts.title || '¿Confirmar acción?';
        msgEl.textContent   = opts.msg   || '';

        /* danger btn */
        btnEl.className = opts.type === 'danger' ? 'btn btn-danger' : 'btn btn-premium';
        btnEl.textContent = opts.btnText || 'Confirmar';

        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmModal'));
        var handler = function () {
            modal.hide();
            btnEl.removeEventListener('click', handler);
            if (typeof opts.onConfirm === 'function') opts.onConfirm();
        };
        btnEl.removeEventListener('click', window._lastConfirmHandler || function(){});
        window._lastConfirmHandler = handler;
        btnEl.addEventListener('click', handler);
        modal.show();
    };
})();
</script>
<?= $extraScripts ?>
</body>
</html>
    <?php
}
