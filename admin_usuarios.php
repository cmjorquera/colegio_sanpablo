<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_permissions.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_once __DIR__ . '/includes/mail_helpers.php';

function admin_user_roles(): array
{
    return ['super_admin', 'admin_institucion', 'editor', 'solo_lectura'];
}

function admin_user_redirect(): void
{
    cms_redirect('admin_usuarios.php');
}

function admin_user_can_manage_all(string $role): bool
{
    return $role === 'super_admin';
}

$db = cms_get_connection();
$sessionInstitutionId = (int) ($_SESSION['id_institucion'] ?? 1);
$adminRole = (string) ($_SESSION['admin_rol'] ?? '');
$canManageAll = admin_user_can_manage_all($adminRole);
$institutionId = $canManageAll ? 0 : $sessionInstitutionId;
$site = cms_get_site_data($db);
$institution = $site['institution'];
$roles = admin_user_roles();

try {
    admin_requerir_permiso_submenu('usuarios', 'ver');
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
    cms_redirect('admin.php?panel=dashboard');
}
$canCreateUsers = admin_tiene_permiso_submenu('usuarios', 'crear');
$canEditUsers = admin_tiene_permiso_submenu('usuarios', 'editar');
$canDeleteUsers = admin_tiene_permiso_submenu('usuarios', 'eliminar');
$canViewPermissions = admin_tiene_permiso('permisos', 'ver');
$canEditPermissions = admin_puede_modificar_permisos();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string) ($_POST['accion'] ?? '');

        if ($action === 'guardar_usuario') {
            $id = (int) ($_POST['id_usuario'] ?? 0);
            admin_requerir_permiso_submenu('usuarios', $id > 0 ? 'editar' : 'crear');
            $nombre = trim((string) ($_POST['nombre'] ?? ''));
            $apellido = trim((string) ($_POST['apellido'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $usuario = trim((string) ($_POST['usuario'] ?? ''));
            $rol = trim((string) ($_POST['rol'] ?? ''));
            $estado = trim((string) ($_POST['estado'] ?? 'activo'));
            $idInstitucionForm = (int) ($_POST['id_institucion'] ?? $sessionInstitutionId);
            $clave = (string) ($_POST['clave'] ?? '');

            if ($nombre === '') {
                throw new RuntimeException('El nombre es obligatorio.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Ingresa un email valido.');
            }
            if ($usuario === '') {
                $usuario = $email;
            }
            if (!in_array($rol, $roles, true)) {
                throw new RuntimeException('Selecciona un rol valido.');
            }
            if (!in_array($estado, ['activo', 'inactivo'], true)) {
                throw new RuntimeException('Selecciona un estado valido.');
            }
            if (!$canManageAll) {
                $idInstitucionForm = $sessionInstitutionId;
            }
            if ($idInstitucionForm <= 0) {
                throw new RuntimeException('Selecciona una institucion valida.');
            }

            if ($id > 0) {
                $scopeSql = $canManageAll ? '' : ' AND id_institucion = ?';
                $stmtExists = $db->prepare("SELECT clave FROM usuario WHERE id_usuario = ?{$scopeSql} LIMIT 1");
                if (!$stmtExists) {
                    throw new RuntimeException('No se pudo validar el usuario.');
                }
                if ($canManageAll) {
                    $stmtExists->bind_param('i', $id);
                } else {
                    $stmtExists->bind_param('ii', $id, $sessionInstitutionId);
                }
                $stmtExists->execute();
                $exists = $stmtExists->get_result()?->fetch_assoc();
                $stmtExists->close();
                if (!$exists) {
                    throw new RuntimeException('No se encontro el usuario a editar.');
                }

                if ($clave !== '') {
                    $claveHash = password_hash($clave, PASSWORD_DEFAULT);
                    $stmtUpdate = $db->prepare("
                        UPDATE usuario
                        SET id_institucion = ?, nombre = ?, apellido = ?, email = ?, usuario = ?, rol = ?, estado = ?, clave = ?
                        WHERE id_usuario = ?
                    ");
                    if (!$stmtUpdate) {
                        throw new RuntimeException('No se pudo preparar la actualizacion.');
                    }
                    $stmtUpdate->bind_param('isssssssi', $idInstitucionForm, $nombre, $apellido, $email, $usuario, $rol, $estado, $claveHash, $id);
                } else {
                    $stmtUpdate = $db->prepare("
                        UPDATE usuario
                        SET id_institucion = ?, nombre = ?, apellido = ?, email = ?, usuario = ?, rol = ?, estado = ?
                        WHERE id_usuario = ?
                    ");
                    if (!$stmtUpdate) {
                        throw new RuntimeException('No se pudo preparar la actualizacion.');
                    }
                    $stmtUpdate->bind_param('issssssi', $idInstitucionForm, $nombre, $apellido, $email, $usuario, $rol, $estado, $id);
                }
                $stmtUpdate->execute();
                $stmtUpdate->close();
                cms_set_flash('success', 'El usuario fue actualizado correctamente.');
            } else {
                if ($clave === '') {
                    throw new RuntimeException('La clave es obligatoria al crear un usuario.');
                }
                $claveHash = password_hash($clave, PASSWORD_DEFAULT);
                $stmtInsert = $db->prepare("
                    INSERT INTO usuario (id_institucion, nombre, apellido, email, usuario, rol, estado, clave)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if (!$stmtInsert) {
                    throw new RuntimeException('No se pudo preparar la creacion.');
                }
                $stmtInsert->bind_param('isssssss', $idInstitucionForm, $nombre, $apellido, $email, $usuario, $rol, $estado, $claveHash);
                $stmtInsert->execute();
                $stmtInsert->close();
                cms_set_flash('success', 'El usuario fue creado correctamente.');
            }

            admin_user_redirect();
        }

        if ($action === 'toggle_usuario' || $action === 'eliminar_usuario') {
            admin_requerir_permiso_submenu('usuarios', $action === 'eliminar_usuario' ? 'eliminar' : 'editar');
            $id = (int) ($_POST['id_usuario'] ?? 0);
            $scopeSql = $canManageAll ? '' : ' AND id_institucion = ?';
            $stmt = $db->prepare("SELECT estado FROM usuario WHERE id_usuario = ?{$scopeSql} LIMIT 1");
            if (!$stmt) {
                throw new RuntimeException('No se pudo consultar el usuario.');
            }
            if ($canManageAll) {
                $stmt->bind_param('i', $id);
            } else {
                $stmt->bind_param('ii', $id, $sessionInstitutionId);
            }
            $stmt->execute();
            $row = $stmt->get_result()?->fetch_assoc();
            $stmt->close();
            if (!$row) {
                throw new RuntimeException('Usuario no encontrado.');
            }
            $nextState = $action === 'eliminar_usuario'
                ? 'inactivo'
                : (((string) ($row['estado'] ?? '')) === 'activo' ? 'inactivo' : 'activo');
            $stmtUpdate = $db->prepare("UPDATE usuario SET estado = ? WHERE id_usuario = ?");
            if (!$stmtUpdate) {
                throw new RuntimeException('No se pudo cambiar el estado.');
            }
            $stmtUpdate->bind_param('si', $nextState, $id);
            $stmtUpdate->execute();
            $stmtUpdate->close();
            cms_set_flash('success', $nextState === 'activo' ? 'El usuario fue activado.' : 'El usuario fue inactivado.');
            admin_user_redirect();
        }

        if ($action === 'enviar_reset') {
            admin_requerir_permiso_submenu('usuarios', 'editar');
            $id = (int) ($_POST['id_usuario'] ?? 0);
            admin_create_password_reset_and_send($db, $id, $sessionInstitutionId, $canManageAll, $institution);
            cms_set_flash('success', 'Se envio el correo de cambio de clave.');
            admin_user_redirect();
        }

        if ($action === 'guardar_permisos_usuario') {
            if (!admin_puede_modificar_permisos()) {
                throw new RuntimeException('No tienes permiso para modificar permisos.');
            }
            $idUsuarioPerm = (int) ($_POST['id_usuario_permiso'] ?? 0);
            $idPerfilPerm = (int) ($_POST['id_perfil_permiso'] ?? 0);
            $permissions = is_array($_POST['perm'] ?? null) ? $_POST['perm'] : [];
            $db->begin_transaction();
            admin_asignar_perfil_usuario($db, $idUsuarioPerm, $idPerfilPerm);
            admin_guardar_permisos_perfil($db, $idPerfilPerm, $permissions);
            $db->commit();
            cms_set_flash('success', 'Los permisos del usuario fueron actualizados segun su perfil asignado.');
            admin_user_redirect();
        }
    }
} catch (Throwable $exception) {
    if ($db instanceof mysqli) {
        @$db->rollback();
    }
    cms_set_flash('danger', $exception->getMessage());
    admin_user_redirect();
}

$flash = cms_get_flash();

$institutions = [];
$resultInstitutions = $db->query("SELECT id_institucion, nombre FROM institucion ORDER BY nombre ASC");
if ($resultInstitutions) {
    while ($row = $resultInstitutions->fetch_assoc()) {
        $institutions[] = $row;
    }
    $resultInstitutions->free();
}

$profiles = admin_listar_perfiles_activos($db);
$permissionMenus = admin_listar_matriz_menus($db);
$permissionsByProfile = [];
foreach ($profiles as $profile) {
    $permissionsByProfile[(int) $profile['id_perfil']] = admin_obtener_permisos_perfil($db, (int) $profile['id_perfil']);
}

$where = [];
$types = '';
$values = [];
if (!$canManageAll) {
    $where[] = 'u.id_institucion = ?';
    $types .= 'i';
    $values[] = $sessionInstitutionId;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmtUsers = $db->prepare("
    SELECT u.id_usuario, u.id_institucion, u.nombre, u.apellido, u.email, u.usuario, u.rol, u.estado,
           i.nombre AS institucion_nombre,
           GROUP_CONCAT(up.id_perfil ORDER BY up.fecha_asignacion DESC SEPARATOR ',') AS perfiles_ids,
           GROUP_CONCAT(p.nombre_perfil ORDER BY up.fecha_asignacion DESC SEPARATOR ', ') AS perfiles_nombres
    FROM usuario u
    LEFT JOIN institucion i ON i.id_institucion = u.id_institucion
    LEFT JOIN usuario_perfil up ON up.id_usuario = u.id_usuario
    LEFT JOIN perfiles p ON p.id_perfil = up.id_perfil AND p.estado = 'activo'
    {$whereSql}
    GROUP BY u.id_usuario, u.id_institucion, u.nombre, u.apellido, u.email, u.usuario, u.rol, u.estado, i.nombre
    ORDER BY u.nombre ASC, u.apellido ASC, u.id_usuario DESC
");
if (!$stmtUsers) {
    throw new RuntimeException('No se pudo preparar el listado de usuarios.');
}
if ($types !== '') {
    $stmtUsers->bind_param($types, ...$values);
}
$stmtUsers->execute();
$users = $stmtUsers->get_result()?->fetch_all(MYSQLI_ASSOC) ?? [];
$stmtUsers->close();

$adminName = trim((string) ($_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador'));

function admin_user_checked_perm(array $saved, string $type, int $idMenu, string $column): string
{
    return !empty($saved[$type][$idMenu][$column]) ? ' checked' : '';
}

function admin_user_role_label(string $role): string
{
    $labels = [
        'super_admin' => 'Super Administrador',
        'admin_institucion' => 'Administrador',
        'editor' => 'Editor',
        'solo_lectura' => 'Solo Lectura',
    ];

    return $labels[$role] ?? ucwords(str_replace('_', ' ', $role));
}

admin_render_layout_start([
    'title' => 'Usuarios | CMS',
    'page_title' => 'Usuarios',
    'active_panel' => 'usuarios',
    'institution_name' => $institution['nombre'] ?? 'Colegio San Pablo',
    'institution_short_name' => $institution['nombre_corto'] ?? ($institution['nombre'] ?? 'San Pablo'),
    'admin_name' => $adminName,
    'header_actions' => $canCreateUsers ? '<button class="btn btn-premium btn-sm" type="button" data-user-open><i class="bi bi-plus-lg me-1"></i> Nuevo usuario</button>' : '',
    'color_primario' => $institution['color_primario'] ?? null,
    'color_secundario' => $institution['color_secundario'] ?? null,
    'color_terciario' => $institution['color_terciario'] ?? null,
    'color_cuaternario' => $institution['color_cuaternario'] ?? null,
    'extra_head' => '',
]);
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= cms_e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= cms_e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<section class="usr-card">
    <div class="usr-head">
        <div>
            <h1>Usuarios</h1>
            <p>Administra accesos del CMS por institucion y rol.</p>
        </div>
        <?php if ($canCreateUsers): ?>
            <button class="btn btn-premium" type="button" data-user-open><i class="bi bi-plus-lg me-1"></i> Nuevo usuario</button>
        <?php endif; ?>
    </div>
    <div class="usr-table-wrap">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Institucion</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $isActive = ($user['estado'] ?? '') === 'activo';
                        $payload = [
                            'id_usuario' => (int) $user['id_usuario'],
                            'id_institucion' => (int) $user['id_institucion'],
                            'nombre' => (string) $user['nombre'],
                            'apellido' => (string) ($user['apellido'] ?? ''),
                            'email' => (string) $user['email'],
                            'usuario' => (string) $user['usuario'],
                            'rol' => (string) $user['rol'],
                            'estado' => (string) $user['estado'],
                        ];
                        $userProfileIds = array_values(array_filter(array_map('intval', explode(',', (string) ($user['perfiles_ids'] ?? '')))));
                        $primaryProfileId = (int) ($userProfileIds[0] ?? 0);
                        $profileLabel = trim((string) ($user['perfiles_nombres'] ?? ''));
                        if ($profileLabel === '') {
                            $profileLabel = admin_user_role_label((string) $user['rol']);
                        }
                        $permissionPayload = [
                            'id_usuario' => (int) $user['id_usuario'],
                            'nombre' => trim((string) $user['nombre'] . ' ' . (string) ($user['apellido'] ?? '')),
                            'email' => (string) $user['email'],
                            'id_perfil' => $primaryProfileId,
                        ];
                        ?>
                        <tr>
                            <td><span class="usr-name"><?= cms_e($user['nombre']) ?></span></td>
                            <td><?= cms_e($user['apellido']) ?></td>
                            <td><?= cms_e($user['email']) ?></td>
                            <td><code><?= cms_e($user['usuario']) ?></code></td>
                            <td><span class="role-pill"><?= cms_e($profileLabel) ?></span></td>
                            <td><?= cms_e($user['institucion_nombre'] ?? 'Sin institucion') ?></td>
                            <td><span class="badge-soft <?= $isActive ? 'success' : 'warning' ?>"><?= $isActive ? 'Activo' : 'Inactivo' ?></span></td>
                            <td>
                                <div class="usr-actions">
                                    <button class="btn-icon edit" type="button" data-user-edit="<?= cms_e(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>" title="Editar" aria-label="Editar"<?= $canEditUsers ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>><i class="bi bi-pencil-square"></i></button>
                                    <form method="post" class="d-inline" data-confirm-title="<?= $isActive ? 'Inactivar usuario' : 'Activar usuario' ?>" data-confirm-message="Se cambiara el estado del usuario.">
                                        <input type="hidden" name="accion" value="toggle_usuario">
                                        <input type="hidden" name="id_usuario" value="<?= (int) $user['id_usuario'] ?>">
                                        <button class="btn-icon <?= $isActive ? '' : 'preview' ?>" type="submit" title="<?= $isActive ? 'Inactivar' : 'Activar' ?>"<?= $canEditUsers ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>><i class="bi <?= $isActive ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i></button>
                                    </form>
                                    <form method="post" class="d-inline js-reset-form">
                                        <input type="hidden" name="accion" value="enviar_reset">
                                        <input type="hidden" name="id_usuario" value="<?= (int) $user['id_usuario'] ?>">
                                        <button
                                            class="btn-icon preview js-send-reset"
                                            type="button"
                                            title="Enviar correo cambio clave"
                                            onclick="return confirmarEnvioCredenciales(this);"
                                            data-user-name="<?= cms_e(trim((string) $user['nombre'] . ' ' . (string) ($user['apellido'] ?? ''))) ?>"
                                            <?= $canEditUsers ? '' : 'disabled' ?>
                                            <?= $canEditUsers ? '' : ' data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('editar')) . '"' ?>
                                        ><i class="bi bi-envelope"></i></button>
                                    </form>
                                    <?php if ($canViewPermissions): ?>
                                        <button class="btn-icon preview" type="button" data-user-permissions="<?= cms_e(json_encode($permissionPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>" title="Permisos" aria-label="Permisos">
                                            <i class="bi bi-shield-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <form method="post" class="d-inline" data-confirm-title="Eliminar/Inactivar usuario" data-confirm-message="El usuario quedara inactivo.">
                                        <input type="hidden" name="accion" value="eliminar_usuario">
                                        <input type="hidden" name="id_usuario" value="<?= (int) $user['id_usuario'] ?>">
                                        <button class="btn-icon delete" type="submit" title="Eliminar/Inactivar"<?= $canDeleteUsers ? '' : ' disabled data-admin-denied="' . cms_e(admin_permiso_denegado_mensaje('eliminar')) . '"' ?>><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$users): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No hay usuarios para mostrar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="usuarioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" id="usuarioForm">
                <input type="hidden" name="accion" value="guardar_usuario">
                <input type="hidden" name="id_usuario" id="id_usuario" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="usuarioModalTitle">Nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="usr-modal-grid">
                        <div><label class="form-label" for="nombre">Nombre *</label><input class="form-control" id="nombre" name="nombre" required></div>
                        <div><label class="form-label" for="apellido">Apellido</label><input class="form-control" id="apellido" name="apellido"></div>
                        <div><label class="form-label" for="email">Email *</label><input class="form-control" id="email" name="email" type="email" required></div>
                        <div><label class="form-label" for="usuario">Usuario</label><input class="form-control" id="usuario" name="usuario" placeholder="Puede ser igual al email"></div>
                        <div>
                            <label class="form-label" for="rol">Rol *</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <?php foreach ($roles as $roleOption): ?>
                                    <option value="<?= cms_e($roleOption) ?>"><?= cms_e($roleOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="estado">Estado *</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="id_institucion">Institucion *</label>
                            <select class="form-select" id="id_institucion" name="id_institucion" required <?= $canManageAll ? '' : 'disabled' ?>>
                                <?php foreach ($institutions as $inst): ?>
                                    <option value="<?= (int) $inst['id_institucion'] ?>" <?= (int) $inst['id_institucion'] === $sessionInstitutionId ? 'selected' : '' ?>><?= cms_e($inst['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!$canManageAll): ?><input type="hidden" name="id_institucion" value="<?= (int) $sessionInstitutionId ?>"><?php endif; ?>
                        </div>
                        <div>
                            <label class="form-label" for="clave">Clave <span id="claveRequired">*</span></label>
                            <input class="form-control" id="clave" name="clave" type="password" autocomplete="new-password">
                            <div class="form-text" id="claveHelp">Obligatoria al crear. Al editar, dejar vacia para mantener la actual.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-premium"><i class="bi bi-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($canViewPermissions): ?>
<div class="modal fade permissions-modal" id="usuarioPermisosModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" id="usuarioPermisosForm">
                <input type="hidden" name="accion" value="guardar_permisos_usuario">
                <input type="hidden" name="id_usuario_permiso" id="perm_id_usuario" value="0">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="usuarioPermisosTitle">Permisos de usuario</h5>
                        <div class="text-muted small" id="usuarioPermisosSub">Selecciona el perfil y revisa la matriz asociada.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <?php if (!$canEditPermissions): ?>
                        <div class="alert alert-info py-2">
                            Puedes revisar los permisos, pero solo el usuario id 1 puede modificarlos.
                        </div>
                    <?php endif; ?>
                    <div class="row g-3 mb-3">
                        <div class="col-lg-5">
                            <label class="form-label" for="id_perfil_permiso">Perfil asignado</label>
                            <select class="form-select" id="id_perfil_permiso" name="id_perfil_permiso" <?= $canEditPermissions ? '' : 'disabled' ?>>
                                <?php foreach ($profiles as $profile): ?>
                                    <option value="<?= (int) $profile['id_perfil'] ?>"><?= cms_e($profile['nombre_perfil']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!$canEditPermissions): ?>
                                <input type="hidden" name="id_perfil_permiso" id="id_perfil_permiso_hidden" value="">
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-7">
                            <label class="form-label">Alcance</label>
                            <div class="alert alert-warning mb-0 py-2">
                                La base actual guarda permisos en <strong>perfil_permiso_admin</strong>. Editar esta matriz afecta a todos los usuarios con el mismo perfil.
                            </div>
                        </div>
                    </div>

                    <?php foreach ($profiles as $profile): ?>
                        <?php
                        $profileId = (int) $profile['id_perfil'];
                        $savedPerms = $permissionsByProfile[$profileId] ?? ['menu' => [], 'submenu' => []];
                        ?>
                        <div class="permissions-grid permissions-card-grid" data-perm-profile-panel="<?= $profileId ?>">
                            <?php foreach ($permissionMenus as $menu): ?>
                                <?php $menuId = (int) $menu['id_admin_menu']; ?>
                                <div class="perm-switch-card">
                                    <div class="perm-switch-title"><i class="bi <?= cms_e($menu['icono'] ?: 'bi-circle') ?>"></i><?= cms_e($menu['label']) ?></div>
                                    <div class="perm-switch-actions">
                                        <?php foreach (['ver' => 'puede_ver', 'crear' => 'puede_crear', 'editar' => 'puede_editar', 'eliminar' => 'puede_eliminar'] as $action => $column): ?>
                                            <label class="perm-switch">
                                                <span><?= cms_e(ucfirst($action)) ?></span>
                                                <input type="checkbox" name="perm[menu][<?= $menuId ?>][<?= cms_e($action) ?>]" value="1"<?= admin_user_checked_perm($savedPerms, 'menu', $menuId, $column) ?><?= $canEditPermissions ? '' : ' disabled' ?>>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php foreach ($menu['children'] as $child): ?>
                                    <?php $childId = (int) $child['id_admin_submenu']; ?>
                                    <div class="perm-switch-card is-child">
                                        <div class="perm-switch-title"><i class="bi <?= cms_e($child['icono'] ?: 'bi-dot') ?>"></i><?= cms_e($child['label']) ?></div>
                                        <div class="perm-switch-actions">
                                            <?php foreach (['ver' => 'puede_ver', 'crear' => 'puede_crear', 'editar' => 'puede_editar', 'eliminar' => 'puede_eliminar'] as $action => $column): ?>
                                                <label class="perm-switch">
                                                    <span><?= cms_e(ucfirst($action)) ?></span>
                                                    <input type="checkbox" name="perm[submenu][<?= $childId ?>][<?= cms_e($action) ?>]" value="1"<?= admin_user_checked_perm($savedPerms, 'submenu', $childId, $column) ?><?= $canEditPermissions ? '' : ' disabled' ?>>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
                    <?php if ($canEditPermissions): ?>
                        <button type="submit" class="btn btn-premium"><i class="bi bi-save me-1"></i> Guardar permisos</button>
                    <?php else: ?>
                        <button type="button" class="btn btn-premium" data-admin-denied="No tienes permiso para modificar permisos."><i class="bi bi-lock me-1"></i> Solo lectura</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$extraScripts = <<<'HTML'
<script>
(function () {
    var modalEl = document.getElementById('usuarioModal');
    var modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    var permissionsModalEl = document.getElementById('usuarioPermisosModal');
    var permissionsModal = permissionsModalEl ? bootstrap.Modal.getOrCreateInstance(permissionsModalEl) : null;
    var permissionsForm = document.getElementById('usuarioPermisosForm');
    var form = document.getElementById('usuarioForm');
    function setValue(id, value) {
        var el = document.getElementById(id);
        if (el) el.value = value == null ? '' : value;
    }
    function resetForm() {
        if (!form) return;
        form.reset();
        setValue('id_usuario', '0');
        setValue('estado', 'activo');
        document.getElementById('clave').required = true;
        document.getElementById('claveRequired').style.display = '';
        document.getElementById('usuarioModalTitle').textContent = 'Nuevo usuario';
    }
    document.querySelectorAll('[data-user-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            resetForm();
            if (modal) modal.show();
        });
    });
    document.querySelectorAll('[data-user-edit]').forEach(function (button) {
        button.addEventListener('click', function () {
            var data = {};
            try { data = JSON.parse(button.getAttribute('data-user-edit') || '{}'); } catch (e) { data = {}; }
            if (!form) return;
            form.reset();
            setValue('id_usuario', data.id_usuario || 0);
            setValue('nombre', data.nombre || '');
            setValue('apellido', data.apellido || '');
            setValue('email', data.email || '');
            setValue('usuario', data.usuario || '');
            setValue('rol', data.rol || 'editor');
            setValue('estado', data.estado || 'activo');
            setValue('id_institucion', data.id_institucion || '');
            setValue('clave', '');
            document.getElementById('clave').required = false;
            document.getElementById('claveRequired').style.display = 'none';
            document.getElementById('usuarioModalTitle').textContent = 'Editar usuario';
            if (modal) modal.show();
        });
    });

    function syncPermissionPanels(profileId) {
        var selected = String(profileId || '');
        document.querySelectorAll('[data-perm-profile-panel]').forEach(function (panel) {
            var active = panel.getAttribute('data-perm-profile-panel') === selected;
            panel.style.display = active ? '' : 'none';
            panel.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
                input.disabled = !active || input.hasAttribute('data-readonly-lock');
            });
        });
        var hidden = document.getElementById('id_perfil_permiso_hidden');
        if (hidden) {
            hidden.value = selected;
        }
    }

    document.querySelectorAll('#usuarioPermisosModal input[type="checkbox"]:disabled').forEach(function (input) {
        input.setAttribute('data-readonly-lock', '1');
    });

    document.querySelectorAll('[data-user-permissions]').forEach(function (button) {
        button.addEventListener('click', function () {
            var data = {};
            try { data = JSON.parse(button.getAttribute('data-user-permissions') || '{}'); } catch (e) { data = {}; }
            var profileSelect = document.getElementById('id_perfil_permiso');
            var profileId = data.id_perfil || (profileSelect && profileSelect.options.length ? profileSelect.options[0].value : '');
            setValue('perm_id_usuario', data.id_usuario || 0);
            if (profileSelect) {
                profileSelect.value = profileId;
            }
            var title = document.getElementById('usuarioPermisosTitle');
            var sub = document.getElementById('usuarioPermisosSub');
            if (title) { title.textContent = 'Permisos de ' + (data.nombre || 'usuario'); }
            if (sub) { sub.textContent = data.email || 'Selecciona el perfil y revisa la matriz asociada.'; }
            syncPermissionPanels(profileId);
            if (permissionsModal) permissionsModal.show();
        });
    });

    var profileSelect = document.getElementById('id_perfil_permiso');
    if (profileSelect) {
        profileSelect.addEventListener('change', function () {
            syncPermissionPanels(profileSelect.value);
        });
        syncPermissionPanels(profileSelect.value);
    }

    window.confirmarEnvioCredenciales = function (button) {
        if (!button || button.disabled) {
            return false;
        }
        var resetForm = button.closest('form');
        if (!resetForm) {
            return false;
        }
        var userName = button.getAttribute('data-user-name') || 'este usuario';
        adminConfirm({
            title: 'Enviar correo de credenciales',
            msg: '¿Deseas enviarle a ' + userName + ' un correo para crear una nueva clave?',
            type: 'warning',
            btnText: 'Sí, enviar correo',
            onConfirm: function () {
                var fd = new FormData(resetForm);
                button.disabled = true;
                fetch('ajax/enviar_correo_clave.php', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (!result.ok || !result.data.ok) {
                        throw new Error(result.data.message || 'No se pudo enviar el correo.');
                    }
                    adminNotify({ title: 'Correo enviado', msg: result.data.message || 'Se envio el correo para crear una nueva clave.', type: 'success' });
                })
                .catch(function (error) {
                    if (window.Swal) {
                        Swal.fire({ icon: 'error', title: 'No se pudo enviar', text: error.message });
                    } else {
                        adminNotify({ title: 'No se pudo enviar', msg: error.message, type: 'danger' });
                    }
                })
                .finally(function () {
                    button.disabled = false;
                });
            }
        });
        return false;
    };

    document.querySelectorAll('form[data-confirm-title]').forEach(function (confirmForm) {
        confirmForm.addEventListener('submit', function (event) {
            event.preventDefault();
            adminConfirm({
                title: confirmForm.getAttribute('data-confirm-title') || 'Confirmar accion',
                msg: confirmForm.getAttribute('data-confirm-message') || 'Confirma para continuar.',
                type: confirmForm.querySelector('.btn-icon.delete') ? 'danger' : 'warning',
                btnText: 'Confirmar',
                onConfirm: function () {
                    confirmForm.submit();
                }
            });
        });
    });
})();
</script>
HTML;

admin_render_layout_end(['extra_scripts' => $extraScripts]);
