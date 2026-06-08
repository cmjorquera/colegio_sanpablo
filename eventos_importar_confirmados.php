<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/eventos_excel_helpers.php';
require_once __DIR__ . '/includes/funciones_auditoria.php';

$idSeccion = (int) ($_POST['id_seccion'] ?? 0);
$redirect = 'editar_contenedor.php?id=' . $idSeccion . '&tab=items';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Metodo no permitido.');
    }

    $selectedRows = $_POST['eventos'] ?? [];
    if (!is_array($selectedRows) || count($selectedRows) === 0) {
        throw new RuntimeException('Debes seleccionar al menos un evento valido.');
    }

    $db = cms_get_connection();
    $created = 0;
    $duplicated = 0;
    $errors = 0;

    foreach ($selectedRows as $encodedRow) {
        try {
            $row = eventos_decode_row((string) $encodedRow);
            $payload = cms_normalize_event_payload($row);
            if (cms_event_duplicate_exists($db, $payload)) {
                $duplicated++;
                continue;
            }
            $newEventId = cms_save_event($db, $payload);
            $datosDespues = obtenerRegistroAuditoria($db, 'eventos', 'id_evento', $newEventId);
            registrarAuditoria($db, 'Eventos del calendario', 'eventos', $newEventId, 'importar', 'Se importó un evento desde carga masiva confirmada', null, $datosDespues);
            $created++;
        } catch (Throwable $exception) {
            $errors++;
        }
    }

    registrarAuditoria($db, 'Eventos del calendario', 'eventos', null, 'importar', 'Importación masiva confirmada', null, [
        'creados' => $created,
        'duplicados' => $duplicated,
        'errores' => $errors,
        'seleccionados' => count($selectedRows),
    ]);

    cms_set_flash('success', "Importacion confirmada: {$created} creados, {$duplicated} duplicados, {$errors} errores.");
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
}

cms_redirect($redirect);
