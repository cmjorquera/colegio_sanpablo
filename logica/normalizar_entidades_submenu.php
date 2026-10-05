<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/cms_helpers.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este mantenimiento solo puede ejecutarse por CLI.\n");
}

$db = cms_get_connection();
$fields = ['titulo', 'bajada', 'contenido', 'boton_texto', 'meta_title', 'meta_description'];
$result = $db->query('SELECT id_pagina, id_sub_menu, titulo, bajada, contenido, boton_texto, meta_title, meta_description FROM sub_menu_paginas ORDER BY id_pagina');
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$changed = [];
foreach ($rows as $row) {
    $normalized = $row;
    foreach ($fields as $field) {
        $raw = $row[$field] ?? null;
        if (is_string($raw) && preg_match('/(?:&amp;)?&#(?:\d+|x[0-9a-f]+);|&(?:aacute|eacute|iacute|oacute|uacute|uuml|ntilde|iquest|iexcl);/i', $raw)) {
            $normalized[$field] = cms_decode_editor_entities($raw);
        }
    }
    if (array_intersect_key($normalized, array_flip($fields)) !== array_intersect_key($row, array_flip($fields))) {
        $changed[] = ['before' => $row, 'after' => $normalized];
    }
}

if (!$changed) {
    exit("No se encontraron entidades editoriales para normalizar.\n");
}

$quote = static function ($value) use ($db): string {
    return $value === null ? 'NULL' : "'" . $db->real_escape_string((string) $value) . "'";
};
$backup = ["-- Respaldo anterior a normalización de entidades", 'SET NAMES utf8mb4;', 'START TRANSACTION;'];
foreach ($changed as $item) {
    $r = $item['before'];
    $sets = [];
    foreach ($fields as $field) { $sets[] = "`$field`=" . $quote($r[$field]); }
    $backup[] = 'UPDATE sub_menu_paginas SET ' . implode(',', $sets) . ' WHERE id_pagina=' . (int) $r['id_pagina'] . ' AND id_sub_menu=' . (int) $r['id_sub_menu'] . ';';
}
$backup[] = 'COMMIT;';
$backupPath = __DIR__ . '/../informacion/respaldo_entidades_submenu_' . date('Ymd_His') . '.sql';
if (file_put_contents($backupPath, implode(PHP_EOL, $backup) . PHP_EOL) === false) {
    throw new RuntimeException('No se pudo crear el respaldo; no se modificó la base.');
}

$db->begin_transaction();
try {
    $sql = 'UPDATE sub_menu_paginas SET titulo=?, bajada=?, contenido=?, boton_texto=?, meta_title=?, meta_description=? WHERE id_pagina=? AND id_sub_menu=?';
    $stmt = $db->prepare($sql);
    foreach ($changed as $item) {
        $r = $item['after'];
        $stmt->bind_param('ssssssii', $r['titulo'], $r['bajada'], $r['contenido'], $r['boton_texto'], $r['meta_title'], $r['meta_description'], $r['id_pagina'], $r['id_sub_menu']);
        $stmt->execute();
    }
    $stmt->close();
    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    throw $e;
}

echo count($changed) . " página(s) normalizada(s). Respaldo: $backupPath\n";
