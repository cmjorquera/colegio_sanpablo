<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_permissions.php';
require_once __DIR__ . '/includes/admin_layout.php';

$db = cms_get_connection();
admin_ensure_permissions_menu();

try {
    admin_requerir_permiso('permisos', 'ver');
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
    cms_redirect('admin.php');
}

if (!cms_table_exists($db, 'perfil_permiso_admin')) {
    cms_set_flash('danger', 'La tabla perfil_permiso_admin no existe en la base de datos activa.');
    cms_redirect('admin.php');
}

function admin_permisos_redirect(int $profileId = 0): void
{
    cms_redirect('admin_permisos.php' . ($profileId > 0 ? '?perfil=' . $profileId : ''));
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!admin_puede_modificar_permisos()) {
            throw new RuntimeException('No tienes permiso para modificar permisos.');
        }

        $profileId = (int) ($_POST['id_perfil'] ?? 0);
        $permissions = is_array($_POST['perm'] ?? null) ? $_POST['perm'] : [];
        $db->begin_transaction();
        admin_guardar_permisos_perfil($db, $profileId, $permissions);
        $db->commit();
        cms_set_flash('success', 'Los permisos del perfil fueron guardados correctamente.');
        admin_permisos_redirect($profileId);
    }
} catch (Throwable $exception) {
    if ($db instanceof mysqli) {
        @$db->rollback();
    }
    cms_set_flash('danger', $exception->getMessage());
    admin_permisos_redirect((int) ($_POST['id_perfil'] ?? 0));
}

$profiles = admin_listar_perfiles_activos($db);
$selectedProfileId = (int) ($_GET['perfil'] ?? ($profiles[0]['id_perfil'] ?? 0));
$menus = admin_listar_matriz_menus($db);
$saved = admin_obtener_permisos_perfil($db, $selectedProfileId);
$site = cms_get_site_data($db);
$institution = $site['institution'];
$flash = cms_get_flash();
$canEditPermissions = admin_puede_modificar_permisos();
$adminName = trim((string) ($_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador'));

function checked_perm(array $saved, string $type, int $idMenu, string $column): string
{
    return !empty($saved[$type][$idMenu][$column]) ? ' checked' : '';
}

$disabled = $canEditPermissions ? '' : ' disabled';

admin_render_layout_start([
    'title' => 'Permisos | CMS',
    'page_title' => 'Permisos',
    'breadcrumb' => 'Seguridad / Matriz general',
    'active_panel' => 'permisos',
    'institution_name' => $institution['nombre'] ?? 'Colegio San Pablo',
    'institution_short_name' => $institution['nombre_corto'] ?? ($institution['nombre'] ?? 'San Pablo'),
    'admin_name' => $adminName,
    'color_primario' => $institution['color_primario'] ?? null,
    'color_secundario' => $institution['color_secundario'] ?? null,
    'color_terciario' => $institution['color_terciario'] ?? null,
    'color_cuaternario' => $institution['color_cuaternario'] ?? null,
]);
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= cms_e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= cms_e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (!$canEditPermissions): ?>
    <div class="alert alert-info">
        Puedes revisar esta matriz, pero solo el usuario principal puede guardar cambios de permisos.
    </div>
<?php endif; ?>

<section class="perm-card">
    <div class="perm-head">
        <div>
            <h1>Permisos por perfil</h1>
            <p>Matriz general de acceso para el sidebar y acciones del panel administrador.</p>
        </div>
        <?php if ($canEditPermissions): ?>
            <button class="btn btn-premium btn-sm" type="submit" form="permForm"><i class="bi bi-save me-1"></i> Guardar permisos</button>
        <?php endif; ?>
    </div>

    <form class="perm-toolbar" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label fw-semibold" for="perfil">Perfil</label>
                <select class="form-select" id="perfil" name="perfil" onchange="this.form.submit()">
                    <?php foreach ($profiles as $profile): ?>
                        <option value="<?= (int) $profile['id_perfil'] ?>" <?= (int) $profile['id_perfil'] === $selectedProfileId ? 'selected' : '' ?>>
                            <?= cms_e($profile['nombre_perfil']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <form method="post" id="permForm">
        <input type="hidden" name="id_perfil" value="<?= (int) $selectedProfileId ?>">
        <div class="perm-table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Modulo</th>
                            <th class="perm-check">Ver</th>
                            <th class="perm-check">Crear</th>
                            <th class="perm-check">Editar</th>
                            <th class="perm-check">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $menu): ?>
                            <?php $menuId = (int) $menu['id_admin_menu']; ?>
                            <tr>
                                <td>
                                    <button class="btn btn-link p-0 me-2 text-muted" type="button" data-perm-row-toggle title="Marcar fila"><i class="bi bi-check2-square"></i></button>
                                    <span class="perm-module"><i class="bi <?= cms_e($menu['icono'] ?: 'bi-circle') ?> me-2"></i><?= cms_e($menu['grupo']) ?> / <?= cms_e($menu['label']) ?></span>
                                </td>
                                <?php foreach (['ver' => 'puede_ver', 'crear' => 'puede_crear', 'editar' => 'puede_editar', 'eliminar' => 'puede_eliminar'] as $action => $column): ?>
                                    <td class="perm-check"><input type="checkbox" name="perm[menu][<?= $menuId ?>][<?= cms_e($action) ?>]" value="1"<?= checked_perm($saved, 'menu', $menuId, $column) ?><?= $disabled ?>></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php foreach ($menu['children'] as $child): ?>
                                <?php $childId = (int) $child['id_admin_submenu']; ?>
                                <tr>
                                    <td>
                                        <button class="btn btn-link p-0 me-2 text-muted" type="button" data-perm-row-toggle title="Marcar fila"><i class="bi bi-check2-square"></i></button>
                                        <span class="perm-child"><i class="bi <?= cms_e($child['icono'] ?: 'bi-dot') ?> me-2"></i><?= cms_e($child['label']) ?></span>
                                    </td>
                                    <?php foreach (['ver' => 'puede_ver', 'crear' => 'puede_crear', 'editar' => 'puede_editar', 'eliminar' => 'puede_eliminar'] as $action => $column): ?>
                                        <td class="perm-check"><input type="checkbox" name="perm[submenu][<?= $childId ?>][<?= cms_e($action) ?>]" value="1"<?= checked_perm($saved, 'submenu', $childId, $column) ?><?= $disabled ?>></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</section>

<?php
admin_render_layout_end();
