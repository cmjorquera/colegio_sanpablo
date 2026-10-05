<?php

return static function (array $data): array {
    $nombre = trim((string) ($data['nombre'] ?? 'Usuario'));
    $institucion = trim((string) ($data['institucion'] ?? 'Colegio San Pablo'));
    $link = trim((string) ($data['link'] ?? ''));
    $expiresText = trim((string) ($data['expira_texto'] ?? '1 hora'));

    $safeNombre = htmlspecialchars($nombre !== '' ? $nombre : 'Usuario', ENT_QUOTES, 'UTF-8');
    $safeInstitucion = htmlspecialchars($institucion !== '' ? $institucion : 'Colegio San Pablo', ENT_QUOTES, 'UTF-8');
    $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    $safeExpires = htmlspecialchars($expiresText, ENT_QUOTES, 'UTF-8');

    $subject = 'Crear nueva clave - ' . ($institucion !== '' ? $institucion : 'Colegio San Pablo');

    $html = <<<HTML
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{$subject}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#172033;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6fb;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #dde4ee;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 28px;border-bottom:1px solid #edf1f7;">
                            <div style="font-size:13px;color:#667085;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">{$safeInstitucion}</div>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.25;color:#172033;">Crear nueva clave</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:26px 28px;">
                            <p style="margin:0 0 14px;font-size:15px;line-height:1.55;">Hola {$safeNombre},</p>
                            <p style="margin:0 0 18px;font-size:15px;line-height:1.55;">Se generó un enlace para crear una nueva clave de acceso al panel del CMS.</p>
                            <p style="margin:0 0 22px;font-size:15px;line-height:1.55;">Este enlace estará disponible por {$safeExpires}.</p>
                            <p style="margin:0 0 24px;">
                                <a href="{$safeLink}" style="display:inline-block;background:#26384f;color:#ffffff;text-decoration:none;font-weight:700;border-radius:8px;padding:12px 18px;">Crear nueva clave</a>
                            </p>
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.5;color:#667085;">Si el botón no funciona, copia y pega este enlace en el navegador:</p>
                            <p style="margin:0;font-size:13px;line-height:1.5;word-break:break-all;color:#344054;">{$safeLink}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px;background:#f8fafc;border-top:1px solid #edf1f7;color:#667085;font-size:12px;line-height:1.5;">
                            Si no solicitaste este cambio, puedes ignorar este mensaje.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    $text = "Hola {$nombre},\n\n"
        . "Se genero un enlace para crear una nueva clave de acceso al panel del CMS de {$institucion}.\n\n"
        . "Crear nueva clave:\n{$link}\n\n"
        . "Este enlace estara disponible por {$expiresText}.\n\n"
        . "Si no solicitaste este cambio, puedes ignorar este mensaje.";

    return [
        'subject' => $subject,
        'html' => $html,
        'text' => $text,
    ];
};
