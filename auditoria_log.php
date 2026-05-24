<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index_1.php');
    exit;
}

require_once __DIR__ . '/includes/cms_helpers.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_once __DIR__ . '/includes/funciones_auditoria.php';

$db = cms_get_connection();
$site = cms_get_site_data($db);
$estadoTabla = auditoriaTablaLista($db);
$filtros = [
    'id_usuario' => $_GET['id_usuario'] ?? '',
    'modulo' => $_GET['modulo'] ?? '',
    'accion' => $_GET['accion'] ?? '',
    'fecha_desde' => $_GET['fecha_desde'] ?? '',
    'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
];
$logs = $estadoTabla['ok'] ? auditoriaListarLogs($db, $filtros) : [];
$usuarios = auditoriaListarUsuarios($db);
$modulos = $estadoTabla['ok'] ? auditoriaValoresUnicos($db, 'modulo') : [];
$acciones = $estadoTabla['ok'] ? auditoriaValoresUnicos($db, 'accion') : auditoriaAccionesPermitidas();

admin_render_layout_start([
    'title' => 'Auditoría / Logs | Colegio San Pablo',
    'page_title' => 'Auditoría / Logs',
    'breadcrumb' => 'Panel institucional / Auditoría',
    'active_panel' => 'auditoria',
    'institution_name' => $site['institution']['nombre'] ?? 'Institución activa',
    'institution_short_name' => $site['institution']['nombre_corto'] ?? ($site['institution']['nombre'] ?? 'Institución'),
    'institution_logo' => $site['institution']['logo_header'] ?? '',
    'admin_name' => $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador',
    'header_actions' => '<a href="admin_1.php?panel=contenedores" class="btn btn-soft"><i class="bi bi-arrow-left me-2"></i>Volver al panel</a>',
    'extra_head' => <<<'HTML'
    <style>
        .audit-filter-grid { display:grid; grid-template-columns: repeat(5, minmax(140px, 1fr)); gap:12px; }
        .audit-json {
            background:#0f172a;
            color:#e2e8f0;
            border-radius:12px;
            padding:14px;
            max-height:340px;
            overflow:auto;
            font-size:.82rem;
            white-space:pre-wrap;
        }
        .audit-diff-list { display:grid; gap:8px; }
        .audit-diff-item { border:1px solid #e4ebf5; border-radius:12px; padding:10px; background:#fff; }
        .audit-diff-item strong { color:#162338; }
        @media (max-width: 1199px) { .audit-filter-grid { grid-template-columns: repeat(2, minmax(140px, 1fr)); } }
        @media (max-width: 575px) { .audit-filter-grid { grid-template-columns: 1fr; } }
    </style>
HTML,
]);
?>

<?php if (!$estadoTabla['ok']): ?>
    <div class="alert alert-warning">
        <strong>Auditoría pendiente de activar.</strong>
        La tabla <code>auditoria_log</code> no existe o le faltan columnas esperadas:
        <code><?= cms_e(implode(', ', $estadoTabla['missing'])) ?></code>.
    </div>
<?php endif; ?>

<section class="section-card">
    <div class="section-head">
        <div>
            <h3>Filtros de auditoría</h3>
            <p class="text-muted mb-0">Consulta cambios realizados por administradores del CMS.</p>
        </div>
    </div>
    <form method="get" class="audit-filter-grid align-items-end">
        <div>
            <label class="form-label">Usuario</label>
            <select class="form-select" name="id_usuario">
                <option value="">Todos</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <?php $nombreUsuario = trim((string) ($usuario['nombre'] ?? '') . ' ' . (string) ($usuario['apellido'] ?? '')); ?>
                    <option value="<?= (int) $usuario['id_usuario'] ?>" <?= (string) $filtros['id_usuario'] === (string) $usuario['id_usuario'] ? 'selected' : '' ?>>
                        <?= cms_e($nombreUsuario !== '' ? $nombreUsuario : (($usuario['usuario'] ?? '') ?: ($usuario['email'] ?? 'Usuario'))) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Módulo</label>
            <select class="form-select" name="modulo">
                <option value="">Todos</option>
                <?php foreach ($modulos as $modulo): ?>
                    <option value="<?= cms_e($modulo) ?>" <?= (string) $filtros['modulo'] === $modulo ? 'selected' : '' ?>><?= cms_e($modulo) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Acción</label>
            <select class="form-select" name="accion">
                <option value="">Todas</option>
                <?php foreach ($acciones as $accion): ?>
                    <option value="<?= cms_e($accion) ?>" <?= (string) $filtros['accion'] === $accion ? 'selected' : '' ?>><?= cms_e($accion) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Fecha desde</label>
            <input class="form-control" type="date" name="fecha_desde" value="<?= cms_e((string) $filtros['fecha_desde']) ?>">
        </div>
        <div>
            <label class="form-label">Fecha hasta</label>
            <input class="form-control" type="date" name="fecha_hasta" value="<?= cms_e((string) $filtros['fecha_hasta']) ?>">
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-premium" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            <a class="btn btn-soft" href="auditoria_log.php">Limpiar</a>
        </div>
    </form>
</section>

<section class="section-card">
    <div class="section-head">
        <div>
            <h3>Historial de cambios</h3>
            <p class="text-muted mb-0"><?= count($logs) ?> registros encontrados.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-modern align-middle" id="auditoriaTable">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Módulo</th>
                    <th>Acción</th>
                    <th>Tabla</th>
                    <th>ID</th>
                    <th>Descripción</th>
                    <th>IP</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <?php
                    $logId = (int) ($log['id_log'] ?? 0);
                    $usuarioNombre = trim((string) ($log['nombre'] ?? '') . ' ' . (string) ($log['apellido'] ?? ''));
                    $usuarioNombre = $usuarioNombre !== '' ? $usuarioNombre : (string) (($log['usuario'] ?? '') ?: ($log['email'] ?? 'Sistema'));
                    ?>
                    <tr>
                        <td><?= cms_e($log['fecha_hora'] ?? '') ?></td>
                        <td><?= cms_e($usuarioNombre) ?></td>
                        <td><span class="badge-soft dark"><?= cms_e($log['rol'] ?? '') ?></span></td>
                        <td><?= cms_e($log['modulo'] ?? '') ?></td>
                        <td><span class="badge-soft <?= in_array(($log['accion'] ?? ''), ['error', 'eliminar', 'cancelar'], true) ? 'warning' : 'success' ?>"><?= cms_e($log['accion'] ?? '') ?></span></td>
                        <td><code><?= cms_e($log['tabla_afectada'] ?? '') ?></code></td>
                        <td><?= cms_e((string) ($log['id_registro'] ?? '')) ?></td>
                        <td><div style="min-width:240px;white-space:normal;"><?= cms_e($log['descripcion'] ?? '') ?></div></td>
                        <td><?= cms_e($log['ip_usuario'] ?? '') ?></td>
                        <td><button type="button" class="btn btn-sm btn-outline-secondary js-audit-detail" data-log-id="<?= $logId ?>"><i class="bi bi-eye me-1"></i>Ver detalle</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="offcanvas offcanvas-end" tabindex="-1" id="auditDetailCanvas" aria-labelledby="auditDetailTitle" style="width:min(760px, 100vw);">
    <div class="offcanvas-header" style="background:#0f4c81;color:#fff;">
        <div>
            <h5 class="offcanvas-title" id="auditDetailTitle">Detalle de auditoría</h5>
            <small id="auditDetailSubtitle"></small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body bg-light">
        <div id="auditGeneral" class="mb-3"></div>
        <h6>Comparación</h6>
        <div id="auditDiff" class="audit-diff-list mb-3"></div>
        <div class="row g-3">
            <div class="col-lg-6">
                <h6>Datos antes</h6>
                <pre class="audit-json" id="auditBefore">{}</pre>
            </div>
            <div class="col-lg-6">
                <h6>Datos después</h6>
                <pre class="audit-json" id="auditAfter">{}</pre>
            </div>
        </div>
    </div>
</div>

<?php
$logsById = [];
foreach ($logs as $log) {
    $logId = (int) ($log['id_log'] ?? 0);
    $logsById[$logId] = $log;
}

admin_render_layout_end([
    'extra_scripts' => '<script>window.auditLogs = ' . json_encode($logsById, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';</script>' . <<<'HTML'
    <script>
        function parseAuditJson(value) {
            if (!value) {
                return null;
            }
            try {
                return JSON.parse(value);
            } catch (error) {
                return value;
            }
        }

        function prettyAudit(value) {
            var parsed = parseAuditJson(value);
            return JSON.stringify(parsed || {}, null, 2);
        }

        function buildDiff(beforeValue, afterValue) {
            var before = parseAuditJson(beforeValue) || {};
            var after = parseAuditJson(afterValue) || {};
            if (typeof before !== 'object' || typeof after !== 'object' || Array.isArray(before) || Array.isArray(after)) {
                return '<div class="text-muted">Comparación visual disponible para objetos JSON simples.</div>';
            }
            var keys = Array.from(new Set(Object.keys(before).concat(Object.keys(after))));
            var changed = keys.filter(function (key) {
                return JSON.stringify(before[key] ?? null) !== JSON.stringify(after[key] ?? null);
            });
            if (!changed.length) {
                return '<div class="text-muted">No se detectaron diferencias de primer nivel.</div>';
            }
            return changed.slice(0, 20).map(function (key) {
                return '<div class="audit-diff-item"><strong>' + key + '</strong><div class="small text-muted">Antes: ' + escapeHtml(JSON.stringify(before[key] ?? null)) + '</div><div class="small text-muted">Después: ' + escapeHtml(JSON.stringify(after[key] ?? null)) + '</div></div>';
            }).join('');
        }

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (char) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
            });
        }

        $(function () {
            if ($('#auditoriaTable').length) {
                $('#auditoriaTable').DataTable({
                    pageLength: 25,
                    order: [[0, 'desc']],
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
                });
            }

            $('.js-audit-detail').on('click', function () {
                var id = this.getAttribute('data-log-id');
                var log = window.auditLogs[id];
                if (!log) {
                    return;
                }
                $('#auditDetailSubtitle').text((log.fecha_hora || '') + ' · ' + (log.accion || ''));
                $('#auditGeneral').html(
                    '<div class="section-card mb-0">' +
                    '<div><strong>Módulo:</strong> ' + escapeHtml(log.modulo || '') + '</div>' +
                    '<div><strong>Tabla:</strong> ' + escapeHtml(log.tabla_afectada || '') + ' #' + escapeHtml(log.id_registro || '') + '</div>' +
                    '<div><strong>Descripción:</strong> ' + escapeHtml(log.descripcion || '') + '</div>' +
                    '<div><strong>IP:</strong> ' + escapeHtml(log.ip_usuario || '') + '</div>' +
                    '<div><strong>User agent:</strong> ' + escapeHtml(log.user_agent || '') + '</div>' +
                    '</div>'
                );
                $('#auditBefore').text(prettyAudit(log.datos_antes));
                $('#auditAfter').text(prettyAudit(log.datos_despues));
                $('#auditDiff').html(buildDiff(log.datos_antes, log.datos_despues));
                bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('auditDetailCanvas')).show();
            });
        });
    </script>
HTML,
]);
?>
