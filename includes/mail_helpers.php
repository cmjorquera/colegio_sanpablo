<?php

require_once __DIR__ . '/cms_helpers.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function admin_mail_config(): array
{
    $configFile = __DIR__ . '/../config/mail.php';
    if (is_file($configFile)) {
        require_once $configFile;
    }

    return [
        'host' => defined('SMTP_HOST') ? (string) SMTP_HOST : '',
        'port' => defined('SMTP_PORT') ? (int) SMTP_PORT : 587,
        'user' => defined('SMTP_USER') ? (string) SMTP_USER : '',
        'pass' => defined('SMTP_PASS') ? (string) SMTP_PASS : '',
        'from' => defined('SMTP_FROM') ? (string) SMTP_FROM : '',
        'from_name' => defined('SMTP_FROM_NAME') ? (string) SMTP_FROM_NAME : 'Colegio San Pablo',
        'secure' => defined('SMTP_SECURE') ? (string) SMTP_SECURE : 'tls',
        'timeout' => defined('SMTP_TIMEOUT') ? (int) SMTP_TIMEOUT : 20,
    ];
}

function admin_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '/');
    $path = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if (str_ends_with($path, '/ajax')) {
        $path = rtrim(dirname($path), '/');
    }
    return $scheme . '://' . $host . ($path === '' ? '' : $path);
}

function admin_password_reset_columns_ready(mysqli $db): bool
{
    return cms_column_exists($db, 'usuario', 'reset_token') && cms_column_exists($db, 'usuario', 'reset_token_expira');
}

function admin_send_password_reset_email(string $email, string $nombre, string $linkCambioClave, array $institution): void
{
    $mailConfig = admin_mail_config();
    if ($mailConfig['host'] === '' || $mailConfig['user'] === '' || $mailConfig['pass'] === '') {
        throw new RuntimeException('La configuracion SMTP no esta completa en config/mail.php.');
    }

    require_once __DIR__ . '/../PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

    $fromEmail = filter_var($mailConfig['from'], FILTER_VALIDATE_EMAIL)
        ? $mailConfig['from']
        : ((filter_var((string) ($institution['email_soporte'] ?? ''), FILTER_VALIDATE_EMAIL)) ? (string) $institution['email_soporte'] : (string) ($institution['email'] ?? ''));
    if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Configura un correo remitente valido en config/mail.php.');
    }

    $fromName = trim($mailConfig['from_name']) !== '' ? $mailConfig['from_name'] : (string) ($institution['nombre'] ?? 'Colegio San Pablo');
    $templateFile = __DIR__ . '/../correos/enviarCredenciales.php';
    if (!is_file($templateFile)) {
        throw new RuntimeException('No existe la plantilla de correo correos/enviarCredenciales.php.');
    }
    $template = require $templateFile;
    if (!is_callable($template)) {
        throw new RuntimeException('La plantilla de correo enviarCredenciales.php no es valida.');
    }
    $message = $template([
        'nombre' => $nombre,
        'institucion' => (string) ($institution['nombre'] ?? 'Colegio San Pablo'),
        'link' => $linkCambioClave,
        'expira_texto' => '1 hora',
    ]);

    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = $mailConfig['host'];
        $mail->Port = $mailConfig['port'];
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig['user'];
        $mail->Password = $mailConfig['pass'];
        $mail->Timeout = $mailConfig['timeout'];
        if ($mailConfig['secure'] !== '') {
            $mail->SMTPSecure = $mailConfig['secure'];
        }
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($email, $nombre);
        $mail->isHTML(true);
        $mail->Subject = (string) ($message['subject'] ?? 'Crear nueva clave');
        $mail->Body = (string) ($message['html'] ?? '');
        $mail->AltBody = (string) ($message['text'] ?? '');
        $mail->send();
    } catch (PHPMailerException $exception) {
        throw new RuntimeException('No se pudo enviar el correo: ' . $exception->getMessage());
    }
}

function admin_create_password_reset_and_send(mysqli $db, int $idUsuario, int $sessionInstitutionId, bool $canManageAll, array $institution): void
{
    if (!admin_password_reset_columns_ready($db)) {
        throw new RuntimeException('Faltan las columnas reset_token y reset_token_expira en usuario.');
    }

    $scopeSql = $canManageAll ? '' : ' AND u.id_institucion = ?';
    $stmtUser = $db->prepare("
        SELECT u.id_usuario, u.nombre, u.apellido, u.email, i.nombre AS institucion_nombre, i.email AS institucion_email, i.email_soporte
        FROM usuario u
        LEFT JOIN institucion i ON i.id_institucion = u.id_institucion
        WHERE u.id_usuario = ?{$scopeSql}
        LIMIT 1
    ");
    if (!$stmtUser) {
        throw new RuntimeException('No se pudo preparar el envio de correo.');
    }
    if ($canManageAll) {
        $stmtUser->bind_param('i', $idUsuario);
    } else {
        $stmtUser->bind_param('ii', $idUsuario, $sessionInstitutionId);
    }
    $stmtUser->execute();
    $user = $stmtUser->get_result()?->fetch_assoc();
    $stmtUser->close();
    if (!$user || !filter_var((string) ($user['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('El usuario no tiene un correo valido.');
    }

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 3600);
    $stmtToken = $db->prepare('UPDATE usuario SET reset_token = ?, reset_token_expira = ? WHERE id_usuario = ?');
    if (!$stmtToken) {
        throw new RuntimeException('No se pudo guardar el token.');
    }
    $stmtToken->bind_param('ssi', $token, $expires, $idUsuario);
    $stmtToken->execute();
    $stmtToken->close();

    $link = admin_base_url() . '/cambiar_clave.php?token=' . urlencode($token);
    $nombreCompleto = trim((string) ($user['nombre'] ?? '') . ' ' . (string) ($user['apellido'] ?? ''));
    admin_send_password_reset_email((string) $user['email'], $nombreCompleto !== '' ? $nombreCompleto : 'Usuario', $link, $institution);
}
