<?php

/** Resolve legacy website links locally; independent services retain their hosts. */
function cms_menu_page_current_url(?string $url): string
{
    $url = trim((string) $url);
    $scheme = preg_replace('/[\x00-\x20\x7f]+/', '', $url);
    if (preg_match('~^(?:javascript|data|vbscript):~i', $scheme)) { return '#'; }
    $parts = parse_url($url);
    if ($parts !== false && in_array(strtolower($parts['host'] ?? ''), ['sanpablo.edu.uy', 'www.sanpablo.edu.uy'], true)) {
        $url = ($parts['path'] ?? '/') ?: '/';
        $url .= isset($parts['query']) ? '?' . $parts['query'] : '';
        $url .= isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
    }
    return cms_public_url($url);
}

function cms_menu_page_content_html(?string $content): string
{
    $html = cms_basic_content_html($content);
    return preg_replace_callback('/\bhref\s*=\s*(["\'])(.*?)\1/i', static function (array $match): string {
        $url = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return 'href="' . cms_e(cms_menu_page_current_url($url)) . '"';
    }, $html) ?? $html;
}

/** Reserve local image proportions before lazy loading; never request remote files. */
function cms_menu_page_image_size(string $src): array
{
    if (!str_starts_with($src, '/') || str_starts_with($src, '//')) { return []; }
    $root = realpath(__DIR__ . '/..');
    $path = rawurldecode((string) parse_url($src, PHP_URL_PATH));
    if (str_contains($path, "\0")) { return []; }
    $file = realpath(__DIR__ . '/..' . $path);
    if ($root === false || $file === false || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file)) { return []; }
    $size = @getimagesize($file);
    return $size ? ['width' => (int) $size[0], 'height' => (int) $size[1]] : [];
}

/** Resolve only active, eligible menus, using exactly the same policy as the header. */
function cms_menu_page_resolve(array $menus, array $submenus, mixed $slug, mixed $id = null): ?array
{
    $validId = is_scalar($id) && ctype_digit((string) $id) && (int) $id > 0;
    if ($id !== null && !$validId) { return null; }
    if (!$validId && (!is_string($slug) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug))) { return null; }
    foreach ($menus as $menu) {
        $menuId = (int) $menu['id_menu'];
        $url = cms_menu_page_url($menu, $submenus[$menuId] ?? [], $menus);
        if ($url !== null && ($validId ? $menuId === (int) $id : $url === '/' . $slug)) {
            return ['menu' => $menu, 'url' => $url];
        }
    }
    return null;
}

/** Three batches, independent of section/image count. No DDL or data migrations. */
function cms_menu_page_load_pages(mysqli $db, array $menu): array
{
    $id = (int) ($menu['id_menu'] ?? 0);
    if ($id <= 0) { return []; }
    $stmt = $db->prepare('SELECT sm.*, m.nombre AS menu_padre,
        sp.titulo AS pagina_titulo, sp.bajada AS pagina_bajada, sp.contenido AS pagina_contenido,
        sp.imagen_hero AS pagina_imagen_hero, sp.hero_video_url AS pagina_hero_video_url,
        sp.hero_video_archivo AS pagina_hero_video_archivo, sp.imagen_secundaria AS pagina_imagen_secundaria,
        sp.video_url AS pagina_video_url, sp.video_archivo AS pagina_video_archivo,
        sp.boton_texto AS pagina_boton_texto, sp.boton_url AS pagina_boton_url,
        sp.meta_title AS pagina_meta_title, sp.meta_description AS pagina_meta_description
        FROM sub_menus sm INNER JOIN menus m ON m.id_menu = sm.id_menu
        LEFT JOIN sub_menu_paginas sp ON sp.id_sub_menu = sm.id_sub_menu
        WHERE sm.id_menu = ? AND sm.estado = 1 AND m.estado = 1
        ORDER BY sm.orden ASC, sm.id_sub_menu ASC');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $pages = [];
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        if (!cms_menu_submenu_is_internal($row)) { continue; }
        $row['pagina_media'] = $row['pagina_history'] = [];
        $pages[(int) $row['id_sub_menu']] = cms_decode_submenu_editor_row($row);
    }
    $stmt->close();
    if (!$pages) { return []; }
    $stmt = $db->prepare('SELECT pm.* FROM sub_menu_pagina_media pm
        INNER JOIN sub_menus sm ON sm.id_sub_menu = pm.id_sub_menu
        WHERE sm.id_menu = ? AND sm.estado = 1 AND pm.visible = 1
        ORDER BY sm.orden ASC, sm.id_sub_menu ASC, pm.orden ASC, pm.id_media ASC');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $media) {
        $subId = (int) $media['id_sub_menu'];
        if (isset($pages[$subId])) { $pages[$subId]['pagina_media'][] = $media; }
    }
    $stmt->close();
    if (cms_table_exists($db, 'sub_menu_historia_item')) {
        $stmt = $db->prepare('SELECT hi.* FROM sub_menu_historia_item hi
            INNER JOIN sub_menus sm ON sm.id_sub_menu = hi.id_sub_menu
            WHERE sm.id_menu = ? AND sm.estado = 1 AND hi.visible = 1
            ORDER BY sm.orden ASC, sm.id_sub_menu ASC, hi.orden ASC, hi.id_historia_item ASC');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $item) {
            $subId = (int) $item['id_sub_menu'];
            if (!isset($pages[$subId])) { continue; }
            foreach (['titulo', 'contenido', 'imagen_alt'] as $key) { $item[$key] = cms_decode_editor_entities((string) ($item[$key] ?? '')); }
            $item['imagen'] = cms_public_image($item['imagen'] ?? '', '');
            $pages[$subId]['pagina_history'][] = $item;
        }
        $stmt->close();
    }
    return array_values($pages);
}

/** Internal anchors and deliberate destinations share the configured submenu order. */
function cms_menu_page_navigation(array $view, array $submenus, string $url): array
{
    $anchors = array_column($view['sections'], 'anchor', 'id');
    usort($submenus, static fn(array $a, array $b): int => [(int) ($a['orden'] ?? 0), (int) $a['id_sub_menu']] <=> [(int) ($b['orden'] ?? 0), (int) $b['id_sub_menu']]);
    $navigation = [];
    foreach ($submenus as $sub) {
        if (isset($sub['estado']) && (int) $sub['estado'] !== 1) { continue; }
        $internal = isset($anchors[(int) $sub['id_sub_menu']]);
        $target = $internal ? $url . '#' . $anchors[(int) $sub['id_sub_menu']] : cms_submenu_public_url($sub);
        if (preg_match('~^(?:javascript|data|vbscript):~i', $target)) { continue; }
        $navigation[] = ['label' => cms_decode_editor_entities((string) $sub['nombre']), 'url' => $target, 'internal' => $internal];
    }
    return $navigation;
}

/** The uploader historically substitutes the physical filename for an empty title. */
function cms_menu_page_gallery_title(array $media): string
{
    $title = trim((string) ($media['titulo'] ?? ''));
    $filename = basename(rawurldecode((string) (parse_url((string) ($media['archivo'] ?? ''), PHP_URL_PATH) ?? '')));
    return in_array($title, [$filename, pathinfo($filename, PATHINFO_FILENAME)], true) ? '' : $title;
}

function cms_menu_page_view(array $menu, array $pages): array
{
    $sections = [];
    $gallery = [];
    $subtitle = '';
    foreach ($pages as $page) {
        $title = trim((string) ($page['pagina_titulo'] ?? '')) ?: (string) $page['nombre'];
        $images = [];
        $sectionGallery = [];
        $videos = [];
        $image = cms_public_image(cms_menu_page_current_url($page['pagina_imagen_hero'] ?? ''), '');
        if ($image !== '') {
            $images[$image] = ['src' => $image, 'title' => $title, 'description' => ''];
        }
        foreach (['pagina_hero_video', 'pagina_video'] as $prefix) {
            $file = cms_public_image(cms_menu_page_current_url($page[$prefix . '_archivo'] ?? ''), '');
            $embed = sp_submenu_video_embed($page[$prefix . '_url'] ?? '');
            if ($file !== '' || $embed !== '') {
                $key = $file !== '' ? $file : $embed;
                $videos[$key] = ['src' => $key, 'type' => $file !== '' ? 'video' : 'embed', 'title' => $title, 'description' => ''];
            }
        }
        foreach ($page['pagina_media'] ?? [] as $media) {
            if ((int) ($media['visible'] ?? 1) !== 1) {
                continue;
            }
            $mediaTitle = trim((string) ($media['titulo'] ?? '')) ?: $title;
            $description = trim((string) ($media['descripcion'] ?? ''));
            if (($media['tipo'] ?? '') === 'imagen') {
                $image = cms_public_image(cms_menu_page_current_url($media['archivo'] ?? ''), '');
                if ($image !== '') {
                    $caption = cms_menu_page_gallery_title($media);
                    $entry = ['src' => $image, 'title' => $caption, 'alt' => $caption !== '' ? $caption : $title, 'description' => $description];
                    $sectionGallery[$image] = $entry;
                    $gallery[$image] = $entry;
                }
            } elseif (in_array($media['tipo'] ?? '', ['video', 'youtube'], true)) {
                $file = ($media['tipo'] === 'video') ? cms_public_image(cms_menu_page_current_url($media['archivo'] ?? ''), '') : '';
                $embed = sp_submenu_video_embed($media['url'] ?? '');
                $source = $file !== '' ? $file : $embed;
                if ($source !== '') {
                    $videos[$source] = ['src' => $source, 'type' => $file !== '' ? 'video' : 'embed', 'title' => $mediaTitle, 'description' => $description];
                }
            }
        }
        // Legacy secondary images are displayed without migrating or creating records.
        $secondary = cms_public_image(cms_menu_page_current_url($page['pagina_imagen_secundaria'] ?? ''), '');
        if ($secondary !== '') {
            $sectionGallery[$secondary] = $sectionGallery[$secondary] ?? ['src' => $secondary, 'title' => '', 'alt' => $title, 'description' => ''];
            $gallery[$secondary] = $sectionGallery[$secondary];
        }
        $bajada = trim((string) ($page['pagina_bajada'] ?? ''));
        if ($subtitle === '' && $bajada !== '') {
            $subtitle = $bajada;
        }
        $sections[] = [
            'anchor' => cms_menu_page_anchor($page),
            'id' => (int) $page['id_sub_menu'],
            'label' => (string) $page['nombre'],
            'title' => $title,
            'subtitle' => $bajada,
            'content' => cms_menu_page_content_html($page['pagina_contenido'] ?? ''),
            'button_text' => trim((string) ($page['pagina_boton_texto'] ?? '')),
            'button_url' => cms_menu_page_current_url($page['pagina_boton_url'] ?? ''),
            'images' => array_values($images),
            'gallery' => array_values($sectionGallery),
            'videos' => array_values($videos),
            'history' => $page['pagina_history'] ?? [],
        ];
    }
    return [
        'title' => (string) $menu['nombre'],
        'subtitle' => $subtitle,
        'hero' => cms_menu_header_media($menu),
        'sections' => $sections,
        'gallery' => count($gallery) >= 2 ? array_values($gallery) : [],
    ];
}
