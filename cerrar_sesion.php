<?php
/** Cierre de sesión administrativo: invalida los datos y la cookie de sesión. */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método no permitido');
}
session_start();
$token = (string) ($_POST['csrf_logout'] ?? '');
if ($token === '' || !hash_equals((string) ($_SESSION['csrf_logout'] ?? ''), $token)) {
    http_response_code(403);
    exit('Solicitud de cierre de sesión no válida');
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}
session_destroy();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Location: index.php?login=1', true, 303);
exit;
