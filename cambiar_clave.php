<?php
session_start();

require_once __DIR__ . '/includes/cms_helpers.php';

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return cms_e($value);
    }
}

$db = cms_get_connection();
$site = cms_get_site_data($db);
$institution = $site['institution'];
$loginRedirect = 'index.php';
$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$message = '';
$messageType = 'danger';
$validUser = null;
$columnsReady = cms_column_exists($db, 'usuario', 'reset_token') && cms_column_exists($db, 'usuario', 'reset_token_expira');

try {
    if (!$columnsReady) {
        throw new RuntimeException('El cambio de clave por token no esta disponible.');
    }
    if ($token === '' || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
        throw new RuntimeException('El enlace no es valido.');
    }

    $stmt = $db->prepare("
        SELECT id_usuario, nombre, apellido, email
        FROM usuario
        WHERE reset_token = ?
          AND reset_token_expira > NOW()
        LIMIT 1
    ");
    if (!$stmt) {
        throw new RuntimeException('No se pudo validar el enlace.');
    }
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $validUser = $stmt->get_result()?->fetch_assoc();
    $stmt->close();

    if (!$validUser) {
        throw new RuntimeException('El enlace expiro o ya fue utilizado.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $clave = (string) ($_POST['clave'] ?? '');
        $claveConfirm = (string) ($_POST['clave_confirm'] ?? '');
        if (strlen($clave) < 8) {
            throw new RuntimeException('La nueva clave debe tener al menos 8 caracteres.');
        }
        if ($clave !== $claveConfirm) {
            throw new RuntimeException('Las claves no coinciden.');
        }

        $hash = password_hash($clave, PASSWORD_DEFAULT);
        $idUsuario = (int) $validUser['id_usuario'];
        $stmtUpdate = $db->prepare("
            UPDATE usuario
            SET clave = ?,
                reset_token = NULL,
                reset_token_expira = NULL
            WHERE id_usuario = ?
        ");
        if (!$stmtUpdate) {
            throw new RuntimeException('No se pudo actualizar la clave.');
        }
        $stmtUpdate->bind_param('si', $hash, $idUsuario);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        $message = 'Tu clave fue actualizada correctamente. Ya puedes iniciar sesion.';
        $messageType = 'success';
        $validUser = null;
    }
} catch (Throwable $exception) {
    $message = $exception->getMessage();
    $messageType = 'danger';
}

$primary = $institution['color_primario'] ?? '#F0A000';
$secondary = $institution['color_secundario'] ?? '#EF6C00';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar clave | <?= e($institution['nombre'] ?? 'Colegio San Pablo') ?></title>
    <link rel="shortcut icon" href="<?= e($institution['favicon'] ?? 'assets/images/icono_ppt.png') ?>">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <style>
        :root{--cc-primary:<?= e($primary) ?>;--cc-secondary:<?= e($secondary) ?>}
        body{min-height:100vh;margin:0;display:grid;place-items:center;background:#f3f6fa;font-family:"Poppins",system-ui,-apple-system,sans-serif;color:#0f172a;padding:20px}
        .cc-card{width:min(100%,460px);background:#fff;border:1px solid #e2e8f0;border-radius:18px;box-shadow:0 20px 60px rgba(15,23,42,.12);overflow:hidden}
        .cc-head{padding:28px 28px 20px;border-bottom:1px solid #e2e8f0}
        .cc-head h1{font-size:1.55rem;font-weight:900;margin:0}
        .cc-head p{margin:6px 0 0;color:#64748b}
        .cc-body{padding:24px 28px 28px}
        .btn-cc{background:linear-gradient(90deg,var(--cc-primary),var(--cc-secondary));color:#fff;border:0;font-weight:800}
        .btn-cc:hover{color:#fff;filter:brightness(1.04)}
    </style>
</head>
<body>
    <main class="cc-card">
        <div class="cc-head">
            <h1>Cambiar clave</h1>
            <p><?= e($institution['nombre'] ?? 'Colegio San Pablo') ?></p>
        </div>
        <div class="cc-body">
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?= e($messageType) ?>"><?= e($message) ?></div>
            <?php endif; ?>

            <?php if ($validUser): ?>
                <form method="post">
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="clave">Nueva clave</label>
                        <input class="form-control" id="clave" name="clave" type="password" minlength="8" required autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="clave_confirm">Confirmar nueva clave</label>
                        <input class="form-control" id="clave_confirm" name="clave_confirm" type="password" minlength="8" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-cc w-100" type="submit">Guardar nueva clave</button>
                </form>
            <?php else: ?>
                <a class="btn btn-cc w-100" href="<?= e($loginRedirect) ?>">Ir al inicio de sesion</a>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
