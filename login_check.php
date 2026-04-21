<?php
/**
 * login_check.php - Verifica credenciales de administrador via AJAX (POST).
 * Usa email + clave y mantiene compatibilidad con tablas usuario/usuarios.
 * Responde JSON: { ok: bool, msg: string, redirect?: string }
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// Solo acepta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/class/conexion.php';

$email = trim($_POST['email'] ?? $_POST['usuario'] ?? '');
$clave = trim($_POST['clave'] ?? '');

if ($email === '' || $clave === '') {
    echo json_encode(['ok' => false, 'msg' => 'Completa email y clave']);
    exit;
}

try {
    $db = (new Conexion())->getConexion();

    $user = null;
    $tableCandidates = ['usuario', 'usuarios'];

    foreach ($tableCandidates as $tableName) {
        $sql = "SELECT id_usuario, nombre, apellido, email, usuario, clave, estado
                  FROM {$tableName}
                 WHERE email = ?
                 LIMIT 1";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            continue;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($user) {
            break;
        }
    }

    if (!$user) {
        echo json_encode(['ok' => false, 'msg' => 'Email o clave incorrectos']);
        exit;
    }

    if (strtolower($user['estado']) !== 'activo') {
        echo json_encode(['ok' => false, 'msg' => 'Tu cuenta no está activa. Contacta al administrador.']);
        exit;
    }

    $claveDB = $user['clave'];
    $valid = false;

    if (password_get_info($claveDB)['algo']) {
        $valid = password_verify($clave, $claveDB);
    } elseif (strlen($claveDB) === 32 && ctype_xdigit($claveDB)) {
        $valid = (md5($clave) === $claveDB);
    } else {
        $valid = ($clave === $claveDB);
    }

    if (!$valid) {
        echo json_encode(['ok' => false, 'msg' => 'Email o clave incorrectos']);
        exit;
    }

    $_SESSION['admin_logged'] = true;
    $_SESSION['admin_id'] = $user['id_usuario'];
    $_SESSION['admin_usuario'] = $user['usuario'] ?? $user['email'];
    $_SESSION['admin_email'] = $user['email'] ?? $email;
    $_SESSION['admin_nombre'] = trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? ''));

    echo json_encode(['ok' => true, 'redirect' => 'admin.php']);

} catch (RuntimeException $e) {
    error_log('login_check.php error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Error del servidor, intenta nuevamente']);
}
