<?php

/** Reuse the CMS embed formats; reject hosts outside the existing providers. */
function cms_menu_video_embed(?string $url): string
{
    $url = trim((string) $url);
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    if (!in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)
        || !in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be', 'vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
        return '';
    }
    return sp_submenu_video_embed($url);
}

/** One menu header, independent of submenu principal media and galleries. */
function cms_menu_header_media(array $menu): array
{
    $fallback = cms_public_image('assets/images/frontis_01.jpg', '');
    if (($menu['hero_tipo'] ?? 'imagen') === 'video') {
        $file = trim((string) ($menu['hero_video_archivo'] ?? ''));
        $src = cms_es_ruta_local_upload(ltrim($file, '/')) ? cms_public_image($file, '') : '';
        if ($src !== '') { return ['type' => 'video', 'src' => $src, 'fallback' => $fallback]; }
        $embed = cms_menu_video_embed($menu['hero_video_url'] ?? '');
        if ($embed !== '') { return ['type' => 'embed', 'src' => $embed, 'fallback' => $fallback]; }
    } else {
        $image = cms_public_image($menu['imagen_hero'] ?? '', '');
        if ($image !== '') { return ['type' => 'imagen', 'src' => $image, 'fallback' => $fallback]; }
    }
    return ['type' => 'imagen', 'src' => $fallback, 'fallback' => $fallback];
}

/** Returns old paths for cleanup AFTER the enclosing menu transaction commits. */
function cms_save_menu_header(mysqli $db, int $idMenu, array $post, array &$newPaths): array
{
    $type = (string) ($post['menu_hero_tipo'] ?? '');
    if (!in_array($type, ['imagen', 'video'], true)) { throw new RuntimeException('Selecciona imagen o video para la cabecera.'); }
    $stmt = $db->prepare('SELECT hero_tipo, imagen_hero, hero_video_url, hero_video_archivo FROM menus WHERE id_menu = ? FOR UPDATE');
    $stmt->bind_param('i', $idMenu);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$current) { throw new RuntimeException('El menú ya no existe.'); }
    $image = null; $file = null; $url = '';
    $oldPaths = array_filter([$current['imagen_hero'], $current['hero_video_archivo']]);
    if ($type === 'imagen' && empty($post['delete_menu_imagen_hero'])) {
        $image = cms_upload_image('menu_imagen_hero', 'menus/' . $idMenu, $current['imagen_hero']);
        if ($image && $image !== $current['imagen_hero']) { $newPaths[] = $image; }
    } elseif ($type === 'video' && empty($post['delete_menu_hero_video'])) {
        $url = trim((string) ($post['menu_hero_video_url'] ?? $current['hero_video_url'] ?? ''));
        if ($url !== '' && cms_menu_video_embed($url) === '') {
            throw new RuntimeException('Ingresa una URL compatible de YouTube o Vimeo.');
        }
        $file = cms_upload_file('menu_hero_video_archivo', 'menus/' . $idMenu, ['mp4', 'webm', 'mov', 'm4v'], $current['hero_video_archivo']);
        if ($file && $file !== $current['hero_video_archivo']) { $newPaths[] = $file; $url = ''; }
        elseif ($url !== (string) ($current['hero_video_url'] ?? '')) { $file = null; }
        elseif ($file) { $url = ''; }
    }
    $stmt = $db->prepare('UPDATE menus SET hero_tipo = ?, imagen_hero = ?, hero_video_url = ?, hero_video_archivo = ? WHERE id_menu = ?');
    $stmt->bind_param('ssssi', $type, $image, $url, $file, $idMenu);
    if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('No se pudo guardar la cabecera del menú.'); }
    $stmt->close();
    return array_values(array_diff($oldPaths, array_filter([$image, $file])));
}

/** Conservatively protect files also used by other CMS records, including JSON. */
function cms_menu_header_file_is_referenced(mysqli $db, string $path): bool
{
    if (cms_submenu_file_is_referenced($db, $path)) { return true; }
    foreach ([
        ['seccion_item', ['imagen', 'imagen_mobile']],
        ['evento_media', ['archivo']], ['eventos', ['imagen', 'archivo_adjunto']],
        ['comunicado', ['archivo']],
        ['institucion', ['logo_header', 'logo_footer', 'logo_blanco', 'logo_mobile', 'favicon', 'imagen_portada', 'imagen_login', 'og_image']],
    ] as [$table, $columns]) {
        if (!cms_table_exists($db, $table)) { continue; }
        $where = implode(' OR ', array_map(static fn(string $column): string => "REPLACE(TRIM(LEADING '/' FROM `$column`), CHAR(92), '/') = ?", $columns));
        $stmt = $db->prepare('SELECT 1 FROM `' . $table . '` WHERE ' . $where . ' LIMIT 1');
        if (!$stmt) { return true; }
        $values = array_fill(0, count($columns), ltrim(str_replace('\\', '/', $path), '/'));
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        if (!$stmt->execute()) { $stmt->close(); return true; }
        $found = (bool) $stmt->get_result()->fetch_row();
        $stmt->close();
        if ($found) { return true; }
    }
    if (cms_table_exists($db, 'seccion_config')) {
        $stmt = $db->prepare("SELECT 1 FROM seccion_config WHERE valor LIKE ? ESCAPE '=' OR valor LIKE ? ESCAPE '=' LIMIT 1");
        if (!$stmt) { return true; }
        $plain = '%' . strtr($path, ['=' => '==', '%' => '=%', '_' => '=_']) . '%';
        $escaped = '%' . strtr(str_replace('/', '\\/', $path), ['=' => '==', '%' => '=%', '_' => '=_']) . '%';
        $stmt->bind_param('ss', $plain, $escaped);
        if (!$stmt->execute()) { $stmt->close(); return true; }
        $found = (bool) $stmt->get_result()->fetch_row();
        $stmt->close();
        if ($found) { return true; }
    }
    return false;
}
