<?php
/**
 * api/menus.php — CRUD de la tabla `menus` vía AJAX.
 * Acciones: list | save | delete | toggle
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// Protección básica: solo admins logueados
if (empty($_SESSION['admin_logged'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../class/conexion.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $db = (new Conexion())->getConexion();

    // ──────────────────────────────────────────
    // LIST — devuelve todos los menús ordenados
    // ──────────────────────────────────────────
    if ($action === 'list') {
        $res  = $db->query("SELECT * FROM menus ORDER BY orden ASC");
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['ok' => true, 'data' => $rows]);
        exit;
    }

    // ──────────────────────────────────────────
    // SAVE — inserta o actualiza un menú
    // ──────────────────────────────────────────
    if ($action === 'save') {
        $id     = (int)($_POST['id_menu'] ?? 0);
        $nombre = trim($_POST['nombre']   ?? '');
        $url    = trim($_POST['url']      ?? '');
        $icono  = trim($_POST['icono']    ?? '');
        $orden  = (int)($_POST['orden']   ?? 0);
        $estado = ($_POST['estado'] ?? '1') == '1' ? 1 : 0;

        if ($nombre === '') {
            echo json_encode(['ok' => false, 'msg' => 'El nombre es obligatorio']);
            exit;
        }

        if ($id > 0) {
            // UPDATE
            $stmt = $db->prepare(
                "UPDATE menus SET nombre=?, url=?, icono=?, orden=?, estado=? WHERE id_menu=?"
            );
            $stmt->bind_param('sssiii', $nombre, $url, $icono, $orden, $estado, $id);
        } else {
            // INSERT
            $stmt = $db->prepare(
                "INSERT INTO menus (nombre, url, icono, orden, estado) VALUES (?,?,?,?,?)"
            );
            $stmt->bind_param('sssii', $nombre, $url, $icono, $orden, $estado);
        }

        $stmt->execute();
        $newId = ($id > 0) ? $id : $db->insert_id;
        $stmt->close();

        echo json_encode(['ok' => true, 'id_menu' => $newId,
                          'msg' => $id > 0 ? 'Menú actualizado' : 'Menú creado']);
        exit;
    }

    // ──────────────────────────────────────────
    // DELETE — elimina un menú por id
    // ──────────────────────────────────────────
    if ($action === 'delete') {
        $id = (int)($_POST['id_menu'] ?? 0);
        if ($id < 1) {
            echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
            exit;
        }
        $stmt = $db->prepare("DELETE FROM menus WHERE id_menu = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['ok' => true, 'msg' => 'Menú eliminado']);
        exit;
    }

    // ──────────────────────────────────────────
    // TOGGLE — cambia estado 0↔1
    // ──────────────────────────────────────────
    if ($action === 'toggle') {
        $id = (int)($_POST['id_menu'] ?? 0);
        if ($id < 1) {
            echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
            exit;
        }
        $stmt = $db->prepare(
            "UPDATE menus SET estado = IF(estado=1,0,1) WHERE id_menu = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        // Devolver el nuevo estado
        $res    = $db->query("SELECT estado FROM menus WHERE id_menu = $id");
        $row    = $res ? $res->fetch_assoc() : ['estado' => 0];
        echo json_encode(['ok' => true, 'estado' => (int)$row['estado']]);
        exit;
    }

    echo json_encode(['ok' => false, 'msg' => 'Acción desconocida']);

} catch (RuntimeException $e) {
    error_log('api/menus.php: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Error del servidor']);
}
