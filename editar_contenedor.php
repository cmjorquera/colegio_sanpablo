<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_once __DIR__ . '/includes/funciones_auditoria.php';

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

function topbar_social_icon_options(): array
{
    return [
        [
            'name' => 'Instagram',
            'icon' => 'assets/redes_sociales/instagram.jpg',
            'url' => 'https://instagram.com/',
            'keys' => ['instagram', 'insta'],
        ],
        [
            'name' => 'Facebook',
            'icon' => 'assets/redes_sociales/facebook.jpg',
            'url' => 'https://facebook.com/',
            'keys' => ['facebook', 'fb'],
        ],
        [
            'name' => 'YouTube',
            'icon' => 'assets/redes_sociales/youtube.png',
            'url' => 'https://youtube.com/',
            'keys' => ['youtube', 'youtu.be'],
        ],
        [
            'name' => 'Twitter',
            'icon' => 'assets/redes_sociales/twitter.png',
            'url' => 'https://x.com/',
            'keys' => ['twitter', 'x.com'],
        ],
        [
            'name' => 'LinkedIn',
            'icon' => 'assets/redes_sociales/linkeding.png',
            'url' => 'https://linkedin.com/',
            'keys' => ['linkedin', 'linkeding'],
        ],
    ];
}

function topbar_icon_is_image(string $icon): bool
{
    return (bool) preg_match('/\.(png|jpe?g|webp|gif|svg)(\?.*)?$/i', $icon);
}

function topbar_resolve_social_icon(string $icon, string $source = ''): string
{
    $icon = trim($icon);
    if ($icon !== '' && topbar_icon_is_image($icon)) {
        return $icon;
    }

    $haystack = strtolower(trim($icon . ' ' . $source));
    foreach (topbar_social_icon_options() as $option) {
        foreach ($option['keys'] as $key) {
            if ($haystack !== '' && strpos($haystack, strtolower($key)) !== false) {
                return (string) $option['icon'];
            }
        }
    }

    return $icon;
}

function topbar_render_social_icon(string $icon, string $title = 'Red social'): string
{
    $icon = topbar_resolve_social_icon($icon, $title);
    if ($icon !== '' && topbar_icon_is_image($icon)) {
        return '<img src="' . cms_e($icon) . '" alt="' . cms_e($title) . '">';
    }

    return '<i class="' . cms_e($icon !== '' ? $icon : 'bi bi-link-45deg') . '"></i>';
}

function topbar_save_general(mysqli $db, array $section, array $post): void
{
    cms_ensure_section_tracking_columns($db);
    $idSeccion = (int) $section['id_seccion'];
    $idInstitucion = (int) $section['id_institucion'];
    $visible = (($post['visible'] ?? 'no') === 'si') ? 'si' : 'no';
    $orden = max(1, (int) ($post['orden'] ?? ($section['orden'] ?? 1)));
    $observacion = trim((string) ($post['observacion'] ?? ''));
    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;

    $stmtSeccion = $db->prepare('UPDATE seccion SET visible = ?, orden = ?, observacion = ?, actualizado_en = NOW(), actualizado_por = ? WHERE id_seccion = ?');
    $stmtSeccion->bind_param('sisii', $visible, $orden, $observacion, $idUsuario, $idSeccion);
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
        'mostrar_direccion' => (($post['mostrar_direccion'] ?? 'no') === 'si') ? 'si' : 'no',
        'mostrar_telefono' => (($post['mostrar_telefono'] ?? 'no') === 'si') ? 'si' : 'no',
        'mostrar_email' => (($post['mostrar_email'] ?? 'no') === 'si') ? 'si' : 'no',
        'mostrar_redes' => (($post['mostrar_redes'] ?? 'no') === 'si') ? 'si' : 'no',
        'mostrar_boton_ingresar' => (($post['mostrar_boton_ingresar'] ?? 'no') === 'si') ? 'si' : 'no',
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
    $icono = topbar_resolve_social_icon((string) ($post['icono'] ?? ''), $titulo . ' ' . $url);
    $visible = (($post['visible'] ?? 'no') === 'si') ? 'si' : 'no';
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

function topbar_toggle_item_visible(mysqli $db, array $section, int $idItem, string $visible): void
{
    $idSeccion = (int) $section['id_seccion'];
    $visible = $visible === 'si' ? 'si' : 'no';

    if ($visible === 'si') {
        $stmtCount = $db->prepare("SELECT COUNT(*) AS total
            FROM seccion_item
            WHERE id_seccion = ? AND etiqueta = 'red_social' AND visible = 'si' AND id_item <> ?");
        $stmtCount->bind_param('ii', $idSeccion, $idItem);
        $stmtCount->execute();
        $countResult = $stmtCount->get_result();
        $visibleCount = (int) (($countResult ? $countResult->fetch_assoc()['total'] : 0));
        $stmtCount->close();

        if ($visibleCount >= 4) {
            throw new RuntimeException('El topbar solo puede tener 4 redes sociales visibles como máximo.');
        }
    }

    $stmt = $db->prepare("UPDATE seccion_item
        SET visible = ?
        WHERE id_item = ? AND id_seccion = ? AND etiqueta = 'red_social'");
    $stmt->bind_param('sii', $visible, $idItem, $idSeccion);
    $stmt->execute();
    $stmt->close();
}

function admin_is_ajax_request(): bool
{
    return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
}

function admin_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
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
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items&saved=red_social');
        }

        if (($section['nombre_interno'] ?? '') === 'topbar' && $action === 'toggle_topbar_item_visible') {
            $nextVisible = (string) ($_POST['visible'] ?? 'no');
            topbar_toggle_item_visible($db, $section, (int) ($_POST['id_item'] ?? 0), $nextVisible);
            if (admin_is_ajax_request()) {
                admin_json_response([
                    'ok' => true,
                    'visible' => $nextVisible === 'si' ? 'si' : 'no',
                ]);
            }
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }

        if ((($section['nombre_interno'] ?? '') === 'calendario_eventos_home' || ($section['tipo_seccion'] ?? '') === 'events') && $action === 'guardar_evento') {
            $idEventoAudit = (int) ($_POST['id_evento'] ?? 0);
            $datosAntes = $idEventoAudit > 0 ? obtenerRegistroAuditoria($db, 'eventos', 'id_evento', $idEventoAudit) : null;
            $savedEventId = cms_save_event($db, $_POST);
            cms_save_event_media_uploads($db, $savedEventId);
            $datosDespues = obtenerRegistroAuditoria($db, 'eventos', 'id_evento', $savedEventId);
            registrarAuditoria($db, 'Eventos del calendario', 'eventos', $savedEventId, $idEventoAudit > 0 ? 'editar' : 'crear', $idEventoAudit > 0 ? 'Se modificó un evento' : 'Se creó un evento', $datosAntes, $datosDespues);
            cms_set_flash('success', 'El evento fue guardado correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }

        if ((($section['nombre_interno'] ?? '') === 'calendario_eventos_home' || ($section['tipo_seccion'] ?? '') === 'events') && $action === 'toggle_evento') {
            $idEventoAudit = (int) ($_POST['id_evento'] ?? 0);
            $datosAntes = obtenerRegistroAuditoria($db, 'eventos', 'id_evento', $idEventoAudit);
            cms_toggle_event_visible($db, $idEventoAudit);
            $datosDespues = obtenerRegistroAuditoria($db, 'eventos', 'id_evento', $idEventoAudit);
            $accionAudit = (int) ($datosDespues['visible'] ?? 0) === 1 ? 'publicar' : 'ocultar';
            registrarAuditoria($db, 'Eventos del calendario', 'eventos', $idEventoAudit, $accionAudit, 'Se cambió la visibilidad de un evento', $datosAntes, $datosDespues);
            if (admin_is_ajax_request()) {
                admin_json_response(['ok' => true, 'visible' => (int) ($datosDespues['visible'] ?? 0)]);
            }
            cms_set_flash('success', 'La visibilidad del evento fue actualizada.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }

        if ((($section['nombre_interno'] ?? '') === 'calendario_eventos_home' || ($section['tipo_seccion'] ?? '') === 'events') && $action === 'cancelar_evento') {
            $idEventoAudit = (int) ($_POST['id_evento'] ?? 0);
            $datosAntes = obtenerRegistroAuditoria($db, 'eventos', 'id_evento', $idEventoAudit);
            cms_cancel_event($db, $idEventoAudit);
            $datosDespues = obtenerRegistroAuditoria($db, 'eventos', 'id_evento', $idEventoAudit);
            registrarAuditoria($db, 'Eventos del calendario', 'eventos', $idEventoAudit, 'cancelar', 'Se canceló un evento', $datosAntes, $datosDespues);
            cms_set_flash('success', 'El evento fue cancelado correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }

        if ((($section['nombre_interno'] ?? '') === 'calendario_eventos_home' || ($section['tipo_seccion'] ?? '') === 'events') && strpos($action, 'toggle_evento_media:') === 0) {
            cms_toggle_event_media_visible($db, (int) substr($action, strlen('toggle_evento_media:')));
            cms_set_flash('success', 'La visibilidad del archivo multimedia fue actualizada.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items&modal=evento&evento=' . (int) ($_POST['id_evento'] ?? 0));
        }

        if ((($section['nombre_interno'] ?? '') === 'calendario_eventos_home' || ($section['tipo_seccion'] ?? '') === 'events') && strpos($action, 'eliminar_evento_media:') === 0) {
            cms_delete_event_media($db, (int) substr($action, strlen('eliminar_evento_media:')));
            cms_set_flash('success', 'El archivo multimedia fue eliminado.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items&modal=evento&evento=' . (int) ($_POST['id_evento'] ?? 0));
        }

        if ($action === 'toggle_item_visible') {
            $idItemToggle = (int) ($_POST['id_item'] ?? 0);
            $newVisible = (string) ($_POST['visible'] ?? 'no');
            $newVisible = $newVisible === 'si' ? 'si' : 'no';
            $stmtToggle = $db->prepare('UPDATE seccion_item SET visible = ? WHERE id_item = ? AND id_seccion = ?');
            $stmtToggle->bind_param('sii', $newVisible, $idItemToggle, $idSeccion);
            $stmtToggle->execute();
            $stmtToggle->close();
            if (admin_is_ajax_request()) {
                admin_json_response(['ok' => true, 'visible' => $newVisible]);
            }
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }

        if ($action === 'reorder_items') {
            $ids = array_map('intval', (array) ($_POST['items'] ?? []));
            foreach ($ids as $index => $idReorder) {
                if ($idReorder <= 0) { continue; }
                $orden = $index + 1;
                $stmtReorder = $db->prepare('UPDATE seccion_item SET orden = ? WHERE id_item = ? AND id_seccion = ?');
                $stmtReorder->bind_param('iii', $orden, $idReorder, $idSeccion);
                $stmtReorder->execute();
                $stmtReorder->close();
            }
            if (admin_is_ajax_request()) {
                admin_json_response(['ok' => true]);
            }
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }

        if ($action === 'guardar_seccion') {
            $datosAntes = cms_get_section_configs($db, $idSeccion);
            cms_save_section($db, $idSeccion, $_POST);
            $datosDespues = cms_get_section_configs($db, $idSeccion);
            registrarAuditoria($db, 'Configuración de contenedores', 'seccion_config', $idSeccion, 'editar', 'Se modificó la configuración de un contenedor', $datosAntes, $datosDespues);
            cms_set_flash('success', 'El contenedor fue actualizado correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion);
        }

        if ($action === 'guardar_item') {
            $idItemAudit = (int) ($_POST['id_item'] ?? 0);
            $datosAntes = $idItemAudit > 0 ? obtenerRegistroAuditoria($db, 'seccion_item', 'id_item', $idItemAudit) : null;
            $savedItemId = cms_save_item($db, $section, $_POST);
            $datosDespues = obtenerRegistroAuditoria($db, 'seccion_item', 'id_item', $savedItemId);
            registrarAuditoria($db, 'Items de contenedor', 'seccion_item', $savedItemId, $idItemAudit > 0 ? 'editar' : 'crear', $idItemAudit > 0 ? 'Se modificó un item de contenedor' : 'Se creó un item de contenedor', $datosAntes, $datosDespues);
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items&saved=item');
        }

        if ($action === 'eliminar_item') {
            $idItemAudit = (int) ($_POST['id_item'] ?? 0);
            $datosAntes = obtenerRegistroAuditoria($db, 'seccion_item', 'id_item', $idItemAudit);
            cms_delete_item($db, $idItemAudit);
            registrarAuditoria($db, 'Items de contenedor', 'seccion_item', $idItemAudit, 'eliminar', 'Se eliminó un item de contenedor', $datosAntes, null);
            cms_set_flash('success', 'El item fue eliminado correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }
    }
} catch (Throwable $e) {
    if (admin_is_ajax_request()) {
        admin_json_response([
            'ok' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
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
$isEventsCalendar = ($section['nombre_interno'] ?? '') === 'calendario_eventos_home' || ($section['tipo_seccion'] ?? '') === 'events';
$isVideoFeatured = ($section['nombre_interno'] ?? '') === 'video_destacado_home' || ($section['tipo_seccion'] ?? '') === 'video';
$isModal = in_array(($section['nombre_interno'] ?? ''), ['modal_informativo', 'modal_bienvenida'], true) || ($section['tipo_seccion'] ?? '') === 'modal';
$isCarouselAdmin = in_array(($section['tipo_seccion'] ?? ''), ['carousel', 'hero'], true);
$isGalleryAdmin  = ($section['tipo_seccion'] ?? '') === 'gallery';
$eventosCalendario = $isEventsCalendar ? cms_list_events($db, 300) : [];
$editingEvent = ($isEventsCalendar && isset($_GET['evento'])) ? cms_get_event($db, (int) $_GET['evento']) : null;
$editingEventMedia = ($isEventsCalendar && $editingEvent) ? cms_list_event_media($db, (int) ($editingEvent['id_evento'] ?? 0), false) : [];
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
$topbarSocialIcons = topbar_social_icon_options();

admin_render_layout_start([
    'title' => 'Editar contenedor | ' . ($section['titulo_admin'] ?? 'Contenedor'),
    'page_title' => $section['titulo_admin'] ?? 'Editar contenedor',
    'breadcrumb' => 'Contenedores del sitio / ' . ($section['nombre_interno'] ?? ''),
    'active_panel' => 'contenedores',
    'institution_name' => $site['institution']['nombre'] ?? 'Institución activa',
    'institution_short_name' => $site['institution']['nombre_corto'] ?? ($site['institution']['nombre'] ?? 'Institución'),
    'institution_logo' => $site['institution']['logo_header'] ?? '',
    'color_primario' => $site['institution']['color_primario'] ?? '',
    'color_secundario' => $site['institution']['color_secundario'] ?? '',
    'color_terciario' => $site['institution']['color_terciario'] ?? '',
    'color_cuaternario' => $site['institution']['color_cuaternario'] ?? '',
    'admin_name' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'header_actions' => '<a href="admin.php?panel=contenedores" class="btn btn-soft"><i class="bi bi-arrow-left me-2"></i>Volver</a><a href="preview_contenedor.php?id=' . (int) $idSeccion . '" class="btn btn-premium"><i class="bi bi-eye me-2"></i>Visualizar</a>',
    'extra_head' => <<<'HTML'
    <style>
        .hero-card { border: 1px solid var(--adm-border); border-radius: 10px; overflow: hidden; background: #fff; height: 100%; }
        .hero-thumb { height: 180px; background-size: cover; background-position: center; }
        .carousel-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 18px;
        }
        .carousel-admin-card {
            min-height: 360px;
            border: 1px solid var(--adm-border);
            border-radius: 10px;
            background: #fff;
            box-shadow: var(--adm-shadow-sm);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .carousel-admin-media {
            position: relative;
            min-height: 190px;
            background: linear-gradient(135deg, #f8fafc, #eef4fb);
            display: grid;
            place-items: center;
            border-bottom: 1px solid var(--adm-border);
        }
        .carousel-admin-media img {
            width: 100%;
            height: 190px;
            display: block;
            object-fit: cover;
            object-position: center;
        }
        .carousel-admin-placeholder {
            width: 74px;
            height: 74px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: var(--adm-brand-gradient-soft);
            color: var(--adm-tertiary);
            font-size: 1.8rem;
        }
        .carousel-card-menu {
            position: absolute;
            top: 14px;
            right: 14px;
        }
        .carousel-card-menu .dropdown-toggle {
            width: 34px;
            height: 34px;
            border: 1px solid rgba(255,255,255,.68);
            border-radius: 999px;
            background: rgba(255,255,255,.92);
            color: var(--adm-muted);
            display: inline-grid;
            place-items: center;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .14);
        }
        .carousel-card-menu .dropdown-toggle::after {
            display: none;
        }
        .carousel-admin-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 9px;
            flex: 1;
        }
        .carousel-admin-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--adm-text);
        }
        .carousel-admin-meta {
            color: var(--adm-muted);
            font-size: .82rem;
        }
        .carousel-admin-desc {
            color: var(--adm-text-2);
            font-size: .88rem;
            margin: 0;
        }
        .carousel-admin-card { cursor: grab; }
        .carousel-admin-card:active { cursor: grabbing; }
        .carousel-admin-card.sortable-ghost { opacity: .35; border: 2px dashed var(--adm-primary); }
        .carousel-admin-card.sortable-chosen { box-shadow: 0 20px 40px rgba(15,23,42,.18) !important; transform: scale(1.025); z-index: 10; }
        .carousel-admin-card.sortable-drag { box-shadow: 0 24px 48px rgba(15,23,42,.22) !important; }
        .carousel-drag-hint {
            display: flex; align-items: center; gap: 6px;
            font-size: .78rem; color: var(--adm-muted); margin-bottom: 14px;
        }
        .carousel-drag-hint i { font-size: 1rem; }
        .admin-tabs-card { padding: 12px; }
        .admin-tabs {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px;
            border: 1px solid var(--adm-border);
            border-radius: 10px;
            background: #f8fafc;
        }
        .admin-tabs .nav-item {
            display: block;
            width: auto;
            margin: 0;
            padding: 0;
            border: 0;
            background: transparent;
            overflow: visible;
        }
        .admin-tabs .nav-link {
            min-width: 104px;
            text-align: center;
            color: var(--adm-text-2);
            border-radius: 8px;
            font-size: .875rem;
            font-weight: 700;
            padding: 9px 14px;
            line-height: 1;
            border: 1px solid transparent;
        }
        .admin-tabs .nav-link:hover {
            color: var(--adm-tertiary);
            background: rgba(var(--adm-tertiary-rgb),.06);
        }
        .admin-tabs .nav-link.active {
            background: var(--adm-brand-gradient);
            color: #fff;
            box-shadow: 0 8px 18px rgba(var(--adm-primary-rgb),.22);
        }
        .topbar-toggle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 12px;
        }
        .setting-toggle {
            min-height: 66px;
            border: 1px solid var(--adm-border);
            border-radius: 10px;
            background: #fff;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .setting-toggle-copy {
            font-weight: 700;
            color: var(--adm-text);
            font-size: .88rem;
        }
        .setting-toggle-copy small {
            display: block;
            color: var(--adm-muted);
            font-weight: 500;
            margin-top: 2px;
        }
        .state-switch { flex-shrink: 0; }
        .setting-toggle .form-check.form-switch,
        .table-check .form-check.form-switch { padding-left: 0; }
        .setting-toggle .form-check.form-switch .form-check-input,
        .table-check .form-check.form-switch .form-check-input { margin-left: 0; float: none; cursor: pointer; }
        .table-check {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: var(--adm-text-2);
            font-weight: 700;
        }
        .social-icon-presets {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-top: 10px;
        }
        .social-icon-preset {
            border: 1px solid var(--adm-border);
            border-radius: 10px;
            background: #fff;
            color: var(--adm-text-2);
            min-height: 54px;
            padding: 8px;
            font-size: 1.25rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: border-color .18s ease, background .18s ease, color .18s ease;
        }
        .social-icon-preset:hover {
            border-color: rgba(var(--adm-tertiary-rgb), .35);
            background: rgba(var(--adm-tertiary-rgb), .07);
            color: var(--adm-tertiary);
        }
        .social-icon-preset.is-selected {
            border-color: rgba(var(--adm-primary-rgb), .45);
            background: rgba(var(--adm-primary-rgb), .12);
            color: #8a5b00;
        }
        .social-icon-display {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--adm-primary-rgb), .10);
            color: var(--adm-primary);
            font-size: 1.05rem;
        }
        .social-icon-display img {
            width: 24px;
            height: 24px;
            display: block;
            object-fit: contain;
        }
        .social-icon-display.empty {
            color: var(--adm-muted);
            background: #eef2f7;
        }
        .social-icon-preset img {
            width: 30px;
            height: 30px;
            display: block;
            object-fit: contain;
        }
        .admin-modal .modal-content {
            border: 0;
            border-radius: var(--adm-radius-lg);
            overflow: hidden;
            box-shadow: var(--adm-shadow-lg);
        }
        .admin-modal .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--adm-border);
            background: var(--adm-card);
        }
        .admin-modal .modal-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--adm-text);
        }
        .admin-modal .modal-body {
            padding: 18px 20px;
            background: var(--adm-card);
        }
        #eventModal .modal-content { max-height: calc(100vh - 48px); }
        #eventModal .modal-body {
            max-height: calc(100vh - 170px);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--adm-border-dark) var(--adm-bg);
        }
        #eventModal .modal-body::-webkit-scrollbar { width: 6px; }
        #eventModal .modal-body::-webkit-scrollbar-track { background: var(--adm-bg); }
        #eventModal .modal-body::-webkit-scrollbar-thumb { background: var(--adm-border-dark); border-radius: 4px; }
        .admin-modal .modal-footer {
            padding: 12px 20px;
            border-top: 1px solid var(--adm-border);
            background: #f8fafc;
        }
        .field-card {
            background: rgba(255,255,255,0.92);
            border: 1px solid #e4ebf5;
            border-radius: 10px;
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
            color: var(--adm-tertiary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: .2s ease;
        }
        .field-help-btn:hover {
            background: rgba(var(--adm-tertiary-rgb),.08);
            color: var(--adm-tertiary);
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
        .admin-event-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 16px;
            border: 1px solid #e4ebf5;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 18px rgba(18, 35, 68, 0.04);
            margin-bottom: 18px;
        }
        .event-import-form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .event-import-form .form-control {
            max-width: 260px;
        }
        .event-upload-overlay {
            position: fixed;
            inset: 0;
            z-index: 1090;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.42);
            backdrop-filter: blur(3px);
        }
        .event-upload-overlay.is-visible {
            display: flex;
        }
        .event-upload-card {
            width: min(360px, calc(100vw - 32px));
            border-radius: 18px;
            background: #fff;
            padding: 24px;
            text-align: center;
            box-shadow: 0 24px 54px rgba(15, 23, 42, 0.2);
        }
        .event-upload-card .spinner-border {
            color: var(--adm-primary);
            width: 2.5rem;
            height: 2.5rem;
        }
        .event-upload-card strong {
            display: block;
            margin-top: 14px;
            color: #162338;
            font-weight: 800;
        }
        .event-upload-card small {
            display: block;
            margin-top: 4px;
            color: #72809a;
        }
        .event-media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 12px;
        }
        .event-media-card {
            border: 1px solid #e4ebf5;
            border-radius: 14px;
            overflow: hidden;
            background: #f8fbff;
        }
        .event-media-thumb {
            aspect-ratio: 16 / 10;
            display: grid;
            place-items: center;
            background: #eaf1f8;
            color: #12324a;
            font-size: 1.6rem;
        }
        .event-media-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .event-media-body {
            padding: 10px;
        }
        .event-media-body strong,
        .event-media-body small {
            display: block;
        }
        .event-media-body small {
            color: #72809a;
        }
        .event-help-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #bfd2ee;
            color: var(--adm-tertiary);
            background: rgba(var(--adm-tertiary-rgb),.08);
        }
        .event-help-btn:hover {
            color: #fff;
            background: var(--adm-tertiary);
            border-color: var(--adm-tertiary);
        }
        .event-help-offcanvas .offcanvas-header {
            background: var(--adm-tertiary);
            color: #fff;
        }
        .event-help-offcanvas .help-step {
            border: 1px solid #e4ebf5;
            border-radius: 12px;
            padding: 12px;
            background: #fff;
            margin-bottom: 10px;
        }
        .event-help-offcanvas .help-step strong {
            display: block;
            color: #162338;
            margin-bottom: 4px;
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

<div class="section-card admin-tabs-card">
    <ul class="nav admin-tabs">
        <li class="nav-item"><a class="nav-link <?= $tab === 'general' ? 'active' : '' ?>" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=general"><i class="bi bi-sliders me-1"></i>General</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'items' ? 'active' : '' ?>" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items"><i class="bi bi-layers me-1"></i>Items</a></li>
        <li class="nav-item"><a class="nav-link js-preview-btn" href="preview_contenedor.php?id=<?= (int) $idSeccion ?>" data-preview-title="<?= cms_e($section['titulo_admin'] ?? 'Contenedor') ?>" data-preview-url="preview_contenedor.php?id=<?= (int) $idSeccion ?>&embed=1"><i class="bi bi-eye me-1"></i>Vista previa</a></li>
    </ul>
</div>

<?php if ($tab === 'general'): ?>
    <div class="section-card">
        <div class="section-head">
            <div>
                <h3>Configuración del contenedor</h3>
                <p><?= $isTopbar ? 'Datos de contacto, visibilidad y comportamiento del topbar.' : ($isCarouselAdmin ? 'Visible, observación y opciones del carrusel guardadas en <code>seccion_config</code>.' : 'Visible, orden, observación y claves guardadas en <code>seccion_config</code>.') ?></p>
            </div>
        </div>
        <?php if ($isTopbar): ?>
            <form method="post" class="js-confirm-submit" data-confirm-title="Guardar topbar" data-confirm-msg="Se actualizarán los datos visibles del topbar superior.">
                <input type="hidden" name="accion" value="guardar_topbar_general">
                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="setting-toggle h-100">
                            <span class="setting-toggle-copy">Visible<small>Mostrar topbar</small></span>
                            <span class="form-check form-switch mb-0 state-switch">
                                <input class="form-check-input" type="checkbox" name="visible" value="si" <?= ($section['visible'] ?? '') === 'si' ? 'checked' : '' ?>>
                                <span class="state-switch-track" data-on="Si" data-off="No"></span>
                            </span>
                        </label>
                    </div>
                    <div class="col-md-9">
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
                    <div class="col-12">
                        <div class="topbar-toggle-grid">
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Dirección<small>Mostrar en el topbar</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input class="form-check-input" type="checkbox" name="mostrar_direccion" value="si" <?= $topbarConfigs['mostrar_direccion'] === 'si' ? 'checked' : '' ?>>
                                </span>
                            </label>
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Teléfono<small>Mostrar en el topbar</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input class="form-check-input" type="checkbox" name="mostrar_telefono" value="si" <?= $topbarConfigs['mostrar_telefono'] === 'si' ? 'checked' : '' ?>>
                                </span>
                            </label>
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Correo<small>Mostrar en el topbar</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input class="form-check-input" type="checkbox" name="mostrar_email" value="si" <?= $topbarConfigs['mostrar_email'] === 'si' ? 'checked' : '' ?>>
                                </span>
                            </label>
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Redes<small>Mostrar redes sociales</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input class="form-check-input" type="checkbox" name="mostrar_redes" value="si" <?= $topbarConfigs['mostrar_redes'] === 'si' ? 'checked' : '' ?>>
                                </span>
                            </label>
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Botón ingresar<small>Mostrar acceso al sistema</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input class="form-check-input" type="checkbox" name="mostrar_boton_ingresar" value="si" <?= $topbarConfigs['mostrar_boton_ingresar'] === 'si' ? 'checked' : '' ?>>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-premium"><i class="bi bi-save me-1"></i>Guardar topbar</button>
                </div>
            </form>
        <?php else: ?>
            <?php if ($isModal): ?>
                <div class="alert border-0 mb-4" style="background:#f0f7ff;color:#1a4080;border-radius:12px;font-size:.875rem;">
                    <strong><i class="bi bi-info-circle me-1"></i>Configuración del Modal informativo</strong><br>
                    <strong>Opción A — desde esta pestaña (General):</strong> agrega estas claves de configuración:<br>
                    &nbsp;&nbsp;<code>titulo</code> → título del modal<br>
                    &nbsp;&nbsp;<code>descripcion</code> → texto del cuerpo<br>
                    &nbsp;&nbsp;<code>imagen</code> → ruta a la imagen (ej: <code>uploads/modal/imagen.jpg</code>)<br>
                    &nbsp;&nbsp;<code>boton_texto</code> → texto del botón (dejar vacío para no mostrar)<br>
                    &nbsp;&nbsp;<code>boton_url</code> → URL del botón<br>
                    <strong>Opción B — desde la pestaña Items:</strong> agrega un item con imagen, título, descripción y botón.<br>
                    <strong>Comportamiento:</strong> <code>mostrar</code> → <code>siempre</code> o <code>una_vez</code> (default) · <code>delay_ms</code> → ej: <code>1200</code>
                </div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="accion" value="guardar_seccion">
                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                <?php if ($isCarouselAdmin || $isVideoFeatured): ?>
                    <input type="hidden" name="orden" value="<?= (int) $section['orden'] ?>">
                <?php endif; ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="setting-toggle h-100">
                            <span class="setting-toggle-copy">Visible<small>Mostrar contenedor</small></span>
                            <span class="form-check form-switch mb-0 state-switch">
                                <input class="form-check-input" type="checkbox" name="visible" value="si" <?= ($section['visible'] ?? '') === 'si' ? 'checked' : '' ?>>
                            </span>
                        </label>
                    </div>
                    <?php if (!$isCarouselAdmin && !$isVideoFeatured): ?>
                        <div class="col-md-3">
                            <label class="form-label">Orden</label>
                            <input class="form-control" type="number" name="orden" min="1" value="<?= (int) $section['orden'] ?>">
                        </div>
                    <?php endif; ?>
                    <div class="<?= ($isCarouselAdmin || $isVideoFeatured) ? 'col-md-9' : 'col-md-6' ?>">
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
                                <?php $configValue = strtolower(trim((string) ($config['valor'] ?? ''))); ?>
                                <?php if (in_array($configValue, ['si', 'no'], true)): ?>
                                    <label class="setting-toggle mb-0">
                                        <span class="setting-toggle-copy">Valor<small><?= $configValue === 'si' ? 'Activo' : 'Inactivo' ?></small></span>
                                        <span class="form-check form-switch mb-0 state-switch">
                                            <input type="hidden" name="config_value[]" value="<?= $configValue === 'si' ? 'si' : 'no' ?>">
                                            <input class="form-check-input js-config-boolean-toggle" type="checkbox" <?= $configValue === 'si' ? 'checked' : '' ?>>
                                                </span>
                                    </label>
                                <?php else: ?>
                                    <input class="form-control" name="config_value[]" value="<?= cms_e($config['valor']) ?>">
                                <?php endif; ?>
                            </div>
                            <div class="col-auto d-flex align-items-end pb-1">
                                <button type="button" class="btn-icon delete remove-config-row" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$configs): ?>
                        <div class="row g-3 align-items-end mb-3 config-row">
                            <div class="col-md-4"><label class="form-label">Clave</label><input class="form-control" name="config_key[]" placeholder="titulo_bloque"></div>
                            <div class="col-md-7"><label class="form-label">Valor</label><input class="form-control" name="config_value[]" placeholder="Últimas Noticias"></div>
                            <div class="col-auto d-flex align-items-end pb-1"><button type="button" class="btn-icon delete remove-config-row" title="Eliminar"><i class="bi bi-trash"></i></button></div>
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
                <h3><?= $isEventsCalendar ? 'Eventos del calendario' : 'Items del contenedor' ?></h3>
                <p><?= $isEventsCalendar ? 'Carga individual y masiva de eventos reales desde la tabla eventos.' : 'Administración específica del bloque.' ?></p>
            </div>
            <?php if (!$isEventsCalendar && !$isVideoFeatured): ?>
                <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item" class="btn btn-premium"><i class="bi bi-plus-circle me-1"></i>Agregar item</a>
            <?php endif; ?>
        </div>

        <?php if ($isEventsCalendar): ?>
            <div class="admin-event-toolbar">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=evento" class="btn btn-premium"><i class="bi bi-plus-circle me-1"></i>Agregar evento</a>
                    <a href="eventos_descargar_plantilla.php?id=<?= (int) $idSeccion ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Descargar plantilla Excel</a>
                    <button type="button" class="event-help-btn" data-eventos-help title="Ayuda de carga masiva"><i class="bi bi-question-circle-fill"></i></button>
                    <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item" class="btn btn-outline-secondary"><i class="bi bi-layers me-1"></i>Agregar item legacy</a>
                </div>
                <form class="event-import-form" id="eventExcelUploadForm" method="post" action="eventos_preview_importacion.php" enctype="multipart/form-data">
                    <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                    <input class="form-control" type="file" name="archivo_excel" accept=".xlsx,.xls,.csv" required>
                    <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i>Subir eventos desde Excel</button>
                </form>
            </div>
            <div class="event-upload-overlay" id="eventExcelUploadOverlay" aria-hidden="true">
                <div class="event-upload-card">
                    <div class="spinner-border" role="status" aria-label="Validando archivo"></div>
                    <strong>Validando archivo Excel</strong>
                    <small>Preparando preview de eventos...</small>
                </div>
            </div>

            <div class="offcanvas offcanvas-end event-help-offcanvas" tabindex="-1" id="eventosExcelHelp" aria-labelledby="eventosExcelHelpLabel">
                <div class="offcanvas-header">
                    <div>
                        <h5 class="offcanvas-title" id="eventosExcelHelpLabel">Carga masiva de eventos</h5>
                        <small>Flujo institucional con validación previa</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body bg-light">
                    <div class="help-step"><strong>1. Descargar plantilla</strong>Usa la plantilla Excel oficial. La hoja EVENTOS contiene solo los campos cargables y la hoja AYUDA documenta formatos y ejemplos.</div>
                    <div class="help-step"><strong>2. Completar columnas</strong>El titulo y fecha_inicio son obligatorios. Las fechas usan yyyy-mm-dd y las horas HH:mm.</div>
                    <div class="help-step"><strong>3. Subir archivo</strong>La subida no publica nada. El sistema solo interpreta el Excel y muestra una tabla de preview.</div>
                    <div class="help-step"><strong>4. Validar filas</strong>Cada fila muestra su estado: valido, fecha invalida, falta titulo, categoria vacia o duplicado.</div>
                    <div class="help-step"><strong>5. Seleccionar y confirmar</strong>Marca los eventos que deseas importar o usa Seleccionar todos. La inserción real ocurre con Cargar eventos seleccionados.</div>
                    <div class="help-step"><strong>Regla calendario/eventos</strong>Este flujo trabaja solo con la tabla eventos. No crea ni modifica registros de calendario, feriados ni días institucionales.</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Visible</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventosCalendario as $evento): ?>
                            <?php
                            $eventId = (int) ($evento['id_evento'] ?? ($evento['id'] ?? 0));
                            $eventTitle = $evento['titulo'] ?? '';
                            ?>
                            <tr>
                                <td><?= cms_e($evento['fecha_inicio'] ?? '') ?></td>
                                <td><?= cms_e($evento['hora_inicio'] ?? '') ?></td>
                                <td><?= cms_e($eventTitle) ?></td>
                                <td><?= cms_e($evento['categoria'] ?? '') ?></td>
                                <td><?= cms_e($evento['ubicacion'] ?? '') ?></td>
                                <td><span class="badge-soft <?= ($evento['estado'] ?? '') === 'publicado' ? 'success' : 'warning' ?>"><?= cms_e($evento['estado'] ?? '') ?></span></td>
                                <td>
                                    <form method="post" class="m-0 js-visible-toggle-form">
                                        <input type="hidden" name="accion" value="toggle_evento">
                                        <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                        <input type="hidden" name="id_evento" value="<?= $eventId ?>">
                                        <span class="form-check form-switch mb-0 state-switch" style="padding-left:0;">
                                            <input class="form-check-input js-evento-visible-toggle" type="checkbox" role="switch" style="margin-left:0;cursor:pointer;" <?= (int) ($evento['visible'] ?? 1) === 1 ? 'checked' : '' ?>>
                                        </span>
                                    </form>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=evento&evento=<?= $eventId ?>" class="btn-icon edit" title="Editar" aria-label="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="post" class="m-0" onsubmit="return confirm('¿Cancelar este evento?');">
                                            <input type="hidden" name="accion" value="cancelar_evento">
                                            <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                            <input type="hidden" name="id_evento" value="<?= $eventId ?>">
                                            <button type="submit" class="btn-icon" title="Cancelar" aria-label="Cancelar" style="color:var(--adm-danger-brand);border-color:var(--adm-danger-brand);">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                        <a href="evento_detalle.php?id_evento=<?= $eventId ?>" target="_blank" class="btn-icon preview" title="Ver detalle" aria-label="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($isTopbar): ?>
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
                            <th>Icono</th>
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
                                <td>
                                    <span class="social-icon-display <?= trim((string) ($item['icono'] ?? '')) === '' ? 'empty' : '' ?>" title="<?= cms_e($item['titulo'] ?? 'Red social') ?>">
                                        <?= topbar_render_social_icon((string) ($item['icono'] ?? ''), (string) ($item['titulo'] ?? 'Red social')) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" class="m-0 js-visible-toggle-form">
                                        <input type="hidden" name="accion" value="toggle_topbar_item_visible">
                                        <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                        <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                        <input type="hidden" name="visible" value="<?= ($item['visible'] ?? '') === 'si' ? 'no' : 'si' ?>">
                                        <label class="table-check mb-0" title="<?= ($item['visible'] ?? '') === 'si' ? 'Dejar oculta' : 'Dejar visible' ?>">
                                            <span class="form-check form-switch mb-0 state-switch">
                                                <input class="form-check-input js-visible-toggle" type="checkbox" <?= ($item['visible'] ?? '') === 'si' ? 'checked' : '' ?>>
                                                    </span>
                                        </label>
                                    </form>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= cms_e($item['descripcion'] ?? '#') ?>" target="_blank" rel="noopener" class="btn-icon preview" title="Ver red social" aria-label="Ver red social">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn-icon edit" title="Editar" aria-label="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="post" class="m-0" onsubmit="return confirm('¿Eliminar esta red social?');">
                                            <input type="hidden" name="accion" value="eliminar_item">
                                            <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                            <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                            <button type="submit" class="btn-icon delete" title="Eliminar" aria-label="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($isCarouselAdmin): ?>
            <div class="carousel-drag-hint">
                <i class="bi bi-grip-vertical"></i> Arrastra las cards para cambiar el orden. El cambio se guarda automáticamente.
            </div>
            <div class="carousel-card-grid mb-4" id="carouselSortable">
                <?php foreach ($items as $item): ?>
                    <?php $slideTitle = trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? '')); ?>
                    <article class="carousel-admin-card" data-id="<?= (int) $item['id_item'] ?>">
                        <div class="carousel-admin-media">
                            <?php if (!empty($item['imagen'])): ?>
                                <img src="<?= cms_e($item['imagen']) ?>" alt="<?= cms_e($slideTitle ?: 'Slide del carrusel') ?>">
                            <?php else: ?>
                                <div class="carousel-admin-placeholder"><i class="bi bi-image"></i></div>
                            <?php endif; ?>
                            <div class="dropdown carousel-card-menu">
                                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <button type="button" class="dropdown-item js-carousel-edit"
                                            data-item="<?= htmlspecialchars(json_encode([
                                                'id_item'        => (int) $item['id_item'],
                                                'etiqueta'       => $item['etiqueta'] ?? '',
                                                'titulo_linea_1' => $item['titulo_linea_1'] ?? '',
                                                'titulo_linea_2' => $item['titulo_linea_2'] ?? '',
                                                'titulo_linea_3' => $item['titulo_linea_3'] ?? '',
                                                'descripcion'    => $item['descripcion'] ?? '',
                                                'boton_1_texto'  => $item['boton_1_texto'] ?? '',
                                                'boton_1_url'    => $item['boton_1_url'] ?? '',
                                                'boton_2_texto'  => $item['boton_2_texto'] ?? '',
                                                'boton_2_url'    => $item['boton_2_url'] ?? '',
                                                'visible'        => $item['visible'] ?? 'si',
                                                'orden'          => (int) ($item['orden'] ?? 1),
                                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-pencil-square me-2"></i>Editar
                                    </button>
                                    <form method="post" onsubmit="return confirm('¿Eliminar este item?');">
                                        <input type="hidden" name="accion" value="eliminar_item">
                                        <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                        <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash me-2"></i>Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-admin-body">
                                <button type="button"
                                        class="badge-soft <?= ($item['visible'] ?? '') === 'si' ? 'success' : 'warning' ?> js-toggle-badge"
                                        style="border:none;cursor:pointer;"
                                        data-item-id="<?= (int) $item['id_item'] ?>"
                                        data-item-visible="<?= cms_e($item['visible'] ?? 'no') ?>"
                                        data-id-seccion="<?= (int) $idSeccion ?>"
                                        title="Cambiar visibilidad">
                                    <?= ($item['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?>
                                </button>
                            <h4 class="carousel-admin-title"><?= cms_e($slideTitle ?: 'Slide sin título') ?></h4>
                            <?php if (!empty($item['etiqueta'])): ?>
                                <div class="carousel-admin-meta"><?= cms_e($item['etiqueta']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item['descripcion'])): ?>
                                <p class="carousel-admin-desc"><?= cms_e($item['descripcion']) ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php elseif ($section['tipo_seccion'] === 'events'): ?>
            <div class="row g-4 mb-4">
                <?php foreach ($items as $item): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="hero-card">
                            <div class="hero-thumb" style="background-image:url('<?= cms_e($item['imagen'] ?: 'assets/images/frontis_01.jpg') ?>')"></div>
                            <div class="p-3">
                                <span class="badge-soft <?= ($item['visible'] ?? '') === 'si' ? 'success' : 'warning' ?>"><?= ($item['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?></span>
                                <h5 class="mt-3 mb-1"><?= cms_e($item['titulo'] ?? '') ?></h5>
                                <small class="text-muted"><?= cms_e($item['fecha_publicacion'] ?? '') ?> · <?= cms_e($item['subtitulo'] ?? '') ?></small>
                                <p class="text-muted mt-2 mb-3"><?= cms_e($item['etiqueta'] ?? '') ?></p>
                                <div class="d-flex gap-2">
                                    <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                    <form method="post" onsubmit="return confirm('¿Eliminar este evento?');">
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
        <?php elseif ($isGalleryAdmin): ?>
            <div class="carousel-drag-hint">
                <i class="bi bi-grip-vertical"></i> Arrastra las cards para cambiar el orden. El cambio se guarda automáticamente.
            </div>
            <div class="carousel-card-grid mb-4" id="gallerySortable">
                <?php foreach ($items as $item): ?>
                    <article class="carousel-admin-card" data-id="<?= (int) $item['id_item'] ?>">
                        <div class="carousel-admin-media">
                            <?php if (!empty($item['imagen'])): ?>
                                <img src="<?= cms_e($item['imagen']) ?>" alt="<?= cms_e($item['titulo'] ?: 'Imagen de galería') ?>">
                            <?php else: ?>
                                <div class="carousel-admin-placeholder"><i class="bi bi-image"></i></div>
                            <?php endif; ?>
                            <div class="dropdown carousel-card-menu">
                                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <a class="dropdown-item" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>">
                                        <i class="bi bi-pencil-square me-2"></i>Editar
                                    </a>
                                    <form method="post" onsubmit="return confirm('¿Eliminar esta imagen?');">
                                        <input type="hidden" name="accion" value="eliminar_item">
                                        <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                        <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash me-2"></i>Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-admin-body">
                            <button type="button"
                                    class="badge-soft <?= ($item['visible'] ?? '') === 'si' ? 'success' : 'warning' ?> js-toggle-badge"
                                    style="border:none;cursor:pointer;"
                                    data-item-id="<?= (int) $item['id_item'] ?>"
                                    data-item-visible="<?= cms_e($item['visible'] ?? 'no') ?>"
                                    data-id-seccion="<?= (int) $idSeccion ?>"
                                    title="Cambiar visibilidad">
                                <?= ($item['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?>
                            </button>
                            <h4 class="carousel-admin-title"><?= cms_e($item['titulo'] ?: 'Sin título') ?></h4>
                            <?php if (!empty($item['descripcion'])): ?>
                                <p class="carousel-admin-desc"><?= cms_e($item['descripcion']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['url'])): ?>
                                <div class="carousel-admin-meta"><i class="bi bi-link-45deg me-1"></i><?= cms_e($item['url']) ?></div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                    <div class="col-12 text-center text-muted py-5" style="grid-column:1/-1;">
                        <i class="bi bi-images" style="font-size:2.5rem;display:block;margin-bottom:10px;"></i>
                        No hay imágenes en la galería. Usa "Agregar item" para subir la primera.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$isTopbar && !$isEventsCalendar && !$isCarouselAdmin && !$isGalleryAdmin): ?>
            <div class="carousel-drag-hint">
                <i class="bi bi-grip-vertical"></i> Arrastra las filas para cambiar el orden. El cambio se guarda automáticamente.
            </div>
            <div class="table-responsive">
                <table class="table table-modern align-middle" id="generalItemsTable">
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th style="width:52px;">Orden</th>
                            <th><?= $isVideoFeatured ? 'Video' : 'Título' ?></th>
                            <th style="width:90px;">Visible</th>
                            <th style="width:110px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="generalItemsTbody">
                        <?php foreach ($items as $item): ?>
                            <tr data-id="<?= (int) $item['id_item'] ?>">
                                <td style="text-align:center;vertical-align:middle;">
                                    <i class="bi bi-grip-vertical drag-handle" style="color:var(--adm-muted);font-size:1.1rem;cursor:grab;"></i>
                                </td>
                                <td class="item-orden-cell"><?= (int) $item['orden'] ?></td>
                                <td>
                                    <?php $displayTitle = $item['titulo'] ?: trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? '')); ?>
                                    <?php if ($isVideoFeatured): ?>
                                        <div class="fw-semibold"><?= cms_e($displayTitle ?: 'Video destacado') ?></div>
                                        <small class="text-muted"><?= cms_e($item['url'] ?? '') ?></small>
                                    <?php else: ?>
                                        <?= cms_e($displayTitle) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form class="m-0 js-visible-toggle-form">
                                        <input type="hidden" name="accion" value="toggle_item_visible">
                                        <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                        <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                        <input type="hidden" name="visible" value="<?= ($item['visible'] ?? '') === 'si' ? 'no' : 'si' ?>">
                                        <span class="form-check form-switch mb-0 state-switch" style="padding-left:0;">
                                            <input class="form-check-input js-visible-toggle" type="checkbox" role="switch" style="margin-left:0;cursor:pointer;" <?= ($item['visible'] ?? '') === 'si' ? 'checked' : '' ?>>
                                        </span>
                                    </form>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= cms_e(!empty($item['boton_1_url']) ? $item['boton_1_url'] : (!empty($item['url']) ? $item['url'] : '#')) ?>" target="_blank" class="btn-icon preview" title="Ver" aria-label="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn-icon edit" title="Editar" aria-label="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="post" class="m-0" onsubmit="return confirm('¿Eliminar este item?');">
                                            <input type="hidden" name="accion" value="eliminar_item">
                                            <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                            <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                            <button type="submit" class="btn-icon delete" title="Eliminar" aria-label="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
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

<?php if ($isEventsCalendar): ?>
    <?php
    $eventForm = $editingEvent ?: [];
    $eventIdValue = (int) ($eventForm['id_evento'] ?? ($eventForm['id'] ?? 0));
    ?>
    <div class="modal fade admin-modal" id="eventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="guardar_evento">
                    <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                    <input type="hidden" name="id_evento" value="<?= $eventIdValue ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= $eventIdValue > 0 ? 'Editar evento' : 'Agregar evento' ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Título del evento', 'evento_titulo', 'calendario_eventos_home', 'titulo'); ?>
                                    <input class="form-control" id="evento_titulo" name="titulo" value="<?= cms_e($eventForm['titulo'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Categoría', 'evento_categoria', 'calendario_eventos_home', 'categoria'); ?>
                                    <select class="form-select" id="evento_categoria" name="categoria">
                                        <?php foreach (['Pastoral', 'Académico', 'Deportivo', 'Institucional'] as $categoryOption): ?>
                                            <option value="<?= cms_e($categoryOption) ?>" <?= (($eventForm['categoria'] ?? '') === $categoryOption) ? 'selected' : '' ?>><?= cms_e($categoryOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Descripción corta', 'evento_descripcion_corta', 'calendario_eventos_home', 'descripcion-corta'); ?>
                                    <textarea class="form-control" id="evento_descripcion_corta" name="descripcion_corta"><?= cms_e($eventForm['descripcion_corta'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Descripción completa', 'evento_descripcion', 'calendario_eventos_home', 'descripcion'); ?>
                                    <textarea class="form-control" id="evento_descripcion" name="descripcion"><?= cms_e($eventForm['descripcion'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Fecha inicio', 'evento_fecha_inicio', 'calendario_eventos_home', 'fecha-inicio'); ?>
                                    <input class="form-control" id="evento_fecha_inicio" type="date" name="fecha_inicio" value="<?= cms_e($eventForm['fecha_inicio'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Fecha término', 'evento_fecha_termino', 'calendario_eventos_home', 'fecha-termino'); ?>
                                    <input class="form-control" id="evento_fecha_termino" type="date" name="fecha_termino" value="<?= cms_e($eventForm['fecha_termino'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Hora inicio', 'evento_hora_inicio', 'calendario_eventos_home', 'hora-inicio'); ?>
                                    <input class="form-control" id="evento_hora_inicio" type="time" name="hora_inicio" value="<?= cms_e(substr((string) ($eventForm['hora_inicio'] ?? ''), 0, 5)) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Hora término', 'evento_hora_termino', 'calendario_eventos_home', 'hora-termino'); ?>
                                    <input class="form-control" id="evento_hora_termino" type="time" name="hora_termino" value="<?= cms_e(substr((string) ($eventForm['hora_termino'] ?? ''), 0, 5)) ?>">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Ubicación', 'evento_ubicacion', 'calendario_eventos_home', 'ubicacion'); ?>
                                    <input class="form-control" id="evento_ubicacion" name="ubicacion" value="<?= cms_e($eventForm['ubicacion'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Color', 'evento_color', 'calendario_eventos_home', 'color'); ?>
                                    <input class="form-control" id="evento_color" type="color" name="color" value="<?= cms_e($eventForm['color'] ?? '#fd7e14') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-card">
                                    <?php admin_modal_field_head('Estado', 'evento_estado', 'calendario_eventos_home', 'estado', false); ?>
                                    <select class="form-select" id="evento_estado" name="estado">
                                        <?php foreach (['borrador', 'publicado', 'oculto', 'cancelado'] as $stateOption): ?>
                                            <option value="<?= cms_e($stateOption) ?>" <?= (($eventForm['estado'] ?? 'publicado') === $stateOption) ? 'selected' : '' ?>><?= cms_e($stateOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Imagen principal', 'evento_imagen', 'calendario_eventos_home', 'imagen'); ?>
                                    <input class="form-control" id="evento_imagen" type="file" name="imagen" accept="image/*">
                                    <div class="field-note">Esta imagen se usa como portada/hero del detalle del evento.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="field-card">
                                    <label class="form-label d-block">Visible</label>
                                    <select class="form-select" name="visible">
                                        <option value="1" <?= (int) ($eventForm['visible'] ?? 1) === 1 ? 'selected' : '' ?>>Si</option>
                                        <option value="0" <?= (int) ($eventForm['visible'] ?? 1) === 0 ? 'selected' : '' ?>>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="field-card">
                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                                        <div>
                                            <label class="form-label d-block mb-1">Multimedia del evento</label>
                                            <div class="field-note">Galería y videos del detalle del evento. Estos archivos se guardan en <code>evento_media</code>.</div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Imágenes para galería</label>
                                            <input class="form-control" type="file" name="event_media_images[]" accept="image/*" multiple>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Videos locales</label>
                                            <input class="form-control" type="file" name="event_media_videos[]" accept="video/*" multiple>
                                        </div>
                                        <div class="col-12">
                                            <div class="event-media-grid d-none" id="eventMediaUploadPreview"></div>
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label">URL YouTube</label>
                                            <input class="form-control" name="event_media_youtube_url[]" placeholder="https://www.youtube.com/watch?v=...">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Título del video</label>
                                            <input class="form-control" name="event_media_youtube_title[]" placeholder="Resumen del evento">
                                        </div>
                                    </div>

                                    <?php if ($editingEventMedia): ?>
                                        <div class="event-media-grid mt-3">
                                            <?php foreach ($editingEventMedia as $media): ?>
                                                <div class="event-media-card">
                                                    <div class="event-media-thumb">
                                                        <?php if (($media['tipo'] ?? '') === 'imagen' && !empty($media['archivo'])): ?>
                                                            <img src="<?= cms_e($media['archivo']) ?>" alt="<?= cms_e($media['titulo'] ?? '') ?>">
                                                        <?php elseif (($media['tipo'] ?? '') === 'video'): ?>
                                                            <i class="bi bi-play-btn"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-youtube"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="event-media-body">
                                                        <strong><?= cms_e($media['titulo'] ?? ucfirst((string) ($media['tipo'] ?? 'media'))) ?></strong>
                                                        <small><?= cms_e($media['tipo'] ?? '') ?> · <?= (int) ($media['visible'] ?? 1) === 1 ? 'Visible' : 'Oculto' ?></small>
                                                        <div class="d-flex gap-2 mt-2">
                                                            <button type="submit" name="accion" value="toggle_evento_media:<?= (int) $media['id_media'] ?>" class="btn btn-sm btn-outline-warning" formnovalidate>
                                                                <?= (int) ($media['visible'] ?? 1) === 1 ? 'Ocultar' : 'Mostrar' ?>
                                                            </button>
                                                            <button type="submit" name="accion" value="eliminar_evento_media:<?= (int) $media['id_media'] ?>" class="btn btn-sm btn-outline-danger" formnovalidate onclick="return confirm('¿Eliminar este archivo multimedia?');">Eliminar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($eventIdValue > 0): ?>
                                        <div class="field-note mt-3">Este evento todavía no tiene multimedia asociado.</div>
                                    <?php else: ?>
                                        <div class="field-note mt-3">Guarda el evento para asociar y administrar multimedia.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-admin-action">Guardar evento</button>
                    </div>
                </form>
            </div>
        </div>
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
                                    <label class="form-label d-block mb-2">Icono</label>
                                    <input type="hidden" id="topbar_icono" name="icono" value="<?= cms_e($editingItem['icono'] ?? '') ?>">
                                    <div class="social-icon-presets" aria-label="Iconos rápidos">
                                        <?php foreach ($topbarSocialIcons as $socialIcon): ?>
                                            <button type="button" class="social-icon-preset" data-social-name="<?= cms_e($socialIcon['name']) ?>" data-social-icon="<?= cms_e($socialIcon['icon']) ?>" data-social-url="<?= cms_e($socialIcon['url']) ?>" title="<?= cms_e($socialIcon['name']) ?>" aria-label="<?= cms_e($socialIcon['name']) ?>">
                                                <?= topbar_render_social_icon((string) $socialIcon['icon'], (string) $socialIcon['name']) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="field-note">Elige el logo de la red social.</div>
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
                                    <label class="setting-toggle mb-0">
                                        <span class="setting-toggle-copy">Visible<small>Mostrar esta red</small></span>
                                        <span class="form-check form-switch mb-0 state-switch">
                                            <input class="form-check-input" id="topbar_visible" type="checkbox" name="visible" value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'checked' : '' ?>>
                                                </span>
                                    </label>
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
                        <?php if ($isModal): ?>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Imagen del modal', 'item_imagen_modal', $section['nombre_interno'], 'imagen', true, 'clear_imagen'); ?>
                                        <input class="form-control" id="item_imagen_modal" type="file" name="imagen" accept="image/*">
                                        <?php if (!empty($editingItem['imagen'])): ?>
                                            <div class="field-note mt-2">
                                                <img src="<?= cms_e($editingItem['imagen']) ?>" alt="Vista previa" style="max-height:80px;border-radius:8px;border:1px solid #e4ebf5;">
                                            </div>
                                        <?php endif; ?>
                                        <div class="field-note">Recomendado: 880 × 340 px. Aparece en la parte superior del modal.</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Título', 'item_titulo_modal', $section['nombre_interno'], 'titulo'); ?>
                                        <input class="form-control" id="item_titulo_modal" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>" placeholder="Bienvenidos al nuevo año escolar">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Mensaje', 'item_descripcion_modal', $section['nombre_interno'], 'descripcion'); ?>
                                        <textarea class="form-control" id="item_descripcion_modal" name="descripcion" rows="4" placeholder="Texto que aparecerá en el cuerpo del modal..."><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Texto del botón', 'item_boton1_texto_modal', $section['nombre_interno'], 'boton-1-texto'); ?>
                                        <input class="form-control" id="item_boton1_texto_modal" name="boton_1_texto" value="<?= cms_e($editingItem['boton_1_texto'] ?? '') ?>" placeholder="Ver más">
                                        <div class="field-note">Dejar vacío para no mostrar botón.</div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('URL del botón', 'item_boton1_url_modal', $section['nombre_interno'], 'boton-1-url'); ?>
                                        <input class="form-control" id="item_boton1_url_modal" name="boton_1_url" value="<?= cms_e($editingItem['boton_1_url'] ?? '') ?>" placeholder="https://...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-card">
                                        <?php admin_modal_field_head('Visible', 'item_visible_modal', $section['nombre_interno'], 'visible', false); ?>
                                        <select class="form-select" id="item_visible_modal" name="visible">
                                            <option value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'selected' : '' ?>>Si</option>
                                            <option value="no" <?= ($editingItem['visible'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-card">
                                        <?php admin_modal_field_head('Orden', 'item_orden_modal', $section['nombre_interno'], 'orden', false); ?>
                                        <input class="form-control" id="item_orden_modal" type="number" name="orden" min="1" value="<?= (int) ($editingItem['orden'] ?? 1) ?>">
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($isVideoFeatured): ?>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Título interno', 'item_titulo_video', $section['nombre_interno'], 'titulo'); ?>
                                        <input class="form-control" id="item_titulo_video" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? 'Video destacado') ?>" placeholder="Video destacado">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('URL de YouTube', 'item_url_video', $section['nombre_interno'], 'url'); ?>
                                        <?php $editingVideoUrl = (string) ($editingItem['url'] ?? ''); ?>
                                        <input class="form-control" id="item_url_video" name="url" value="<?= preg_match('/(youtube\.com|youtu\.be)/i', $editingVideoUrl) ? cms_e($editingVideoUrl) : '' ?>" placeholder="https://www.youtube.com/watch?v=...">
                                        <div class="field-note">Si cargas un video local y dejas esta URL vacía, se usará el archivo subido.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Video local', 'item_video_file', $section['nombre_interno'], 'video'); ?>
                                        <input class="form-control" id="item_video_file" type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime,video/x-m4v">
                                        <?php if (!empty($editingItem['url']) && !preg_match('/(youtube\.com|youtu\.be)/i', (string) $editingItem['url'])): ?>
                                            <div class="field-note">Actual: <code><?= cms_e($editingItem['url']) ?></code></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="field-card">
                                        <?php admin_modal_field_head('Visible', 'item_visible_video', $section['nombre_interno'], 'visible', false); ?>
                                        <select class="form-select" id="item_visible_video" name="visible"><option value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'selected' : '' ?>>Si</option><option value="no" <?= ($editingItem['visible'] ?? '') === 'no' ? 'selected' : '' ?>>No</option></select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="field-card">
                                        <?php admin_modal_field_head('Orden', 'item_orden_video', $section['nombre_interno'], 'orden', false); ?>
                                        <input class="form-control" id="item_orden_video" type="number" name="orden" min="1" value="<?= (int) ($editingItem['orden'] ?? count($items) + 1) ?>">
                                    </div>
                                </div>
                            </div>
                        <?php elseif (in_array($section['tipo_seccion'], ['carousel', 'hero'], true)): ?>
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
                        <?php elseif ($section['tipo_seccion'] === 'events'): ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Categoría', 'item_etiqueta_event', $section['nombre_interno'], 'etiqueta'); ?>
                                        <input class="form-control" id="item_etiqueta_event" name="etiqueta" value="<?= cms_e($editingItem['etiqueta'] ?? '') ?>" placeholder="Pastoral, Deportivo, Institucional...">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Título', 'item_titulo_event', $section['nombre_interno'], 'titulo'); ?>
                                        <input class="form-control" id="item_titulo_event" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Fecha del evento', 'item_fecha_event', $section['nombre_interno'], 'fecha-publicacion'); ?>
                                        <input class="form-control" id="item_fecha_event" type="date" name="fecha_publicacion" value="<?= cms_e($editingItem['fecha_publicacion'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Hora', 'item_subtitulo_event', $section['nombre_interno'], 'subtitulo'); ?>
                                        <input class="form-control" id="item_subtitulo_event" name="subtitulo" value="<?= cms_e($editingItem['subtitulo'] ?? '') ?>" placeholder="09:00 hrs.">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Ubicación', 'item_boton_2_texto_event', $section['nombre_interno'], 'boton-2-texto'); ?>
                                        <input class="form-control" id="item_boton_2_texto_event" name="boton_2_texto" value="<?= cms_e($editingItem['boton_2_texto'] ?? '') ?>" placeholder="Capilla del Colegio">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Descripción', 'item_descripcion_event', $section['nombre_interno'], 'descripcion'); ?>
                                        <textarea class="form-control" id="item_descripcion_event" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Botón texto', 'item_boton_1_texto_event', $section['nombre_interno'], 'boton-1-texto'); ?>
                                        <input class="form-control" id="item_boton_1_texto_event" name="boton_1_texto" value="<?= cms_e($editingItem['boton_1_texto'] ?? 'Ver detalle') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Botón URL', 'item_boton_1_url_event', $section['nombre_interno'], 'boton-1-url'); ?>
                                        <input class="form-control" id="item_boton_1_url_event" name="boton_1_url" value="<?= cms_e($editingItem['boton_1_url'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('URL alternativa', 'item_url_event', $section['nombre_interno'], 'url'); ?>
                                        <input class="form-control" id="item_url_event" name="url" value="<?= cms_e($editingItem['url'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($section['tipo_seccion'] === 'gallery'): ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Título / alt', 'item_titulo_gallery', $section['nombre_interno'], 'titulo'); ?>
                                        <input class="form-control" id="item_titulo_gallery" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('URL', 'item_url_gallery', $section['nombre_interno'], 'url'); ?>
                                        <input class="form-control" id="item_url_gallery" name="url" value="<?= cms_e($editingItem['url'] ?? '') ?>" placeholder="#0 o enlace de Instagram">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Descripción interna', 'item_descripcion_gallery', $section['nombre_interno'], 'descripcion'); ?>
                                        <textarea class="form-control" id="item_descripcion_gallery" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea>
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

                        <?php if (!$isVideoFeatured && !$isModal): ?>
                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Imagen', 'item_imagen', $section['nombre_interno'], 'imagen', true, 'clear_imagen'); ?>
                                        <input class="form-control" id="item_imagen" type="file" name="imagen" accept="image/*">
                                        <div class="field-note">Si bloqueas este campo, la imagen se guardará vacía.</div>
                                    </div>
                                </div>
                                <div class="<?= $isCarouselAdmin ? 'col-md-4' : 'col-md-3' ?>">
                                    <div class="field-card">
                                        <?php admin_modal_field_head('Visible', 'item_visible', $section['nombre_interno'], 'visible', false); ?>
                                        <label class="setting-toggle mb-0">
                                            <span class="setting-toggle-copy">Visible<small>Mostrar item</small></span>
                                            <span class="form-check form-switch mb-0 state-switch">
                                                <input class="form-check-input" id="item_visible" type="checkbox" name="visible" value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'checked' : '' ?>>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <input type="hidden" name="orden" value="<?= (int) ($editingItem['orden'] ?? count($items) + 1) ?>">
                            </div>
                        <?php endif; ?>
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
        <div class="col-auto d-flex align-items-end pb-1"><button type="button" class="btn-icon delete remove-config-row" title="Eliminar"><i class="bi bi-trash"></i></button></div>
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

            var generalTbody = document.getElementById('generalItemsTbody');
            if (generalTbody && typeof Sortable !== 'undefined') {
                var saveGenTimeout = null;
                Sortable.create(generalTbody, {
                    animation: 180,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onEnd: function () {
                        clearTimeout(saveGenTimeout);
                        saveGenTimeout = setTimeout(function () {
                            var ids = Array.from(generalTbody.querySelectorAll('tr'))
                                           .map(function (tr) { return tr.dataset.id; })
                                           .filter(Boolean);
                            ids.forEach(function (id, idx) {
                                var row = generalTbody.querySelector('tr[data-id="' + id + '"]');
                                if (row) {
                                    var cell = row.querySelector('.item-orden-cell');
                                    if (cell) { cell.textContent = idx + 1; }
                                }
                            });
                            var fd = new FormData();
                            fd.append('accion', 'reorder_items');
                            fd.append('id_seccion', '<?= (int) $idSeccion ?>');
                            ids.forEach(function (id) { fd.append('items[]', id); });
                            fetch(window.location.href, {
                                method: 'POST', body: fd,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                            .then(function (r) { return r.json(); })
                            .then(function (data) {
                                if (data.ok) {
                                    adminNotify({ title: 'Orden guardado', msg: 'El nuevo orden fue guardado.', type: 'info', autoClose: 1800 });
                                }
                            })
                            .catch(function () {});
                        }, 400);
                    }
                });
            }

            document.querySelectorAll('.js-confirm-submit').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === '1') {
                        return;
                    }
                    event.preventDefault();
                    adminConfirm({
                        title: form.dataset.confirmTitle || 'Confirmar guardado',
                        msg: form.dataset.confirmMsg || 'Se guardarán los cambios realizados.',
                        type: 'info',
                        btnText: 'Guardar',
                        onConfirm: function () {
                            form.dataset.confirmed = '1';
                            form.submit();
                        }
                    });
                });
            });

            var socialPresets = [
                { keys: ['instagram', 'insta'], name: 'Instagram', icon: 'assets/redes_sociales/instagram.jpg', url: 'https://instagram.com/' },
                { keys: ['facebook', 'fb'], name: 'Facebook', icon: 'assets/redes_sociales/facebook.jpg', url: 'https://facebook.com/' },
                { keys: ['youtube', 'youtu.be'], name: 'YouTube', icon: 'assets/redes_sociales/youtube.png', url: 'https://youtube.com/' },
                { keys: ['twitter', 'x.com'], name: 'Twitter', icon: 'assets/redes_sociales/twitter.png', url: 'https://x.com/' },
                { keys: ['linkedin', 'linkeding'], name: 'LinkedIn', icon: 'assets/redes_sociales/linkeding.png', url: 'https://linkedin.com/' }
            ];
            var socialNameInput = document.getElementById('topbar_titulo');
            var socialUrlInput = document.getElementById('topbar_descripcion');
            var socialIconInput = document.getElementById('topbar_icono');

            function applySocialPreset(preset, fillNameAndUrl) {
                if (!preset || !socialIconInput) {
                    return;
                }
                socialIconInput.value = preset.icon;
                if (fillNameAndUrl) {
                    if (socialNameInput && !socialNameInput.value.trim()) {
                        socialNameInput.value = preset.name;
                    }
                    if (socialUrlInput && !socialUrlInput.value.trim()) {
                        socialUrlInput.value = preset.url;
                    }
                }
                document.querySelectorAll('.social-icon-preset').forEach(function (button) {
                    button.classList.toggle('is-selected', button.dataset.socialIcon === preset.icon);
                });
            }

            function detectSocialPreset() {
                if (!socialIconInput || socialIconInput.value.trim()) {
                    return;
                }
                var source = ((socialNameInput ? socialNameInput.value : '') + ' ' + (socialUrlInput ? socialUrlInput.value : '')).toLowerCase();
                var preset = socialPresets.find(function (candidate) {
                    return candidate.keys.some(function (key) {
                        return source.indexOf(key) !== -1;
                    });
                });
                if (preset) {
                    applySocialPreset(preset, false);
                }
            }

            document.querySelectorAll('.social-icon-preset').forEach(function (button) {
                button.addEventListener('click', function () {
                    applySocialPreset({
                        name: button.dataset.socialName,
                        icon: button.dataset.socialIcon,
                        url: button.dataset.socialUrl
                    }, true);
                });
            });
            if (socialNameInput) {
                socialNameInput.addEventListener('input', detectSocialPreset);
            }
            if (socialUrlInput) {
                socialUrlInput.addEventListener('input', detectSocialPreset);
            }
            if (socialIconInput && socialIconInput.value.trim()) {
                document.querySelectorAll('.social-icon-preset').forEach(function (button) {
                    button.classList.toggle('is-selected', button.dataset.socialIcon === socialIconInput.value.trim());
                });
            }

            document.querySelectorAll('.js-config-boolean-toggle').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    var switchShell = toggle.closest('.state-switch');
                    var toggleShell = toggle.closest('.setting-toggle');
                    var hiddenValue = switchShell ? switchShell.querySelector('input[type="hidden"]') : null;
                    var copy = toggleShell ? toggleShell.querySelector('.setting-toggle-copy small') : null;
                    if (hiddenValue) {
                        hiddenValue.value = toggle.checked ? 'si' : 'no';
                    }
                    if (copy) {
                        copy.textContent = toggle.checked ? 'Activo' : 'Inactivo';
                    }
                });
            });

            document.querySelectorAll('.js-visible-toggle').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    var form = toggle.closest('form');
                    if (!form) {
                        return;
                    }
                    var hiddenVisible = form.querySelector('input[name="visible"]');
                    var previousChecked = !toggle.checked;
                    var nextVisible = toggle.checked ? 'si' : 'no';
                    if (hiddenVisible) {
                        hiddenVisible.value = nextVisible;
                    }
                    toggle.disabled = true;

                    fetch(window.location.href, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                if (!response.ok || !data.ok) {
                                    throw new Error(data.message || 'No se pudo actualizar la visibilidad.');
                                }
                                return data;
                            });
                        })
                        .then(function (data) {
                            toggle.checked = data.visible === 'si';
                            if (hiddenVisible) {
                                hiddenVisible.value = data.visible === 'si' ? 'no' : 'si';
                            }
                            var toggleLabel = form.querySelector('.table-check');
                            if (toggleLabel) {
                                toggleLabel.setAttribute('title', data.visible === 'si' ? 'Dejar oculta' : 'Dejar visible');
                            }
                        })
                        .catch(function (error) {
                            toggle.checked = previousChecked;
                            if (hiddenVisible) {
                                hiddenVisible.value = previousChecked ? 'no' : 'si';
                            }
                            adminConfirm({
                                title: 'No se pudo guardar',
                                msg: error.message,
                                type: 'danger',
                                btnText: 'OK',
                                onConfirm: function () {}
                            });
                        })
                        .finally(function () {
                            toggle.disabled = false;
                        });
                });
            });

            var _qs = new URLSearchParams(window.location.search);
            var _idParam = encodeURIComponent(_qs.get('id') || '');

            if (_qs.get('saved') === 'red_social') {
                window.history.replaceState({}, document.title, window.location.pathname + '?id=' + _idParam + '&tab=items');
                adminNotify({
                    title: 'Red social guardada',
                    msg: 'La red social fue guardada correctamente.',
                    type: 'info'
                });
            }

            if (_qs.get('saved') === 'item') {
                window.history.replaceState({}, document.title, window.location.pathname + '?id=' + _idParam + '&tab=items');
                adminNotify({
                    title: 'Item guardado',
                    msg: 'El item fue guardado correctamente.',
                    type: 'info'
                });
            }

            document.querySelectorAll('.js-preview-btn').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    var url = button.getAttribute('data-preview-url');
                    if (!url) {
                        return;
                    }
                    event.preventDefault();
                    var modalEl = document.getElementById('previewModal');
                    var titleEl = document.getElementById('previewModalLabel');
                    var frameEl = document.getElementById('previewFrame');
                    if (!modalEl || !titleEl || !frameEl) {
                        window.location.href = button.href;
                        return;
                    }
                    titleEl.textContent = 'Vista previa: ' + (button.getAttribute('data-preview-title') || 'Contenedor');
                    frameEl.src = url;
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                });
            });

            var previewModal = document.getElementById('previewModal');
            if (previewModal) {
                previewModal.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('previewFrame').src = 'about:blank';
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
            if (openModal === 'evento') {
                var eventModalEl = document.getElementById('eventModal');
                if (eventModalEl) {
                    var eventModal = new bootstrap.Modal(eventModalEl);
                    eventModal.show();
                }
            }

            var mediaPreview = document.getElementById('eventMediaUploadPreview');
            function renderMediaUploadPreview() {
                if (!mediaPreview) {
                    return;
                }
                var fields = document.querySelectorAll('input[name="event_media_images[]"], input[name="event_media_videos[]"]');
                mediaPreview.innerHTML = '';
                fields.forEach(function (field) {
                    Array.prototype.slice.call(field.files || []).forEach(function (file) {
                        var card = document.createElement('div');
                        card.className = 'event-media-card';
                        var thumb = document.createElement('div');
                        thumb.className = 'event-media-thumb';
                        if (file.type.indexOf('image/') === 0) {
                            var img = document.createElement('img');
                            img.src = URL.createObjectURL(file);
                            img.onload = function () { URL.revokeObjectURL(img.src); };
                            thumb.appendChild(img);
                        } else {
                            thumb.innerHTML = '<i class="bi bi-play-btn"></i>';
                        }
                        var body = document.createElement('div');
                        body.className = 'event-media-body';
                        var title = document.createElement('strong');
                        title.textContent = file.name;
                        var meta = document.createElement('small');
                        meta.textContent = 'Archivo seleccionado';
                        body.appendChild(title);
                        body.appendChild(meta);
                        card.appendChild(thumb);
                        card.appendChild(body);
                        mediaPreview.appendChild(card);
                    });
                });
                mediaPreview.classList.toggle('d-none', mediaPreview.children.length === 0);
            }
            document.querySelectorAll('input[name="event_media_images[]"], input[name="event_media_videos[]"]').forEach(function (field) {
                field.addEventListener('change', renderMediaUploadPreview);
            });

            document.querySelectorAll('.js-evento-visible-toggle').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    var form = toggle.closest('form');
                    if (!form) { return; }
                    toggle.disabled = true;
                    fetch(window.location.href, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function (r) { return r.json ? r.json() : {}; })
                    .catch(function () {})
                    .finally(function () { toggle.disabled = false; });
                });
            });

            document.querySelectorAll('.js-toggle-badge').forEach(function (badge) {
                badge.addEventListener('click', function () {
                    var idItem    = badge.dataset.itemId;
                    var visible   = badge.dataset.itemVisible;
                    var idSeccion = badge.dataset.idSeccion;
                    var next      = visible === 'si' ? 'no' : 'si';

                    badge.style.opacity = '0.5';
                    badge.style.pointerEvents = 'none';

                    var fd = new FormData();
                    fd.append('accion',     'toggle_item_visible');
                    fd.append('id_seccion', idSeccion);
                    fd.append('id_item',    idItem);
                    fd.append('visible',    next);

                    fetch(window.location.href, {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.ok) { throw new Error(data.message || 'Error'); }
                        badge.dataset.itemVisible = data.visible;
                        var isActive = data.visible === 'si';
                        badge.className = 'badge-soft ' + (isActive ? 'success' : 'warning') + ' js-toggle-badge';
                        badge.textContent = isActive ? 'Activo' : 'Oculto';
                    })
                    .catch(function () {
                        badge.dataset.itemVisible = visible;
                    })
                    .finally(function () {
                        badge.style.opacity = '';
                        badge.style.pointerEvents = '';
                    });
                });
            });

            document.querySelectorAll('.js-carousel-edit').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var item = {};
                    try { item = JSON.parse(btn.dataset.item || '{}'); } catch(e) {}

                    var modal = document.getElementById('itemModal');
                    if (!modal) { return; }

                    var f = modal.querySelector('form');
                    var set = function(sel, val) { var el = f ? f.querySelector(sel) : modal.querySelector(sel); if (el) { if (el.type === 'checkbox') { el.checked = val === 'si'; } else { el.value = val !== undefined ? val : ''; } } };

                    set('[name="id_item"]', item.id_item !== undefined ? String(item.id_item) : '0');
                    set('#item_etiqueta', item.etiqueta);
                    set('#item_titulo_linea_1', item.titulo_linea_1);
                    set('#item_titulo_linea_2', item.titulo_linea_2);
                    set('#item_titulo_linea_3', item.titulo_linea_3);
                    set('#item_descripcion', item.descripcion);
                    set('#item_boton_1_texto', item.boton_1_texto);
                    set('#item_boton_1_url', item.boton_1_url);
                    set('#item_boton_2_texto', item.boton_2_texto);
                    set('#item_boton_2_url', item.boton_2_url);
                    set('#item_visible', item.visible);
                    set('#item_orden', item.orden !== undefined ? String(item.orden) : '1');

                    var titleEl = modal.querySelector('.modal-title');
                    if (titleEl) { titleEl.textContent = 'Editar item'; }

                    bootstrap.Modal.getOrCreateInstance(modal).show();
                });
            });

            var excelUploadForm = document.getElementById('eventExcelUploadForm');
            var excelUploadOverlay = document.getElementById('eventExcelUploadOverlay');
            if (excelUploadForm && excelUploadOverlay) {
                excelUploadForm.addEventListener('submit', function (event) {
                    if (excelUploadForm.dataset.readyToSubmit === '1') {
                        return;
                    }
                    event.preventDefault();
                    excelUploadOverlay.classList.add('is-visible');
                    excelUploadOverlay.setAttribute('aria-hidden', 'false');
                    excelUploadForm.querySelectorAll('button').forEach(function (control) {
                        control.disabled = true;
                    });
                    window.setTimeout(function () {
                        excelUploadForm.dataset.readyToSubmit = '1';
                        excelUploadForm.submit();
                    }, 3000);
                });
            }
        });
    </script>
    <script src="assets/js/eventos_excel_help.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
    (function () {
        var galleryGrid = document.getElementById('gallerySortable');
        if (galleryGrid && typeof Sortable !== 'undefined') {
            var saveGalleryTimeout = null;
            Sortable.create(galleryGrid, {
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function () {
                    clearTimeout(saveGalleryTimeout);
                    saveGalleryTimeout = setTimeout(function () {
                        var ids = Array.from(galleryGrid.querySelectorAll('.carousel-admin-card'))
                                       .map(function (c) { return c.dataset.id; })
                                       .filter(Boolean);
                        var fd = new FormData();
                        fd.append('accion', 'reorder_items');
                        fd.append('id_seccion', '<?= (int) $idSeccion ?>');
                        ids.forEach(function (id) { fd.append('items[]', id); });
                        fetch(window.location.href, {
                            method: 'POST', body: fd,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.ok) {
                                adminNotify({ title: 'Orden guardado', msg: 'El orden de la galería fue actualizado.', type: 'info', autoClose: 1800 });
                            }
                        })
                        .catch(function () {});
                    }, 400);
                }
            });
        }
    })();

    (function () {
        var grid = document.getElementById('carouselSortable');
        if (!grid || typeof Sortable === 'undefined') { return; }

        var saveTimeout = null;

        Sortable.create(grid, {
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function () {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(function () {
                    var ids = Array.from(grid.querySelectorAll('.carousel-admin-card'))
                                   .map(function (c) { return c.dataset.id; })
                                   .filter(Boolean);

                    var fd = new FormData();
                    fd.append('accion', 'reorder_items');
                    fd.append('id_seccion', '<?= (int) $idSeccion ?>');
                    ids.forEach(function (id) { fd.append('items[]', id); });

                    fetch(window.location.href, {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.ok) {
                            adminNotify({ title: 'Orden guardado', msg: 'El nuevo orden de los slides fue guardado.', type: 'info', autoClose: 1800 });
                        }
                    })
                    .catch(function () {});
                }, 400);
            }
        });
    })();
    </script>
HTML
    ),
]);
?>
