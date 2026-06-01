<?php
session_start();

header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['admin_logged'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Sesion admin no valida.']);
    exit;
}

require_once __DIR__ . '/../includes/cms_helpers.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Metodo no permitido.']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw ?: '', true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $items = $payload['items'] ?? [];
    if ($items === [] && isset($payload['id_seccion'], $payload['orden']) && is_array($payload['id_seccion']) && is_array($payload['orden'])) {
        $items = [];
        foreach ($payload['id_seccion'] as $index => $idSeccion) {
            $items[] = [
                'id_seccion' => $idSeccion,
                'orden' => $payload['orden'][$index] ?? 0,
            ];
        }
    }
    if (!is_array($items) || $items === []) {
        throw new RuntimeException('No se recibio un orden valido.');
    }

    $normalized = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $idSeccion = (int) ($item['id_seccion'] ?? 0);
        $orden = (int) ($item['orden'] ?? 0);
        if ($idSeccion <= 0 || $orden <= 0) {
            continue;
        }
        $normalized[$idSeccion] = $orden;
    }

    if ($normalized === []) {
        throw new RuntimeException('No se recibieron contenedores movibles validos.');
    }

    $db = cms_get_connection();
    $institutionId = cms_get_institution_id($db);
    cms_sync_sections($db, $institutionId);

    $ids = array_keys($normalized);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $sql = "SELECT id_seccion, nombre_interno FROM seccion WHERE id_institucion = ? AND id_seccion IN ($placeholders)";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('No se pudo validar el orden.');
    }

    $bindTypes = 'i' . $types;
    $values = array_merge([$institutionId], $ids);
    $refs = [];
    foreach ($values as $key => $value) {
        $refs[$key] = &$values[$key];
    }
    $stmt->bind_param($bindTypes, ...$refs);
    $stmt->execute();
    $result = $stmt->get_result();
    $sections = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    if (count($sections) !== count($normalized)) {
        throw new RuntimeException('Uno o mas contenedores no pertenecen a la institucion activa.');
    }

    foreach ($sections as $section) {
        $name = (string) ($section['nombre_interno'] ?? '');
        if (cms_section_is_fixed($name)) {
            throw new RuntimeException('No se puede mover el contenedor fijo ' . $name . '.');
        }
        if (!cms_section_is_movable($name)) {
            throw new RuntimeException('El contenedor ' . $name . ' no esta habilitado para ordenamiento.');
        }
    }

    $db->begin_transaction();
    $updateStmt = $db->prepare('UPDATE seccion SET orden = ? WHERE id_seccion = ?');
    if (!$updateStmt) {
        throw new RuntimeException('No se pudo preparar la actualizacion de orden.');
    }

    foreach ($normalized as $idSeccion => $orden) {
        $updateStmt->bind_param('ii', $orden, $idSeccion);
        $updateStmt->execute();
    }
    $updateStmt->close();
    $db->commit();

    echo json_encode(['ok' => true, 'total' => count($normalized)]);
} catch (Throwable $e) {
    if (isset($db) && $db instanceof mysqli) {
        $db->rollback();
    }
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
