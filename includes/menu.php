<?php
/**
 * Utilidades para cargar y renderizar menus desde la base de datos.
 */
require_once __DIR__ . '/../class/conexion.php';

$GLOBALS['menu_sistema_error'] = null;
$GLOBALS['menu_sistema_origen'] = 'fallback';

if (!function_exists('obtenerMenusSistema')) {
    function obtenerMenusSistema(): array
    {
        try {
            $conexion = (new Conexion())->getConexion();
            $sql = "SELECT id_menu, nombre, url, icono, id_padre, orden, activo, visible
                    FROM menus
                    ORDER BY COALESCE(id_padre, 0) ASC, orden ASC, id_menu ASC";

            $resultado = $conexion->query($sql);

            if (!$resultado) {
                $GLOBALS['menu_sistema_error'] = 'La consulta a la tabla menus no devolvio resultados validos.';
                error_log('[menu_sistema] Error al consultar menus: ' . $conexion->error);
                return [];
            }

            $menus = [];

            while ($fila = $resultado->fetch_assoc()) {
                if (!menuEstaDisponible($fila['activo'] ?? null, $fila['visible'] ?? null)) {
                    continue;
                }

                $fila['id_menu'] = (int) $fila['id_menu'];
                $fila['id_padre'] = $fila['id_padre'] !== null ? (int) $fila['id_padre'] : 0;
                $fila['orden'] = (int) $fila['orden'];
                $menus[] = $fila;
            }

            $arbol = construirArbolMenu($menus);

            if (empty($arbol) && !empty($menus)) {
                $GLOBALS['menu_sistema_error'] = 'Se cargaron filas desde menus, pero no fue posible formar el arbol principal. Revisa id_padre.';
                error_log('[menu_sistema] Menus cargados sin nodos raiz. Revisa id_padre en la tabla menus.');
                return [];
            }

            if (empty($menus)) {
                $GLOBALS['menu_sistema_error'] = 'La tabla menus no tiene registros activos y visibles segun las reglas actuales.';
                return [];
            }

            $GLOBALS['menu_sistema_origen'] = 'base_de_datos';

            return $arbol;
        } catch (Throwable $exception) {
            $GLOBALS['menu_sistema_error'] = $exception->getMessage();
            error_log('[menu_sistema] ' . $exception->getMessage());
            return [];
        }
    }
}

if (!function_exists('menuEstaDisponible')) {
    function menuEstaDisponible(mixed $activo, mixed $visible): bool
    {
        return valorMenuActivo($activo) && valorMenuActivo($visible);
    }
}

if (!function_exists('valorMenuActivo')) {
    function valorMenuActivo(mixed $valor): bool
    {
        if ($valor === null || $valor === '') {
            return false;
        }

        $normalizado = strtolower(trim((string) $valor));

        return in_array($normalizado, ['1', 'true', 'si', 'sí', 'yes', 'on'], true);
    }
}

if (!function_exists('construirArbolMenu')) {
    function construirArbolMenu(array $menus, int $idPadre = 0): array
    {
        $rama = [];

        foreach ($menus as $menu) {
            if ((int) $menu['id_padre'] !== $idPadre) {
                continue;
            }

            $hijos = construirArbolMenu($menus, (int) $menu['id_menu']);

            if (!empty($hijos)) {
                $menu['hijos'] = $hijos;
            }

            $rama[] = $menu;
        }

        return $rama;
    }
}

if (!function_exists('renderMenuSistema')) {
    function renderMenuSistema(array $menus, int $nivel = 0): void
    {
        if (empty($menus)) {
            return;
        }

        $claseSubmenu = $nivel === 0 ? 'sub-menu' : 'sub-sub-menu';

        echo $nivel === 0 ? '<ul>' : '<ul class="' . $claseSubmenu . '">';

        foreach ($menus as $menu) {
            $nombre = htmlspecialchars((string) $menu['nombre'], ENT_QUOTES, 'UTF-8');
            $url = trim((string) ($menu['url'] ?? '')) !== '' ? (string) $menu['url'] : '#0';
            $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $icono = trim((string) ($menu['icono'] ?? ''));
            $hijos = $menu['hijos'] ?? [];

            echo '<li>';
            echo '<a href="' . $url . '">';
            if ($icono !== '') {
                echo '<i class="' . htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') . '"></i> ';
            }
            echo $nombre;

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

if (!function_exists('obtenerMenuRespaldo')) {
    function obtenerMenuRespaldo(): array
    {
        return [
            [
                'nombre' => 'Inicio',
                'url' => 'index.php',
                'icono' => '',
                'hijos' => [],
            ],
            [
                'nombre' => 'Nosotros',
                'url' => 'about.html',
                'icono' => '',
                'hijos' => [],
            ],
            [
                'nombre' => 'Cursos',
                'url' => 'course.html',
                'icono' => '',
                'hijos' => [],
            ],
            [
                'nombre' => 'Blog',
                'url' => 'blog.html',
                'icono' => '',
                'hijos' => [],
            ],
            [
                'nombre' => 'Contacto',
                'url' => 'contact.html',
                'icono' => '',
                'hijos' => [],
            ],
        ];
    }
}

if (!function_exists('obtenerEstadoMenuSistema')) {
    function obtenerEstadoMenuSistema(): array
    {
        return [
            'origen' => $GLOBALS['menu_sistema_origen'] ?? 'fallback',
            'error' => $GLOBALS['menu_sistema_error'] ?? null,
        ];
    }
}
