<?php

/** Convierte solo enlaces de navegacion conocidos; no altera endpoints o URLs externas. */
function cms_public_url(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '' || $url === '#' || str_starts_with($url, '#')) {
        return $url;
    }
    if (preg_match('~^(?:[a-z][a-z0-9+.-]*:|//)~i', $url)) {
        return $url;
    }

    $parts = parse_url($url);
    if ($parts === false) {
        return $url;
    }
    $path = preg_replace('~^(?:\./|/)+~', '', (string) ($parts['path'] ?? ''));
    parse_str((string) ($parts['query'] ?? ''), $query);
    $routes = ['index.php' => '/', 'noticias.php' => '/noticias', 'todas_noticias.php' => '/noticias', 'comunicados.php' => '/comunicados', 'biblioteca.php' => '/biblioteca'];
    $target = $routes[$path] ?? null;
    if ($path === 'admin.php' && !$query) {
        $target = '/admin';
    }
    $details = [
        'noticia_detalle.php' => ['id' => '/noticia/'],
        'evento_detalle.php' => ['id_evento' => '/evento/', 'id_item' => '/evento/item/'],
        'pagina_submenu.php' => ['id' => '/pagina/', 'submenu' => '/pagina/'],
        'feriado_detalle.php' => ['id_calendario' => '/feriado/'],
    ];
    foreach ($details[$path] ?? [] as $key => $prefix) {
        if (isset($query[$key]) && is_scalar($query[$key]) && ctype_digit((string) $query[$key]) && (int) $query[$key] > 0) {
            $target = $prefix . (int) $query[$key];
            unset($query[$key]);
            break;
        }
    }
    if ($path === 'feriado_detalle.php' && $target === null && isset($query['fecha']) && is_string($query['fecha']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $query['fecha'])) {
        $target = '/feriado/fecha/' . $query['fecha'];
        unset($query['fecha']);
    }
    if ($target === null) {
        return $url;
    }
    return $target . ($query ? '?' . http_build_query($query) : '')
        . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
}

function cms_public_slug(string $name): string
{
    $name = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $name = strtr($name, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
    ]);
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');
    return $slug;
}

function cms_menu_page_anchor(array $submenu): string
{
    return (cms_public_slug((string) ($submenu['nombre'] ?? '')) ?: 'seccion') . '-' . (int) ($submenu['id_sub_menu'] ?? 0);
}

/** Only the submenu's own individual page is eligible for an in-page section. */
function cms_menu_submenu_is_internal(array $submenu): bool
{
    $id = (int) ($submenu['id_sub_menu'] ?? 0);
    if ($id <= 0 || (isset($submenu['estado']) && (int) $submenu['estado'] !== 1)) { return false; }
    $url = trim((string) ($submenu['url'] ?? ''));
    return $url === '' || $url === '#' || rtrim(cms_public_url($url), '/') === '/pagina/' . $id;
}

/** Null means an intentional external/file/special destination, left untouched. */
function cms_menu_page_route(array $menu): ?string
{
    $id = (int) ($menu['id_menu'] ?? 0);
    if ($id <= 0) { return null; }
    $url = trim((string) ($menu['url'] ?? ''));
    if ($url === '' || $url === '#') {
        $path = cms_public_slug((string) ($menu['nombre'] ?? ''));
    } elseif (ltrim($url, '/') === 'biblioteca.php') {
        // Historical entry point, not a menu identity or a second renderer.
        $path = 'biblioteca';
    } elseif (rtrim($url, '/') === '/menu/' . $id) {
        return '/menu/' . $id;
    } elseif (preg_match('~^/?([a-z0-9]+(?:-[a-z0-9]+)*)/?$~D', $url, $match)) {
        $path = $match[1];
    } else {
        return null;
    }
    $reserved = ['admin', 'noticias', 'comunicados', 'calendario', 'noticia', 'evento', 'pagina', 'feriado', 'menu'];
    $occupied = $path === '' || in_array($path, $reserved, true) || file_exists(__DIR__ . '/../' . $path);
    if ($occupied && $url !== '' && $url !== '#') { return null; }
    return $occupied ? '/menu/' . $id : '/' . $path;
}

function cms_menu_page_url(array $menu, array $submenus, array $menus = []): ?string
{
    if (isset($menu['estado']) && (int) $menu['estado'] !== 1) { return null; }
    $id = (int) ($menu['id_menu'] ?? 0);
    $hasInternal = false;
    foreach ($submenus as $sub) {
        if ((int) ($sub['id_menu'] ?? 0) === $id && cms_menu_submenu_is_internal($sub)) { $hasInternal = true; break; }
    }
    $route = $hasInternal ? cms_menu_page_route($menu) : null;
    if ($route === null) { return null; }
    foreach ($menus as $other) {
        if ((int) ($other['id_menu'] ?? 0) !== $id && cms_menu_page_route($other) === $route) { return '/menu/' . $id; }
    }
    return $route;
}

function cms_public_menu_url(array $menu, array $submenus = [], array $menus = []): string
{
    return cms_menu_page_url($menu, $submenus, $menus) ?? cms_public_url($menu['url'] ?: '#');
}

function cms_public_header_submenu_url(array $submenu, array $menu, ?string $menuUrl = null): string
{
    if ($menuUrl !== null && cms_menu_submenu_is_internal($submenu)
        && (int) ($submenu['id_menu'] ?? 0) === (int) ($menu['id_menu'] ?? 0)) {
        return $menuUrl . '#' . cms_menu_page_anchor($submenu);
    }
    return cms_submenu_public_url($submenu);
}
