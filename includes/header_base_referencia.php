<?php

require_once __DIR__ . '/../clases/Session.php';
Session::iniciar();

$usuario_nombre   = Session::get('usuario_nombre', 'Usuario');
$usuario_apellido = Session::get('usuario_apellido', '');
$usuario_rol      = Session::get('usuario_rol', 'usuario');
$pagina_actual    = basename($_SERVER['PHP_SELF'], '.php');

$menu = [
    ['ruta' => 'dashboard.php',         'icono' => 'fa-gauge',         'label' => 'Dashboard',           'id' => 'dashboard',      'grupo' => 'Principal'],
    ['ruta' => 'pages/modulos.php',      'icono' => 'fa-cubes',         'label' => 'Módulos',             'id' => 'modulos',        'grupo' => 'Administración'],
    ['ruta' => 'pages/usuarios.php',     'icono' => 'fa-users',         'label' => 'Usuarios',            'id' => 'usuarios',       'grupo' => 'Administración'],
    ['ruta' => 'pages/colegios.php',     'icono' => 'fa-school',        'label' => 'Colegios',            'id' => 'colegios',       'grupo' => 'Operación'],
    ['ruta' => 'pages/eventos.php',      'icono' => 'fa-calendar',      'label' => 'Eventos',             'id' => 'eventos',        'grupo' => 'Operación'],
    ['ruta' => 'pages/permisos.php',     'icono' => 'fa-shield-halved', 'label' => 'Permisos',            'id' => 'permisos',       'grupo' => 'Seguridad'],
    ['ruta' => 'pages/beneficios.php',   'icono' => 'fa-gift',          'label' => 'Beneficios',          'id' => 'beneficios',     'grupo' => 'Operación'],
    ['ruta' => 'pages/contactos.php',    'icono' => 'fa-address-book',  'label' => 'Contactos',           'id' => 'contactos',      'grupo' => 'Operación'],
    ['ruta' => 'pages/contenedores.php', 'icono' => 'fa-box',           'label' => 'Contenedores',        'id' => 'contenedores',   'grupo' => 'Utilidades'],
    ['ruta' => 'pages/informa.php',      'icono' => 'fa-newspaper',     'label' => 'Informa',             'id' => 'informa',        'grupo' => 'Comunicaciones'],
    ['ruta' => 'pages/capsulas.php',     'icono' => 'fa-capsules',      'label' => 'Cápsulas',            'id' => 'capsulas',       'grupo' => 'Comunicaciones'],
    ['ruta' => 'pages/componentes.php',  'icono' => 'fa-code',          'label' => 'Componentes UI',      'id' => 'componentes',    'grupo' => 'Utilidades'],
    ['ruta' => 'pages/estados.php',      'icono' => 'fa-circle-info',   'label' => 'Estados del sistema', 'id' => 'estados',        'grupo' => 'Utilidades'],
    ['ruta' => 'pages/perfil.php',       'icono' => 'fa-user-circle',   'label' => 'Perfil',              'id' => 'perfil',         'grupo' => 'Cuenta'],
    ['ruta' => 'pages/configuracion.php','icono' => 'fa-gear',          'label' => 'Configuración',       'id' => 'configuracion',  'grupo' => 'Cuenta'],
];

$depth = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../' : '';
$menu_actual = null;
foreach ($menu as $item) {
    if ($item['id'] === $pagina_actual) {
        $menu_actual = $item;
        break;
    }
}
$breadcrumb_grupo = $menu_actual['grupo'] ?? 'Sección';
$breadcrumb_label = $pagina_titulo ?? ($menu_actual['label'] ?? 'Inicio');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pagina_titulo ?? 'Sistema') ?></title>
    <link rel="stylesheet" href="<?= $depth ?>css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="layout">
    <div class="mobile-backdrop" id="mobile-backdrop" onclick="closeMobileSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo"><i class="fa-solid fa-layer-group"></i> <span>Sistema</span></span>
            <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <?php $grupo_actual = ''; foreach ($menu as $item): ?>
                <?php if (($item['grupo'] ?? '') !== $grupo_actual): $grupo_actual = $item['grupo'] ?? ''; ?>
                <li class="sidebar-section"><?= htmlspecialchars($grupo_actual) ?></li>
                <?php endif; ?>
                <li>
                    <a href="<?= $depth . $item['ruta'] ?>"
                       class="<?= $pagina_actual === $item['id'] ? 'active' : '' ?>">
                        <i class="fa-solid <?= $item['icono'] ?>"></i>
                        <span><?= $item['label'] ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= $depth ?>includes/cerrar_sesion.php" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </aside>

    <!-- Contenido principal -->
    <div class="main-wrap">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-toggle" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <span class="mobile-brand"><i class="fa-solid fa-layer-group"></i> Sistema</span>
                <span class="breadcrumb">
                    SEDUC <i class="fa-solid fa-chevron-right"></i>
                    <?= htmlspecialchars($breadcrumb_grupo) ?> <i class="fa-solid fa-chevron-right"></i>
                    <strong><?= htmlspecialchars($breadcrumb_label) ?></strong>
                </span>
            </div>
            <div class="topbar-right">
                <button class="topbar-icon" onclick="toggleTheme()" title="Modo claro/oscuro">
                    <i class="fa-solid fa-moon" id="theme-icon"></i>
                </button>
                <div class="topbar-dd">
                    <button class="topbar-icon notification-btn" onclick="toggleDD(this)" title="Notificaciones">
                        <i class="fa-solid fa-bell"></i>
                        <span class="notification-dot" id="notification-dot"></span>
                    </button>
                    <div class="dropdown-menu notification-menu">
                        <div class="notification-head">
                            <strong>Notificaciones</strong>
                            <button type="button" onclick="markAllRead(event)">Marcar todas como leídas</button>
                        </div>
                        <div id="notification-list"></div>
                    </div>
                </div>
                <div class="topbar-dd">
                    <button class="topbar-user" onclick="toggleDD(this)">
                        <i class="fa-solid fa-user-circle"></i>
                        <span><?= htmlspecialchars($usuario_nombre . ' ' . $usuario_apellido) ?></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?= $depth ?>pages/perfil.php"><i class="fa-solid fa-user"></i> Perfil</a>
                        <a href="<?= $depth ?>includes/cerrar_sesion.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Zona de contenido -->
        <main class="content">
