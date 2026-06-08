<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/eventos_excel_helpers.php';
require_once __DIR__ . '/includes/funciones_auditoria.php';

try {
    $db = cms_get_connection();
    $hasZip = class_exists('ZipArchive');
    $file = $hasZip ? eventos_generate_template_xlsx($db) : eventos_generate_template_xls_xml($db);
    registrarAuditoria($db, 'Eventos del calendario', 'eventos', null, 'descargar', 'Se descargó la plantilla de carga masiva de eventos', null, [
        'archivo' => 'plantilla_eventos_calendario.' . ($hasZip ? 'xlsx' : 'xls'),
    ]);

    header('Content-Type: ' . ($hasZip ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/vnd.ms-excel'));
    header('Content-Disposition: attachment; filename="plantilla_eventos_calendario.' . ($hasZip ? 'xlsx' : 'xls') . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    unlink($file);
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
    cms_redirect('editar_contenedor.php?id=' . (int) ($_GET['id'] ?? 0) . '&tab=items');
}
