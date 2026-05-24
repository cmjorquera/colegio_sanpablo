<?php
$sectionName = $section['nombre_interno'] ?? 'calendario_eventos_home';
$title = cfg($sectionConfigsMap, $sectionName, 'titulo_bloque', 'Calendario de eventos');
$subtitle = cfg($sectionConfigsMap, $sectionName, 'subtitulo_bloque', 'Actividades y fechas institucionales');
$monthTitle = cfg($sectionConfigsMap, $sectionName, 'mes_titulo', date('Y-m'));
$limit = max(3, (int) cfg($sectionConfigsMap, $sectionName, 'cantidad_items', '6'));

$monthDate = DateTime::createFromFormat('Y-m', $monthTitle) ?: new DateTime('first day of this month');
$monthStart = $monthDate->format('Y-m-01');
$monthEnd = $monthDate->format('Y-m-t');
$events = isset($db) ? cms_list_public_events($db, $monthStart, $monthEnd, 80) : [];
if (!$events && $monthDate->format('Y-m') === date('Y-m')) {
    $events = isset($db) ? cms_list_public_events($db, date('Y-m-01'), date('Y-m-t'), 80) : [];
}
$calendarDays = isset($db) ? cms_list_calendar_days($db, $monthStart, $monthEnd) : [];

$eventsByDate = [];
foreach ($events as $event) {
    $date = (string) ($event['fecha_inicio'] ?? '');
    if ($date !== '') {
        $eventsByDate[$date][] = $event;
    }
}

$firstDay = new DateTime($monthStart);
$lastDay = new DateTime($monthEnd);
$startOffset = ((int) $firstDay->format('N')) - 1;
$totalCells = (int) ceil(($startOffset + (int) $lastDay->format('j')) / 7) * 7;
$current = clone $firstDay;
$current->modify('-' . $startOffset . ' days');
$upcoming = array_slice($events, 0, $limit);
?>

<section class="sp-calendario-eventos" id="calendario-eventos-home">
    <div class="container">
        <div class="section-header-four text-center">
            <h5><?= e($subtitle) ?></h5>
            <h2><?= e($title) ?></h2>
        </div>

        <div class="calendario-eventos-layout">
            <div class="class-time__table">
                <table class="calendario-eventos-table">
                    <thead>
                        <tr>
                            <th>Lun</th>
                            <th>Mar</th>
                            <th>Mie</th>
                            <th>Jue</th>
                            <th>Vie</th>
                            <th>Sab</th>
                            <th>Dom</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($cell = 0; $cell < $totalCells; $cell++): ?>
                            <?php if ($cell % 7 === 0): ?><tr><?php endif; ?>
                            <?php
                            $dateKey = $current->format('Y-m-d');
                            $isCurrentMonth = $current->format('m') === $monthDate->format('m');
                            $dayEvents = $eventsByDate[$dateKey] ?? [];
                            $calendarDay = $calendarDays[$dateKey] ?? [];
                            $isHoliday = !empty($calendarDay['es_feriado']) || (($calendarDay['tipo'] ?? '') === 'feriado');
                            $isWeekend = !empty($calendarDay['es_fin_semana']) || (int) $current->format('N') >= 6;
                            $cellClass = ['calendar-day'];
                            if (!$isCurrentMonth) {
                                $cellClass[] = 'calendar-empty';
                            }
                            if ($isWeekend) {
                                $cellClass[] = 'is-weekend';
                            }
                            if ($isHoliday) {
                                $cellClass[] = 'is-holiday';
                            }
                            if ($dayEvents) {
                                $cellClass[] = 'event-day';
                                $cellClass[] = strtolower((string) ($dayEvents[0]['categoria'] ?? 'institucional'));
                            }
                            ?>
                            <td class="<?= e(implode(' ', $cellClass)) ?>" <?= !empty($calendarDay['color']) ? 'style="--day-color:' . e($calendarDay['color']) . '"' : '' ?>>
                                <?php if ($isCurrentMonth): ?>
                                    <span class="calendar-day-number"><?= (int) $current->format('j') ?></span>
                                    <?php if ($isHoliday && !empty($calendarDay['nombre_feriado'])): ?>
                                        <span class="calendar-event-title"><?= e($calendarDay['nombre_feriado']) ?></span>
                                        <span class="calendar-event-meta">Feriado</span>
                                    <?php endif; ?>
                                    <?php if ($dayEvents): ?>
                                        <a class="calendar-event-title" href="evento_detalle.php?id_evento=<?= (int) ($dayEvents[0]['id_evento'] ?? 0) ?>"><?= e($dayEvents[0]['titulo'] ?? '') ?></a>
                                        <span class="calendar-event-meta"><?= e($dayEvents[0]['hora_inicio'] ?? '') ?></span>
                                        <?php if (count($dayEvents) > 1): ?><span class="calendar-event-more">+<?= count($dayEvents) - 1 ?></span><?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <?php if ($cell % 7 === 6): ?></tr><?php endif; ?>
                            <?php $current->modify('+1 day'); ?>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="calendario-eventos-next">
                <h3>Proximos eventos</h3>
                <?php if ($upcoming): ?>
                    <?php foreach ($upcoming as $event): ?>
                        <?php
                        $eventDate = !empty($event['fecha_inicio']) ? new DateTime($event['fecha_inicio']) : null;
                        $categoryClass = strtolower((string) ($event['categoria'] ?? 'institucional'));
                        ?>
                        <a class="calendario-event-card <?= e($categoryClass) ?>" href="evento_detalle.php?id_evento=<?= (int) ($event['id_evento'] ?? 0) ?>">
                            <span class="event-date-box">
                                <strong><?= $eventDate ? e($eventDate->format('d')) : '' ?></strong>
                                <small><?= $eventDate ? e(strtoupper($eventDate->format('M'))) : '' ?></small>
                            </span>
                            <span class="event-card-body">
                                <strong><?= e($event['titulo'] ?? '') ?></strong>
                                <em><?= e($event['categoria'] ?? 'Institucional') ?></em>
                                <small><?= e(trim((string) ($event['hora_inicio'] ?? '') . ' ' . (string) ($event['ubicacion'] ?? ''))) ?></small>
                            </span>
                            <i class="fa-light fa-arrow-right-long"></i>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="calendario-event-card institucional">
                        <span class="event-card-body">
                            <strong>Sin eventos cargados</strong>
                            <em>Los feriados institucionales siguen visibles en el calendario.</em>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
