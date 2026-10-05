<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_once __DIR__ . '/includes/funciones_auditoria.php';

const ADMIN_CALENDAR_DEFAULT_COLOR = '#0d6efd';
const ADMIN_CALENDAR_HOLIDAY_COLOR = '#dc3545';

function admin_calendar_year(): int
{
    $year = (int) ($_GET['anio'] ?? $_POST['anio'] ?? date('Y'));
    if ($year < 2000 || $year > 2100) {
        return (int) date('Y');
    }
    return $year;
}

function admin_calendar_week_days(mysqli $db): array
{
    $days = [];
    if (cms_table_exists($db, 'dia_semana')) {
        $result = $db->query('SELECT id_dia_semana, nombre, orden, es_fin_semana FROM dia_semana ORDER BY orden ASC');
        while ($row = $result?->fetch_assoc()) {
            $days[(int) $row['orden']] = [
                'id' => (int) $row['id_dia_semana'],
                'nombre' => (string) $row['nombre'],
                'orden' => (int) $row['orden'],
                'es_fin_semana' => (int) $row['es_fin_semana'],
            ];
        }
    }

    if ($days) {
        return $days;
    }

    return [
        1 => ['id' => 1, 'nombre' => 'Lunes', 'orden' => 1, 'es_fin_semana' => 0],
        2 => ['id' => 2, 'nombre' => 'Martes', 'orden' => 2, 'es_fin_semana' => 0],
        3 => ['id' => 3, 'nombre' => 'Miércoles', 'orden' => 3, 'es_fin_semana' => 0],
        4 => ['id' => 4, 'nombre' => 'Jueves', 'orden' => 4, 'es_fin_semana' => 0],
        5 => ['id' => 5, 'nombre' => 'Viernes', 'orden' => 5, 'es_fin_semana' => 0],
        6 => ['id' => 6, 'nombre' => 'Sábado', 'orden' => 6, 'es_fin_semana' => 1],
        7 => ['id' => 7, 'nombre' => 'Domingo', 'orden' => 7, 'es_fin_semana' => 1],
    ];
}

function admin_calendar_exists_year(mysqli $db, int $year): bool
{
    $stmt = $db->prepare('SELECT COUNT(*) AS total FROM calendario WHERE ano = ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('i', $year);
    $stmt->execute();
    $row = $stmt->get_result()?->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0) > 0;
}

function admin_calendar_generate_year(mysqli $db, int $year): int
{
    if (admin_calendar_exists_year($db, $year)) {
        throw new RuntimeException('El calendario del año seleccionado ya existe.');
    }

    $weekDays = admin_calendar_week_days($db);
    $start = new DateTimeImmutable($year . '-01-01');
    $end = new DateTimeImmutable($year . '-12-31');
    $stmt = $db->prepare("
        INSERT INTO calendario
        (fecha, ano, mes, dia, numero_dia_semana, nombre_dia_semana, es_fin_semana, es_feriado, es_dia_habil, nombre_feriado, descripcion_feriado, tipo, color, visible)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, NULL, NULL, 'normal', ?, 1)
    ");
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar la generación del calendario.');
    }

    $created = 0;
    for ($date = $start; $date <= $end; $date = $date->modify('+1 day')) {
        $isoDay = (int) $date->format('N');
        $dayInfo = $weekDays[$isoDay] ?? $weekDays[1];
        $dateText = $date->format('Y-m-d');
        $month = (int) $date->format('n');
        $day = (int) $date->format('j');
        $dayNumber = (int) ($dayInfo['orden'] ?? $isoDay);
        $dayName = (string) ($dayInfo['nombre'] ?? $date->format('l'));
        $isWeekend = (int) ($dayInfo['es_fin_semana'] ?? in_array($isoDay, [6, 7], true));
        $isBusiness = $isWeekend ? 0 : 1;
        $color = ADMIN_CALENDAR_DEFAULT_COLOR;
        $stmt->bind_param('siiiisiis', $dateText, $year, $month, $day, $dayNumber, $dayName, $isWeekend, $isBusiness, $color);
        $stmt->execute();
        $created++;
    }
    $stmt->close();

    return $created;
}

function admin_calendar_uruguay_holidays(int $year): array
{
    return [
        $year . '-01-01' => 'Año Nuevo',
        $year . '-05-01' => 'Día de los Trabajadores',
        $year . '-07-18' => 'Jura de la Constitución',
        $year . '-08-25' => 'Declaratoria de la Independencia',
        $year . '-12-25' => 'Navidad',
    ];
}

function admin_calendar_load_uruguay_holidays(mysqli $db, int $year): int
{
    if (!admin_calendar_exists_year($db, $year)) {
        throw new RuntimeException('Primero debes generar el año seleccionado.');
    }

    $holidays = admin_calendar_uruguay_holidays($year);
    $stmt = $db->prepare("
        UPDATE calendario
           SET es_feriado = 1,
               es_dia_habil = 0,
               tipo = 'feriado',
               nombre_feriado = ?,
               descripcion_feriado = 'Feriado nacional en Uruguay.',
               color = ?,
               visible = 1
         WHERE fecha = ?
           AND ano = ?
         LIMIT 1
    ");
    if (!$stmt) {
        throw new RuntimeException('No se pudieron preparar los feriados.');
    }

    $updated = 0;
    $color = ADMIN_CALENDAR_HOLIDAY_COLOR;
    foreach ($holidays as $date => $name) {
        $stmt->bind_param('sssi', $name, $color, $date, $year);
        $stmt->execute();
        $updated += $stmt->affected_rows > 0 ? 1 : 0;
    }
    $stmt->close();

    return $updated;
}

function admin_calendar_list_days(mysqli $db, int $year, int $month, string $filter): array
{
    $where = ['ano = ?'];
    $types = 'i';
    $values = [$year];

    if ($month >= 1 && $month <= 12) {
        $where[] = 'mes = ?';
        $types .= 'i';
        $values[] = $month;
    }
    if ($filter === 'feriado') {
        $where[] = 'es_feriado = 1';
    } elseif ($filter === 'habil') {
        $where[] = 'es_dia_habil = 1';
    } elseif ($filter === 'fin_semana') {
        $where[] = 'es_fin_semana = 1';
    }

    $sql = 'SELECT * FROM calendario WHERE ' . implode(' AND ', $where) . ' ORDER BY fecha ASC';
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $rows = $stmt->get_result()?->fetch_all(MYSQLI_ASSOC) ?? [];
    $stmt->close();
    return $rows;
}

function admin_calendar_save_days(mysqli $db, array $post): int
{
    $ids = array_map('intval', (array) ($post['id_calendario'] ?? []));
    if (!$ids) {
        return 0;
    }

    $stmt = $db->prepare("
        UPDATE calendario
           SET es_feriado = ?,
               nombre_feriado = ?,
               tipo = ?,
               color = ?,
               es_dia_habil = ?,
               visible = ?
         WHERE id_calendario = ?
         LIMIT 1
    ");
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar el guardado.');
    }

    $saved = 0;
    foreach ($ids as $id) {
        if ($id <= 0) {
            continue;
        }
        $holiday = isset($post['es_feriado'][$id]) ? 1 : 0;
        $business = isset($post['es_dia_habil'][$id]) ? 1 : 0;
        $visible = isset($post['visible'][$id]) ? 1 : 0;
        $name = trim((string) ($post['nombre_feriado'][$id] ?? ''));
        $type = trim((string) ($post['tipo'][$id] ?? 'normal'));
        if (!in_array($type, ['normal', 'feriado', 'institucional', 'academico', 'vacaciones', 'suspension'], true)) {
            $type = 'normal';
        }
        $color = trim((string) ($post['color'][$id] ?? ''));
        if (!preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $color)) {
            $color = $holiday ? ADMIN_CALENDAR_HOLIDAY_COLOR : ADMIN_CALENDAR_DEFAULT_COLOR;
        }
        $stmt->bind_param('isssiii', $holiday, $name, $type, $color, $business, $visible, $id);
        $stmt->execute();
        $saved++;
    }
    $stmt->close();
    return $saved;
}

$db = cms_get_connection();
$site = cms_get_site_data($db);
$year = admin_calendar_year();
$month = max(0, min(12, (int) ($_GET['mes'] ?? 0)));
$filter = in_array(($_GET['filtro'] ?? ''), ['feriado', 'habil', 'fin_semana'], true) ? (string) $_GET['filtro'] : '';
$canEditCalendar = admin_tiene_permiso('calendario', 'editar');

try {
    admin_requerir_permiso('calendario', 'ver');
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
    cms_redirect('admin.php?panel=dashboard');
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string) ($_POST['accion'] ?? '');
        $year = admin_calendar_year();
        if (!$canEditCalendar) {
            throw new RuntimeException('No tienes permiso para modificar el calendario.');
        }

        if ($action === 'generar_anio') {
            $created = admin_calendar_generate_year($db, $year);
            registrarAuditoria($db, 'Calendario institucional', 'calendario', null, 'crear', 'Se generó un año calendario institucional', null, ['ano' => $year, 'dias' => $created]);
            cms_set_flash('success', 'Calendario ' . $year . ' generado con ' . $created . ' días.');
            cms_redirect('admin_calendario.php?anio=' . $year);
        }

        if ($action === 'cargar_feriados_uy') {
            $updated = admin_calendar_load_uruguay_holidays($db, $year);
            registrarAuditoria($db, 'Calendario institucional', 'calendario', null, 'importar', 'Se cargaron feriados nacionales de Uruguay', null, ['ano' => $year, 'actualizados' => $updated]);
            cms_set_flash('success', 'Feriados de Uruguay cargados para ' . $year . '. Registros actualizados: ' . $updated . '.');
            cms_redirect('admin_calendario.php?anio=' . $year . '&filtro=feriado');
        }

        if ($action === 'guardar_dias') {
            $saved = admin_calendar_save_days($db, $_POST);
            registrarAuditoria($db, 'Calendario institucional', 'calendario', null, 'editar', 'Se actualizaron días del calendario institucional', null, ['ano' => $year, 'dias' => $saved]);
            cms_set_flash('success', 'Días actualizados: ' . $saved . '.');
            $redirect = 'admin_calendario.php?anio=' . $year . '&mes=' . (int) ($_POST['mes_actual'] ?? 0) . '&filtro=' . urlencode((string) ($_POST['filtro_actual'] ?? ''));
            cms_redirect($redirect);
        }
    }
} catch (Throwable $exception) {
    cms_set_flash('danger', $exception->getMessage());
    cms_redirect('admin_calendario.php?anio=' . $year);
}

$flash = cms_get_flash();
$days = admin_calendar_list_days($db, $year, $month, $filter);
$yearExists = admin_calendar_exists_year($db, $year);
$summary = [
    'total' => count(admin_calendar_list_days($db, $year, 0, '')),
    'holidays' => count(admin_calendar_list_days($db, $year, 0, 'feriado')),
    'business' => count(admin_calendar_list_days($db, $year, 0, 'habil')),
    'weekend' => count(admin_calendar_list_days($db, $year, 0, 'fin_semana')),
];
$months = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
];

admin_render_layout_start([
    'title' => 'Calendario institucional | Colegio San Pablo',
    'page_title' => 'Calendario institucional',
    'breadcrumb' => 'Sistema / Calendario institucional',
    'active_panel' => 'calendario',
    'institution_name' => $site['institution']['nombre'] ?? 'Institución activa',
    'institution_short_name' => $site['institution']['nombre_corto'] ?? ($site['institution']['nombre'] ?? 'Institución'),
    'institution_logo' => $site['institution']['logo_header'] ?? '',
    'color_primario' => $site['institution']['color_primario'] ?? '',
    'color_secundario' => $site['institution']['color_secundario'] ?? '',
    'color_terciario' => $site['institution']['color_terciario'] ?? '',
    'color_cuaternario' => $site['institution']['color_cuaternario'] ?? '',
    'admin_name' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'extra_head' => '<link rel="stylesheet" href="assets/css/admin_calendario.css">',
]);
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= cms_e($flash['type']) ?> alert-dismissible fade show" role="alert" data-calendar-flash="<?= cms_e($flash['type']) ?>" data-calendar-flash-message="<?= cms_e($flash['message']) ?>">
        <?= cms_e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="calendar-admin-page">
    <section class="section-card calendar-admin-hero">
        <div>
            <h3>Calendario institucional</h3>
            <p>Administra los días del año, feriados nacionales, días hábiles y marcas visibles para el calendario público.</p>
        </div>
        <form class="calendar-admin-year" method="get">
            <label for="anio">Año</label>
            <input class="form-control" id="anio" type="number" name="anio" min="2000" max="2100" value="<?= (int) $year ?>">
            <button class="btn btn-soft" type="submit"><i class="bi bi-search me-1"></i>Ver año</button>
        </form>
    </section>

    <div class="calendar-admin-stats">
        <div class="calendar-stat"><strong><?= (int) $summary['total'] ?></strong><span>Días del año</span></div>
        <div class="calendar-stat"><strong><?= (int) $summary['holidays'] ?></strong><span>Feriados</span></div>
        <div class="calendar-stat"><strong><?= (int) $summary['business'] ?></strong><span>Días hábiles</span></div>
        <div class="calendar-stat"><strong><?= (int) $summary['weekend'] ?></strong><span>Fines de semana</span></div>
    </div>

    <?php if ($yearExists): ?>
        <div class="alert alert-warning">El calendario del año seleccionado ya existe.</div>
    <?php endif; ?>

    <section class="section-card calendar-admin-actions">
        <form method="post" class="js-calendar-protected-action">
            <input type="hidden" name="accion" value="generar_anio">
            <input type="hidden" name="anio" value="<?= (int) $year ?>">
            <button type="submit" class="btn btn-premium" <?= $yearExists ? 'disabled' : '' ?><?= $canEditCalendar ? '' : ' data-admin-denied="No tienes permiso para modificar el calendario."' ?>>
                <i class="bi bi-calendar-plus me-1"></i>Generar año
            </button>
        </form>
        <form method="post" class="js-calendar-protected-action">
            <input type="hidden" name="accion" value="cargar_feriados_uy">
            <input type="hidden" name="anio" value="<?= (int) $year ?>">
            <button type="submit" class="btn btn-outline-danger" <?= $yearExists ? '' : 'disabled' ?><?= $canEditCalendar ? '' : ' data-admin-denied="No tienes permiso para modificar el calendario."' ?>>
                <i class="bi bi-flag me-1"></i>Cargar feriados de Uruguay
            </button>
        </form>
    </section>

    <section class="section-card calendar-admin-filters">
        <form method="get">
            <input type="hidden" name="anio" value="<?= (int) $year ?>">
            <div>
                <label class="form-label" for="mes">Mes</label>
                <select class="form-select" id="mes" name="mes">
                    <option value="0">Todos los meses</option>
                    <?php foreach ($months as $monthNumber => $monthName): ?>
                        <option value="<?= (int) $monthNumber ?>" <?= $month === $monthNumber ? 'selected' : '' ?>><?= cms_e($monthName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label" for="filtro">Tipo de día</label>
                <select class="form-select" id="filtro" name="filtro">
                    <option value="">Todos</option>
                    <option value="feriado" <?= $filter === 'feriado' ? 'selected' : '' ?>>Feriados</option>
                    <option value="habil" <?= $filter === 'habil' ? 'selected' : '' ?>>Días hábiles</option>
                    <option value="fin_semana" <?= $filter === 'fin_semana' ? 'selected' : '' ?>>Fin de semana</option>
                </select>
            </div>
            <button class="btn btn-soft" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
        </form>
    </section>

    <form method="post" class="section-card calendar-admin-table-card">
        <input type="hidden" name="accion" value="guardar_dias">
        <input type="hidden" name="anio" value="<?= (int) $year ?>">
        <input type="hidden" name="mes_actual" value="<?= (int) $month ?>">
        <input type="hidden" name="filtro_actual" value="<?= cms_e($filter) ?>">
        <div class="calendar-admin-table-head">
            <div>
                <h3>Días del calendario <?= (int) $year ?></h3>
                <p><?= count($days) ?> registros visibles con el filtro actual.</p>
            </div>
            <button type="submit" class="btn btn-premium"<?= $canEditCalendar ? '' : ' data-admin-denied="No tienes permiso para modificar el calendario."' ?>>
                <i class="bi bi-save me-1"></i>Guardar cambios
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle calendar-admin-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Día</th>
                        <th>Feriado</th>
                        <th>Nombre feriado</th>
                        <th>Tipo</th>
                        <th>Color</th>
                        <th>Hábil</th>
                        <th>Visible</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($days as $day): ?>
                        <?php $dayId = (int) ($day['id_calendario'] ?? 0); ?>
                        <tr>
                            <td>
                                <input type="hidden" name="id_calendario[]" value="<?= $dayId ?>">
                                <strong><?= cms_e(date('d-m-Y', strtotime((string) $day['fecha']))) ?></strong>
                                <small><?= cms_e($months[(int) $day['mes']] ?? '') ?></small>
                            </td>
                            <td><?= cms_e($day['nombre_dia_semana'] ?? '') ?></td>
                            <td><input class="form-check-input" type="checkbox" name="es_feriado[<?= $dayId ?>]" value="1" <?= (int) ($day['es_feriado'] ?? 0) === 1 ? 'checked' : '' ?> <?= $canEditCalendar ? '' : 'disabled' ?>></td>
                            <td><input class="form-control form-control-sm" name="nombre_feriado[<?= $dayId ?>]" value="<?= cms_e($day['nombre_feriado'] ?? '') ?>" <?= $canEditCalendar ? '' : 'readonly' ?>></td>
                            <td>
                                <select class="form-select form-select-sm" name="tipo[<?= $dayId ?>]" <?= $canEditCalendar ? '' : 'disabled' ?>>
                                    <?php foreach (['normal', 'feriado', 'institucional', 'academico', 'vacaciones', 'suspension'] as $type): ?>
                                        <option value="<?= cms_e($type) ?>" <?= ($day['tipo'] ?? '') === $type ? 'selected' : '' ?>><?= cms_e(ucfirst($type)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input class="form-control form-control-color" type="color" name="color[<?= $dayId ?>]" value="<?= cms_e($day['color'] ?: ADMIN_CALENDAR_DEFAULT_COLOR) ?>" <?= $canEditCalendar ? '' : 'disabled' ?>></td>
                            <td><input class="form-check-input" type="checkbox" name="es_dia_habil[<?= $dayId ?>]" value="1" <?= (int) ($day['es_dia_habil'] ?? 0) === 1 ? 'checked' : '' ?> <?= $canEditCalendar ? '' : 'disabled' ?>></td>
                            <td><input class="form-check-input" type="checkbox" name="visible[<?= $dayId ?>]" value="1" <?= (int) ($day['visible'] ?? 1) === 1 ? 'checked' : '' ?> <?= $canEditCalendar ? '' : 'disabled' ?>></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$days): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No hay días para el año y filtros seleccionados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<?php
admin_render_layout_end([
    'extra_scripts' => <<<'HTML'
    <script>
    (function () {
        document.querySelectorAll('.js-calendar-protected-action').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var denied = form.querySelector('[data-admin-denied]');
                if (denied) {
                    event.preventDefault();
                    window.adminNotifyDenied ? window.adminNotifyDenied('No tienes permiso para modificar el calendario.') : alert('No tienes permiso para modificar el calendario.');
                }
            });
        });
    })();
    </script>
HTML,
]);
