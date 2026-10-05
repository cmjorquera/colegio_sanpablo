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

try {
    admin_requerir_permiso('contenedores', 'ver');
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
    cms_redirect('admin.php?panel=dashboard');
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

function header_save_general(mysqli $db, array $section, array $post): void
{
    $idSeccion = (int) $section['id_seccion'];
    $idInstitucion = (int) $section['id_institucion'];
    $textoBoton = trim((string) ($post['texto_boton_principal'] ?? ''));
    $urlBoton = trim((string) ($post['url_boton_principal'] ?? ''));
    $observacion = trim((string) ($post['observacion'] ?? ''));
    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;

    if ($textoBoton === '') {
        throw new RuntimeException('El texto del botón no puede quedar vacío.');
    }

    if ($urlBoton === '') {
        $urlBoton = '#';
    }

    $stmtInstitucion = $db->prepare('UPDATE institucion SET texto_boton_principal = ?, url_boton_principal = ? WHERE id_institucion = ?');
    $stmtInstitucion->bind_param('ssi', $textoBoton, $urlBoton, $idInstitucion);
    $stmtInstitucion->execute();
    $stmtInstitucion->close();

    $stmtSeccion = $db->prepare('UPDATE seccion SET observacion = ?, actualizado_en = NOW(), actualizado_por = ? WHERE id_seccion = ?');
    $stmtSeccion->bind_param('sii', $observacion, $idUsuario, $idSeccion);
    $stmtSeccion->execute();
    $stmtSeccion->close();
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

function admin_youtube_video_id(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    $patterns = [
        '/youtu\.be\/([A-Za-z0-9_-]{6,})/i',
        '/youtube\.com\/(?:embed|shorts|live)\/([A-Za-z0-9_-]{6,})/i',
        '/[?&]v=([A-Za-z0-9_-]{6,})/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $match)) {
            return $match[1];
        }
    }

    return '';
}

function admin_reorder_news_gallery(mysqli $db, int $idSeccion, int $idItem, array $ids): void
{
    if ($idSeccion <= 0 || $idItem <= 0) {
        throw new RuntimeException('No se pudo identificar la galería de la noticia.');
    }

    $stmtItem = $db->prepare('SELECT id_item FROM seccion_item WHERE id_item = ? AND id_seccion = ? LIMIT 1');
    if (!$stmtItem) {
        throw new RuntimeException('No se pudo validar la noticia.');
    }
    $stmtItem->bind_param('ii', $idItem, $idSeccion);
    $stmtItem->execute();
    $exists = $stmtItem->get_result();
    $hasItem = $exists ? (bool) $exists->fetch_assoc() : false;
    $stmtItem->close();
    if (!$hasItem) {
        throw new RuntimeException('La noticia no pertenece a este contenedor.');
    }

    $gallery = cms_get_news_gallery_config($db, $idSeccion, $idItem);
    if (!$gallery) {
        return;
    }

    $positions = [];
    foreach (array_values(array_filter(array_map('strval', $ids))) as $index => $id) {
        $positions[$id] = $index + 1;
    }

    foreach ($gallery as $index => &$item) {
        $id = (string) ($item['id'] ?? '');
        $item['orden'] = $positions[$id] ?? (count($positions) + $index + 1);
    }
    unset($item);

    usort($gallery, static fn(array $a, array $b): int => ((int) $a['orden'] <=> (int) $b['orden']) ?: strcmp((string) $a['id'], (string) $b['id']));
    foreach ($gallery as $index => &$item) {
        $item['orden'] = $index + 1;
    }
    unset($item);

    $key = cms_news_gallery_config_key($idItem);
    $json = json_encode($gallery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmtDelete = $db->prepare('DELETE FROM seccion_config WHERE id_seccion = ? AND clave = ?');
    if ($stmtDelete) {
        $stmtDelete->bind_param('is', $idSeccion, $key);
        $stmtDelete->execute();
        $stmtDelete->close();
    }
    if ($json) {
        $stmtInsert = $db->prepare('INSERT INTO seccion_config (id_seccion, clave, valor) VALUES (?, ?, ?)');
        if ($stmtInsert) {
            $stmtInsert->bind_param('iss', $idSeccion, $key, $json);
            $stmtInsert->execute();
            $stmtInsert->close();
        }
    }
}

function admin_render_news_item_editor(array $item, int $idSeccion, array $categories, array $gallery, bool $canManage, string $sectionInternalName, string $formId, int $orden, string $cancelUrl): void
{
    $idItem = (int) ($item['id_item'] ?? 0);
    $disabledAttr = $canManage ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje($idItem > 0 ? 'editar' : 'crear')) . '"';
    $readonlyAttr = $canManage ? '' : ' readonly';
    $localVideo = trim((string) ($item['url'] ?? ''));
    $videoTipo = trim((string) ($item['video_youtube'] ?? '')) !== ''
        ? 'youtube'
        : ($localVideo !== '' ? 'archivo' : 'youtube');
    ?>
    <form method="post" enctype="multipart/form-data" class="news-inline-edit-panel">
        <input type="hidden" name="accion" value="guardar_item">
        <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
        <input type="hidden" name="id_item" value="<?= $idItem ?>">
        <input type="hidden" name="return_inline_news" value="1">
        <input type="hidden" name="orden" value="<?= $orden ?>">
        <input type="hidden" name="visible" value="<?= cms_e($item['visible'] ?? 'si') ?>">

        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
            <div>
                <h4 class="mb-1"><?= $idItem > 0 ? 'Editar noticia' : 'Nueva noticia' ?></h4>
                <div class="text-muted small">La galería del detalle queda asociada a esta noticia.</div>
            </div>
            <a class="btn btn-soft" href="<?= cms_e($cancelUrl) ?>">Cerrar</a>
        </div>

        <div class="row g-2">
            <div class="col-md-4">
                <div class="field-card" data-field-shell>
                    <?php admin_modal_field_head('Categoría', 'news_categoria_' . $formId, $sectionInternalName, 'categoria'); ?>
                    <select class="form-select" id="news_categoria_<?= cms_e($formId) ?>" name="id_categoria"<?= $disabledAttr ?>>
                        <option value="">Seleccione</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id_categoria'] ?>" <?= ((int) ($item['id_categoria'] ?? 0) === (int) $category['id_categoria']) ? 'selected' : '' ?>><?= cms_e($category['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="field-card" data-field-shell>
                    <?php admin_modal_field_head('Título', 'news_titulo_' . $formId, $sectionInternalName, 'titulo'); ?>
                    <input class="form-control" id="news_titulo_<?= cms_e($formId) ?>" name="titulo" value="<?= cms_e($item['titulo'] ?? '') ?>"<?= $readonlyAttr ?>>
                </div>
            </div>
            <div class="col-md-4">
                <div class="field-card" data-field-shell>
                    <?php admin_modal_field_head('Etiqueta visual', 'news_etiqueta_' . $formId, $sectionInternalName, 'etiqueta'); ?>
                    <input class="form-control" id="news_etiqueta_<?= cms_e($formId) ?>" name="etiqueta" value="<?= cms_e($item['etiqueta'] ?? '') ?>"<?= $readonlyAttr ?>>
                </div>
            </div>
            <div class="col-md-8">
                <div class="field-card" data-field-shell>
                    <?php admin_modal_field_head('Descripción', 'news_descripcion_' . $formId, $sectionInternalName, 'descripcion'); ?>
                    <textarea class="form-control" id="news_descripcion_<?= cms_e($formId) ?>" name="descripcion" rows="3"<?= $readonlyAttr ?>><?= cms_e($item['descripcion'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="col-md-4">
                <div class="field-card" data-field-shell>
                    <?php admin_modal_field_head('Fecha publicación', 'news_fecha_' . $formId, $sectionInternalName, 'fecha-publicacion'); ?>
                    <input class="form-control" id="news_fecha_<?= cms_e($formId) ?>" type="date" name="fecha_publicacion" value="<?= cms_e($item['fecha_publicacion'] ?? '') ?>"<?= $readonlyAttr ?>>
                </div>
            </div>
            <div class="col-md-4">
                <div class="field-card" data-field-shell>
                    <?php admin_modal_field_head('Botón texto', 'news_boton_texto_' . $formId, $sectionInternalName, 'boton-1-texto'); ?>
                    <input class="form-control" id="news_boton_texto_<?= cms_e($formId) ?>" name="boton_1_texto" value="<?= cms_e($item['boton_1_texto'] ?? 'Leer más') ?>" placeholder="Leer más"<?= $readonlyAttr ?>>
                </div>
            </div>
            <div class="col-md-4">
                <div class="field-card" data-field-shell>
                    <?php admin_modal_field_head('Botón URL', 'news_boton_url_' . $formId, $sectionInternalName, 'boton-1-url'); ?>
                    <input class="form-control" id="news_boton_url_<?= cms_e($formId) ?>" name="boton_1_url" value="<?= cms_e($item['boton_1_url'] ?? '') ?>" placeholder="https://..."<?= $readonlyAttr ?>>
                </div>
            </div>
            <div class="col-12">
                <div class="field-card news-video-type-card" data-field-shell data-news-video-wrap>
                    <label class="form-label mb-2">Video relacionado (opcional)</label>
                    <div class="slide-type-options" role="radiogroup" aria-label="Tipo de video">
                        <label class="slide-type-option">
                            <input type="radio" name="video_tipo" value="youtube" <?= $videoTipo === 'youtube' ? 'checked' : '' ?><?= $disabledAttr ?>>
                            <span><i class="bi bi-youtube"></i> Enlace YouTube</span>
                        </label>
                        <label class="slide-type-option">
                            <input type="radio" name="video_tipo" value="archivo" <?= $videoTipo === 'archivo' ? 'checked' : '' ?><?= $disabledAttr ?>>
                            <span><i class="bi bi-file-earmark-play"></i> Archivo de video</span>
                        </label>
                    </div>
                    <div class="mt-2" data-news-video-resource="youtube">
                        <input class="form-control" id="news_video_youtube_<?= cms_e($formId) ?>" name="video_youtube" value="<?= cms_e($item['video_youtube'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=..."<?= $readonlyAttr ?>>
                    </div>
                    <div class="mt-2" data-news-video-resource="archivo">
                        <input class="form-control" id="news_video_file_<?= cms_e($formId) ?>" type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime,video/x-m4v"<?= $disabledAttr ?>>
                        <?php if ($localVideo !== ''): ?>
                            <div class="field-note">Video actual: <code><?= cms_e($localVideo) ?></code></div>
                        <?php endif; ?>
                    </div>
                    <div class="field-note">Elige un solo tipo de video a la vez. Si no cargas ninguno, el bloque de video no se muestra en el detalle público.</div>
                </div>
            </div>
            <div class="col-12">
                <div class="field-card" data-field-shell>
                    <?php admin_modal_field_head('Imagen principal', 'news_imagen_' . $formId, $sectionInternalName, 'imagen', true, 'clear_imagen'); ?>
                    <?php if (!empty($item['imagen'])): ?>
                        <div class="mb-2">
                            <img src="<?= cms_e($item['imagen']) ?>" alt="Imagen actual" class="news-inline-main-image">
                        </div>
                    <?php endif; ?>
                    <input class="form-control" id="news_imagen_<?= cms_e($formId) ?>" type="file" name="imagen" accept="image/*"<?= $disabledAttr ?>>
                    <div class="field-note">Si bloqueas este campo, la imagen principal se guardará vacía.</div>
                </div>
            </div>
            <div class="col-12">
                <section class="news-inline-gallery-block" data-news-gallery-wrap data-news-id="<?= $idItem ?>" data-section-id="<?= (int) $idSeccion ?>">
                    <div class="news-inline-gallery-head">
                        <div>
                            <h5><i class="bi bi-images me-2"></i>Galería del detalle</h5>
                            <p>Imágenes adicionales que se mostrarán como carrusel en la noticia pública. Se guardan al instante.</p>
                        </div>
                        <?php if ($idItem > 0): ?>
                            <label class="btn btn-soft mb-0">
                                <i class="bi bi-upload me-1"></i>Agregar imágenes
                                <input class="d-none js-news-gallery-add-input" type="file" name="news_gallery_images[]" accept="image/*" multiple<?= $canManage ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>>
                            </label>
                        <?php endif; ?>
                    </div>
                    <?php if ($idItem <= 0): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>Guarda primero la noticia para poder agregar imágenes a la galería.
                        </div>
                    <?php else: ?>
                        <div class="news-gallery-grid news-gallery-grid--wide js-news-gallery-sortable" data-news-id="<?= $idItem ?>" data-section-id="<?= (int) $idSeccion ?>"<?= $gallery ? '' : ' hidden' ?>>
                            <div class="news-gallery-drag-hint">
                                <i class="bi bi-grip-vertical"></i> Arrastra las imágenes para ordenar. El cambio se guarda automáticamente.
                            </div>
                            <?php foreach ($gallery as $galleryIndex => $galleryImage): ?>
                                <?php $galleryId = (string) ($galleryImage['id'] ?? ('img_' . $galleryIndex)); ?>
                                <?php $galleryVisible = (int) ($galleryImage['visible'] ?? 1) === 1; ?>
                                <article class="news-gallery-card news-gallery-card--wide" data-id="<?= cms_e($galleryId) ?>">
                                    <img src="<?= cms_e($galleryImage['archivo'] ?? '') ?>" alt="<?= cms_e($galleryImage['titulo'] ?? 'Imagen de noticia') ?>">
                                    <div class="dropdown news-gallery-menu">
                                        <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones de imagen">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <button type="button" class="dropdown-item js-news-gallery-edit"><i class="bi bi-pencil-square me-2"></i>Editar</button>
                                            <button type="button" class="dropdown-item text-danger js-news-gallery-delete"><i class="bi bi-trash me-2"></i>Eliminar</button>
                                        </div>
                                    </div>
                                    <span class="news-gallery-title"><?= cms_e($galleryImage['titulo'] ?: 'Imagen de noticia') ?></span>
                                    <span class="news-gallery-state<?= $galleryVisible ? '' : ' is-hidden' ?>"><?= $galleryVisible ? 'Activo' : 'Oculto' ?></span>
                                    <div class="news-gallery-card-body">
                                        <input type="hidden" name="news_gallery_order[<?= cms_e($galleryId) ?>]" value="<?= (int) ($galleryIndex + 1) ?>">
                                        <input class="form-control" name="news_gallery_titles[<?= cms_e($galleryId) ?>]" value="<?= cms_e($galleryImage['titulo'] ?? '') ?>" placeholder="Título / alt">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <label class="form-check d-flex align-items-center gap-2 mb-0">
                                                <input class="form-check-input m-0 js-news-gallery-visible" type="checkbox" name="news_gallery_visible[<?= cms_e($galleryId) ?>]" value="1" <?= $galleryVisible ? 'checked' : '' ?>>
                                                Mostrar
                                            </label>
                                            <label class="text-danger d-flex align-items-center gap-2 mb-0">
                                                <input class="form-check-input m-0 js-news-gallery-delete-check" type="checkbox" name="delete_news_gallery[]" value="<?= cms_e($galleryId) ?>">
                                                Eliminar
                                            </label>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <div class="news-inline-gallery-empty" data-news-gallery-empty<?= $gallery ? ' hidden' : '' ?>>
                            <i class="bi bi-images"></i>
                            <span>Esta noticia todavía no tiene imágenes extra.</span>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>

        <div class="news-inline-edit-actions">
            <a class="btn btn-soft" href="<?= cms_e($cancelUrl) ?>">Cancelar</a>
            <button type="submit" class="btn btn-admin-action"<?= $disabledAttr ?>>Guardar</button>
        </div>
    </form>
    <?php
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
        </div>
    </div>
    <?php
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['accion'] ?? '';
        $permissionAction = match (true) {
            in_array($action, ['guardar_topbar_general', 'guardar_header_general', 'toggle_topbar_item_visible', 'toggle_evento', 'cancelar_evento', 'toggle_item_visible', 'reorder_items', 'reorder_news_gallery', 'subir_galeria_noticia', 'guardar_seccion'], true) => 'editar',
            in_array($action, ['guardar_topbar_item', 'guardar_evento', 'guardar_item'], true) => ((int) ($_POST['id_item'] ?? $_POST['id_evento'] ?? 0) > 0 ? 'editar' : 'crear'),
            $action === 'eliminar_item' || str_starts_with((string) $action, 'eliminar_evento_media:') => 'eliminar',
            str_starts_with((string) $action, 'toggle_evento_media:') => 'editar',
            default => '',
        };
        if ($permissionAction !== '') {
            admin_requerir_permiso('contenedores', $permissionAction);
        }

        if (($section['nombre_interno'] ?? '') === 'topbar' && $action === 'guardar_topbar_general') {
            topbar_save_general($db, $section, $_POST);
            cms_set_flash('success', 'La configuración del topbar fue actualizada correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion);
        }

        if (($section['nombre_interno'] ?? '') === 'header_principal' && $action === 'guardar_header_general') {
            $datosAntes = obtenerRegistroAuditoria($db, 'institucion', 'id_institucion', (int) ($section['id_institucion'] ?? 0));
            header_save_general($db, $section, $_POST);
            $datosDespues = obtenerRegistroAuditoria($db, 'institucion', 'id_institucion', (int) ($section['id_institucion'] ?? 0));
            registrarAuditoria($db, 'Header principal', 'institucion', (int) ($section['id_institucion'] ?? 0), 'editar', 'Se modificó el botón principal del header', $datosAntes, $datosDespues);
            cms_set_flash('success', 'El botón del header fue actualizado correctamente.');
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

        if ($action === 'reorder_news_gallery') {
            admin_reorder_news_gallery($db, $idSeccion, (int) ($_POST['id_item'] ?? 0), (array) ($_POST['items'] ?? []));
            if (admin_is_ajax_request()) {
                admin_json_response(['ok' => true]);
            }
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }

        if ($action === 'subir_galeria_noticia') {
            $idItemGaleria = (int) ($_POST['id_item'] ?? 0);
            if (($section['tipo_seccion'] ?? '') !== 'news' || $idItemGaleria <= 0) {
                throw new RuntimeException('Primero guarda la noticia para poder agregar imágenes a la galería.');
            }
            cms_save_news_gallery_config($db, $idSeccion, $idItemGaleria, 'noticias', $_POST);
            $updatedGallery = cms_get_news_gallery_config($db, $idSeccion, $idItemGaleria);
            if (admin_is_ajax_request()) {
                admin_json_response(['ok' => true, 'gallery' => $updatedGallery]);
            }
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items&edit_item=' . $idItemGaleria . '#news-edit-' . $idItemGaleria);
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

            if (in_array(($section['tipo_seccion'] ?? ''), ['carousel', 'hero'], true)) {
                $slideTipo = (string) ($_POST['slide_tipo'] ?? '');
                $slideTipo = in_array($slideTipo, ['imagen', 'video'], true) ? $slideTipo : 'imagen';
                $youtubeId = admin_youtube_video_id((string) ($_POST['url'] ?? ''));
                $hasYoutube = $youtubeId !== '';
                $hasUploadedImage = isset($_FILES['imagen']) && (int) ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
                $itemActualSlide = $idItemAudit > 0 ? cms_get_item($db, $idItemAudit) : null;
                $clearRequested = (string) ($_POST['clear_imagen'] ?? '') === '1';
                $hasExistingImage = !$clearRequested && trim((string) ($itemActualSlide['imagen'] ?? '')) !== '';

                if ($hasUploadedImage && $hasYoutube) {
                    throw new RuntimeException('Selecciona imagen o video YouTube, no ambos.');
                }

                if ($slideTipo === 'video') {
                    if (!$hasYoutube) {
                        throw new RuntimeException('Ingresa una URL válida de YouTube para este slide.');
                    }
                    $_POST['clear_imagen'] = '1';
                } else {
                    $_POST['url'] = '';
                    if (!$hasUploadedImage && !$hasExistingImage) {
                        throw new RuntimeException('Selecciona una imagen para este slide.');
                    }
                }
            }

            $savedItemId = cms_save_item($db, $section, $_POST);
            $datosDespues = obtenerRegistroAuditoria($db, 'seccion_item', 'id_item', $savedItemId);
            registrarAuditoria($db, 'Items de contenedor', 'seccion_item', $savedItemId, $idItemAudit > 0 ? 'editar' : 'crear', $idItemAudit > 0 ? 'Se modificó un item de contenedor' : 'Se creó un item de contenedor', $datosAntes, $datosDespues);
            if (($section['tipo_seccion'] ?? '') === 'news' && (string) ($_POST['return_inline_news'] ?? '') === '1') {
                cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items&edit_item=' . $savedItemId . '&saved=item#news-edit-' . $savedItemId);
            }
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
$isHeader = ($section['nombre_interno'] ?? '') === 'header_principal';
$isEventsCalendar = ($section['nombre_interno'] ?? '') === 'calendario_eventos_home' || ($section['tipo_seccion'] ?? '') === 'events';
$isVideoFeatured = ($section['nombre_interno'] ?? '') === 'video_destacado_home' || ($section['tipo_seccion'] ?? '') === 'video';
$isModal = ($section['nombre_interno'] ?? '') === 'modal_informativo' || ($section['tipo_seccion'] ?? '') === 'modal';
$isSpotifyPodcast = ($section['nombre_interno'] ?? '') === 'spotify_podcast_home' || ($section['tipo_seccion'] ?? '') === 'podcast';
$isCarouselAdmin = in_array(($section['tipo_seccion'] ?? ''), ['carousel', 'hero'], true);
$isMainCarousel = ($section['nombre_interno'] ?? '') === 'hero_principal' && ($section['tipo_seccion'] ?? '') === 'carousel';
$isGalleryAdmin  = ($section['tipo_seccion'] ?? '') === 'gallery';
$isNewsAdmin = ($section['tipo_seccion'] ?? '') === 'news';
$editingNewsInlineId = $isNewsAdmin ? max(0, (int) ($_GET['edit_item'] ?? 0)) : 0;
$isCreatingNewsInline = $isNewsAdmin && $editingNewsInlineId === 0 && isset($_GET['new_item']);
$hasItemsTab = !$isHeader;
$hasDesignTab = !$isHeader;
if ($isHeader && !in_array($tab, ['general', 'opciones'], true)) {
    cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=general');
}
if ($isMainCarousel && !in_array($tab, ['general', 'items', 'diseno'], true)) {
    cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=general');
}
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
$containerPermissions = [
    'crear' => admin_tiene_permiso('contenedores', 'crear'),
    'editar' => admin_tiene_permiso('contenedores', 'editar'),
    'eliminar' => admin_tiene_permiso('contenedores', 'eliminar'),
];
$canEditContainer = (bool) $containerPermissions['editar'];
$canManageItemModal = $editingItem ? (bool) $containerPermissions['editar'] : (bool) $containerPermissions['crear'];
$canManageTopbarItemModal = $editingItem ? (bool) $containerPermissions['editar'] : (bool) $containerPermissions['crear'];
$containerDisabledAttr = $canEditContainer ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"';
$containerReadonlyAttr = $canEditContainer ? '' : ' readonly';
$itemModalDisabledAttr = $canManageItemModal ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje($editingItem ? 'editar' : 'crear')) . '"';
$itemModalReadonlyAttr = $canManageItemModal ? '' : ' readonly';
$headerDisabledAttr = $canEditContainer ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"';
$headerReadonlyAttr = $canEditContainer ? '' : ' readonly';
$topbarDisabledAttr = $canEditContainer ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"';
$topbarReadonlyAttr = $canEditContainer ? '' : ' readonly';
$topbarItemDisabledAttr = $canManageTopbarItemModal ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje($editingItem ? 'editar' : 'crear')) . '"';
$topbarItemReadonlyAttr = $canManageTopbarItemModal ? '' : ' readonly';
$carouselConfigKeys = ['alineacion_texto', 'mostrar_flechas', 'mostrar_indicadores', 'overlay'];
$carouselConfigs = [
    'alineacion_texto' => topbar_get_config_value($configs, 'alineacion_texto', 'izquierda'),
    'mostrar_flechas' => topbar_get_config_value($configs, 'mostrar_flechas', 'si'),
    'mostrar_indicadores' => topbar_get_config_value($configs, 'mostrar_indicadores', 'si'),
    'overlay' => topbar_get_config_value($configs, 'overlay', 'oscuro'),
];
$carouselExtraConfigs = $isMainCarousel
    ? array_values(array_filter($configs, static fn(array $config): bool => !in_array((string) ($config['clave'] ?? ''), $carouselConfigKeys, true)))
    : [];
$newsConfigKeys = ['titulo_bloque', 'texto_boton', 'url_boton', 'cantidad_items'];
$newsConfigs = [
    'titulo_bloque' => topbar_get_config_value($configs, 'titulo_bloque', 'Noticias'),
    'texto_boton' => topbar_get_config_value($configs, 'texto_boton', 'Ver todas'),
    'url_boton' => topbar_get_config_value($configs, 'url_boton', 'noticias.php'),
    'cantidad_items' => topbar_get_config_value($configs, 'cantidad_items', '3'),
];
$genericHiddenConfigs = array_values(array_filter($configs, static function (array $config) use ($isNewsAdmin, $newsConfigKeys, $isMainCarousel, $carouselConfigKeys): bool {
    $clave = (string) ($config['clave'] ?? '');
    if ($isNewsAdmin && in_array($clave, $newsConfigKeys, true)) {
        return false;
    }
    if ($isMainCarousel && in_array($clave, $carouselConfigKeys, true)) {
        return false;
    }
    return true;
}));
$containerPermissionsJson = json_encode($containerPermissions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$adminEventCalendarRows = [];
if ($isEventsCalendar) {
    foreach ($eventosCalendario as $evento) {
        $eventId = (int) ($evento['id_evento'] ?? ($evento['id'] ?? 0));
        if ($eventId <= 0) {
            continue;
        }
        $adminEventCalendarRows[] = [
            'id_evento' => $eventId,
            'titulo' => (string) ($evento['titulo'] ?? ''),
            'descripcion_corta' => (string) ($evento['descripcion_corta'] ?? ''),
            'descripcion' => (string) ($evento['descripcion'] ?? ''),
            'fecha_inicio' => (string) ($evento['fecha_inicio'] ?? ''),
            'fecha_termino' => (string) ($evento['fecha_termino'] ?? ''),
            'hora_inicio' => (string) ($evento['hora_inicio'] ?? ''),
            'hora_termino' => (string) ($evento['hora_termino'] ?? ''),
            'categoria' => (string) ($evento['categoria'] ?? ''),
            'ubicacion' => (string) ($evento['ubicacion'] ?? ''),
            'color' => (string) ($evento['color'] ?? ''),
            'destacado' => (int) ($evento['destacado'] ?? 0),
            'visible' => (int) ($evento['visible'] ?? 1),
            'estado' => (string) ($evento['estado'] ?? ''),
            'orden' => (int) ($evento['orden'] ?? 0),
            'detalle_url' => 'evento_detalle.php?id_evento=' . $eventId,
            'editar_url' => 'editar_contenedor.php?id=' . (int) $idSeccion . '&tab=items&modal=evento&evento=' . $eventId,
        ];
    }
}
$adminEventCalendarJson = json_encode($adminEventCalendarRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$adminEventCalendarJsonSafe = str_replace('</', '<\/', $adminEventCalendarJson ?: '[]');

// Feriados: solo lectura desde la tabla calendario, nunca desde eventos.
$holidaysCalendario = $isEventsCalendar ? cms_list_holidays($db) : [];
$canEditHolidays = $isEventsCalendar ? admin_tiene_permiso('calendario', 'editar') : false;
$holidayDeniedMessage = 'No tienes permiso para editar feriados.';

$adminHolidayCalendarRows = [];
if ($isEventsCalendar) {
    foreach ($holidaysCalendario as $holiday) {
        $idCalendario = (int) ($holiday['id_calendario'] ?? 0);
        if ($idCalendario <= 0) {
            continue;
        }
        $adminHolidayCalendarRows[] = [
            'id_calendario' => $idCalendario,
            'fecha' => (string) ($holiday['fecha'] ?? ''),
            'nombre_feriado' => (string) ($holiday['nombre_feriado'] ?? 'Feriado'),
            'nombre_dia_semana' => (string) ($holiday['nombre_dia_semana'] ?? ''),
            'tipo' => (string) ($holiday['tipo'] ?? 'feriado'),
            'color' => (string) ($holiday['color'] ?? ''),
            'detalle_url' => 'feriado_detalle.php?id_calendario=' . $idCalendario,
        ];
    }
}
$adminHolidayCalendarJson = json_encode($adminHolidayCalendarRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$adminHolidayCalendarJsonSafe = str_replace('</', '<\/', $adminHolidayCalendarJson ?: '[]');

// Vista Tabla unificada: combina eventos (tabla eventos) y feriados (tabla calendario)
// solo para presentacion. Nunca se escribe un feriado en eventos ni un evento en calendario.
$tablaRows = [];
if ($isEventsCalendar) {
    foreach ($eventosCalendario as $evento) {
        $eventId = (int) ($evento['id_evento'] ?? ($evento['id'] ?? 0));
        if ($eventId <= 0) {
            continue;
        }
        $tablaRows[] = [
            'tipo' => 'evento',
            'fecha' => (string) ($evento['fecha_inicio'] ?? ''),
            'data' => $evento,
        ];
    }
    foreach ($holidaysCalendario as $holiday) {
        $idCalendario = (int) ($holiday['id_calendario'] ?? 0);
        if ($idCalendario <= 0) {
            continue;
        }
        $tablaRows[] = [
            'tipo' => 'feriado',
            'fecha' => (string) ($holiday['fecha'] ?? ''),
            'data' => $holiday,
        ];
    }
    usort($tablaRows, static fn(array $a, array $b): int => $a['fecha'] <=> $b['fecha']);
}

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
    'extra_head' => '<link rel="stylesheet" href="assets/css/admin_contenedores.css">' . ($isEventsCalendar ? '<link rel="stylesheet" href="assets/css/admin_eventos.css">' : ''),
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
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= cms_e(cms_get_preview_target($section['nombre_interno'])) ?>" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-1"></i>Ver en sitio</a>
        </div>
    </div>
</div>

<div class="section-card admin-tabs-card">
    <ul class="nav admin-tabs">
        <li class="nav-item"><a class="nav-link <?= $tab === 'general' ? 'active' : '' ?>" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=general"><i class="bi bi-sliders me-1"></i>General</a></li>
        <?php if ($hasItemsTab): ?>
            <li class="nav-item"><a class="nav-link <?= $tab === 'items' ? 'active' : '' ?>" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items"><i class="bi bi-layers me-1"></i>Items</a></li>
        <?php endif; ?>
        <?php if ($hasDesignTab): ?>
            <li class="nav-item"><a class="nav-link <?= $tab === 'diseno' ? 'active' : '' ?>" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=diseno"><i class="bi bi-palette me-1"></i>Diseño</a></li>
        <?php endif; ?>
        <?php if ($isHeader): ?>
            <li class="nav-item"><a class="nav-link <?= $tab === 'opciones' ? 'active' : '' ?>" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=opciones"><i class="bi bi-sliders2 me-1"></i>Opciones</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link js-preview-btn" href="preview_contenedor.php?id=<?= (int) $idSeccion ?>" data-preview-title="<?= cms_e($section['titulo_admin'] ?? 'Contenedor') ?>" data-preview-url="preview_contenedor.php?id=<?= (int) $idSeccion ?>&embed=1"><i class="bi bi-eye me-1"></i>Vista previa</a></li>
    </ul>
</div>

<?php if ($tab === 'general'): ?>
    <div class="section-card">
        <div class="section-head">
            <div>
                <h3><?= $isHeader ? 'Botón principal del header' : 'Configuración del contenedor' ?></h3>
                <p><?= $isHeader ? 'Edita el botón visible al extremo derecho del header. Los menús y submenús se administran desde el panel Menús.' : ($isTopbar ? 'Datos de contacto, visibilidad y comportamiento del topbar.' : ($isMainCarousel ? 'Ajustes generales de visualización del carrusel principal.' : ($isNewsAdmin ? 'Define el título, botón y cantidad de noticias visibles en este bloque.' : 'Ajustes generales visibles para el equipo administrador.'))) ?></p>
            </div>
        </div>
        <?php if ($isHeader): ?>
            <div class="header-admin-notices">
                <div class="header-admin-notice is-info">
                    <div class="header-admin-notice-icon"><i class="bi bi-info-circle"></i></div>
                    <div class="header-admin-notice-copy">
                        <strong>Administración de menús</strong>
                        <span>Los menús y submenús del header no se editan desde este contenedor. Para gestionarlos, entra al módulo Menús.</span>
                    </div>
                    <a href="admin.php?panel=menus" class="btn btn-sm btn-outline-primary">Ir a Menús</a>
                </div>
                <div class="header-admin-notice is-warning">
                    <div class="header-admin-notice-icon"><i class="bi bi-info-circle"></i></div>
                    <div class="header-admin-notice-copy">
                        <strong>Logo institucional</strong>
                        <span>El logo mostrado en el header se administra desde Configuración institucional.</span>
                    </div>
                    <a href="admin.php?panel=configuracion" class="btn btn-sm btn-outline-secondary">Editar logo</a>
                </div>
            </div>
            <form method="post" class="js-confirm-submit" data-confirm-title="Guardar header" data-confirm-msg="Se actualizará el botón principal del header.">
                <input type="hidden" name="accion" value="guardar_header_general">
                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-12">
                        <label class="form-label">Observación</label>
                        <input class="form-control" type="text" name="observacion" value="<?= cms_e($section['observacion'] ?? '') ?>" placeholder="Describe qué hace este bloque para el equipo administrador"<?= $headerReadonlyAttr ?>>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Texto del botón</label>
                        <input class="form-control" name="texto_boton_principal" value="<?= cms_e($site['institution']['texto_boton_principal'] ?? 'Matrícula') ?>" placeholder="Matrícula" required<?= $headerReadonlyAttr ?>>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">URL del botón</label>
                        <input class="form-control" name="url_boton_principal" value="<?= cms_e($site['institution']['url_boton_principal'] ?? '#') ?>" placeholder="https://... o #"<?= $headerReadonlyAttr ?>>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-premium"<?= $headerDisabledAttr ?>><i class="bi bi-save me-1"></i>Guardar</button>
                </div>
            </form>
        <?php elseif ($isTopbar): ?>
            <form method="post" class="js-confirm-submit" data-confirm-title="Guardar cambios" data-confirm-msg="Se actualizarán los datos visibles del topbar superior.">
                <input type="hidden" name="accion" value="guardar_topbar_general">
                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                <input type="hidden" name="visible" value="<?= ($section['visible'] ?? '') === 'si' ? 'si' : 'no' ?>">
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label">Observación</label>
                        <input class="form-control" type="text" name="observacion" value="<?= cms_e($section['observacion'] ?? '') ?>"<?= $topbarReadonlyAttr ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dirección</label>
                        <input class="form-control" name="direccion" value="<?= cms_e($site['institution']['direccion'] ?? '') ?>"<?= $topbarReadonlyAttr ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input class="form-control" name="telefono" value="<?= cms_e($site['institution']['telefono'] ?? '') ?>"<?= $topbarReadonlyAttr ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Correo</label>
                        <input class="form-control" name="email" value="<?= cms_e($site['institution']['email'] ?? '') ?>"<?= $topbarReadonlyAttr ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Texto del botón "Ingresar"</label>
                        <input class="form-control" name="texto_boton_ingresar" value="<?= cms_e($topbarConfigs['texto_boton_ingresar']) ?>"<?= $topbarReadonlyAttr ?>>
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
                                    <input class="form-check-input" type="checkbox" name="mostrar_direccion" value="si" <?= $topbarConfigs['mostrar_direccion'] === 'si' ? 'checked' : '' ?><?= $topbarDisabledAttr ?>>
                                </span>
                            </label>
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Teléfono<small>Mostrar en el topbar</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input class="form-check-input" type="checkbox" name="mostrar_telefono" value="si" <?= $topbarConfigs['mostrar_telefono'] === 'si' ? 'checked' : '' ?><?= $topbarDisabledAttr ?>>
                                </span>
                            </label>
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Correo<small>Mostrar en el topbar</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input class="form-check-input" type="checkbox" name="mostrar_email" value="si" <?= $topbarConfigs['mostrar_email'] === 'si' ? 'checked' : '' ?><?= $topbarDisabledAttr ?>>
                                </span>
                            </label>
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Redes<small>Mostrar redes sociales</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input class="form-check-input" type="checkbox" name="mostrar_redes" value="si" <?= $topbarConfigs['mostrar_redes'] === 'si' ? 'checked' : '' ?><?= $topbarDisabledAttr ?>>
                                </span>
                            </label>
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Botón ingresar<small>Mostrar acceso al sistema</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input class="form-check-input" type="checkbox" name="mostrar_boton_ingresar" value="si" <?= $topbarConfigs['mostrar_boton_ingresar'] === 'si' ? 'checked' : '' ?><?= $topbarDisabledAttr ?>>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-premium"<?= $canEditContainer ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>><i class="bi bi-save me-1"></i>Guardar</button>
                </div>
            </form>
        <?php elseif ($isMainCarousel): ?>
            <form method="post" class="js-confirm-submit" data-confirm-title="Guardar carrusel" data-confirm-msg="Se actualizarán los ajustes del carrusel principal.">
                <input type="hidden" name="accion" value="guardar_seccion">
                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                <input type="hidden" name="visible" value="<?= ($section['visible'] ?? '') === 'si' ? 'si' : 'no' ?>">
                <input type="hidden" name="orden" value="<?= (int) ($section['orden'] ?? 1) ?>">
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label">Observación</label>
                        <input class="form-control" type="text" name="observacion" value="<?= cms_e($section['observacion'] ?? '') ?>"<?= $containerReadonlyAttr ?>>
                    </div>
                    <div class="col-md-4">
                        <input type="hidden" name="config_key[]" value="alineacion_texto">
                        <label class="form-label">Alineación del texto</label>
                        <select class="form-select" name="config_value[]"<?= $containerDisabledAttr ?>>
                            <option value="izquierda" <?= $carouselConfigs['alineacion_texto'] === 'izquierda' ? 'selected' : '' ?>>Izquierda</option>
                            <option value="centro" <?= $carouselConfigs['alineacion_texto'] === 'centro' ? 'selected' : '' ?>>Centro</option>
                            <option value="derecha" <?= $carouselConfigs['alineacion_texto'] === 'derecha' ? 'selected' : '' ?>>Derecha</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="hidden" name="config_key[]" value="overlay">
                        <label class="form-label">Overlay</label>
                        <select class="form-select" name="config_value[]"<?= $containerDisabledAttr ?>>
                            <option value="oscuro" <?= $carouselConfigs['overlay'] === 'oscuro' ? 'selected' : '' ?>>Oscuro</option>
                            <option value="claro" <?= $carouselConfigs['overlay'] === 'claro' ? 'selected' : '' ?>>Claro</option>
                            <option value="ninguno" <?= $carouselConfigs['overlay'] === 'ninguno' ? 'selected' : '' ?>>Sin overlay</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="carousel-config-switches">
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Mostrar flechas<small>Controles anterior y siguiente</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input type="hidden" name="config_key[]" value="mostrar_flechas">
                                    <input type="hidden" name="config_value[]" value="<?= $carouselConfigs['mostrar_flechas'] === 'si' ? 'si' : 'no' ?>" data-carousel-switch-value>
                                    <input class="form-check-input js-carousel-config-switch" type="checkbox" <?= $carouselConfigs['mostrar_flechas'] === 'si' ? 'checked' : '' ?><?= $containerDisabledAttr ?>>
                                </span>
                            </label>
                            <label class="setting-toggle">
                                <span class="setting-toggle-copy">Mostrar indicadores<small>Puntos de navegación</small></span>
                                <span class="form-check form-switch mb-0 state-switch">
                                    <input type="hidden" name="config_key[]" value="mostrar_indicadores">
                                    <input type="hidden" name="config_value[]" value="<?= $carouselConfigs['mostrar_indicadores'] === 'si' ? 'si' : 'no' ?>" data-carousel-switch-value>
                                    <input class="form-check-input js-carousel-config-switch" type="checkbox" <?= $carouselConfigs['mostrar_indicadores'] === 'si' ? 'checked' : '' ?><?= $containerDisabledAttr ?>>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <?php foreach ($carouselExtraConfigs as $config): ?>
                    <input type="hidden" name="config_key[]" value="<?= cms_e($config['clave'] ?? '') ?>">
                    <input type="hidden" name="config_value[]" value="<?= cms_e($config['valor'] ?? '') ?>">
                <?php endforeach; ?>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-premium"<?= $containerDisabledAttr ?>><i class="bi bi-save me-1"></i>Guardar</button>
                </div>
            </form>
        <?php else: ?>
            <form method="post" class="js-confirm-submit" data-confirm-title="Guardar cambios" data-confirm-msg="Se actualizará la configuración del contenedor.">
                <input type="hidden" name="accion" value="guardar_seccion">
                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                <input type="hidden" name="visible" value="<?= ($section['visible'] ?? '') === 'si' ? 'si' : 'no' ?>">
                <input type="hidden" name="orden" value="<?= (int) ($section['orden'] ?? 1) ?>">
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label">Observación</label>
                        <input class="form-control" type="text" name="observacion" value="<?= cms_e($section['observacion'] ?? '') ?>" placeholder="Nota interna para el equipo administrador"<?= $containerReadonlyAttr ?>>
                    </div>
                    <?php if ($isNewsAdmin): ?>
                        <div class="col-md-6">
                            <input type="hidden" name="config_key[]" value="titulo_bloque">
                            <label class="form-label">Título del bloque</label>
                            <input class="form-control" name="config_value[]" value="<?= cms_e($newsConfigs['titulo_bloque']) ?>" placeholder="Noticias"<?= $containerReadonlyAttr ?>>
                        </div>
                        <div class="col-md-6">
                            <input type="hidden" name="config_key[]" value="texto_boton">
                            <label class="form-label">Texto del botón</label>
                            <input class="form-control" name="config_value[]" value="<?= cms_e($newsConfigs['texto_boton']) ?>" placeholder="Ver todas"<?= $containerReadonlyAttr ?>>
                        </div>
                        <!-- url_boton y cantidad_items quedan fijos (noticias.php / 4) y no son editables desde la interfaz. -->
                        <input type="hidden" name="config_key[]" value="url_boton">
                        <input type="hidden" name="config_value[]" value="noticias.php">
                        <input type="hidden" name="config_key[]" value="cantidad_items">
                        <input type="hidden" name="config_value[]" value="4">
                    <?php endif; ?>
                </div>
                <?php foreach ($genericHiddenConfigs as $config): ?>
                    <input type="hidden" name="config_key[]" value="<?= cms_e($config['clave'] ?? '') ?>">
                    <input type="hidden" name="config_value[]" value="<?= cms_e($config['valor'] ?? '') ?>">
                <?php endforeach; ?>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-premium"<?= $containerDisabledAttr ?>><i class="bi bi-save me-1"></i>Guardar</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
<?php elseif ($isMainCarousel && $tab === 'diseno'): ?>
    <div class="section-card">
        <div class="section-head">
            <div>
                <h3>Opciones de diseño del carrusel</h3>
                <p>Actualmente este carrusel utiliza el diseño base del sistema. En una próxima etapa se podrán seleccionar variantes visuales, efectos de transición y estructuras alternativas.</p>
            </div>
        </div>
        <div class="topbar-design-grid">
            <?php
            $carouselDesignOptions = [
                ['label' => 'Diseño actual', 'icon' => 'bi-check-circle', 'active' => true],
                ['label' => 'Carrusel con texto a la izquierda', 'icon' => 'bi-layout-sidebar'],
                ['label' => 'Carrusel con texto centrado', 'icon' => 'bi-text-center'],
                ['label' => 'Carrusel institucional', 'icon' => 'bi-building'],
                ['label' => 'Carrusel con video', 'icon' => 'bi-play-btn'],
                ['label' => 'Carrusel minimalista', 'icon' => 'bi-dash-lg'],
            ];
            ?>
            <?php foreach ($carouselDesignOptions as $option): ?>
                <div class="topbar-design-option <?= !empty($option['active']) ? 'is-active' : 'is-disabled' ?>">
                    <div class="topbar-design-icon"><i class="bi <?= cms_e($option['icon']) ?>"></i></div>
                    <div>
                        <strong><?= cms_e($option['label']) ?></strong>
                        <span><?= !empty($option['active']) ? 'Activo' : 'Próximamente' ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php elseif ($isHeader && $tab === 'opciones'): ?>
    <div class="section-card">
        <div class="section-head">
            <div>
                <h3>Opciones del header</h3>
                <p>Actualmente el header utiliza el diseño base del sistema. En una próxima etapa se podrán administrar variantes visuales, comportamiento responsive y opciones avanzadas del menú.</p>
            </div>
        </div>
        <div class="topbar-design-grid">
            <?php
            $headerOptions = [
                ['label' => 'Diseño actual', 'icon' => 'bi-check-circle', 'active' => true],
                ['label' => 'Header con logo centrado', 'icon' => 'bi-image'],
                ['label' => 'Header minimalista', 'icon' => 'bi-dash-lg'],
                ['label' => 'Header institucional', 'icon' => 'bi-building'],
                ['label' => 'Header con menú superior', 'icon' => 'bi-menu-button-wide'],
                ['label' => 'Header fijo al hacer scroll', 'icon' => 'bi-pin-angle'],
            ];
            ?>
            <?php foreach ($headerOptions as $option): ?>
                <div class="topbar-design-option <?= !empty($option['active']) ? 'is-active' : 'is-disabled' ?>">
                    <div class="topbar-design-icon"><i class="bi <?= cms_e($option['icon']) ?>"></i></div>
                    <div>
                        <strong><?= cms_e($option['label']) ?></strong>
                        <span><?= !empty($option['active']) ? 'Activo' : 'Próximamente' ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php elseif ($isTopbar && $tab === 'diseno'): ?>
    <div class="section-card">
        <div class="section-head">
            <div>
                <h3>Opciones de diseño del topbar</h3>
                <p>Actualmente este contenedor utiliza el diseño base del sistema. En una próxima etapa se podrán seleccionar variantes visuales del topbar sin modificar código.</p>
            </div>
        </div>
        <div class="topbar-design-grid">
            <?php
            $designOptions = [
                ['label' => 'Diseño actual', 'icon' => 'bi-check-circle', 'active' => true],
                ['label' => 'Topbar institucional', 'icon' => 'bi-building'],
                ['label' => 'Topbar con logo', 'icon' => 'bi-image'],
                ['label' => 'Topbar minimalista', 'icon' => 'bi-dash-lg'],
                ['label' => 'Topbar con redes destacadas', 'icon' => 'bi-share'],
            ];
            ?>
            <?php foreach ($designOptions as $option): ?>
                <div class="topbar-design-option <?= !empty($option['active']) ? 'is-active' : 'is-disabled' ?>">
                    <div class="topbar-design-icon"><i class="bi <?= cms_e($option['icon']) ?>"></i></div>
                    <div>
                        <strong><?= cms_e($option['label']) ?></strong>
                        <span><?= !empty($option['active']) ? 'Activo' : 'Próximamente' ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php elseif ($hasDesignTab && $tab === 'diseno'): ?>
    <?php
    $genericDesignMap = [
        'news' => [
            'title' => 'Opciones de diseño de noticias',
            'text' => 'Actualmente este bloque utiliza el diseño base del sistema. En una próxima etapa se podrán seleccionar variantes visuales para mostrar noticias.',
            'options' => ['Diseño actual', 'Noticias en grilla', 'Noticia destacada + listado', 'Noticias tipo carrusel', 'Noticias minimalistas'],
            'icons' => ['bi-check-circle', 'bi-grid-3x3-gap', 'bi-layout-text-sidebar-reverse', 'bi-collection-play', 'bi-dash-lg'],
        ],
        'gallery' => [
            'title' => 'Opciones de diseño de galería',
            'text' => 'Actualmente este bloque utiliza el diseño base del sistema. En una próxima etapa se podrán seleccionar variantes visuales para mostrar imágenes.',
            'options' => ['Diseño actual', 'Galería en grilla', 'Galería tipo mosaico', 'Galería tipo carrusel', 'Galería minimalista'],
            'icons' => ['bi-check-circle', 'bi-grid-3x3-gap', 'bi-columns-gap', 'bi-collection', 'bi-dash-lg'],
        ],
        'faq' => [
            'title' => 'Opciones de diseño de preguntas frecuentes',
            'text' => 'Actualmente este bloque utiliza el diseño base del sistema. En una próxima etapa se podrán seleccionar variantes visuales para preguntas frecuentes.',
            'options' => ['Diseño actual', 'Acordeón clásico', 'Preguntas en dos columnas', 'FAQ institucional', 'FAQ minimalista'],
            'icons' => ['bi-check-circle', 'bi-list-ul', 'bi-layout-split', 'bi-building', 'bi-dash-lg'],
        ],
        'podcast' => [
            'title' => 'Opciones de diseño de podcast',
            'text' => 'Actualmente este bloque utiliza el diseño base del sistema. En una próxima etapa se podrán seleccionar variantes visuales para canal y episodios.',
            'options' => ['Diseño actual', 'Podcast destacado', 'Lista de episodios', 'Podcast tipo carrusel', 'Podcast minimalista'],
            'icons' => ['bi-check-circle', 'bi-spotify', 'bi-list-stars', 'bi-collection-play', 'bi-dash-lg'],
        ],
        'video' => [
            'title' => 'Opciones de diseño de video',
            'text' => 'Actualmente este bloque utiliza el diseño base del sistema. En una próxima etapa se podrán seleccionar variantes visuales para videos destacados.',
            'options' => ['Diseño actual', 'Video destacado ancho', 'Video con texto lateral', 'Video institucional', 'Video minimalista'],
            'icons' => ['bi-check-circle', 'bi-play-btn', 'bi-layout-sidebar-reverse', 'bi-building', 'bi-dash-lg'],
        ],
    ];
    $designInfo = $genericDesignMap[$section['tipo_seccion'] ?? ''] ?? [
        'title' => 'Opciones de diseño del bloque',
        'text' => 'Actualmente este bloque utiliza el diseño base del sistema. En una próxima etapa se podrán seleccionar variantes visuales sin modificar código.',
        'options' => ['Diseño actual', 'Variante institucional', 'Vista compacta', 'Vista destacada', 'Vista minimalista'],
        'icons' => ['bi-check-circle', 'bi-building', 'bi-arrows-collapse', 'bi-stars', 'bi-dash-lg'],
    ];
    ?>
    <div class="section-card">
        <div class="section-head">
            <div>
                <h3><?= cms_e($designInfo['title']) ?></h3>
                <p><?= cms_e($designInfo['text']) ?></p>
            </div>
        </div>
        <div class="topbar-design-grid">
            <?php foreach ($designInfo['options'] as $index => $label): ?>
                <div class="topbar-design-option <?= $index === 0 ? 'is-active' : 'is-disabled' ?>">
                    <div class="topbar-design-icon"><i class="bi <?= cms_e($designInfo['icons'][$index] ?? 'bi-palette') ?>"></i></div>
                    <div>
                        <strong><?= cms_e($label) ?></strong>
                        <span><?= $index === 0 ? 'Activo' : 'Próximamente' ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="section-card">
        <div class="section-head">
            <div>
                <h3><?= $isTopbar ? 'Redes sociales' : ($isEventsCalendar ? 'Eventos del calendario' : ($isSpotifyPodcast ? 'Canal y episodios de Spotify' : 'Items del contenedor')) ?></h3>
                <p><?= $isTopbar ? 'Administra las redes que se muestran dentro del topbar.' : ($isEventsCalendar ? 'Carga individual y masiva de eventos reales desde la tabla eventos.' : ($isSpotifyPodcast ? 'Administra el canal principal, episodios destacados, portada, fechas, duración y URLs de Spotify.' : 'Administración específica del bloque.')) ?></p>
            </div>
            <?php if (!$isEventsCalendar && !$isVideoFeatured && !$isNewsAdmin): ?>
                <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item" class="btn btn-premium"<?= $containerPermissions['crear'] ? '' : ' data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('crear')) . '"' ?>><i class="bi bi-plus-circle me-1"></i><?= $isTopbar ? 'Agregar red social' : 'Agregar item' ?></a>
            <?php elseif ($isNewsAdmin): ?>
                <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items<?= $isCreatingNewsInline ? '' : '&new_item=1#news-new-item' ?>" class="btn btn-premium <?= $isCreatingNewsInline ? 'active' : '' ?>"<?= $containerPermissions['crear'] ? '' : ' data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('crear')) . '"' ?>><i class="bi bi-plus-circle me-1"></i><?= $isCreatingNewsInline ? 'Cerrar formulario' : 'Agregar noticia' ?></a>
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

            <div class="admin-events-view" data-admin-events-module data-can-edit="<?= $containerPermissions['editar'] ? '1' : '0' ?>" data-denied-message="<?= cms_e(admin_permiso_denegado_mensaje('editar')) ?>">
                <div class="admin-events-viewbar">
                    <div class="admin-events-viewbar__copy">
                        <strong>Vista administrativa de eventos</strong>
                        <span>La tabla mantiene la gestión masiva; el calendario permite revisar el mes rápidamente.</span>
                    </div>
                    <div class="admin-events-segment" role="group" aria-label="Cambiar vista de eventos">
                        <button type="button" class="admin-events-segment__btn" data-event-view-button="tabla">
                            <i class="bi bi-table"></i>Tabla
                        </button>
                        <button type="button" class="admin-events-segment__btn" data-event-view-button="calendario">
                            <i class="bi bi-calendar3"></i>Calendario
                        </button>
                    </div>
                </div>

                <div data-event-view-panel="tabla">
                    <div class="table-responsive admin-events-table-wrap">
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
                                    <th>Tipo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tablaRows as $row): ?>
                                    <?php if ($row['tipo'] === 'feriado'): ?>
                                        <?php
                                        $holiday = $row['data'];
                                        $idCalendario = (int) ($holiday['id_calendario'] ?? 0);
                                        $holidayVisible = (int) ($holiday['visible'] ?? 1) === 1;
                                        $editHolidayUrl = 'admin_feriado_editar.php?id_calendario=' . $idCalendario . '&id_seccion=' . (int) $idSeccion;
                                        ?>
                                        <tr class="is-feriado-row">
                                            <td><?= cms_e($holiday['fecha'] ?? '') ?></td>
                                            <td>Todo el día</td>
                                            <td><?= cms_e($holiday['nombre_feriado'] ?? 'Feriado') ?></td>
                                            <td><?= cms_e(ucfirst((string) ($holiday['tipo'] ?? 'feriado'))) ?></td>
                                            <td>Nacional / Uruguay</td>
                                            <td><span class="badge-soft is-feriado-badge">Feriado</span></td>
                                            <td><span class="badge-soft <?= $holidayVisible ? 'success' : 'warning' ?>"><?= $holidayVisible ? 'Sí' : 'No' ?></span></td>
                                            <td><span class="badge-soft is-feriado-badge">Feriado</span></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="<?= cms_e($editHolidayUrl) ?>" class="btn-icon edit" title="Editar feriado" aria-label="Editar feriado" <?= $canEditHolidays ? '' : 'data-admin-denied="' . cms_e($holidayDeniedMessage) . '"' ?>>
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <a href="feriado_detalle.php?id_calendario=<?= $idCalendario ?>" target="_blank" class="btn-icon preview" title="Ver feriado" aria-label="Ver feriado">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php
                                        $evento = $row['data'];
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
                                            <td><span class="badge-soft">Evento</span></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=evento&evento=<?= $eventId ?>" class="btn-icon edit" title="Editar" aria-label="Editar" data-event-edit-link="<?= $eventId ?>">
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
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-events-calendar-shell" data-event-view-panel="calendario" hidden>
                    <script type="application/json" data-admin-events-json><?= $adminEventCalendarJsonSafe ?></script>
                    <script type="application/json" data-admin-holidays-json><?= $adminHolidayCalendarJsonSafe ?></script>
                    <div class="admin-events-calendar-card">
                        <div class="admin-events-calendar-head">
                            <h4 data-calendar-title>Calendario</h4>
                            <div class="admin-events-calendar-nav">
                                <button type="button" class="btn-icon" data-calendar-prev title="Mes anterior" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></button>
                                <button type="button" class="btn-icon" data-calendar-next title="Mes siguiente" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="admin-events-weekdays" aria-hidden="true">
                            <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                        </div>
                        <div class="admin-events-month-grid" data-calendar-grid></div>
                    </div>
                    <aside class="admin-events-list-card">
                        <div class="admin-events-list-head">
                            <h4>Eventos y feriados</h4>
                            <span data-calendar-count>0 eventos</span>
                        </div>
                        <div class="admin-events-list" data-calendar-list></div>
                    </aside>
                </div>
            </div>
        <?php elseif ($isTopbar): ?>
            <div class="table-responsive">
                <table class="table table-modern topbar-items-table align-middle" id="itemsTable">
                    <colgroup>
                        <col style="width:44px;">
                        <col style="width:22%;">
                        <col>
                        <col style="width:110px;">
                        <col style="width:110px;">
                        <col style="width:132px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Red</th>
                            <th>URL</th>
                            <th>Icono</th>
                            <th>Visible</th>
                            <th class="topbar-actions-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="topbarItemsTbody" data-section-id="<?= (int) $idSeccion ?>">
                        <?php foreach ($topbarItems as $item): ?>
                            <tr data-id="<?= (int) $item['id_item'] ?>">
                                <td class="topbar-drag-cell">
                                    <span class="topbar-drag-handle" title="Arrastrar para ordenar" aria-label="Arrastrar para ordenar">
                                        <i class="bi bi-grip-vertical"></i>
                                    </span>
                                </td>
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
                                                <input class="form-check-input js-visible-toggle" type="checkbox" <?= ($item['visible'] ?? '') === 'si' ? 'checked' : '' ?><?= $topbarDisabledAttr ?>>
                                                    </span>
                                        </label>
                                    </form>
                                </td>
                                <td class="topbar-actions-cell">
                                    <div class="table-actions">
                                        <a href="<?= cms_e($item['descripcion'] ?? '#') ?>" target="_blank" rel="noopener" class="btn-icon preview" title="Ver red social" aria-label="Ver red social">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn-icon edit" title="Editar" aria-label="Editar"<?= $canEditContainer ? '' : ' data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>>
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="post" class="m-0" onsubmit="return confirm('¿Eliminar esta red social?');">
                                            <input type="hidden" name="accion" value="eliminar_item">
                                            <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                            <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                            <button type="submit" class="btn-icon delete" title="Eliminar" aria-label="Eliminar"<?= $containerPermissions['eliminar'] ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('eliminar')) . '"' ?>>
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
            <div class="carousel-viewbar" data-carousel-view-module>
                <div class="carousel-drag-hint">
                    <i class="bi bi-grip-vertical"></i> Puedes ordenar las diapositivas arrastrándolas. El cambio se guarda automáticamente.
                </div>
                <div class="carousel-view-segment" role="tablist" aria-label="Vista de diapositivas">
                    <button type="button" class="carousel-view-segment__btn" data-carousel-view-button="listado">
                        <i class="bi bi-list-ul"></i> Listado
                    </button>
                    <button type="button" class="carousel-view-segment__btn is-active" data-carousel-view-button="tarjetas">
                        <i class="bi bi-grid-3x3-gap"></i> Tarjetas
                    </button>
                </div>
            </div>
            <div data-carousel-view-panel="listado" hidden>
                <div class="table-responsive">
                    <table class="table table-modern carousel-items-table align-middle">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Imagen</th>
                                <th>Título</th>
                                <th>Subtítulo / Etiqueta</th>
                                <th>Botón 1</th>
                                <th>Botón 2</th>
                                <th>Visible</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="carouselListSortable" data-section-id="<?= (int) $idSeccion ?>" data-can-edit="<?= $canEditContainer ? '1' : '0' ?>">
                            <?php foreach ($items as $item): ?>
                                <?php
                                $slideTitle = trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? ''));
                                $slideYoutubeId = admin_youtube_video_id((string) ($item['url'] ?? ''));
                                $slideHasYoutube = $slideYoutubeId !== '';
                                $carouselItemData = htmlspecialchars(json_encode([
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
                                    'imagen'         => $item['imagen'] ?? '',
                                    'url'            => $item['url'] ?? '',
                                    'visible'        => $item['visible'] ?? 'si',
                                    'orden'          => (int) ($item['orden'] ?? 1),
                                ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr data-id="<?= (int) $item['id_item'] ?>">
                                    <td class="carousel-order-cell"><span class="drag-handle"><i class="bi bi-grip-vertical"></i></span><span class="item-orden-cell"><?= (int) ($item['orden'] ?? 0) ?></span></td>
                                    <td>
                                        <div class="carousel-list-thumb">
                                            <?php if ($slideHasYoutube): ?>
                                                <img src="https://img.youtube.com/vi/<?= cms_e($slideYoutubeId) ?>/hqdefault.jpg" alt="<?= cms_e($slideTitle ?: 'Video YouTube del carrusel') ?>">
                                                <i class="bi bi-play-fill"></i>
                                            <?php elseif (!empty($item['imagen'])): ?>
                                                <img src="<?= cms_e($item['imagen']) ?>" alt="<?= cms_e($slideTitle ?: 'Slide del carrusel') ?>">
                                            <?php else: ?>
                                                <span><i class="bi bi-image"></i></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><strong><?= cms_e($slideTitle ?: 'Slide sin título') ?></strong></td>
                                    <td><?= cms_e($item['etiqueta'] ?? '') ?></td>
                                    <td><?= cms_e($item['boton_1_texto'] ?? '') ?></td>
                                    <td><?= cms_e($item['boton_2_texto'] ?? '') ?></td>
                                    <td>
                                        <button type="button"
                                                class="badge-soft <?= ($item['visible'] ?? '') === 'si' ? 'success' : 'warning' ?> js-toggle-badge"
                                                style="border:none;cursor:pointer;"
                                                data-item-id="<?= (int) $item['id_item'] ?>"
                                                data-item-visible="<?= cms_e($item['visible'] ?? 'no') ?>"
                                                data-id-seccion="<?= (int) $idSeccion ?>"
                                                title="Cambiar visibilidad"<?= $containerDisabledAttr ?>>
                                            <?= ($item['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button type="button" class="btn-icon edit js-carousel-edit" title="Editar" aria-label="Editar" data-item="<?= $carouselItemData ?>"<?= $canEditContainer ? '' : ' data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form method="post" class="m-0" onsubmit="return confirm('¿Eliminar este item?');">
                                                <input type="hidden" name="accion" value="eliminar_item">
                                                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                                <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                                <button type="submit" class="btn-icon delete" title="Eliminar" aria-label="Eliminar"<?= $containerPermissions['eliminar'] ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('eliminar')) . '"' ?>>
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
            </div>
            <div data-carousel-view-panel="tarjetas">
                <div class="carousel-card-grid mb-4" id="carouselSortable" data-section-id="<?= (int) $idSeccion ?>" data-can-edit="<?= $canEditContainer ? '1' : '0' ?>">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $slideTitle = trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? ''));
                        $slideYoutubeId = admin_youtube_video_id((string) ($item['url'] ?? ''));
                        $slideHasYoutube = $slideYoutubeId !== '';
                        $carouselItemData = htmlspecialchars(json_encode([
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
                            'imagen'         => $item['imagen'] ?? '',
                            'url'            => $item['url'] ?? '',
                            'visible'        => $item['visible'] ?? 'si',
                            'orden'          => (int) ($item['orden'] ?? 1),
                        ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                        ?>
                        <article class="carousel-admin-card" data-id="<?= (int) $item['id_item'] ?>">
                            <div class="carousel-admin-media">
                                <?php if ($slideHasYoutube): ?>
                                    <img
                                        class="carousel-admin-youtube-thumb"
                                        src="https://img.youtube.com/vi/<?= cms_e($slideYoutubeId) ?>/maxresdefault.jpg"
                                        alt="<?= cms_e($slideTitle ?: 'Video YouTube del carrusel') ?>"
                                        onerror="this.onerror=null;this.src='https://img.youtube.com/vi/<?= cms_e($slideYoutubeId) ?>/hqdefault.jpg';"
                                    >
                                    <div class="carousel-admin-video-badge" aria-hidden="true">
                                        <i class="bi bi-play-fill"></i>
                                    </div>
                                <?php elseif (!empty($item['imagen'])): ?>
                                    <img src="<?= cms_e($item['imagen']) ?>" alt="<?= cms_e($slideTitle ?: 'Slide del carrusel') ?>">
                                <?php else: ?>
                                    <div class="carousel-admin-placeholder"><i class="bi bi-image"></i></div>
                                <?php endif; ?>
                                <div class="dropdown carousel-card-menu">
                                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <button type="button" class="dropdown-item js-carousel-edit" data-item="<?= $carouselItemData ?>"<?= $canEditContainer ? '' : ' data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>>
                                            <i class="bi bi-pencil-square me-2"></i>Editar
                                        </button>
                                        <form method="post" onsubmit="return confirm('¿Eliminar este item?');">
                                            <input type="hidden" name="accion" value="eliminar_item">
                                            <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                            <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                            <button type="submit" class="dropdown-item text-danger"<?= $containerPermissions['eliminar'] ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('eliminar')) . '"' ?>>
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
                                            title="Cambiar visibilidad"<?= $containerDisabledAttr ?>>
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

        <?php if ($isSpotifyPodcast): ?>
            <div class="carousel-drag-hint">
                <i class="bi bi-grip-vertical"></i> Arrastra las filas para cambiar el orden. Mantén el canal principal arriba y los episodios debajo.
            </div>
            <div class="table-responsive">
                <table class="table table-modern align-middle" id="generalItemsTable">
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th style="width:52px;">Orden</th>
                            <th>Tipo</th>
                            <th>Contenido</th>
                            <th>Fecha / duración</th>
                            <th style="width:90px;">Visible</th>
                            <th style="width:110px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="generalItemsTbody" data-section-id="<?= (int) $idSeccion ?>" data-can-edit="<?= $canEditContainer ? '1' : '0' ?>">
                        <?php foreach ($items as $item): ?>
                            <?php
                            $spotifyItemType = ($item['etiqueta'] ?? '') === 'canal_spotify' ? 'Canal' : 'Episodio';
                            $spotifyUrl = !empty($item['boton_1_url']) ? $item['boton_1_url'] : (!empty($item['url']) ? $item['url'] : '#');
                            ?>
                            <tr data-id="<?= (int) $item['id_item'] ?>">
                                <td style="text-align:center;vertical-align:middle;">
                                    <i class="bi bi-grip-vertical drag-handle" style="color:var(--adm-muted);font-size:1.1rem;cursor:grab;"></i>
                                </td>
                                <td class="item-orden-cell"><?= (int) $item['orden'] ?></td>
                                <td>
                                    <span class="badge-soft <?= $spotifyItemType === 'Canal' ? 'success' : 'info' ?>"><?= cms_e($spotifyItemType) ?></span>
                                    <div class="text-muted small mt-1"><?= cms_e($item['etiqueta'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="carousel-admin-placeholder" style="width:54px;height:54px;border-radius:14px;font-size:1.2rem;flex:0 0 auto;overflow:hidden;">
                                            <?php if (!empty($item['imagen'])): ?>
                                                <img src="<?= cms_e($item['imagen']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                            <?php else: ?>
                                                <i class="bi bi-spotify"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= cms_e($item['titulo'] ?: 'Sin título') ?></div>
                                            <?php if (!empty($item['descripcion'])): ?>
                                                <?php $spotifyAdminDesc = (string) $item['descripcion']; ?>
                                                <small class="text-muted"><?= cms_e(strlen($spotifyAdminDesc) > 110 ? substr($spotifyAdminDesc, 0, 110) . '...' : $spotifyAdminDesc) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?= cms_e($item['fecha_publicacion'] ?? '') ?></div>
                                    <small class="text-muted"><?= cms_e($item['subtitulo'] ?? '') ?></small>
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
                                        <a href="<?= cms_e($spotifyUrl) ?>" target="_blank" class="btn-icon preview" title="Ver" aria-label="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn-icon edit" title="Editar" aria-label="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="post" class="m-0" onsubmit="return confirm('¿Eliminar este item de Spotify?');">
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

        <?php if (!$isTopbar && !$isEventsCalendar && !$isCarouselAdmin && !$isGalleryAdmin && !$isSpotifyPodcast): ?>
            <div class="<?= $isNewsAdmin ? 'carousel-viewbar' : '' ?>">
                <div class="carousel-drag-hint">
                    <i class="bi bi-grip-vertical"></i> Puedes ordenar los items arrastrándolos. El cambio se guarda automáticamente.
                </div>
                <?php if ($isNewsAdmin): ?>
                    <div class="carousel-view-segment" role="group" aria-label="Cambiar vista de noticias" data-generic-view-module>
                        <button type="button" class="carousel-view-segment__btn is-active" data-generic-view-button="listado">
                            <i class="bi bi-list-ul"></i>Listado
                        </button>
                        <button type="button" class="carousel-view-segment__btn" data-generic-view-button="tarjetas">
                            <i class="bi bi-grid-3x3-gap"></i>Tarjetas
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="table-responsive" data-generic-view-panel="listado">
                <table class="table table-modern align-middle" id="generalItemsTable">
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th style="width:52px;">Orden</th>
                            <?php if ($isNewsAdmin): ?>
                                <th style="width:90px;">Imagen</th>
                            <?php endif; ?>
                            <th><?= $isVideoFeatured ? 'Video' : 'Título' ?></th>
                            <th style="width:90px;">Visible</th>
                            <th style="width:110px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="generalItemsTbody" data-section-id="<?= (int) $idSeccion ?>" data-can-edit="<?= $canEditContainer ? '1' : '0' ?>">
                        <?php if ($isCreatingNewsInline): ?>
                            <tr class="news-inline-edit-row" id="news-new-item">
                                <td colspan="<?= $isNewsAdmin ? 6 : 5 ?>">
                                    <?php
                                    admin_render_news_item_editor(
                                        [],
                                        $idSeccion,
                                        $categories,
                                        [],
                                        $containerPermissions['crear'],
                                        $section['nombre_interno'],
                                        'new',
                                        count($items) + 1,
                                        'editar_contenedor.php?id=' . (int) $idSeccion . '&tab=items'
                                    );
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($items as $item): ?>
                            <?php $isInlineNewsOpen = $isNewsAdmin && $editingNewsInlineId === (int) $item['id_item']; ?>
                            <tr data-id="<?= (int) $item['id_item'] ?>">
                                <td style="text-align:center;vertical-align:middle;">
                                    <i class="bi bi-grip-vertical drag-handle" style="color:var(--adm-muted);font-size:1.1rem;cursor:grab;"></i>
                                </td>
                                <td class="item-orden-cell"><?= (int) $item['orden'] ?></td>
                                <?php if ($isNewsAdmin): ?>
                                    <td>
                                        <div class="carousel-list-thumb">
                                            <?php if (!empty($item['imagen'])): ?>
                                                <img src="<?= cms_e($item['imagen']) ?>" alt="<?= cms_e($item['titulo'] ?: 'Noticia') ?>">
                                            <?php else: ?>
                                                <span><i class="bi bi-newspaper"></i></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
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
                                                <input class="form-check-input js-visible-toggle" type="checkbox" role="switch" style="margin-left:0;cursor:pointer;" <?= ($item['visible'] ?? '') === 'si' ? 'checked' : '' ?><?= $containerDisabledAttr ?>>
                                            </span>
                                        </form>
                                    </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= cms_e(!empty($item['boton_1_url']) ? $item['boton_1_url'] : (!empty($item['url']) ? $item['url'] : '#')) ?>" target="_blank" class="btn-icon preview" title="Ver" aria-label="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= $isNewsAdmin ? 'editar_contenedor.php?id=' . (int) $idSeccion . '&tab=items' . ($isInlineNewsOpen ? '' : '&edit_item=' . (int) $item['id_item'] . '#news-edit-' . (int) $item['id_item']) : 'editar_contenedor.php?id=' . (int) $idSeccion . '&tab=items&modal=item&item=' . (int) $item['id_item'] ?>" class="btn-icon edit <?= $isInlineNewsOpen ? 'active' : '' ?>" title="<?= $isInlineNewsOpen ? 'Cerrar edición' : 'Editar' ?>" aria-label="<?= $isInlineNewsOpen ? 'Cerrar edición' : 'Editar' ?>"<?= $canEditContainer || $isInlineNewsOpen ? '' : ' data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>>
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="post" class="m-0" onsubmit="return confirm('¿Eliminar este item?');">
                                            <input type="hidden" name="accion" value="eliminar_item">
                                            <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                            <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                            <button type="submit" class="btn-icon delete" title="Eliminar" aria-label="Eliminar"<?= $containerPermissions['eliminar'] ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('eliminar')) . '"' ?>>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php if ($isInlineNewsOpen): ?>
                                <tr class="news-inline-edit-row" id="news-edit-<?= (int) $item['id_item'] ?>" data-id="<?= (int) $item['id_item'] ?>-editor">
                                    <td colspan="<?= $isNewsAdmin ? 6 : 5 ?>">
                                        <?php
                                        admin_render_news_item_editor(
                                            $item,
                                            $idSeccion,
                                            $categories,
                                            cms_get_news_gallery_config($db, $idSeccion, (int) $item['id_item']),
                                            $containerPermissions['editar'],
                                            $section['nombre_interno'],
                                            (string) (int) $item['id_item'],
                                            (int) ($item['orden'] ?? 1),
                                            'editar_contenedor.php?id=' . (int) $idSeccion . '&tab=items'
                                        );
                                        ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($isNewsAdmin): ?>
                <div class="carousel-card-grid mb-4" id="genericCardsSortable" data-generic-view-panel="tarjetas" data-section-id="<?= (int) $idSeccion ?>" data-can-edit="<?= $canEditContainer ? '1' : '0' ?>" hidden>
                    <?php foreach ($items as $item): ?>
                        <?php $displayTitle = $item['titulo'] ?: trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? '')); ?>
                        <article class="carousel-admin-card" data-id="<?= (int) $item['id_item'] ?>">
                            <div class="carousel-admin-media">
                                <?php if (!empty($item['imagen'])): ?>
                                    <img src="<?= cms_e($item['imagen']) ?>" alt="<?= cms_e($displayTitle ?: 'Noticia') ?>">
                                <?php else: ?>
                                    <div class="carousel-admin-placeholder"><i class="bi bi-newspaper"></i></div>
                                <?php endif; ?>
                                <div class="dropdown carousel-card-menu">
                                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <a class="dropdown-item" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&edit_item=<?= (int) $item['id_item'] ?>#news-edit-<?= (int) $item['id_item'] ?>"<?= $canEditContainer ? '' : ' data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>>
                                            <i class="bi bi-pencil-square me-2"></i>Editar
                                        </a>
                                        <form method="post" onsubmit="return confirm('¿Eliminar esta noticia?');">
                                            <input type="hidden" name="accion" value="eliminar_item">
                                            <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                            <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                            <button type="submit" class="dropdown-item text-danger"<?= $containerPermissions['eliminar'] ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('eliminar')) . '"' ?>>
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
                                        title="Cambiar visibilidad"<?= $containerDisabledAttr ?>>
                                    <?= ($item['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?>
                                </button>
                                <h4 class="carousel-admin-title"><?= cms_e($displayTitle ?: 'Noticia sin título') ?></h4>
                                <?php if (!empty($item['etiqueta'])): ?>
                                    <div class="carousel-admin-meta"><?= cms_e($item['etiqueta']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['descripcion'])): ?>
                                    <?php $newsCardDesc = trim(strip_tags((string) $item['descripcion'])); ?>
                                    <p class="carousel-admin-desc"><?= cms_e(strlen($newsCardDesc) > 130 ? substr($newsCardDesc, 0, 130) . '...' : $newsCardDesc) ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
                                            <div class="field-note">Galería y videos que se mostrarán en el detalle público del evento.</div>
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
                    <input type="hidden" name="orden" value="<?= (int) ($editingItem['orden'] ?? count($topbarItems) + 1) ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= $editingItem ? 'Editar red social' : 'Agregar red social' ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="field-card" data-field-shell>
                                    <?php admin_modal_field_head('Nombre de la red', 'topbar_titulo', 'topbar', 'titulo'); ?>
                                    <input class="form-control" id="topbar_titulo" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>" placeholder="Instagram"<?= $topbarItemReadonlyAttr ?>>
                                    <div class="field-note">Si se bloquea, la red se guardará sin nombre visible.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-card" data-field-shell>
                                    <label class="form-label d-block mb-2">Icono</label>
                                    <input type="hidden" id="topbar_icono" name="icono" value="<?= cms_e($editingItem['icono'] ?? '') ?>">
                                    <div class="social-icon-presets" aria-label="Iconos rápidos">
                                        <?php foreach ($topbarSocialIcons as $socialIcon): ?>
                                            <button type="button" class="social-icon-preset" data-social-name="<?= cms_e($socialIcon['name']) ?>" data-social-icon="<?= cms_e($socialIcon['icon']) ?>" data-social-url="<?= cms_e($socialIcon['url']) ?>" title="<?= cms_e($socialIcon['name']) ?>" aria-label="<?= cms_e($socialIcon['name']) ?>"<?= $topbarItemDisabledAttr ?>>
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
                                    <input class="form-control" id="topbar_descripcion" name="descripcion" value="<?= cms_e($editingItem['descripcion'] ?? '') ?>" placeholder="https://instagram.com/..."<?= $topbarItemReadonlyAttr ?>>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="field-card">
                                    <?php admin_modal_field_head('Visible', 'topbar_visible', 'topbar', 'visible', false); ?>
                                    <label class="setting-toggle mb-0">
                                        <span class="setting-toggle-copy">Visible<small>Mostrar esta red</small></span>
                                        <span class="form-check form-switch mb-0 state-switch">
                                            <input class="form-check-input" id="topbar_visible" type="checkbox" name="visible" value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'checked' : '' ?><?= $topbarItemDisabledAttr ?>>
                                                </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-text mt-3">El sitio mostrará como máximo 4 redes visibles según el orden de la tabla.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-admin-action"<?= $topbarItemDisabledAttr ?>>Guardar red social</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="modal fade admin-modal <?= $isCarouselAdmin ? 'carousel-item-modal' : '' ?>" id="itemModal" tabindex="-1" aria-hidden="true"<?= $isCarouselAdmin ? ' data-bs-backdrop="static" data-bs-keyboard="false"' : '' ?>>
        <div class="modal-dialog modal-xl <?= $isCarouselAdmin ? '' : 'modal-dialog-scrollable' ?>">
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
                            <?php
                            $carouselEditingYoutubeId = admin_youtube_video_id((string) ($editingItem['url'] ?? ''));
                            $carouselInitialType = $carouselEditingYoutubeId !== '' ? 'video' : 'imagen';
                            $carouselCurrentImage = (string) ($editingItem['imagen'] ?? '');
                            ?>
                            <input type="hidden" id="item_orden" name="orden" value="<?= (int) ($editingItem['orden'] ?? count($items) + 1) ?>">
                            <input type="hidden" id="item_visible" name="visible" value="<?= cms_e($editingItem['visible'] ?? 'si') ?>">
                            <div class="carousel-slide-editor" data-carousel-slide-editor data-current-image="<?= cms_e($carouselCurrentImage) ?>">
                                <div class="carousel-slide-editor__main">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="field-card" data-field-shell>
                                                <?php admin_modal_field_head('Etiqueta', 'item_etiqueta', $section['nombre_interno'], 'etiqueta'); ?>
                                                <input class="form-control" id="item_etiqueta" name="etiqueta" value="<?= cms_e($editingItem['etiqueta'] ?? '') ?>"<?= $itemModalReadonlyAttr ?>>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-card" data-field-shell>
                                                <?php admin_modal_field_head('Título línea 1', 'item_titulo_linea_1', $section['nombre_interno'], 'titulo-linea-1'); ?>
                                                <input class="form-control" id="item_titulo_linea_1" name="titulo_linea_1" value="<?= cms_e($editingItem['titulo_linea_1'] ?? '') ?>"<?= $itemModalReadonlyAttr ?>>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-card" data-field-shell>
                                                <?php admin_modal_field_head('Título línea 2', 'item_titulo_linea_2', $section['nombre_interno'], 'titulo-linea-2'); ?>
                                                <input class="form-control" id="item_titulo_linea_2" name="titulo_linea_2" value="<?= cms_e($editingItem['titulo_linea_2'] ?? '') ?>"<?= $itemModalReadonlyAttr ?>>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-card" data-field-shell>
                                                <?php admin_modal_field_head('Título línea 3', 'item_titulo_linea_3', $section['nombre_interno'], 'titulo-linea-3'); ?>
                                                <input class="form-control" id="item_titulo_linea_3" name="titulo_linea_3" value="<?= cms_e($editingItem['titulo_linea_3'] ?? '') ?>"<?= $itemModalReadonlyAttr ?>>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="field-card" data-field-shell>
                                                <?php admin_modal_field_head('Descripción', 'item_descripcion', $section['nombre_interno'], 'descripcion'); ?>
                                                <textarea class="form-control" id="item_descripcion" name="descripcion" rows="4"<?= $itemModalReadonlyAttr ?>><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-card" data-field-shell>
                                                <?php admin_modal_field_head('Botón 1 texto', 'item_boton_1_texto', $section['nombre_interno'], 'boton-1-texto'); ?>
                                                <input class="form-control" id="item_boton_1_texto" name="boton_1_texto" value="<?= cms_e($editingItem['boton_1_texto'] ?? '') ?>"<?= $itemModalReadonlyAttr ?>>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-card" data-field-shell>
                                                <?php admin_modal_field_head('Botón 1 URL', 'item_boton_1_url', $section['nombre_interno'], 'boton-1-url'); ?>
                                                <input class="form-control" id="item_boton_1_url" name="boton_1_url" value="<?= cms_e($editingItem['boton_1_url'] ?? '') ?>"<?= $itemModalReadonlyAttr ?>>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-card" data-field-shell>
                                                <?php admin_modal_field_head('Botón 2 texto', 'item_boton_2_texto', $section['nombre_interno'], 'boton-2-texto'); ?>
                                                <input class="form-control" id="item_boton_2_texto" name="boton_2_texto" value="<?= cms_e($editingItem['boton_2_texto'] ?? '') ?>"<?= $itemModalReadonlyAttr ?>>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-card" data-field-shell>
                                                <?php admin_modal_field_head('Botón 2 URL', 'item_boton_2_url', $section['nombre_interno'], 'boton-2-url'); ?>
                                                <input class="form-control" id="item_boton_2_url" name="boton_2_url" value="<?= cms_e($editingItem['boton_2_url'] ?? '') ?>"<?= $itemModalReadonlyAttr ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <aside class="carousel-slide-editor__side">
                                    <div class="field-card carousel-slide-type-card">
                                        <div class="field-head">
                                            <label class="form-label mb-0">Tipo de slide</label>
                                        </div>
                                        <div class="slide-type-options" role="radiogroup" aria-label="Tipo de slide">
                                            <label class="slide-type-option">
                                                <input type="radio" name="slide_tipo" value="imagen" <?= $carouselInitialType === 'imagen' ? 'checked' : '' ?><?= $itemModalDisabledAttr ?>>
                                                <span><i class="bi bi-image"></i> Imagen</span>
                                            </label>
                                            <label class="slide-type-option">
                                                <input type="radio" name="slide_tipo" value="video" <?= $carouselInitialType === 'video' ? 'checked' : '' ?><?= $itemModalDisabledAttr ?>>
                                                <span><i class="bi bi-youtube"></i> Video YouTube</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="field-card" data-field-shell data-slide-resource="imagen">
                                        <?php admin_modal_field_head('Imagen', 'item_imagen', $section['nombre_interno'], 'imagen', true, 'clear_imagen'); ?>
                                        <input class="form-control" id="item_imagen" type="file" name="imagen" accept="image/*"<?= $itemModalDisabledAttr ?>>
                                        <div class="field-note">Recomendado: 1920 x 860 px, formato JPG o PNG optimizado.</div>
                                    </div>
                                    <div class="field-card" data-field-shell data-slide-resource="video">
                                        <?php admin_modal_field_head('Video YouTube', 'item_url_carousel', $section['nombre_interno'], 'url'); ?>
                                        <input class="form-control" id="item_url_carousel" name="url" value="<?= cms_e($editingItem['url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=..."<?= $itemModalDisabledAttr ?>>
                                        <input type="hidden" name="clear_imagen" value="1" data-carousel-clear-image disabled>
                                        <div class="field-note">Usa un enlace público de YouTube. El carrusel lo reproduce como fondo silenciado.</div>
                                    </div>
                                    <div class="carousel-resource-preview" data-carousel-preview>
                                        <div class="carousel-resource-preview__image" data-carousel-preview-image>
                                            <?php if ($carouselCurrentImage !== ''): ?>
                                                <img src="<?= cms_e($carouselCurrentImage) ?>" alt="Vista previa de imagen">
                                            <?php else: ?>
                                                <span><i class="bi bi-image"></i> Sin imagen seleccionada</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="carousel-resource-preview__video" data-carousel-preview-video>
                                            <i class="bi bi-youtube"></i>
                                            <strong>Video YouTube</strong>
                                            <span data-carousel-video-id><?= $carouselEditingYoutubeId !== '' ? 'ID: ' . cms_e($carouselEditingYoutubeId) : 'Ingresa una URL para detectar el video' ?></span>
                                        </div>
                                    </div>
                                    <div class="carousel-resource-tip">
                                        <i class="bi bi-info-circle"></i>
                                        <span>El slide debe usar imagen o video YouTube, no ambos.</span>
                                    </div>
                                </aside>
                            </div>
                        <?php elseif ($isSpotifyPodcast): ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Tipo de item', 'item_etiqueta_spotify', $section['nombre_interno'], 'etiqueta'); ?>
                                        <select class="form-select" id="item_etiqueta_spotify" name="etiqueta">
                                            <option value="canal_spotify" <?= ($editingItem['etiqueta'] ?? '') === 'canal_spotify' ? 'selected' : '' ?>>Canal principal</option>
                                            <option value="episodio_spotify" <?= ($editingItem['etiqueta'] ?? 'episodio_spotify') === 'episodio_spotify' ? 'selected' : '' ?>>Episodio destacado</option>
                                        </select>
                                        <div class="field-note">El canal principal se usa para portada, nombre y enlace principal. Los episodios aparecen en la lista derecha.</div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Título', 'item_titulo_spotify', $section['nombre_interno'], 'titulo'); ?>
                                        <input class="form-control" id="item_titulo_spotify" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>" placeholder="Nombre del canal o título del episodio">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Autor / duración', 'item_subtitulo_spotify', $section['nombre_interno'], 'subtitulo'); ?>
                                        <input class="form-control" id="item_subtitulo_spotify" name="subtitulo" value="<?= cms_e($editingItem['subtitulo'] ?? '') ?>" placeholder="Colegio San Pablo o Episodio 1 · 12 min">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Fecha del episodio', 'item_fecha_spotify', $section['nombre_interno'], 'fecha-publicacion'); ?>
                                        <input class="form-control" id="item_fecha_spotify" type="date" name="fecha_publicacion" value="<?= cms_e($editingItem['fecha_publicacion'] ?? '') ?>">
                                        <div class="field-note">Puede quedar vacía para el canal principal.</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Descripción', 'item_descripcion_spotify', $section['nombre_interno'], 'descripcion'); ?>
                                        <textarea class="form-control" id="item_descripcion_spotify" name="descripcion" placeholder="Descripción corta"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Texto del botón', 'item_boton_1_texto_spotify', $section['nombre_interno'], 'boton-1-texto'); ?>
                                        <input class="form-control" id="item_boton_1_texto_spotify" name="boton_1_texto" value="<?= cms_e($editingItem['boton_1_texto'] ?? 'Escuchar episodio') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('URL de Spotify', 'item_boton_1_url_spotify', $section['nombre_interno'], 'boton-1-url'); ?>
                                        <input class="form-control" id="item_boton_1_url_spotify" name="boton_1_url" value="<?= cms_e($editingItem['boton_1_url'] ?? '') ?>" placeholder="https://open.spotify.com/...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('URL alternativa', 'item_url_spotify', $section['nombre_interno'], 'url'); ?>
                                        <input class="form-control" id="item_url_spotify" name="url" value="<?= cms_e($editingItem['url'] ?? '') ?>" placeholder="https://open.spotify.com/...">
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

                        <?php if (!$isVideoFeatured && !$isModal && !$isCarouselAdmin): ?>
                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="field-card" data-field-shell>
                                        <?php admin_modal_field_head('Imagen', 'item_imagen', $section['nombre_interno'], 'imagen', true, 'clear_imagen'); ?>
                                        <input class="form-control" id="item_imagen" type="file" name="imagen" accept="image/*"<?= $isCarouselAdmin ? $itemModalDisabledAttr : '' ?>>
                                        <div class="field-note">Si bloqueas este campo, la imagen se guardará vacía.</div>
                                    </div>
                                </div>
                                <div class="<?= $isCarouselAdmin ? 'col-md-4' : 'col-md-3' ?>">
                                    <div class="field-card">
                                        <?php admin_modal_field_head('Visible', 'item_visible', $section['nombre_interno'], 'visible', false); ?>
                                        <label class="setting-toggle mb-0">
                                            <span class="setting-toggle-copy">Visible<small>Mostrar item</small></span>
                                            <span class="form-check form-switch mb-0 state-switch">
                                                <input class="form-check-input" id="item_visible" type="checkbox" name="visible" value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'checked' : '' ?><?= $isCarouselAdmin ? $itemModalDisabledAttr : '' ?>>
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
                        <button type="submit" class="btn btn-admin-action"<?= $isCarouselAdmin ? $itemModalDisabledAttr : '' ?>><?= $isCarouselAdmin ? 'Guardar' : 'Guardar item' ?></button>
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

<script>
(function () {
    var permissions = <?= $containerPermissionsJson ?: '{}' ?>;
    var deniedMessage = <?= json_encode(admin_permiso_denegado_mensaje('editar'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function requiredAction(form, actionValue) {
        if (['guardar_topbar_general', 'guardar_header_general', 'toggle_topbar_item_visible', 'toggle_evento', 'cancelar_evento', 'toggle_item_visible', 'reorder_items', 'reorder_news_gallery', 'guardar_seccion'].indexOf(actionValue) >= 0) {
            return 'editar';
        }
        if (['guardar_topbar_item', 'guardar_evento', 'guardar_item'].indexOf(actionValue) >= 0) {
            var id = parseInt((form.querySelector('input[name="id_item"], input[name="id_evento"]') || {}).value || '0', 10);
            return id > 0 ? 'editar' : 'crear';
        }
        if (actionValue === 'eliminar_item' || actionValue.indexOf('eliminar_evento_media:') === 0) {
            return 'eliminar';
        }
        if (actionValue.indexOf('toggle_evento_media:') === 0) {
            return 'editar';
        }
        return '';
    }

    document.querySelectorAll('form').forEach(function (form) {
        var actionInput = form.querySelector('input[name="accion"]');
        if (!actionInput) { return; }
        form.addEventListener('submit', function (event) {
            var action = requiredAction(form, actionInput.value);
            if (!action || permissions[action]) { return; }
            event.preventDefault();
            if (window.Swal) {
                Swal.fire({ icon: 'warning', title: 'Permiso requerido', text: deniedMessage, confirmButtonColor: '#2b64d8' });
            } else if (window.adminNotify) {
                adminNotify({ title: 'Permiso requerido', msg: deniedMessage, type: 'warning' });
            } else {
                alert(deniedMessage);
            }
        });
    });

    if (!permissions.editar) {
        document.querySelectorAll('[data-save-order], .js-sortable-handle').forEach(function (node) {
            node.setAttribute('data-admin-denied', deniedMessage);
            node.classList.add('is-disabled');
        });
    }
})();
</script>

<?php
admin_render_layout_end([
    'extra_scripts' => str_replace(
        'OPEN_MODAL_PLACEHOLDER',
        json_encode($openModal, JSON_UNESCAPED_UNICODE),
        <<<'HTML'
    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
    <script>
        if (window.CKEDITOR) {
            CKEDITOR.config.versionCheck = false;
        }

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

            var topbarTbody = document.getElementById('topbarItemsTbody');
            if ($('#itemsTable').length) {
                $('#itemsTable').DataTable({
                    pageLength: 10,
                    ordering: !topbarTbody,
                    paging: !topbarTbody,
                    info: !topbarTbody,
                    order: topbarTbody ? [] : [[0, 'asc']],
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
                });
            }

            var generalTbody = document.getElementById('generalItemsTbody');
            var genericCardsSortable = document.getElementById('genericCardsSortable');
            document.querySelectorAll('[data-generic-view-module]').forEach(function (module) {
                var buttons = Array.prototype.slice.call(module.querySelectorAll('[data-generic-view-button]'));
                var panels = Array.prototype.slice.call(document.querySelectorAll('[data-generic-view-panel]'));
                function setGenericView(view) {
                    buttons.forEach(function (button) {
                        button.classList.toggle('is-active', button.getAttribute('data-generic-view-button') === view);
                    });
                    panels.forEach(function (panel) {
                        panel.hidden = panel.getAttribute('data-generic-view-panel') !== view;
                    });
                }
                buttons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setGenericView(button.getAttribute('data-generic-view-button'));
                    });
                });
            });
            if (generalTbody && typeof Sortable !== 'undefined' && generalTbody.getAttribute('data-can-edit') === '1') {
                var saveGenTimeout = null;
                function generalIdsFrom(container, selector) {
                    if (!container) { return []; }
                    return Array.from(container.querySelectorAll(selector))
                        .map(function (item) { return item.dataset.id; })
                        .filter(function (id) { return id && id.indexOf('-editor') < 0; });
                }
                function syncGeneralOrder(target, selector, ids) {
                    if (!target) { return; }
                    ids.forEach(function (id) {
                        var node = target.querySelector(selector + '[data-id="' + id + '"]');
                        if (node) {
                            target.appendChild(node);
                            var inlineEditor = target.querySelector('tr[data-id="' + id + '-editor"]');
                            if (inlineEditor && node.parentNode === target) {
                                target.insertBefore(inlineEditor, node.nextSibling);
                            }
                        }
                    });
                    ids.forEach(function (id, idx) {
                        var row = target.querySelector(selector + '[data-id="' + id + '"]');
                        var cell = row ? row.querySelector('.item-orden-cell') : null;
                        if (cell) { cell.textContent = idx + 1; }
                    });
                }
                function saveGeneralOrder(ids) {
                    clearTimeout(saveGenTimeout);
                    saveGenTimeout = setTimeout(function () {
                        syncGeneralOrder(generalTbody, 'tr', ids);
                        syncGeneralOrder(genericCardsSortable, '.carousel-admin-card', ids);
                        var fd = new FormData();
                        fd.append('accion', 'reorder_items');
                        fd.append('id_seccion', generalTbody.getAttribute('data-section-id') || '<?= (int) $idSeccion ?>');
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
                Sortable.create(generalTbody, {
                    animation: 180,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onEnd: function () {
                        saveGeneralOrder(generalIdsFrom(generalTbody, 'tr'));
                    }
                });
                if (genericCardsSortable) {
                    Sortable.create(genericCardsSortable, {
                        animation: 200,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        onEnd: function () {
                            saveGeneralOrder(generalIdsFrom(genericCardsSortable, '.carousel-admin-card'));
                        }
                    });
                }
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

            document.querySelectorAll('.js-carousel-config-switch').forEach(function (toggle) {
                var hidden = toggle.parentElement ? toggle.parentElement.querySelector('[data-carousel-switch-value]') : null;
                if (!hidden) { return; }
                toggle.addEventListener('change', function () {
                    hidden.value = toggle.checked ? 'si' : 'no';
                });
            });

            document.querySelectorAll('[data-carousel-view-module]').forEach(function (module) {
                var buttons = Array.prototype.slice.call(module.querySelectorAll('[data-carousel-view-button]'));
                var panels = Array.prototype.slice.call(module.parentElement.querySelectorAll('[data-carousel-view-panel]'));

                function setCarouselView(view) {
                    buttons.forEach(function (button) {
                        button.classList.toggle('is-active', button.getAttribute('data-carousel-view-button') === view);
                    });
                    panels.forEach(function (panel) {
                        panel.hidden = panel.getAttribute('data-carousel-view-panel') !== view;
                    });
                }

                buttons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setCarouselView(button.getAttribute('data-carousel-view-button'));
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

            if (window.CKEDITOR) {
                document.querySelectorAll('textarea.js-news-editor').forEach(function (textarea) {
                    if (textarea.dataset.ckeditorReady === '1') {
                        return;
                    }
                    textarea.dataset.ckeditorReady = '1';
                    CKEDITOR.replace(textarea.id, {
                        height: 230,
                        versionCheck: false,
                        removePlugins: 'elementspath,image,flash,iframe,forms,smiley,specialchar,about',
                        resize_enabled: false,
                        toolbar: [
                            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'RemoveFormat'] },
                            { name: 'paragraph', items: ['BulletedList', 'NumberedList', 'Blockquote'] },
                            { name: 'links', items: ['Link', 'Unlink'] },
                            { name: 'colors', items: ['TextColor'] },
                            { name: 'undo', items: ['Undo', 'Redo'] }
                        ]
                    });
                });
                document.querySelectorAll('form').forEach(function (form) {
                    form.addEventListener('submit', function () {
                        Object.keys(CKEDITOR.instances).forEach(function (key) {
                            CKEDITOR.instances[key].updateElement();
                        });
                    });
                });
            }

            var openModal = OPEN_MODAL_PLACEHOLDER;
            if (openModal === 'item') {
                var itemModalEl = document.getElementById('itemModal');
                if (itemModalEl) {
                    var itemModalOptions = itemModalEl.classList.contains('carousel-item-modal') ? { backdrop: 'static', keyboard: false } : {};
                    var modal = new bootstrap.Modal(itemModalEl, itemModalOptions);
                    modal.show();
                }
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

            function carouselYoutubeVideoId(url) {
                url = (url || '').trim();
                if (!url) { return ''; }
                var patterns = [
                    /youtu\.be\/([A-Za-z0-9_-]{6,})/i,
                    /youtube\.com\/(?:embed|shorts|live)\/([A-Za-z0-9_-]{6,})/i,
                    /[?&]v=([A-Za-z0-9_-]{6,})/i
                ];
                for (var i = 0; i < patterns.length; i++) {
                    var match = url.match(patterns[i]);
                    if (match && match[1]) { return match[1]; }
                }
                return '';
            }

            function carouselSlideNotice(message) {
                if (typeof adminNotify === 'function') {
                    adminNotify({ title: 'Revisa el slide', msg: message, type: 'warning', autoClose: 2600 });
                    return;
                }
                alert(message);
            }

            function setCarouselSlideType(modal, type, clearInactive) {
                var editor = modal ? modal.querySelector('[data-carousel-slide-editor]') : null;
                if (!editor) { return; }

                type = type === 'video' ? 'video' : 'imagen';
                var imageWrap = editor.querySelector('[data-slide-resource="imagen"]');
                var videoWrap = editor.querySelector('[data-slide-resource="video"]');
                var imageInput = editor.querySelector('#item_imagen');
                var youtubeInput = editor.querySelector('#item_url_carousel');
                var clearImageInput = editor.querySelector('[data-carousel-clear-image]');
                var radio = editor.querySelector('input[name="slide_tipo"][value="' + type + '"]');

                if (radio) { radio.checked = true; }
                if (imageWrap) { imageWrap.hidden = type !== 'imagen'; }
                if (videoWrap) { videoWrap.hidden = type !== 'video'; }

                if (imageInput) {
                    imageInput.disabled = type !== 'imagen' || imageInput.hasAttribute('data-admin-denied');
                    if (type !== 'imagen' && clearInactive) {
                        imageInput.value = '';
                    }
                }
                if (youtubeInput) {
                    youtubeInput.disabled = type !== 'video' || youtubeInput.hasAttribute('data-admin-denied');
                    if (type !== 'video' && clearInactive) {
                        youtubeInput.value = '';
                    }
                }
                if (clearImageInput) {
                    clearImageInput.disabled = type !== 'video';
                }

                updateCarouselResourcePreview(modal);
            }

            function updateCarouselResourcePreview(modal) {
                var editor = modal ? modal.querySelector('[data-carousel-slide-editor]') : null;
                if (!editor) { return; }
                var typeInput = editor.querySelector('input[name="slide_tipo"]:checked');
                var type = typeInput ? typeInput.value : 'imagen';
                var imageBox = editor.querySelector('[data-carousel-preview-image]');
                var videoBox = editor.querySelector('[data-carousel-preview-video]');
                var videoIdEl = editor.querySelector('[data-carousel-video-id]');
                var imageInput = editor.querySelector('#item_imagen');
                var youtubeInput = editor.querySelector('#item_url_carousel');
                var currentImage = editor.dataset.currentImage || '';

                if (imageBox) { imageBox.hidden = type !== 'imagen'; }
                if (videoBox) { videoBox.hidden = type !== 'video'; }

                if (type === 'imagen' && imageBox) {
                    if (imageInput && imageInput.files && imageInput.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function (event) {
                            imageBox.innerHTML = '<img src="' + event.target.result + '" alt="Vista previa de imagen">';
                        };
                        reader.readAsDataURL(imageInput.files[0]);
                    } else if (currentImage) {
                        imageBox.innerHTML = '<img src="' + currentImage + '" alt="Vista previa de imagen">';
                    } else {
                        imageBox.innerHTML = '<span><i class="bi bi-image"></i> Sin imagen seleccionada</span>';
                    }
                }

                if (type === 'video' && videoIdEl) {
                    var videoId = carouselYoutubeVideoId(youtubeInput ? youtubeInput.value : '');
                    videoIdEl.textContent = videoId ? ('ID: ' + videoId) : 'Ingresa una URL para detectar el video';
                }
            }

            var carouselModal = document.getElementById('itemModal');
            if (carouselModal && carouselModal.classList.contains('carousel-item-modal')) {
                carouselModal.querySelectorAll('input[name="slide_tipo"]').forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        setCarouselSlideType(carouselModal, radio.value, true);
                    });
                });
                var carouselImageInput = carouselModal.querySelector('#item_imagen');
                if (carouselImageInput) {
                    carouselImageInput.addEventListener('change', function () {
                        updateCarouselResourcePreview(carouselModal);
                    });
                }
                var carouselYoutubeInput = carouselModal.querySelector('#item_url_carousel');
                if (carouselYoutubeInput) {
                    carouselYoutubeInput.addEventListener('input', function () {
                        updateCarouselResourcePreview(carouselModal);
                    });
                }
                var carouselForm = carouselModal.querySelector('form');
                if (carouselForm) {
                    carouselForm.addEventListener('submit', function (event) {
                        var typeInput = carouselModal.querySelector('input[name="slide_tipo"]:checked');
                        var type = typeInput ? typeInput.value : 'imagen';
                        var idInput = carouselForm.querySelector('[name="id_item"]');
                        var isNew = !idInput || parseInt(idInput.value || '0', 10) <= 0;
                        var imageInput = carouselForm.querySelector('#item_imagen');
                        var youtubeInput = carouselForm.querySelector('#item_url_carousel');
                        var currentImage = (carouselModal.querySelector('[data-carousel-slide-editor]') || {}).dataset.currentImage || '';
                        var hasImageFile = !!(imageInput && imageInput.files && imageInput.files.length);
                        var hasYoutube = carouselYoutubeVideoId(youtubeInput ? youtubeInput.value : '') !== '';

                        if (hasImageFile && hasYoutube) {
                            event.preventDefault();
                            event.stopImmediatePropagation();
                            carouselSlideNotice('Selecciona imagen o video YouTube, no ambos.');
                            return;
                        }

                        if (type === 'video') {
                            if (!hasYoutube) {
                                event.preventDefault();
                                event.stopImmediatePropagation();
                                carouselSlideNotice('Ingresa una URL válida de YouTube para este slide.');
                                return;
                            }
                            var clearImageInput = carouselForm.querySelector('[data-carousel-clear-image]');
                            if (clearImageInput) { clearImageInput.disabled = false; }
                            if (imageInput) { imageInput.value = ''; }
                        } else {
                            if (youtubeInput) {
                                youtubeInput.disabled = false;
                                youtubeInput.value = '';
                            }
                            if ((isNew || !currentImage) && !hasImageFile) {
                                event.preventDefault();
                                event.stopImmediatePropagation();
                                carouselSlideNotice('Selecciona una imagen para este slide.');
                                return;
                            }
                        }
                    }, true);
                }
                setCarouselSlideType(carouselModal, carouselYoutubeVideoId(carouselYoutubeInput ? carouselYoutubeInput.value : '') ? 'video' : 'imagen', false);
            }

            document.querySelectorAll('.js-carousel-edit').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (btn.getAttribute('data-admin-denied')) {
                        return;
                    }
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
                    set('#item_url_carousel', item.url);
                    set('#item_visible', item.visible);
                    set('#item_orden', item.orden !== undefined ? String(item.orden) : '1');
                    set('#item_imagen', '');

                    var editor = modal.querySelector('[data-carousel-slide-editor]');
                    if (editor) {
                        editor.dataset.currentImage = item.imagen || '';
                    }
                    setCarouselSlideType(modal, carouselYoutubeVideoId(item.url || '') ? 'video' : 'imagen', false);

                    var titleEl = modal.querySelector('.modal-title');
                    if (titleEl) { titleEl.textContent = 'Editar item'; }

                    bootstrap.Modal.getOrCreateInstance(
                        modal,
                        modal.classList.contains('carousel-item-modal') ? { backdrop: 'static', keyboard: false } : {}
                    ).show();
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
        var topbarTbody = document.getElementById('topbarItemsTbody');
        if (topbarTbody && typeof Sortable !== 'undefined') {
            var saveTopbarTimeout = null;
            Sortable.create(topbarTbody, {
                animation: 180,
                handle: '.topbar-drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function () {
                    clearTimeout(saveTopbarTimeout);
                    saveTopbarTimeout = setTimeout(function () {
                        var ids = Array.from(topbarTbody.querySelectorAll('tr'))
                            .map(function (tr) { return tr.dataset.id; })
                            .filter(Boolean);
                        var fd = new FormData();
                        fd.append('accion', 'reorder_items');
                        fd.append('id_seccion', topbarTbody.dataset.sectionId || '');
                        ids.forEach(function (id) { fd.append('items[]', id); });
                        fetch(window.location.href, {
                            method: 'POST',
                            body: fd,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.ok) {
                                adminNotify({ title: 'Orden guardado', msg: 'El orden de las redes sociales fue actualizado.', type: 'info', autoClose: 1800 });
                            }
                        })
                        .catch(function () {});
                    }, 350);
                }
            });
        }

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

        function syncNewsGalleryOrder(grid) {
            Array.from(grid.querySelectorAll('.news-gallery-card')).forEach(function (card, index) {
                var orderInput = card.querySelector('input[name^="news_gallery_order["]');
                if (orderInput) {
                    orderInput.value = String(index + 1);
                }
            });
        }

        document.querySelectorAll('.js-news-gallery-sortable').forEach(function (grid) {
            syncNewsGalleryOrder(grid);
            if (typeof Sortable === 'undefined') { return; }
            Sortable.create(grid, {
                animation: 180,
                draggable: '.news-gallery-card',
                filter: 'input,button,.dropdown-menu,.news-gallery-card-body',
                preventOnFilter: false,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function () {
                    syncNewsGalleryOrder(grid);
                    var newsId = grid.getAttribute('data-news-id') || '0';
                    if (newsId === '0') { return; }
                    var ids = Array.from(grid.querySelectorAll('.news-gallery-card'))
                        .map(function (card) { return card.getAttribute('data-id'); })
                        .filter(Boolean);
                    var fd = new FormData();
                    fd.append('accion', 'reorder_news_gallery');
                    fd.append('id_seccion', grid.getAttribute('data-section-id') || '<?= (int) $idSeccion ?>');
                    fd.append('id_item', newsId);
                    ids.forEach(function (id) { fd.append('items[]', id); });
                    fetch(window.location.href, {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.ok) {
                            adminNotify({ title: 'Orden guardado', msg: 'El orden de la galería fue actualizado.', type: 'info', autoClose: 1800 });
                        }
                    })
                    .catch(function () {});
                }
            });
        });

        document.addEventListener('click', function (event) {
            var editBtn = event.target.closest('.js-news-gallery-edit');
            if (editBtn) {
                var editCard = editBtn.closest('.news-gallery-card');
                if (editCard) {
                    editCard.classList.toggle('is-editing');
                }
                return;
            }

            var deleteBtn = event.target.closest('.js-news-gallery-delete');
            if (deleteBtn) {
                var deleteCard = deleteBtn.closest('.news-gallery-card');
                var deleteCheck = deleteCard ? deleteCard.querySelector('.js-news-gallery-delete-check') : null;
                if (deleteCheck) {
                    deleteCheck.checked = true;
                    deleteCard.classList.add('is-delete-pending', 'is-editing');
                }
            }
        });

        document.addEventListener('change', function (event) {
            var visibleCheck = event.target.closest('.js-news-gallery-visible');
            if (!visibleCheck) { return; }
            var card = visibleCheck.closest('.news-gallery-card');
            var badge = card ? card.querySelector('.news-gallery-state') : null;
            if (!badge) { return; }
            badge.textContent = visibleCheck.checked ? 'Activo' : 'Oculto';
            badge.classList.toggle('is-hidden', !visibleCheck.checked);
        });

        document.addEventListener('input', function (event) {
            var titleInput = event.target.closest('input[name^="news_gallery_titles["]');
            if (!titleInput) { return; }
            var card = titleInput.closest('.news-gallery-card');
            var title = card ? card.querySelector('.news-gallery-title') : null;
            if (title) {
                title.textContent = titleInput.value.trim() || 'Imagen de noticia';
            }
        });

        function buildNewsGalleryCard(item) {
            var galleryId = String(item.id || '');
            var visible = !(item.visible === 0 || item.visible === '0');
            var titulo = item.titulo || '';

            var card = document.createElement('article');
            card.className = 'news-gallery-card news-gallery-card--wide';
            card.setAttribute('data-id', galleryId);

            var img = document.createElement('img');
            img.src = item.archivo || '';
            img.alt = titulo || 'Imagen de noticia';
            card.appendChild(img);

            var menuWrap = document.createElement('div');
            menuWrap.className = 'dropdown news-gallery-menu';
            var toggleBtn = document.createElement('button');
            toggleBtn.className = 'dropdown-toggle';
            toggleBtn.type = 'button';
            toggleBtn.setAttribute('data-bs-toggle', 'dropdown');
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.setAttribute('aria-label', 'Acciones de imagen');
            toggleBtn.innerHTML = '<i class="bi bi-three-dots-vertical"></i>';
            var menu = document.createElement('div');
            menu.className = 'dropdown-menu dropdown-menu-end shadow-sm';
            menu.innerHTML = '<button type="button" class="dropdown-item js-news-gallery-edit"><i class="bi bi-pencil-square me-2"></i>Editar</button>' +
                '<button type="button" class="dropdown-item text-danger js-news-gallery-delete"><i class="bi bi-trash me-2"></i>Eliminar</button>';
            menuWrap.appendChild(toggleBtn);
            menuWrap.appendChild(menu);
            card.appendChild(menuWrap);

            var titleSpan = document.createElement('span');
            titleSpan.className = 'news-gallery-title';
            titleSpan.textContent = titulo || 'Imagen de noticia';
            card.appendChild(titleSpan);

            var stateSpan = document.createElement('span');
            stateSpan.className = 'news-gallery-state' + (visible ? '' : ' is-hidden');
            stateSpan.textContent = visible ? 'Activo' : 'Oculto';
            card.appendChild(stateSpan);

            var body = document.createElement('div');
            body.className = 'news-gallery-card-body';

            var orderInput = document.createElement('input');
            orderInput.type = 'hidden';
            orderInput.name = 'news_gallery_order[' + galleryId + ']';
            orderInput.value = '1';
            body.appendChild(orderInput);

            var titleInputEl = document.createElement('input');
            titleInputEl.className = 'form-control';
            titleInputEl.name = 'news_gallery_titles[' + galleryId + ']';
            titleInputEl.value = titulo;
            titleInputEl.placeholder = 'Título / alt';
            body.appendChild(titleInputEl);

            var row = document.createElement('div');
            row.className = 'd-flex align-items-center justify-content-between gap-2';

            var visLabel = document.createElement('label');
            visLabel.className = 'form-check d-flex align-items-center gap-2 mb-0';
            var visInput = document.createElement('input');
            visInput.type = 'checkbox';
            visInput.className = 'form-check-input m-0 js-news-gallery-visible';
            visInput.name = 'news_gallery_visible[' + galleryId + ']';
            visInput.value = '1';
            visInput.checked = visible;
            visLabel.appendChild(visInput);
            visLabel.appendChild(document.createTextNode(' Mostrar'));
            row.appendChild(visLabel);

            var delLabel = document.createElement('label');
            delLabel.className = 'text-danger d-flex align-items-center gap-2 mb-0';
            var delInput = document.createElement('input');
            delInput.type = 'checkbox';
            delInput.className = 'form-check-input m-0 js-news-gallery-delete-check';
            delInput.name = 'delete_news_gallery[]';
            delInput.value = galleryId;
            delLabel.appendChild(delInput);
            delLabel.appendChild(document.createTextNode(' Eliminar'));
            row.appendChild(delLabel);

            body.appendChild(row);
            card.appendChild(body);

            return card;
        }

        document.querySelectorAll('.js-news-gallery-add-input').forEach(function (input) {
            input.addEventListener('change', function () {
                var files = input.files;
                if (!files || !files.length) { return; }
                if (input.hasAttribute('data-admin-denied')) {
                    if (window.adminNotifyDenied) { window.adminNotifyDenied(input.getAttribute('data-admin-denied')); }
                    input.value = '';
                    return;
                }

                var wrap = input.closest('[data-news-gallery-wrap]');
                var newsId = wrap ? wrap.getAttribute('data-news-id') : '0';
                var sectionId = (wrap && wrap.getAttribute('data-section-id')) || '<?= (int) $idSeccion ?>';

                if (!newsId || newsId === '0') {
                    adminNotify({ title: 'Guarda primero', msg: 'Primero guarda la noticia para poder agregar imágenes a la galería.', type: 'info' });
                    input.value = '';
                    return;
                }

                var grid = wrap.querySelector('.js-news-gallery-sortable');
                var emptyNotice = wrap.querySelector('[data-news-gallery-empty]');
                var label = input.closest('label');

                var fd = new FormData();
                fd.append('accion', 'subir_galeria_noticia');
                fd.append('id_seccion', sectionId);
                fd.append('id_item', newsId);
                Array.from(files).forEach(function (file) { fd.append('news_gallery_images[]', file); });

                if (label) { label.classList.add('is-uploading'); }

                fetch(window.location.href, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    input.value = '';
                    if (label) { label.classList.remove('is-uploading'); }
                    if (!data.ok) {
                        adminNotify({ title: 'Error', msg: data.message || 'No se pudieron subir las imágenes.', type: 'danger' });
                        return;
                    }
                    var gallery = data.gallery || [];
                    if (grid) {
                        grid.querySelectorAll('.news-gallery-card').forEach(function (card) { card.remove(); });
                        gallery.forEach(function (item) { grid.appendChild(buildNewsGalleryCard(item)); });
                        syncNewsGalleryOrder(grid);
                        grid.hidden = gallery.length === 0;
                    }
                    if (emptyNotice) { emptyNotice.hidden = gallery.length > 0; }
                    adminNotify({ title: 'Imágenes agregadas', msg: 'La galería del detalle fue actualizada.', type: 'success', autoClose: 1800 });
                })
                .catch(function () {
                    input.value = '';
                    if (label) { label.classList.remove('is-uploading'); }
                    adminNotify({ title: 'Error', msg: 'No se pudieron subir las imágenes.', type: 'danger' });
                });
            });
        });
    })();

    (function () {
        document.querySelectorAll('[data-news-video-wrap]').forEach(function (wrap) {
            function setNewsVideoType(type) {
                var youtubeWrap = wrap.querySelector('[data-news-video-resource="youtube"]');
                var fileWrap = wrap.querySelector('[data-news-video-resource="archivo"]');
                var youtubeInput = youtubeWrap ? youtubeWrap.querySelector('input') : null;
                var fileInput = fileWrap ? fileWrap.querySelector('input') : null;

                if (youtubeWrap) { youtubeWrap.hidden = type !== 'youtube'; }
                if (fileWrap) { fileWrap.hidden = type !== 'archivo'; }

                if (youtubeInput) {
                    var youtubeDenied = youtubeInput.hasAttribute('data-admin-denied');
                    youtubeInput.disabled = type !== 'youtube' || youtubeDenied;
                    if (type !== 'youtube') { youtubeInput.value = ''; }
                }
                if (fileInput) {
                    var fileDenied = fileInput.hasAttribute('data-admin-denied');
                    fileInput.disabled = type !== 'archivo' || fileDenied;
                    if (type !== 'archivo') { fileInput.value = ''; }
                }
            }

            wrap.querySelectorAll('input[name="video_tipo"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    setNewsVideoType(radio.value);
                });
            });

            var checkedVideoType = wrap.querySelector('input[name="video_tipo"]:checked');
            setNewsVideoType(checkedVideoType ? checkedVideoType.value : 'youtube');
        });
    })();

    (function () {
        var grid = document.getElementById('carouselSortable');
        var tableBody = document.getElementById('carouselListSortable');
        var sectionId = (grid && grid.getAttribute('data-section-id')) || (tableBody && tableBody.getAttribute('data-section-id')) || '';
        var canEditCarousel = ((grid && grid.getAttribute('data-can-edit')) || (tableBody && tableBody.getAttribute('data-can-edit')) || '0') === '1';
        if ((!grid && !tableBody) || typeof Sortable === 'undefined' || !canEditCarousel) { return; }

        var saveTimeout = null;

        function idsFrom(container, selector) {
            if (!container) { return []; }
            return Array.from(container.querySelectorAll(selector))
                .map(function (item) { return item.dataset.id; })
                .filter(Boolean);
        }

        function syncOrder(target, selector, ids) {
            if (!target) { return; }
            ids.forEach(function (id) {
                var node = target.querySelector(selector + '[data-id="' + id + '"]');
                if (node) {
                    target.appendChild(node);
                }
            });
            ids.forEach(function (id, index) {
                var row = target.querySelector(selector + '[data-id="' + id + '"]');
                var cell = row ? row.querySelector('.item-orden-cell') : null;
                if (cell) { cell.textContent = index + 1; }
            });
        }

        function saveCarouselOrder(ids) {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function () {
                var fd = new FormData();
                fd.append('accion', 'reorder_items');
                fd.append('id_seccion', sectionId);
                ids.forEach(function (id) { fd.append('items[]', id); });

                fetch(window.location.href, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok) {
                        adminNotify({ title: 'Orden guardado', msg: 'El nuevo orden de las diapositivas fue guardado.', type: 'info', autoClose: 1800 });
                    }
                })
                .catch(function () {});
            }, 400);
        }

        if (grid) {
            Sortable.create(grid, {
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function () {
                    var ids = idsFrom(grid, '.carousel-admin-card');
                    syncOrder(tableBody, 'tr', ids);
                    saveCarouselOrder(ids);
                }
            });
        }

        if (tableBody) {
            Sortable.create(tableBody, {
                animation: 180,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function () {
                    var ids = idsFrom(tableBody, 'tr');
                    syncOrder(grid, '.carousel-admin-card', ids);
                    syncOrder(tableBody, 'tr', ids);
                    saveCarouselOrder(ids);
                }
            });
        }
    })();
    </script>
HTML
    ) . ($isEventsCalendar ? '<script src="assets/js/admin_eventos.js"></script>' : ''),
]);
?>

