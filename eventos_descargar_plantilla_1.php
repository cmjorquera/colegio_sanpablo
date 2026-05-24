<?php
session_start();

if (empty($_SESSION['admin_logged'])) {
    header('Location: index_1.php');
    exit;
}

$columns = ['titulo', 'descripcion_corta', 'descripcion', 'fecha_inicio', 'fecha_termino', 'hora_inicio', 'hora_termino', 'categoria', 'ubicacion', 'color', 'destacado', 'visible', 'estado', 'orden'];
$rows = [
    $columns,
    ['Misa Institucional', 'Eucaristía en comunidad.', 'Descripción completa del evento.', '2026-05-22', '2026-05-22', '09:00', '10:00', 'Pastoral', 'Capilla del Colegio', '#8e44ad', '1', '1', 'publicado', '1'],
];

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="plantilla_eventos_calendario.csv"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");
foreach ($rows as $row) {
    fputcsv($output, $row, ';');
}
fclose($output);
