<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index_1.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/eventos_excel_helpers.php';
require_once __DIR__ . '/includes/admin_layout.php';

$idSeccion = (int) ($_POST['id_seccion'] ?? $_GET['id'] ?? 0);
$redirect = 'editar_contenedor.php?id=' . $idSeccion . '&tab=items';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Metodo no permitido.');
    }
    if (empty($_FILES['archivo_excel']['tmp_name'])) {
        throw new RuntimeException('Debes seleccionar un archivo Excel.');
    }

    $db = cms_get_connection();
    $rows = eventos_parse_import_file($_FILES['archivo_excel']['tmp_name'], (string) ($_FILES['archivo_excel']['name'] ?? ''));
    $previewRows = eventos_validate_preview_rows($db, $rows);
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
    cms_redirect($redirect);
}

$site = cms_get_site_data($db);

admin_render_layout_start([
    'title' => 'Preview importacion eventos | Colegio San Pablo',
    'page_title' => 'Validacion previa de eventos',
    'breadcrumb' => 'Contenedores del sitio / calendario_eventos_home',
    'active_panel' => 'contenedores',
    'institution_name' => $site['institution']['nombre'] ?? 'Institucion activa',
    'institution_short_name' => $site['institution']['nombre_corto'] ?? ($site['institution']['nombre'] ?? 'Institucion'),
    'institution_logo' => $site['institution']['logo_header'] ?? '',
    'admin_name' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'header_actions' => '<a href="' . cms_e($redirect) . '" class="btn btn-soft"><i class="bi bi-arrow-left me-2"></i>Volver a eventos</a>',
    'extra_head' => <<<'HTML'
    <style>
        .event-preview-panel { position: relative; min-height: 280px; border: 1px solid #e4ebf5; border-radius: 18px; background: #fff; box-shadow: 0 8px 18px rgba(18, 35, 68, 0.04); overflow: hidden; }
        .event-preview-content { transition: opacity .2s ease; }
        .event-preview-panel.is-loading .event-preview-content { opacity: 0; pointer-events: none; }
        .event-preview-loading { position: absolute; inset: 0; z-index: 5; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,.94); }
        .event-preview-loading-card { width: min(360px, calc(100vw - 32px)); text-align: center; padding: 24px; }
        .event-preview-loading-card .spinner-border { width: 2.5rem; height: 2.5rem; color: #1f8f6b; }
        .event-preview-loading-card strong { display: block; margin-top: 14px; color: #162338; font-weight: 800; }
        .event-preview-loading-card small { display: block; margin-top: 4px; color: #72809a; }
        .event-preview-panel:not(.is-loading) .event-preview-loading { display: none; }
        .event-preview-summary { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:16px; border-bottom:1px solid #e8edf6; }
        .event-preview-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .event-status-list { margin:0; padding-left:0; list-style:none; display:grid; gap:4px; }
        .event-status-list li { font-size:.82rem; color:#64748b; }
        .event-status-list .ok { color:#198754; font-weight:700; }
        .event-status-list .warn { color:#b45309; font-weight:700; }
    </style>
HTML,
]);

$validCount = count(array_filter($previewRows, static fn($item): bool => !empty($item['valid'])));
?>

<form method="post" action="eventos_importar_confirmados.php" class="event-preview-panel is-loading" id="eventPreviewImportForm">
    <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
    <div class="event-preview-loading" aria-live="polite">
        <div class="event-preview-loading-card">
            <div class="spinner-border" role="status" aria-label="Validando eventos"></div>
            <strong>Validando eventos</strong>
            <small>Mostrando resultados en unos segundos...</small>
        </div>
    </div>
    <div class="event-preview-content">
        <div class="event-preview-summary">
            <div>
                <h3 class="mb-1">Preview de carga masiva</h3>
                <div class="text-muted"><?= count($previewRows) ?> filas leidas, <?= $validCount ?> validas para importar.</div>
            </div>
            <div class="event-preview-actions">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" id="eventSelectAll">
                    <label class="form-check-label" for="eventSelectAll">Seleccionar todos</label>
                </div>
                <button type="submit" class="btn btn-success d-none" id="eventImportSubmit" disabled>
                    <i class="bi bi-check2-circle me-1"></i>Cargar eventos seleccionados
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0" id="eventPreviewTable">
                <thead>
                    <tr>
                        <th style="width:44px;"></th>
                        <th>Titulo</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Categoria</th>
                        <th>Ubicacion</th>
                        <th>Estado validacion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($previewRows as $index => $item): ?>
                        <?php $row = $item['row']; ?>
                        <tr>
                            <td>
                                <input class="form-check-input event-row-check" type="checkbox" name="eventos[]" value="<?= cms_e(eventos_encode_row($row)) ?>" <?= $item['valid'] ? '' : 'disabled' ?>>
                            </td>
                            <td><strong><?= cms_e($row['titulo'] ?? '') ?></strong></td>
                            <td><?= cms_e($row['fecha_inicio'] ?? '') ?></td>
                            <td><?= cms_e($row['hora_inicio'] ?? '') ?></td>
                            <td><?= cms_e($row['categoria'] ?? '') ?></td>
                            <td><?= cms_e($row['ubicacion'] ?? '') ?></td>
                            <td>
                                <ul class="event-status-list">
                                    <?php foreach ($item['messages'] as $message): ?>
                                        <li class="<?= $item['valid'] ? 'ok' : 'warn' ?>"><?= $item['valid'] ? 'Valido' : 'Atencion' ?>: <?= cms_e($message) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<?php
admin_render_layout_end([
    'extra_scripts' => <<<'HTML'
    <script src="assets/js/eventos_importacion.js"></script>
HTML,
]);
