<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_logged'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Sesion expirada.']);
    exit;
}

require_once __DIR__ . '/../includes/cms_helpers.php';
require_once __DIR__ . '/../includes/admin_permissions.php';
require_once __DIR__ . '/../includes/mail_helpers.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Metodo no permitido.');
    }

    admin_requerir_permiso_submenu('usuarios', 'editar');

    $db = cms_get_connection();
    $site = cms_get_site_data($db);
    $adminRole = (string) ($_SESSION['admin_rol'] ?? '');
    $canManageAll = $adminRole === 'super_admin' || admin_es_super_admin();
    $sessionInstitutionId = (int) ($_SESSION['id_institucion'] ?? 1);
    $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
    if ($idUsuario <= 0) {
        throw new RuntimeException('No se pudo identificar el usuario.');
    }

    admin_create_password_reset_and_send($db, $idUsuario, $sessionInstitutionId, $canManageAll, $site['institution'] ?? []);
    echo json_encode(['ok' => true, 'message' => 'Se envio el correo de cambio de clave.']);
} catch (Throwable $exception) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => $exception->getMessage()]);
}
