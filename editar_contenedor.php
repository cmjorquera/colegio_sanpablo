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

function admin_modal_help_image_path(string $context, string $fieldKey): string
{
    $context = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($context));
    $fieldKey = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($fieldKey));

    return 'assets/images/admin-help/' . trim($context, '-') . '/' . trim($fieldKey, '-') . '.png';
}

function admin_modal_help_content(string $context, string $fieldKey, string $label): string
{
    $path = admin_modal_help_image_path($context, $fieldKey);
    $html = '<div class="field-help-popover">'
        . '<div class="field-help-title">' . cms_e($label) . '</div>'
        . '<img src="' . cms_e($path) . '" alt="' . cms_e($label) . '" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'block\';">'
        . '<div class="field-help-empty" style="display:none;">Sube una imagen de referencia en <code>' . cms_e($path) . '</code></div>'
        . '</div>';

    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}

function admin_modal_field_head(string $label, string $inputId, string $context, string $fieldKey, bool $blockable = true, string $clearName = ''): void
{
    ?>
    <div class="field-head">
        <label class="form-label" for="<?= cms_e($inputId) ?>"><?= cms_e($label) ?></label>
        <div class="field-tools">
            <?php if ($blockable): ?>
                <label class="field-lock">
                    <input
                        class="form-check-input js-field-block-toggle"
                        type="checkbox"
                        data-target="#<?= cms_e($inputId) ?>"
                        <?= $clearName !== '' ? 'name="' . cms_e($clearName) . '" value="1"' : '' ?>
                    >
                    <span>Bloquear</span>
                </label>
            <?php endif; ?>
            <button
                type="button"
                class="field-help-btn"
                data-bs-toggle="popover"
                data-bs-trigger="focus"
                data-bs-placement="left"
                data-bs-html="true"
                data-bs-content="<?= admin_modal_help_content($context, $fieldKey, $label) ?>"
                title="Referencia"
            >
                <i class="bi bi-info-circle"></i>
            </button>
        </div>
    </div>
    <?php
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
        .admin-modal .modal-content {
            border: 1px solid rgba(53, 88, 213, 0.08);
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 24px 54px rgba(15, 23, 42, 0.15);
        }
        .admin-modal .modal-header {
            padding: 16px 18px 14px;
            border-bottom: 1px solid #e8edf6;
            background: linear-gradient(135deg, rgba(53, 88, 213, 0.08), rgba(46, 197, 161, 0.08));
        }
        .admin-modal .modal-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #162338;
        }
        .admin-modal .modal-body {
            padding: 16px 18px;
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(247,250,255,0.98));
        }
        .admin-modal .modal-footer {
            padding: 14px 18px 16px;
            border-top: 1px solid #e8edf6;
            background: rgba(255,255,255,0.88);
        }
        .field-card {
            background: rgba(255,255,255,0.92);
            border: 1px solid #e4ebf5;
            border-radius: 16px;
            padding: 12px 12px 10px;
            box-shadow: 0 8px 18px rgba(18, 35, 68, 0.04);
            height: 100%;
        }
        .field-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
        }
        .field-head .form-label {
            margin: 0;
            font-weight: 700;
            color: #162338;
            font-size: 0.88rem;
        }
        .field-tools {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .field-lock {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.76rem;
            color: #72809a;
            cursor: pointer;
        }
        .field-lock .form-check-input {
            margin: 0;
            cursor: pointer;
        }
        .field-help-btn {
            width: 28px;
            height: 28px;
            border: 1px solid #d9e2f0;
            border-radius: 999px;
            background: #f8fbff;
            color: #3558d5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: .2s ease;
        }
        .field-help-btn:hover {
            background: #edf2ff;
            color: #2847b5;
        }
        .field-help-popover { width: 220px; }
        .field-help-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: #162338;
            margin-bottom: 6px;
        }
        .field-help-popover img {
            width: 100%;
            border-radius: 10px;
            border: 1px solid #dfe5f2;
            background: #fff;
        }
        .field-help-empty {
            font-size: 0.74rem;
            color: #72809a;
            line-height: 1.45;
            background: #f8fbff;
            border: 1px dashed #d7deed;
            border-radius: 10px;
            padding: 9px;
        }
        .field-card.is-blocked {
            opacity: 0.72;
            border-style: dashed;
            background: #f7f9fc;
        }
        .field-card.is-blocked .form-control,
        .field-card.is-blocked .form-select {
            background: #eef2f7;
        }
        .field-note {
            margin-top: 6px;
            font-size: 0.72rem;
            color: #72809a;
        }
        .admin-modal .form-control,
        .admin-modal .form-select {
            border-radius: 10px;
            min-height: 38px;
            padding: 6px 10px;
            font-size: 14px;
        }
        .admin-modal textarea.form-control {
            min-height: 88px;
        }
        .admin-modal .modal-dialog.modal-xl {
            max-width: 1040px;
        }
        .admin-modal .modal-dialog.modal-lg {
            max-width: 760px;
        }
        .admin-modal .btn {
            min-height: 38px;
        }
        .admin-modal .row.g-3 {
            --bs-gutter-x: 0.9rem;
            --bs-gutter-y: 0.9rem;
        }
        .admin-modal .form-text {
            font-size: 0.76rem;
            color: #6f7e97;
        }
        @media (max-width: 767px) {
            .admin-modal .modal-header,
            .admin-modal .modal-body,
            .admin-modal .modal-footer {
                padding-left: 14px;
                padding-right: 14px;
            }
            .field-card {
                border-radius: 14px;
            }
        }
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
    <div class="modal fade admin-modal" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
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
                            <div class="col-md-6">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Nombre de la red', 'topbar_titulo', 'topbar', 'titulo'); ?>
                                    <input class="form-control" id="topbar_titulo" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>" placeholder="Instagram">
                                    <div class="field-note">Si se bloquea, la red se guardará sin nombre visible.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Clase del ícono', 'topbar_icono', 'topbar', 'icono'); ?>
                                    <input class="form-control" id="topbar_icono" name="icono" value="<?= cms_e($editingItem['icono'] ?? '') ?>" placeholder="fab fa-instagram">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('URL', 'topbar_descripcion', 'topbar', 'url'); ?>
                                    <input class="form-control" id="topbar_descripcion" name="descripcion" value="<?= cms_e($editingItem['descripcion'] ?? '') ?>" placeholder="https://instagram.com/...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-card">
                                    <?php admin_modal_field_head('Visible', 'topbar_visible', 'topbar', 'visible', false); ?>
                                    <select class="form-select" id="topbar_visible" name="visible"><option value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'selected' : '' ?>>Si</option><option value="no" <?= ($editingItem['visible'] ?? '') === 'no' ? 'selected' : '' ?>>No</option></select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-card">
                                    <?php admin_modal_field_head('Orden', 'topbar_orden', 'topbar', 'orden', false); ?>
                                    <input class="form-control" id="topbar_orden" type="number" name="orden" min="1" value="<?= (int) ($editingItem['orden'] ?? count($topbarItems) + 1) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-text mt-3">El sitio mostrará como máximo 4 redes visibles ordenadas por este campo.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-admin-action">Guardar red social</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="modal fade admin-modal" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
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
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Etiqueta', 'item_etiqueta', $section['nombre_interno'], 'etiqueta'); ?>
                                        <input class="form-control" id="item_etiqueta" name="etiqueta" value="<?= cms_e($editingItem['etiqueta'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Título línea 1', 'item_titulo_linea_1', $section['nombre_interno'], 'titulo-linea-1'); ?>
                                        <input class="form-control" id="item_titulo_linea_1" name="titulo_linea_1" value="<?= cms_e($editingItem['titulo_linea_1'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Título línea 2', 'item_titulo_linea_2', $section['nombre_interno'], 'titulo-linea-2'); ?>
                                        <input class="form-control" id="item_titulo_linea_2" name="titulo_linea_2" value="<?= cms_e($editingItem['titulo_linea_2'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Título línea 3', 'item_titulo_linea_3', $section['nombre_interno'], 'titulo-linea-3'); ?>
                                        <input class="form-control" id="item_titulo_linea_3" name="titulo_linea_3" value="<?= cms_e($editingItem['titulo_linea_3'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Descripción', 'item_descripcion', $section['nombre_interno'], 'descripcion'); ?>
                                        <textarea class="form-control" id="item_descripcion" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Botón 1 texto', 'item_boton_1_texto', $section['nombre_interno'], 'boton-1-texto'); ?>
                                        <input class="form-control" id="item_boton_1_texto" name="boton_1_texto" value="<?= cms_e($editingItem['boton_1_texto'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Botón 1 URL', 'item_boton_1_url', $section['nombre_interno'], 'boton-1-url'); ?>
                                        <input class="form-control" id="item_boton_1_url" name="boton_1_url" value="<?= cms_e($editingItem['boton_1_url'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Botón 2 texto', 'item_boton_2_texto', $section['nombre_interno'], 'boton-2-texto'); ?>
                                        <input class="form-control" id="item_boton_2_texto" name="boton_2_texto" value="<?= cms_e($editingItem['boton_2_texto'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Botón 2 URL', 'item_boton_2_url', $section['nombre_interno'], 'boton-2-url'); ?>
                                        <input class="form-control" id="item_boton_2_url" name="boton_2_url" value="<?= cms_e($editingItem['boton_2_url'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($section['tipo_seccion'] === 'news'): ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Categoría', 'item_id_categoria', $section['nombre_interno'], 'categoria'); ?>
                                        <select class="form-select" id="item_id_categoria" name="id_categoria">
                                            <option value="">Seleccione</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= (int) $category['id_categoria'] ?>" <?= ((int) ($editingItem['id_categoria'] ?? 0) === (int) $category['id_categoria']) ? 'selected' : '' ?>><?= cms_e($category['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Título', 'item_titulo', $section['nombre_interno'], 'titulo'); ?>
                                        <input class="form-control" id="item_titulo" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Etiqueta visual', 'item_etiqueta_news', $section['nombre_interno'], 'etiqueta'); ?>
                                        <input class="form-control" id="item_etiqueta_news" name="etiqueta" value="<?= cms_e($editingItem['etiqueta'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Descripción', 'item_descripcion_news', $section['nombre_interno'], 'descripcion'); ?>
                                        <textarea class="form-control" id="item_descripcion_news" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Fecha publicación', 'item_fecha_publicacion', $section['nombre_interno'], 'fecha-publicacion'); ?>
                                        <input class="form-control" id="item_fecha_publicacion" type="date" name="fecha_publicacion" value="<?= cms_e($editingItem['fecha_publicacion'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Botón texto', 'item_boton_1_texto_news', $section['nombre_interno'], 'boton-1-texto'); ?>
                                        <input class="form-control" id="item_boton_1_texto_news" name="boton_1_texto" value="<?= cms_e($editingItem['boton_1_texto'] ?? 'Leer más') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Botón URL', 'item_boton_1_url_news', $section['nombre_interno'], 'boton-1-url'); ?>
                                        <input class="form-control" id="item_boton_1_url_news" name="boton_1_url" value="<?= cms_e($editingItem['boton_1_url'] ?? '#') ?>">
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Título', 'item_titulo_generic', $section['nombre_interno'], 'titulo'); ?>
                                        <input class="form-control" id="item_titulo_generic" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Subtítulo', 'item_subtitulo', $section['nombre_interno'], 'subtitulo'); ?>
                                        <input class="form-control" id="item_subtitulo" name="subtitulo" value="<?= cms_e($editingItem['subtitulo'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Descripción', 'item_descripcion_generic', $section['nombre_interno'], 'descripcion'); ?>
                                        <textarea class="form-control" id="item_descripcion_generic" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Imagen', 'item_imagen', $section['nombre_interno'], 'imagen', true, 'clear_imagen'); ?>
                                    <input class="form-control" id="item_imagen" type="file" name="imagen" accept="image/*">
                                    <div class="field-note">Si bloqueas este campo, la imagen se guardará vacía.</div>
                                </div>
                            </div>
                            <?php if (in_array($section['tipo_seccion'], ['carousel', 'hero'], true)): ?>
                                <div class="col-md-6">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Imagen mobile', 'item_imagen_mobile', $section['nombre_interno'], 'imagen-mobile', true, 'clear_imagen_mobile'); ?>
                                        <input class="form-control" id="item_imagen_mobile" type="file" name="imagen_mobile" accept="image/*">
                                        <div class="field-note">Úsala si el diseño necesita una imagen distinta en móviles.</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-3">
                                <div class="field-card">
                                    <?php admin_modal_field_head('Visible', 'item_visible', $section['nombre_interno'], 'visible', false); ?>
                                    <select class="form-select" id="item_visible" name="visible"><option value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'selected' : '' ?>>Si</option><option value="no" <?= ($editingItem['visible'] ?? '') === 'no' ? 'selected' : '' ?>>No</option></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="field-card">
                                    <?php admin_modal_field_head('Orden', 'item_orden', $section['nombre_interno'], 'orden', false); ?>
                                    <input class="form-control" id="item_orden" type="number" name="orden" min="1" value="<?= (int) ($editingItem['orden'] ?? count($items) + 1) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-admin-action">Guardar item</button>
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
            function syncBlockedField(toggle) {
                var targetSelector = toggle.getAttribute('data-target');
                if (!targetSelector) {
                    return;
                }

                var target = document.querySelector(targetSelector);
                if (!target) {
                    return;
                }

                target.disabled = toggle.checked;
                var shell = toggle.closest('[data-field-shell]');
                if (shell) {
                    shell.classList.toggle('is-blocked', toggle.checked);
                }
            }

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

            document.querySelectorAll('.js-field-block-toggle').forEach(function (toggle) {
                syncBlockedField(toggle);
                toggle.addEventListener('change', function () {
                    syncBlockedField(toggle);
                });
            });

            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (element) {
                new bootstrap.Popover(element);
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
