<?php
/**
 * Verifica credenciales de administrador via AJAX.
 *
 * Tabla real del sistema: usuario
 * Campos usados: id_usuario, id_institucion, nombre, apellido, usuario, email,
 * clave, rol, estado.
 *
 * Respuesta JSON:
 * { ok: bool, msg?: string, redirect?: string }
 */
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Metodo no permitido']);
    exit;
}

require_once __DIR__ . '/class/conexion.php';

$usuario = trim((string) ($_POST['usuario'] ?? ''));
$clave = trim((string) ($_POST['clave'] ?? ''));

if ($usuario === '' || $clave === '') {
    echo json_encode(['ok' => false, 'msg' => 'Completa usuario y clave']);
    exit;
}

try {
    $db = (new Conexion())->getConexion();

    $stmt = $db->prepare(
        "SELECT id_usuario, id_institucion, nombre, apellido, email, usuario, clave, rol, estado
           FROM usuario
          WHERE usuario = ? OR email = ?
          LIMIT 1"
    );

    if (!$stmt) {
        throw new RuntimeException('No fue posible preparar la consulta de login.');
    }

    $stmt->bind_param('ss', $usuario, $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user) {
        echo json_encode(['ok' => false, 'msg' => 'Usuario o clave incorrectos']);
        exit;
    }

    if (strtolower((string) ($user['estado'] ?? '')) !== 'activo') {
        echo json_encode(['ok' => false, 'msg' => 'Tu cuenta no esta activa. Contacta al administrador.']);
        exit;
    }

    $claveDb = (string) ($user['clave'] ?? '');
    $valid = false;

    if (password_get_info($claveDb)['algo']) {
        $valid = password_verify($clave, $claveDb);
    } elseif (strlen($claveDb) === 32 && ctype_xdigit($claveDb)) {
        $valid = md5($clave) === strtolower($claveDb);
    } else {
        $valid = hash_equals($claveDb, $clave);
    }

    if (!$valid) {
        echo json_encode(['ok' => false, 'msg' => 'Usuario o clave incorrectos']);
        exit;
    }

    $_SESSION['admin_logged'] = true;
    $_SESSION['admin_id'] = (int) $user['id_usuario'];
    $_SESSION['id_usuario'] = (int) $user['id_usuario'];
    $_SESSION['id_institucion'] = (int) $user['id_institucion'];
    $_SESSION['admin_usuario'] = (string) ($user['usuario'] ?: $user['email']);
    $_SESSION['admin_email'] = (string) ($user['email'] ?? '');
    $_SESSION['admin_nombre'] = trim((string) ($user['nombre'] ?? '') . ' ' . (string) ($user['apellido'] ?? ''));
    $_SESSION['admin_rol'] = (string) ($user['rol'] ?? '');

    echo json_encode(['ok' => true, 'redirect' => 'admin_1.php']);
} catch (Throwable $exception) {
    error_log('login_check.php: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error del servidor, intenta nuevamente']);
}
