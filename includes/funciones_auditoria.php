<?php

function auditoriaCamposEsperados(): array
{
    return [
        'id_log',
        'id_usuario',
        'id_institucion',
        'modulo',
        'tabla_afectada',
        'id_registro',
        'accion',
        'descripcion',
        'datos_antes',
        'datos_despues',
        'ip_usuario',
        'user_agent',
        'fecha_hora',
    ];
}

function auditoriaAccionesPermitidas(): array
{
    return ['login', 'logout', 'crear', 'editar', 'eliminar', 'activar', 'desactivar', 'ocultar', 'publicar', 'cancelar', 'importar', 'descargar', 'error'];
}

function auditoriaWhitelist(): array
{
    return [
        'usuario' => 'id_usuario',
        'institucion' => 'id_institucion',
        'menus' => 'id_menu',
        'sub_menus' => 'id_sub_menu',
        'seccion' => 'id_seccion',
        'seccion_config' => 'id_config',
        'seccion_item' => 'id_item',
        'eventos' => 'id_evento',
        'auditoria_log' => 'id_log',
    ];
}

function auditoriaTablaLista(mysqli $conexion): array
{
    $result = $conexion->query('SHOW TABLES LIKE "auditoria_log"');
    if (!$result || $result->num_rows === 0) {
        return ['ok' => false, 'missing' => ['auditoria_log']];
    }

    $columns = [];
    $result = $conexion->query('SHOW COLUMNS FROM auditoria_log');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        $result->free();
    }

    $missing = array_values(array_diff(auditoriaCamposEsperados(), $columns));
    return ['ok' => count($missing) === 0, 'missing' => $missing, 'columns' => $columns];
}

function normalizarDatosAuditoria($datos)
{
    if ($datos === null) {
        return null;
    }

    if (is_object($datos)) {
        $datos = (array) $datos;
    }

    if (!is_array($datos)) {
        return $datos;
    }

    $sensibles = ['clave', 'password', 'token', 'token_reinicio'];
    $normalizados = [];
    foreach ($datos as $key => $value) {
        $keyString = strtolower((string) $key);
        if (in_array($keyString, $sensibles, true)) {
            $normalizados[$key] = '[OCULTO]';
            continue;
        }
        $normalizados[$key] = is_array($value) || is_object($value)
            ? normalizarDatosAuditoria($value)
            : $value;
    }

    return $normalizados;
}

function auditoriaJson($datos): ?string
{
    if ($datos === null) {
        return null;
    }

    return json_encode(normalizarDatosAuditoria($datos), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function obtenerRegistroAuditoria(mysqli $conexion, string $tabla, string $campoId, int $id): ?array
{
    $whitelist = auditoriaWhitelist();
    if (!isset($whitelist[$tabla]) || $whitelist[$tabla] !== $campoId) {
        throw new RuntimeException('Tabla o campo no permitido para auditoria.');
    }

    $stmt = $conexion->prepare("SELECT * FROM `$tabla` WHERE `$campoId` = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ? normalizarDatosAuditoria($row) : null;
}

function obtenerDatosAntes(mysqli $conexion, string $tabla, string $campoId, int $id): ?array
{
    return obtenerRegistroAuditoria($conexion, $tabla, $campoId, $id);
}

function registrarAuditoria(mysqli $conexion, string $modulo, string $tablaAfectada, $idRegistro, string $accion, string $descripcion = '', $datosAntes = null, $datosDespues = null): bool
{
    $estadoTabla = auditoriaTablaLista($conexion);
    if (!$estadoTabla['ok']) {
        error_log('auditoria_log no disponible. Faltan: ' . implode(', ', $estadoTabla['missing']));
        return false;
    }

    if (!in_array($accion, auditoriaAccionesPermitidas(), true)) {
        $accion = 'error';
    }

    $idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;
    $idInstitucion = isset($_SESSION['id_institucion']) ? (int) $_SESSION['id_institucion'] : null;
    $idRegistro = is_numeric($idRegistro) ? (int) $idRegistro : null;
    $datosAntesJson = auditoriaJson($datosAntes);
    $datosDespuesJson = auditoriaJson($datosDespues);
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);

    $stmt = $conexion->prepare(
        'INSERT INTO auditoria_log
            (id_usuario, id_institucion, modulo, tabla_afectada, id_registro, accion, descripcion, datos_antes, datos_despues, ip_usuario, user_agent, fecha_hora)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        'iississssss',
        $idUsuario,
        $idInstitucion,
        $modulo,
        $tablaAfectada,
        $idRegistro,
        $accion,
        $descripcion,
        $datosAntesJson,
        $datosDespuesJson,
        $ip,
        $userAgent
    );
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function auditoriaListarLogs(mysqli $conexion, array $filtros = []): array
{
    if (!auditoriaTablaLista($conexion)['ok']) {
        return [];
    }

    $where = [];
    $types = '';
    $values = [];

    if (!empty($filtros['id_usuario'])) {
        $where[] = 'a.id_usuario = ?';
        $types .= 'i';
        $values[] = (int) $filtros['id_usuario'];
    }
    if (!empty($filtros['modulo'])) {
        $where[] = 'a.modulo = ?';
        $types .= 's';
        $values[] = (string) $filtros['modulo'];
    }
    if (!empty($filtros['accion'])) {
        $where[] = 'a.accion = ?';
        $types .= 's';
        $values[] = (string) $filtros['accion'];
    }
    if (!empty($filtros['fecha_desde'])) {
        $where[] = 'DATE(a.fecha_hora) >= ?';
        $types .= 's';
        $values[] = (string) $filtros['fecha_desde'];
    }
    if (!empty($filtros['fecha_hasta'])) {
        $where[] = 'DATE(a.fecha_hora) <= ?';
        $types .= 's';
        $values[] = (string) $filtros['fecha_hasta'];
    }

    $sql = 'SELECT a.*, u.nombre, u.apellido, u.email, u.usuario, u.rol
              FROM auditoria_log a
         LEFT JOIN usuario u ON u.id_usuario = a.id_usuario';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY a.fecha_hora DESC, a.id_log DESC LIMIT 1000';

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '') {
        $refs = [];
        foreach ($values as $key => $value) {
            $refs[$key] = &$values[$key];
        }
        $stmt->bind_param($types, ...$refs);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return array_map('normalizarDatosAuditoria', $rows);
}

function auditoriaListarUsuarios(mysqli $conexion): array
{
    $result = $conexion->query('SELECT id_usuario, nombre, apellido, email, usuario FROM usuario ORDER BY nombre ASC, apellido ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function auditoriaValoresUnicos(mysqli $conexion, string $campo): array
{
    if (!in_array($campo, ['modulo', 'accion'], true) || !auditoriaTablaLista($conexion)['ok']) {
        return [];
    }
    $result = $conexion->query("SELECT DISTINCT `$campo` FROM auditoria_log WHERE `$campo` IS NOT NULL AND `$campo` <> '' ORDER BY `$campo` ASC");
    $values = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $values[] = (string) $row[$campo];
        }
    }
    return $values;
}
