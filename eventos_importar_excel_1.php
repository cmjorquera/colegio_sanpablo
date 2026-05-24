<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index_1.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';

$idSeccion = (int) ($_POST['id_seccion'] ?? $_GET['id'] ?? 0);
$redirect = 'editar_contenedor.php?id=' . $idSeccion . '&tab=items';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Método no permitido.');
    }
    if (empty($_FILES['archivo_eventos']['tmp_name'])) {
        throw new RuntimeException('Debes seleccionar un archivo CSV.');
    }

    $db = cms_get_connection();
    $handle = fopen($_FILES['archivo_eventos']['tmp_name'], 'r');
    if (!$handle) {
        throw new RuntimeException('No fue posible leer el archivo.');
    }

    $headers = fgetcsv($handle, 0, ';');
    if (!$headers) {
        throw new RuntimeException('El archivo no tiene encabezados.');
    }

    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
    $headers = array_map(static fn($value) => trim((string) $value), $headers);
    $created = 0;
    $duplicated = 0;
    $errors = 0;

    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        if (!array_filter($row, static fn($value) => trim((string) $value) !== '')) {
            continue;
        }
        $data = [];
        foreach ($headers as $index => $header) {
            $data[$header] = $row[$index] ?? '';
        }
        try {
            $payload = cms_normalize_event_payload($data);
            if (cms_event_duplicate_exists($db, $payload)) {
                $duplicated++;
                continue;
            }
            cms_save_event($db, $data);
            $created++;
        } catch (Throwable $exception) {
            $errors++;
        }
    }
    fclose($handle);
    cms_set_flash('success', "Importación finalizada: {$created} creados, {$duplicated} duplicados, {$errors} errores.");
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
}

cms_redirect($redirect);
