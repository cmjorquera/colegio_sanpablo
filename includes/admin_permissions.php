<?php

require_once __DIR__ . '/cms_helpers.php';

const ADMIN_READONLY_MESSAGE = 'Acceso solo lectura. Tu perfil permite revisar la informacion, pero no modificarla.';
const ADMIN_DENIED_MESSAGE = 'No tienes permiso para realizar esta accion.';
const ADMIN_ACCESS_DENIED_MESSAGE = 'No tienes permiso para acceder a este módulo';

function admin_usuario_actual(): array
{
    $idUsuario = (int) ($_SESSION['id_usuario'] ?? $_SESSION['admin_id'] ?? 0);

    return [
        'id_usuario' => $idUsuario,
        'id_institucion' => (int) ($_SESSION['id_institucion'] ?? 0),
        'usuario' => (string) ($_SESSION['admin_usuario'] ?? ''),
        'nombre' => (string) ($_SESSION['admin_nombre'] ?? ''),
        'rol' => (string) ($_SESSION['admin_rol'] ?? ''),
    ];
}

function admin_normalizar_permiso_nombre(string $value): string
{
    $value = trim(function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value));
    $map = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n'];
    $value = strtr($value, $map);
    $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? $value;
    return trim($value, '_');
}

function admin_obtener_perfiles_usuario(int $idUsuario): array
{
    static $cache = [];
    if ($idUsuario <= 0) {
        return [];
    }
    if (isset($cache[$idUsuario])) {
        return $cache[$idUsuario];
    }

    $db = cms_get_connection();
    $profiles = [];

    if (cms_table_exists($db, 'usuario_perfil') && cms_table_exists($db, 'perfiles')) {
        $stmt = $db->prepare("
            SELECT p.id_perfil, p.nombre_perfil, p.descripcion, p.estado
            FROM usuario_perfil up
            INNER JOIN perfiles p ON p.id_perfil = up.id_perfil
            WHERE up.id_usuario = ?
              AND p.estado = 'activo'
            ORDER BY p.nombre_perfil ASC
        ");
        if ($stmt) {
            $stmt->bind_param('i', $idUsuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $profiles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
        }
    }

    if (!$profiles) {
        $stmt = $db->prepare('SELECT rol FROM usuario WHERE id_usuario = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('i', $idUsuario);
            $stmt->execute();
            $row = $stmt->get_result()?->fetch_assoc();
            $stmt->close();
            $role = trim((string) ($row['rol'] ?? $_SESSION['admin_rol'] ?? ''));
            if ($role !== '') {
                $profiles[] = [
                    'id_perfil' => 0,
                    'nombre_perfil' => $role,
                    'descripcion' => 'Rol legacy de usuario',
                    'estado' => 'activo',
                ];
            }
        }
    }

    $cache[$idUsuario] = $profiles;
    return $profiles;
}

function admin_perfil_actual_label(?int $idUsuario = null): string
{
    $idUsuario = $idUsuario ?? (int) ($_SESSION['id_usuario'] ?? $_SESSION['admin_id'] ?? 0);
    $profiles = admin_obtener_perfiles_usuario($idUsuario);
    $labels = array_values(array_filter(array_map(
        static fn(array $profile): string => trim((string) ($profile['nombre_perfil'] ?? '')),
        $profiles
    )));

    if ($labels) {
        return implode(', ', $labels);
    }

    $role = trim((string) ($_SESSION['admin_rol'] ?? ''));
    return $role !== '' ? $role : 'Administrador';
}

function admin_es_super_admin(?int $idUsuario = null): bool
{
    $idUsuario = $idUsuario ?? (int) ($_SESSION['id_usuario'] ?? $_SESSION['admin_id'] ?? 0);
    if ($idUsuario === 1) {
        return true;
    }

    $role = admin_normalizar_permiso_nombre((string) ($_SESSION['admin_rol'] ?? ''));
    if (in_array($role, ['super_admin', 'super_administrador'], true)) {
        return true;
    }

    foreach (admin_obtener_perfiles_usuario($idUsuario) as $profile) {
        $name = admin_normalizar_permiso_nombre((string) ($profile['nombre_perfil'] ?? ''));
        if (in_array($name, ['super_admin', 'super_administrador'], true)) {
            return true;
        }
    }

    return false;
}

function admin_es_solo_lectura(?int $idUsuario = null): bool
{
    $idUsuario = $idUsuario ?? (int) ($_SESSION['id_usuario'] ?? $_SESSION['admin_id'] ?? 0);
    foreach (admin_obtener_perfiles_usuario($idUsuario) as $profile) {
        $name = admin_normalizar_permiso_nombre((string) ($profile['nombre_perfil'] ?? ''));
        if (in_array($name, ['solo_lectura', 'lectura', 'read_only'], true)) {
            return true;
        }
    }

    return admin_normalizar_permiso_nombre((string) ($_SESSION['admin_rol'] ?? '')) === 'solo_lectura';
}

function admin_accion_columna(string $accion): string
{
    return match ($accion) {
        'crear' => 'puede_crear',
        'editar' => 'puede_editar',
        'eliminar' => 'puede_eliminar',
        default => 'puede_ver',
    };
}

function admin_permiso_por_clave(string $tipoMenu, string $clave, string $accion): bool
{
    $user = admin_usuario_actual();
    $idUsuario = (int) $user['id_usuario'];
    if ($clave === 'dashboard' && $accion === 'ver') {
        return $idUsuario > 0;
    }
    if ($idUsuario === 1) {
        return true;
    }
    if ($accion !== 'ver' && admin_es_solo_lectura($idUsuario)) {
        return false;
    }
    if (admin_es_super_admin($idUsuario)) {
        return true;
    }

    $db = cms_get_connection();
    if (!cms_table_exists($db, 'perfil_permiso_admin')) {
        return true;
    }

    $profiles = admin_obtener_perfiles_usuario($idUsuario);
    $profileIds = array_values(array_filter(array_map(static fn($p): int => (int) ($p['id_perfil'] ?? 0), $profiles)));
    if (!$profileIds) {
        return false;
    }

    $table = $tipoMenu === 'submenu' ? 'admin_submenu' : 'admin_menu';
    $idColumn = $tipoMenu === 'submenu' ? 'id_admin_submenu' : 'id_admin_menu';
    $stmtMenu = $db->prepare("SELECT {$idColumn} AS id_menu FROM {$table} WHERE clave = ? LIMIT 1");
    if (!$stmtMenu) {
        return false;
    }
    $stmtMenu->bind_param('s', $clave);
    $stmtMenu->execute();
    $menu = $stmtMenu->get_result()?->fetch_assoc();
    $stmtMenu->close();
    $idMenu = (int) ($menu['id_menu'] ?? 0);
    if ($idMenu <= 0) {
        if ($tipoMenu === 'menu') {
            return admin_permiso_por_clave('submenu', $clave, $accion);
        }
        return false;
    }

    $column = admin_accion_columna($accion);
    $placeholders = implode(',', array_fill(0, count($profileIds), '?'));
    $types = str_repeat('i', count($profileIds));
    $sql = "SELECT MAX({$column}) AS allowed
            FROM perfil_permiso_admin
            WHERE tipo_menu = ?
              AND id_menu = ?
              AND id_perfil IN ({$placeholders})";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $tipo = $tipoMenu === 'submenu' ? 'submenu' : 'menu';
    $values = array_merge([$tipo, $idMenu], $profileIds);
    $stmt->bind_param('si' . $types, ...$values);
    $stmt->execute();
    $row = $stmt->get_result()?->fetch_assoc();
    $stmt->close();

    return (int) ($row['allowed'] ?? 0) === 1;
}

function admin_tiene_permiso(string $claveMenu, string $accion): bool
{
    return admin_permiso_por_clave('menu', $claveMenu, $accion);
}

function admin_tiene_permiso_submenu(string $claveSubmenu, string $accion): bool
{
    return admin_permiso_por_clave('submenu', $claveSubmenu, $accion);
}

function admin_permiso_denegado_mensaje(string $accion): string
{
    if ($accion === 'ver') {
        return ADMIN_ACCESS_DENIED_MESSAGE;
    }
    if ($accion !== 'ver' && admin_es_solo_lectura()) {
        return ADMIN_READONLY_MESSAGE;
    }
    return ADMIN_DENIED_MESSAGE;
}

function admin_requerir_permiso(string $claveMenu, string $accion): void
{
    if (!admin_tiene_permiso($claveMenu, $accion)) {
        throw new RuntimeException(admin_permiso_denegado_mensaje($accion));
    }
}

function admin_requerir_permiso_submenu(string $claveSubmenu, string $accion): void
{
    if (!admin_tiene_permiso_submenu($claveSubmenu, $accion)) {
        throw new RuntimeException(admin_permiso_denegado_mensaje($accion));
    }
}

function admin_puede_modificar_permisos(): bool
{
    return (int) ($_SESSION['id_usuario'] ?? $_SESSION['admin_id'] ?? 0) === 1;
}

function admin_listar_perfiles_activos(mysqli $db): array
{
    if (!cms_table_exists($db, 'perfiles')) {
        return [];
    }
    $result = $db->query("SELECT id_perfil, nombre_perfil, descripcion FROM perfiles WHERE estado = 'activo' ORDER BY nombre_perfil ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function admin_listar_matriz_menus(mysqli $db): array
{
    $menus = [];
    if (!cms_table_exists($db, 'admin_menu')) {
        return $menus;
    }

    $resultMenus = $db->query("
        SELECT id_admin_menu, clave, grupo, label, href, icono, orden
        FROM admin_menu
        WHERE visible = 1 AND estado = 'activo'
        ORDER BY CASE grupo
            WHEN 'PRINCIPAL' THEN 1
            WHEN 'GESTIÓN DEL SITIO' THEN 2
            WHEN 'GESTION DEL SITIO' THEN 2
            WHEN 'SEGURIDAD' THEN 3
            WHEN 'ANALÍTICA' THEN 4
            WHEN 'ANALITICA' THEN 4
            WHEN 'SISTEMA' THEN 5
            ELSE 99
        END, orden ASC, id_admin_menu ASC
    ");
    if ($resultMenus) {
        while ($row = $resultMenus->fetch_assoc()) {
            $row['children'] = [];
            $menus[(int) $row['id_admin_menu']] = $row;
        }
        $resultMenus->free();
    }

    if (!cms_table_exists($db, 'admin_submenu')) {
        return $menus;
    }

    $resultSubmenus = $db->query("
        SELECT id_admin_submenu, id_admin_menu, clave, label, href, icono, orden
        FROM admin_submenu
        WHERE visible = 1 AND estado = 'activo'
        ORDER BY orden ASC, id_admin_submenu ASC
    ");
    if ($resultSubmenus) {
        while ($row = $resultSubmenus->fetch_assoc()) {
            $parentId = (int) ($row['id_admin_menu'] ?? 0);
            if (isset($menus[$parentId])) {
                $menus[$parentId]['children'][] = $row;
            }
        }
        $resultSubmenus->free();
    }

    return $menus;
}

function admin_obtener_permisos_perfil(mysqli $db, int $idPerfil): array
{
    $saved = ['menu' => [], 'submenu' => []];
    if ($idPerfil <= 0 || !cms_table_exists($db, 'perfil_permiso_admin')) {
        return $saved;
    }

    $stmtPerms = $db->prepare('SELECT * FROM perfil_permiso_admin WHERE id_perfil = ?');
    if ($stmtPerms) {
        $stmtPerms->bind_param('i', $idPerfil);
        $stmtPerms->execute();
        $resultPerms = $stmtPerms->get_result();
        while ($row = $resultPerms?->fetch_assoc()) {
            $type = (string) ($row['tipo_menu'] ?? 'menu');
            $saved[$type][(int) ($row['id_menu'] ?? 0)] = $row;
        }
        $stmtPerms->close();
    }

    return $saved;
}

function admin_guardar_permisos_perfil(mysqli $db, int $idPerfil, array $permissions): void
{
    if ($idPerfil <= 0) {
        throw new RuntimeException('Selecciona un perfil valido.');
    }
    if (!cms_table_exists($db, 'perfil_permiso_admin')) {
        throw new RuntimeException('La tabla perfil_permiso_admin no existe.');
    }

    $stmtProfile = $db->prepare("SELECT id_perfil FROM perfiles WHERE id_perfil = ? AND estado = 'activo' LIMIT 1");
    if (!$stmtProfile) {
        throw new RuntimeException('No se pudo validar el perfil.');
    }
    $stmtProfile->bind_param('i', $idPerfil);
    $stmtProfile->execute();
    $existsProfile = $stmtProfile->get_result()?->fetch_assoc();
    $stmtProfile->close();
    if (!$existsProfile) {
        throw new RuntimeException('El perfil no existe o esta inactivo.');
    }

    $stmtDelete = $db->prepare('DELETE FROM perfil_permiso_admin WHERE id_perfil = ?');
    if (!$stmtDelete) {
        throw new RuntimeException('No se pudo limpiar permisos anteriores.');
    }
    $stmtDelete->bind_param('i', $idPerfil);
    $stmtDelete->execute();
    $stmtDelete->close();

    $stmtInsert = $db->prepare("
        INSERT INTO perfil_permiso_admin
        (id_perfil, tipo_menu, id_menu, puede_ver, puede_crear, puede_editar, puede_eliminar, creado_en, actualizado_en)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    if (!$stmtInsert) {
        throw new RuntimeException('No se pudo preparar el guardado de permisos.');
    }

    foreach (['menu', 'submenu'] as $type) {
        foreach (($permissions[$type] ?? []) as $idMenu => $actions) {
            $idMenu = (int) $idMenu;
            if ($idMenu <= 0 || !is_array($actions)) {
                continue;
            }
            $canView = isset($actions['ver']) ? 1 : 0;
            $canCreate = isset($actions['crear']) ? 1 : 0;
            $canEdit = isset($actions['editar']) ? 1 : 0;
            $canDelete = isset($actions['eliminar']) ? 1 : 0;
            $stmtInsert->bind_param('isiiiii', $idPerfil, $type, $idMenu, $canView, $canCreate, $canEdit, $canDelete);
            $stmtInsert->execute();
        }
    }

    $stmtInsert->close();
}

function admin_asignar_perfil_usuario(mysqli $db, int $idUsuario, int $idPerfil): void
{
    if ($idUsuario <= 0 || $idPerfil <= 0) {
        throw new RuntimeException('Selecciona usuario y perfil validos.');
    }
    if (!cms_table_exists($db, 'usuario_perfil')) {
        throw new RuntimeException('La tabla usuario_perfil no existe.');
    }

    $stmtUser = $db->prepare('SELECT id_usuario FROM usuario WHERE id_usuario = ? LIMIT 1');
    $stmtUser->bind_param('i', $idUsuario);
    $stmtUser->execute();
    $hasUser = $stmtUser->get_result()?->fetch_assoc();
    $stmtUser->close();
    if (!$hasUser) {
        throw new RuntimeException('El usuario no existe.');
    }

    $stmtProfile = $db->prepare("SELECT id_perfil FROM perfiles WHERE id_perfil = ? AND estado = 'activo' LIMIT 1");
    $stmtProfile->bind_param('i', $idPerfil);
    $stmtProfile->execute();
    $hasProfile = $stmtProfile->get_result()?->fetch_assoc();
    $stmtProfile->close();
    if (!$hasProfile) {
        throw new RuntimeException('El perfil no existe o esta inactivo.');
    }

    $stmtDelete = $db->prepare('DELETE FROM usuario_perfil WHERE id_usuario = ?');
    $stmtDelete->bind_param('i', $idUsuario);
    $stmtDelete->execute();
    $stmtDelete->close();

    $stmtInsert = $db->prepare('INSERT INTO usuario_perfil (id_usuario, id_perfil, fecha_asignacion) VALUES (?, ?, NOW())');
    if (!$stmtInsert) {
        throw new RuntimeException('No se pudo asignar el perfil.');
    }
    $stmtInsert->bind_param('ii', $idUsuario, $idPerfil);
    $stmtInsert->execute();
    $stmtInsert->close();
}

function admin_ensure_permissions_menu(): void
{
    $db = cms_get_connection();
    if (!cms_table_exists($db, 'admin_menu')) {
        return;
    }

    if (cms_table_exists($db, 'admin_submenu')) {
        $stmtSubmenu = $db->prepare("SELECT id_admin_submenu FROM admin_submenu WHERE clave = 'permisos' LIMIT 1");
        if ($stmtSubmenu) {
            $stmtSubmenu->execute();
            $existsSubmenu = $stmtSubmenu->get_result()?->fetch_assoc();
            $stmtSubmenu->close();
            if ($existsSubmenu) {
                return;
            }
        }
    }

    $stmt = $db->prepare("SELECT id_admin_menu FROM admin_menu WHERE clave = 'permisos' LIMIT 1");
    if (!$stmt) {
        return;
    }
    $stmt->execute();
    $exists = $stmt->get_result()?->fetch_assoc();
    $stmt->close();
    if ($exists) {
        return;
    }

    $stmtInsert = $db->prepare("
        INSERT INTO admin_menu (clave, grupo, label, href, icono, orden, visible, estado)
        VALUES ('permisos', 'SEGURIDAD', 'Permisos', 'admin_permisos.php', 'bi-shield-check', 1, 1, 'activo')
    ");
    if ($stmtInsert) {
        $stmtInsert->execute();
        $stmtInsert->close();
    }
}

function admin_permission_attr(string $claveMenu, string $accion, string $mode = 'disabled'): string
{
    if (admin_tiene_permiso($claveMenu, $accion)) {
        return '';
    }
    $message = cms_e(admin_permiso_denegado_mensaje($accion));
    if ($mode === 'hidden') {
        return ' hidden';
    }
    return ' disabled data-admin-denied="' . $message . '"';
}
