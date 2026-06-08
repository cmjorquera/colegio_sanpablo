<?php
$sectionName = $section['nombre_interno'] ?? 'calendario_eventos_home';
$title    = cfg($sectionConfigsMap, $sectionName, 'titulo_bloque',   'Calendario de eventos');
$subtitle = cfg($sectionConfigsMap, $sectionName, 'subtitulo_bloque','Actividades y fechas institucionales');
$limit    = max(3, (int) cfg($sectionConfigsMap, $sectionName, 'cantidad_items', '6'));

$calendarColor = static function (?string $value, string $fallback): string {
    $value = trim((string) $value);
    return preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value) ? $value : $fallback;
};
$calendarPrimary    = $calendarColor($institution['color_primario']    ?? null, '#2060B0');
$calendarSecondary  = $calendarColor($institution['color_secundario']  ?? null, '#E8A030');
$calendarTertiary   = $calendarColor($institution['color_terciario']   ?? null, '#2D2D2D');
$calendarQuaternary = $calendarColor($institution['color_cuaternario'] ?? null, '#D94535');
$calendarStyle = sprintf(
    '--calendar-primary:%s;--calendar-secondary:%s;--calendar-tertiary:%s;--calendar-quaternary:%s;',
    $calendarPrimary, $calendarSecondary, $calendarTertiary, $calendarQuaternary
);

// RGB del color primario para rgba() en CSS
$hexToRgb = static function (string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    return hexdec(substr($hex, 0, 2)) . ',' . hexdec(substr($hex, 2, 2)) . ',' . hexdec(substr($hex, 4, 2));
};
$calendarPrimaryRgb = $hexToRgb($calendarPrimary);

// Próximos eventos para la barra lateral (desde hoy en adelante)
$todayStr  = date('Y-m-d');
$futureStr = date('Y-m-d', strtotime('+120 days'));
$upcoming  = isset($db) ? cms_list_public_events($db, $todayStr, $futureStr, $limit) : [];

$eventCategoryClass = static function (?string $category): string {
    $normalized = strtolower(trim((string) $category));
    $normalized = str_replace(
        ['á','é','í','ó','ú','ñ',' '],
        ['a','e','i','o','u','n','-'],
        $normalized
    );
    return preg_replace('/[^a-z0-9_-]/', '', $normalized) ?: 'institucional';
};
?>

<section class="sp-calendario-eventos" id="calendario-eventos-home" style="<?= e($calendarStyle) ?>">
    <div class="container">
        <div class="section-header-four text-center">
            <h5><?= e($subtitle) ?></h5>
            <h2><?= e($title) ?></h2>
        </div>

        <div class="calendario-eventos-layout">
            <div class="class-time__table">
                <div id="fc-calendario-home"></div>
            </div>

            <div class="calendario-eventos-next">
                <h3>Próximos eventos</h3>
                <?php if ($upcoming): ?>
                    <?php foreach ($upcoming as $event): ?>
                        <?php
                        $eventDate     = !empty($event['fecha_inicio']) ? new DateTime($event['fecha_inicio']) : null;
                        $categoryClass = $eventCategoryClass($event['categoria'] ?? 'institucional');
                        ?>
                        <a class="calendario-event-card <?= e($categoryClass) ?>" href="evento_detalle.php?id_evento=<?= (int) ($event['id_evento'] ?? 0) ?>">
                            <span class="event-date-box">
                                <strong><?= $eventDate ? e($eventDate->format('d')) : '' ?></strong>
                                <small><?= $eventDate ? e(strtoupper($eventDate->format('M'))) : '' ?></small>
                            </span>
                            <span class="event-card-body">
                                <strong><?= e($event['titulo'] ?? '') ?></strong>
                                <em><?= e($event['categoria'] ?? 'Institucional') ?></em>
                                <small><?= e(trim(($event['hora_inicio'] ?? '') . ' ' . ($event['ubicacion'] ?? ''))) ?></small>
                            </span>
                            <i class="fa-light fa-arrow-right-long"></i>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="calendario-event-card institucional">
                        <span class="event-card-body">
                            <strong>Sin próximos eventos</strong>
                            <em>Los feriados aparecen en el calendario.</em>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* ---- FullCalendar overrides institucionales ---- */
#fc-calendario-home {
    --fc-border-color: #e8edf4;
    --fc-page-bg-color: #ffffff;
    --fc-neutral-bg-color: #f8f9fc;
    --fc-button-bg-color: <?= e($calendarPrimary) ?>;
    --fc-button-border-color: <?= e($calendarPrimary) ?>;
    --fc-button-text-color: #ffffff;
    --fc-button-hover-bg-color: <?= e($calendarSecondary) ?>;
    --fc-button-hover-border-color: <?= e($calendarSecondary) ?>;
    --fc-button-active-bg-color: <?= e($calendarSecondary) ?>;
    --fc-button-active-border-color: <?= e($calendarSecondary) ?>;
    --fc-today-bg-color: rgba(<?= e($calendarPrimaryRgb) ?>, 0.09);
    --fc-non-business-color: rgba(0,0,0,.025);
    font-family: inherit;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0,0,0,.07);
}
#fc-calendario-home .fc-toolbar {
    padding: 12px 16px;
    background: #fff;
    margin-bottom: 0 !important;
}
#fc-calendario-home .fc-toolbar-title {
    font-size: 18px;
    font-weight: 800;
    color: #0f1720;
    text-transform: capitalize;
}
#fc-calendario-home .fc-button-primary {
    border-radius: 50% !important;
    width: 36px;
    height: 36px;
    padding: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: background .2s, border-color .2s;
}
#fc-calendario-home .fc-button-primary:focus,
#fc-calendario-home .fc-button-primary:focus-visible {
    box-shadow: 0 0 0 3px rgba(<?= e($calendarPrimaryRgb) ?>, 0.25) !important;
    outline: none;
}
#fc-calendario-home .fc-col-header {
    background: <?= e($calendarPrimary) ?>;
}
#fc-calendario-home .fc-col-header-cell {
    background: <?= e($calendarPrimary) ?>;
    padding: 12px 0;
}
#fc-calendario-home .fc-col-header-cell-cushion {
    color: #fff !important;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    text-decoration: none !important;
}
#fc-calendario-home .fc-daygrid-day-number {
    color: #8b98a5;
    font-size: 13px;
    padding: 5px 8px;
    text-decoration: none !important;
}
#fc-calendario-home .fc-day-today .fc-daygrid-day-number {
    color: <?= e($calendarPrimary) ?> !important;
    font-weight: 800;
}
#fc-calendario-home .fc-daygrid-day.fc-day-other {
    background: #fbfbfb;
}
#fc-calendario-home .fc-event {
    border: none;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    padding: 1px 5px;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
#fc-calendario-home .fc-event:hover {
    filter: brightness(0.92);
}
#fc-calendario-home .fc-daygrid-more-link {
    font-size: 11px;
    color: <?= e($calendarPrimary) ?>;
    font-weight: 700;
}
#fc-calendario-home .fc-popover {
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.14);
    border: 1px solid #e8edf4;
}
#fc-calendario-home .fc-popover-header {
    background: <?= e($calendarPrimary) ?>;
    color: #fff;
    border-radius: 10px 10px 0 0;
    font-size: 12px;
    font-weight: 700;
}
#fc-calendario-home .fc-feriado-bg {
    opacity: .3;
}
#fc-calendario-home .fc-scrollgrid {
    border-radius: 0;
}
#fc-calendario-home .fc-scrollgrid td:last-child,
#fc-calendario-home .fc-scrollgrid th:last-child {
    border-right: none;
}
@media (max-width: 767px) {
    #fc-calendario-home .fc-toolbar-title { font-size: 15px; }
    #fc-calendario-home .fc-col-header-cell { padding: 8px 0; }
    #fc-calendario-home .fc-col-header-cell-cushion { font-size: 10px; }
    #fc-calendario-home .fc-daygrid-day-number { font-size: 11px; padding: 3px 4px; }
    #fc-calendario-home .fc-event { font-size: 9px; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
(function () {
    function initCalendario() {
        var calEl = document.getElementById('fc-calendario-home');
        if (!calEl || !window.FullCalendar) {
            return;
        }

        var calendar = new FullCalendar.Calendar(calEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            firstDay: 1,
            headerToolbar: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            height: 'auto',
            dayMaxEvents: 3,
            moreLinkText: function (n) { return '+' + n + ' más'; },
            events: {
                url: 'calendario_eventos_json.php',
                failure: function () {
                    console.warn('[Calendario] No se pudieron cargar los eventos.');
                }
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            eventClick: function (info) {
                var props = info.event.extendedProps || {};
                if (props.type === 'feriado') {
                    info.jsEvent.preventDefault();
                    return;
                }
                if (info.event.url) {
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                }
            },
            eventDidMount: function (info) {
                var props = info.event.extendedProps || {};
                if (props.type === 'feriado') {
                    return;
                }
                var parts = [info.event.title];
                if (props.hora)      { parts.push(props.hora); }
                if (props.ubicacion) { parts.push(props.ubicacion); }
                info.el.setAttribute('title', parts.join(' · '));
                info.el.style.cursor = 'pointer';
            }
        });

        calendar.render();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendario);
    } else {
        initCalendario();
    }
})();
</script>
