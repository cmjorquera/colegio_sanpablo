<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_permissions.php';
require_once __DIR__ . '/includes/admin_layout.php';

function admin_com_slug(string $text): string
{
    $text = trim($text);
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim((string) $text, '-');

    return $text !== '' ? $text : 'comunicado';
}

function admin_com_redirect(array $params = []): void
{
    $base = 'admin_comunicados.php';
    if ($params) {
        $base .= '?' . http_build_query($params);
    }
    cms_redirect($base);
}

function admin_com_safe_file(?string $archivo): array
{
    $archivo = trim((string) $archivo);
    if ($archivo === '') {
        return ['', false];
    }

    $archivo = str_replace('\\', '/', $archivo);
    if (str_contains($archivo, '..') || !str_starts_with($archivo, 'uploads/comunicados/')) {
        return ['', false];
    }

    return [$archivo, is_file(__DIR__ . '/' . $archivo)];
}

function admin_com_delete_file(?string $archivo): void
{
    [$safePath, $exists] = admin_com_safe_file($archivo);
    if ($safePath !== '' && $exists) {
        @unlink(__DIR__ . '/' . $safePath);
    }
}

function admin_com_upload_file(string $field, string $title, ?string $currentFile = null, bool $required = false): string
{
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
        if ($required) {
            throw new RuntimeException('Debes adjuntar un archivo PDF o imagen.');
        }
        return trim((string) $currentFile);
    }

    $file = $_FILES[$field];
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            throw new RuntimeException('Debes adjuntar un archivo PDF o imagen.');
        }
        return trim((string) $currentFile);
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir el archivo. Intenta nuevamente.');
    }

    $originalName = (string) ($file['name'] ?? '');
    $tmpName = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Formato no permitido. Usa PDF, JPG, JPEG, PNG o WEBP.');
    }

    // Defensa adicional compartida con includes/upload_helpers.php: bloquea nombres con
    // doble extension (ej. "circular.php.pdf") y aplica el limite de tamano de adjuntos.
    cms_validar_nombre_archivo($originalName);
    if ($size > CMS_UPLOAD_MAX_ADJUNTO_BYTES) {
        throw new RuntimeException('El archivo supera el tamano maximo permitido.');
    }

    if (function_exists('finfo_open')) {
        $allowedMime = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
        ];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? (string) finfo_file($finfo, $tmpName) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        if ($mime !== '' && !in_array($mime, $allowedMime[$extension] ?? [], true)) {
            throw new RuntimeException('El tipo de archivo no coincide con el formato permitido.');
        }
    }

    $uploadDir = __DIR__ . '/uploads/comunicados';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('No se pudo crear la carpeta de comunicados.');
    }

    $filename = admin_com_slug($title) . '-' . date('YmdHis') . '.' . $extension;
    $destination = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('No se pudo guardar el archivo subido.');
    }

    if ($currentFile) {
        admin_com_delete_file($currentFile);
    }

    return 'uploads/comunicados/' . $filename;
}

function admin_com_category_exists(mysqli $db, int $institutionId, int $categoryId): bool
{
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM comunicado_categoria
        WHERE id_categoria_comunicado = ?
          AND id_institucion = ?
          AND visible = 1
    ");
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar la validacion de categoria.');
    }
    $stmt->bind_param('ii', $categoryId, $institutionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result ? (int) $result->fetch_column() > 0 : false;
    $stmt->close();

    return $exists;
}

$db = cms_get_connection();
$institutionId = cms_get_institution_id($db);
$site = cms_get_site_data($db);
$institution = $site['institution'];

try {
    admin_requerir_permiso('comunicados', 'ver');
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
    cms_redirect('admin.php?panel=dashboard');
}
$canCreateCom = admin_tiene_permiso('comunicados', 'crear');
$canEditCom = admin_tiene_permiso('comunicados', 'editar');
$canDeleteCom = admin_tiene_permiso('comunicados', 'eliminar');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string) ($_POST['accion'] ?? '');

        if ($action === 'guardar_comunicado') {
            $id = (int) ($_POST['id_comunicado'] ?? 0);
            admin_requerir_permiso('comunicados', $id > 0 ? 'editar' : 'crear');
            $title = trim((string) ($_POST['titulo'] ?? ''));
            $description = trim((string) ($_POST['descripcion_corta'] ?? ''));
            $categoryId = (int) ($_POST['id_categoria_comunicado'] ?? 0);
            $publishDate = trim((string) ($_POST['fecha_publicacion'] ?? ''));
            $state = (string) ($_POST['estado'] ?? 'borrador');
            $visible = isset($_POST['visible']) ? 1 : 0;
            $order = max(0, (int) ($_POST['orden'] ?? 0));

            if ($title === '') {
                throw new RuntimeException('El titulo es obligatorio.');
            }
            if ($categoryId <= 0 || !admin_com_category_exists($db, $institutionId, $categoryId)) {
                throw new RuntimeException('Selecciona una categoria valida.');
            }
            if ($publishDate === '') {
                throw new RuntimeException('La fecha de publicacion es obligatoria.');
            }
            if (!in_array($state, ['borrador', 'publicado', 'oculto'], true)) {
                $state = 'borrador';
            }

            $current = null;
            if ($id > 0) {
                $stmtCurrent = $db->prepare("
                    SELECT *
                    FROM comunicado
                    WHERE id_comunicado = ?
                      AND id_institucion = ?
                    LIMIT 1
                ");
                if (!$stmtCurrent) {
                    throw new RuntimeException('No se pudo preparar la consulta del comunicado.');
                }
                $stmtCurrent->bind_param('ii', $id, $institutionId);
                $stmtCurrent->execute();
                $resultCurrent = $stmtCurrent->get_result();
                $current = $resultCurrent ? $resultCurrent->fetch_assoc() : null;
                $stmtCurrent->close();
                if (!$current) {
                    throw new RuntimeException('No se encontro el comunicado a editar.');
                }
            }

            $archivo = admin_com_upload_file('archivo', $title, $current['archivo'] ?? null, $id <= 0);
            $slug = admin_com_slug($title);

            if ($id > 0) {
                $stmtUpdate = $db->prepare("
                    UPDATE comunicado
                    SET id_categoria_comunicado = ?,
                        titulo = ?,
                        slug = ?,
                        descripcion_corta = ?,
                        archivo = ?,
                        fecha_publicacion = ?,
                        estado = ?,
                        visible = ?,
                        orden = ?,
                        actualizado_en = NOW()
                    WHERE id_comunicado = ?
                      AND id_institucion = ?
                ");
                if (!$stmtUpdate) {
                    throw new RuntimeException('No se pudo preparar la actualizacion del comunicado.');
                }
                $stmtUpdate->bind_param(
                    'issssssiiii',
                    $categoryId,
                    $title,
                    $slug,
                    $description,
                    $archivo,
                    $publishDate,
                    $state,
                    $visible,
                    $order,
                    $id,
                    $institutionId
                );
                $stmtUpdate->execute();
                $stmtUpdate->close();
                cms_set_flash('success', 'El comunicado fue actualizado correctamente.');
            } else {
                $stmtInsert = $db->prepare("
                    INSERT INTO comunicado (
                        id_institucion,
                        id_categoria_comunicado,
                        titulo,
                        slug,
                        descripcion_corta,
                        archivo,
                        fecha_publicacion,
                        estado,
                        visible,
                        orden,
                        creado_en,
                        actualizado_en
                    ) VALUES (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        NOW(),
                        NOW()
                    )
                ");
                if (!$stmtInsert) {
                    throw new RuntimeException('No se pudo preparar la creacion del comunicado.');
                }
                $stmtInsert->bind_param(
                    'iissssssii',
                    $institutionId,
                    $categoryId,
                    $title,
                    $slug,
                    $description,
                    $archivo,
                    $publishDate,
                    $state,
                    $visible,
                    $order
                );
                $stmtInsert->execute();
                $stmtInsert->close();
                cms_set_flash('success', 'El comunicado fue creado correctamente.');
            }

            admin_com_redirect();
        }

        if ($action === 'toggle_comunicado') {
            admin_requerir_permiso('comunicados', 'editar');
            $id = (int) ($_POST['id_comunicado'] ?? 0);
            $stmt = $db->prepare("
                SELECT estado, visible
                FROM comunicado
                WHERE id_comunicado = ?
                  AND id_institucion = ?
                LIMIT 1
            ");
            if (!$stmt) {
                throw new RuntimeException('No se pudo preparar la consulta del comunicado.');
            }
            $stmt->bind_param('ii', $id, $institutionId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            if (!$row) {
                throw new RuntimeException('No se encontro el comunicado.');
            }

            $isActive = ($row['estado'] ?? '') === 'publicado' && (int) ($row['visible'] ?? 0) === 1;
            $nextState = $isActive ? 'oculto' : 'publicado';
            $nextVisible = $isActive ? 0 : 1;

            $stmtUpdate = $db->prepare("
                UPDATE comunicado
                SET estado = ?,
                    visible = ?,
                    actualizado_en = NOW()
                WHERE id_comunicado = ?
                  AND id_institucion = ?
            ");
            if (!$stmtUpdate) {
                throw new RuntimeException('No se pudo preparar el cambio de estado.');
            }
            $stmtUpdate->bind_param('siii', $nextState, $nextVisible, $id, $institutionId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
            cms_set_flash('success', $isActive ? 'El comunicado fue inactivado.' : 'El comunicado fue activado.');
            admin_com_redirect();
        }

        if ($action === 'eliminar_comunicado') {
            admin_requerir_permiso('comunicados', 'eliminar');
            $id = (int) ($_POST['id_comunicado'] ?? 0);
            $stmtUpdate = $db->prepare("
                UPDATE comunicado
                SET estado = 'oculto',
                    visible = 0,
                    actualizado_en = NOW()
                WHERE id_comunicado = ?
                  AND id_institucion = ?
            ");
            if (!$stmtUpdate) {
                throw new RuntimeException('No se pudo preparar la accion de ocultar comunicado.');
            }
            $stmtUpdate->bind_param('ii', $id, $institutionId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
            cms_set_flash('success', 'El comunicado fue ocultado.');
            admin_com_redirect();
        }
    }
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
    admin_com_redirect();
}

$filterQ = trim((string) ($_GET['q'] ?? ''));
$filterCategory = max(0, (int) ($_GET['categoria'] ?? 0));
$filterState = trim((string) ($_GET['estado'] ?? ''));

$stmtCategories = $db->prepare("
    SELECT id_categoria_comunicado, nombre, color
    FROM comunicado_categoria
    WHERE id_institucion = ?
      AND visible = 1
    ORDER BY orden ASC, nombre ASC
");
if (!$stmtCategories) {
    throw new RuntimeException('No se pudo preparar la consulta de categorias.');
}
$stmtCategories->bind_param('i', $institutionId);
$stmtCategories->execute();
$resultCategories = $stmtCategories->get_result();
$categories = $resultCategories ? $resultCategories->fetch_all(MYSQLI_ASSOC) : [];
$stmtCategories->close();

$where = ['c.id_institucion = ?'];
$types = 'i';
$values = [$institutionId];

if ($filterQ !== '') {
    $where[] = '(c.titulo LIKE ? OR c.descripcion_corta LIKE ?)';
    $types .= 'ss';
    $likeFilter = '%' . $filterQ . '%';
    $values[] = $likeFilter;
    $values[] = $likeFilter;
}
if ($filterCategory > 0) {
    $where[] = 'c.id_categoria_comunicado = ?';
    $types .= 'i';
    $values[] = $filterCategory;
}
if (in_array($filterState, ['borrador', 'publicado', 'oculto'], true)) {
    $where[] = 'c.estado = ?';
    $types .= 's';
    $values[] = $filterState;
}

$stmtList = $db->prepare("
    SELECT
        c.id_comunicado,
        c.id_categoria_comunicado,
        c.titulo,
        c.slug,
        c.descripcion_corta,
        c.archivo,
        c.fecha_publicacion,
        c.estado,
        c.visible,
        c.orden,
        cc.nombre AS categoria_nombre,
        cc.color AS categoria_color
    FROM comunicado c
    INNER JOIN comunicado_categoria cc ON cc.id_categoria_comunicado = c.id_categoria_comunicado
    WHERE " . implode(' AND ', $where) . "
    ORDER BY c.fecha_publicacion DESC, c.orden ASC, c.id_comunicado DESC
");
if (!$stmtList) {
    throw new RuntimeException('No se pudo preparar el listado de comunicados.');
}
$stmtList->bind_param($types, ...$values);
$stmtList->execute();
$resultList = $stmtList->get_result();
$comunicados = $resultList ? $resultList->fetch_all(MYSQLI_ASSOC) : [];
$stmtList->close();

$flash = cms_get_flash();
$adminName = trim((string) ($_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador'));
$headerActions = '
    <a class="btn btn-outline-secondary" href="comunicados.php" target="_blank" rel="noopener">
        <i class="bi bi-eye me-1"></i> Visualizar
    </a>
    ' . ($canCreateCom ? '<button class="btn btn-premium" type="button" data-com-open><i class="bi bi-plus-lg me-1"></i> Nuevo comunicado</button>' : '') . '
';

admin_render_layout_start([
    'title' => 'Comunicados | CMS',
    'page_title' => 'Comunicados',
    'active_panel' => 'comunicados',
    'institution_name' => $institution['nombre'] ?? 'Colegio San Pablo',
    'institution_short_name' => $institution['nombre_corto'] ?? ($institution['nombre'] ?? 'San Pablo'),
    'admin_name' => $adminName,
    'header_actions' => $headerActions,
    'color_primario' => $institution['color_primario'] ?? null,
    'color_secundario' => $institution['color_secundario'] ?? null,
    'color_terciario' => $institution['color_terciario'] ?? null,
    'color_cuaternario' => $institution['color_cuaternario'] ?? null,
    'extra_head' => '<link rel="stylesheet" href="assets/css/admin_comunicados.css">',
]);
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= cms_e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= cms_e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<section class="com-admin-card">
    <div class="com-admin-head">
        <div>
            <h1>Comunicados</h1>
            <p>Administra avisos, circulares y documentos para la comunidad educativa.</p>
        </div>
        <?php if ($canCreateCom): ?>
            <button class="btn btn-premium" type="button" data-com-open>
                <i class="bi bi-plus-lg me-1"></i> Nuevo comunicado
            </button>
        <?php endif; ?>
    </div>

    <form class="com-filter" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label fw-semibold" for="filterQ">Buscar por titulo</label>
                <input class="form-control" id="filterQ" name="q" type="search" value="<?= cms_e($filterQ) ?>" placeholder="Ej: reunion, circular">
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-semibold" for="filterCategory">Categoria</label>
                <select class="form-select" id="filterCategory" name="categoria">
                    <option value="0">Todas</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id_categoria_comunicado'] ?>" <?= $filterCategory === (int) $category['id_categoria_comunicado'] ? 'selected' : '' ?>>
                            <?= cms_e($category['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label fw-semibold" for="filterState">Estado</label>
                <select class="form-select" id="filterState" name="estado">
                    <option value="">Todos</option>
                    <option value="publicado" <?= $filterState === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                    <option value="borrador" <?= $filterState === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                    <option value="oculto" <?= $filterState === 'oculto' ? 'selected' : '' ?>>Oculto</option>
                </select>
            </div>
            <div class="col-lg-2 d-grid">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bi bi-search me-1"></i> Filtrar
                </button>
            </div>
        </div>
    </form>

    <div class="com-table-wrap">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Titulo</th>
                        <th>Categoria</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Visible</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comunicados as $item): ?>
                        <?php
                        [$filePath, $fileExists] = admin_com_safe_file($item['archivo'] ?? '');
                        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                        $fileIcon = $extension === 'pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image';
                        $isActive = ($item['estado'] ?? '') === 'publicado' && (int) ($item['visible'] ?? 0) === 1;
                        $payload = [
                            'id_comunicado' => (int) $item['id_comunicado'],
                            'titulo' => (string) $item['titulo'],
                            'descripcion_corta' => (string) ($item['descripcion_corta'] ?? ''),
                            'id_categoria_comunicado' => (int) $item['id_categoria_comunicado'],
                            'fecha_publicacion' => (string) $item['fecha_publicacion'],
                            'estado' => (string) $item['estado'],
                            'visible' => (int) $item['visible'],
                            'orden' => (int) $item['orden'],
                            'archivo' => (string) ($item['archivo'] ?? ''),
                        ];
                        ?>
                        <tr>
                            <td>
                                <div class="com-file-thumb" title="<?= cms_e($extension ?: 'archivo') ?>">
                                    <i class="bi <?= cms_e($fileIcon) ?>"></i>
                                </div>
                            </td>
                            <td>
                                <div class="com-title"><?= cms_e($item['titulo']) ?></div>
                                <?php if (trim((string) ($item['descripcion_corta'] ?? '')) !== ''): ?>
                                    <div class="com-desc"><?= cms_e($item['descripcion_corta']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-soft dark">
                                    <?= cms_e($item['categoria_nombre']) ?>
                                </span>
                            </td>
                            <td><?= cms_e(date('d-m-Y', strtotime((string) $item['fecha_publicacion']))) ?></td>
                            <td>
                                <span class="badge-soft <?= $item['estado'] === 'publicado' ? 'success' : ($item['estado'] === 'borrador' ? 'warning' : 'dark') ?>">
                                    <?= cms_e(ucfirst((string) $item['estado'])) ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" class="com-visible-form js-com-visible-form" data-confirm-title="<?= $isActive ? 'Inactivar comunicado' : 'Activar comunicado' ?>" data-confirm-message="<?= $isActive ? 'El comunicado dejara de mostrarse en la pagina publica.' : 'El comunicado quedara publicado y visible.' ?>">
                                    <input type="hidden" name="accion" value="toggle_comunicado">
                                    <input type="hidden" name="id_comunicado" value="<?= (int) $item['id_comunicado'] ?>">
                                    <span class="form-check form-switch">
                                        <input class="form-check-input js-com-visible-toggle" type="checkbox" role="switch" <?= $isActive ? 'checked' : '' ?> aria-label="Cambiar visibilidad"<?= $canEditCom ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>>
                                    </span>
                                </form>
                            </td>
                            <td>
                                <div class="com-actions">
                                    <?php if ($fileExists): ?>
                                        <a class="btn-icon preview" href="<?= cms_e($filePath) ?>" target="_blank" rel="noopener" title="Ver archivo" aria-label="Ver archivo">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn-icon" type="button" disabled title="Archivo no disponible" aria-label="Archivo no disponible">
                                            <i class="bi bi-file-earmark-x"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button
                                        class="btn-icon edit"
                                        type="button"
                                        data-com-edit="<?= cms_e(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
                                        title="Editar"
                                        aria-label="Editar"
                                        <?= $canEditCom ? '' : 'disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="post" class="d-inline" data-confirm-title="Ocultar comunicado" data-confirm-message="El comunicado quedara oculto y no se eliminara la tabla ni el archivo fisico.">
                                        <input type="hidden" name="accion" value="eliminar_comunicado">
                                        <input type="hidden" name="id_comunicado" value="<?= (int) $item['id_comunicado'] ?>">
                                        <button class="btn-icon delete" type="submit" title="Eliminar/Ocultar" aria-label="Eliminar/Ocultar"<?= $canDeleteCom ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('eliminar')) . '"' ?>>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$comunicados): ?>
                        <tr>
                            <td colspan="7">
                                <div class="com-empty">
                                    <i class="bi bi-megaphone d-block fs-1 mb-2"></i>
                                    No hay comunicados para los filtros seleccionados.
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="comunicadoModal" tabindex="-1" aria-labelledby="comunicadoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data" id="comunicadoForm">
                <input type="hidden" name="accion" value="guardar_comunicado">
                <input type="hidden" name="id_comunicado" id="id_comunicado" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="comunicadoModalLabel">Nuevo comunicado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="com-modal-grid">
                        <div class="full">
                            <label class="form-label fw-semibold" for="titulo">Titulo *</label>
                            <input class="form-control" id="titulo" name="titulo" type="text" required maxlength="180">
                        </div>
                        <div class="full">
                            <label class="form-label fw-semibold" for="descripcion_corta">Descripcion corta</label>
                            <textarea class="form-control" id="descripcion_corta" name="descripcion_corta" rows="4" maxlength="600"></textarea>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="id_categoria_comunicado">Categoria *</label>
                            <select class="form-select" id="id_categoria_comunicado" name="id_categoria_comunicado" required>
                                <option value="">Seleccionar categoria</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id_categoria_comunicado'] ?>">
                                        <?= cms_e($category['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="fecha_publicacion">Fecha de publicacion *</label>
                            <input class="form-control" id="fecha_publicacion" name="fecha_publicacion" type="date" required>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="estado">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="borrador">Borrador</option>
                                <option value="publicado">Publicado</option>
                                <option value="oculto">Oculto</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="orden">Orden</label>
                            <input class="form-control" id="orden" name="orden" type="number" min="0" step="1" value="0">
                        </div>
                        <div class="full">
                            <label class="form-label fw-semibold" for="archivo">Archivo PDF o imagen <span id="archivoRequired">*</span></label>
                            <input class="form-control" id="archivo" name="archivo" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <div class="form-text" id="archivoActual">Al crear, el archivo es obligatorio. Al editar, puedes mantener el archivo actual.</div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" id="visible" name="visible" type="checkbox" checked>
                            <label class="form-check-label fw-semibold" for="visible">Visible</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-premium">
                        <i class="bi bi-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'HTML'
<script>
(function () {
    var modalEl = document.getElementById('comunicadoModal');
    var modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    var form = document.getElementById('comunicadoForm');
    var titleEl = document.getElementById('comunicadoModalLabel');

    function setValue(id, value) {
        var el = document.getElementById(id);
        if (el) el.value = value == null ? '' : value;
    }

    function resetForm() {
        if (!form) return;
        form.reset();
        setValue('id_comunicado', '0');
        setValue('fecha_publicacion', new Date().toISOString().slice(0, 10));
        setValue('estado', 'borrador');
        setValue('orden', '0');
        document.getElementById('visible').checked = true;
        document.getElementById('archivo').required = true;
        document.getElementById('archivoRequired').style.display = '';
        document.getElementById('archivoActual').textContent = 'Al crear, el archivo es obligatorio.';
        if (titleEl) titleEl.textContent = 'Nuevo comunicado';
    }

    document.querySelectorAll('[data-com-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            resetForm();
            if (modal) modal.show();
        });
    });

    document.querySelectorAll('[data-com-edit]').forEach(function (button) {
        button.addEventListener('click', function () {
            var data = {};
            try {
                data = JSON.parse(button.getAttribute('data-com-edit') || '{}');
            } catch (error) {
                data = {};
            }
            if (!form) return;
            form.reset();
            setValue('id_comunicado', data.id_comunicado || 0);
            setValue('titulo', data.titulo || '');
            setValue('descripcion_corta', data.descripcion_corta || '');
            setValue('id_categoria_comunicado', data.id_categoria_comunicado || '');
            setValue('fecha_publicacion', data.fecha_publicacion || '');
            setValue('estado', data.estado || 'borrador');
            setValue('orden', data.orden || '0');
            document.getElementById('visible').checked = String(data.visible) === '1';
            document.getElementById('archivo').required = false;
            document.getElementById('archivoRequired').style.display = 'none';
            document.getElementById('archivoActual').textContent = data.archivo ? 'Archivo actual: ' + data.archivo : 'Este comunicado no tiene archivo asociado.';
            if (titleEl) titleEl.textContent = 'Editar comunicado';
            if (modal) modal.show();
        });
    });

    document.querySelectorAll('form[data-confirm-title]').forEach(function (confirmForm) {
        if (confirmForm.classList.contains('js-com-visible-form')) return;
        confirmForm.addEventListener('submit', function (event) {
            event.preventDefault();
            adminConfirm({
                title: confirmForm.getAttribute('data-confirm-title') || 'Confirmar accion',
                msg: confirmForm.getAttribute('data-confirm-message') || 'Confirma para continuar.',
                type: confirmForm.querySelector('.btn-icon.delete') ? 'danger' : 'warning',
                btnText: 'Confirmar',
                onConfirm: function () {
                    confirmForm.submit();
                }
            });
        });
    });

    document.querySelectorAll('.js-com-visible-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var form = toggle.closest('form');
            if (!form) return;
            toggle.disabled = true;
            form.submit();
        });
    });
})();
</script>
HTML;

admin_render_layout_end(['extra_scripts' => $extraScripts]);
