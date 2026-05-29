<?php
require_once __DIR__ . '/includes/cms_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache');

$startParam = $_GET['start'] ?? '';
$endParam   = $_GET['end']   ?? '';

if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $startParam) || !preg_match('/^\d{4}-\d{2}-\d{2}/', $endParam)) {
    echo '[]';
    exit;
}

$dateFrom = substr($startParam, 0, 10);
$dateTo   = substr($endParam,   0, 10);

function cal_normalize_category(string $cat): string
{
    $cat = strtolower(trim($cat));
    return str_replace(
        ['á','é','í','ó','ú','ü','ñ',' '],
        ['a','e','i','o','u','u','n','-'],
        $cat
    );
}

function cal_color_valid(?string $v, string $d): string
{
    $v = trim((string) $v);
    return preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $v) ? $v : $d;
}

try {
    $db            = cms_get_connection();
    $institutionId = cms_get_institution_id($db);

    $instResult = $db->query(
        'SELECT color_primario, color_secundario, color_terciario, color_cuaternario
           FROM institucion
          WHERE id_institucion = ' . (int) $institutionId . '
          LIMIT 1'
    );
    $inst = $instResult ? ($instResult->fetch_assoc() ?: []) : [];

    $colorPrimario    = cal_color_valid($inst['color_primario']    ?? null, '#2060B0');
    $colorSecundario  = cal_color_valid($inst['color_secundario']  ?? null, '#E8A030');
    $colorTerciario   = cal_color_valid($inst['color_terciario']   ?? null, '#2D7D9A');
    $colorCuaternario = cal_color_valid($inst['color_cuaternario'] ?? null, '#D94535');

    // Mismo mapeo categoría → color que el CSS existente
    $categoryColors = [
        'academico'    => $colorPrimario,
        'pastoral'     => $colorSecundario,
        'deportivo'    => $colorTerciario,
        'institucional'=> $colorCuaternario,
    ];

    $output = [];

    // Eventos institucionales
    $publicEvents = cms_list_public_events($db, $dateFrom, $dateTo, 300);
    foreach ($publicEvents as $evt) {
        $catNorm = cal_normalize_category((string) ($evt['categoria'] ?? ''));
        $color   = $categoryColors[$catNorm] ?? $colorPrimario;

        $startDate = (string) ($evt['fecha_inicio'] ?? '');
        if ($startDate === '') {
            continue;
        }
        $hora    = !empty($evt['hora_inicio']) ? substr((string) $evt['hora_inicio'], 0, 5) : '';
        $endDate = null;
        if (!empty($evt['fecha_termino']) && $evt['fecha_termino'] > $startDate) {
            $endDate = date('Y-m-d', strtotime($evt['fecha_termino'] . ' +1 day'));
        }

        $output[] = [
            'id'    => 'ev_' . (int) $evt['id_evento'],
            'title' => $evt['titulo'] ?? '',
            'start' => $hora !== '' ? $startDate . 'T' . $hora . ':00' : $startDate,
            'end'   => $endDate,
            'color' => $color,
            'url'   => 'evento_detalle.php?id_evento=' . (int) $evt['id_evento'],
            'extendedProps' => [
                'type'      => 'evento',
                'categoria' => $evt['categoria'] ?? '',
                'hora'      => $hora,
                'ubicacion' => $evt['ubicacion'] ?? '',
            ],
        ];
    }

    // Feriados desde tabla calendario (como eventos de fondo)
    $calDays = cms_list_calendar_days($db, $dateFrom, $dateTo);
    foreach ($calDays as $date => $day) {
        $isHoliday = !empty($day['es_feriado']) || (($day['tipo'] ?? '') === 'feriado');
        if ($isHoliday && !empty($day['nombre_feriado'])) {
            $output[] = [
                'id'         => 'feriado_' . $date,
                'title'      => (string) $day['nombre_feriado'],
                'start'      => $date,
                'display'    => 'background',
                'color'      => '#FFF3CD',
                'classNames' => ['fc-feriado-bg'],
                'extendedProps' => ['type' => 'feriado'],
            ];
        }
    }

    echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    echo '[]';
}
