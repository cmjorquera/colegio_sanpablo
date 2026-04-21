<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: colegiosanpablo.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_layout.php';

$db = cms_get_connection();
$institutionId = cms_get_institution_id($db);
cms_sync_sections($db, $institutionId);

$panel = $_GET['panel'] ?? 'contenedores';
$sectionId = isset($_GET['section']) ? (int) $_GET['section'] : 0;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['accion'] ?? '';
        $sectionId = (int) ($_POST['id_seccion'] ?? $sectionId);
        $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

        if ($action === 'toggle_seccion' && $sectionId > 0) {
            cms_toggle_section_visibility($db, $sectionId);
            if ($isAjax) {
                $updatedSection = cms_get_section($db, $sectionId);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'ok' => true,
                    'visible' => $updatedSection['visible'] ?? 'no',
                    'label' => ($updatedSection['visible'] ?? 'no') === 'si' ? 'Activo' : 'Oculto',
                ]);
                exit;
            }
            cms_set_flash('success', 'La visibilidad del contenedor fue actualizada.');
            cms_redirect('admin.php?panel=contenedores');
        }

        if ($action === 'guardar_menu') {
            cms_save_menu($db, $_POST);
            cms_set_flash('success', 'El menú fue guardado correctamente.');
            cms_redirect('admin.php?panel=menus');
        }

        if ($action === 'toggle_menu') {
            cms_toggle_menu($db, (int) ($_POST['id_menu'] ?? 0));
            cms_set_flash('success', 'El estado del menú fue actualizado.');
            cms_redirect('admin.php?panel=menus');
        }

        if ($action === 'guardar_submenu') {
            cms_save_submenu($db, $_POST);
            cms_set_flash('success', 'El submenú fue guardado correctamente.');
            cms_redirect('admin.php?panel=submenus');
        }

        if ($action === 'toggle_submenu') {
            cms_toggle_submenu($db, (int) ($_POST['id_sub_menu'] ?? 0));
            cms_set_flash('success', 'El estado del submenú fue actualizado.');
            cms_redirect('admin.php?panel=submenus');
        }

        if ($action === 'guardar_institucion') {
            cms_save_institution($db, $institutionId, $_POST);
            cms_set_flash('success', 'La configuración institucional fue actualizada.');
            cms_redirect('admin.php?panel=configuracion');
        }
    }
} catch (Throwable $e) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'message' => $e->getMessage(),
        ]);
        exit;
    }
    cms_set_flash('danger', $e->getMessage());
    cms_redirect('admin.php?panel=' . urlencode($panel));
}

$flash = cms_get_flash();
$site = cms_get_site_data($db);
$sections = cms_list_sections_admin($db, $institutionId);
$institution = $site['institution'];
$menus = cms_list_menus($db);
$submenus = cms_list_submenus($db);
$editingMenu = isset($_GET['menu']) ? cms_get_menu($db, (int) $_GET['menu']) : null;
$editingSubmenu = isset($_GET['submenu']) ? cms_get_submenu($db, (int) $_GET['submenu']) : null;

$pageTitles = [
    'dashboard' => ['title' => 'Dashboard', 'crumb' => 'Panel general del CMS'],
    'contenedores' => ['title' => 'Contenedores del sitio', 'crumb' => 'Listado general de bloques visuales'],
    'menus' => ['title' => 'Menú principal', 'crumb' => 'Administración de menus'],
    'submenus' => ['title' => 'Submenús', 'crumb' => 'Administración de sub_menus'],
    'configuracion' => ['title' => 'Configuración institucional', 'crumb' => 'Datos globales del sitio'],
];
$pageMeta = $pageTitles[$panel] ?? $pageTitles['contenedores'];

admin_render_layout_start([
    'title' => 'Panel CMS | Colegio San Pablo',
    'page_title' => $pageMeta['title'],
    'breadcrumb' => $pageMeta['crumb'],
    'active_panel' => $panel,
    'institution_name' => $institution['nombre'] ?? 'Institución activa',
    'institution_short_name' => $institution['nombre_corto'] ?? ($institution['nombre'] ?? 'Institución'),
    'institution_logo' => $institution['logo_header'] ?? '',
    'admin_name' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'header_actions' => '<a href="index.php" target="_blank" class="btn btn-soft"><i class="bi bi-eye me-2"></i>Ver sitio</a>',
]);
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= cms_e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= cms_e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($panel === 'dashboard'): ?>
    <div class="row g-4">
        <div class="col-md-6 col-xl-3"><div class="metric-card metric-green"><div class="big-number"><?= count($sections) ?></div><h5>Contenedores</h5><p>Bloques registrados y sincronizados desde <code>seccion</code>.</p></div></div>
        <div class="col-md-6 col-xl-3"><div class="metric-card metric-blue"><div class="big-number"><?= count($menus) ?></div><h5>Menús</h5><p>Navegación principal usando tablas reales.</p></div></div>
        <div class="col-md-6 col-xl-3"><div class="metric-card metric-gold"><div class="big-number"><?= count($submenus) ?></div><h5>Submenús</h5><p>Enlaces secundarios del sitio institucional.</p></div></div>
        <div class="col-md-6 col-xl-3"><div class="metric-card" style="background:linear-gradient(135deg,#1a2238,#2d3654)"><div class="big-number"><?= count(array_filter($sections, static fn($section) => ($section['visible'] ?? '') === 'si')) ?></div><h5>Visibles</h5><p>Contenedores activos en el frontend.</p></div></div>
    </div>
<?php elseif ($panel === 'contenedores'): ?>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Contenedores del sitio</h3>
                <p>Panel general de bloques. Cada contenedor se edita en su propia página.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle" id="contenedoresTable">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Contenedor</th>
                        <th>Observación</th>
                        <th>Tipo</th>
                        <th>Visible</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sections as $section): ?>
                        <tr>
                            <td><?= (int) $section['orden'] ?></td>
                            <td>
                                <strong><?= cms_e($section['titulo_admin']) ?></strong>
                            </td>
                            <td><div class="text-muted" style="min-width:280px; white-space:normal;"><?= cms_e($section['observacion'] ?? '') ?></div></td>
                            <td><span class="badge-soft dark"><?= cms_e($section['tipo_seccion']) ?></span></td>
                            <td>
                                <form method="post" class="m-0 js-toggle-seccion-form">
                                    <input type="hidden" name="accion" value="toggle_seccion">
                                    <input type="hidden" name="id_seccion" value="<?= (int) $section['id_seccion'] ?>">
                                    <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                        <input class="form-check-input js-toggle-seccion" type="checkbox" role="switch" <?= ($section['visible'] ?? '') === 'si' ? 'checked' : '' ?>>
                                        <label class="form-check-label js-toggle-label"><?= ($section['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?></label>
                                    </div>
                                </form>
                            </td>
                            <td class="cell-actions">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-soft js-preview-btn" data-preview-title="<?= cms_e($section['titulo_admin']) ?>" data-preview-url="preview_contenedor.php?id=<?= (int) $section['id_seccion'] ?>&embed=1">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a class="btn btn-admin-action" href="editar_contenedor.php?id=<?= (int) $section['id_seccion'] ?>&modo=editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php elseif ($panel === 'menus'): ?>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Menú principal</h3>
                <p>Gestionado desde <code>menus</code> sin tocar su lógica actual.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="table-responsive">
                    <table class="table table-modern align-middle" id="menusTable">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Nombre</th>
                                <th>URL</th>
                                <th>Ícono</th>
                                <th>Activo</th>
                                <th>Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menus as $menu): ?>
                                <tr>
                                    <td><?= (int) $menu['orden'] ?></td>
                                    <td><strong><?= cms_e($menu['nombre']) ?></strong></td>
                                    <td><code><?= cms_e($menu['url']) ?></code></td>
                                    <td><?= cms_e($menu['icono']) ?></td>
                                    <td>
                                        <form method="post" class="m-0">
                                            <input type="hidden" name="accion" value="toggle_menu">
                                            <input type="hidden" name="id_menu" value="<?= (int) $menu['id_menu'] ?>">
                                            <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                                <input class="form-check-input" type="checkbox" role="switch" <?= (int) $menu['estado'] === 1 ? 'checked' : '' ?> onchange="this.form.submit()">
                                                <label class="form-check-label"><?= (int) $menu['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                                            </div>
                                        </form>
                                    </td>
                                    <td><a class="btn btn-sm btn-outline-secondary" href="admin.php?panel=menus&menu=<?= (int) $menu['id_menu'] ?>">Editar</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="section-card mb-0">
                    <h3><?= $editingMenu ? 'Editar menú' : 'Nuevo menú' ?></h3>
                    <div class="text-muted mb-3">Edición rápida del menú principal.</div>
                    <form method="post">
                        <input type="hidden" name="accion" value="guardar_menu">
                        <input type="hidden" name="id_menu" value="<?= (int) ($editingMenu['id_menu'] ?? 0) ?>">
                        <div class="mb-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" value="<?= cms_e($editingMenu['nombre'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">URL</label><input class="form-control" name="url" value="<?= cms_e($editingMenu['url'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">Ícono</label><input class="form-control" name="icono" value="<?= cms_e($editingMenu['icono'] ?? '') ?>"></div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Orden</label><input class="form-control" type="number" name="orden" value="<?= (int) ($editingMenu['orden'] ?? count($menus) + 1) ?>"></div>
                            <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="estado" <?= !isset($editingMenu['estado']) || (int) $editingMenu['estado'] === 1 ? 'checked' : '' ?>><label class="form-check-label">Activo</label></div></div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button class="btn btn-premium flex-fill" type="submit">Guardar</button>
                            <?php if ($editingMenu): ?><a class="btn btn-soft" href="admin.php?panel=menus">Cancelar</a><?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php elseif ($panel === 'submenus'): ?>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Submenús</h3>
                <p>Gestionados desde <code>sub_menus</code> sin romper navegación existente.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="table-responsive">
                    <table class="table table-modern align-middle" id="submenusTable">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Nombre</th>
                                <th>Menú padre</th>
                                <th>URL</th>
                                <th>Activo</th>
                                <th>Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submenus as $submenu): ?>
                                <tr>
                                    <td><?= (int) $submenu['orden'] ?></td>
                                    <td><strong><?= cms_e($submenu['nombre']) ?></strong></td>
                                    <td><?= cms_e($submenu['menu_padre']) ?></td>
                                    <td><code><?= cms_e($submenu['url']) ?></code></td>
                                    <td>
                                        <form method="post" class="m-0">
                                            <input type="hidden" name="accion" value="toggle_submenu">
                                            <input type="hidden" name="id_sub_menu" value="<?= (int) $submenu['id_sub_menu'] ?>">
                                            <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                                <input class="form-check-input" type="checkbox" role="switch" <?= (int) $submenu['estado'] === 1 ? 'checked' : '' ?> onchange="this.form.submit()">
                                                <label class="form-check-label"><?= (int) $submenu['estado'] === 1 ? 'Activo' : 'Inactivo' ?></label>
                                            </div>
                                        </form>
                                    </td>
                                    <td><a class="btn btn-sm btn-outline-secondary" href="admin.php?panel=submenus&submenu=<?= (int) $submenu['id_sub_menu'] ?>">Editar</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="section-card mb-0">
                    <h3><?= $editingSubmenu ? 'Editar submenú' : 'Nuevo submenú' ?></h3>
                    <div class="text-muted mb-3">Edición rápida de submenús.</div>
                    <form method="post">
                        <input type="hidden" name="accion" value="guardar_submenu">
                        <input type="hidden" name="id_sub_menu" value="<?= (int) ($editingSubmenu['id_sub_menu'] ?? 0) ?>">
                        <div class="mb-3">
                            <label class="form-label">Menú padre</label>
                            <select class="form-select" name="id_menu">
                                <option value="">Seleccione</option>
                                <?php foreach ($menus as $menu): ?>
                                    <option value="<?= (int) $menu['id_menu'] ?>" <?= ((int) ($editingSubmenu['id_menu'] ?? 0) === (int) $menu['id_menu']) ? 'selected' : '' ?>><?= cms_e($menu['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" value="<?= cms_e($editingSubmenu['nombre'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">URL</label><input class="form-control" name="url" value="<?= cms_e($editingSubmenu['url'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">Ícono</label><input class="form-control" name="icono" value="<?= cms_e($editingSubmenu['icono'] ?? '') ?>"></div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Orden</label><input class="form-control" type="number" name="orden" value="<?= (int) ($editingSubmenu['orden'] ?? 1) ?>"></div>
                            <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="estado" <?= !isset($editingSubmenu['estado']) || (int) $editingSubmenu['estado'] === 1 ? 'checked' : '' ?>><label class="form-check-label">Activo</label></div></div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button class="btn btn-premium flex-fill" type="submit">Guardar</button>
                            <?php if ($editingSubmenu): ?><a class="btn btn-soft" href="admin.php?panel=submenus">Cancelar</a><?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php elseif ($panel === 'configuracion'): ?>
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3>Configuración institucional</h3>
                <p>Datos globales del sitio desde la tabla <code>institucion</code>.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-xl-8">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="guardar_institucion">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nombre del sitio</label><input class="form-control" name="nombre" value="<?= cms_e($institution['nombre'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Correo contacto</label><input class="form-control" name="email" value="<?= cms_e($institution['email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" value="<?= cms_e($institution['telefono'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Dirección</label><input class="form-control" name="direccion" value="<?= cms_e($institution['direccion'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Color principal</label><input class="form-control" name="color_primario" value="<?= cms_e($institution['color_primario'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Color secundario</label><input class="form-control" name="color_secundario" value="<?= cms_e($institution['color_secundario'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Facebook</label><input class="form-control" name="facebook" value="<?= cms_e($institution['facebook'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Instagram</label><input class="form-control" name="instagram" value="<?= cms_e($institution['instagram'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Logo</label><input class="form-control" type="file" name="logo_header" accept="image/*"></div>
                        <div class="col-md-6"><label class="form-label">Favicon</label><input class="form-control" type="file" name="favicon" accept="image/*,.ico"></div>
                    </div>
                    <div class="mt-4">
                        <button class="btn btn-premium" type="submit"><i class="bi bi-save me-2"></i>Guardar configuración</button>
                    </div>
                </form>
            </div>
            <div class="col-xl-4">
                <div class="section-card mb-0">
                    <h3>Vista rápida</h3>
                    <p class="text-muted">Resumen de identidad institucional.</p>
                    <div class="mb-3"><strong><?= cms_e($institution['nombre'] ?? '') ?></strong></div>
                    <div class="mb-2 text-muted"><?= cms_e($institution['email'] ?? '') ?></div>
                    <div class="mb-3 text-muted"><?= cms_e($institution['telefono'] ?? '') ?></div>
                    <div class="d-flex gap-2">
                        <div style="width:56px;height:56px;border-radius:16px;background:<?= cms_e($institution['color_primario'] ?? '#2563EB') ?>;"></div>
                        <div style="width:56px;height:56px;border-radius:16px;background:<?= cms_e($institution['color_secundario'] ?? '#E9A629') ?>;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:24px; overflow:hidden; border:0;">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Vista previa del contenedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0" style="background:#f8fafc;">
                <iframe id="previewFrame" title="Vista previa del contenedor" style="width:100%; min-height:70vh; border:0; display:block;" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>

<?php
admin_render_layout_end([
    'extra_scripts' => <<<'HTML'
    <script>
        $(function () {
            ['#contenedoresTable', '#menusTable', '#submenusTable'].forEach(function (selector) {
                if ($(selector).length) {
                    $(selector).DataTable({
                        pageLength: 10,
                        order: [[0, 'asc']],
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
                        }
                    });
                }
            });

            $('.js-toggle-seccion').on('change', function () {
                var checkbox = this;
                var form = checkbox.closest('.js-toggle-seccion-form');
                var label = form.querySelector('.js-toggle-label');
                var formData = new FormData(form);
                var previousState = !checkbox.checked;

                checkbox.disabled = true;

                fetch('admin.php?panel=contenedores', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('No se pudo actualizar la visibilidad.');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        if (!data.ok) {
                            throw new Error(data.message || 'No se pudo actualizar la visibilidad.');
                        }
                        checkbox.checked = data.visible === 'si';
                        label.textContent = data.label;
                    })
                    .catch(function (error) {
                        checkbox.checked = previousState;
                        label.textContent = previousState ? 'Activo' : 'Oculto';
                        window.alert(error.message);
                    })
                    .finally(function () {
                        checkbox.disabled = false;
                    });
            });

            $('.js-preview-btn').on('click', function () {
                var button = this;
                var title = button.getAttribute('data-preview-title') || 'Vista previa del contenedor';
                var url = button.getAttribute('data-preview-url');
                var modalEl = document.getElementById('previewModal');
                var titleEl = document.getElementById('previewModalLabel');
                var frameEl = document.getElementById('previewFrame');

                titleEl.textContent = 'Vista previa: ' + title;
                frameEl.src = url;
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });

            document.getElementById('previewModal').addEventListener('hidden.bs.modal', function () {
                document.getElementById('previewFrame').src = 'about:blank';
            });
        });
    </script>
HTML,
]);
