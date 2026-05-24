<?php

function eventos_excel_columns(): array
{
    return [
        'titulo',
        'descripcion_corta',
        'descripcion',
        'fecha_inicio',
        'fecha_termino',
        'hora_inicio',
        'hora_termino',
        'categoria',
        'ubicacion',
        'color',
        'destacado',
        'visible',
        'orden',
    ];
}

function eventos_excel_help_rows(): array
{
    return [
        ['titulo', 'Nombre público del evento. Obligatorio.', 'Feria Cientifica'],
        ['descripcion_corta', 'Resumen breve para listados y tarjetas.', 'Actividad abierta a familias.'],
        ['descripcion', 'Descripción completa del evento.', 'Jornada con muestras de proyectos.'],
        ['fecha_inicio', 'Fecha inicial en formato yyyy-mm-dd. Obligatorio.', '2026-05-22'],
        ['fecha_termino', 'Fecha final en formato yyyy-mm-dd. Si queda vacía se usa fecha_inicio.', '2026-05-22'],
        ['hora_inicio', 'Hora inicial en formato HH:mm.', '09:30'],
        ['hora_termino', 'Hora final en formato HH:mm.', '11:00'],
        ['categoria', 'Categoría institucional para clasificar el evento.', 'Pastoral, Academico, Deportivo, Institucional'],
        ['ubicacion', 'Lugar donde se realizará el evento.', 'Capilla del Colegio'],
        ['color', 'Color hexadecimal opcional. Si queda vacío se asigna por categoría.', '#0d6efd'],
        ['destacado', 'Indica si el evento es destacado. Usar 1 o 0.', '1'],
        ['visible', 'Indica si el evento queda visible al confirmar importación. Usar 1 o 0.', '1'],
        ['orden', 'Orden visual numérico. Si queda vacío se usa 0.', '1'],
    ];
}

function eventos_excel_admin_name(mysqli $db): string
{
    $idUsuario = (int) ($_SESSION['id_usuario'] ?? $_SESSION['admin_id'] ?? 0);
    if ($idUsuario > 0) {
        $stmt = $db->prepare('SELECT nombre, apellido, usuario, email FROM usuario WHERE id_usuario = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('i', $idUsuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            if ($user) {
                $fullName = trim((string) ($user['nombre'] ?? '') . ' ' . (string) ($user['apellido'] ?? ''));
                return $fullName !== '' ? $fullName : (string) (($user['usuario'] ?? '') ?: ($user['email'] ?? 'Administrador'));
            }
        }
    }

    return (string) ($_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'] ?? 'Administrador');
}

function eventos_excel_institution_name(mysqli $db): string
{
    $stmt = $db->prepare('SELECT nombre, nombre_corto FROM institucion ORDER BY id_institucion ASC LIMIT 1');
    if (!$stmt) {
        return 'Institucion';
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $institution = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return (string) (($institution['nombre'] ?? '') ?: ($institution['nombre_corto'] ?? 'Institucion'));
}

function eventos_xlsx_col(int $index): string
{
    $name = '';
    while ($index > 0) {
        $index--;
        $name = chr(65 + ($index % 26)) . $name;
        $index = intdiv($index, 26);
    }
    return $name;
}

function eventos_xml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function eventos_xlsx_shared_index(array &$sharedStrings, string $value): int
{
    if (!array_key_exists($value, $sharedStrings)) {
        $sharedStrings[$value] = count($sharedStrings);
    }

    return $sharedStrings[$value];
}

function eventos_xlsx_cell(int $col, int $row, string $value, int $style = 0, ?array &$sharedStrings = null): string
{
    $ref = eventos_xlsx_col($col) . $row;
    $styleAttr = $style > 0 ? ' s="' . $style . '"' : '';
    if ($sharedStrings !== null) {
        $index = eventos_xlsx_shared_index($sharedStrings, $value);
        return '<c r="' . $ref . '"' . $styleAttr . ' t="s"><v>' . $index . '</v></c>';
    }

    return '<c r="' . $ref . '"' . $styleAttr . ' t="inlineStr"><is><t>' . eventos_xml($value) . '</t></is></c>';
}

function eventos_xlsx_row(int $rowNumber, array $values, int $style = 0, ?array &$sharedStrings = null, ?float $height = null): string
{
    $cells = '';
    foreach (array_values($values) as $index => $value) {
        $cells .= eventos_xlsx_cell($index + 1, $rowNumber, (string) $value, $style, $sharedStrings);
    }
    $heightAttr = $height !== null ? ' ht="' . $height . '" customHeight="1"' : '';
    return '<row r="' . $rowNumber . '"' . $heightAttr . '>' . $cells . '</row>';
}

function eventos_xlsx_shared_strings_xml(array $sharedStrings): string
{
    $items = '';
    $values = array_flip($sharedStrings);
    ksort($values);
    foreach ($values as $value) {
        $items .= '<si><t>' . eventos_xml((string) $value) . '</t></si>';
    }

    $count = count($sharedStrings);
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $count . '" uniqueCount="' . $count . '">'
        . $items
        . '</sst>';
}

function eventos_generate_template_xlsx(mysqli $db): string
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('La extensión ZipArchive de PHP es necesaria para generar archivos Excel.');
    }

    $columns = eventos_excel_columns();
    $lastColumn = eventos_xlsx_col(count($columns));
    $institution = eventos_excel_institution_name($db);
    $adminName = eventos_excel_admin_name($db);
    $downloadedAt = date('Y-m-d H:i');
    $tmp = tempnam(sys_get_temp_dir(), 'eventos_xlsx_');
    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('No fue posible preparar la plantilla Excel.');
    }

    $sharedStrings = [];
    $sheet1Rows = '';
    $sheet1Rows .= eventos_xlsx_row(1, ['Plantilla de Carga Masiva de Eventos'], 1, $sharedStrings, 28);
    $sheet1Rows .= eventos_xlsx_row(2, [$institution], 2, $sharedStrings, 22);
    $sheet1Rows .= eventos_xlsx_row(3, ['Descargado por: ' . $adminName . ' | Fecha descarga: ' . $downloadedAt], 3, $sharedStrings, 20);
    $sheet1Rows .= eventos_xlsx_row(5, $columns, 4, $sharedStrings, 24);
    $sheet1Rows .= eventos_xlsx_row(6, ['Misa Institucional', 'Eucaristia en comunidad.', 'Descripcion completa del evento.', '2026-05-22', '2026-05-22', '09:00', '10:00', 'Pastoral', 'Capilla del Colegio', '#8e44ad', '1', '1', '1'], 5, $sharedStrings, 22);

    $sheet1 = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<dimension ref="A1:' . $lastColumn . '500"/>'
        . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
        . '<sheetFormatPr defaultRowHeight="18"/>'
        . '<cols>'
        . '<col min="1" max="1" width="28" customWidth="1"/><col min="2" max="3" width="34" customWidth="1"/>'
        . '<col min="4" max="7" width="16" customWidth="1"/><col min="8" max="10" width="20" customWidth="1"/>'
        . '<col min="11" max="13" width="12" customWidth="1"/>'
        . '</cols><sheetData>' . $sheet1Rows . '</sheetData>'
        . '<autoFilter ref="A5:' . $lastColumn . '500"/>'
        . '<mergeCells count="3"><mergeCell ref="A1:' . $lastColumn . '1"/><mergeCell ref="A2:' . $lastColumn . '2"/><mergeCell ref="A3:' . $lastColumn . '3"/></mergeCells>'
        . '<dataValidations count="4">'
        . '<dataValidation type="date" operator="between" allowBlank="0" sqref="D6:D500"><formula1>DATE(2020,1,1)</formula1><formula2>DATE(2035,12,31)</formula2></dataValidation>'
        . '<dataValidation type="time" operator="between" allowBlank="1" sqref="F6:G500"><formula1>TIME(0,0,0)</formula1><formula2>TIME(23,59,0)</formula2></dataValidation>'
        . '<dataValidation type="list" allowBlank="0" sqref="K6:K500"><formula1>"0,1"</formula1></dataValidation>'
        . '<dataValidation type="list" allowBlank="0" sqref="L6:L500"><formula1>"0,1"</formula1></dataValidation>'
        . '</dataValidations>'
        . '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>'
        . '</worksheet>';

    $helpRows = eventos_xlsx_row(1, ['AYUDA DE CARGA MASIVA DE EVENTOS'], 1, $sharedStrings, 28);
    $helpRows .= eventos_xlsx_row(3, ['COLUMNA', 'DESCRIPCION', 'EJEMPLO'], 4, $sharedStrings, 24);
    $rowNumber = 4;
    foreach (eventos_excel_help_rows() as $row) {
        $helpRows .= eventos_xlsx_row($rowNumber++, $row, 5, $sharedStrings, 22);
    }
    $helpRows .= eventos_xlsx_row($rowNumber + 1, ['Reglas importantes', 'La carga masiva trabaja solo con la tabla eventos. No inserta ni modifica calendario.', ''], 6, $sharedStrings, 22);
    $helpRows .= eventos_xlsx_row($rowNumber + 2, ['Fechas', 'Usar yyyy-mm-dd. La fecha de termino no puede ser anterior a fecha_inicio.', '2026-05-22'], 5, $sharedStrings, 22);
    $helpRows .= eventos_xlsx_row($rowNumber + 3, ['Horas', 'Usar HH:mm. Evitar textos como 9 am o 09:00 hrs.', '09:30'], 5, $sharedStrings, 22);
    $helpRows .= eventos_xlsx_row($rowNumber + 4, ['Errores comunes', 'Titulo vacio, fecha invalida, hora invalida o valores visibles fuera de 0/1.', ''], 5, $sharedStrings, 22);

    $sheet2 = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<dimension ref="A1:C' . ($rowNumber + 4) . '"/>'
        . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
        . '<sheetFormatPr defaultRowHeight="18"/>'
        . '<cols><col min="1" max="1" width="24" customWidth="1"/><col min="2" max="2" width="78" customWidth="1"/><col min="3" max="3" width="36" customWidth="1"/></cols>'
        . '<sheetData>' . $helpRows . '</sheetData><mergeCells count="1"><mergeCell ref="A1:C1"/></mergeCells>'
        . '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>'
        . '</worksheet>';

    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/></Types>');
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>');
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="EVENTOS" sheetId="1" r:id="rId1"/><sheet name="AYUDA" sheetId="2" r:id="rId2"/></sheets></workbook>');
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>');
    $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="4"><font><sz val="11"/><color rgb="FF1F2937"/><name val="Calibri"/></font><font><b/><sz val="18"/><color rgb="FF0F2F57"/><name val="Calibri"/></font><font><b/><sz val="12"/><color rgb="FF3558D5"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="5"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0F4C81"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF8FBFF"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFEFF6FF"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFDDE6F2"/></left><right style="thin"><color rgb="FFDDE6F2"/></right><top style="thin"><color rgb="FFDDE6F2"/></top><bottom style="thin"><color rgb="FFDDE6F2"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="7"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1"/></cellXfs></styleSheet>');
    $zip->addFromString('xl/sharedStrings.xml', eventos_xlsx_shared_strings_xml($sharedStrings));
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1);
    $zip->addFromString('xl/worksheets/sheet2.xml', $sheet2);
    $zip->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title>Plantilla eventos</dc:title><dc:creator>CMS Colegio San Pablo</dc:creator></cp:coreProperties>');
    $zip->addFromString('docProps/app.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"><Application>CMS Colegio San Pablo</Application></Properties>');
    $zip->close();

    return $tmp;
}

function eventos_generate_template_xls_xml(mysqli $db): string
{
    $columns = eventos_excel_columns();
    $institution = eventos_excel_institution_name($db);
    $adminName = eventos_excel_admin_name($db);
    $downloadedAt = date('Y-m-d H:i');
    $tmp = tempnam(sys_get_temp_dir(), 'eventos_xls_');

    $eventRows = '';
    $eventRows .= eventos_xls_row(['Plantilla de Carga Masiva de Eventos'], 'Title');
    $eventRows .= eventos_xls_row([$institution], 'Subtitle');
    $eventRows .= eventos_xls_row(['Descargado por: ' . $adminName . ' | Fecha descarga: ' . $downloadedAt], 'Meta');
    $eventRows .= eventos_xls_row(array_fill(0, count($columns), ''), 'Default');
    $eventRows .= eventos_xls_row($columns, 'Header');
    $eventRows .= eventos_xls_row(['Misa Institucional', 'Eucaristia en comunidad.', 'Descripcion completa del evento.', '2026-05-22', '2026-05-22', '09:00', '10:00', 'Pastoral', 'Capilla del Colegio', '#8e44ad', '1', '1', '1'], 'Body');

    $helpRows = eventos_xls_row(['AYUDA DE CARGA MASIVA DE EVENTOS'], 'Title');
    $helpRows .= eventos_xls_row(['', '', ''], 'Default');
    $helpRows .= eventos_xls_row(['COLUMNA', 'DESCRIPCION', 'EJEMPLO'], 'Header');
    foreach (eventos_excel_help_rows() as $row) {
        $helpRows .= eventos_xls_row($row, 'Body');
    }
    $helpRows .= eventos_xls_row(['Reglas importantes', 'La carga masiva trabaja solo con la tabla eventos. No inserta ni modifica calendario.', ''], 'Body');
    $helpRows .= eventos_xls_row(['Fechas', 'Usar yyyy-mm-dd. La fecha de termino no puede ser anterior a fecha_inicio.', '2026-05-22'], 'Body');
    $helpRows .= eventos_xls_row(['Horas', 'Usar HH:mm. Evitar textos como 9 am o 09:00 hrs.', '09:30'], 'Body');
    $helpRows .= eventos_xls_row(['Errores comunes', 'Titulo vacio, fecha invalida, hora invalida o valores visibles fuera de 0/1.', ''], 'Body');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<?mso-application progid="Excel.Sheet"?>'
        . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
        . '<Styles>'
        . '<Style ss:ID="Default"><Alignment ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="11"/></Style>'
        . '<Style ss:ID="Title"><Font ss:FontName="Calibri" ss:Size="18" ss:Bold="1" ss:Color="#0F2F57"/></Style>'
        . '<Style ss:ID="Subtitle"><Font ss:FontName="Calibri" ss:Size="12" ss:Bold="1" ss:Color="#3558D5"/></Style>'
        . '<Style ss:ID="Meta"><Font ss:FontName="Calibri" ss:Size="10" ss:Color="#4B5563"/></Style>'
        . '<Style ss:ID="Header"><Interior ss:Color="#0F4C81" ss:Pattern="Solid"/><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDE6F2"/></Borders></Style>'
        . '<Style ss:ID="Body"><Interior ss:Color="#F8FBFF" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#DDE6F2"/></Borders></Style>'
        . '</Styles>'
        . '<Worksheet ss:Name="EVENTOS"><Table ss:DefaultColumnWidth="120">' . $eventRows . '</Table><WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><FreezePanes/><FrozenNoSplit/><SplitHorizontal>5</SplitHorizontal><TopRowBottomPane>5</TopRowBottomPane></WorksheetOptions></Worksheet>'
        . '<Worksheet ss:Name="AYUDA"><Table ss:DefaultColumnWidth="150">' . $helpRows . '</Table></Worksheet>'
        . '</Workbook>';

    file_put_contents($tmp, $xml);
    return $tmp;
}

function eventos_xls_row(array $values, string $style): string
{
    $cells = '';
    foreach ($values as $value) {
        $cells .= '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . eventos_xml((string) $value) . '</Data></Cell>';
    }
    return '<Row>' . $cells . '</Row>';
}

function eventos_parse_import_file(string $path, string $originalName): array
{
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($extension === 'csv') {
        return eventos_parse_csv($path);
    }
    if ($extension === 'xlsx') {
        return eventos_parse_xlsx($path);
    }
    if ($extension === 'xls') {
        return eventos_parse_xls_xml($path);
    }

    throw new RuntimeException('Debes subir un archivo .xlsx, .xls o .csv.');
}

function eventos_parse_csv(string $path): array
{
    $handle = fopen($path, 'r');
    if (!$handle) {
        throw new RuntimeException('No fue posible leer el archivo CSV.');
    }
    $rows = [];
    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        $rows[] = array_map(static fn($value): string => trim((string) $value), $row);
    }
    fclose($handle);

    return eventos_rows_to_payloads($rows);
}

function eventos_parse_xlsx(string $path): array
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('La extensión ZipArchive de PHP es necesaria para leer archivos Excel.');
    }
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new RuntimeException('No fue posible abrir el archivo Excel.');
    }

    $sharedStrings = [];
    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedXml !== false) {
        $shared = simplexml_load_string($sharedXml);
        if ($shared) {
            foreach ($shared->si as $si) {
                $text = '';
                if (isset($si->t)) {
                    $text = (string) $si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }
    }

    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    if ($sheetXml === false) {
        throw new RuntimeException('El Excel no contiene la hoja EVENTOS.');
    }

    $xml = simplexml_load_string($sheetXml);
    if (!$xml) {
        throw new RuntimeException('No fue posible interpretar la hoja EVENTOS.');
    }

    $rows = [];
    foreach ($xml->sheetData->row as $row) {
        $values = [];
        foreach ($row->c as $cell) {
            $ref = (string) ($cell['r'] ?? '');
            preg_match('/^([A-Z]+)/', $ref, $match);
            $colIndex = isset($match[1]) ? eventos_col_index($match[1]) : count($values) + 1;
            $type = (string) ($cell['t'] ?? '');
            if ($type === 's') {
                $value = $sharedStrings[(int) $cell->v] ?? '';
            } elseif ($type === 'inlineStr') {
                $value = (string) ($cell->is->t ?? '');
            } else {
                $value = (string) ($cell->v ?? '');
            }
            $values[$colIndex - 1] = trim($value);
        }
        if ($values) {
            ksort($values);
            $rows[] = array_values($values);
        }
    }

    return eventos_rows_to_payloads($rows);
}

function eventos_parse_xls_xml(string $path): array
{
    $contents = file_get_contents($path);
    if ($contents === false) {
        throw new RuntimeException('No fue posible leer el archivo Excel.');
    }

    $xml = simplexml_load_string($contents);
    if (!$xml) {
        throw new RuntimeException('El archivo .xls no tiene un formato Excel XML compatible.');
    }
    $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');

    $worksheet = null;
    foreach ($xml->Worksheet as $sheet) {
        $attributes = $sheet->attributes('urn:schemas-microsoft-com:office:spreadsheet');
        if ((string) ($attributes['Name'] ?? '') === 'EVENTOS') {
            $worksheet = $sheet;
            break;
        }
    }
    if (!$worksheet) {
        $worksheet = $xml->Worksheet[0] ?? null;
    }
    if (!$worksheet) {
        throw new RuntimeException('El Excel no contiene la hoja EVENTOS.');
    }

    $rows = [];
    foreach ($worksheet->Table->Row as $row) {
        $values = [];
        foreach ($row->Cell as $cell) {
            $values[] = trim((string) ($cell->Data ?? ''));
        }
        $rows[] = $values;
    }

    return eventos_rows_to_payloads($rows);
}

function eventos_col_index(string $letters): int
{
    $index = 0;
    foreach (str_split($letters) as $letter) {
        $index = ($index * 26) + (ord($letter) - 64);
    }
    return $index;
}

function eventos_rows_to_payloads(array $rows): array
{
    $allowed = eventos_excel_columns();
    $headerIndex = null;
    $headers = [];
    foreach ($rows as $index => $row) {
        $normalized = array_map(static function ($value): string {
            $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);
            return strtolower(trim($value));
        }, $row);
        if (in_array('titulo', $normalized, true) && in_array('fecha_inicio', $normalized, true)) {
            $headerIndex = $index;
            $headers = $normalized;
            break;
        }
    }
    if ($headerIndex === null) {
        throw new RuntimeException('El archivo no contiene encabezados válidos para eventos.');
    }

    $payloads = [];
    for ($i = $headerIndex + 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        if (!array_filter($row, static fn($value): bool => trim((string) $value) !== '')) {
            continue;
        }
        $payload = [];
        foreach ($headers as $columnIndex => $header) {
            if (in_array($header, $allowed, true)) {
                $payload[$header] = eventos_normalize_excel_value($header, trim((string) ($row[$columnIndex] ?? '')));
            }
        }
        $payload['__row'] = $i + 1;
        $payloads[] = $payload;
    }

    return $payloads;
}

function eventos_normalize_excel_value(string $header, string $value): string
{
    if ($value === '') {
        return '';
    }

    if (in_array($header, ['fecha_inicio', 'fecha_termino'], true) && is_numeric($value)) {
        $timestamp = ((float) $value - 25569) * 86400;
        return gmdate('Y-m-d', (int) round($timestamp));
    }

    if (in_array($header, ['hora_inicio', 'hora_termino'], true) && is_numeric($value)) {
        $seconds = (int) round(((float) $value - floor((float) $value)) * 86400);
        return gmdate('H:i', $seconds);
    }

    return $value;
}

function eventos_validate_preview_rows(mysqli $db, array $rows): array
{
    $preview = [];
    foreach ($rows as $row) {
        $messages = [];
        $valid = true;
        try {
            $payload = cms_normalize_event_payload($row);
            if (cms_event_duplicate_exists($db, $payload)) {
                $valid = false;
                $messages[] = 'evento duplicado';
            }
        } catch (Throwable $exception) {
            $valid = false;
            $messages[] = $exception->getMessage();
        }
        if (trim((string) ($row['categoria'] ?? '')) === '') {
            $messages[] = 'categoria vacia';
        }
        $preview[] = [
            'row' => $row,
            'valid' => $valid,
            'messages' => $valid && !$messages ? ['valido'] : $messages,
        ];
    }

    return $preview;
}

function eventos_encode_row(array $row): string
{
    unset($row['__row']);
    return base64_encode(json_encode($row, JSON_UNESCAPED_UNICODE));
}

function eventos_decode_row(string $encoded): array
{
    $json = base64_decode($encoded, true);
    $data = $json !== false ? json_decode($json, true) : null;
    if (!is_array($data)) {
        throw new RuntimeException('Una fila seleccionada no pudo interpretarse.');
    }
    return $data;
}
