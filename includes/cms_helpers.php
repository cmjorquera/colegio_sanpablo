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
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return false;
    }

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

function cms_section_fixed_names(): array
{
    return ['topbar', 'header_principal', 'menu_principal', 'footer_principal', 'modal_informativo'];
}

function cms_section_movable_names(): array
{
    return ['hero_principal', 'noticias_home', 'calendario_eventos_home', 'video_destacado_home', 'galeria_home', 'faq_home', 'about_home'];
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
            'variante' => 'imagen_texto',
            'orden' => 99,
            'observacion' => 'Modal emergente que se muestra al cargar la pagina. Configurable con imagen, titulo, descripcion y boton.',
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

    // Limpieza: elimina la sección renombrada modal_bienvenida si no tiene ítems
    $stmtClean = $db->prepare(
        "DELETE s FROM seccion s
          WHERE s.nombre_interno = 'modal_bienvenida'
            AND s.id_institucion = ?
            AND NOT EXISTS (
                SELECT 1 FROM seccion_item si WHERE si.id_seccion = s.id_seccion
            )"
    );
    if ($stmtClean) {
        $stmtClean->bind_param('i', $institutionId);
        $stmtClean->execute();
        $stmtClean->close();
    }
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
        'calendario_eventos_home' => '#calendario-eventos-home',
        'video_destacado_home' => '#video-destacado-home',
        'galeria_home' => '#galeria',
        'faq_home' => '#faq',
        'about_home' => '#about',
        'footer_principal'  => '#footer-principal',
        'modal_informativo' => '#modal-informativo',
        'modal_bienvenida'  => '#modal-informativo',
    ];

    return 'index.php' . ($anchors[$name] ?? '');
}

function cms_get_component_path(string $name): ?string
{
    $fallbackComponents = [
        'header_principal'    => 'header',
        'modal_bienvenida'    => 'modal_informativo',
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

function cms_upload_file(string $fieldName, string $folder, array $allowedExtensions, ?string $current = null): ?string
{
    if (empty($_FILES[$fieldName]) || !isset($_FILES[$fieldName]['error'])) {
        return $current;
    }

    if ((int) $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $current;
    }

    if ((int) $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No fue posible subir el archivo del campo ' . $fieldName . '.');
    }

    $ext = strtolower(pathinfo((string) $_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions, true)) {
        throw new RuntimeException('Tipo de archivo no permitido en ' . $fieldName . '.');
    }

    $relativeDir = 'uploads/' . trim($folder, '/');
    $absoluteDir = dirname(__DIR__) . '/' . $relativeDir;
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0777, true) && !is_dir($absoluteDir)) {
        throw new RuntimeException('No fue posible crear la carpeta de subida.');
    }

    $filename = cms_normalize_filename((string) $_FILES[$fieldName]['name']);
    $absolutePath = $absoluteDir . '/' . $filename;
    $relativePath = $relativeDir . '/' . $filename;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $absolutePath)) {
        throw new RuntimeException('No fue posible mover el archivo subido.');
    }

    return $relativePath;
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
    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No fue posible subir un archivo multimedia del evento.');
    }

    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    $allowed = $tipo === 'video' ? ['mp4', 'webm', 'mov', 'm4v'] : ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('Formato multimedia no permitido.');
    }

    $relativeDir = 'uploads/eventos/media/' . $idEvento;
    $absoluteDir = dirname(__DIR__) . '/' . $relativeDir;
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0777, true) && !is_dir($absoluteDir)) {
        throw new RuntimeException('No fue posible crear la carpeta multimedia del evento.');
    }

    $filename = cms_normalize_filename((string) $file['name']);
    $absolutePath = $absoluteDir . '/' . $filename;
    $relativePath = $relativeDir . '/' . $filename;
    if (!move_uploaded_file((string) $file['tmp_name'], $absolutePath)) {
        throw new RuntimeException('No fue posible mover el archivo multimedia.');
    }
    return $relativePath;
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
                    boton_2_texto = ?, boton_2_url = ?, url = ?, fecha_publicacion = ?, visible = ?, orden = ?
                WHERE id_item = ? AND id_seccion = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            'issssssssssssssssiii',
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
            $url,
            $fechaPublicacion,
            $visible,
            $orden,
            $idItem,
            $idSeccion
        );
    } else {
        $sql = 'INSERT INTO seccion_item
                (id_seccion, id_categoria, etiqueta, titulo, titulo_linea_1, titulo_linea_2, titulo_linea_3, subtitulo, descripcion, imagen, imagen_mobile, boton_1_texto, boton_1_url, boton_2_texto, boton_2_url, url, fecha_publicacion, visible, orden)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            'iissssssssssssssssi',
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
            $url,
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

function cms_save_menu(mysqli $db, array $post): int
{
    $idMenu = (int) ($post['id_menu'] ?? 0);
    $nombre = trim((string) ($post['nombre'] ?? ''));
    $url = trim((string) ($post['url'] ?? ''));
    $icono = trim((string) ($post['icono'] ?? ''));
    $estado = isset($post['estado']) ? 1 : 0;

    if ($nombre === '') {
        throw new RuntimeException('El nombre del menu es obligatorio.');
    }

    if ($idMenu > 0) {
        $stmt = $db->prepare('UPDATE menus SET nombre = ?, url = ?, icono = ?, estado = ? WHERE id_menu = ?');
        $stmt->bind_param('sssii', $nombre, $url, $icono, $estado, $idMenu);
    } else {
        $res = $db->query('SELECT COALESCE(MAX(orden), 0) + 1 AS next_orden FROM menus');
        $orden = $res ? (int) $res->fetch_assoc()['next_orden'] : 1;
        $stmt = $db->prepare('INSERT INTO menus (nombre, url, icono, orden, estado) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssii', $nombre, $url, $icono, $orden, $estado);
    }

    $stmt->execute();
    $savedId = $idMenu > 0 ? $idMenu : (int) $db->insert_id;
    $stmt->close();
    return $savedId;
}

function cms_toggle_menu(mysqli $db, int $idMenu): void
{
    $stmt = $db->prepare('UPDATE menus SET estado = IF(estado = 1, 0, 1) WHERE id_menu = ?');
    $stmt->bind_param('i', $idMenu);
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
    $idSubMenu = (int) ($post['id_sub_menu'] ?? 0);
    $idMenu = (int) ($post['id_menu'] ?? 0);
    $nombre = trim((string) ($post['nombre'] ?? ''));
    $url = trim((string) ($post['url'] ?? ''));
    $icono = trim((string) ($post['icono'] ?? ''));
    $estado = isset($post['estado']) ? 1 : 0;

    if ($idMenu < 1 || $nombre === '') {
        throw new RuntimeException('El submenu debe tener menu padre y nombre.');
    }

    if ($idSubMenu > 0) {
        $stmt = $db->prepare('UPDATE sub_menus SET id_menu = ?, nombre = ?, url = ?, icono = ?, estado = ? WHERE id_sub_menu = ?');
        $stmt->bind_param('isssii', $idMenu, $nombre, $url, $icono, $estado, $idSubMenu);
    } else {
        $res = $db->query('SELECT COALESCE(MAX(orden), 0) + 1 AS next_orden FROM sub_menus WHERE id_menu = ' . $idMenu);
        $orden = $res ? (int) $res->fetch_assoc()['next_orden'] : 1;
        $stmt = $db->prepare('INSERT INTO sub_menus (id_menu, nombre, url, icono, orden, estado, fecha_creacion, hora_creacion, ip_creacion) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), CURTIME(), ?)');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt->bind_param('isssiis', $idMenu, $nombre, $url, $icono, $orden, $estado, $ip);
    }

    $stmt->execute();
    $savedId = $idSubMenu > 0 ? $idSubMenu : (int) $db->insert_id;
    $stmt->close();
    return $savedId;
}

function cms_toggle_submenu(mysqli $db, int $idSubMenu): void
{
    $stmt = $db->prepare('UPDATE sub_menus SET estado = IF(estado = 1, 0, 1) WHERE id_sub_menu = ?');
    $stmt->bind_param('i', $idSubMenu);
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
