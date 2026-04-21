<?php
require_once __DIR__ . '/../class/conexion.php';

function cms_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
    $stmt = $db->prepare('SHOW TABLES LIKE ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

function cms_column_exists(mysqli $db, string $table, string $column): bool
{
    $stmt = $db->prepare('SHOW COLUMNS FROM `' . $table . '` LIKE ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $column);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
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
            'nombre_interno' => 'faq_home',
            'titulo_admin' => 'Preguntas frecuentes',
            'tipo_seccion' => 'faq',
            'variante' => 'imagen_lateral',
            'orden' => 5,
            'observacion' => 'Contenedor de preguntas frecuentes con acordeon e imagen lateral.',
        ],
        [
            'nombre_interno' => 'about_home',
            'titulo_admin' => 'Sobre nosotros',
            'tipo_seccion' => 'content',
            'variante' => 'imagen_texto',
            'orden' => 6,
            'observacion' => 'Bloque institucional de presentacion con imagen principal, video y descripcion.',
        ],
        [
            'nombre_interno' => 'footer_principal',
            'titulo_admin' => 'Footer principal',
            'tipo_seccion' => 'footer',
            'variante' => 'institucional',
            'orden' => 7,
            'observacion' => 'Este es el contenedor del footer. Aqui se muestran logo, descripcion institucional, enlaces rapidos, contacto, redes sociales y datos principales del sitio.',
        ],
    ];
}

function cms_sync_sections(mysqli $db, int $institutionId): void
{
    if (!cms_column_exists($db, 'seccion', 'observacion')) {
        $db->query("ALTER TABLE seccion ADD COLUMN observacion TEXT NULL AFTER orden");
    }

    $selectStmt = $db->prepare('SELECT id_seccion FROM seccion WHERE id_institucion = ? AND nombre_interno = ? LIMIT 1');
    $insertStmt = $db->prepare('INSERT INTO seccion (id_institucion, nombre_interno, titulo_admin, tipo_seccion, variante, visible, orden, observacion) VALUES (?, ?, ?, ?, ?, \'si\', ?, ?)');
    $updateStmt = $db->prepare('UPDATE seccion SET titulo_admin = ?, tipo_seccion = ?, variante = ?, orden = ?, observacion = ? WHERE id_seccion = ?');

    foreach (cms_default_sections() as $section) {
        $name = $section['nombre_interno'];
        $selectStmt->bind_param('is', $institutionId, $name);
        $selectStmt->execute();
        $result = $selectStmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;

        if ($row) {
            $idSeccion = (int) $row['id_seccion'];
            $updateStmt->bind_param(
                'sssisi',
                $section['titulo_admin'],
                $section['tipo_seccion'],
                $section['variante'],
                $section['orden'],
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
}

function cms_get_preview_target(string $name): string
{
    $anchors = [
        'topbar' => '#topbar',
        'header_principal' => '#header-principal',
        // Compatibilidad legacy: la navegación vive dentro de header_principal.
        'menu_principal' => '#header-principal',
        'hero_principal' => '#hero-principal',
        'noticias_home' => '#noticias',
        'faq_home' => '#faq',
        'about_home' => '#about',
        'footer_principal' => '#footer-principal',
    ];

    return 'index.php' . ($anchors[$name] ?? '');
}

function cms_get_component_path(string $name): ?string
{
    $path = __DIR__ . '/../componentes/' . $name . '.php';
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
    $sql = "SELECT s.*, COUNT(si.id_item) AS total_items
            FROM seccion s
            LEFT JOIN seccion_item si ON si.id_seccion = s.id_seccion
            WHERE s.id_institucion = ?
              AND s.nombre_interno <> 'menu_principal'
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
    $result = $db->query('SELECT * FROM menus ORDER BY orden ASC, id_menu ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function cms_get_submenu(mysqli $db, int $idSubMenu): ?array
{
    $stmt = $db->prepare('SELECT * FROM sub_menus WHERE id_sub_menu = ? LIMIT 1');
    $stmt->bind_param('i', $idSubMenu);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function cms_list_submenus(mysqli $db): array
{
    $sql = 'SELECT sm.*, m.nombre AS menu_padre
            FROM sub_menus sm
            INNER JOIN menus m ON m.id_menu = sm.id_menu
            ORDER BY m.orden ASC, sm.orden ASC, sm.id_sub_menu ASC';
    $result = $db->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function cms_normalize_filename(string $name): string
{
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $base = strtolower(pathinfo($name, PATHINFO_FILENAME));
    $base = preg_replace('/[^a-z0-9]+/', '-', $base);
    $base = trim((string) $base, '-');
    $base = $base !== '' ? $base : 'archivo';

    return $base . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . ($ext ? '.' . $ext : '');
}

function cms_upload_image(string $fieldName, string $folder, ?string $current = null): ?string
{
    if (empty($_FILES[$fieldName]) || !isset($_FILES[$fieldName]['error'])) {
        return $current;
    }

    if ((int) $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $current;
    }

    if ((int) $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No fue posible subir la imagen del campo ' . $fieldName . '.');
    }

    $tmpPath = $_FILES[$fieldName]['tmp_name'];
    $mime = mime_content_type($tmpPath) ?: '';
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'];
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Formato de imagen no permitido en ' . $fieldName . '.');
    }

    $relativeDir = 'uploads/' . trim($folder, '/');
    $absoluteDir = dirname(__DIR__) . '/' . $relativeDir;
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0777, true) && !is_dir($absoluteDir)) {
        throw new RuntimeException('No fue posible crear la carpeta de subida.');
    }

    $filename = cms_normalize_filename((string) $_FILES[$fieldName]['name']);
    $absolutePath = $absoluteDir . '/' . $filename;
    $relativePath = $relativeDir . '/' . $filename;

    if (!move_uploaded_file($tmpPath, $absolutePath)) {
        throw new RuntimeException('No fue posible mover la imagen subida.');
    }

    return $relativePath;
}

function cms_toggle_section_visibility(mysqli $db, int $idSeccion): void
{
    $stmt = $db->prepare("UPDATE seccion SET visible = IF(visible = 'si', 'no', 'si') WHERE id_seccion = ?");
    $stmt->bind_param('i', $idSeccion);
    $stmt->execute();
    $stmt->close();
}

function cms_save_section(mysqli $db, int $idSeccion, array $post): void
{
    $visible = (($post['visible'] ?? 'no') === 'si') ? 'si' : 'no';
    $orden = max(1, (int) ($post['orden'] ?? 1));
    $observacion = trim((string) ($post['observacion'] ?? ''));

    $stmt = $db->prepare('UPDATE seccion SET visible = ?, orden = ?, observacion = ? WHERE id_seccion = ?');
    $stmt->bind_param('sisi', $visible, $orden, $observacion, $idSeccion);
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
    $fechaPublicacion = trim((string) ($post['fecha_publicacion'] ?? ''));
    $visible = (($post['visible'] ?? 'no') === 'si') ? 'si' : 'no';
    $orden = max(1, (int) ($post['orden'] ?? 1));

    $folder = $section['tipo_seccion'] === 'news'
        ? 'noticias'
        : 'secciones/' . preg_replace('/[^a-z0-9_-]+/i', '-', $section['nombre_interno']);

    $clearImagen = isset($post['clear_imagen']) && (string) $post['clear_imagen'] === '1';
    $clearImagenMobile = isset($post['clear_imagen_mobile']) && (string) $post['clear_imagen_mobile'] === '1';

    $imagen = $clearImagen
        ? null
        : cms_upload_image('imagen', $folder, $itemActual['imagen'] ?? null);
    $imagenMobile = $clearImagenMobile
        ? null
        : cms_upload_image('imagen_mobile', $folder, $itemActual['imagen_mobile'] ?? null);

    $fechaPublicacion = $fechaPublicacion !== '' ? $fechaPublicacion : null;

    if ($idItem > 0) {
        $sql = 'UPDATE seccion_item
                SET id_categoria = ?, etiqueta = ?, titulo = ?, titulo_linea_1 = ?, titulo_linea_2 = ?, titulo_linea_3 = ?,
                    subtitulo = ?, descripcion = ?, imagen = ?, imagen_mobile = ?, boton_1_texto = ?, boton_1_url = ?,
                    boton_2_texto = ?, boton_2_url = ?, fecha_publicacion = ?, visible = ?, orden = ?
                WHERE id_item = ? AND id_seccion = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            'isssssssssssssssiii',
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
            $boton1Texto,
            $boton1Url,
            $boton2Texto,
            $boton2Url,
            $fechaPublicacion,
            $visible,
            $orden,
            $idItem,
            $idSeccion
        );
    } else {
        $sql = 'INSERT INTO seccion_item
                (id_seccion, id_categoria, etiqueta, titulo, titulo_linea_1, titulo_linea_2, titulo_linea_3, subtitulo, descripcion, imagen, imagen_mobile, boton_1_texto, boton_1_url, boton_2_texto, boton_2_url, fecha_publicacion, visible, orden)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            'iisssssssssssssssi',
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
            $boton1Texto,
            $boton1Url,
            $boton2Texto,
            $boton2Url,
            $fechaPublicacion,
            $visible,
            $orden
        );
    }

    $stmt->execute();
    $newId = $idItem > 0 ? $idItem : (int) $db->insert_id;
    $stmt->close();
    return $newId;
}

function cms_delete_item(mysqli $db, int $idItem): void
{
    $stmt = $db->prepare('DELETE FROM seccion_item WHERE id_item = ?');
    $stmt->bind_param('i', $idItem);
    $stmt->execute();
    $stmt->close();
}

function cms_save_menu(mysqli $db, array $post): void
{
    $idMenu = (int) ($post['id_menu'] ?? 0);
    $nombre = trim((string) ($post['nombre'] ?? ''));
    $url = trim((string) ($post['url'] ?? ''));
    $icono = trim((string) ($post['icono'] ?? ''));
    $orden = max(0, (int) ($post['orden'] ?? 0));
    $estado = isset($post['estado']) ? 1 : 0;

    if ($nombre === '') {
        throw new RuntimeException('El nombre del menu es obligatorio.');
    }

    if ($idMenu > 0) {
        $stmt = $db->prepare('UPDATE menus SET nombre = ?, url = ?, icono = ?, orden = ?, estado = ? WHERE id_menu = ?');
        $stmt->bind_param('sssiii', $nombre, $url, $icono, $orden, $estado, $idMenu);
    } else {
        $stmt = $db->prepare('INSERT INTO menus (nombre, url, icono, orden, estado) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssii', $nombre, $url, $icono, $orden, $estado);
    }

    $stmt->execute();
    $stmt->close();
}

function cms_toggle_menu(mysqli $db, int $idMenu): void
{
    $stmt = $db->prepare('UPDATE menus SET estado = IF(estado = 1, 0, 1) WHERE id_menu = ?');
    $stmt->bind_param('i', $idMenu);
    $stmt->execute();
    $stmt->close();
}

function cms_save_submenu(mysqli $db, array $post): void
{
    $idSubMenu = (int) ($post['id_sub_menu'] ?? 0);
    $idMenu = (int) ($post['id_menu'] ?? 0);
    $nombre = trim((string) ($post['nombre'] ?? ''));
    $url = trim((string) ($post['url'] ?? ''));
    $icono = trim((string) ($post['icono'] ?? ''));
    $orden = max(0, (int) ($post['orden'] ?? 0));
    $estado = isset($post['estado']) ? 1 : 0;

    if ($idMenu < 1 || $nombre === '') {
        throw new RuntimeException('El submenu debe tener menu padre y nombre.');
    }

    if ($idSubMenu > 0) {
        $stmt = $db->prepare('UPDATE sub_menus SET id_menu = ?, nombre = ?, url = ?, icono = ?, orden = ?, estado = ? WHERE id_sub_menu = ?');
        $stmt->bind_param('isssiii', $idMenu, $nombre, $url, $icono, $orden, $estado, $idSubMenu);
    } else {
        $stmt = $db->prepare('INSERT INTO sub_menus (id_menu, nombre, url, icono, orden, estado, fecha_creacion, hora_creacion, ip_creacion) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), CURTIME(), ?)');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt->bind_param('isssiis', $idMenu, $nombre, $url, $icono, $orden, $estado, $ip);
    }

    $stmt->execute();
    $stmt->close();
}

function cms_toggle_submenu(mysqli $db, int $idSubMenu): void
{
    $stmt = $db->prepare('UPDATE sub_menus SET estado = IF(estado = 1, 0, 1) WHERE id_sub_menu = ?');
    $stmt->bind_param('i', $idSubMenu);
    $stmt->execute();
    $stmt->close();
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
    $favicon = cms_upload_image('favicon', 'institucion', $current['favicon'] ?? null);

    $stmt = $db->prepare('UPDATE institucion
        SET nombre = ?, email = ?, telefono = ?, direccion = ?, logo_header = ?, favicon = ?, facebook = ?, instagram = ?, color_primario = ?, color_secundario = ?
        WHERE id_institucion = ?');

    $nombre = trim((string) ($post['nombre'] ?? ''));
    $email = trim((string) ($post['email'] ?? ''));
    $telefono = trim((string) ($post['telefono'] ?? ''));
    $direccion = trim((string) ($post['direccion'] ?? ''));
    $facebook = trim((string) ($post['facebook'] ?? ''));
    $instagram = trim((string) ($post['instagram'] ?? ''));
    $colorPrimario = trim((string) ($post['color_primario'] ?? ''));
    $colorSecundario = trim((string) ($post['color_secundario'] ?? ''));

    $stmt->bind_param(
        'ssssssssssi',
        $nombre,
        $email,
        $telefono,
        $direccion,
        $logoHeader,
        $favicon,
        $facebook,
        $instagram,
        $colorPrimario,
        $colorSecundario,
        $institutionId
    );
    $stmt->execute();
    $stmt->close();
}
