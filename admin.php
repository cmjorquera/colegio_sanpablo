<?php
/**
 * Panel de administracion de bloques del sitio.
 */
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: colegiosanpablo.php');
    exit;
}

require_once __DIR__ . '/class/conexion.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['admin_flash'])) {
        return null;
    }

    $flash = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);

    return $flash;
}

function redirect_admin(string $query = ''): void
{
    header('Location: admin.php' . $query);
    exit;
}

function table_exists(mysqli $db, string $table): bool
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

function get_institution_id(mysqli $db): int
{
    if (!empty($_SESSION['id_institucion'])) {
        return (int) $_SESSION['id_institucion'];
    }

    $res = $db->query('SELECT id_institucion FROM institucion ORDER BY id_institucion ASC LIMIT 1');
    if ($res && ($row = $res->fetch_assoc())) {
        return (int) $row['id_institucion'];
    }

    return 1;
}

function listar_secciones(mysqli $db, int $idInstitucion): array
{
    $sql = "SELECT s.*,
                   COUNT(si.id_item) AS total_items
            FROM seccion s
            LEFT JOIN seccion_item si ON si.id_seccion = s.id_seccion
            WHERE s.id_institucion = ?
            GROUP BY s.id_seccion
            ORDER BY s.orden ASC, s.id_seccion ASC";

    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $idInstitucion);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return $rows;
}

function obtener_seccion(mysqli $db, int $idSeccion): ?array
{
    $stmt = $db->prepare('SELECT * FROM seccion WHERE id_seccion = ? LIMIT 1');
    $stmt->bind_param('i', $idSeccion);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function obtener_configs_seccion(mysqli $db, int $idSeccion): array
{
    $stmt = $db->prepare('SELECT * FROM seccion_config WHERE id_seccion = ? ORDER BY clave ASC, id_config ASC');
    $stmt->bind_param('i', $idSeccion);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return $rows;
}

function listar_items_seccion(mysqli $db, int $idSeccion): array
{
    $stmt = $db->prepare('SELECT * FROM seccion_item WHERE id_seccion = ? ORDER BY orden ASC, id_item ASC');
    $stmt->bind_param('i', $idSeccion);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return $rows;
}

function obtener_item(mysqli $db, int $idItem): ?array
{
    $stmt = $db->prepare('SELECT * FROM seccion_item WHERE id_item = ? LIMIT 1');
    $stmt->bind_param('i', $idItem);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function obtener_institucion(mysqli $db, int $idInstitucion): ?array
{
    $stmt = $db->prepare('SELECT * FROM institucion WHERE id_institucion = ? LIMIT 1');
    $stmt->bind_param('i', $idInstitucion);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function listar_menus(mysqli $db): array
{
    $result = $db->query('SELECT * FROM menus ORDER BY orden ASC, id_menu ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function listar_submenus(mysqli $db): array
{
    $sql = 'SELECT sm.*, m.nombre AS menu_padre
            FROM sub_menus sm
            INNER JOIN menus m ON m.id_menu = sm.id_menu
            ORDER BY m.orden ASC, sm.orden ASC, sm.id_sub_menu ASC';
    $result = $db->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function obtener_menu(mysqli $db, int $idMenu): ?array
{
    $stmt = $db->prepare('SELECT * FROM menus WHERE id_menu = ? LIMIT 1');
    $stmt->bind_param('i', $idMenu);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function obtener_submenu(mysqli $db, int $idSubmenu): ?array
{
    $stmt = $db->prepare('SELECT * FROM sub_menus WHERE id_sub_menu = ? LIMIT 1');
    $stmt->bind_param('i', $idSubmenu);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function obtener_categorias_noticia(mysqli $db): array
{
    if (!table_exists($db, 'categoria_noticia')) {
        return [];
    }

    $result = $db->query('SELECT * FROM categoria_noticia ORDER BY nombre ASC, id_categoria ASC');
    if (!$result) {
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function obtener_categorias_fallback(mysqli $db, int $idSeccion): array
{
    $categorias = [];

    $stmt = $db->prepare("SELECT DISTINCT etiqueta FROM seccion_item WHERE id_seccion = ? AND etiqueta IS NOT NULL AND etiqueta <> '' ORDER BY etiqueta ASC");
    $stmt->bind_param('i', $idSeccion);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($result && ($row = $result->fetch_assoc())) {
        $categorias[] = $row['etiqueta'];
    }

    $stmt->close();

    return $categorias;
}

function normalizar_archivo(string $name): string
{
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $base = strtolower(pathinfo($name, PATHINFO_FILENAME));
    $base = preg_replace('/[^a-z0-9]+/', '-', $base);
    $base = trim((string) $base, '-');
    $base = $base !== '' ? $base : 'archivo';

    return $base . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . ($ext ? '.' . $ext : '');
}

function subir_imagen(string $fieldName, string $folder, ?string $current = null): ?string
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
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];

    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Formato de imagen no permitido en ' . $fieldName . '.');
    }

    $relativeDir = 'uploads/' . trim($folder, '/');
    $absoluteDir = __DIR__ . '/' . $relativeDir;

    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0777, true) && !is_dir($absoluteDir)) {
        throw new RuntimeException('No fue posible crear la carpeta de subida.');
    }

    $filename = normalizar_archivo((string) $_FILES[$fieldName]['name']);
    $absolutePath = $absoluteDir . '/' . $filename;
    $relativePath = $relativeDir . '/' . $filename;

    if (!move_uploaded_file($tmpPath, $absolutePath)) {
        throw new RuntimeException('No fue posible mover la imagen subida.');
    }

    return $relativePath;
}

function editar_seccion(mysqli $db, int $idSeccion, array $post): void
{
    $visible = (($post['visible'] ?? 'no') === 'si') ? 'si' : 'no';
    $orden = max(1, (int) ($post['orden'] ?? 1));

    $stmt = $db->prepare('UPDATE seccion SET visible = ?, orden = ? WHERE id_seccion = ?');
    $stmt->bind_param('sii', $visible, $orden, $idSeccion);
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

function toggle_visible_seccion(mysqli $db, int $idSeccion): void
{
    $stmt = $db->prepare("UPDATE seccion SET visible = IF(visible = 'si', 'no', 'si') WHERE id_seccion = ?");
    $stmt->bind_param('i', $idSeccion);
    $stmt->execute();
    $stmt->close();
}

function guardar_menu(mysqli $db, array $post): void
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

function toggle_menu(mysqli $db, int $idMenu): void
{
    $stmt = $db->prepare('UPDATE menus SET estado = IF(estado = 1, 0, 1) WHERE id_menu = ?');
    $stmt->bind_param('i', $idMenu);
    $stmt->execute();
    $stmt->close();
}

function guardar_submenu(mysqli $db, array $post): void
{
    $idSubmenu = (int) ($post['id_sub_menu'] ?? 0);
    $idMenu = (int) ($post['id_menu'] ?? 0);
    $nombre = trim((string) ($post['nombre'] ?? ''));
    $url = trim((string) ($post['url'] ?? ''));
    $icono = trim((string) ($post['icono'] ?? ''));
    $orden = max(0, (int) ($post['orden'] ?? 0));
    $estado = isset($post['estado']) ? 1 : 0;

    if ($idMenu < 1 || $nombre === '') {
        throw new RuntimeException('El submenu debe tener menu padre y nombre.');
    }

    if ($idSubmenu > 0) {
        $stmt = $db->prepare('UPDATE sub_menus SET id_menu = ?, nombre = ?, url = ?, icono = ?, orden = ?, estado = ? WHERE id_sub_menu = ?');
        $stmt->bind_param('isssiii', $idMenu, $nombre, $url, $icono, $orden, $estado, $idSubmenu);
    } else {
        $stmt = $db->prepare('INSERT INTO sub_menus (id_menu, nombre, url, icono, orden, estado, fecha_creacion, hora_creacion, ip_creacion) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), CURTIME(), ?)');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt->bind_param('isssiis', $idMenu, $nombre, $url, $icono, $orden, $estado, $ip);
    }

    $stmt->execute();
    $stmt->close();
}

function toggle_submenu(mysqli $db, int $idSubmenu): void
{
    $stmt = $db->prepare('UPDATE sub_menus SET estado = IF(estado = 1, 0, 1) WHERE id_sub_menu = ?');
    $stmt->bind_param('i', $idSubmenu);
    $stmt->execute();
    $stmt->close();
}

function guardar_institucion(mysqli $db, int $idInstitucion, array $post): void
{
    $actual = obtener_institucion($db, $idInstitucion);
    if (!$actual) {
        throw new RuntimeException('No se encontro la institucion activa.');
    }

    $logoHeader = subir_imagen('logo_header', 'institucion', $actual['logo_header'] ?? null);
    $favicon = subir_imagen('favicon', 'institucion', $actual['favicon'] ?? null);

    $nombre = trim((string) ($post['nombre'] ?? ''));
    $email = trim((string) ($post['email'] ?? ''));
    $telefono = trim((string) ($post['telefono'] ?? ''));
    $direccion = trim((string) ($post['direccion'] ?? ''));
    $facebook = trim((string) ($post['facebook'] ?? ''));
    $instagram = trim((string) ($post['instagram'] ?? ''));
    $colorPrimario = trim((string) ($post['color_primario'] ?? ''));
    $colorSecundario = trim((string) ($post['color_secundario'] ?? ''));

    $stmt = $db->prepare('UPDATE institucion
        SET nombre = ?, email = ?, telefono = ?, direccion = ?, logo_header = ?, favicon = ?, facebook = ?, instagram = ?, color_primario = ?, color_secundario = ?
        WHERE id_institucion = ?');
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
        $idInstitucion
    );
    $stmt->execute();
    $stmt->close();
}

function editar_item(mysqli $db, array $section, array $post): int
{
    $idSeccion = (int) $section['id_seccion'];
    $tipoSeccion = (string) $section['tipo_seccion'];
    $idItem = (int) ($post['id_item'] ?? 0);
    $itemActual = $idItem > 0 ? obtener_item($db, $idItem) : null;

    $titulo = trim((string) ($post['titulo'] ?? ''));
    $subtitulo = trim((string) ($post['subtitulo'] ?? ''));
    $descripcion = trim((string) ($post['descripcion'] ?? ''));
    $etiqueta = trim((string) ($post['etiqueta'] ?? ''));
    $boton1Texto = trim((string) ($post['boton_1_texto'] ?? ''));
    $boton1Url = trim((string) ($post['boton_1_url'] ?? ''));
    $boton2Texto = trim((string) ($post['boton_2_texto'] ?? ''));
    $boton2Url = trim((string) ($post['boton_2_url'] ?? ''));
    $fechaPublicacion = trim((string) ($post['fecha_publicacion'] ?? ''));
    $idCategoria = !empty($post['id_categoria']) ? (int) $post['id_categoria'] : null;
    $visible = (($post['visible'] ?? 'no') === 'si') ? 'si' : 'no';
    $orden = max(1, (int) ($post['orden'] ?? 1));
    $tituloLinea1 = trim((string) ($post['titulo_linea_1'] ?? ''));
    $tituloLinea2 = trim((string) ($post['titulo_linea_2'] ?? ''));
    $tituloLinea3 = trim((string) ($post['titulo_linea_3'] ?? ''));

    if ($tipoSeccion === 'news') {
        $etiqueta = trim((string) ($post['etiqueta'] ?? $etiqueta));
        $subtitulo = trim((string) ($post['subtitulo'] ?? 'Últimas Noticias'));
        $fechaPublicacion = $fechaPublicacion !== '' ? $fechaPublicacion : null;
        if ($boton1Texto === '') {
            $boton1Texto = 'Leer más';
        }
    } else {
        $fechaPublicacion = $fechaPublicacion !== '' ? $fechaPublicacion : null;
    }

    $folder = $tipoSeccion === 'news' ? 'noticias' : 'secciones/' . preg_replace('/[^a-z0-9_-]+/i', '-', $section['nombre_interno']);
    $imagen = subir_imagen('imagen', $folder, $itemActual['imagen'] ?? null);
    $imagenMobile = subir_imagen('imagen_mobile', $folder, $itemActual['imagen_mobile'] ?? null);

    if ($idItem > 0) {
        $sql = 'UPDATE seccion_item
                SET id_categoria = ?, etiqueta = ?, titulo = ?, titulo_linea_1 = ?, titulo_linea_2 = ?, titulo_linea_3 = ?,
                    subtitulo = ?, descripcion = ?, imagen = ?, imagen_mobile = ?,
                    boton_1_texto = ?, boton_1_url = ?, boton_2_texto = ?, boton_2_url = ?,
                    fecha_publicacion = ?, visible = ?, orden = ?
                WHERE id_item = ? AND id_seccion = ?';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No fue posible preparar la actualizacion del item.');
        }
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
                (id_seccion, id_categoria, etiqueta, titulo, titulo_linea_1, titulo_linea_2, titulo_linea_3,
                 subtitulo, descripcion, imagen, imagen_mobile,
                 boton_1_texto, boton_1_url, boton_2_texto, boton_2_url,
                 fecha_publicacion, visible, orden)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No fue posible preparar la creacion del item.');
        }
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

function eliminar_item(mysqli $db, int $idItem): void
{
    $stmt = $db->prepare('DELETE FROM seccion_item WHERE id_item = ?');
    $stmt->bind_param('i', $idItem);
    $stmt->execute();
    $stmt->close();
}

$db = (new Conexion())->getConexion();
$idInstitucion = get_institution_id($db);
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $sectionId = (int) ($_POST['id_seccion'] ?? 0);

    try {
        if ($accion === 'toggle_seccion' && $sectionId > 0) {
            toggle_visible_seccion($db, $sectionId);
            set_flash('success', 'La visibilidad de la seccion fue actualizada correctamente.');
            redirect_admin('?section=' . $sectionId);
        }

        if ($accion === 'guardar_seccion' && $sectionId > 0) {
            editar_seccion($db, $sectionId, $_POST);
            set_flash('success', 'La seccion fue actualizada correctamente.');
            redirect_admin('?section=' . $sectionId);
        }

        if ($accion === 'guardar_item' && $sectionId > 0) {
            $section = obtener_seccion($db, $sectionId);
            if (!$section) {
                throw new RuntimeException('La seccion indicada no existe.');
            }

            editar_item($db, $section, $_POST);
            set_flash('success', 'El item fue guardado correctamente.');
            redirect_admin('?section=' . $sectionId . '&tab=items');
        }

        if ($accion === 'eliminar_item') {
            $idItem = (int) ($_POST['id_item'] ?? 0);
            eliminar_item($db, $idItem);
            set_flash('success', 'El item fue eliminado correctamente.');
            redirect_admin('?section=' . $sectionId . '&tab=items');
        }

        if ($accion === 'guardar_menu') {
            guardar_menu($db, $_POST);
            set_flash('success', 'El menu fue guardado correctamente.');
            redirect_admin('?section=' . $sectionId . '&admin_tab=menus');
        }

        if ($accion === 'toggle_menu') {
            $idMenu = (int) ($_POST['id_menu'] ?? 0);
            toggle_menu($db, $idMenu);
            set_flash('success', 'El estado del menu fue actualizado.');
            redirect_admin('?section=' . $sectionId . '&admin_tab=menus');
        }

        if ($accion === 'guardar_submenu') {
            guardar_submenu($db, $_POST);
            set_flash('success', 'El submenu fue guardado correctamente.');
            redirect_admin('?section=' . $sectionId . '&admin_tab=submenus');
        }

        if ($accion === 'toggle_submenu') {
            $idSubmenu = (int) ($_POST['id_sub_menu'] ?? 0);
            toggle_submenu($db, $idSubmenu);
            set_flash('success', 'El estado del submenu fue actualizado.');
            redirect_admin('?section=' . $sectionId . '&admin_tab=submenus');
        }

        if ($accion === 'guardar_institucion') {
            guardar_institucion($db, $idInstitucion, $_POST);
            set_flash('success', 'La configuracion institucional fue actualizada.');
            redirect_admin('?section=' . $sectionId . '&admin_tab=configuracion');
        }
    } catch (Throwable $e) {
        set_flash('danger', $e->getMessage());
        redirect_admin($sectionId > 0 ? '?section=' . $sectionId : '');
    }
}

$flash = get_flash();
$sections = listar_secciones($db, $idInstitucion);
$institution = obtener_institucion($db, $idInstitucion);
$menuRows = listar_menus($db);
$submenuRows = listar_submenus($db);
$selectedSectionId = isset($_GET['section']) ? (int) $_GET['section'] : (isset($sections[0]['id_seccion']) ? (int) $sections[0]['id_seccion'] : 0);
$selectedSection = $selectedSectionId > 0 ? obtener_seccion($db, $selectedSectionId) : null;
$sectionConfigs = $selectedSection ? obtener_configs_seccion($db, (int) $selectedSection['id_seccion']) : [];
$sectionItems = $selectedSection ? listar_items_seccion($db, (int) $selectedSection['id_seccion']) : [];
$categoriesTable = obtener_categorias_noticia($db);
$categoryFallback = $selectedSection ? obtener_categorias_fallback($db, (int) $selectedSection['id_seccion']) : [];
$openModal = $_GET['modal'] ?? '';
$editingItemId = isset($_GET['item']) ? (int) $_GET['item'] : 0;
$editingItem = $editingItemId > 0 ? obtener_item($db, $editingItemId) : null;
$editingMenuId = isset($_GET['menu']) ? (int) $_GET['menu'] : 0;
$editingSubmenuId = isset($_GET['submenu']) ? (int) $_GET['submenu'] : 0;
$editingMenu = $editingMenuId > 0 ? obtener_menu($db, $editingMenuId) : null;
$editingSubmenu = $editingSubmenuId > 0 ? obtener_submenu($db, $editingSubmenuId) : null;
$heroLines = [
    $editingItem['titulo_linea_1'] ?? '',
    $editingItem['titulo_linea_2'] ?? '',
    $editingItem['titulo_linea_3'] ?? '',
];
$activeTab = $_GET['tab'] ?? 'secciones';
$adminTab = $_GET['admin_tab'] ?? 'secciones';

if ($selectedSection && !$editingItem && $openModal === 'item') {
    $heroLines = ['', '', ''];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel CMS | Bloques del sitio</title>
    <link rel="shortcut icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --sp-primary: #0f766e;
            --sp-primary-soft: #e6fffb;
            --sp-secondary: #12324a;
            --sp-accent: #e4a72d;
            --sp-bg: #f4f7fb;
            --sp-card: #ffffff;
            --sp-border: #dbe4ef;
            --sp-text: #0f172a;
            --sp-muted: #64748b;
            --sp-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            --sp-radius: 22px;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.12), transparent 22%),
                radial-gradient(circle at top right, rgba(228, 167, 45, 0.10), transparent 20%),
                var(--sp-bg);
            color: var(--sp-text);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .layout {
            display: grid;
            grid-template-columns: 290px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #10243c 100%);
            color: #fff;
            padding: 28px 20px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            padding: 18px;
            border-radius: 24px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.08);
            margin-bottom: 28px;
        }

        .brand h1 {
            margin: 0 0 4px;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .brand p,
        .sidebar small {
            margin: 0;
            color: rgba(255,255,255,.68);
        }

        .nav-link {
            color: rgba(255,255,255,.76);
            border-radius: 16px;
            margin-bottom: 8px;
            padding: 12px 14px;
            font-weight: 600;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #fff;
            background: rgba(15, 118, 110, 0.24);
        }

        .content {
            padding: 28px;
        }

        .topbar,
        .card-panel {
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(255,255,255,.9);
            border-radius: var(--sp-radius);
            box-shadow: var(--sp-shadow);
        }

        .topbar {
            padding: 22px 24px;
            margin-bottom: 24px;
        }

        .topbar h2 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
        }

        .topbar p {
            margin: 6px 0 0;
            color: var(--sp-muted);
        }

        .card-panel {
            padding: 22px;
            margin-bottom: 24px;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .section-head h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .section-head p {
            margin: 5px 0 0;
            color: var(--sp-muted);
        }

        .kpi {
            border-radius: 18px;
            padding: 18px;
            color: #fff;
            height: 100%;
        }

        .kpi h4 {
            font-size: 2rem;
            margin: 8px 0 0;
            font-weight: 800;
        }

        .kpi-green { background: linear-gradient(135deg, #0f766e, #14b8a6); }
        .kpi-blue { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
        .kpi-gold { background: linear-gradient(135deg, #c0821f, #f0b54b); }

        .table thead th {
            white-space: nowrap;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 700;
            font-size: .8rem;
        }

        .badge-soft.success { background: #e8fbf2; color: #0f766e; }
        .badge-soft.warning { background: #fff7df; color: #a16207; }
        .badge-soft.dark { background: #eef2f7; color: #334155; }

        .thumb {
            width: 72px;
            height: 56px;
            border-radius: 12px;
            object-fit: cover;
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
        }

        .config-row {
            border: 1px dashed var(--sp-border);
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 12px;
            background: #fbfdff;
        }

        .modal-content {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 24px 64px rgba(15,23,42,.2);
        }

        .modal-header {
            border-bottom: 1px solid #eef2f7;
            padding: 18px 22px;
        }

        .modal-body {
            padding: 20px 22px;
        }

        .modal-footer {
            border-top: 1px solid #eef2f7;
            padding: 18px 22px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            border: none;
        }

        .btn-outline-secondary {
            border-color: #cbd5e1;
            color: #334155;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            min-height: 46px;
            border-color: #d8e1ea;
        }

        textarea.form-control {
            min-height: 110px;
        }

        .helper {
            font-size: .86rem;
            color: var(--sp-muted);
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
        }

        @media (max-width: 991px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: relative;
                height: auto;
            }

            .content {
                padding: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <h1>CMS Institucional</h1>
                <p>Bloques del sitio y contenido visual</p>
            </div>

            <nav class="nav flex-column">
                <a class="nav-link active" href="#secciones"><i class="bi bi-grid me-2"></i> Secciones del sitio</a>
                <a class="nav-link" href="#editor"><i class="bi bi-sliders me-2"></i> Configurar bloque</a>
                <a class="nav-link" href="#items"><i class="bi bi-collection me-2"></i> Administrar items</a>
                <a class="nav-link" href="#menus"><i class="bi bi-list-nested me-2"></i> Menus</a>
                <a class="nav-link" href="#submenus"><i class="bi bi-diagram-3 me-2"></i> Submenus</a>
                <a class="nav-link" href="#configuracion"><i class="bi bi-building-gear me-2"></i> Configuracion institucional</a>
            </nav>

            <div class="mt-4 pt-3 border-top border-light border-opacity-10">
                <small>Los menus y submenus se administran desde sus tablas reales. Los bloques del home usan <code>seccion</code>, <code>seccion_config</code> y <code>seccion_item</code>.</small>
            </div>
        </aside>

        <main class="content">
            <div class="topbar">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div>
                        <h2>Administrador de bloques</h2>
                        <p>Gestiona secciones, configuraciones, slides e items del sitio institucional desde base de datos.</p>
                    </div>
                    <?php if ($selectedSection): ?>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="index.php" class="btn btn-outline-secondary" target="_blank">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Ver sitio
                            </a>
                            <a href="admin.php?section=<?= (int) $selectedSection['id_seccion'] ?>&modal=section" class="btn btn-primary">
                                <i class="bi bi-pencil-square me-1"></i> Editar bloque actual
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4 mb-2">
                <div class="col-md-4">
                    <div class="kpi kpi-green">
                        <div>Secciones activas</div>
                        <h4><?= count($sections) ?></h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kpi kpi-blue">
                        <div>Items en la seccion actual</div>
                        <h4><?= count($sectionItems) ?></h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kpi kpi-gold">
                        <div>Configuraciones del bloque</div>
                        <h4><?= count($sectionConfigs) ?></h4>
                    </div>
                </div>
            </div>

            <section class="card-panel" id="secciones">
                <div class="section-head">
                    <div>
                        <h3>Secciones del sitio</h3>
                        <p>Listado tomado desde la tabla <code>seccion</code>.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="sectionsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Orden</th>
                                <th>Nombre interno</th>
                                <th>Nombre admin</th>
                                <th>Tipo</th>
                                <th>Visible</th>
                                <th>Items</th>
                                <th>Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sections as $section): ?>
                                <tr>
                                    <td><?= (int) $section['orden'] ?></td>
                                    <td><code><?= e($section['nombre_interno']) ?></code></td>
                                    <td><?= e($section['titulo_admin']) ?></td>
                                    <td><span class="badge-soft dark"><?= e($section['tipo_seccion']) ?></span></td>
                                    <td>
                                        <form method="post" class="m-0">
                                            <input type="hidden" name="accion" value="toggle_seccion">
                                            <input type="hidden" name="id_seccion" value="<?= (int) $section['id_seccion'] ?>">
                                            <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    role="switch"
                                                    <?= $section['visible'] === 'si' ? 'checked' : '' ?>
                                                    onchange="this.form.submit()">
                                                <label class="form-check-label small fw-semibold">
                                                    <?= $section['visible'] === 'si' ? 'Visible' : 'Oculto' ?>
                                                </label>
                                            </div>
                                        </form>
                                    </td>
                                    <td><?= (int) $section['total_items'] ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="admin.php?section=<?= (int) $section['id_seccion'] ?>" class="btn btn-sm btn-outline-secondary">
                                                Ver
                                            </a>
                                            <a href="admin.php?section=<?= (int) $section['id_seccion'] ?>&modal=section" class="btn btn-sm btn-primary">
                                                Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <?php if ($selectedSection): ?>
                <section class="card-panel" id="editor">
                    <div class="section-head">
                        <div>
                            <h3>Bloque seleccionado: <?= e($selectedSection['titulo_admin']) ?></h3>
                            <p>
                                <strong><?= e($selectedSection['nombre_interno']) ?></strong>
                                · tipo <code><?= e($selectedSection['tipo_seccion']) ?></code>
                                <?php if (!empty($selectedSection['variante'])): ?>
                                    · variante <code><?= e($selectedSection['variante']) ?></code>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="admin.php?section=<?= (int) $selectedSection['id_seccion'] ?>&modal=section" class="btn btn-primary">
                                <i class="bi bi-sliders me-1"></i> Configurar bloque
                            </a>
                            <a href="admin.php?section=<?= (int) $selectedSection['id_seccion'] ?>&tab=items&modal=item" class="btn btn-outline-secondary">
                                <i class="bi bi-plus-circle me-1"></i> Agregar item
                            </a>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-3">
                            <div class="config-row h-100">
                                <div class="helper">Visible</div>
                                <div class="fw-bold fs-5"><?= $selectedSection['visible'] === 'si' ? 'Si' : 'No' ?></div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="config-row h-100">
                                <div class="helper">Orden</div>
                                <div class="fw-bold fs-5"><?= (int) $selectedSection['orden'] ?></div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="config-row h-100">
                                <div class="helper">Configuraciones</div>
                                <div class="fw-bold fs-5"><?= count($sectionConfigs) ?></div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="config-row h-100">
                                <div class="helper">Items</div>
                                <div class="fw-bold fs-5"><?= count($sectionItems) ?></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card-panel" id="items">
                    <div class="section-head">
                        <div>
                            <h3>Items del bloque</h3>
                            <p>
                                <?php if (in_array($selectedSection['tipo_seccion'], ['hero', 'carousel'], true)): ?>
                                    Gestion de slides del carrusel principal.
                                <?php elseif ($selectedSection['tipo_seccion'] === 'news'): ?>
                                    Gestion de noticias destacadas del home.
                                <?php else: ?>
                                    Gestion de contenido asociado al bloque.
                                <?php endif; ?>
                            </p>
                        </div>
                        <a href="admin.php?section=<?= (int) $selectedSection['id_seccion'] ?>&tab=items&modal=item" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Agregar item
                        </a>
                    </div>

                    <?php if (in_array($selectedSection['tipo_seccion'], ['hero', 'carousel'], true)): ?>
                        <div class="row g-4 mb-4">
                            <?php foreach ($sectionItems as $item): ?>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border-0 shadow-sm" style="border-radius:20px; overflow:hidden;">
                                        <div style="height:180px; background:url('<?= e($item['imagen'] ?: 'assets/images/portada_1.jpg') ?>') center/cover no-repeat;"></div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <span class="badge-soft <?= $item['visible'] === 'si' ? 'success' : 'warning' ?>">
                                                        <?= $item['visible'] === 'si' ? 'Activo' : 'Oculto' ?>
                                                    </span>
                                                    <h5 class="mt-3 mb-1"><?= e(trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? ''))) ?></h5>
                                                    <small class="text-muted">Orden <?= (int) $item['orden'] ?></small>
                                                </div>
                                            </div>
                                            <p class="text-muted mb-2"><?= e($item['etiqueta']) ?></p>
                                            <div class="d-flex gap-2">
                                                <a href="admin.php?section=<?= (int) $selectedSection['id_seccion'] ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                                <form method="post" onsubmit="return confirm('¿Eliminar este slide?');">
                                                    <input type="hidden" name="accion" value="eliminar_item">
                                                    <input type="hidden" name="id_seccion" value="<?= (int) $selectedSection['id_seccion'] ?>">
                                                    <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Imagen</th>
                                    <?php if (in_array($selectedSection['tipo_seccion'], ['hero', 'carousel'], true)): ?>
                                        <th>Etiqueta</th>
                                        <th>Titulo</th>
                                    <?php elseif ($selectedSection['tipo_seccion'] === 'news'): ?>
                                        <th>Categoria</th>
                                        <th>Titulo</th>
                                        <th>Fecha</th>
                                    <?php else: ?>
                                        <th>Titulo</th>
                                        <th>Subtitulo</th>
                                    <?php endif; ?>
                                    <th>Visible</th>
                                    <th>Orden</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sectionItems as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['imagen'])): ?>
                                                <img class="thumb" src="<?= e($item['imagen']) ?>" alt="Imagen">
                                            <?php else: ?>
                                                <div class="thumb d-inline-flex align-items-center justify-content-center text-muted">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <?php if (in_array($selectedSection['tipo_seccion'], ['hero', 'carousel'], true)): ?>
                                            <td><?= e($item['etiqueta']) ?></td>
                                            <td><?= e(trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? ''))) ?></td>
                                        <?php elseif ($selectedSection['tipo_seccion'] === 'news'): ?>
                                            <td>
                                                <?php
                                                $nombreCategoria = $item['etiqueta'] ?? '';
                                                foreach ($categoriesTable as $categoria) {
                                                    if ((int) ($categoria['id_categoria'] ?? 0) === (int) ($item['id_categoria'] ?? 0)) {
                                                        $nombreCategoria = $categoria['nombre'];
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <?= e($nombreCategoria) ?>
                                            </td>
                                            <td><?= e($item['titulo']) ?></td>
                                            <td><?= e($item['fecha_publicacion']) ?></td>
                                        <?php else: ?>
                                            <td><?= e($item['titulo']) ?></td>
                                            <td><?= e($item['subtitulo']) ?></td>
                                        <?php endif; ?>

                                        <td>
                                            <span class="badge-soft <?= $item['visible'] === 'si' ? 'success' : 'warning' ?>">
                                                <?= $item['visible'] === 'si' ? 'Si' : 'No' ?>
                                            </span>
                                        </td>
                                        <td><?= (int) $item['orden'] ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="admin.php?section=<?= (int) $selectedSection['id_seccion'] ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn btn-sm btn-outline-secondary">
                                                    Editar
                                                </a>
                                                <form method="post" onsubmit="return confirm('¿Eliminar este item?');">
                                                    <input type="hidden" name="accion" value="eliminar_item">
                                                    <input type="hidden" name="id_seccion" value="<?= (int) $selectedSection['id_seccion'] ?>">
                                                    <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>

            <section class="card-panel" id="menus">
                <div class="section-head">
                    <div>
                        <h3>Menus del sitio</h3>
                        <p>Administracion directa sobre la tabla <code>menus</code>.</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="menusTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Orden</th>
                                        <th>Nombre</th>
                                        <th>URL</th>
                                        <th>Icono</th>
                                        <th>Activo</th>
                                        <th>Editar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menuRows as $menu): ?>
                                        <tr>
                                            <td><?= (int) $menu['orden'] ?></td>
                                            <td><strong><?= e($menu['nombre']) ?></strong></td>
                                            <td><code><?= e($menu['url']) ?></code></td>
                                            <td><?= e($menu['icono']) ?></td>
                                            <td>
                                                <form method="post" class="m-0">
                                                    <input type="hidden" name="accion" value="toggle_menu">
                                                    <input type="hidden" name="id_menu" value="<?= (int) $menu['id_menu'] ?>">
                                                    <input type="hidden" name="id_seccion" value="<?= (int) ($selectedSection['id_seccion'] ?? 0) ?>">
                                                    <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                                        <input class="form-check-input" type="checkbox" role="switch" <?= (int) $menu['estado'] === 1 ? 'checked' : '' ?> onchange="this.form.submit()">
                                                        <label class="form-check-label small fw-semibold"><?= (int) $menu['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <a href="admin.php?section=<?= (int) ($selectedSection['id_seccion'] ?? 0) ?>&admin_tab=menus&menu=<?= (int) $menu['id_menu'] ?>#menus" class="btn btn-sm btn-outline-secondary">Editar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="config-row h-100">
                            <h5 class="mb-3"><?= $editingMenu ? 'Editar menu' : 'Nuevo menu' ?></h5>
                            <form method="post">
                                <input type="hidden" name="accion" value="guardar_menu">
                                <input type="hidden" name="id_seccion" value="<?= (int) ($selectedSection['id_seccion'] ?? 0) ?>">
                                <input type="hidden" name="id_menu" value="<?= (int) ($editingMenu['id_menu'] ?? 0) ?>">
                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input class="form-control" type="text" name="nombre" value="<?= e($editingMenu['nombre'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">URL</label>
                                    <input class="form-control" type="text" name="url" value="<?= e($editingMenu['url'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Icono</label>
                                    <input class="form-control" type="text" name="icono" value="<?= e($editingMenu['icono'] ?? '') ?>" placeholder="bi bi-house">
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Orden</label>
                                        <input class="form-control" type="number" name="orden" min="0" value="<?= (int) ($editingMenu['orden'] ?? (count($menuRows) + 1)) ?>">
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="estado" <?= !isset($editingMenu['estado']) || (int) $editingMenu['estado'] === 1 ? 'checked' : '' ?>>
                                            <label class="form-check-label">Activo</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">Guardar menu</button>
                                    <?php if ($editingMenu): ?>
                                        <a href="admin.php?section=<?= (int) ($selectedSection['id_seccion'] ?? 0) ?>&admin_tab=menus#menus" class="btn btn-outline-secondary">Cancelar</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card-panel" id="submenus">
                <div class="section-head">
                    <div>
                        <h3>Submenus del sitio</h3>
                        <p>Administracion directa sobre la tabla <code>sub_menus</code>.</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="submenusTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Orden</th>
                                        <th>Nombre</th>
                                        <th>Menu padre</th>
                                        <th>URL</th>
                                        <th>Activo</th>
                                        <th>Editar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submenuRows as $submenu): ?>
                                        <tr>
                                            <td><?= (int) $submenu['orden'] ?></td>
                                            <td><strong><?= e($submenu['nombre']) ?></strong></td>
                                            <td><?= e($submenu['menu_padre']) ?></td>
                                            <td><code><?= e($submenu['url']) ?></code></td>
                                            <td>
                                                <form method="post" class="m-0">
                                                    <input type="hidden" name="accion" value="toggle_submenu">
                                                    <input type="hidden" name="id_sub_menu" value="<?= (int) $submenu['id_sub_menu'] ?>">
                                                    <input type="hidden" name="id_seccion" value="<?= (int) ($selectedSection['id_seccion'] ?? 0) ?>">
                                                    <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                                        <input class="form-check-input" type="checkbox" role="switch" <?= (int) $submenu['estado'] === 1 ? 'checked' : '' ?> onchange="this.form.submit()">
                                                        <label class="form-check-label small fw-semibold"><?= (int) $submenu['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <a href="admin.php?section=<?= (int) ($selectedSection['id_seccion'] ?? 0) ?>&admin_tab=submenus&submenu=<?= (int) $submenu['id_sub_menu'] ?>#submenus" class="btn btn-sm btn-outline-secondary">Editar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="config-row h-100">
                            <h5 class="mb-3"><?= $editingSubmenu ? 'Editar submenu' : 'Nuevo submenu' ?></h5>
                            <form method="post">
                                <input type="hidden" name="accion" value="guardar_submenu">
                                <input type="hidden" name="id_seccion" value="<?= (int) ($selectedSection['id_seccion'] ?? 0) ?>">
                                <input type="hidden" name="id_sub_menu" value="<?= (int) ($editingSubmenu['id_sub_menu'] ?? 0) ?>">
                                <div class="mb-3">
                                    <label class="form-label">Menu padre</label>
                                    <select class="form-select" name="id_menu" required>
                                        <option value="">Seleccione</option>
                                        <?php foreach ($menuRows as $menu): ?>
                                            <option value="<?= (int) $menu['id_menu'] ?>" <?= ((int) ($editingSubmenu['id_menu'] ?? 0) === (int) $menu['id_menu']) ? 'selected' : '' ?>>
                                                <?= e($menu['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input class="form-control" type="text" name="nombre" value="<?= e($editingSubmenu['nombre'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">URL</label>
                                    <input class="form-control" type="text" name="url" value="<?= e($editingSubmenu['url'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Icono</label>
                                    <input class="form-control" type="text" name="icono" value="<?= e($editingSubmenu['icono'] ?? '') ?>">
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Orden</label>
                                        <input class="form-control" type="number" name="orden" min="0" value="<?= (int) ($editingSubmenu['orden'] ?? 1) ?>">
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="estado" <?= !isset($editingSubmenu['estado']) || (int) $editingSubmenu['estado'] === 1 ? 'checked' : '' ?>>
                                            <label class="form-check-label">Activo</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">Guardar submenu</button>
                                    <?php if ($editingSubmenu): ?>
                                        <a href="admin.php?section=<?= (int) ($selectedSection['id_seccion'] ?? 0) ?>&admin_tab=submenus#submenus" class="btn btn-outline-secondary">Cancelar</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card-panel" id="configuracion">
                <div class="section-head">
                    <div>
                        <h3>Configuracion general del sitio</h3>
                        <p>Datos institucionales desde la tabla <code>institucion</code>.</p>
                    </div>
                </div>
                <?php if ($institution): ?>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="guardar_institucion">
                        <input type="hidden" name="id_seccion" value="<?= (int) ($selectedSection['id_seccion'] ?? 0) ?>">
                        <div class="row g-4">
                            <div class="col-xl-8">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre del sitio</label>
                                        <input class="form-control" type="text" name="nombre" value="<?= e($institution['nombre'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Correo contacto</label>
                                        <input class="form-control" type="email" name="email" value="<?= e($institution['email'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Telefono</label>
                                        <input class="form-control" type="text" name="telefono" value="<?= e($institution['telefono'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Direccion</label>
                                        <input class="form-control" type="text" name="direccion" value="<?= e($institution['direccion'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Color principal</label>
                                        <input class="form-control" type="text" name="color_primario" value="<?= e($institution['color_primario'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Color secundario</label>
                                        <input class="form-control" type="text" name="color_secundario" value="<?= e($institution['color_secundario'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Facebook</label>
                                        <input class="form-control" type="text" name="facebook" value="<?= e($institution['facebook'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Instagram</label>
                                        <input class="form-control" type="text" name="instagram" value="<?= e($institution['instagram'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Logo</label>
                                        <input class="form-control" type="file" name="logo_header" accept="image/*">
                                        <?php if (!empty($institution['logo_header'])): ?>
                                            <div class="helper mt-2">Actual: <a target="_blank" href="<?= e($institution['logo_header']) ?>"><?= e($institution['logo_header']) ?></a></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Favicon</label>
                                        <input class="form-control" type="file" name="favicon" accept="image/*,.ico">
                                        <?php if (!empty($institution['favicon'])): ?>
                                            <div class="helper mt-2">Actual: <a target="_blank" href="<?= e($institution['favicon']) ?>"><?= e($institution['favicon']) ?></a></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="config-row h-100">
                                    <h5 class="mb-3">Vista rapida institucional</h5>
                                    <div class="mb-3">
                                        <strong><?= e($institution['nombre'] ?? '') ?></strong>
                                        <div class="helper"><?= e($institution['email'] ?? '') ?></div>
                                        <div class="helper"><?= e($institution['telefono'] ?? '') ?></div>
                                    </div>
                                    <div class="d-flex gap-3 mb-4">
                                        <div style="width:54px;height:54px;border-radius:16px;background:<?= e($institution['color_primario'] ?? '#2563EB') ?>;"></div>
                                        <div style="width:54px;height:54px;border-radius:16px;background:<?= e($institution['color_secundario'] ?? '#E9A629') ?>;"></div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Guardar configuracion</button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <?php if ($selectedSection): ?>
        <div class="modal fade" id="sectionModal" tabindex="-1" aria-labelledby="sectionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form method="post">
                        <input type="hidden" name="accion" value="guardar_seccion">
                        <input type="hidden" name="id_seccion" value="<?= (int) $selectedSection['id_seccion'] ?>">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="sectionModalLabel">Editar bloque: <?= e($selectedSection['titulo_admin']) ?></h5>
                                <div class="helper"><?= e($selectedSection['nombre_interno']) ?> · <?= e($selectedSection['tipo_seccion']) ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Visible</label>
                                    <select class="form-select" name="visible">
                                        <option value="si" <?= $selectedSection['visible'] === 'si' ? 'selected' : '' ?>>Si</option>
                                        <option value="no" <?= $selectedSection['visible'] === 'no' ? 'selected' : '' ?>>No</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Orden</label>
                                    <input class="form-control" type="number" name="orden" min="1" value="<?= (int) $selectedSection['orden'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Variante</label>
                                    <input class="form-control" type="text" value="<?= e($selectedSection['variante']) ?>" disabled>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-1">Configuraciones del bloque</h6>
                                    <div class="helper">Se guardan en <code>seccion_config</code>.</div>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="addConfigRow">
                                    <i class="bi bi-plus-circle me-1"></i> Agregar configuracion
                                </button>
                            </div>

                            <div id="configRows">
                                <?php if ($sectionConfigs): ?>
                                    <?php foreach ($sectionConfigs as $config): ?>
                                        <div class="config-row">
                                            <div class="row g-3 align-items-end">
                                                <div class="col-md-4">
                                                    <label class="form-label">Clave</label>
                                                    <input type="text" name="config_key[]" class="form-control" value="<?= e($config['clave']) ?>">
                                                </div>
                                                <div class="col-md-7">
                                                    <label class="form-label">Valor</label>
                                                    <input type="text" name="config_value[]" class="form-control" value="<?= e($config['valor']) ?>">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-outline-danger w-100 remove-config-row">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="config-row">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label">Clave</label>
                                                <input type="text" name="config_key[]" class="form-control" placeholder="cantidad_items">
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label">Valor</label>
                                                <input type="text" name="config_value[]" class="form-control" placeholder="4">
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-outline-danger w-100 remove-config-row">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="guardar_item">
                        <input type="hidden" name="id_seccion" value="<?= (int) $selectedSection['id_seccion'] ?>">
                        <input type="hidden" name="id_item" value="<?= (int) ($editingItem['id_item'] ?? 0) ?>">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="itemModalLabel">
                                    <?= $editingItem ? 'Editar item' : 'Agregar item' ?>
                                </h5>
                                <div class="helper"><?= e($selectedSection['titulo_admin']) ?> · <?= e($selectedSection['tipo_seccion']) ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (in_array($selectedSection['tipo_seccion'], ['hero', 'carousel'], true)): ?>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Etiqueta</label>
                                        <input type="text" class="form-control" name="etiqueta" value="<?= e($editingItem['etiqueta'] ?? '') ?>" placeholder="Colegio San Pablo">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Titulo linea 1</label>
                                        <input type="text" class="form-control" name="titulo_linea_1" value="<?= e($heroLines[0]) ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Titulo linea 2</label>
                                        <input type="text" class="form-control" name="titulo_linea_2" value="<?= e($heroLines[1]) ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Titulo linea 3</label>
                                        <input type="text" class="form-control" name="titulo_linea_3" value="<?= e($heroLines[2]) ?>">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Descripcion</label>
                                        <textarea class="form-control" name="descripcion"><?= e($editingItem['descripcion'] ?? '') ?></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Boton 1 texto</label>
                                        <input type="text" class="form-control" name="boton_1_texto" value="<?= e($editingItem['boton_1_texto'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Boton 1 URL</label>
                                        <input type="text" class="form-control" name="boton_1_url" value="<?= e($editingItem['boton_1_url'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Boton 2 texto</label>
                                        <input type="text" class="form-control" name="boton_2_texto" value="<?= e($editingItem['boton_2_texto'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Boton 2 URL</label>
                                        <input type="text" class="form-control" name="boton_2_url" value="<?= e($editingItem['boton_2_url'] ?? '') ?>">
                                    </div>
                                </div>
                            <?php elseif ($selectedSection['tipo_seccion'] === 'news'): ?>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Categoria</label>
                                        <?php if ($categoriesTable): ?>
                                            <select class="form-select" name="id_categoria">
                                                <option value="">Seleccione una categoria</option>
                                                <?php foreach ($categoriesTable as $category): ?>
                                                    <option value="<?= (int) $category['id_categoria'] ?>" <?= ((int) ($editingItem['id_categoria'] ?? 0) === (int) $category['id_categoria']) ? 'selected' : '' ?>>
                                                        <?= e($category['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <input list="newsCategories" type="text" class="form-control" name="etiqueta" value="<?= e($editingItem['etiqueta'] ?? '') ?>" placeholder="DEPORTE">
                                            <datalist id="newsCategories">
                                                <?php foreach ($categoryFallback as $category): ?>
                                                    <option value="<?= e($category) ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                            <div class="helper mt-1">No se encontro la tabla <code>categoria_noticia</code>, se usa la etiqueta del item como categoria visible.</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Titulo</label>
                                        <input type="text" class="form-control" name="titulo" value="<?= e($editingItem['titulo'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Etiqueta visual</label>
                                        <input type="text" class="form-control" name="etiqueta" value="<?= e($editingItem['etiqueta'] ?? '') ?>" placeholder="DEPORTE">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Descripcion</label>
                                        <textarea class="form-control" name="descripcion"><?= e($editingItem['descripcion'] ?? '') ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Fecha de publicacion</label>
                                        <input type="date" class="form-control" name="fecha_publicacion" value="<?= e($editingItem['fecha_publicacion'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Texto boton</label>
                                        <input type="text" class="form-control" name="boton_1_texto" value="<?= e($editingItem['boton_1_texto'] ?? 'Leer más') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">URL boton</label>
                                        <input type="text" class="form-control" name="boton_1_url" value="<?= e($editingItem['boton_1_url'] ?? '#') ?>">
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Etiqueta</label>
                                        <input type="text" class="form-control" name="etiqueta" value="<?= e($editingItem['etiqueta'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Titulo</label>
                                        <input type="text" class="form-control" name="titulo" value="<?= e($editingItem['titulo'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Subtitulo</label>
                                        <input type="text" class="form-control" name="subtitulo" value="<?= e($editingItem['subtitulo'] ?? '') ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Descripcion</label>
                                        <textarea class="form-control" name="descripcion"><?= e($editingItem['descripcion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Imagen</label>
                                    <input type="file" class="form-control" name="imagen" accept="image/*">
                                    <?php if (!empty($editingItem['imagen'])): ?>
                                        <div class="helper mt-2">Actual: <a href="<?= e($editingItem['imagen']) ?>" target="_blank"><?= e($editingItem['imagen']) ?></a></div>
                                    <?php endif; ?>
                                </div>

                                <?php if (in_array($selectedSection['tipo_seccion'], ['hero', 'carousel'], true)): ?>
                                    <div class="col-md-6">
                                        <label class="form-label">Imagen mobile</label>
                                        <input type="file" class="form-control" name="imagen_mobile" accept="image/*">
                                        <?php if (!empty($editingItem['imagen_mobile'])): ?>
                                            <div class="helper mt-2">Actual: <a href="<?= e($editingItem['imagen_mobile']) ?>" target="_blank"><?= e($editingItem['imagen_mobile']) ?></a></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="col-md-3">
                                    <label class="form-label">Visible</label>
                                    <select class="form-select" name="visible">
                                        <option value="si" <?= (($editingItem['visible'] ?? 'si') === 'si') ? 'selected' : '' ?>>Si</option>
                                        <option value="no" <?= (($editingItem['visible'] ?? '') === 'no') ? 'selected' : '' ?>>No</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Orden</label>
                                    <input type="number" class="form-control" name="orden" min="1" value="<?= (int) ($editingItem['orden'] ?? (count($sectionItems) + 1)) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary"><?= $editingItem ? 'Guardar item' : 'Agregar item' ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <template id="configRowTemplate">
        <div class="config-row">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Clave</label>
                    <input type="text" name="config_key[]" class="form-control" placeholder="texto_boton">
                </div>
                <div class="col-md-7">
                    <label class="form-label">Valor</label>
                    <input type="text" name="config_value[]" class="form-control" placeholder="Ver todas las noticias">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger w-100 remove-config-row">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            $('#sectionsTable').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
                }
            });

            if ($('#itemsTable').length) {
                $('#itemsTable').DataTable({
                    pageLength: 10,
                    order: [[4, 'asc']],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
                    }
                });
            }

            if ($('#menusTable').length) {
                $('#menusTable').DataTable({
                    pageLength: 10,
                    order: [[0, 'asc']],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
                    }
                });
            }

            if ($('#submenusTable').length) {
                $('#submenusTable').DataTable({
                    pageLength: 10,
                    order: [[0, 'asc']],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
                    }
                });
            }

            $('#addConfigRow').on('click', function () {
                const tpl = document.getElementById('configRowTemplate');
                const clone = tpl.content.cloneNode(true);
                document.getElementById('configRows').appendChild(clone);
            });

            $(document).on('click', '.remove-config-row', function () {
                const rows = document.querySelectorAll('#configRows .config-row');
                if (rows.length === 1) {
                    rows[0].querySelectorAll('input').forEach(function (input) {
                        input.value = '';
                    });
                    return;
                }

                this.closest('.config-row').remove();
            });

            const openModal = <?= json_encode($openModal, JSON_UNESCAPED_UNICODE) ?>;
            if (openModal === 'section') {
                const modal = new bootstrap.Modal(document.getElementById('sectionModal'));
                modal.show();
            }
            if (openModal === 'item') {
                const modal = new bootstrap.Modal(document.getElementById('itemModal'));
                modal.show();
            }
        });
    </script>
</body>
</html>
