<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: colegiosanpablo.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_layout.php';

$db = cms_get_connection();
$institutionId = cms_get_institution_id($db);
cms_sync_sections($db, $institutionId);

$idSeccion = (int) ($_GET['id'] ?? $_POST['id_seccion'] ?? 0);
$section = $idSeccion > 0 ? cms_get_section($db, $idSeccion) : null;

if (!$section) {
    cms_set_flash('danger', 'El contenedor solicitado no existe.');
    cms_redirect('admin.php?panel=contenedores');
}

function topbar_get_config_value(array $configs, string $key, string $default = ''): string
{
    foreach ($configs as $config) {
        if (($config['clave'] ?? '') === $key) {
            return (string) ($config['valor'] ?? '');
        }
    }

    return $default;
}

function topbar_save_general(mysqli $db, array $section, array $post): void
{
    $idSeccion = (int) $section['id_seccion'];
    $idInstitucion = (int) $section['id_institucion'];
    $visible = (($post['visible'] ?? 'no') === 'si') ? 'si' : 'no';
    $orden = max(1, (int) ($post['orden'] ?? 1));
    $observacion = trim((string) ($post['observacion'] ?? ''));

    $stmtSeccion = $db->prepare('UPDATE seccion SET visible = ?, orden = ?, observacion = ? WHERE id_seccion = ?');
    $stmtSeccion->bind_param('sisi', $visible, $orden, $observacion, $idSeccion);
    $stmtSeccion->execute();
    $stmtSeccion->close();

    $direccion = trim((string) ($post['direccion'] ?? ''));
    $telefono = trim((string) ($post['telefono'] ?? ''));
    $email = trim((string) ($post['email'] ?? ''));

    $stmtInstitucion = $db->prepare('UPDATE institucion SET direccion = ?, telefono = ?, email = ? WHERE id_institucion = ?');
    $stmtInstitucion->bind_param('sssi', $direccion, $telefono, $email, $idInstitucion);
    $stmtInstitucion->execute();
    $stmtInstitucion->close();

    $configValues = [
        'texto_boton_ingresar' => trim((string) ($post['texto_boton_ingresar'] ?? 'Ingresar')),
        'mostrar_direccion' => (($post['mostrar_direccion'] ?? 'si') === 'si') ? 'si' : 'no',
        'mostrar_telefono' => (($post['mostrar_telefono'] ?? 'si') === 'si') ? 'si' : 'no',
        'mostrar_email' => (($post['mostrar_email'] ?? 'si') === 'si') ? 'si' : 'no',
        'mostrar_redes' => (($post['mostrar_redes'] ?? 'si') === 'si') ? 'si' : 'no',
        'mostrar_boton_ingresar' => (($post['mostrar_boton_ingresar'] ?? 'si') === 'si') ? 'si' : 'no',
    ];

    $deleteSql = "DELETE FROM seccion_config
        WHERE id_seccion = ?
          AND clave IN ('texto_boton_ingresar', 'mostrar_direccion', 'mostrar_telefono', 'mostrar_email', 'mostrar_redes', 'mostrar_boton_ingresar')";
    $stmtDelete = $db->prepare($deleteSql);
    $stmtDelete->bind_param('i', $idSeccion);
    $stmtDelete->execute();
    $stmtDelete->close();

    $stmtInsert = $db->prepare('INSERT INTO seccion_config (id_seccion, clave, valor) VALUES (?, ?, ?)');
    foreach ($configValues as $clave => $valor) {
        $stmtInsert->bind_param('iss', $idSeccion, $clave, $valor);
        $stmtInsert->execute();
    }
    $stmtInsert->close();
}

function topbar_save_item(mysqli $db, array $section, array $post): int
{
    $idSeccion = (int) $section['id_seccion'];
    $idItem = (int) ($post['id_item'] ?? 0);
    $titulo = trim((string) ($post['titulo'] ?? ''));
    $url = trim((string) ($post['descripcion'] ?? ''));
    $icono = trim((string) ($post['icono'] ?? ''));
    $visible = (($post['visible'] ?? 'si') === 'si') ? 'si' : 'no';
    $orden = max(1, (int) ($post['orden'] ?? 1));

    if ($titulo === '' || $url === '' || $icono === '') {
        throw new RuntimeException('Cada red social debe tener nombre, URL e icono.');
    }

    if ($visible === 'si') {
        $excludeId = $idItem > 0 ? $idItem : 0;
        $stmtCount = $db->prepare("SELECT COUNT(*) AS total
            FROM seccion_item
            WHERE id_seccion = ? AND etiqueta = 'red_social' AND visible = 'si' AND id_item <> ?");
        $stmtCount->bind_param('ii', $idSeccion, $excludeId);
        $stmtCount->execute();
        $countResult = $stmtCount->get_result();
        $visibleCount = (int) (($countResult ? $countResult->fetch_assoc()['total'] : 0));
        $stmtCount->close();

        if ($visibleCount >= 4) {
            throw new RuntimeException('El topbar solo puede tener 4 redes sociales visibles como máximo.');
        }
    }

    if ($idItem > 0) {
        $stmtExists = $db->prepare("SELECT id_item
            FROM seccion_item
            WHERE id_item = ? AND id_seccion = ? AND etiqueta = 'red_social'
            LIMIT 1");
        $stmtExists->bind_param('ii', $idItem, $idSeccion);
        $stmtExists->execute();
        $existsResult = $stmtExists->get_result();
        $exists = $existsResult ? $existsResult->fetch_assoc() : null;
        $stmtExists->close();

        if (!$exists) {
            throw new RuntimeException('La red social solicitada no existe en este contenedor.');
        }

        $stmt = $db->prepare("UPDATE seccion_item
            SET etiqueta = 'red_social', icono = ?, titulo = ?, descripcion = ?, visible = ?, orden = ?
            WHERE id_item = ? AND id_seccion = ?");
        $stmt->bind_param('ssssiii', $icono, $titulo, $url, $visible, $orden, $idItem, $idSeccion);
        $stmt->execute();
        $stmt->close();

        return $idItem;
    }

    $stmt = $db->prepare("INSERT INTO seccion_item (id_seccion, id_categoria, etiqueta, icono, titulo, descripcion, visible, orden)
        VALUES (?, NULL, 'red_social', ?, ?, ?, ?, ?)");
    $stmt->bind_param('issssi', $idSeccion, $icono, $titulo, $url, $visible, $orden);
    $stmt->execute();
    $newId = (int) $db->insert_id;
    $stmt->close();

    return $newId;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['accion'] ?? '';

        if (($section['nombre_interno'] ?? '') === 'topbar' && $action === 'guardar_topbar_general') {
            topbar_save_general($db, $section, $_POST);
            cms_set_flash('success', 'La configuración del topbar fue actualizada correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion);
        }

        if (($section['nombre_interno'] ?? '') === 'topbar' && $action === 'guardar_topbar_item') {
            topbar_save_item($db, $section, $_POST);
            cms_set_flash('success', 'La red social fue guardada correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }

        if ($action === 'guardar_seccion') {
            cms_save_section($db, $idSeccion, $_POST);
            cms_set_flash('success', 'El contenedor fue actualizado correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion);
        }

        if ($action === 'guardar_item') {
            cms_save_item($db, $section, $_POST);
            cms_set_flash('success', 'El item fue guardado correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }

        if ($action === 'eliminar_item') {
            cms_delete_item($db, (int) ($_POST['id_item'] ?? 0));
            cms_set_flash('success', 'El item fue eliminado correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }
    }
} catch (Throwable $e) {
    cms_set_flash('danger', $e->getMessage());
    cms_redirect('editar_contenedor.php?id=' . $idSeccion);
}

$flash = cms_get_flash();
$configs = cms_get_section_configs($db, $idSeccion);
$items = cms_get_section_items($db, $idSeccion);
$site = cms_get_site_data($db);
$categories = array_values($site['categories']);
$editingItem = isset($_GET['item']) ? cms_get_item($db, (int) $_GET['item']) : null;
$openModal = $_GET['modal'] ?? '';
$tab = $_GET['tab'] ?? 'general';
$isTopbar = ($section['nombre_interno'] ?? '') === 'topbar';
$topbarConfigs = [
    'texto_boton_ingresar' => topbar_get_config_value($configs, 'texto_boton_ingresar', 'Ingresar'),
    'mostrar_direccion' => topbar_get_config_value($configs, 'mostrar_direccion', 'si'),
    'mostrar_telefono' => topbar_get_config_value($configs, 'mostrar_telefono', 'si'),
    'mostrar_email' => topbar_get_config_value($configs, 'mostrar_email', 'si'),
    'mostrar_redes' => topbar_get_config_value($configs, 'mostrar_redes', 'si'),
    'mostrar_boton_ingresar' => topbar_get_config_value($configs, 'mostrar_boton_ingresar', 'si'),
];
$topbarItems = $isTopbar
    ? array_values(array_filter($items, static fn(array $item): bool => ($item['etiqueta'] ?? '') === 'red_social'))
    : [];

admin_render_layout_start([
    'title' => 'Editar contenedor | ' . ($section['titulo_admin'] ?? 'Contenedor'),
    'page_title' => $section['titulo_admin'] ?? 'Editar contenedor',
    'breadcrumb' => 'Contenedores del sitio / ' . ($section['nombre_interno'] ?? ''),
    'active_panel' => 'contenedores',
    'institution_name' => $site['institution']['nombre'] ?? 'Institución activa',
    'institution_short_name' => $site['institution']['nombre_corto'] ?? ($site['institution']['nombre'] ?? 'Institución'),
    'institution_logo' => $site['institution']['logo_header'] ?? '',
    'admin_name' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'header_actions' => '<a href="admin.php?panel=contenedores" class="btn btn-soft"><i class="bi bi-arrow-left me-2"></i>Volver</a><a href="preview_contenedor.php?id=' . (int) $idSeccion . '" class="btn btn-premium"><i class="bi bi-eye me-2"></i>Visualizar</a>',
    'extra_head' => <<<'HTML'
    <style>
        .hero-card { border: 1px solid #dbe4ef; border-radius: 22px; overflow: hidden; background: #fff; height: 100%; }
        .hero-thumb { height: 180px; background-size: cover; background-position: center; }
        .nav-pills .nav-link.active { background: linear-gradient(135deg, #1f8f6b, #27b785); }
    </style>
HTML,
]);
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= cms_e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= cms_e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="section-card">
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
        <div>
            <h3 class="mb-1"><?= cms_e($section['titulo_admin']) ?></h3>
            <div class="text-muted">
                <code><?= cms_e($section['nombre_interno']) ?></code> · tipo <code><?= cms_e($section['tipo_seccion']) ?></code>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= cms_e(cms_get_preview_target($section['nombre_interno'])) ?>" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-1"></i>Ver en sitio</a>
        </div>
    </div>
</div>

<div class="section-card">
    <ul class="nav nav-pills gap-2">
        <li class="nav-item"><a class="nav-link <?= $tab === 'general' ? 'active' : '' ?>" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=general">General</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'items' ? 'active' : '' ?>" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items">Items</a></li>
    </ul>
</div>

<?php if ($tab === 'general'): ?>
    <div class="section-card">
        <div class="section-head">
            <div>
                <h3>Configuración del contenedor</h3>
                <p>Visible, orden, observación y claves guardadas en <code>seccion_config</code>.</p>
            </div>
        </div>
        <?php if ($isTopbar): ?>
            <form method="post">
                <input type="hidden" name="accion" value="guardar_topbar_general">
                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Visible</label>
                        <select class="form-select" name="visible">
                            <option value="si" <?= ($section['visible'] ?? '') === 'si' ? 'selected' : '' ?>>Si</option>
                            <option value="no" <?= ($section['visible'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Orden</label>
                        <input class="form-control" type="number" name="orden" min="1" value="<?= (int) $section['orden'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Observación</label>
                        <input class="form-control" type="text" name="observacion" value="<?= cms_e($section['observacion'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dirección</label>
                        <input class="form-control" name="direccion" value="<?= cms_e($site['institution']['direccion'] ?? '') ?>">
                        <div class="form-text">Se guarda en <code>institucion.direccion</code>.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input class="form-control" name="telefono" value="<?= cms_e($site['institution']['telefono'] ?? '') ?>">
                        <div class="form-text">Se guarda en <code>institucion.telefono</code>.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Correo</label>
                        <input class="form-control" name="email" value="<?= cms_e($site['institution']['email'] ?? '') ?>">
                        <div class="form-text">Se guarda en <code>institucion.email</code>.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Texto del botón "Ingresar"</label>
                        <input class="form-control" name="texto_boton_ingresar" value="<?= cms_e($topbarConfigs['texto_boton_ingresar']) ?>">
                        <div class="form-text">Solo cambia el texto. La acción del modal se conserva.</div>
                    </div>
                    <!-- <div class="col-md-6">
                        <label class="form-label">Gradiente institucional</label>
                        <div class="form-control d-flex align-items-center" style="min-height:46px; background:linear-gradient(90deg, <?= cms_e($site['institution']['color_primario'] ?? '#2563EB') ?>, <?= cms_e($site['institution']['color_secundario'] ?? '#E9A629') ?>, <?= cms_e($site['institution']['color_terciario'] ?? '#222222') ?>); color:#fff;">
                            <?= cms_e(($site['institution']['color_primario'] ?? '#2563EB') . ' / ' . ($site['institution']['color_secundario'] ?? '#E9A629') . ' / ' . ($site['institution']['color_terciario'] ?? '#222222')) ?>
                        </div>
                        <div class="form-text">Los colores vienen de <code>institucion</code> y no se editan aquí.</div>
                    </div> -->
                    <div class="col-md-2">
                        <label class="form-label">Mostrar dirección</label>
                        <select class="form-select" name="mostrar_direccion">
                            <option value="si" <?= $topbarConfigs['mostrar_direccion'] === 'si' ? 'selected' : '' ?>>Si</option>
                            <option value="no" <?= $topbarConfigs['mostrar_direccion'] === 'no' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Mostrar teléfono</label>
                        <select class="form-select" name="mostrar_telefono">
                            <option value="si" <?= $topbarConfigs['mostrar_telefono'] === 'si' ? 'selected' : '' ?>>Si</option>
                            <option value="no" <?= $topbarConfigs['mostrar_telefono'] === 'no' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Mostrar correo</label>
                        <select class="form-select" name="mostrar_email">
                            <option value="si" <?= $topbarConfigs['mostrar_email'] === 'si' ? 'selected' : '' ?>>Si</option>
                            <option value="no" <?= $topbarConfigs['mostrar_email'] === 'no' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mostrar redes</label>
                        <select class="form-select" name="mostrar_redes">
                            <option value="si" <?= $topbarConfigs['mostrar_redes'] === 'si' ? 'selected' : '' ?>>Si</option>
                            <option value="no" <?= $topbarConfigs['mostrar_redes'] === 'no' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mostrar botón ingresar</label>
                        <select class="form-select" name="mostrar_boton_ingresar">
                            <option value="si" <?= $topbarConfigs['mostrar_boton_ingresar'] === 'si' ? 'selected' : '' ?>>Si</option>
                            <option value="no" <?= $topbarConfigs['mostrar_boton_ingresar'] === 'no' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-premium"><i class="bi bi-save me-1"></i>Guardar topbar</button>
                </div>
            </form>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="accion" value="guardar_seccion">
                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Visible</label>
                        <select class="form-select" name="visible">
                            <option value="si" <?= ($section['visible'] ?? '') === 'si' ? 'selected' : '' ?>>Si</option>
                            <option value="no" <?= ($section['visible'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Orden</label>
                        <input class="form-control" type="number" name="orden" min="1" value="<?= (int) $section['orden'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Observación</label>
                        <input class="form-control" type="text" name="observacion" value="<?= cms_e($section['observacion'] ?? '') ?>">
                    </div>
                </div>
                <div id="configRows">
                    <?php foreach ($configs as $config): ?>
                        <div class="row g-3 align-items-end mb-3 config-row">
                            <div class="col-md-4">
                                <label class="form-label">Clave</label>
                                <input class="form-control" name="config_key[]" value="<?= cms_e($config['clave']) ?>">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Valor</label>
                                <input class="form-control" name="config_value[]" value="<?= cms_e($config['valor']) ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger w-100 remove-config-row"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$configs): ?>
                        <div class="row g-3 align-items-end mb-3 config-row">
                            <div class="col-md-4"><label class="form-label">Clave</label><input class="form-control" name="config_key[]" placeholder="titulo_bloque"></div>
                            <div class="col-md-7"><label class="form-label">Valor</label><input class="form-control" name="config_value[]" placeholder="Últimas Noticias"></div>
                            <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-config-row"><i class="bi bi-trash"></i></button></div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" id="addConfigRow" class="btn btn-outline-secondary"><i class="bi bi-plus-circle me-1"></i>Agregar configuración</button>
                    <button type="submit" class="btn btn-premium"><i class="bi bi-save me-1"></i>Guardar contenedor</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="section-card">
        <div class="section-head">
            <div>
                <h3>Items del contenedor</h3>
                <p>Administración específica del bloque.</p>
            </div>
            <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item" class="btn btn-premium"><i class="bi bi-plus-circle me-1"></i>Agregar item</a>
        </div>

        <?php if ($isTopbar): ?>
            <div class="alert alert-info border-0" style="background:#eef8ff; color:#234;">
                Las redes sociales del topbar se guardan en <code>seccion_item</code> con <code>etiqueta = 'red_social'</code>. En el sitio solo se muestran las primeras 4 visibles según <code>orden</code>.
            </div>
            <div class="table-responsive">
                <table class="table table-modern align-middle" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Orden</th>
                            <th>Red</th>
                            <th>URL</th>
                            <th>Ícono</th>
                            <th>Visible</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topbarItems as $item): ?>
                            <tr>
                                <td><?= (int) $item['orden'] ?></td>
                                <td><?= cms_e($item['titulo'] ?? '') ?></td>
                                <td><a href="<?= cms_e($item['descripcion'] ?? '#') ?>" target="_blank" rel="noopener"><?= cms_e($item['descripcion'] ?? '') ?></a></td>
                                <td><code><?= cms_e($item['icono'] ?? '') ?></code></td>
                                <td><span class="badge-soft <?= ($item['visible'] ?? '') === 'si' ? 'success' : 'warning' ?>"><?= ($item['visible'] ?? '') === 'si' ? 'Si' : 'No' ?></span></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                        <form method="post" onsubmit="return confirm('¿Eliminar esta red social?');">
                                            <input type="hidden" name="accion" value="eliminar_item">
                                            <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
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
        <?php elseif (in_array($section['tipo_seccion'], ['carousel', 'hero'], true)): ?>
            <div class="row g-4 mb-4">
                <?php foreach ($items as $item): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="hero-card">
                            <div class="hero-thumb" style="background-image:url('<?= cms_e($item['imagen'] ?: 'assets/images/portada_1.jpg') ?>')"></div>
                            <div class="p-3">
                                <span class="badge-soft <?= ($item['visible'] ?? '') === 'si' ? 'success' : 'warning' ?>"><?= ($item['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?></span>
                                <h5 class="mt-3 mb-1"><?= cms_e(trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? ''))) ?></h5>
                                <small class="text-muted">Orden <?= (int) $item['orden'] ?></small>
                                <p class="text-muted mt-2 mb-3"><?= cms_e($item['etiqueta'] ?? '') ?></p>
                                <div class="d-flex gap-2">
                                    <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                    <form method="post" onsubmit="return confirm('¿Eliminar este item?');">
                                        <input type="hidden" name="accion" value="eliminar_item">
                                        <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
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

        <?php if (!$isTopbar): ?>
            <div class="table-responsive">
                <table class="table table-modern align-middle" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Orden</th>
                            <th>Título</th>
                            <th>Visible</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= (int) $item['orden'] ?></td>
                                <td>
                                    <?php $displayTitle = $item['titulo'] ?: trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? '')); ?>
                                    <?= cms_e($displayTitle) ?>
                                </td>
                                <td><span class="badge-soft <?= ($item['visible'] ?? '') === 'si' ? 'success' : 'warning' ?>"><?= ($item['visible'] ?? '') === 'si' ? 'Si' : 'No' ?></span></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                        <form method="post" onsubmit="return confirm('¿Eliminar este item?');">
                                            <input type="hidden" name="accion" value="eliminar_item">
                                            <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
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
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($isTopbar): ?>
    <div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:24px; border:0;">
                <form method="post">
                    <input type="hidden" name="accion" value="guardar_topbar_item">
                    <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                    <input type="hidden" name="id_item" value="<?= (int) ($editingItem['id_item'] ?? 0) ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= $editingItem ? 'Editar red social' : 'Agregar red social' ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Nombre de la red</label><input class="form-control" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>" placeholder="Instagram"></div>
                            <div class="col-md-6"><label class="form-label">Clase del ícono</label><input class="form-control" name="icono" value="<?= cms_e($editingItem['icono'] ?? '') ?>" placeholder="fab fa-instagram"></div>
                            <div class="col-12"><label class="form-label">URL</label><input class="form-control" name="descripcion" value="<?= cms_e($editingItem['descripcion'] ?? '') ?>" placeholder="https://instagram.com/..."></div>
                            <div class="col-md-6"><label class="form-label">Visible</label><select class="form-select" name="visible"><option value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'selected' : '' ?>>Si</option><option value="no" <?= ($editingItem['visible'] ?? '') === 'no' ? 'selected' : '' ?>>No</option></select></div>
                            <div class="col-md-6"><label class="form-label">Orden</label><input class="form-control" type="number" name="orden" min="1" value="<?= (int) ($editingItem['orden'] ?? count($topbarItems) + 1) ?>"></div>
                        </div>
                        <div class="form-text mt-3">El sitio mostrará como máximo 4 redes visibles ordenadas por este campo.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-premium">Guardar red social</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:24px; border:0;">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="guardar_item">
                    <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                    <input type="hidden" name="id_item" value="<?= (int) ($editingItem['id_item'] ?? 0) ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= $editingItem ? 'Editar item' : 'Agregar item' ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (in_array($section['tipo_seccion'], ['carousel', 'hero'], true)): ?>
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Etiqueta</label><input class="form-control" name="etiqueta" value="<?= cms_e($editingItem['etiqueta'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Título línea 1</label><input class="form-control" name="titulo_linea_1" value="<?= cms_e($editingItem['titulo_linea_1'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Título línea 2</label><input class="form-control" name="titulo_linea_2" value="<?= cms_e($editingItem['titulo_linea_2'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Título línea 3</label><input class="form-control" name="titulo_linea_3" value="<?= cms_e($editingItem['titulo_linea_3'] ?? '') ?>"></div>
                                <div class="col-md-8"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea></div>
                                <div class="col-md-3"><label class="form-label">Botón 1 texto</label><input class="form-control" name="boton_1_texto" value="<?= cms_e($editingItem['boton_1_texto'] ?? '') ?>"></div>
                                <div class="col-md-3"><label class="form-label">Botón 1 URL</label><input class="form-control" name="boton_1_url" value="<?= cms_e($editingItem['boton_1_url'] ?? '') ?>"></div>
                                <div class="col-md-3"><label class="form-label">Botón 2 texto</label><input class="form-control" name="boton_2_texto" value="<?= cms_e($editingItem['boton_2_texto'] ?? '') ?>"></div>
                                <div class="col-md-3"><label class="form-label">Botón 2 URL</label><input class="form-control" name="boton_2_url" value="<?= cms_e($editingItem['boton_2_url'] ?? '') ?>"></div>
                            </div>
                        <?php elseif ($section['tipo_seccion'] === 'news'): ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" name="id_categoria">
                                        <option value="">Seleccione</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= (int) $category['id_categoria'] ?>" <?= ((int) ($editingItem['id_categoria'] ?? 0) === (int) $category['id_categoria']) ? 'selected' : '' ?>><?= cms_e($category['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-8"><label class="form-label">Título</label><input class="form-control" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Etiqueta visual</label><input class="form-control" name="etiqueta" value="<?= cms_e($editingItem['etiqueta'] ?? '') ?>"></div>
                                <div class="col-md-8"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea></div>
                                <div class="col-md-4"><label class="form-label">Fecha publicación</label><input class="form-control" type="date" name="fecha_publicacion" value="<?= cms_e($editingItem['fecha_publicacion'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Botón texto</label><input class="form-control" name="boton_1_texto" value="<?= cms_e($editingItem['boton_1_texto'] ?? 'Leer más') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Botón URL</label><input class="form-control" name="boton_1_url" value="<?= cms_e($editingItem['boton_1_url'] ?? '#') ?>"></div>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Título</label><input class="form-control" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>"></div>
                                <div class="col-md-6"><label class="form-label">Subtítulo</label><input class="form-control" name="subtitulo" value="<?= cms_e($editingItem['subtitulo'] ?? '') ?>"></div>
                                <div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea></div>
                            </div>
                        <?php endif; ?>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Imagen</label><input class="form-control" type="file" name="imagen" accept="image/*"></div>
                            <?php if (in_array($section['tipo_seccion'], ['carousel', 'hero'], true)): ?>
                                <div class="col-md-6"><label class="form-label">Imagen mobile</label><input class="form-control" type="file" name="imagen_mobile" accept="image/*"></div>
                            <?php endif; ?>
                            <div class="col-md-3"><label class="form-label">Visible</label><select class="form-select" name="visible"><option value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'selected' : '' ?>>Si</option><option value="no" <?= ($editingItem['visible'] ?? '') === 'no' ? 'selected' : '' ?>>No</option></select></div>
                            <div class="col-md-3"><label class="form-label">Orden</label><input class="form-control" type="number" name="orden" min="1" value="<?= (int) ($editingItem['orden'] ?? count($items) + 1) ?>"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-premium">Guardar item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<template id="configRowTemplate">
    <div class="row g-3 align-items-end mb-3 config-row">
        <div class="col-md-4"><label class="form-label">Clave</label><input class="form-control" name="config_key[]" placeholder="clave"></div>
        <div class="col-md-7"><label class="form-label">Valor</label><input class="form-control" name="config_value[]" placeholder="valor"></div>
        <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-config-row"><i class="bi bi-trash"></i></button></div>
    </div>
</template>

<?php
admin_render_layout_end([
    'extra_scripts' => str_replace(
        'OPEN_MODAL_PLACEHOLDER',
        json_encode($openModal, JSON_UNESCAPED_UNICODE),
        <<<'HTML'
    <script>
        $(function () {
            if ($('#itemsTable').length) {
                $('#itemsTable').DataTable({
                    pageLength: 10,
                    order: [[0, 'asc']],
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
                });
            }

            $('#addConfigRow').on('click', function () {
                var tpl = document.getElementById('configRowTemplate');
                document.getElementById('configRows').appendChild(tpl.content.cloneNode(true));
            });

            $(document).on('click', '.remove-config-row', function () {
                var rows = document.querySelectorAll('#configRows .config-row');
                if (rows.length === 1) {
                    rows[0].querySelectorAll('input').forEach(function (input) { input.value = ''; });
                    return;
                }
                this.closest('.config-row').remove();
            });

            var openModal = OPEN_MODAL_PLACEHOLDER;
            if (openModal === 'item') {
                var modal = new bootstrap.Modal(document.getElementById('itemModal'));
                modal.show();
            }
        });
    </script>
HTML
    ),
]);
?>
