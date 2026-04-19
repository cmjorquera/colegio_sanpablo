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

function obtener_categorias_noticia(mysqli $db): array
{
    if (!table_exists($db, 'categoria_noticia')) {
        return [];
    }

    $result = $db->query('SELECT * FROM categoria_noticia ORDER BY 1 ASC');
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

function separar_titulo_hero(?string $titulo): array
{
    $parts = preg_split('/\R/', (string) $titulo) ?: [];
    $parts = array_values(array_filter(array_map('trim', $parts), static fn($value) => $value !== ''));

    return [
        $parts[0] ?? '',
        $parts[1] ?? '',
        $parts[2] ?? '',
    ];
}

function unir_titulo_hero(array $lineas): string
{
    $lineas = array_values(array_filter(array_map(static function ($value) {
        return trim((string) $value);
    }, $lineas), static fn($value) => $value !== ''));

    return implode("\n", $lineas);
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
    $visible = (($post['visible'] ?? 'no') === 'si') ? 'si' : 'no';
    $orden = max(1, (int) ($post['orden'] ?? 1));

    if (in_array($tipoSeccion, ['hero', 'carousel'], true)) {
        $titulo = unir_titulo_hero([
            $post['titulo_linea_1'] ?? '',
            $post['titulo_linea_2'] ?? '',
            $post['titulo_linea_3'] ?? '',
        ]);
    }

    if ($tipoSeccion === 'news') {
        $etiqueta = trim((string) ($post['categoria'] ?? $etiqueta));
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
                SET titulo = ?, subtitulo = ?, descripcion = ?, imagen = ?, imagen_mobile = ?,
                    boton_1_texto = ?, boton_1_url = ?, boton_2_texto = ?, boton_2_url = ?,
                    etiqueta = ?, fecha_publicacion = ?, visible = ?, orden = ?
                WHERE id_item = ? AND id_seccion = ?';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No fue posible preparar la actualizacion del item.');
        }
        $stmt->bind_param(
            'ssssssssssssiii',
            $titulo,
            $subtitulo,
            $descripcion,
            $imagen,
            $imagenMobile,
            $boton1Texto,
            $boton1Url,
            $boton2Texto,
            $boton2Url,
            $etiqueta,
            $fechaPublicacion,
            $visible,
            $orden,
            $idItem,
            $idSeccion
        );
    } else {
        $sql = 'INSERT INTO seccion_item
                (id_seccion, titulo, subtitulo, descripcion, imagen, imagen_mobile,
                 boton_1_texto, boton_1_url, boton_2_texto, boton_2_url, etiqueta,
                 fecha_publicacion, visible, orden)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No fue posible preparar la creacion del item.');
        }
        $stmt->bind_param(
            'issssssssssssi',
            $idSeccion,
            $titulo,
            $subtitulo,
            $descripcion,
            $imagen,
            $imagenMobile,
            $boton1Texto,
            $boton1Url,
            $boton2Texto,
            $boton2Url,
            $etiqueta,
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
        if ($accion === 'guardar_seccion' && $sectionId > 0) {
            editar_seccion($db, $sectionId, $_POST);
            set_flash('success', 'La seccion fue actualizada correctamente.');
            header('Location: admin.php?section=' . $sectionId);
            exit;
        }

        if ($accion === 'guardar_item' && $sectionId > 0) {
            $section = obtener_seccion($db, $sectionId);
            if (!$section) {
                throw new RuntimeException('La seccion indicada no existe.');
            }

            editar_item($db, $section, $_POST);
            set_flash('success', 'El item fue guardado correctamente.');
            header('Location: admin.php?section=' . $sectionId . '&tab=items');
            exit;
        }

        if ($accion === 'eliminar_item') {
            $idItem = (int) ($_POST['id_item'] ?? 0);
            eliminar_item($db, $idItem);
            set_flash('success', 'El item fue eliminado correctamente.');
            header('Location: admin.php?section=' . $sectionId . '&tab=items');
            exit;
        }
    } catch (Throwable $e) {
        set_flash('danger', $e->getMessage());
        header('Location: admin.php' . ($sectionId > 0 ? '?section=' . $sectionId : ''));
        exit;
    }
}

$flash = get_flash();
$sections = listar_secciones($db, $idInstitucion);
$selectedSectionId = isset($_GET['section']) ? (int) $_GET['section'] : (isset($sections[0]['id_seccion']) ? (int) $sections[0]['id_seccion'] : 0);
$selectedSection = $selectedSectionId > 0 ? obtener_seccion($db, $selectedSectionId) : null;
$sectionConfigs = $selectedSection ? obtener_configs_seccion($db, (int) $selectedSection['id_seccion']) : [];
$sectionItems = $selectedSection ? listar_items_seccion($db, (int) $selectedSection['id_seccion']) : [];
$categoriesTable = obtener_categorias_noticia($db);
$categoryFallback = $selectedSection ? obtener_categorias_fallback($db, (int) $selectedSection['id_seccion']) : [];
$openModal = $_GET['modal'] ?? '';
$editingItemId = isset($_GET['item']) ? (int) $_GET['item'] : 0;
$editingItem = $editingItemId > 0 ? obtener_item($db, $editingItemId) : null;
$heroLines = separar_titulo_hero($editingItem['titulo'] ?? '');
$activeTab = $_GET['tab'] ?? 'secciones';

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
            </nav>

            <div class="mt-4 pt-3 border-top border-light border-opacity-10">
                <small>No se modifica la logica de menus ni submenus. Este panel trabaja solo con bloques definidos en <code>seccion</code>.</small>
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
                                        <span class="badge-soft <?= $section['visible'] === 'si' ? 'success' : 'warning' ?>">
                                            <?= $section['visible'] === 'si' ? 'Si' : 'No' ?>
                                        </span>
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
                                            <td><?= nl2br(e($item['titulo'])) ?></td>
                                        <?php elseif ($selectedSection['tipo_seccion'] === 'news'): ?>
                                            <td><?= e($item['etiqueta']) ?></td>
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
                                            <select class="form-select" name="categoria">
                                                <option value="">Seleccione una categoria</option>
                                                <?php foreach ($categoriesTable as $category): ?>
                                                    <?php
                                                    $categoryName = '';
                                                    foreach ($category as $column => $value) {
                                                        if (stripos((string) $column, 'nombre') !== false || stripos((string) $column, 'titulo') !== false || stripos((string) $column, 'categoria') !== false) {
                                                            $categoryName = (string) $value;
                                                            break;
                                                        }
                                                    }
                                                    $categoryName = $categoryName !== '' ? $categoryName : (string) reset($category);
                                                    ?>
                                                    <option value="<?= e($categoryName) ?>" <?= ($editingItem['etiqueta'] ?? '') === $categoryName ? 'selected' : '' ?>>
                                                        <?= e($categoryName) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <input list="newsCategories" type="text" class="form-control" name="categoria" value="<?= e($editingItem['etiqueta'] ?? '') ?>" placeholder="DEPORTE">
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
