<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: colegiosanpablo.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';

$db = cms_get_connection();
$institutionId = cms_get_institution_id($db);
cms_sync_sections($db, $institutionId);

$idSeccion = (int) ($_GET['id'] ?? $_POST['id_seccion'] ?? 0);
$section = $idSeccion > 0 ? cms_get_section($db, $idSeccion) : null;

if (!$section) {
    cms_set_flash('danger', 'El contenedor solicitado no existe.');
    cms_redirect('admin.php?panel=contenedores');
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['accion'] ?? '';

        if ($action === 'guardar_seccion') {
            cms_save_section($db, $idSeccion, $_POST);
            cms_set_flash('success', 'El contenedor fue actualizado correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion);
        }

        if ($action === 'guardar_item') {
            cms_save_item($db, $section, $_POST);
            cms_set_flash('success', 'El item fue guardado correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }

        if ($action === 'eliminar_item') {
            cms_delete_item($db, (int) ($_POST['id_item'] ?? 0));
            cms_set_flash('success', 'El item fue eliminado correctamente.');
            cms_redirect('editar_contenedor.php?id=' . $idSeccion . '&tab=items');
        }
    }
} catch (Throwable $e) {
    cms_set_flash('danger', $e->getMessage());
    cms_redirect('editar_contenedor.php?id=' . $idSeccion);
}

$flash = cms_get_flash();
$configs = cms_get_section_configs($db, $idSeccion);
$items = cms_get_section_items($db, $idSeccion);
$site = cms_get_site_data($db);
$categories = array_values($site['categories']);
$editingItem = isset($_GET['item']) ? cms_get_item($db, (int) $_GET['item']) : null;
$openModal = $_GET['modal'] ?? '';
$tab = $_GET['tab'] ?? 'general';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar contenedor | <?= cms_e($section['titulo_admin']) ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --sp-primary: #1f8f6b;
            --sp-primary-soft: #eaf7f2;
            --sp-secondary: #12324a;
            --sp-bg: #f4f7fb;
            --sp-border: #dbe4ef;
            --sp-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }
        body { background: var(--sp-bg); font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; color: #12324a; }
        .wrap { max-width: 1420px; margin: 0 auto; padding: 26px; }
        .card-ui { background: #fff; border-radius: 24px; box-shadow: var(--sp-shadow); border: 1px solid #eef2f7; padding: 22px; margin-bottom: 22px; }
        .hero-card { border: 1px solid #dbe4ef; border-radius: 22px; overflow: hidden; background: #fff; height: 100%; }
        .hero-thumb { height: 180px; background-size: cover; background-position: center; }
        .badge-soft { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; font-weight: 700; font-size: .82rem; }
        .badge-soft.success { background: #e8fbf2; color: #1b8f67; }
        .badge-soft.warning { background: #fff6e0; color: #b7791f; }
        .form-control, .form-select { border-radius: 16px; min-height: 48px; border-color: #dbe4ef; }
        textarea.form-control { min-height: 120px; }
        .nav-pills .nav-link.active { background: linear-gradient(135deg, #1f8f6b, #27b785); }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card-ui">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <a href="admin.php?panel=contenedores" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i>Volver al panel</a>
                    <h1 class="h3 mb-1"><?= cms_e($section['titulo_admin']) ?></h1>
                    <div class="text-muted">
                        <code><?= cms_e($section['nombre_interno']) ?></code> · tipo <code><?= cms_e($section['tipo_seccion']) ?></code>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="preview_contenedor.php?id=<?= (int) $section['id_seccion'] ?>" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-eye me-1"></i>Visualizar</a>
                    <a href="<?= cms_e(cms_get_preview_target($section['nombre_interno'])) ?>" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-1"></i>Ver en sitio</a>
                </div>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= cms_e($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= cms_e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card-ui">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item"><a class="nav-link <?= $tab === 'general' ? 'active' : '' ?>" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=general">General</a></li>
                <li class="nav-item"><a class="nav-link <?= $tab === 'items' ? 'active' : '' ?>" href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items">Items</a></li>
            </ul>
        </div>

        <?php if ($tab === 'general'): ?>
            <div class="card-ui">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h2 class="h5 mb-1">Configuración del contenedor</h2>
                        <div class="text-muted">Visible, orden, observación y claves guardadas en <code>seccion_config</code>.</div>
                    </div>
                </div>
                <form method="post">
                    <input type="hidden" name="accion" value="guardar_seccion">
                    <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Visible</label>
                            <select class="form-select" name="visible">
                                <option value="si" <?= ($section['visible'] ?? '') === 'si' ? 'selected' : '' ?>>Si</option>
                                <option value="no" <?= ($section['visible'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Orden</label>
                            <input class="form-control" type="number" name="orden" min="1" value="<?= (int) $section['orden'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Observación</label>
                            <input class="form-control" type="text" name="observacion" value="<?= cms_e($section['observacion'] ?? '') ?>">
                        </div>
                    </div>
                    <div id="configRows">
                        <?php foreach ($configs as $index => $config): ?>
                            <div class="row g-3 align-items-end mb-3 config-row">
                                <div class="col-md-4">
                                    <label class="form-label">Clave</label>
                                    <input class="form-control" name="config_key[]" value="<?= cms_e($config['clave']) ?>">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Valor</label>
                                    <input class="form-control" name="config_value[]" value="<?= cms_e($config['valor']) ?>">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger w-100 remove-config-row"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$configs): ?>
                            <div class="row g-3 align-items-end mb-3 config-row">
                                <div class="col-md-4"><label class="form-label">Clave</label><input class="form-control" name="config_key[]" placeholder="titulo_bloque"></div>
                                <div class="col-md-7"><label class="form-label">Valor</label><input class="form-control" name="config_value[]" placeholder="Últimas Noticias"></div>
                                <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-config-row"><i class="bi bi-trash"></i></button></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" id="addConfigRow" class="btn btn-outline-secondary"><i class="bi bi-plus-circle me-1"></i>Agregar configuración</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Guardar contenedor</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="card-ui">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h2 class="h5 mb-1">Items del contenedor</h2>
                        <div class="text-muted">Administración específica del bloque.</div>
                    </div>
                    <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i>Agregar item</a>
                </div>

                <?php if (in_array($section['tipo_seccion'], ['carousel', 'hero'], true)): ?>
                    <div class="row g-4 mb-4">
                        <?php foreach ($items as $item): ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="hero-card">
                                    <div class="hero-thumb" style="background-image:url('<?= cms_e($item['imagen'] ?: 'assets/images/portada_1.jpg') ?>')"></div>
                                    <div class="p-3">
                                        <span class="badge-soft <?= ($item['visible'] ?? '') === 'si' ? 'success' : 'warning' ?>"><?= ($item['visible'] ?? '') === 'si' ? 'Activo' : 'Oculto' ?></span>
                                        <h5 class="mt-3 mb-1"><?= cms_e(trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? ''))) ?></h5>
                                        <small class="text-muted">Orden <?= (int) $item['orden'] ?></small>
                                        <p class="text-muted mt-2 mb-3"><?= cms_e($item['etiqueta'] ?? '') ?></p>
                                        <div class="d-flex gap-2">
                                            <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                            <form method="post" onsubmit="return confirm('¿Eliminar este item?');">
                                                <input type="hidden" name="accion" value="eliminar_item">
                                                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                                <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Orden</th>
                                <th>Título</th>
                                <th>Visible</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= (int) $item['orden'] ?></td>
                                    <td>
                                        <?php
                                        $displayTitle = $item['titulo'] ?: trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? ''));
                                        ?>
                                        <?= cms_e($displayTitle) ?>
                                    </td>
                                    <td><span class="badge-soft <?= ($item['visible'] ?? '') === 'si' ? 'success' : 'warning' ?>"><?= ($item['visible'] ?? '') === 'si' ? 'Si' : 'No' ?></span></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="editar_contenedor.php?id=<?= (int) $idSeccion ?>&tab=items&modal=item&item=<?= (int) $item['id_item'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                            <form method="post" onsubmit="return confirm('¿Eliminar este item?');">
                                                <input type="hidden" name="accion" value="eliminar_item">
                                                <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                                                <input type="hidden" name="id_item" value="<?= (int) $item['id_item'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:24px; border:0;">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="guardar_item">
                    <input type="hidden" name="id_seccion" value="<?= (int) $idSeccion ?>">
                    <input type="hidden" name="id_item" value="<?= (int) ($editingItem['id_item'] ?? 0) ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= $editingItem ? 'Editar item' : 'Agregar item' ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (in_array($section['tipo_seccion'], ['carousel', 'hero'], true)): ?>
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Etiqueta</label><input class="form-control" name="etiqueta" value="<?= cms_e($editingItem['etiqueta'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Título línea 1</label><input class="form-control" name="titulo_linea_1" value="<?= cms_e($editingItem['titulo_linea_1'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Título línea 2</label><input class="form-control" name="titulo_linea_2" value="<?= cms_e($editingItem['titulo_linea_2'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Título línea 3</label><input class="form-control" name="titulo_linea_3" value="<?= cms_e($editingItem['titulo_linea_3'] ?? '') ?>"></div>
                                <div class="col-md-8"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea></div>
                                <div class="col-md-3"><label class="form-label">Botón 1 texto</label><input class="form-control" name="boton_1_texto" value="<?= cms_e($editingItem['boton_1_texto'] ?? '') ?>"></div>
                                <div class="col-md-3"><label class="form-label">Botón 1 URL</label><input class="form-control" name="boton_1_url" value="<?= cms_e($editingItem['boton_1_url'] ?? '') ?>"></div>
                                <div class="col-md-3"><label class="form-label">Botón 2 texto</label><input class="form-control" name="boton_2_texto" value="<?= cms_e($editingItem['boton_2_texto'] ?? '') ?>"></div>
                                <div class="col-md-3"><label class="form-label">Botón 2 URL</label><input class="form-control" name="boton_2_url" value="<?= cms_e($editingItem['boton_2_url'] ?? '') ?>"></div>
                            </div>
                        <?php elseif ($section['tipo_seccion'] === 'news'): ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" name="id_categoria">
                                        <option value="">Seleccione</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= (int) $category['id_categoria'] ?>" <?= ((int) ($editingItem['id_categoria'] ?? 0) === (int) $category['id_categoria']) ? 'selected' : '' ?>><?= cms_e($category['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-8"><label class="form-label">Título</label><input class="form-control" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Etiqueta visual</label><input class="form-control" name="etiqueta" value="<?= cms_e($editingItem['etiqueta'] ?? '') ?>"></div>
                                <div class="col-md-8"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea></div>
                                <div class="col-md-4"><label class="form-label">Fecha publicación</label><input class="form-control" type="date" name="fecha_publicacion" value="<?= cms_e($editingItem['fecha_publicacion'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Botón texto</label><input class="form-control" name="boton_1_texto" value="<?= cms_e($editingItem['boton_1_texto'] ?? 'Leer más') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Botón URL</label><input class="form-control" name="boton_1_url" value="<?= cms_e($editingItem['boton_1_url'] ?? '#') ?>"></div>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Título</label><input class="form-control" name="titulo" value="<?= cms_e($editingItem['titulo'] ?? '') ?>"></div>
                                <div class="col-md-6"><label class="form-label">Subtítulo</label><input class="form-control" name="subtitulo" value="<?= cms_e($editingItem['subtitulo'] ?? '') ?>"></div>
                                <div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion"><?= cms_e($editingItem['descripcion'] ?? '') ?></textarea></div>
                            </div>
                        <?php endif; ?>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Imagen</label><input class="form-control" type="file" name="imagen" accept="image/*"></div>
                            <?php if (in_array($section['tipo_seccion'], ['carousel', 'hero'], true)): ?>
                                <div class="col-md-6"><label class="form-label">Imagen mobile</label><input class="form-control" type="file" name="imagen_mobile" accept="image/*"></div>
                            <?php endif; ?>
                            <div class="col-md-3"><label class="form-label">Visible</label><select class="form-select" name="visible"><option value="si" <?= ($editingItem['visible'] ?? 'si') === 'si' ? 'selected' : '' ?>>Si</option><option value="no" <?= ($editingItem['visible'] ?? '') === 'no' ? 'selected' : '' ?>>No</option></select></div>
                            <div class="col-md-3"><label class="form-label">Orden</label><input class="form-control" type="number" name="orden" min="1" value="<?= (int) ($editingItem['orden'] ?? count($items) + 1) ?>"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success">Guardar item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="configRowTemplate">
        <div class="row g-3 align-items-end mb-3 config-row">
            <div class="col-md-4"><label class="form-label">Clave</label><input class="form-control" name="config_key[]" placeholder="clave"></div>
            <div class="col-md-7"><label class="form-label">Valor</label><input class="form-control" name="config_value[]" placeholder="valor"></div>
            <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-config-row"><i class="bi bi-trash"></i></button></div>
        </div>
    </template>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            if ($('#itemsTable').length) {
                $('#itemsTable').DataTable({
                    pageLength: 10,
                    order: [[0, 'asc']],
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
                });
            }

            $('#addConfigRow').on('click', function () {
                const tpl = document.getElementById('configRowTemplate');
                document.getElementById('configRows').appendChild(tpl.content.cloneNode(true));
            });

            $(document).on('click', '.remove-config-row', function () {
                const rows = document.querySelectorAll('#configRows .config-row');
                if (rows.length === 1) {
                    rows[0].querySelectorAll('input').forEach(function (input) { input.value = ''; });
                    return;
                }
                this.closest('.config-row').remove();
            });

            const openModal = <?= json_encode($openModal, JSON_UNESCAPED_UNICODE) ?>;
            if (openModal === 'item') {
                const modal = new bootstrap.Modal(document.getElementById('itemModal'));
                modal.show();
            }
        });
    </script>
</body>
</html>
