<?php
require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/upload_helpers.php';
require_once __DIR__ . '/public_routes.php';

function cms_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cms_decode_editor_entities(?string $value): string
{
    $decoded = (string) $value;
    $decoded = preg_replace('/&amp;(?=#(?:\d+|x[0-9a-f]+);)/i', '&', $decoded) ?? $decoded;
    $safeNamed = 'aacute|eacute|iacute|oacute|uacute|Aacute|Eacute|Iacute|Oacute|Uacute|uuml|Uuml|ntilde|Ntilde|iquest|iexcl';
    $decoded = preg_replace_callback('/(?:&#(?:\d+|x[0-9a-f]+);|&(?:' . $safeNamed . ');)/i', static function (array $match): string {
        return html_entity_decode($match[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }, $decoded) ?? $decoded;
    return $decoded;
}

function cms_decode_submenu_editor_row(array $row): array
{
    foreach (['nombre', 'pagina_titulo', 'pagina_bajada', 'pagina_contenido', 'pagina_boton_texto', 'pagina_meta_title', 'pagina_meta_description'] as $field) {
        if (array_key_exists($field, $row)) {
            $row[$field] = cms_decode_editor_entities((string) $row[$field]);
        }
    }
    return $row;
}

function cms_redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function cms_set_flash(string $type, string $message): void
{
    $_SESSION['cms_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function cms_get_flash(): ?array
{
    if (!isset($_SESSION['cms_flash'])) {
        return null;
    }

    $flash = $_SESSION['cms_flash'];
    unset($_SESSION['cms_flash']);

    return $flash;
}

function cms_table_exists(mysqli $db, string $table): bool
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return false;
    }

    $stmt = $db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    if ($stmt) {
        $stmt->bind_param('s', $table);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ((int) $count > 0) {
            return true;
        }
    }

    $result = $db->query('SELECT 1 FROM `' . $table . '` LIMIT 0');
    if ($result) {
        $result->free();
        return true;
    }

    return false;
}

function cms_column_exists(mysqli $db, string $table, string $column): bool
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
        return false;
    }

    $stmtInfo = $db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    if ($stmtInfo) {
        $stmtInfo->bind_param('ss', $table, $column);
        $stmtInfo->execute();
        $stmtInfo->bind_result($count);
        $stmtInfo->fetch();
        $stmtInfo->close();
        if ((int) $count > 0) {
            return true;
        }
    }

    $stmt = $db->prepare('SHOW COLUMNS FROM `' . $table . '` LIKE ?');
    if ($stmt) {
        $stmt->bind_param('s', $column);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        if ($exists) {
            return true;
        }
    }

    $result = $db->query('SHOW COLUMNS FROM `' . $table . '`');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if (strcasecmp((string) ($row['Field'] ?? ''), $column) === 0) {
                $result->free();
                return true;
            }
        }
        $result->free();
    }

    return false;
}

function cms_get_connection(): mysqli
{
    static $db = null;
    if ($db instanceof mysqli) {
        return $db;
    }
    $db = (new Conexion())->getConexion();
    return $db;
}

function cms_get_institution_id(mysqli $db): int
{
    if (!empty($_SESSION['id_institucion'])) {
        return (int) $_SESSION['id_institucion'];
    }

    $result = $db->query("SELECT id_institucion FROM institucion ORDER BY id_institucion ASC LIMIT 1");
    if ($result && ($row = $result->fetch_assoc())) {
        return (int) $row['id_institucion'];
    }

    return 1;
}

function cms_section_fixed_names(): array
{
    return ['topbar', 'header_principal', 'footer_principal', 'modal_informativo'];
}

function cms_section_movable_names(): array
{
    return ['hero_principal', 'noticias_home', 'calendario_eventos_home', 'video_destacado_home', 'galeria_home', 'spotify_podcast_home', 'faq_home', 'about_home', 'estadisticas_home'];
}

function cms_section_is_fixed(string $name): bool
{
    return in_array($name, cms_section_fixed_names(), true);
}

function cms_section_is_movable(string $name): bool
{
    return in_array($name, cms_section_movable_names(), true);
}

function cms_ensure_section_tracking_columns(mysqli $db): void
{
    if (!cms_column_exists($db, 'seccion', 'actualizado_en')) {
        $db->query('ALTER TABLE seccion ADD COLUMN actualizado_en DATETIME NULL AFTER fecha_creacion');
    }

    if (!cms_column_exists($db, 'seccion', 'actualizado_por')) {
        $db->query('ALTER TABLE seccion ADD COLUMN actualizado_por INT NULL AFTER actualizado_en');
    }
}

function cms_default_sections(): array
{
    return [
        [
            'nombre_interno' => 'topbar',
            'titulo_admin' => 'Topbar superior',
            'tipo_seccion' => 'topbar',
            'variante' => 'clasico',
            'orden' => 1,
            'observacion' => 'Franja superior con direccion, telefono, correo y redes institucionales.',
        ],
        [
            'nombre_interno' => 'header_principal',
            'titulo_admin' => 'Header principal',
            'tipo_seccion' => 'header',
            'variante' => 'branding',
            'orden' => 2,
            'observacion' => 'Bloque visual completo del encabezado. Incluye logo, identidad institucional, navegacion horizontal basada en menus y sub_menus, y boton principal.',
        ],
        [
            'nombre_interno' => 'hero_principal',
            'titulo_admin' => 'Carrusel principal',
            'tipo_seccion' => 'carousel',
            'variante' => 'texto_izquierda',
            'orden' => 3,
            'observacion' => 'Carrusel destacado del home con slides, imagenes y botones principales.',
        ],
        [
            'nombre_interno' => 'noticias_home',
            'titulo_admin' => 'Noticias home',
            'tipo_seccion' => 'news',
            'variante' => 'cards_4',
            'orden' => 4,
            'observacion' => 'Bloque de noticias destacadas del home con categoria, imagen y fecha.',
        ],
        [
            'nombre_interno' => 'calendario_eventos_home',
            'titulo_admin' => 'Calendario de eventos',
            'tipo_seccion' => 'events',
            'variante' => 'calendario_lista',
            'orden' => 5,
            'observacion' => 'Contenedor del home que muestra calendario institucional y próximos eventos.',
        ],
        [
            'nombre_interno' => 'video_destacado_home',
            'titulo_admin' => 'Video destacado',
            'tipo_seccion' => 'video',
            'variante' => 'banner_video',
            'orden' => 6,
            'observacion' => 'Contenedor del home con banner de video destacado basado en template_07.',
        ],
        [
            'nombre_interno' => 'galeria_home',
            'titulo_admin' => 'Galería home',
            'tipo_seccion' => 'gallery',
            'variante' => 'slider_seven',
            'orden' => 7,
            'observacion' => 'Contenedor del home con galería visual tipo carrusel basado en template_07.',
        ],
        [
            'nombre_interno' => 'spotify_podcast_home',
            'titulo_admin' => 'Spotify Podcast Home',
            'tipo_seccion' => 'podcast',
            'variante' => 'spotify_home',
            'orden' => 8,
            'observacion' => 'Contenedor del home para promocionar el canal de Spotify del colegio y sus episodios destacados.',
        ],
        [
            'nombre_interno' => 'faq_home',
            'titulo_admin' => 'Preguntas frecuentes',
            'tipo_seccion' => 'faq',
            'variante' => 'imagen_lateral',
            'orden' => 8,
            'observacion' => 'Contenedor de preguntas frecuentes con acordeon e imagen lateral.',
        ],
        [
            'nombre_interno' => 'about_home',
            'titulo_admin' => 'Sobre nosotros',
            'tipo_seccion' => 'content',
            'variante' => 'imagen_texto',
            'orden' => 9,
            'observacion' => 'Bloque institucional de presentacion con imagen principal, video y descripcion.',
        ],
        [
            'nombre_interno' => 'estadisticas_home',
            'titulo_admin' => 'Estadisticas home',
            'tipo_seccion' => 'content',
            'variante' => 'contadores_animados',
            'orden' => 9,
            'observacion' => 'Contenedor administrable de datos destacados con contadores animados al entrar en pantalla.',
        ],
        [
            'nombre_interno' => 'footer_principal',
            'titulo_admin' => 'Footer principal',
            'tipo_seccion' => 'footer',
            'variante' => 'institucional',
            'orden' => 10,
            'observacion' => 'Este es el contenedor del footer. Aqui se muestran logo, descripcion institucional, enlaces rapidos, contacto, redes sociales y datos principales del sitio.',
        ],
        [
            'nombre_interno' => 'modal_informativo',
            'titulo_admin' => 'Modal informativo',
            'tipo_seccion' => 'modal',
            'variante' => 'bienvenida',
            'orden' => 99,
            'observacion' => 'Modal administrable que se muestra al cargar el sitio por primera vez o cuando cambia su contenido.',
        ],
    ];
}

function cms_sync_sections(mysqli $db, int $institutionId): void
{
    if (!cms_column_exists($db, 'seccion', 'observacion')) {
        $db->query("ALTER TABLE seccion ADD COLUMN observacion TEXT NULL AFTER orden");
    }
    cms_ensure_section_tracking_columns($db);

    $selectStmt = $db->prepare('SELECT id_seccion FROM seccion WHERE id_institucion = ? AND nombre_interno = ? LIMIT 1');
    $insertStmt = $db->prepare('INSERT INTO seccion (id_institucion, nombre_interno, titulo_admin, tipo_seccion, variante, visible, orden, observacion) VALUES (?, ?, ?, ?, ?, \'si\', ?, ?)');
    $updateStmt = $db->prepare('UPDATE seccion SET titulo_admin = ?, tipo_seccion = ?, variante = ?, observacion = ? WHERE id_seccion = ?');

    foreach (cms_default_sections() as $section) {
        $name = $section['nombre_interno'];
        $selectStmt->bind_param('is', $institutionId, $name);
        $selectStmt->execute();
        $result = $selectStmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;

        if ($row) {
            $idSeccion = (int) $row['id_seccion'];
            $updateStmt->bind_param(
                'ssssi',
                $section['titulo_admin'],
                $section['tipo_seccion'],
                $section['variante'],
                $section['observacion'],
                $idSeccion
            );
            $updateStmt->execute();
        } else {
            $insertStmt->bind_param(
                'issssis',
                $institutionId,
                $section['nombre_interno'],
                $section['titulo_admin'],
                $section['tipo_seccion'],
                $section['variante'],
                $section['orden'],
                $section['observacion']
            );
            $insertStmt->execute();
        }
    }

    $selectStmt->close();
    $insertStmt->close();
    $updateStmt->close();

    cms_remove_legacy_menu_principal_section($db, $institutionId);
    cms_sync_modal_informativo_defaults($db, $institutionId);
    cms_sync_spotify_podcast_defaults($db, $institutionId);
}

function cms_sync_spotify_podcast_defaults(mysqli $db, int $institutionId): void
{
    $stmtSection = $db->prepare("SELECT id_seccion FROM seccion WHERE id_institucion = ? AND nombre_interno = 'spotify_podcast_home' LIMIT 1");
    if (!$stmtSection) {
        return;
    }

    $stmtSection->bind_param('i', $institutionId);
    $stmtSection->execute();
    $result = $stmtSection->get_result();
    $section = $result ? $result->fetch_assoc() : null;
    $stmtSection->close();

    if (!$section) {
        return;
    }

    $idSeccion = (int) $section['id_seccion'];

    $defaults = [
        'subtitulo_bloque' => 'Podcast institucional',
        'titulo_bloque' => 'Escucha el Colegio San Pablo en Spotify',
        'descripcion_bloque' => 'Conversaciones, historias y experiencias de nuestra comunidad educativa para escuchar cuando quieras.',
        'nombre_canal' => 'Podcast Colegio San Pablo',
        'autor_canal' => 'Colegio San Pablo',
        'texto_boton' => 'Ir al canal de Spotify',
        'url_boton' => 'https://open.spotify.com/',
        'mostrar_episodios' => 'si',
        'cantidad_items' => '3',
        'mostrar_qr' => 'si',
        'texto_qr' => 'Escuchanos desde tu celular',
        'descripcion_qr' => 'Escanea el codigo o abre Spotify para seguir los nuevos episodios del colegio.',
    ];

    $stmtExistsConfig = $db->prepare('SELECT id_config FROM seccion_config WHERE id_seccion = ? AND clave = ? LIMIT 1');
    $stmtInsertConfig = $db->prepare('INSERT INTO seccion_config (id_seccion, clave, valor) VALUES (?, ?, ?)');
    if ($stmtExistsConfig && $stmtInsertConfig) {
        foreach ($defaults as $clave => $valor) {
            $stmtExistsConfig->bind_param('is', $idSeccion, $clave);
            $stmtExistsConfig->execute();
            $existsResult = $stmtExistsConfig->get_result();
            if ($existsResult && $existsResult->fetch_assoc()) {
                continue;
            }
            $stmtInsertConfig->bind_param('iss', $idSeccion, $clave, $valor);
            $stmtInsertConfig->execute();
        }
    }
    if ($stmtExistsConfig) {
        $stmtExistsConfig->close();
    }
    if ($stmtInsertConfig) {
        $stmtInsertConfig->close();
    }

    $stmtCount = $db->prepare('SELECT COUNT(*) AS total FROM seccion_item WHERE id_seccion = ?');
    if (!$stmtCount) {
        return;
    }
    $stmtCount->bind_param('i', $idSeccion);
    $stmtCount->execute();
    $countResult = $stmtCount->get_result();
    $totalItems = (int) (($countResult ? $countResult->fetch_assoc()['total'] : 0));
    $stmtCount->close();

    if ($totalItems > 0) {
        return;
    }

    $items = [
        [
            'etiqueta' => 'canal_spotify',
            'titulo' => 'Podcast Colegio San Pablo',
            'subtitulo' => 'Colegio San Pablo',
            'descripcion' => 'Un espacio de encuentro con voces, proyectos e historias de nuestra comunidad educativa.',
            'boton_1_texto' => 'Seguir en Spotify',
            'boton_1_url' => 'https://open.spotify.com/',
            'url' => 'https://open.spotify.com/',
            'fecha_publicacion' => null,
            'orden' => 1,
        ],
        [
            'etiqueta' => 'episodio_spotify',
            'titulo' => 'Bienvenidos al podcast del colegio',
            'subtitulo' => 'Episodio 1 · 12 min',
            'descripcion' => 'Presentacion del canal y de las historias que iremos compartiendo durante el ano.',
            'boton_1_texto' => 'Escuchar episodio',
            'boton_1_url' => 'https://open.spotify.com/',
            'url' => 'https://open.spotify.com/',
            'fecha_publicacion' => date('Y-m-d'),
            'orden' => 2,
        ],
        [
            'etiqueta' => 'episodio_spotify',
            'titulo' => 'Voces de nuestra comunidad',
            'subtitulo' => 'Episodio 2 · 18 min',
            'descripcion' => 'Una conversacion cercana sobre vida escolar, identidad y participacion.',
            'boton_1_texto' => 'Escuchar episodio',
            'boton_1_url' => 'https://open.spotify.com/',
            'url' => 'https://open.spotify.com/',
            'fecha_publicacion' => date('Y-m-d'),
            'orden' => 3,
        ],
    ];

    $stmtItem = $db->prepare('INSERT INTO seccion_item (id_seccion, etiqueta, titulo, subtitulo, descripcion, boton_1_texto, boton_1_url, url, fecha_publicacion, visible, orden) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'si\', ?)');
    if (!$stmtItem) {
        return;
    }
    foreach ($items as $item) {
        $stmtItem->bind_param(
            'issssssssi',
            $idSeccion,
            $item['etiqueta'],
            $item['titulo'],
            $item['subtitulo'],
            $item['descripcion'],
            $item['boton_1_texto'],
            $item['boton_1_url'],
            $item['url'],
            $item['fecha_publicacion'],
            $item['orden']
        );
        $stmtItem->execute();
    }
    $stmtItem->close();
}

function cms_remove_legacy_menu_principal_section(mysqli $db, int $institutionId): void
{
    $stmtConfigs = $db->prepare(
        "DELETE sc FROM seccion_config sc
         INNER JOIN seccion s ON s.id_seccion = sc.id_seccion
         WHERE s.id_institucion = ? AND s.nombre_interno = 'menu_principal'"
    );
    if ($stmtConfigs) {
        $stmtConfigs->bind_param('i', $institutionId);
        $stmtConfigs->execute();
        $stmtConfigs->close();
    }

    $stmtItems = $db->prepare(
        "DELETE si FROM seccion_item si
         INNER JOIN seccion s ON s.id_seccion = si.id_seccion
         WHERE s.id_institucion = ? AND s.nombre_interno = 'menu_principal'"
    );
    if ($stmtItems) {
        $stmtItems->bind_param('i', $institutionId);
        $stmtItems->execute();
        $stmtItems->close();
    }

    $stmtSection = $db->prepare("DELETE FROM seccion WHERE id_institucion = ? AND nombre_interno = 'menu_principal'");
    if ($stmtSection) {
        $stmtSection->bind_param('i', $institutionId);
        $stmtSection->execute();
        $stmtSection->close();
    }
}

function cms_sync_modal_informativo_defaults(mysqli $db, int $institutionId): void
{
    $stmtSection = $db->prepare("SELECT id_seccion FROM seccion WHERE id_institucion = ? AND nombre_interno = 'modal_informativo' LIMIT 1");
    if (!$stmtSection) {
        return;
    }

    $stmtSection->bind_param('i', $institutionId);
    $stmtSection->execute();
    $result = $stmtSection->get_result();
    $section = $result ? $result->fetch_assoc() : null;
    $stmtSection->close();

    if (!$section) {
        return;
    }

    $idSeccion = (int) $section['id_seccion'];
    $stmtCount = $db->prepare('SELECT COUNT(*) AS total FROM seccion_item WHERE id_seccion = ?');
    if (!$stmtCount) {
        return;
    }

    $stmtCount->bind_param('i', $idSeccion);
    $stmtCount->execute();
    $countResult = $stmtCount->get_result();
    $totalItems = (int) (($countResult ? $countResult->fetch_assoc()['total'] : 0));
    $stmtCount->close();

    if ($totalItems > 0) {
        return;
    }

    $titulo = 'Bienvenido a la version 2025';
    $descripcion = "Hemos renovado nuestro sitio web para que toda la comunidad del **Colegio San Pablo** pueda informarse y conectarse de forma mas simple y rapida.\n\nNueva organizacion del menu por niveles educativos.\nAcceso directo a **Mi San Pablo** para familias, estudiantes, funcionarios y docentes.\nSeccion de noticias y comunicaciones actualizada durante el ano.\nDiseno adaptado para celulares, tablets y computadores.\n\nTe invitamos a recorrer el sitio y guardarlo en tus favoritos para mantenerte siempre informado. Gracias por ser parte de nuestra comunidad.";
    $botonTexto = 'Comenzar a navegar';
    $botonUrl = '#';
    $orden = 1;

    $stmtItem = $db->prepare('INSERT INTO seccion_item (id_seccion, titulo, descripcion, boton_1_texto, boton_1_url, visible, orden) VALUES (?, ?, ?, ?, ?, \'si\', ?)');
    if ($stmtItem) {
        $stmtItem->bind_param('issssi', $idSeccion, $titulo, $descripcion, $botonTexto, $botonUrl, $orden);
        $stmtItem->execute();
        $stmtItem->close();
    }

    $defaults = [
        'mostrar' => 'una_vez',
        'delay_ms' => '650',
        'color_boton' => '#ef4444',
    ];
    $stmtConfig = $db->prepare('INSERT INTO seccion_config (id_seccion, clave, valor) VALUES (?, ?, ?)');
    if ($stmtConfig) {
        foreach ($defaults as $clave => $valor) {
            $stmtConfig->bind_param('iss', $idSeccion, $clave, $valor);
            $stmtConfig->execute();
        }
        $stmtConfig->close();
    }
}

function cms_get_preview_target(string $name): string
{
    $anchors = [
        'topbar' => '#topbar',
        'header_principal' => '#header-principal',
        'hero_principal' => '#hero-principal',
        'noticias_home' => '#noticias',
        'calendario_eventos_home' => '#calendario-eventos-home',
        'video_destacado_home' => '#video-destacado-home',
        'galeria_home' => '#galeria',
        'spotify_podcast_home' => '#spotify-podcast-home',
        'faq_home' => '#faq',
        'about_home' => '#about',
        'estadisticas_home' => '#estadisticas',
        'footer_principal'  => '#footer-principal',
        'modal_informativo' => '#modal-informativo',
    ];

    return 'index.php' . ($anchors[$name] ?? '');
}

function cms_get_component_path(string $name): ?string
{
    $fallbackComponents = [
        'header_principal'    => 'header',
    ];

    $path = __DIR__ . '/../componentes/' . $name . '.php';
    if (!is_file($path) && isset($fallbackComponents[$name])) {
        $path = __DIR__ . '/../componentes/' . $fallbackComponents[$name] . '.php';
    }

    return is_file($path) ? $path : null;
}

function cms_get_site_data(mysqli $db): array
{
    $institutionId = cms_get_institution_id($db);
    cms_sync_sections($db, $institutionId);

    $institution = null;
    $sections = [];
    $configsMap = [];
    $itemsMap = [];
    $categoriesById = [];
    $arrMenus = [];
    $arrSubs = [];

    $resInstitution = $db->query("SELECT * FROM institucion WHERE id_institucion = " . $institutionId . " LIMIT 1");
    if ($resInstitution) {
        $institution = $resInstitution->fetch_assoc();
    }

    $resMenus = $db->query("SELECT id_menu, nombre, url, icono, orden FROM menus WHERE estado = 1 ORDER BY orden ASC, id_menu ASC");
    if ($resMenus) {
        $arrMenus = $resMenus->fetch_all(MYSQLI_ASSOC);
        $resMenus->free();
    }

    $resSubs = $db->query("SELECT id_sub_menu, id_menu, nombre, url, icono, orden FROM sub_menus WHERE estado = 1 ORDER BY id_menu ASC, orden ASC, id_sub_menu ASC");
    if ($resSubs) {
        while ($row = $resSubs->fetch_assoc()) {
            $row['url'] = cms_submenu_public_url($row);
            $arrSubs[(int) $row['id_menu']][] = $row;
        }
        $resSubs->free();
    }

    $stmtSections = $db->prepare('SELECT * FROM seccion WHERE id_institucion = ? ORDER BY orden ASC, id_seccion ASC');
    $stmtSections->bind_param('i', $institutionId);
    $stmtSections->execute();
    $resultSections = $stmtSections->get_result();
    $sections = $resultSections ? $resultSections->fetch_all(MYSQLI_ASSOC) : [];
    $stmtSections->close();

    $resConfigs = $db->query("SELECT sc.*, s.nombre_interno FROM seccion_config sc INNER JOIN seccion s ON s.id_seccion = sc.id_seccion");
    if ($resConfigs) {
        while ($row = $resConfigs->fetch_assoc()) {
            $configsMap[$row['nombre_interno']][$row['clave']] = $row['valor'];
        }
        $resConfigs->free();
    }

    $resItems = $db->query("SELECT si.*, s.nombre_interno
        FROM seccion_item si
        INNER JOIN seccion s ON s.id_seccion = si.id_seccion
        WHERE si.visible = 'si'
        ORDER BY s.orden ASC, si.orden ASC, si.id_item ASC");
    if ($resItems) {
        while ($row = $resItems->fetch_assoc()) {
            $itemsMap[$row['nombre_interno']][] = $row;
        }
        $resItems->free();
    }

    if (cms_table_exists($db, 'categoria_noticia')) {
        $resCategories = $db->query('SELECT * FROM categoria_noticia ORDER BY nombre ASC, id_categoria ASC');
        if ($resCategories) {
            while ($row = $resCategories->fetch_assoc()) {
                $categoriesById[(int) $row['id_categoria']] = $row;
            }
            $resCategories->free();
        }
    }

    return [
        'institution_id' => $institutionId,
        'institution' => $institution,
        'sections' => $sections,
        'configs' => $configsMap,
        'items' => $itemsMap,
        'categories' => $categoriesById,
        'menus' => $arrMenus,
        'subs' => $arrSubs,
    ];
}

function cms_cfg(array $configs, string $sectionName, string $key, string $default = ''): string
{
    return $configs[$sectionName][$key] ?? $default;
}

function cms_find_section(array $sections, int $idSeccion): ?array
{
    foreach ($sections as $section) {
        if ((int) $section['id_seccion'] === $idSeccion) {
            return $section;
        }
    }
    return null;
}

function cms_list_sections_admin(mysqli $db, int $institutionId): array
{
    cms_ensure_section_tracking_columns($db);

    $sql = "SELECT s.*, COUNT(si.id_item) AS total_items
                   , u.nombre AS actualizado_por_nombre
                   , u.apellido AS actualizado_por_apellido
                   , u.usuario AS actualizado_por_usuario
                   , u.email AS actualizado_por_email
            FROM seccion s
            LEFT JOIN seccion_item si ON si.id_seccion = s.id_seccion
            LEFT JOIN usuario u ON u.id_usuario = s.actualizado_por
            WHERE s.id_institucion = ?
            GROUP BY s.id_seccion
            ORDER BY s.orden ASC, s.id_seccion ASC";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $institutionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function cms_get_section(mysqli $db, int $idSeccion): ?array
{
    $stmt = $db->prepare('SELECT * FROM seccion WHERE id_seccion = ? LIMIT 1');
    $stmt->bind_param('i', $idSeccion);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function cms_get_section_configs(mysqli $db, int $idSeccion): array
{
    $stmt = $db->prepare('SELECT * FROM seccion_config WHERE id_seccion = ? ORDER BY clave ASC, id_config ASC');
    $stmt->bind_param('i', $idSeccion);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function cms_get_section_items(mysqli $db, int $idSeccion): array
{
    $stmt = $db->prepare('SELECT * FROM seccion_item WHERE id_seccion = ? ORDER BY orden ASC, id_item ASC');
    $stmt->bind_param('i', $idSeccion);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function cms_get_item(mysqli $db, int $idItem): ?array
{
    $stmt = $db->prepare('SELECT * FROM seccion_item WHERE id_item = ? LIMIT 1');
    $stmt->bind_param('i', $idItem);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function cms_get_menu(mysqli $db, int $idMenu): ?array
{
    $stmt = $db->prepare('SELECT * FROM menus WHERE id_menu = ? LIMIT 1');
    $stmt->bind_param('i', $idMenu);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function cms_list_menus(mysqli $db): array
{
    $sql = "SELECT m.*,
                   COALESCE(ms.total_submenus, 0) AS total_submenus,
                   u.nombre AS actualizado_por_nombre,
                   u.apellido AS actualizado_por_apellido,
                   u.usuario AS actualizado_por_usuario,
                   u.email AS actualizado_por_email
            FROM menus m
            LEFT JOIN (
                SELECT id_menu, COUNT(*) AS total_submenus
                FROM sub_menus
                GROUP BY id_menu
            ) ms ON ms.id_menu = m.id_menu
            LEFT JOIN usuario u ON u.id_usuario = m.actualizado_por
            ORDER BY m.orden ASC, m.id_menu ASC";
    $result = $db->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function cms_get_submenu(mysqli $db, int $idSubMenu): ?array
{
    cms_ensure_submenu_page_tables($db);
    $stmt = $db->prepare("SELECT sm.*, m.nombre AS menu_padre,
                                 sp.id_pagina AS pagina_id, sp.titulo AS pagina_titulo,
                                 sp.bajada AS pagina_bajada, sp.contenido AS pagina_contenido,
                                 sp.imagen_hero AS pagina_imagen_hero,
                                 sp.hero_video_url AS pagina_hero_video_url,
                                 sp.hero_video_archivo AS pagina_hero_video_archivo,
                                 sp.imagen_secundaria AS pagina_imagen_secundaria,
                                 sp.video_url AS pagina_video_url,
                                 sp.video_archivo AS pagina_video_archivo,
                                 sp.boton_texto AS pagina_boton_texto,
                                 sp.boton_url AS pagina_boton_url,
                                 sp.meta_title AS pagina_meta_title,
                                 sp.meta_description AS pagina_meta_description
                          FROM sub_menus sm
                          INNER JOIN menus m ON m.id_menu = sm.id_menu
                          LEFT JOIN sub_menu_paginas sp ON sp.id_sub_menu = sm.id_sub_menu
                          WHERE sm.id_sub_menu = ? LIMIT 1");
    $stmt->bind_param('i', $idSubMenu);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ? cms_decode_submenu_editor_row($row) : null;
}

function cms_list_submenus(mysqli $db): array
{
    cms_ensure_submenu_page_tables($db);
    $sql = "SELECT sm.*, m.nombre AS menu_padre,
                   sp.id_pagina AS pagina_id,
                   sp.titulo AS pagina_titulo,
                   sp.bajada AS pagina_bajada,
                   sp.contenido AS pagina_contenido,
                   sp.imagen_hero AS pagina_imagen_hero,
                   sp.hero_video_url AS pagina_hero_video_url,
                   sp.hero_video_archivo AS pagina_hero_video_archivo,
                   sp.imagen_secundaria AS pagina_imagen_secundaria,
                   sp.video_url AS pagina_video_url,
                   sp.video_archivo AS pagina_video_archivo,
                   sp.boton_texto AS pagina_boton_texto,
                   sp.boton_url AS pagina_boton_url,
                   sp.meta_title AS pagina_meta_title,
                   sp.meta_description AS pagina_meta_description,
                   COALESCE(sp.actualizado_en, sm.actualizado_en) AS editorial_actualizado_en,
                   COALESCE(sp.actualizado_por, sm.actualizado_por) AS editorial_actualizado_por,
                   COALESCE(NULLIF(CONCAT_WS(' ', up.nombre, up.apellido), ''), up.usuario, up.email,
                            NULLIF(CONCAT_WS(' ', us.nombre, us.apellido), ''), us.usuario, us.email) AS editorial_actualizado_usuario,
                   (SELECT COUNT(*) FROM sub_menu_pagina_media smpm
                    WHERE smpm.id_sub_menu = sm.id_sub_menu AND smpm.visible = 1 AND smpm.tipo = 'imagen') AS media_imagenes,
                   (SELECT COUNT(*) FROM sub_menu_pagina_media smpm
                    WHERE smpm.id_sub_menu = sm.id_sub_menu AND smpm.visible = 1 AND smpm.tipo IN ('video','youtube')) AS media_videos
            FROM sub_menus sm
            INNER JOIN menus m ON m.id_menu = sm.id_menu
            LEFT JOIN sub_menu_paginas sp ON sp.id_sub_menu = sm.id_sub_menu
            LEFT JOIN usuario up ON up.id_usuario = sp.actualizado_por
            LEFT JOIN usuario us ON us.id_usuario = sm.actualizado_por
            ORDER BY m.orden ASC, sm.orden ASC, sm.id_sub_menu ASC";
    $result = $db->query($sql);
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    foreach ($rows as &$row) {
        $row = cms_decode_submenu_editor_row($row);
        $row['url_publica'] = cms_submenu_public_url($row);
        cms_migrate_submenu_secondary_image_to_gallery($db, (int) $row['id_sub_menu'], (string) ($row['pagina_imagen_secundaria'] ?? ''));
        $row['pagina_imagen_secundaria'] = '';
        $row['pagina_media'] = cms_list_submenu_page_media($db, (int) $row['id_sub_menu']);
        $titulo = trim((string) ($row['pagina_titulo'] ?? ''));
        $bajada = trim((string) ($row['pagina_bajada'] ?? ''));
        $contenido = trim(strip_tags((string) ($row['pagina_contenido'] ?? '')));
        $isExternal = preg_match('~^https?://~i', trim((string) ($row['url'] ?? ''))) === 1
            && stripos((string) $row['url'], 'pagina_submenu.php') === false;
        $row['es_enlace_externo'] = $isExternal ? 1 : 0;
        $row['estado_contenido'] = $isExternal ? 'externo'
            : (($titulo === '' && $bajada === '' && $contenido === '') ? 'vacio'
            : (($titulo !== '' && $bajada !== '' && $contenido !== '') ? 'completo' : 'incompleto'));
        $heroImage = trim((string) ($row['pagina_imagen_hero'] ?? '')) !== '';
        $heroVideo = trim((string) ($row['pagina_hero_video_url'] ?? '')) !== '' || trim((string) ($row['pagina_hero_video_archivo'] ?? '')) !== '';
        $row['media_tiene_hero'] = ($heroImage || $heroVideo) ? 1 : 0;
    }
    unset($row);
    return $rows;
}

function cms_ensure_submenu_page_tables(mysqli $db): void
{
    $db->query("CREATE TABLE IF NOT EXISTS sub_menu_paginas (
        id_pagina INT NOT NULL AUTO_INCREMENT,
        id_sub_menu INT NOT NULL,
        titulo VARCHAR(180) DEFAULT NULL,
        bajada VARCHAR(300) DEFAULT NULL,
        contenido MEDIUMTEXT NULL,
        imagen_hero VARCHAR(255) DEFAULT NULL,
        hero_video_url VARCHAR(500) DEFAULT NULL,
        hero_video_archivo VARCHAR(255) DEFAULT NULL,
        imagen_secundaria VARCHAR(255) DEFAULT NULL,
        video_url VARCHAR(500) DEFAULT NULL,
        video_archivo VARCHAR(255) DEFAULT NULL,
        boton_texto VARCHAR(150) DEFAULT NULL,
        boton_url VARCHAR(255) DEFAULT NULL,
        meta_title VARCHAR(180) DEFAULT NULL,
        meta_description VARCHAR(300) DEFAULT NULL,
        actualizado_en DATETIME DEFAULT NULL,
        actualizado_por INT DEFAULT NULL,
        PRIMARY KEY (id_pagina),
        UNIQUE KEY uq_sub_menu_paginas_submenu (id_sub_menu)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if (!cms_column_exists($db, 'sub_menu_paginas', 'hero_video_url')) {
        $db->query('ALTER TABLE sub_menu_paginas ADD COLUMN hero_video_url VARCHAR(500) DEFAULT NULL AFTER imagen_hero');
    }

    if (!cms_column_exists($db, 'sub_menu_paginas', 'hero_video_archivo')) {
        $db->query('ALTER TABLE sub_menu_paginas ADD COLUMN hero_video_archivo VARCHAR(255) DEFAULT NULL AFTER hero_video_url');
    }

    $db->query("CREATE TABLE IF NOT EXISTS sub_menu_pagina_media (
        id_media INT NOT NULL AUTO_INCREMENT,
        id_sub_menu INT NOT NULL,
        tipo ENUM('imagen','video','youtube') NOT NULL DEFAULT 'imagen',
        archivo VARCHAR(255) DEFAULT NULL,
        url VARCHAR(500) DEFAULT NULL,
        titulo VARCHAR(180) DEFAULT NULL,
        descripcion VARCHAR(300) DEFAULT NULL,
        visible TINYINT(1) NOT NULL DEFAULT 1,
        orden INT NOT NULL DEFAULT 0,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_media),
        KEY idx_sub_menu_pagina_media_submenu (id_sub_menu)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function cms_submenu_public_url(array $submenu): string
{
    $url = trim((string) ($submenu['url'] ?? ''));
    if ($url !== '' && $url !== '#') {
        return cms_public_url($url);
    }

    return '/pagina/' . (int) ($submenu['id_sub_menu'] ?? 0);
}

function cms_list_submenu_page_media(mysqli $db, int $idSubMenu): array
{
    cms_ensure_submenu_page_tables($db);
    $stmt = $db->prepare('SELECT * FROM sub_menu_pagina_media WHERE id_sub_menu = ? ORDER BY orden ASC, id_media ASC');
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('i', $idSubMenu);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function cms_add_submenu_gallery_image(mysqli $db, int $idSubMenu, array $post): array
{
    if ($idSubMenu <= 0) {
        throw new RuntimeException('No se pudo identificar el submenú.');
    }
    $archivo = cms_upload_image('pagina_galeria_imagen', 'submenus/' . $idSubMenu, null);
    if (!$archivo) {
        throw new RuntimeException('Selecciona una imagen válida para la galería.');
    }
    $titulo = trim(cms_decode_editor_entities((string) ($post['pagina_galeria_titulo'] ?? '')));
    if ($titulo === '') {
        $titulo = pathinfo(basename($archivo), PATHINFO_FILENAME);
    }
    $result = $db->query('SELECT COALESCE(MAX(orden), 0) + 1 AS next_orden FROM sub_menu_pagina_media WHERE id_sub_menu = ' . $idSubMenu);
    $orden = $result ? (int) $result->fetch_assoc()['next_orden'] : 1;
    $stmt = $db->prepare("INSERT INTO sub_menu_pagina_media (id_sub_menu,tipo,archivo,titulo,visible,orden) VALUES (?,'imagen',?,?,1,?)");
    $stmt->bind_param('issi', $idSubMenu, $archivo, $titulo, $orden);
    if (!$stmt->execute()) {
        $stmt->close();
        cms_eliminar_archivo_seguro($archivo);
        throw new RuntimeException('No se pudo registrar la imagen de galería.');
    }
    $idMedia = (int) $db->insert_id;
    $stmt->close();
    $stmt = $db->prepare('SELECT * FROM sub_menu_pagina_media WHERE id_media = ? AND id_sub_menu = ? LIMIT 1');
    $stmt->bind_param('ii', $idMedia, $idSubMenu);
    $stmt->execute();
    $media = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();
    return $media;
}

function cms_delete_submenu_gallery_media(mysqli $db, int $idSubMenu, int $idMedia): void
{
    $stmt = $db->prepare('SELECT archivo FROM sub_menu_pagina_media WHERE id_media = ? AND id_sub_menu = ? LIMIT 1');
    $stmt->bind_param('ii', $idMedia, $idSubMenu);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) { throw new RuntimeException('La imagen ya no existe.'); }
    $stmt = $db->prepare('DELETE FROM sub_menu_pagina_media WHERE id_media = ? AND id_sub_menu = ?');
    $stmt->bind_param('ii', $idMedia, $idSubMenu);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        $stmt->close();
        throw new RuntimeException('No se pudo eliminar la imagen de la base de datos.');
    }
    $stmt->close();
    if (!cms_submenu_file_is_referenced($db, (string) ($row['archivo'] ?? ''))) {
        cms_eliminar_archivo_seguro($row['archivo'] ?? null);
    }
}

function cms_submenu_file_is_referenced(mysqli $db, string $path): bool
{
    $path = trim($path);
    if ($path === '') { return false; }
    foreach ([
        ['sub_menu_paginas', ['imagen_hero', 'hero_video_archivo', 'imagen_secundaria', 'video_archivo']],
        ['sub_menu_pagina_media', ['archivo']],
        ['sub_menu_historia_item', ['imagen']],
    ] as [$table, $columns]) {
        if (!cms_table_exists($db, $table)) { continue; }
        $where = implode(' OR ', array_map(static fn(string $column): string => '`' . $column . '` = ?', $columns));
        $stmt = $db->prepare('SELECT 1 FROM `' . $table . '` WHERE ' . $where . ' LIMIT 1');
        if (!$stmt) { continue; }
        $types = str_repeat('s', count($columns));
        $values = array_fill(0, count($columns), $path);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $found = (bool) $stmt->get_result()->fetch_row();
        $stmt->close();
        if ($found) { return true; }
    }
    return false;
}

function cms_toggle_submenu_gallery_media(mysqli $db, int $idSubMenu, int $idMedia): int
{
    $stmt = $db->prepare('UPDATE sub_menu_pagina_media SET visible = IF(visible=1,0,1) WHERE id_media=? AND id_sub_menu=?');
    $stmt->bind_param('ii', $idMedia, $idSubMenu);
    $stmt->execute();
    $stmt->close();
    $stmt = $db->prepare('SELECT visible FROM sub_menu_pagina_media WHERE id_media=? AND id_sub_menu=? LIMIT 1');
    $stmt->bind_param('ii', $idMedia, $idSubMenu);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) { throw new RuntimeException('La imagen ya no existe.'); }
    return (int) $row['visible'];
}

function cms_list_submenu_history_items(mysqli $db, int $idSubMenu, bool $onlyVisible = false): array
{
    if ($idSubMenu !== 2 || !cms_table_exists($db, 'sub_menu_historia_item')) { return []; }
    $sql = 'SELECT * FROM sub_menu_historia_item WHERE id_sub_menu = ?' . ($onlyVisible ? ' AND visible = 1' : '') . ' ORDER BY orden ASC, id_historia_item ASC';
    $stmt = $db->prepare($sql); $stmt->bind_param('i', $idSubMenu); $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    return $rows;
}

function cms_save_submenu_history_item(mysqli $db, array $post): array
{
    $idSubMenu = (int)($post['id_sub_menu'] ?? 0);
    if ($idSubMenu !== 2 || !cms_table_exists($db, 'sub_menu_historia_item')) { throw new RuntimeException('La tabla de historia visual no está instalada o el submenú no es válido.'); }
    $idItem=(int)($post['id_historia_item']??0); $titulo=trim(cms_decode_editor_entities((string)($post['titulo']??'')));
    if($titulo===''){throw new RuntimeException('El año o título es obligatorio.');}
    $contenido=cms_basic_content_html(cms_decode_editor_entities((string)($post['contenido']??'')));
    $alt=trim(cms_decode_editor_entities((string)($post['imagen_alt']??''))); $visible=(string)($post['visible']??'')==='1'?1:0;
    $current=null;
    if($idItem>0){$stmt=$db->prepare('SELECT * FROM sub_menu_historia_item WHERE id_historia_item=? AND id_sub_menu=2 LIMIT 1');$stmt->bind_param('i',$idItem);$stmt->execute();$current=$stmt->get_result()->fetch_assoc();$stmt->close();if(!$current){throw new RuntimeException('El hito no existe.');}}
    $imagen=$current['imagen']??null; $newImage=null; $oldImage=null;
    if(isset($_FILES['imagen']) && (int)($_FILES['imagen']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
        $ext=strtolower(pathinfo((string)$_FILES['imagen']['name'],PATHINFO_EXTENSION));
        if(!in_array($ext,['jpg','jpeg','png','webp'],true)){throw new RuntimeException('La imagen debe ser JPG, JPEG, PNG o WEBP.');}
        cms_validar_nombre_archivo((string)$_FILES['imagen']['name']);
        $historyFile=$_FILES['imagen'];
        $historyFile['name']='historia-' . $titulo . '.' . $ext;
        $newImage=cms_guardar_archivo($historyFile,'submenus/2/historia','imagenes');
        if($newImage){$oldImage=$imagen;$imagen=$newImage;}
    }
    $uid=isset($_SESSION['id_usuario'])?(int)$_SESSION['id_usuario']:null;
    if($current){$stmt=$db->prepare('UPDATE sub_menu_historia_item SET titulo=?,contenido=?,imagen=?,imagen_alt=?,visible=?,actualizado_por=?,actualizado_en=NOW() WHERE id_historia_item=? AND id_sub_menu=2');$stmt->bind_param('ssssiii',$titulo,$contenido,$imagen,$alt,$visible,$uid,$idItem);}
    else{$res=$db->query('SELECT COALESCE(MAX(orden),0)+1 next_orden FROM sub_menu_historia_item WHERE id_sub_menu=2');$orden=(int)$res->fetch_assoc()['next_orden'];$stmt=$db->prepare('INSERT INTO sub_menu_historia_item(id_sub_menu,titulo,contenido,imagen,imagen_alt,visible,orden,creado_por,actualizado_por) VALUES(2,?,?,?,?,?,?,?,?)');$stmt->bind_param('ssssiiii',$titulo,$contenido,$imagen,$alt,$visible,$orden,$uid,$uid);}
    try {
        if(!$stmt->execute()){throw new RuntimeException('No se pudo guardar el hito en la base de datos.');}
        if(!$current){$idItem=(int)$db->insert_id;}
        $stmt->close();
    } catch (Throwable $error) {
        if(isset($stmt) && $stmt instanceof mysqli_stmt){$stmt->close();}
        if($newImage){cms_eliminar_archivo_seguro($newImage);}
        throw $error;
    }
    if($oldImage && !cms_submenu_file_is_referenced($db,$oldImage)){cms_eliminar_archivo_seguro($oldImage);}
    $stmt=$db->prepare('SELECT * FROM sub_menu_historia_item WHERE id_historia_item=? LIMIT 1');$stmt->bind_param('i',$idItem);$stmt->execute();$item=$stmt->get_result()->fetch_assoc()?:[];$stmt->close();return $item;
}

function cms_delete_submenu_history_item(mysqli $db,int $idItem): void
{
    if(!cms_table_exists($db,'sub_menu_historia_item')){throw new RuntimeException('La tabla de historia visual no está instalada.');}
    $stmt=$db->prepare('SELECT imagen FROM sub_menu_historia_item WHERE id_historia_item=? AND id_sub_menu=2 LIMIT 1');$stmt->bind_param('i',$idItem);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();if(!$row){throw new RuntimeException('El hito no existe.');}
    $stmt=$db->prepare('DELETE FROM sub_menu_historia_item WHERE id_historia_item=? AND id_sub_menu=2');$stmt->bind_param('i',$idItem);
    if(!$stmt->execute() || $stmt->affected_rows!==1){$stmt->close();throw new RuntimeException('No se pudo eliminar el hito de la base de datos.');}
    $stmt->close();
    if(!cms_submenu_file_is_referenced($db,(string)($row['imagen']??''))){cms_eliminar_archivo_seguro($row['imagen']??null);}
}

function cms_toggle_submenu_history_item(mysqli $db,int $idItem): int
{
    $stmt=$db->prepare('UPDATE sub_menu_historia_item SET visible=IF(visible=1,0,1),actualizado_en=NOW() WHERE id_historia_item=? AND id_sub_menu=2');$stmt->bind_param('i',$idItem);$stmt->execute();$stmt->close();$stmt=$db->prepare('SELECT visible FROM sub_menu_historia_item WHERE id_historia_item=? AND id_sub_menu=2');$stmt->bind_param('i',$idItem);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();if(!$row){throw new RuntimeException('El hito no existe.');}return(int)$row['visible'];
}

function cms_reorder_submenu_history_items(mysqli $db,array $ids): void
{
    $stmt=$db->prepare('UPDATE sub_menu_historia_item SET orden=? WHERE id_historia_item=? AND id_sub_menu=2');$order=1;foreach($ids as $id){$id=(int)$id;if($id<1)continue;$stmt->bind_param('ii',$order,$id);$stmt->execute();$order++;}$stmt->close();
}

function cms_migrate_submenu_secondary_image_to_gallery(mysqli $db, int $idSubMenu, string $imagePath): void
{
    cms_ensure_submenu_page_tables($db);
    $imagePath = trim($imagePath);
    if ($idSubMenu <= 0 || $imagePath === '') {
        return;
    }

    $stmtExists = $db->prepare('SELECT id_media FROM sub_menu_pagina_media WHERE id_sub_menu = ? AND archivo = ? LIMIT 1');
    if ($stmtExists) {
        $stmtExists->bind_param('is', $idSubMenu, $imagePath);
        $stmtExists->execute();
        $existsResult = $stmtExists->get_result();
        $exists = $existsResult ? $existsResult->fetch_assoc() : null;
        $stmtExists->close();
        if (!$exists) {
            $res = $db->query('SELECT COALESCE(MAX(orden), 0) + 1 AS next_orden FROM sub_menu_pagina_media WHERE id_sub_menu = ' . $idSubMenu);
            $orden = $res ? (int) $res->fetch_assoc()['next_orden'] : 1;
            $titulo = 'Imagen de galería';
            $stmtInsert = $db->prepare("INSERT INTO sub_menu_pagina_media (id_sub_menu, tipo, archivo, titulo, visible, orden) VALUES (?, 'imagen', ?, ?, 1, ?)");
            if ($stmtInsert) {
                $stmtInsert->bind_param('issi', $idSubMenu, $imagePath, $titulo, $orden);
                $stmtInsert->execute();
                $stmtInsert->close();
            }
        }
    }

    $stmtClear = $db->prepare('UPDATE sub_menu_paginas SET imagen_secundaria = NULL WHERE id_sub_menu = ? AND imagen_secundaria = ?');
    if ($stmtClear) {
        $stmtClear->bind_param('is', $idSubMenu, $imagePath);
        $stmtClear->execute();
        $stmtClear->close();
    }
}

function cms_reorder_submenu_page_media(mysqli $db, int $idSubMenu, array $ids): void
{
    cms_ensure_submenu_page_tables($db);
    $orden = 1;
    $stmt = $db->prepare('UPDATE sub_menu_pagina_media SET orden = ? WHERE id_media = ? AND id_sub_menu = ?');
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar el orden de la galería.');
    }

    foreach ($ids as $idMedia) {
        $idMedia = (int) $idMedia;
        if ($idMedia <= 0) {
            continue;
        }
        $stmt->bind_param('iii', $orden, $idMedia, $idSubMenu);
        $stmt->execute();
        $orden++;
    }
    $stmt->close();
}

function cms_get_public_submenu_page(mysqli $db, int $idSubMenu): ?array
{
    cms_ensure_submenu_page_tables($db);
    $stmt = $db->prepare("SELECT sm.*, m.nombre AS menu_padre, m.id_menu,
                                 sp.titulo AS pagina_titulo, sp.bajada AS pagina_bajada,
                                 sp.contenido AS pagina_contenido,
                                 sp.imagen_hero AS pagina_imagen_hero,
                                 sp.hero_video_url AS pagina_hero_video_url,
                                 sp.hero_video_archivo AS pagina_hero_video_archivo,
                                 sp.imagen_secundaria AS pagina_imagen_secundaria,
                                 sp.video_url AS pagina_video_url,
                                 sp.video_archivo AS pagina_video_archivo,
                                 sp.boton_texto AS pagina_boton_texto,
                                 sp.boton_url AS pagina_boton_url,
                                 sp.meta_title AS pagina_meta_title,
                                 sp.meta_description AS pagina_meta_description
                          FROM sub_menus sm
                          INNER JOIN menus m ON m.id_menu = sm.id_menu
                          LEFT JOIN sub_menu_paginas sp ON sp.id_sub_menu = sm.id_sub_menu
                          WHERE sm.id_sub_menu = ? AND sm.estado = 1 LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $idSubMenu);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    if (!$row) {
        return null;
    }
    cms_migrate_submenu_secondary_image_to_gallery($db, $idSubMenu, (string) ($row['pagina_imagen_secundaria'] ?? ''));
    $row['pagina_imagen_secundaria'] = '';
    $row['pagina_media'] = cms_list_submenu_page_media($db, $idSubMenu);
    return cms_decode_submenu_editor_row($row);
}

function cms_list_sibling_submenus(mysqli $db, int $idMenu): array
{
    $stmt = $db->prepare('SELECT * FROM sub_menus WHERE id_menu = ? AND estado = 1 ORDER BY orden ASC, id_sub_menu ASC');
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('i', $idMenu);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    foreach ($rows as &$row) {
        $row['url_publica'] = cms_submenu_public_url($row);
    }
    unset($row);
    return $rows;
}

function cms_save_submenu_page(mysqli $db, int $idSubMenu, array $post): void
{
    cms_ensure_submenu_page_tables($db);
    $current = null;
    $stmtCurrent = $db->prepare('SELECT * FROM sub_menu_paginas WHERE id_sub_menu = ? LIMIT 1');
    if ($stmtCurrent) {
        $stmtCurrent->bind_param('i', $idSubMenu);
        $stmtCurrent->execute();
        $result = $stmtCurrent->get_result();
        $current = $result ? $result->fetch_assoc() : null;
        $stmtCurrent->close();
    }

    $folder = 'submenus/' . $idSubMenu;
    $heroTipo = ($post['pagina_hero_tipo'] ?? '') === 'video' ? 'video' : 'imagen';
    $imagenHero = $heroTipo === 'imagen'
        ? cms_upload_image('pagina_imagen_hero', $folder, $current['imagen_hero'] ?? null)
        : ($current['imagen_hero'] ?? null);
    $heroVideoArchivo = $heroTipo === 'video'
        ? cms_upload_file('pagina_hero_video_archivo', $folder, ['mp4', 'webm', 'mov', 'm4v'], $current['hero_video_archivo'] ?? null)
        : ($current['hero_video_archivo'] ?? null);
    cms_migrate_submenu_secondary_image_to_gallery($db, $idSubMenu, (string) ($current['imagen_secundaria'] ?? ''));
    $imagenSecundaria = null;
    $videoArchivo = null;

    $titulo = trim(cms_decode_editor_entities((string) ($post['pagina_titulo'] ?? '')));
    $bajada = trim(cms_decode_editor_entities((string) ($post['pagina_bajada'] ?? '')));
    $contenido = trim(cms_decode_editor_entities((string) ($post['pagina_contenido'] ?? '')));
    $heroVideoUrl = trim((string) ($post['pagina_hero_video_url'] ?? ''));
    $videoUrl = '';
    $botonTexto = trim(cms_decode_editor_entities((string) ($post['pagina_boton_texto'] ?? '')));
    $botonUrl = trim((string) ($post['pagina_boton_url'] ?? ''));
    $metaTitle = trim(cms_decode_editor_entities((string) ($post['pagina_meta_title'] ?? '')));
    $metaDescription = trim(cms_decode_editor_entities((string) ($post['pagina_meta_description'] ?? '')));
    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;
    if (!empty($post['delete_pagina_imagen_hero'])) {
        $imagenHero = null;
    }

    if (!empty($post['delete_pagina_hero_video'])) {
        $heroVideoUrl = '';
        $heroVideoArchivo = null;
    }

    if ($heroTipo === 'video') {
        $imagenHero = null;
    } else {
        $heroVideoUrl = '';
        $heroVideoArchivo = null;
    }

    if ($current) {
        $stmt = $db->prepare('UPDATE sub_menu_paginas
            SET titulo = ?, bajada = ?, contenido = ?, imagen_hero = ?, hero_video_url = ?, hero_video_archivo = ?, imagen_secundaria = ?,
                video_url = ?, video_archivo = ?, boton_texto = ?, boton_url = ?,
                meta_title = ?, meta_description = ?, actualizado_en = NOW(), actualizado_por = ?
            WHERE id_sub_menu = ?');
        $stmt->bind_param(
            'sssssssssssssii',
            $titulo,
            $bajada,
            $contenido,
            $imagenHero,
            $heroVideoUrl,
            $heroVideoArchivo,
            $imagenSecundaria,
            $videoUrl,
            $videoArchivo,
            $botonTexto,
            $botonUrl,
            $metaTitle,
            $metaDescription,
            $idUsuario,
            $idSubMenu
        );
    } else {
        $stmt = $db->prepare('INSERT INTO sub_menu_paginas
            (id_sub_menu, titulo, bajada, contenido, imagen_hero, hero_video_url, hero_video_archivo, imagen_secundaria, video_url, video_archivo,
             boton_texto, boton_url, meta_title, meta_description, actualizado_en, actualizado_por)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)');
        $stmt->bind_param(
            'isssssssssssssi',
            $idSubMenu,
            $titulo,
            $bajada,
            $contenido,
            $imagenHero,
            $heroVideoUrl,
            $heroVideoArchivo,
            $imagenSecundaria,
            $videoUrl,
            $videoArchivo,
            $botonTexto,
            $botonUrl,
            $metaTitle,
            $metaDescription,
            $idUsuario
        );
    }
    $oldPaths = array_filter([
        (string) ($current['imagen_hero'] ?? ''),
        (string) ($current['hero_video_archivo'] ?? ''),
    ]);
    $newUploadedPaths = [];
    foreach ([$imagenHero, $heroVideoArchivo] as $candidate) {
        if ($candidate && !in_array($candidate, $oldPaths, true)) { $newUploadedPaths[] = $candidate; }
    }
    try {
        if (!$stmt->execute()) { throw new RuntimeException('No se pudo guardar la página del submenú.'); }
        $stmt->close();
    } catch (Throwable $error) {
        if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
        foreach ($newUploadedPaths as $newPath) { cms_eliminar_archivo_seguro($newPath); }
        throw $error;
    }

    cms_save_submenu_page_media($db, $idSubMenu, $post, $folder);

    $keptPaths = array_filter([(string) $imagenHero, (string) $heroVideoArchivo]);
    foreach ($oldPaths as $oldPath) {
        if (!in_array($oldPath, $keptPaths, true) && !cms_submenu_file_is_referenced($db, $oldPath)) {
            cms_eliminar_archivo_seguro($oldPath);
        }
    }
}

function cms_save_submenu_page_media(mysqli $db, int $idSubMenu, array $post, string $folder): void
{
    $mediaTitles = is_array($post['media_titles'] ?? null) ? $post['media_titles'] : [];
    $mediaVisible = is_array($post['media_visible'] ?? null) ? $post['media_visible'] : [];
    $replaceFiles = $_FILES['replace_media'] ?? null;

    foreach ($mediaTitles as $idMediaRaw => $title) {
        $idMedia = (int) $idMediaRaw;
        if ($idMedia <= 0) {
            continue;
        }

        $currentArchivo = null;
        $stmtCurrent = $db->prepare('SELECT archivo FROM sub_menu_pagina_media WHERE id_media = ? AND id_sub_menu = ? LIMIT 1');
        if ($stmtCurrent) {
            $stmtCurrent->bind_param('ii', $idMedia, $idSubMenu);
            $stmtCurrent->execute();
            $result = $stmtCurrent->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $currentArchivo = $row['archivo'] ?? null;
            $stmtCurrent->close();
        }

        $newArchivo = $currentArchivo;
        if (is_array($replaceFiles) && isset($replaceFiles['error'][$idMedia])) {
            $payload = [
                'name' => $replaceFiles['name'][$idMedia] ?? '',
                'type' => $replaceFiles['type'][$idMedia] ?? '',
                'tmp_name' => $replaceFiles['tmp_name'][$idMedia] ?? '',
                'error' => $replaceFiles['error'][$idMedia] ?? UPLOAD_ERR_NO_FILE,
                'size' => $replaceFiles['size'][$idMedia] ?? 0,
            ];
            $newArchivo = cms_upload_image_payload($payload, $folder, $currentArchivo);
        }

        $titulo = trim((string) $title);
        $visible = isset($mediaVisible[$idMedia]) ? 1 : 0;
        $stmtUpdate = $db->prepare('UPDATE sub_menu_pagina_media SET titulo = ?, archivo = ?, visible = ? WHERE id_media = ? AND id_sub_menu = ?');
        if ($stmtUpdate) {
            $stmtUpdate->bind_param('ssiii', $titulo, $newArchivo, $visible, $idMedia, $idSubMenu);
            if (!$stmtUpdate->execute()) {
                $stmtUpdate->close();
                if ($newArchivo && $newArchivo !== $currentArchivo) { cms_eliminar_archivo_seguro($newArchivo); }
                throw new RuntimeException('No se pudo actualizar la imagen de galería.');
            }
            $stmtUpdate->close();
            if ($currentArchivo && $newArchivo !== $currentArchivo
                && !cms_submenu_file_is_referenced($db, $currentArchivo)) {
                cms_eliminar_archivo_seguro($currentArchivo);
            }
        }
    }

    $deleteIds = array_map('intval', $post['delete_media'] ?? []);
    foreach ($deleteIds as $idMedia) {
        if ($idMedia <= 0) {
            continue;
        }
        cms_delete_submenu_gallery_media($db, $idSubMenu, $idMedia);
    }

    $galleryImage = cms_upload_image('pagina_galeria_imagen', $folder, null);
    if ($galleryImage) {
        $res = $db->query('SELECT COALESCE(MAX(orden), 0) + 1 AS next_orden FROM sub_menu_pagina_media WHERE id_sub_menu = ' . $idSubMenu);
        $orden = $res ? (int) $res->fetch_assoc()['next_orden'] : 1;
        $titulo = trim((string) ($post['pagina_galeria_titulo'] ?? ''));
        $stmt = $db->prepare("INSERT INTO sub_menu_pagina_media (id_sub_menu, tipo, archivo, titulo, visible, orden) VALUES (?, 'imagen', ?, ?, 1, ?)");
        if ($stmt) {
            $stmt->bind_param('issi', $idSubMenu, $galleryImage, $titulo, $orden);
            if (!$stmt->execute()) {
                $stmt->close();
                cms_eliminar_archivo_seguro($galleryImage);
                throw new RuntimeException('No se pudo registrar la imagen de galería.');
            }
            $stmt->close();
        } else {
            cms_eliminar_archivo_seguro($galleryImage);
            throw new RuntimeException('No se pudo preparar el registro de la imagen de galería.');
        }
    }
}

function cms_menu_icon_class(string $name, ?string $icon): string
{
    $icon = trim((string) $icon);
    if ($icon !== '') {
        return $icon;
    }

    $fallback = [
        'Inicio' => 'bi bi-house-door',
        'Institucional' => 'bi bi-building',
        'Maternal' => 'bi bi-balloon-heart',
        'Inicial' => 'bi bi-palette',
        'Primaria' => 'bi bi-book',
        '3er Ciclo EBI' => 'bi bi-journal-text',
        'Bachillerato' => 'bi bi-mortarboard',
        'Libre Asistido' => 'bi bi-people',
        'Confesionalidad' => 'bi bi-cross',
        'Biblioteca' => 'bi bi-book-half',
        'Mi San Pablo' => 'bi bi-star',
    ];

    return $fallback[trim($name)] ?? 'bi bi-list-nested';
}

function cms_menu_display_url(?string $name, ?string $url): string
{
    $name = trim((string) $name);
    $url = trim((string) $url);
    if (strcasecmp($name, 'Inicio') === 0) {
        return '/';
    }

    return $url !== '' ? $url : '#';
}

// Alias historico: la generacion de nombres seguros vive ahora en includes/upload_helpers.php.
function cms_normalize_filename(string $name): string
{
    return cms_generar_nombre_seguro($name);
}

function cms_upload_image(string $fieldName, string $folder, ?string $current = null): ?string
{
    if (empty($_FILES[$fieldName]) || !isset($_FILES[$fieldName]['error'])) {
        return $current;
    }

    if ((int) $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $current;
    }

    return cms_guardar_archivo($_FILES[$fieldName], $folder, 'imagenes') ?? $current;
}

function cms_upload_image_payload(array $file, string $folder, ?string $current = null): ?string
{
    if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $current;
    }

    return cms_guardar_archivo($file, $folder, 'imagenes') ?? $current;
}

function cms_upload_file(string $fieldName, string $folder, array $allowedExtensions, ?string $current = null): ?string
{
    if (empty($_FILES[$fieldName]) || !isset($_FILES[$fieldName]['error'])) {
        return $current;
    }

    if ((int) $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $current;
    }

    // Los campos de video declaran su whitelist como extensiones de video puro; el resto
    // (PDF, Office, imagenes de adjuntos, etc.) se trata como adjunto generico.
    $esVideo = !array_diff(array_map('strtolower', $allowedExtensions), ['mp4', 'webm', 'mov', 'm4v']);

    $ruta = $esVideo
        ? cms_guardar_archivo($_FILES[$fieldName], $folder, 'videos')
        : cms_guardar_archivo($_FILES[$fieldName], $folder, 'adjuntos', $allowedExtensions);

    return $ruta ?? $current;
}

function cms_get_table_columns(mysqli $db, string $table): array
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return [];
    }

    $columns = [];
    $result = $db->query('SHOW COLUMNS FROM `' . $table . '`');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = $row;
        }
        $result->free();
    }

    return $columns;
}

function cms_youtube_embed_url(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    $parts = parse_url($url);
    if (!$parts || empty($parts['host'])) {
        return '';
    }

    $host = strtolower((string) $parts['host']);
    $path = trim((string) ($parts['path'] ?? ''), '/');
    $videoId = '';

    if (str_contains($host, 'youtu.be')) {
        $videoId = strtok($path, '/');
    } elseif (str_contains($host, 'youtube.com')) {
        if ($path === 'watch') {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $videoId = (string) ($query['v'] ?? '');
        } elseif (preg_match('~^(embed|shorts)/([^/?#]+)~', $path, $matches)) {
            $videoId = $matches[2];
        }
    }

    $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $videoId);
    if ($videoId === '') {
        return '';
    }

    return 'https://www.youtube.com/embed/' . $videoId;
}

function cms_basic_content_html(?string $html): string
{
    $html = (string) $html;
    if (trim($html) === '') {
        return '';
    }

    $allowed = '<p><br><strong><b><em><i><u><span><ul><ol><li><a><blockquote>';
    $html = strip_tags($html, $allowed);
    $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
    $html = preg_replace('/href\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', 'href="#"', $html) ?? $html;
    $html = preg_replace_callback('/\sstyle\s*=\s*("|\')([^"\']*)\1/i', static function (array $match): string {
        $style = (string) ($match[2] ?? '');
        if (preg_match('/(?:^|;)\s*color\s*:\s*(#[0-9a-f]{3,6}|rgb\([^)]+\)|[a-z]+)\s*(?:;|$)/i', $style, $colorMatch)) {
            return ' style="color:' . cms_e($colorMatch[1]) . ';"';
        }
        return '';
    }, $html) ?? $html;

    if (strip_tags($html) === $html) {
        $paragraphs = array_values(array_filter(preg_split('/\R{2,}/', trim($html)) ?: []));
        if (!$paragraphs) {
            return nl2br(cms_e($html));
        }
        return implode('', array_map(static fn(string $paragraph): string => '<p>' . nl2br(cms_e(trim($paragraph))) . '</p>', $paragraphs));
    }

    return $html;
}

function cms_ensure_event_media_table(mysqli $db): void
{
    $sql = "CREATE TABLE IF NOT EXISTS evento_media (
        id_media int(11) NOT NULL AUTO_INCREMENT,
        id_evento int(11) NOT NULL,
        tipo enum('imagen','video','youtube') NOT NULL DEFAULT 'imagen',
        archivo varchar(255) DEFAULT NULL,
        url varchar(500) DEFAULT NULL,
        titulo varchar(180) DEFAULT NULL,
        descripcion text DEFAULT NULL,
        portada tinyint(1) NOT NULL DEFAULT 0,
        visible tinyint(1) NOT NULL DEFAULT 1,
        orden int(11) NOT NULL DEFAULT 0,
        creado_en timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (id_media),
        KEY idx_evento_media_evento (id_evento),
        CONSTRAINT fk_evento_media_evento FOREIGN KEY (id_evento) REFERENCES eventos (id_evento) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $db->query($sql);
}

function cms_list_event_media(mysqli $db, int $idEvento, bool $onlyVisible = true): array
{
    cms_ensure_event_media_table($db);
    $where = 'id_evento = ?';
    if ($onlyVisible) {
        $where .= ' AND visible = 1';
    }
    $stmt = $db->prepare("SELECT * FROM evento_media WHERE {$where} ORDER BY portada DESC, orden ASC, id_media ASC");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('i', $idEvento);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function cms_upload_event_media_file(array $file, int $idEvento, string $tipo): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $folder = 'eventos/media/' . $idEvento;

    return $tipo === 'video'
        ? cms_guardar_archivo($file, $folder, 'videos')
        : cms_guardar_archivo($file, $folder, 'imagenes');
}

function cms_save_event_media_uploads(mysqli $db, int $idEvento): void
{
    cms_ensure_event_media_table($db);
    foreach (['event_media_images' => 'imagen', 'event_media_videos' => 'video'] as $field => $tipo) {
        if (empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) {
            continue;
        }
        foreach ($_FILES[$field]['name'] as $index => $name) {
            $file = [
                'name' => $name,
                'tmp_name' => $_FILES[$field]['tmp_name'][$index] ?? '',
                'error' => $_FILES[$field]['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            ];
            $path = cms_upload_event_media_file($file, $idEvento, $tipo);
            if ($path === null) {
                continue;
            }
            $titulo = pathinfo((string) $name, PATHINFO_FILENAME);
            $orden = (int) (time() % 100000);
            $stmt = $db->prepare('INSERT INTO evento_media (id_evento, tipo, archivo, titulo, visible, orden) VALUES (?, ?, ?, ?, 1, ?)');
            $stmt->bind_param('isssi', $idEvento, $tipo, $path, $titulo, $orden);
            $stmt->execute();
            $stmt->close();
        }
    }

    $youtubeUrls = $_POST['event_media_youtube_url'] ?? [];
    $youtubeTitles = $_POST['event_media_youtube_title'] ?? [];
    if (is_array($youtubeUrls)) {
        foreach ($youtubeUrls as $index => $url) {
            $url = trim((string) $url);
            if ($url === '') {
                continue;
            }
            $titulo = trim((string) ($youtubeTitles[$index] ?? 'Video YouTube'));
            $orden = (int) (time() % 100000);
            $stmt = $db->prepare("INSERT INTO evento_media (id_evento, tipo, url, titulo, visible, orden) VALUES (?, 'youtube', ?, ?, 1, ?)");
            $stmt->bind_param('issi', $idEvento, $url, $titulo, $orden);
            $stmt->execute();
            $stmt->close();
        }
    }
}

function cms_toggle_event_media_visible(mysqli $db, int $idMedia): void
{
    cms_ensure_event_media_table($db);
    $stmt = $db->prepare('UPDATE evento_media SET visible = IF(visible = 1, 0, 1) WHERE id_media = ?');
    $stmt->bind_param('i', $idMedia);
    $stmt->execute();
    $stmt->close();
}

function cms_delete_event_media(mysqli $db, int $idMedia): void
{
    cms_ensure_event_media_table($db);
    $stmt = $db->prepare('SELECT archivo FROM evento_media WHERE id_media = ? LIMIT 1');
    $stmt->bind_param('i', $idMedia);
    $stmt->execute();
    $result = $stmt->get_result();
    $media = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    $stmt = $db->prepare('DELETE FROM evento_media WHERE id_media = ?');
    $stmt->bind_param('i', $idMedia);
    $stmt->execute();
    $stmt->close();

    $archivo = trim((string) ($media['archivo'] ?? ''));
    if ($archivo !== '' && !preg_match('/^https?:\/\//i', $archivo)) {
        $absolutePath = dirname(__DIR__) . '/' . ltrim($archivo, '/');
        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }
}

function cms_bind_params(mysqli_stmt $stmt, string $types, array &$values): void
{
    $refs = [];
    foreach ($values as $key => $value) {
        $refs[$key] = &$values[$key];
    }
    $stmt->bind_param($types, ...$refs);
}

function cms_event_id_column(array $columns): string
{
    if (isset($columns['id_evento'])) {
        return 'id_evento';
    }

    return isset($columns['id']) ? 'id' : 'id_evento';
}

function cms_event_category_color(string $category): string
{
    $key = strtolower(strtr(trim($category), ['Á' => 'á', 'É' => 'é', 'Í' => 'í', 'Ó' => 'ó', 'Ú' => 'ú', 'Ñ' => 'ñ']));
    $colors = [
        'pastoral' => '#8e44ad',
        'academico' => '#0d6efd',
        'académico' => '#0d6efd',
        'deportivo' => '#198754',
        'institucional' => '#fd7e14',
    ];

    return $colors[$key] ?? '#fd7e14';
}

function cms_normalize_event_payload(array $post): array
{
    $title = trim((string) ($post['titulo'] ?? ''));
    $startDate = trim((string) ($post['fecha_inicio'] ?? ''));
    if ($title === '' || $startDate === '') {
        throw new RuntimeException('El título y la fecha de inicio son obligatorios.');
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        throw new RuntimeException('La fecha de inicio debe tener formato yyyy-mm-dd.');
    }

    $endDate = trim((string) ($post['fecha_termino'] ?? ''));
    if ($endDate === '') {
        $endDate = $startDate;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        throw new RuntimeException('La fecha de término debe tener formato yyyy-mm-dd.');
    }

    $startTime = trim((string) ($post['hora_inicio'] ?? ''));
    $endTime = trim((string) ($post['hora_termino'] ?? ''));
    foreach (['hora_inicio' => $startTime, 'hora_termino' => $endTime] as $field => $time) {
        if ($time !== '' && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
            throw new RuntimeException('El campo ' . $field . ' debe tener formato HH:mm.');
        }
    }

    $state = trim((string) ($post['estado'] ?? ''));
    $state = $state !== '' ? $state : 'publicado';
    $allowedStates = ['borrador', 'publicado', 'oculto', 'cancelado'];
    if (!in_array($state, $allowedStates, true)) {
        throw new RuntimeException('Estado no permitido para el evento.');
    }

    $category = trim((string) ($post['categoria'] ?? ''));
    $color = trim((string) ($post['color'] ?? ''));
    if ($color === '') {
        $color = cms_event_category_color($category);
    }

    return [
        'titulo' => $title,
        'descripcion_corta' => trim((string) ($post['descripcion_corta'] ?? '')),
        'descripcion' => trim((string) ($post['descripcion'] ?? '')),
        'fecha_inicio' => $startDate,
        'fecha_termino' => $endDate,
        'hora_inicio' => $startTime !== '' ? $startTime : null,
        'hora_termino' => $endTime !== '' ? $endTime : null,
        'categoria' => $category,
        'ubicacion' => trim((string) ($post['ubicacion'] ?? '')),
        'color' => $color,
        'destacado' => !empty($post['destacado']) ? 1 : 0,
        'visible' => isset($post['visible']) ? (int) (bool) $post['visible'] : 1,
        'estado' => $state,
        'orden' => max(0, (int) ($post['orden'] ?? 0)),
    ];
}

function cms_event_duplicate_exists(mysqli $db, array $payload, int $excludeId = 0): bool
{
    $columns = cms_get_table_columns($db, 'eventos');
    if (!$columns || !isset($columns['titulo'], $columns['fecha_inicio'])) {
        return false;
    }

    $idColumn = cms_event_id_column($columns);
    $sql = "SELECT `$idColumn` FROM eventos WHERE titulo = ? AND fecha_inicio = ?";
    $types = 'ss';
    $values = [$payload['titulo'], $payload['fecha_inicio']];

    if (isset($columns['hora_inicio'])) {
        if ($payload['hora_inicio'] === null || $payload['hora_inicio'] === '') {
            $sql .= ' AND (hora_inicio IS NULL OR hora_inicio = \'\')';
        } else {
            $sql .= ' AND hora_inicio = ?';
            $types .= 's';
            $values[] = $payload['hora_inicio'];
        }
    }

    if ($excludeId > 0 && isset($columns[$idColumn])) {
        $sql .= " AND `$idColumn` <> ?";
        $types .= 'i';
        $values[] = $excludeId;
    }

    $sql .= ' LIMIT 1';
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return false;
    }
    cms_bind_params($stmt, $types, $values);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}

function cms_save_event(mysqli $db, array $post): int
{
    $columns = cms_get_table_columns($db, 'eventos');
    if (!$columns) {
        throw new RuntimeException('No se pudieron leer las columnas de la tabla eventos en la base de datos activa.');
    }

    $idColumn = cms_event_id_column($columns);
    $idEvento = (int) ($post['id_evento'] ?? 0);
    $payload = cms_normalize_event_payload($post);

    if (cms_event_duplicate_exists($db, $payload, $idEvento)) {
        throw new RuntimeException('Ya existe un evento con el mismo título, fecha y hora.');
    }

    $current = $idEvento > 0 ? cms_get_event($db, $idEvento) : null;
    if (isset($columns['imagen'])) {
        $payload['imagen'] = cms_upload_image('imagen', 'eventos', $current['imagen'] ?? null);
    } elseif (isset($columns['imagen_principal'])) {
        $payload['imagen_principal'] = cms_upload_image('imagen', 'eventos', $current['imagen_principal'] ?? null);
    }

    if (isset($columns['archivo_adjunto'])) {
        $payload['archivo_adjunto'] = cms_upload_file('archivo_adjunto', 'eventos/adjuntos', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'webp'], $current['archivo_adjunto'] ?? null);
    }

    if (isset($columns['id_institucion']) && !isset($payload['id_institucion'])) {
        $payload['id_institucion'] = cms_get_institution_id($db);
    }

    $data = [];
    foreach ($payload as $key => $value) {
        if (isset($columns[$key]) && $key !== $idColumn) {
            $data[$key] = $value;
        }
    }

    if (!$data) {
        throw new RuntimeException('La tabla eventos no tiene columnas compatibles para guardar.');
    }

    if ($idEvento > 0) {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "`$column` = ?";
        }
        $sql = 'UPDATE eventos SET ' . implode(', ', $sets) . " WHERE `$idColumn` = ?";
        $stmt = $db->prepare($sql);
        $types = str_repeat('s', count($data)) . 'i';
        $values = array_values($data);
        $values[] = $idEvento;
        cms_bind_params($stmt, $types, $values);
        $stmt->execute();
        $stmt->close();
        return $idEvento;
    }

    $columnsSql = '`' . implode('`, `', array_keys($data)) . '`';
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $stmt = $db->prepare("INSERT INTO eventos ($columnsSql) VALUES ($placeholders)");
    $types = str_repeat('s', count($data));
    $values = array_values($data);
    cms_bind_params($stmt, $types, $values);
    $stmt->execute();
    $newId = (int) $db->insert_id;
    $stmt->close();

    return $newId;
}

function cms_get_event(mysqli $db, int $idEvento): ?array
{
    $columns = cms_get_table_columns($db, 'eventos');
    if (!$columns) {
        return null;
    }

    $idColumn = cms_event_id_column($columns);
    $stmt = $db->prepare("SELECT * FROM eventos WHERE `$idColumn` = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $idEvento);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function cms_list_events(mysqli $db, int $limit = 200): array
{
    $columns = cms_get_table_columns($db, 'eventos');
    if (!$columns) {
        return [];
    }

    $order = [];
    if (isset($columns['fecha_inicio'])) {
        $order[] = 'fecha_inicio DESC';
    }
    if (isset($columns['hora_inicio'])) {
        $order[] = 'hora_inicio ASC';
    }
    if (isset($columns['orden'])) {
        $order[] = 'orden ASC';
    }
    $orderSql = $order ? implode(', ', $order) : cms_event_id_column($columns) . ' DESC';

    $limit = max(1, $limit);
    $result = $db->query('SELECT * FROM eventos ORDER BY ' . $orderSql . ' LIMIT ' . $limit);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function cms_list_public_events(mysqli $db, string $dateFrom, string $dateTo, int $limit = 40): array
{
    $columns = cms_get_table_columns($db, 'eventos');
    if (!$columns || !isset($columns['fecha_inicio'])) {
        return [];
    }

    $where = ['fecha_inicio BETWEEN ? AND ?'];
    $types = 'ss';
    $values = [$dateFrom, $dateTo];

    if (isset($columns['visible'])) {
        $where[] = 'visible = 1';
    }
    if (isset($columns['estado'])) {
        $where[] = "estado = 'publicado'";
    }

    $order = ['fecha_inicio ASC'];
    if (isset($columns['hora_inicio'])) {
        $order[] = 'hora_inicio ASC';
    }
    if (isset($columns['orden'])) {
        $order[] = 'orden ASC';
    }

    $sql = 'SELECT * FROM eventos WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . implode(', ', $order) . ' LIMIT ' . max(1, $limit);
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return [];
    }
    cms_bind_params($stmt, $types, $values);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function cms_toggle_event_visible(mysqli $db, int $idEvento): void
{
    $columns = cms_get_table_columns($db, 'eventos');
    if (!$columns || !isset($columns['visible'])) {
        throw new RuntimeException('La tabla eventos no tiene columna visible.');
    }
    $idColumn = cms_event_id_column($columns);
    $stmt = $db->prepare("UPDATE eventos SET visible = IF(visible = 1, 0, 1) WHERE `$idColumn` = ?");
    $stmt->bind_param('i', $idEvento);
    $stmt->execute();
    $stmt->close();
}

function cms_cancel_event(mysqli $db, int $idEvento): void
{
    $columns = cms_get_table_columns($db, 'eventos');
    if (!$columns) {
        throw new RuntimeException('La tabla eventos no existe.');
    }
    $idColumn = cms_event_id_column($columns);
    if (isset($columns['estado'])) {
        $stmt = $db->prepare("UPDATE eventos SET estado = 'cancelado' WHERE `$idColumn` = ?");
    } else {
        $stmt = $db->prepare("DELETE FROM eventos WHERE `$idColumn` = ?");
    }
    $stmt->bind_param('i', $idEvento);
    $stmt->execute();
    $stmt->close();
}

function cms_list_holidays(mysqli $db, int $limit = 500): array
{
    $columns = cms_get_table_columns($db, 'calendario');
    if (!$columns || !isset($columns['es_feriado'])) {
        return [];
    }

    $limit = max(1, $limit);
    $result = $db->query("SELECT * FROM calendario WHERE es_feriado = 1 AND visible = 1 ORDER BY fecha ASC LIMIT {$limit}");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Feriado publico por id_calendario o por fecha exacta (YYYY-MM-DD). Solo devuelve
// dias marcados como feriado y visibles; nunca expone dias comunes del calendario.
function cms_get_calendar_holiday(mysqli $db, int $idCalendario = 0, string $fecha = ''): ?array
{
    $columns = cms_get_table_columns($db, 'calendario');
    if (!$columns) {
        return null;
    }

    if ($idCalendario > 0) {
        $stmt = $db->prepare('SELECT * FROM calendario WHERE id_calendario = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $idCalendario);
    } elseif ($fecha !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $stmt = $db->prepare('SELECT * FROM calendario WHERE fecha = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('s', $fecha);
    } else {
        return null;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row || (int) ($row['es_feriado'] ?? 0) !== 1 || (int) ($row['visible'] ?? 1) !== 1) {
        return null;
    }

    return $row;
}

function cms_list_calendar_days(mysqli $db, string $dateFrom, string $dateTo): array
{
    $columns = cms_get_table_columns($db, 'calendario');
    if (!$columns) {
        return [];
    }

    $dateColumn = '';
    foreach (['fecha', 'fecha_calendario', 'fecha_dia', 'dia'] as $candidate) {
        if (isset($columns[$candidate])) {
            $dateColumn = $candidate;
            break;
        }
    }
    if ($dateColumn === '') {
        return [];
    }

    $stmt = $db->prepare("SELECT * FROM calendario WHERE `$dateColumn` BETWEEN ? AND ?");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('ss', $dateFrom, $dateTo);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[(string) $row[$dateColumn]] = $row;
        }
    }
    $stmt->close();
    return $rows;
}

function cms_toggle_section_visibility(mysqli $db, int $idSeccion): void
{
    cms_ensure_section_tracking_columns($db);
    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;
    $stmt = $db->prepare("UPDATE seccion SET visible = IF(visible = 'si', 'no', 'si'), actualizado_en = NOW(), actualizado_por = ? WHERE id_seccion = ?");
    $stmt->bind_param('ii', $idUsuario, $idSeccion);
    $stmt->execute();
    $stmt->close();
}

function cms_set_section_visibility(mysqli $db, int $idSeccion, string $visible): void
{
    cms_ensure_section_tracking_columns($db);
    $visible = $visible === 'si' ? 'si' : 'no';
    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;

    $stmt = $db->prepare('UPDATE seccion SET visible = ?, actualizado_en = NOW(), actualizado_por = ? WHERE id_seccion = ?');
    $stmt->bind_param('sii', $visible, $idUsuario, $idSeccion);
    $stmt->execute();
    $stmt->close();
}

function cms_save_section(mysqli $db, int $idSeccion, array $post): void
{
    cms_ensure_section_tracking_columns($db);
    $visible = (($post['visible'] ?? 'no') === 'si') ? 'si' : 'no';
    $orden = max(1, (int) ($post['orden'] ?? 1));
    $observacion = trim((string) ($post['observacion'] ?? ''));
    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;

    $stmt = $db->prepare('UPDATE seccion SET visible = ?, orden = ?, observacion = ?, actualizado_en = NOW(), actualizado_por = ? WHERE id_seccion = ?');
    $stmt->bind_param('sisii', $visible, $orden, $observacion, $idUsuario, $idSeccion);
    $stmt->execute();
    $stmt->close();

    $deleteStmt = $db->prepare('DELETE FROM seccion_config WHERE id_seccion = ?');
    $deleteStmt->bind_param('i', $idSeccion);
    $deleteStmt->execute();
    $deleteStmt->close();

    $keys = $post['config_key'] ?? [];
    $values = $post['config_value'] ?? [];
    $insertStmt = $db->prepare('INSERT INTO seccion_config (id_seccion, clave, valor) VALUES (?, ?, ?)');

    foreach ($keys as $index => $key) {
        $clave = trim((string) $key);
        $valor = trim((string) ($values[$index] ?? ''));
        if ($clave === '') {
            continue;
        }
        $insertStmt->bind_param('iss', $idSeccion, $clave, $valor);
        $insertStmt->execute();
    }

    $insertStmt->close();
}

function cms_save_item(mysqli $db, array $section, array $post): int
{
    $idSeccion = (int) $section['id_seccion'];
    $idItem = (int) ($post['id_item'] ?? 0);
    $itemActual = $idItem > 0 ? cms_get_item($db, $idItem) : null;

    $idCategoria = !empty($post['id_categoria']) ? (int) $post['id_categoria'] : null;
    $etiqueta = trim((string) ($post['etiqueta'] ?? ''));
    $titulo = trim((string) ($post['titulo'] ?? ''));
    $tituloLinea1 = trim((string) ($post['titulo_linea_1'] ?? ''));
    $tituloLinea2 = trim((string) ($post['titulo_linea_2'] ?? ''));
    $tituloLinea3 = trim((string) ($post['titulo_linea_3'] ?? ''));
    $subtitulo = trim((string) ($post['subtitulo'] ?? ''));
    $descripcion = trim((string) ($post['descripcion'] ?? ''));
    $boton1Texto = trim((string) ($post['boton_1_texto'] ?? ''));
    $boton1Url = trim((string) ($post['boton_1_url'] ?? ''));
    $boton2Texto = trim((string) ($post['boton_2_texto'] ?? ''));
    $boton2Url = trim((string) ($post['boton_2_url'] ?? ''));
    $url = trim((string) ($post['url'] ?? ''));
    $videoYoutube = trim((string) ($post['video_youtube'] ?? ''));
    $fechaPublicacion = trim((string) ($post['fecha_publicacion'] ?? ''));
    $visible = (($post['visible'] ?? 'no') === 'si') ? 'si' : 'no';
    $orden = max(1, (int) ($post['orden'] ?? 1));

    $folder = $section['tipo_seccion'] === 'news'
        ? 'noticias'
        : 'secciones/' . preg_replace('/[^a-z0-9_-]+/i', '-', $section['nombre_interno']);

    if (($section['nombre_interno'] ?? '') === 'video_destacado_home' || ($section['tipo_seccion'] ?? '') === 'video') {
        $youtubeUrl = trim((string) ($post['url'] ?? ''));
        $uploadedVideo = cms_upload_file('video_file', $folder, ['mp4', 'webm', 'mov', 'm4v'], null);
        $url = $youtubeUrl !== ''
            ? $youtubeUrl
            : ($uploadedVideo ?: (string) ($itemActual['url'] ?? ''));
        $titulo = trim((string) ($post['titulo'] ?? 'Video destacado'));
        $titulo = $titulo !== '' ? $titulo : 'Video destacado';

        if ($url === '') {
            throw new RuntimeException('Debes ingresar un enlace de YouTube o cargar un video.');
        }

        if ($idItem > 0) {
            $stmt = $db->prepare("UPDATE seccion_item
                SET id_categoria = NULL, etiqueta = 'video_destacado', titulo = ?, url = ?, visible = ?, orden = ?
                WHERE id_item = ? AND id_seccion = ?");
            $stmt->bind_param('sssiii', $titulo, $url, $visible, $orden, $idItem, $idSeccion);
        } else {
            $stmt = $db->prepare("INSERT INTO seccion_item
                (id_seccion, id_categoria, etiqueta, titulo, url, visible, orden)
                VALUES (?, NULL, 'video_destacado', ?, ?, ?, ?)");
            $stmt->bind_param('isssi', $idSeccion, $titulo, $url, $visible, $orden);
        }

        $stmt->execute();
        $newId = $idItem > 0 ? $idItem : (int) $db->insert_id;
        $stmt->close();
        return $newId;
    }

    if (($section['tipo_seccion'] ?? '') === 'news') {
        // La columna url no se usa para nada mas en noticias; se reutiliza para
        // guardar la ruta del video local subido cuando el tipo de video es "archivo".
        $videoTipo = ((string) ($post['video_tipo'] ?? 'youtube')) === 'archivo' ? 'archivo' : 'youtube';
        if ($videoTipo === 'archivo') {
            $videoYoutube = '';
            $url = (string) cms_upload_file('video_file', $folder, ['mp4', 'webm', 'mov', 'm4v'], (string) ($itemActual['url'] ?? ''));
        } else {
            $url = '';
        }
    }

    $clearImagen = isset($post['clear_imagen']) && (string) $post['clear_imagen'] === '1';
    $clearImagenMobile = isset($post['clear_imagen_mobile']) && (string) $post['clear_imagen_mobile'] === '1';

    $imagen = $clearImagen
        ? null
        : cms_upload_image('imagen', $folder, $itemActual['imagen'] ?? null);
    if (($section['tipo_seccion'] ?? '') === 'news') {
        $imagenMobile = $itemActual['imagen_mobile'] ?? null;
    } else {
        $imagenMobile = $clearImagenMobile
            ? null
            : cms_upload_image('imagen_mobile', $folder, $itemActual['imagen_mobile'] ?? null);
    }

    $fechaPublicacion = $fechaPublicacion !== '' ? $fechaPublicacion : null;

    if ($idItem > 0) {
        $sql = 'UPDATE seccion_item
                SET id_categoria = ?, etiqueta = ?, titulo = ?, titulo_linea_1 = ?, titulo_linea_2 = ?, titulo_linea_3 = ?,
                    subtitulo = ?, descripcion = ?, imagen = ?, imagen_mobile = ?, video_youtube = ?, boton_1_texto = ?, boton_1_url = ?,
                    boton_2_texto = ?, boton_2_url = ?, url = ?, fecha_publicacion = ?, visible = ?, orden = ?
                WHERE id_item = ? AND id_seccion = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            'isssssssssssssssssiii',
            $idCategoria,
            $etiqueta,
            $titulo,
            $tituloLinea1,
            $tituloLinea2,
            $tituloLinea3,
            $subtitulo,
            $descripcion,
            $imagen,
            $imagenMobile,
            $videoYoutube,
            $boton1Texto,
            $boton1Url,
            $boton2Texto,
            $boton2Url,
            $url,
            $fechaPublicacion,
            $visible,
            $orden,
            $idItem,
            $idSeccion
        );
    } else {
        $sql = 'INSERT INTO seccion_item
                (id_seccion, id_categoria, etiqueta, titulo, titulo_linea_1, titulo_linea_2, titulo_linea_3, subtitulo, descripcion, imagen, imagen_mobile, video_youtube, boton_1_texto, boton_1_url, boton_2_texto, boton_2_url, url, fecha_publicacion, visible, orden)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            'iisssssssssssssssssi',
            $idSeccion,
            $idCategoria,
            $etiqueta,
            $titulo,
            $tituloLinea1,
            $tituloLinea2,
            $tituloLinea3,
            $subtitulo,
            $descripcion,
            $imagen,
            $imagenMobile,
            $videoYoutube,
            $boton1Texto,
            $boton1Url,
            $boton2Texto,
            $boton2Url,
            $url,
            $fechaPublicacion,
            $visible,
            $orden
        );
    }

    $stmt->execute();
    $newId = $idItem > 0 ? $idItem : (int) $db->insert_id;
    $stmt->close();

    if (($section['tipo_seccion'] ?? '') === 'news') {
        cms_save_news_gallery_config($db, $idSeccion, $newId, $folder, $post);
    }

    return $newId;
}

function cms_news_gallery_config_key(int $idItem): string
{
    return 'noticia_galeria_' . $idItem;
}

function cms_get_news_gallery_config(mysqli $db, int $idSeccion, int $idItem): array
{
    if ($idSeccion <= 0 || $idItem <= 0) {
        return [];
    }

    $key = cms_news_gallery_config_key($idItem);
    $stmt = $db->prepare('SELECT valor FROM seccion_config WHERE id_seccion = ? AND clave = ? LIMIT 1');
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('is', $idSeccion, $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return cms_decode_news_gallery($row['valor'] ?? '');
}

function cms_save_news_gallery_config(mysqli $db, int $idSeccion, int $idItem, string $folder, array $post): void
{
    $currentGallery = cms_get_news_gallery_config($db, $idSeccion, $idItem);
    $currentJson = $currentGallery ? json_encode($currentGallery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
    $nextJson = cms_save_news_gallery_payload($folder, $currentJson, $post);
    $key = cms_news_gallery_config_key($idItem);

    $stmtDelete = $db->prepare('DELETE FROM seccion_config WHERE id_seccion = ? AND clave = ?');
    if ($stmtDelete) {
        $stmtDelete->bind_param('is', $idSeccion, $key);
        $stmtDelete->execute();
        $stmtDelete->close();
    }

    if ($nextJson === null || $nextJson === '') {
        return;
    }

    $stmtInsert = $db->prepare('INSERT INTO seccion_config (id_seccion, clave, valor) VALUES (?, ?, ?)');
    if ($stmtInsert) {
        $stmtInsert->bind_param('iss', $idSeccion, $key, $nextJson);
        $stmtInsert->execute();
        $stmtInsert->close();
    }
}

function cms_decode_news_gallery(?string $raw): array
{
    $raw = trim((string) $raw);
    if ($raw === '' || $raw[0] !== '[') {
        return [];
    }

    $items = json_decode($raw, true);
    if (!is_array($items)) {
        return [];
    }

    $gallery = [];
    foreach ($items as $index => $item) {
        if (!is_array($item) || empty($item['archivo'])) {
            continue;
        }
        $gallery[] = [
            'id' => preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($item['id'] ?? ('img_' . ($index + 1)))),
            'archivo' => (string) $item['archivo'],
            'titulo' => (string) ($item['titulo'] ?? ''),
            'visible' => !isset($item['visible']) || (int) $item['visible'] === 1 ? 1 : 0,
            'orden' => max(1, (int) ($item['orden'] ?? ($index + 1))),
        ];
    }

    usort($gallery, static fn(array $a, array $b): int => ($a['orden'] <=> $b['orden']) ?: strcmp($a['id'], $b['id']));
    return $gallery;
}

function cms_save_news_gallery_payload(string $folder, ?string $currentJson, array $post): ?string
{
    $gallery = cms_decode_news_gallery($currentJson);
    $deleteIds = array_map('strval', (array) ($post['delete_news_gallery'] ?? []));
    $titles = is_array($post['news_gallery_titles'] ?? null) ? $post['news_gallery_titles'] : [];
    $visible = is_array($post['news_gallery_visible'] ?? null) ? $post['news_gallery_visible'] : [];
    $order = is_array($post['news_gallery_order'] ?? null) ? $post['news_gallery_order'] : [];

    $nextGallery = [];
    foreach ($gallery as $index => $item) {
        $id = (string) $item['id'];
        if (in_array($id, $deleteIds, true)) {
            continue;
        }
        $item['titulo'] = trim((string) ($titles[$id] ?? $item['titulo']));
        $item['visible'] = isset($visible[$id]) ? 1 : 0;
        $item['orden'] = max(1, (int) ($order[$id] ?? ($index + 1)));
        $nextGallery[] = $item;
    }

    $files = $_FILES['news_gallery_images'] ?? null;
    if (is_array($files) && isset($files['error']) && is_array($files['error'])) {
        $nextOrder = count($nextGallery) + 1;
        foreach ($files['error'] as $index => $error) {
            if ((int) $error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $payload = [
                'name' => $files['name'][$index] ?? '',
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $error,
                'size' => $files['size'][$index] ?? 0,
            ];
            $path = cms_upload_image_payload($payload, $folder, null);
            if ($path) {
                $nextGallery[] = [
                    'id' => 'img_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)),
                    'archivo' => $path,
                    'titulo' => '',
                    'visible' => 1,
                    'orden' => $nextOrder,
                ];
                $nextOrder++;
            }
        }
    }

    usort($nextGallery, static fn(array $a, array $b): int => ($a['orden'] <=> $b['orden']) ?: strcmp($a['id'], $b['id']));
    $normalized = [];
    foreach (array_values($nextGallery) as $index => $item) {
        $item['orden'] = $index + 1;
        $normalized[] = $item;
    }

    return $normalized ? json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
}

function cms_delete_item(mysqli $db, int $idItem): void
{
    $item = cms_get_item($db, $idItem);
    if ($item && (int) ($item['id_seccion'] ?? 0) > 0) {
        $key = cms_news_gallery_config_key($idItem);
        $idSeccion = (int) $item['id_seccion'];
        $stmtConfig = $db->prepare('DELETE FROM seccion_config WHERE id_seccion = ? AND clave = ?');
        if ($stmtConfig) {
            $stmtConfig->bind_param('is', $idSeccion, $key);
            $stmtConfig->execute();
            $stmtConfig->close();
        }
    }

    $stmt = $db->prepare('DELETE FROM seccion_item WHERE id_item = ?');
    $stmt->bind_param('i', $idItem);
    $stmt->execute();
    $stmt->close();
}

function cms_save_menu(mysqli $db, array $post): int
{
    $idMenu = (int) ($post['id_menu'] ?? 0);
    $nombre = trim((string) ($post['nombre'] ?? ''));
    $url = trim((string) ($post['url'] ?? ''));
    $icono = trim((string) ($post['icono'] ?? ''));
    $estado = isset($post['estado']) ? 1 : 0;
    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;

    if ($nombre === '') {
        throw new RuntimeException('El nombre del menu es obligatorio.');
    }

    if ($idMenu > 0) {
        $stmt = $db->prepare('UPDATE menus SET nombre = ?, url = ?, icono = ?, estado = ?, actualizado_en = NOW(), actualizado_por = ? WHERE id_menu = ?');
        $stmt->bind_param('sssiii', $nombre, $url, $icono, $estado, $idUsuario, $idMenu);
    } else {
        $res = $db->query('SELECT COALESCE(MAX(orden), 0) + 1 AS next_orden FROM menus');
        $orden = $res ? (int) $res->fetch_assoc()['next_orden'] : 1;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $db->prepare('INSERT INTO menus (nombre, url, icono, orden, estado, fecha_creacion, hora_creacion, ip_creacion, actualizado_en, actualizado_por) VALUES (?, ?, ?, ?, ?, CURDATE(), CURTIME(), ?, NOW(), ?)');
        $stmt->bind_param('sssiisi', $nombre, $url, $icono, $orden, $estado, $ip, $idUsuario);
    }

    $stmt->execute();
    $savedId = $idMenu > 0 ? $idMenu : (int) $db->insert_id;
    $stmt->close();
    return $savedId;
}

function cms_toggle_menu(mysqli $db, int $idMenu): void
{
    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;
    $stmt = $db->prepare('UPDATE menus SET estado = IF(estado = 1, 0, 1), actualizado_en = NOW(), actualizado_por = ? WHERE id_menu = ?');
    $stmt->bind_param('ii', $idUsuario, $idMenu);
    $stmt->execute();
    $stmt->close();
}

function cms_delete_menu(mysqli $db, int $idMenu): void
{
    if ($idMenu <= 0) {
        throw new RuntimeException('Menú no válido para eliminar.');
    }

    $stmtSubs = $db->prepare('DELETE FROM sub_menus WHERE id_menu = ?');
    $stmtSubs->bind_param('i', $idMenu);
    $stmtSubs->execute();
    $stmtSubs->close();

    $stmt = $db->prepare('DELETE FROM menus WHERE id_menu = ?');
    $stmt->bind_param('i', $idMenu);
    $stmt->execute();
    $stmt->close();
}

function cms_save_submenu(mysqli $db, array $post): int
{
    cms_ensure_submenu_page_tables($db);
    $idSubMenu = (int) ($post['id_sub_menu'] ?? 0);
    $idMenu = (int) ($post['id_menu'] ?? 0);
    $nombre = trim((string) ($post['nombre'] ?? ''));
    $url = trim((string) ($post['url'] ?? ''));
    $icono = trim((string) ($post['icono'] ?? ''));
    $estado = isset($post['estado']) ? 1 : 0;
    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;

    if ($idMenu < 1 || $nombre === '') {
        throw new RuntimeException('El submenu debe tener menu padre y nombre.');
    }

    if ($idSubMenu > 0) {
        if ($icono === null) {
            $stmt = $db->prepare('UPDATE sub_menus SET id_menu = ?, nombre = ?, url = ?, estado = ?, actualizado_en = NOW(), actualizado_por = ? WHERE id_sub_menu = ?');
            $stmt->bind_param('issiii', $idMenu, $nombre, $url, $estado, $idUsuario, $idSubMenu);
        } else {
            $stmt = $db->prepare('UPDATE sub_menus SET id_menu = ?, nombre = ?, url = ?, icono = ?, estado = ?, actualizado_en = NOW(), actualizado_por = ? WHERE id_sub_menu = ?');
            $stmt->bind_param('isssiii', $idMenu, $nombre, $url, $icono, $estado, $idUsuario, $idSubMenu);
        }
    } else {
        $res = $db->query('SELECT COALESCE(MAX(orden), 0) + 1 AS next_orden FROM sub_menus WHERE id_menu = ' . $idMenu);
        $orden = $res ? (int) $res->fetch_assoc()['next_orden'] : 1;
        $stmt = $db->prepare('INSERT INTO sub_menus (id_menu, nombre, url, icono, orden, estado, fecha_creacion, hora_creacion, ip_creacion) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), CURTIME(), ?)');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $newIcono = $icono ?? '';
        $stmt->bind_param('isssiis', $idMenu, $nombre, $url, $newIcono, $orden, $estado, $ip);
    }

    $stmt->execute();
    $savedId = $idSubMenu > 0 ? $idSubMenu : (int) $db->insert_id;
    $stmt->close();

    if ($url === '' || $url === '#') {
        $internalUrl = cms_submenu_public_url(['id_sub_menu' => $savedId, 'url' => '']);
        $stmtUrl = $db->prepare('UPDATE sub_menus SET url = ? WHERE id_sub_menu = ?');
        if ($stmtUrl) {
            $stmtUrl->bind_param('si', $internalUrl, $savedId);
            $stmtUrl->execute();
            $stmtUrl->close();
        }
    }

    cms_save_submenu_page($db, $savedId, $post);
    return $savedId;
}

function cms_toggle_submenu(mysqli $db, int $idSubMenu): void
{
    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;
    $stmt = $db->prepare('UPDATE sub_menus SET estado = IF(estado = 1, 0, 1), actualizado_en = NOW(), actualizado_por = ? WHERE id_sub_menu = ?');
    $stmt->bind_param('ii', $idUsuario, $idSubMenu);
    $stmt->execute();
    $stmt->close();
}

function cms_reorder_menus(mysqli $db, array $ids): void
{
    foreach ($ids as $index => $idMenu) {
        if ($idMenu <= 0) { continue; }
        $orden = $index + 1;
        $stmt = $db->prepare('UPDATE menus SET orden = ? WHERE id_menu = ?');
        $stmt->bind_param('ii', $orden, $idMenu);
        $stmt->execute();
        $stmt->close();
    }
}

function cms_reorder_submenus(mysqli $db, array $ids): void
{
    foreach ($ids as $index => $idSubMenu) {
        if ($idSubMenu <= 0) { continue; }
        $orden = $index + 1;
        $stmt = $db->prepare('UPDATE sub_menus SET orden = ? WHERE id_sub_menu = ?');
        $stmt->bind_param('ii', $orden, $idSubMenu);
        $stmt->execute();
        $stmt->close();
    }
}

function cms_save_institution(mysqli $db, int $institutionId, array $post): void
{
    $current = null;
    $result = $db->query('SELECT * FROM institucion WHERE id_institucion = ' . $institutionId . ' LIMIT 1');
    if ($result) {
        $current = $result->fetch_assoc();
    }
    if (!$current) {
        throw new RuntimeException('No se encontro la institucion.');
    }

    $logoHeader = cms_upload_image('logo_header', 'institucion', $current['logo_header'] ?? null);
    $logoFooter = cms_upload_image('logo_footer', 'institucion', $current['logo_footer'] ?? null);
    $favicon    = cms_upload_image('favicon',     'institucion', $current['favicon']     ?? null);

    $s = static fn(string $k): string => trim((string) ($post[$k] ?? ''));

    $stmt = $db->prepare('UPDATE institucion SET
        nombre = ?, nombre_corto = ?, eslogan = ?, descripcion_corta = ?,
        email = ?, email_soporte = ?, telefono = ?, whatsapp = ?,
        direccion = ?, ciudad = ?,
        facebook = ?, instagram = ?, youtube = ?, linkedin = ?,
        color_primario = ?, color_secundario = ?, color_terciario = ?, color_cuaternario = ?,
        logo_header = ?, logo_footer = ?, favicon = ?,
        meta_title = ?, meta_description = ?,
        texto_footer = ?, copyright = ?
        WHERE id_institucion = ?');

    $nombre          = $s('nombre');
    $nombreCorto     = $s('nombre_corto');
    $eslogan         = $s('eslogan');
    $descripcionCorta= $s('descripcion_corta');
    $email           = $s('email');
    $emailSoporte    = $s('email_soporte');
    $telefono        = $s('telefono');
    $whatsapp        = $s('whatsapp');
    $direccion       = $s('direccion');
    $ciudad          = $s('ciudad');
    $facebook        = $s('facebook');
    $instagram       = $s('instagram');
    $youtube         = $s('youtube');
    $linkedin        = $s('linkedin');
    $colorPrimario   = $s('color_primario');
    $colorSecundario = $s('color_secundario');
    $colorTerciario  = $s('color_terciario');
    $colorCuaternario= $s('color_cuaternario');
    $metaTitle       = $s('meta_title');
    $metaDesc        = $s('meta_description');
    $textoFooter     = $s('texto_footer');
    $copyright       = $s('copyright');

    $stmt->bind_param(
        'sssssssssssssssssssssssssi',
        $nombre, $nombreCorto, $eslogan, $descripcionCorta,
        $email, $emailSoporte, $telefono, $whatsapp,
        $direccion, $ciudad,
        $facebook, $instagram, $youtube, $linkedin,
        $colorPrimario, $colorSecundario, $colorTerciario, $colorCuaternario,
        $logoHeader, $logoFooter, $favicon,
        $metaTitle, $metaDesc,
        $textoFooter, $copyright,
        $institutionId
    );
    $stmt->execute();
    $stmt->close();
}
