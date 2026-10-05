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

/** Biblioteca is the existing main menu 10, independently of its editable label. */
function cms_is_biblioteca_menu(array $menu): bool
{
    return (int) ($menu['id_menu'] ?? 0) === 10;
}

function cms_biblioteca_anchor(array $submenu): string
{
    $name = strtr((string) ($submenu['nombre'] ?? ''), [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
    ]);
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');
    return ($slug !== '' ? $slug : 'seccion') . '-' . (int) ($submenu['id_sub_menu'] ?? 0);
}

function cms_public_menu_url(array $menu): string
{
    return cms_is_biblioteca_menu($menu) ? '/biblioteca' : cms_public_url($menu['url'] ?: '#');
}

/** Only Biblioteca's header links change; generic submenu URLs remain compatible. */
function cms_public_header_submenu_url(array $submenu, array $menu): string
{
    if (cms_is_biblioteca_menu($menu) && (int) ($submenu['id_menu'] ?? 0) === (int) $menu['id_menu']) {
        return '/biblioteca#' . cms_biblioteca_anchor($submenu);
    }
    return cms_public_url($submenu['url'] ?: '#');
}
