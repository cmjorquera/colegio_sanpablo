<?php

// Politica global de archivos multimedia. Ver logica/07_politica_archivos_multimedia.txt.
// Punto unico de subida y validacion de archivos fisicos del CMS. Las rutas devueltas
// son siempre relativas ("uploads/..."), listas para guardarse en la base de datos.

define('CMS_UPLOAD_MAX_IMAGEN_BYTES', 8 * 1024 * 1024);
define('CMS_UPLOAD_MAX_VIDEO_BYTES', 150 * 1024 * 1024);
define('CMS_UPLOAD_MAX_ADJUNTO_BYTES', 20 * 1024 * 1024);

define('CMS_UPLOAD_EXTENSIONES_PROHIBIDAS', [
    'php', 'php2', 'php3', 'php4', 'php5', 'php7', 'phtml', 'pht', 'phar',
    'cgi', 'pl', 'sh', 'bash', 'exe', 'bat', 'cmd', 'com', 'msi',
    'asp', 'aspx', 'jsp', 'jspx', 'py', 'rb', 'dll', 'so', 'vbs', 'js', 'jar', 'htaccess',
]);

/** Safe, actionable messages; logs contain codes and limits, never paths or file names. */
function cms_upload_fail(string $reason, string $message, int $phpError = 0): void
{
    error_log('CMS upload: reason=' . $reason . ' php_error=' . $phpError
        . ' upload_max_filesize=' . ini_get('upload_max_filesize')
        . ' post_max_size=' . ini_get('post_max_size') . ' sapi=' . PHP_SAPI);
    throw new RuntimeException($message);
}

function cms_upload_check_php_error(array $file): void
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_OK) { return; }
    $messages = [
        UPLOAD_ERR_INI_SIZE => 'El archivo supera el limite de subida de PHP (' . ini_get('upload_max_filesize') . ').',
        UPLOAD_ERR_FORM_SIZE => 'El archivo supera el limite permitido por el formulario.',
        UPLOAD_ERR_PARTIAL => 'El archivo llego incompleto. Vuelve a subirlo.',
        UPLOAD_ERR_NO_FILE => 'No se recibio el archivo.',
        UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene disponible la carpeta temporal de subida.',
        UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo temporal.',
        UPLOAD_ERR_EXTENSION => 'Una extension de PHP bloqueo la subida.',
    ];
    cms_upload_fail('php_upload', $messages[$error] ?? 'PHP no pudo recibir el archivo.', $error);
}

function cms_upload_ini_bytes(string $value): int
{
    $value = trim($value);
    $unit = strtolower(substr($value, -1));
    return (int) ((float) $value * (['k' => 1024, 'm' => 1048576, 'g' => 1073741824][$unit] ?? 1));
}

function cms_upload_check_request_size(): void
{
    $limit = cms_upload_ini_bytes((string) ini_get('post_max_size'));
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $limit > 0
        && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $limit) {
        cms_upload_fail('post_max_size', 'La solicitud supera el limite total de PHP (' . ini_get('post_max_size') . '). No se guardaron cambios.');
    }
}

function cms_upload_base_path(): string
{
    return dirname(__DIR__) . '/uploads';
}

function cms_upload_base_url(): string
{
    return 'uploads';
}

function cms_es_ruta_local_upload(?string $ruta): bool
{
    $ruta = trim((string) $ruta);
    if ($ruta === '') {
        return false;
    }

    $ruta = str_replace('\\', '/', $ruta);
    if (str_contains($ruta, '..')) {
        return false;
    }

    if (preg_match('~^[a-z][a-z0-9+.-]*://~i', $ruta) || str_starts_with($ruta, '//')) {
        return false;
    }

    return str_starts_with($ruta, 'uploads/');
}

function cms_generar_nombre_seguro(string $nombreOriginal): string
{
    $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $base = strtolower(pathinfo($nombreOriginal, PATHINFO_FILENAME));
    $base = preg_replace('/[^a-z0-9]+/', '-', $base);
    $base = trim((string) $base, '-');
    $base = $base !== '' ? $base : 'archivo';

    return $base . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . ($ext !== '' ? '.' . $ext : '');
}

function cms_extension_prohibida(string $extension): bool
{
    return in_array(strtolower($extension), CMS_UPLOAD_EXTENSIONES_PROHIBIDAS, true);
}

// Bloquea trucos de doble extension como "foto.php.jpg": cada segmento del nombre
// original, no solo el ultimo, se revisa contra la lista negra.
function cms_validar_nombre_archivo(string $nombreOriginal): void
{
    $segmentos = explode('.', strtolower($nombreOriginal));
    if (count($segmentos) <= 1) {
        return;
    }

    array_shift($segmentos);
    foreach ($segmentos as $segmento) {
        if (cms_extension_prohibida($segmento)) {
            throw new RuntimeException('El nombre del archivo contiene una extension no permitida.');
        }
    }
}

function cms_validar_imagen(array $file): void
{
    cms_upload_check_php_error($file);

    $nombre = (string) ($file['name'] ?? '');
    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    cms_validar_nombre_archivo($nombre);

    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'];
    if (!in_array($ext, $extensionesPermitidas, true)) {
        cms_upload_fail('image_extension', 'Formato de imagen no permitido.');
    }

    if ($size > CMS_UPLOAD_MAX_IMAGEN_BYTES) {
        cms_upload_fail('image_size', 'La imagen supera el tamano maximo permitido (8 MB).');
    }

    if (!function_exists('mime_content_type')) {
        cms_upload_fail('fileinfo_missing', 'El servidor necesita habilitar Fileinfo para validar las subidas.');
    }
    $mime = $tmpPath !== '' ? (string) (mime_content_type($tmpPath) ?: '') : '';
    $mimesPermitidos = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon',
    ];
    if (!in_array($mime, $mimesPermitidos, true)) {
        cms_upload_fail('image_mime', 'El contenido real del archivo no corresponde a una imagen permitida.');
    }
}

function cms_validar_video(array $file): void
{
    cms_upload_check_php_error($file);

    $nombre = (string) ($file['name'] ?? '');
    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    cms_validar_nombre_archivo($nombre);

    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    $extensionesPermitidas = ['mp4', 'webm', 'mov', 'm4v'];
    if (!in_array($ext, $extensionesPermitidas, true)) {
        cms_upload_fail('video_extension', 'Formato de video no permitido.');
    }

    if ($size > CMS_UPLOAD_MAX_VIDEO_BYTES) {
        cms_upload_fail('video_size', 'El video supera el tamano maximo permitido.');
    }

    if (!function_exists('mime_content_type')) {
        cms_upload_fail('fileinfo_missing', 'El servidor necesita habilitar Fileinfo para validar las subidas.');
    }
    $mime = $tmpPath !== '' ? (string) (mime_content_type($tmpPath) ?: '') : '';
    $mimesPorExtension = [
        'mp4' => ['video/mp4', 'application/mp4'],
        'm4v' => ['video/mp4', 'video/x-m4v'],
        'webm' => ['video/webm'],
        'mov' => ['video/quicktime'],
    ];
    if ($mime === '' || !in_array($mime, $mimesPorExtension[$ext] ?? [], true)) {
        cms_upload_fail('video_mime', 'El tipo MIME real no corresponde con la extension del video.');
    }
}

function cms_validar_adjunto(array $file, array $extensionesPermitidas): void
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No fue posible subir el archivo adjunto.');
    }

    $nombre = (string) ($file['name'] ?? '');
    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    cms_validar_nombre_archivo($nombre);

    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    $extensionesPermitidas = array_map('strtolower', $extensionesPermitidas);
    if (!in_array($ext, $extensionesPermitidas, true) || cms_extension_prohibida($ext)) {
        throw new RuntimeException('Tipo de archivo adjunto no permitido.');
    }

    if ($size > CMS_UPLOAD_MAX_ADJUNTO_BYTES) {
        throw new RuntimeException('El archivo adjunto supera el tamano maximo permitido.');
    }

    // La lista de extensiones permitidas para adjuntos mezcla PDF, Office e imagenes,
    // por lo que no se exige un unico MIME: solo se bloquea contenido claramente ejecutable.
    $mime = $tmpPath !== '' ? (string) (mime_content_type($tmpPath) ?: '') : '';
    $mimesProhibidos = ['text/x-php', 'application/x-httpd-php', 'application/x-sh', 'application/x-executable'];
    if ($mime !== '' && (in_array($mime, $mimesProhibidos, true) || str_contains($mime, 'php'))) {
        throw new RuntimeException('El contenido real del archivo no coincide con un adjunto permitido.');
    }
}

// Crea (si no existe) la carpeta fisica de un contenedor y devuelve su ruta relativa
// dentro de uploads/, por ejemplo "secciones/hero_principal". Quien llama es responsable
// de haber validado que nombreInterno corresponde a un registro real de seccion.
function cms_crear_directorio_contenedor(string $nombreInterno): string
{
    $nombreInterno = preg_replace('/[^a-z0-9_-]+/i', '-', trim($nombreInterno));
    $nombreInterno = trim((string) $nombreInterno, '-');
    if ($nombreInterno === '') {
        throw new RuntimeException('nombre_interno invalido para crear carpeta de subida.');
    }

    $relativo = 'secciones/' . $nombreInterno;
    $absoluto = cms_upload_base_path() . '/' . $relativo;
    if (!is_dir($absoluto) && !mkdir($absoluto, 0755, true) && !is_dir($absoluto)) {
        throw new RuntimeException('No fue posible crear la carpeta del contenedor.');
    }

    return $relativo;
}

// Punto central de subida. $categoria es 'imagenes', 'videos' o 'adjuntos'; para
// 'adjuntos' se debe pasar $extensionesPermitidas. Devuelve la ruta relativa
// "uploads/..." lista para guardar en la base de datos, o null si no se envio archivo.
function cms_guardar_archivo(array $file, string $carpetaRelativa, string $categoria, array $extensionesPermitidas = []): ?string
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    cms_upload_check_php_error($file);
    if (!is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
        cms_upload_fail('not_uploaded_file', 'No se recibio un archivo de subida valido.');
    }

    switch ($categoria) {
        case 'imagenes':
            cms_validar_imagen($file);
            break;
        case 'videos':
            cms_validar_video($file);
            break;
        case 'adjuntos':
            cms_validar_adjunto($file, $extensionesPermitidas);
            break;
        default:
            throw new RuntimeException('Categoria de archivo no reconocida: ' . $categoria);
    }

    $carpetaRelativa = trim(str_replace('\\', '/', $carpetaRelativa), '/');
    if ($carpetaRelativa === '' || str_contains($carpetaRelativa, '..')
        || !preg_match('~^[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*$~', $carpetaRelativa)) {
        throw new RuntimeException('Carpeta de destino invalida para la subida.');
    }

    $directorioAbsoluto = cms_upload_base_path() . '/' . $carpetaRelativa;
    if (!is_dir($directorioAbsoluto) && !@mkdir($directorioAbsoluto, 0755, true) && !is_dir($directorioAbsoluto)) {
        cms_upload_fail('directory_create', 'No fue posible crear la carpeta de subida. Revisa los permisos del servidor.');
    }
    $baseReal = realpath(cms_upload_base_path());
    $directorioReal = realpath($directorioAbsoluto);
    if ($baseReal === false || $directorioReal === false
        || !str_starts_with(str_replace('\\', '/', $directorioReal) . '/', rtrim(str_replace('\\', '/', $baseReal), '/') . '/')) {
        throw new RuntimeException('La carpeta de subida queda fuera del directorio uploads.');
    }
    if (!is_writable($directorioReal)) {
        cms_upload_fail('directory_not_writable', 'La carpeta de subida no tiene permisos de escritura.');
    }

    $nombreSeguro = cms_generar_nombre_seguro((string) ($file['name'] ?? 'archivo'));
    $rutaAbsoluta = $directorioAbsoluto . '/' . $nombreSeguro;
    $rutaRelativa = 'uploads/' . $carpetaRelativa . '/' . $nombreSeguro;

    if (!@move_uploaded_file((string) ($file['tmp_name'] ?? ''), $rutaAbsoluta)) {
        cms_upload_fail('move_uploaded_file', 'No fue posible guardar el archivo en la carpeta de subida.');
    }

    return $rutaRelativa;
}

// Borra un archivo fisico solo si su ruta relativa cae dentro de uploads/ (protege
// contra path traversal). Uso previsto para limpieza futura y controlada; hoy ningun
// flujo automatico borra archivos al reemplazarlos (ver politica de archivos, punto 8).
function cms_eliminar_archivo_seguro(?string $rutaRelativa): bool
{
    if (!cms_es_ruta_local_upload($rutaRelativa)) {
        return false;
    }

    $rutaRelativa = str_replace('\\', '/', trim((string) $rutaRelativa));
    $baseUploads = str_replace('\\', '/', cms_upload_base_path());
    $rutaAbsoluta = dirname(__DIR__) . '/' . $rutaRelativa;
    $rutaAbsolutaReal = realpath($rutaAbsoluta);

    if ($rutaAbsolutaReal === false
        || !str_starts_with(str_replace('\\', '/', $rutaAbsolutaReal), rtrim($baseUploads, '/') . '/')) {
        return false;
    }

    return @unlink($rutaAbsolutaReal);
}
