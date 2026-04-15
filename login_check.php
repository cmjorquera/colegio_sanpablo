<?php
/**
 * login_check.php — Verifica credenciales de administrador vía AJAX (POST).
 * Tabla: usuarios | Columnas: usuario, clave, estado
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

$usuario = trim($_POST['usuario'] ?? '');
$clave   = trim($_POST['clave']   ?? '');

// Validar que vengan ambos campos
if ($usuario === '' || $clave === '') {
    echo json_encode(['ok' => false, 'msg' => 'Completa usuario y clave']);
    exit;
}

try {
    $db = (new Conexion())->getConexion();

    // Buscar usuario activo por columna "usuario"
    $stmt = $db->prepare(
        "SELECT id_usuario, nombre, apellido, usuario, clave, estado
           FROM usuarios
          WHERE usuario = ?
          LIMIT 1"
    );
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();

    // Usuario no encontrado
    if (!$user) {
        echo json_encode(['ok' => false, 'msg' => 'Usuario o clave incorrectos']);
        exit;
    }

    // Verificar que esté activo
    if (strtolower($user['estado']) !== 'activo') {
        echo json_encode(['ok' => false, 'msg' => 'Tu cuenta no está activa. Contacta al administrador.']);
        exit;
    }

    // Verificar clave: soporta password_hash(), MD5 y texto plano
    $claveDB = $user['clave'];
    $valid   = false;

    if (password_get_info($claveDB)['algo']) {
        // Hasheada con password_hash()
        $valid = password_verify($clave, $claveDB);
    } elseif (strlen($claveDB) === 32 && ctype_xdigit($claveDB)) {
        // MD5
        $valid = (md5($clave) === $claveDB);
    } else {
        // Texto plano
        $valid = ($clave === $claveDB);
    }

    if (!$valid) {
        echo json_encode(['ok' => false, 'msg' => 'Usuario o clave incorrectos']);
        exit;
    }

    // ── Login exitoso → guardar sesión ──
    $_SESSION['admin_logged']   = true;
    $_SESSION['admin_id']       = $user['id_usuario'];
    $_SESSION['admin_usuario']  = $user['usuario'];
    $_SESSION['admin_nombre']   = trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? ''));

    echo json_encode(['ok' => true, 'redirect' => 'admin.php']);

} catch (RuntimeException $e) {
    error_log('login_check.php error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Error del servidor, intenta nuevamente']);
}
