<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_permissions.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_once __DIR__ . '/includes/funciones_auditoria.php';

const ADMIN_FERIADO_TIPOS = ['normal', 'feriado', 'institucional', 'academico', 'vacaciones', 'suspension'];

// Edicion puntual de un dia de calendario. Solo toca las columnas propias del
// feriado (nombre_feriado, descripcion_feriado, tipo, color, visible, es_feriado,
// es_dia_habil). No crea, edita ni elimina nada en eventos ni evento_media.
function admin_feriado_cargar(mysqli $db, int $idCalendario): ?array
{
    if ($idCalendario <= 0) {
        return null;
    }
    $stmt = $db->prepare('SELECT * FROM calendario WHERE id_calendario = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $idCalendario);
    $stmt->execute();
    $row = $stmt->get_result()?->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function admin_feriado_guardar(mysqli $db, int $idCalendario, array $post): void
{
    $nombre = trim((string) ($post['nombre_feriado'] ?? ''));
    $descripcion = trim((string) ($post['descripcion_feriado'] ?? ''));
    $tipo = trim((string) ($post['tipo'] ?? 'feriado'));
    if (!in_array($tipo, ADMIN_FERIADO_TIPOS, true)) {
        $tipo = 'feriado';
    }
    $color = trim((string) ($post['color'] ?? ''));
    if (!preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $color)) {
        $color = '#C6005A';
    }
    $esFeriado = !empty($post['es_feriado']) ? 1 : 0;
    $esDiaHabil = !empty($post['es_dia_habil']) ? 1 : 0;
    $visible = !empty($post['visible']) ? 1 : 0;

    if ($esFeriado && $nombre === '') {
        throw new RuntimeException('Debes ingresar un nombre de feriado.');
    }

    $stmt = $db->prepare('
        UPDATE calendario
           SET nombre_feriado = ?,
               descripcion_feriado = ?,
               tipo = ?,
               color = ?,
               es_feriado = ?,
               es_dia_habil = ?,
               visible = ?
         WHERE id_calendario = ?
         LIMIT 1
    ');
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar el guardado del feriado.');
    }
    $nombreParam = $nombre !== '' ? $nombre : null;
    $descripcionParam = $descripcion !== '' ? $descripcion : null;
    $stmt->bind_param('ssssiiii', $nombreParam, $descripcionParam, $tipo, $color, $esFeriado, $esDiaHabil, $visible, $idCalendario);
    $stmt->execute();
    $stmt->close();
}

$db = cms_get_connection();
$site = cms_get_site_data($db);
$idCalendario = (int) ($_GET['id_calendario'] ?? $_POST['id_calendario'] ?? 0);
$idSeccionOrigen = (int) ($_GET['id_seccion'] ?? $_POST['id_seccion'] ?? 0);
$volverUrl = $idSeccionOrigen > 0
    ? 'editar_contenedor.php?id=' . $idSeccionOrigen . '&tab=items'
    : 'admin_calendario.php';

try {
    admin_requerir_permiso('calendario', 'ver');
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
    cms_redirect('admin.php?panel=dashboard');
}

$canEdit = admin_tiene_permiso('calendario', 'editar');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$canEdit) {
            throw new RuntimeException('No tienes permiso para editar feriados.');
        }
        if ($idCalendario <= 0) {
            throw new RuntimeException('Feriado no válido.');
        }
        $antes = admin_feriado_cargar($db, $idCalendario);
        if (!$antes) {
            throw new RuntimeException('El día de calendario indicado no existe.');
        }
        admin_feriado_guardar($db, $idCalendario, $_POST);
        $despues = admin_feriado_cargar($db, $idCalendario);
        registrarAuditoria($db, 'Calendario institucional', 'calendario', $idCalendario, 'editar', 'Se actualizó un feriado del calendario institucional', $antes, $despues);
        cms_set_flash('success', 'Feriado actualizado correctamente.');
    } catch (Throwable $exception) {
        cms_set_flash('danger', $exception->getMessage());
    }
    cms_redirect('admin_feriado_editar.php?id_calendario=' . $idCalendario . '&id_seccion=' . $idSeccionOrigen);
}

$holiday = admin_feriado_cargar($db, $idCalendario);
$flash = cms_get_flash();

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return cms_e($value);
    }
}

admin_render_layout_start([
    'title' => 'Editar feriado | Colegio San Pablo',
    'page_title' => 'Editar feriado',
    'breadcrumb' => 'Calendario institucional / Editar feriado',
    'active_panel' => 'calendario',
    'institution_name' => $site['institution']['nombre'] ?? 'Institución activa',
    'institution_short_name' => $site['institution']['nombre_corto'] ?? ($site['institution']['nombre'] ?? 'Institución'),
    'institution_logo' => $site['institution']['logo_header'] ?? '',
    'color_primario' => $site['institution']['color_primario'] ?? '',
    'color_secundario' => $site['institution']['color_secundario'] ?? '',
    'color_terciario' => $site['institution']['color_terciario'] ?? '',
    'color_cuaternario' => $site['institution']['color_cuaternario'] ?? '',
    'admin_name' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'header_actions' => '<a href="' . e($volverUrl) . '" class="btn btn-soft"><i class="bi bi-arrow-left me-2"></i>Volver</a>',
]);
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!$holiday): ?>
    <div class="section-card">
        <h3>Feriado no encontrado</h3>
        <p class="text-muted">El día de calendario solicitado no existe.</p>
        <a href="<?= e($volverUrl) ?>" class="btn btn-soft"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
<?php else: ?>
    <div class="section-card">
        <div class="section-head">
            <div>
                <h3>Editar feriado</h3>
                <p>
                    <?= e(date('d-m-Y', strtotime((string) $holiday['fecha']))) ?>
                    · <?= e($holiday['nombre_dia_semana'] ?? '') ?>
                    · Este formulario solo modifica la tabla <code>calendario</code>. No crea ni edita eventos ni evento_media.
                </p>
            </div>
        </div>

        <form method="post">
            <input type="hidden" name="id_calendario" value="<?= (int) $idCalendario ?>">
            <input type="hidden" name="id_seccion" value="<?= (int) $idSeccionOrigen ?>">

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nombre del feriado</label>
                    <input class="form-control" name="nombre_feriado" value="<?= e($holiday['nombre_feriado'] ?? '') ?>" placeholder="Ej: Año Nuevo" <?= $canEdit ? '' : 'readonly' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" name="tipo" <?= $canEdit ? '' : 'disabled' ?>>
                        <?php foreach (ADMIN_FERIADO_TIPOS as $tipoOpcion): ?>
                            <option value="<?= e($tipoOpcion) ?>" <?= ($holiday['tipo'] ?? '') === $tipoOpcion ? 'selected' : '' ?>><?= e(ucfirst($tipoOpcion)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción del feriado</label>
                    <textarea class="form-control" name="descripcion_feriado" rows="3" placeholder="Texto breve para el detalle público" <?= $canEdit ? '' : 'readonly' ?>><?= e($holiday['descripcion_feriado'] ?? '') ?></textarea>
                    <small class="text-muted">Si se deja vacío, el detalle público muestra un texto neutro por defecto.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Color</label>
                    <input class="form-control form-control-color" type="color" name="color" value="<?= e($holiday['color'] ?: '#C6005A') ?>" <?= $canEdit ? '' : 'disabled' ?>>
                </div>
                <div class="col-md-8 d-flex align-items-end gap-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="es_feriado" name="es_feriado" value="1" <?= (int) ($holiday['es_feriado'] ?? 0) === 1 ? 'checked' : '' ?> <?= $canEdit ? '' : 'disabled' ?>>
                        <label class="form-check-label" for="es_feriado">Es feriado</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="es_dia_habil" name="es_dia_habil" value="1" <?= (int) ($holiday['es_dia_habil'] ?? 0) === 1 ? 'checked' : '' ?> <?= $canEdit ? '' : 'disabled' ?>>
                        <label class="form-check-label" for="es_dia_habil">Día hábil</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="visible" name="visible" value="1" <?= (int) ($holiday['visible'] ?? 1) === 1 ? 'checked' : '' ?> <?= $canEdit ? '' : 'disabled' ?>>
                        <label class="form-check-label" for="visible">Visible públicamente</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-premium" <?= $canEdit ? '' : 'data-admin-denied="No tienes permiso para editar feriados."' ?>><i class="bi bi-save me-1"></i>Guardar feriado</button>
                <a href="feriado_detalle.php?id_calendario=<?= (int) $idCalendario ?>" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-eye me-1"></i>Ver feriado público</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php
admin_render_layout_end();
