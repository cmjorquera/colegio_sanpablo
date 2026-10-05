<?php

/** Resolve legacy website links locally; independent services retain their hosts. */
function cms_biblioteca_current_url(?string $url): string
{
    $url = trim((string) $url);
    $parts = parse_url($url);
    if ($parts !== false && in_array(strtolower($parts['host'] ?? ''), ['sanpablo.edu.uy', 'www.sanpablo.edu.uy'], true)) {
        $url = ($parts['path'] ?? '/') ?: '/';
        $url .= isset($parts['query']) ? '?' . $parts['query'] : '';
        $url .= isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
    }
    return cms_public_url($url);
}

function cms_biblioteca_content_html(?string $content): string
{
    $html = cms_basic_content_html($content);
    return preg_replace_callback('/\bhref\s*=\s*(["\'])(.*?)\1/i', static function (array $match): string {
        $url = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return 'href="' . cms_e(cms_biblioteca_current_url($url)) . '"';
    }, $html) ?? $html;
}

/** Existing submenu readers provide the same editorial fields as pagina_submenu.php. */
function cms_biblioteca_load_pages(mysqli $db, array $menu, array $submenus): array
{
    $pages = [];
    foreach ($submenus as $submenu) {
        if ((int) ($submenu['id_menu'] ?? 0) !== (int) $menu['id_menu']
            || (isset($submenu['estado']) && (int) $submenu['estado'] !== 1)) {
            continue;
        }
        $page = cms_get_public_submenu_page($db, (int) $submenu['id_sub_menu'], true);
        if ($page && (int) $page['id_menu'] === (int) $menu['id_menu']) {
            $pages[] = $page;
        }
    }
    usort($pages, static fn(array $a, array $b): int =>
        [(int) ($a['orden'] ?? 0), (int) $a['id_sub_menu']]
        <=> [(int) ($b['orden'] ?? 0), (int) $b['id_sub_menu']]);
    return $pages;
}

/** The uploader historically substitutes the physical filename for an empty title. */
function cms_biblioteca_gallery_title(array $media): string
{
    $title = trim((string) ($media['titulo'] ?? ''));
    $filename = basename(rawurldecode((string) (parse_url((string) ($media['archivo'] ?? ''), PHP_URL_PATH) ?? '')));
    return in_array($title, [$filename, pathinfo($filename, PATHINFO_FILENAME)], true) ? '' : $title;
}

function cms_biblioteca_view(array $menu, array $pages): array
{
    $sections = [];
    $gallery = [];
    $hero = '';
    $subtitle = '';
    foreach ($pages as $page) {
        $title = trim((string) ($page['pagina_titulo'] ?? '')) ?: (string) $page['nombre'];
        $images = [];
        $sectionGallery = [];
        $videos = [];
        $image = cms_public_image(cms_biblioteca_current_url($page['pagina_imagen_hero'] ?? ''), '');
        if ($image !== '') {
            $images[$image] = ['src' => $image, 'title' => $title, 'description' => ''];
            if ($hero === '') {
                $hero = $image;
            }
        }
        foreach (['pagina_hero_video', 'pagina_video'] as $prefix) {
            $file = cms_public_image(cms_biblioteca_current_url($page[$prefix . '_archivo'] ?? ''), '');
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
                $image = cms_public_image(cms_biblioteca_current_url($media['archivo'] ?? ''), '');
                if ($image !== '') {
                    $caption = cms_biblioteca_gallery_title($media);
                    $entry = ['src' => $image, 'title' => $caption, 'alt' => $caption !== '' ? $caption : $title, 'description' => $description];
                    $sectionGallery[$image] = $entry;
                    $gallery[$image] = $entry;
                }
            } elseif (in_array($media['tipo'] ?? '', ['video', 'youtube'], true)) {
                $file = ($media['tipo'] === 'video') ? cms_public_image(cms_biblioteca_current_url($media['archivo'] ?? ''), '') : '';
                $embed = sp_submenu_video_embed($media['url'] ?? '');
                $source = $file !== '' ? $file : $embed;
                if ($source !== '') {
                    $videos[$source] = ['src' => $source, 'type' => $file !== '' ? 'video' : 'embed', 'title' => $mediaTitle, 'description' => $description];
                }
            }
        }
        // Legacy secondary images are displayed without migrating or creating records.
        $secondary = cms_public_image(cms_biblioteca_current_url($page['pagina_imagen_secundaria'] ?? ''), '');
        if ($secondary !== '') {
            $sectionGallery[$secondary] = $sectionGallery[$secondary] ?? ['src' => $secondary, 'title' => '', 'alt' => $title, 'description' => ''];
            $gallery[$secondary] = $sectionGallery[$secondary];
        }
        $bajada = trim((string) ($page['pagina_bajada'] ?? ''));
        if ($subtitle === '' && $bajada !== '') {
            $subtitle = $bajada;
        }
        $sections[] = [
            'anchor' => cms_biblioteca_anchor($page),
            'label' => (string) $page['nombre'],
            'title' => $title,
            'subtitle' => $bajada,
            'content' => cms_biblioteca_content_html($page['pagina_contenido'] ?? ''),
            'button_text' => trim((string) ($page['pagina_boton_texto'] ?? '')),
            'button_url' => cms_biblioteca_current_url($page['pagina_boton_url'] ?? ''),
            'images' => array_values($images),
            'gallery' => array_values($sectionGallery),
            'videos' => array_values($videos),
        ];
    }
    return [
        'title' => (string) $menu['nombre'],
        'subtitle' => $subtitle,
        'hero' => $hero !== '' ? $hero : cms_public_image('assets/images/frontis_01.jpg'),
        'sections' => $sections,
        'gallery' => count($gallery) >= 2 ? array_values($gallery) : [],
    ];
}
