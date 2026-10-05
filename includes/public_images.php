<?php

/** Resolve uploaded images against the installation, without changing stored paths. */
function cms_public_image(?string $path, string $fallback = '/assets/images/frontis_01.jpg'): string
{
    $path = trim((string) $path);
    if (preg_match('~^(?:https?://|//)~i', $path)) {
        return $path;
    }

    $relative = ltrim($path, '/');
    $filePath = rawurldecode((string) parse_url($relative, PHP_URL_PATH));
    if ($filePath !== '' && !preg_match('~(?:^|/)\.\.(?:/|$)|[\\\\\x00]~', $filePath)) {
        $root = realpath(__DIR__ . '/..');
        $file = realpath(__DIR__ . '/../' . $filePath);
        if ($root !== false && $file !== false
            && strncmp($file, $root . DIRECTORY_SEPARATOR, strlen($root) + 1) === 0
            && is_file($file)) {
            return '/' . $relative;
        }
    }

    return $fallback;
}
