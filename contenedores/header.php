<?php
/**
 * Header principal y navegacion.
 */
require_once __DIR__ . '/../includes/menu_sistema.php';

$menusSistema = [];
$estadoMenuSistema = [
    'origen' => 'fallback',
    'error' => null,
];

if (function_exists('obtenerMenusSistema')) {
    $menusSistema = obtenerMenusSistema();
}

if (function_exists('obtenerEstadoMenuSistema')) {
    $estadoMenuSistema = obtenerEstadoMenuSistema();
}

if (empty($menusSistema) && function_exists('obtenerMenuRespaldo')) {
    $menusSistema = obtenerMenuRespaldo();
}

if (empty($menusSistema)) {
    $menusSistema = [
        ['nombre' => 'Inicio', 'url' => 'index.php', 'icono' => '', 'hijos' => []],
        ['nombre' => 'Nosotros', 'url' => 'about.html', 'icono' => '', 'hijos' => []],
        ['nombre' => 'Cursos', 'url' => 'course.html', 'icono' => '', 'hijos' => []],
        ['nombre' => 'Blog', 'url' => 'blog.html', 'icono' => '', 'hijos' => []],
        ['nombre' => 'Contacto', 'url' => 'contact.html', 'icono' => '', 'hijos' => []],
    ];
}

if (!function_exists('renderMenuSistema')) {
    function renderMenuSistema(array $menus, int $nivel = 0): void
    {
        if (empty($menus)) {
            return;
        }

        $claseSubmenu = $nivel === 0 ? '' : ($nivel === 1 ? ' class="sub-menu"' : ' class="sub-sub-menu"');

        echo '<ul' . $claseSubmenu . '>';

        foreach ($menus as $menu) {
            $nombre = htmlspecialchars((string) ($menu['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars((string) (($menu['url'] ?? '') !== '' ? $menu['url'] : '#0'), ENT_QUOTES, 'UTF-8');
            $hijos = $menu['hijos'] ?? [];

            echo '<li>';
            echo '<a href="' . $url . '">' . $nombre;

            if (!empty($hijos)) {
                echo ' <i class="fa-solid fa-angle-down"></i>';
            }

            echo '</a>';

            if (!empty($hijos)) {
                renderMenuSistema($hijos, $nivel + 1);
            }

            echo '</li>';
        }

        echo '</ul>';
    }
}
?>
<!-- menu_sistema_origen: <?= htmlspecialchars((string) $estadoMenuSistema['origen'], ENT_QUOTES, 'UTF-8'); ?> -->
<?php if (!empty($estadoMenuSistema['error'])): ?>
<!-- menu_sistema_error: <?= htmlspecialchars((string) $estadoMenuSistema['error'], ENT_QUOTES, 'UTF-8'); ?> -->
<?php endif; ?>
<header class="header-area header-two-area">
    <div class="container">
        <div class="header__main header-two__main">
            <a href="index.php" class="logo">
                <img src="assets/images/logo/logo.svg" alt="logo">
            </a>
            <div class="main-menu">
                <nav>
                    <?php renderMenuSistema($menusSistema); ?>
                </nav>
            </div>
            <div class="header-two__info">
                <button class="search-trigger d-none d-lg-block mr-30" aria-label="Abrir búsqueda">
                    <i class="fa-regular fa-magnifying-glass"></i>
                </button>
                <div class="menu-btns d-none d-lg-flex">
                    <a class="active" href="pricing.html">Probar gratis</a>
                </div>
                <button class="menubars" type="button" data-bs-toggle="offcanvas" data-bs-target="#menubar" aria-label="Abrir menú">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</header>
