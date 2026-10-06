<?php
/** Read-only regression harness. Run: php -n tests/pagina_menu.php */
if (!in_array(PHP_SAPI, ['cli', 'cli-server'], true) || extension_loaded('mysqli')) {
    if (PHP_SAPI !== 'cli') { http_response_code(404); }
    exit("Prueba aislada: utiliza php -n tests/pagina_menu.php.\n");
}
if (PHP_SAPI === 'cli-server' && getenv('SP_MENU_TEST_SERVER') !== '1') { http_response_code(404); return; }
set_error_handler(static function ($level, $message, $file, $line) {
    if (error_reporting() & $level) { throw new ErrorException($message, 0, $level, $file, $line); }
    return false;
});
define('MYSQLI_ASSOC', 1);

/** Interpret SELECTs against fixtures, never a network/database connection. */
class mysqli
{
    public array $menus = [], $subs = [], $media = [], $history = [], $queries = [];
    public bool $fail = false;
    public array $institution = ['id_institucion' => 1, 'nombre' => 'Colegio San Pablo',
        'logo_header' => '/assets/images/logo/logo.svg', 'favicon' => '/assets/images/icono_ppt.png',
        'color_primario' => '#F0A000', 'color_secundario' => '#EF6C00', 'usar_gradientes' => 1];

    public function __construct()
    {
        $dump = file_get_contents(__DIR__ . '/../logica/qaseduc_colegio_spablo.sql');
        foreach (['menus', 'sub_menus'] as $table) {
            preg_match('/^INSERT INTO `' . $table . '` \(([^\r\n]+)\) VALUES\R(.*?);(?:\R|$)/ms', $dump, $match);
            if (!$match) { throw new RuntimeException('Falta el catalogo publico de referencia.'); }
            preg_match_all('/`([^`]+)`/', $match[1], $keys);
            foreach (preg_split('/\R/', $match[2]) as $line) {
                $values = str_getcsv(trim(trim($line), '(),;'), ',', "'", '\\');
                $row = array_combine($keys[1], $values);
                foreach (['id_menu', 'id_sub_menu', 'orden', 'estado'] as $key) {
                    if (isset($row[$key])) { $row[$key] = (int) $row[$key]; }
                }
                if ($table === 'menus') {
                    $this->menus[] = $row + ['hero_tipo' => 'imagen', 'imagen_hero' => null, 'hero_video_url' => '', 'hero_video_archivo' => null];
                } else {
                    $row += ['pagina_titulo' => $row['nombre'], 'pagina_contenido' => '<p>Contenido de prueba del editor.</p>',
                        'pagina_imagen_hero' => 'assets/images/frontis_02.jpg'];
                    $this->subs[] = $row;
                }
            }
        }
        foreach ($this->subs as $sub) {
            foreach ([4, 5, 6] as $order => $image) {
                $this->media[] = ['id_media' => $sub['id_sub_menu'] * 10 + $order, 'id_sub_menu' => $sub['id_sub_menu'],
                    'tipo' => 'imagen', 'archivo' => 'assets/images/frontis_0' . $image . '.jpg', 'titulo' => '', 'visible' => 1, 'orden' => $order];
            }
        }
    }

    public function prepare(string $sql): MenuTestStatement { return new MenuTestStatement($this, $sql); }
    public function query(string $sql): MenuTestResult { return new MenuTestResult($this->select($sql, [])); }
    public function select(string $sql, array $values): array
    {
        if (!preg_match('/^\s*SELECT\b/i', $sql)) { throw new RuntimeException('Escritura prohibida en la prueba.'); }
        $this->queries[] = $sql;
        if ($this->fail) { throw new RuntimeException('Fallo simulado privado'); }
        $menus = array_values(array_filter($this->menus, static fn($m) => $m['estado'] === 1));
        $subs = array_values(array_filter($this->subs, static fn($s) => $s['estado'] === 1));
        usort($subs, static fn($a, $b) => [$a['id_menu'], $a['orden'], $a['id_sub_menu']] <=> [$b['id_menu'], $b['orden'], $b['id_sub_menu']]);
        if (str_contains($sql, 'information_schema')) { return [[1]]; }
        if (str_contains($sql, 'FROM institucion')) { return [$this->institution]; }
        if (str_contains($sql, 'FROM menus')) { return $menus; }
        if (str_contains($sql, 'FROM sub_menus sm')) {
            return array_values(array_filter($subs, static fn($s) => $s['id_menu'] === (int) $values[0]
                && in_array($s['id_menu'], array_column($menus, 'id_menu'), true)));
        }
        if (str_contains($sql, 'FROM sub_menus')) { return $subs; }
        foreach (['sub_menu_pagina_media' => 'media', 'sub_menu_historia_item' => 'history'] as $table => $property) {
            if (!str_contains($sql, 'FROM ' . $table)) { continue; }
            $ids = array_column(array_filter($subs, static fn($s) => $s['id_menu'] === (int) $values[0]), 'id_sub_menu');
            $rows = array_values(array_filter($this->$property, static fn($r) => in_array($r['id_sub_menu'], $ids, true) && $r['visible'] === 1));
            usort($rows, static fn($a, $b) => [$a['orden'], $a['id_media'] ?? $a['id_historia_item']] <=> [$b['orden'], $b['id_media'] ?? $b['id_historia_item']]);
            return $rows;
        }
        if (str_contains($sql, 'FROM seccion') || str_contains($sql, 'FROM categoria_noticia')) { return []; }
        throw new RuntimeException('Lectura no prevista por el fixture.');
    }
}
class MenuTestResult
{
    private int $index = 0;
    public function __construct(public array $rows) {}
    public function fetch_assoc() { return $this->rows[$this->index++] ?? null; }
    public function fetch_all($mode): array { return $this->rows; }
    public function free(): void {}
}
class MenuTestStatement
{
    private array $values = [], $rows = [];
    private mixed $count;
    public function __construct(private mysqli $db, private string $sql) {}
    public function bind_param($types, &...$values): void { $this->values = $values; }
    public function execute(): bool { $this->rows = $this->db->select($this->sql, $this->values); return true; }
    public function get_result(): MenuTestResult { return new MenuTestResult($this->rows); }
    public function bind_result(&$count): void { $this->count = &$count; }
    public function fetch(): bool { $this->count = $this->rows[0][0] ?? 0; return true; }
    public function close(): void {}
}
require_once __DIR__ . '/../includes/cms_helpers.php';
require_once __DIR__ . '/../includes/pagina_menu_helpers.php';

function menu_test_view(mysqli $db, string $slug): array
{
    $site = cms_get_site_data($db, true);
    $resolved = cms_menu_page_resolve($site['menus'], $site['subs'], $slug);
    if (!$resolved) { throw new RuntimeException('No se resolvio el menu de prueba.'); }
    return cms_menu_page_view($resolved['menu'], cms_menu_page_load_pages($db, $resolved['menu']));
}

if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('~^/(?:assets|uploads)/.*\.(?:css|js|svg|png|jpe?g|gif|ico|woff2?|ttf|mp4|webm)$~i', $path)) { return false; }
    $db = new mysqli();
    if (($_GET['scenario'] ?? '') === 'edited') {
        foreach ($db->subs as &$sub) { if ($sub['nombre'] === 'VISIÓN') { $sub['pagina_contenido'] = '<p>Edicion reflejada.</p>'; } }
        unset($sub);
    }
    if ($path === '/biblioteca.php') { $_GET = ['route_slug' => 'biblioteca']; }
    elseif ($path === '/pagina_menu.php') {}
    elseif (preg_match('~^/menu/([0-9]+)/?$~', $path, $match)) { $_GET = ['route_id' => $match[1]]; }
    elseif (preg_match('~^/([a-z0-9]+(?:-[a-z0-9]+)*)/?$~', $path, $match)) { $_GET = ['route_slug' => $match[1]]; }
    else { http_response_code(404); return; }
    $code = file_get_contents(__DIR__ . '/../pagina_menu.php');
    // Only inject the fixture connection and preserve root-relative include paths.
    $code = str_replace('$db = cms_get_connection();', '$db = $GLOBALS["menuTestDatabase"];', $code);
    $code = str_replace('__DIR__', var_export(dirname(__DIR__), true), $code);
    $GLOBALS['menuTestDatabase'] = $db;
    eval('?>' . $code);
    return;
}

$checks = 0;
$check = static function ($value, string $label) use (&$checks): void { if (!$value) { throw new RuntimeException($label); } $checks++; };
$db = new mysqli(); $site = cms_get_site_data($db, true); $enabled = [];
foreach ($site['menus'] as $menu) {
    $id = $menu['id_menu']; $url = cms_menu_page_url($menu, $site['subs'][$id] ?? [], $site['menus']);
    if ($url === null) { $check($menu['nombre'] === 'Inicio', 'Excepcion inesperada en el catalogo'); continue; }
    $enabled[] = $menu['nombre'];
    $check(cms_menu_page_resolve($site['menus'], $site['subs'], ltrim($url, '/'))['menu']['id_menu'] === $id, 'Resolver y header divergen');
    $view = menu_test_view($db, ltrim($url, '/'));
    $pageStart = count($db->queries); $pages = cms_menu_page_load_pages($db, $menu);
    $check(count($db->queries) - $pageStart === 4, 'Lecturas por lotes');
    $internal = array_filter($site['subs'][$id], 'cms_menu_submenu_is_internal');
    $check(count($view['sections']) === count($internal), 'Hijos internos activos');
    $check(count(array_unique(array_column($view['sections'], 'anchor'))) === count($view['sections']), 'Anclas unicas');
    $check(count(cms_menu_page_navigation($view, $site['subs'][$id], $url)) === count($site['subs'][$id]), 'Navegacion mixta completa');
}
$mi = menu_test_view($db, 'mi-san-pablo'); $check(count($mi['sections']) === 1, 'Mi San Pablo solo contenido interno');
$conf = menu_test_view($db, 'confesionalidad'); $check(count($conf['sections']) === 6, 'Seis hijos de Confesionalidad');
$base = ['id_menu' => 900, 'nombre' => 'Menu futuro', 'url' => '', 'estado' => 1];
$child = ['id_sub_menu' => 901, 'id_menu' => 900, 'nombre' => 'Visión', 'url' => '', 'estado' => 1, 'orden' => 1];
$check(cms_menu_page_url($base, [$child], [$base]) === '/menu-futuro', 'Menu futuro sin PHP');
$check(cms_menu_page_url($base, [], [$base]) === null, 'Sin hijos no pagina vacia');
foreach (['https://example.org/portal', '//example.org', '/archivo.pdf', '/noticias', '#contacto', '/admin?panel=menus', 'noticias.php'] as $url) {
    $special = array_replace($base, ['url' => $url]); $check(cms_menu_page_url($special, [$child], [$special]) === null, 'Destino especial conservado');
}
$duplicate = array_replace($base, ['id_menu' => 902]); $check(cms_menu_page_url($base, [$child], [$base, $duplicate]) === '/menu/900', 'Colision por ID');
$stable = array_replace($base, ['url' => '/ruta-estable', 'nombre' => 'Renombrado']); $check(cms_menu_page_url($stable, [$child], [$stable]) === '/ruta-estable', 'URL fijada estable');
$check(cms_menu_page_url(array_replace($base, ['estado' => 0]), [$child]) === null, 'Menu inactivo');
foreach ([[null, ['900']], [['x'], null], ['../foo', null], [null, '0'], [null, '900 OR 1=1']] as [$slug, $id]) {
    $check(cms_menu_page_resolve([$base], [900 => [$child]], $slug, $id) === null, 'Parametro invalido rechazado');
}
$library = menu_test_view($db, 'biblioteca'); $check($library['hero']['src'] === '/assets/images/frontis_01.jpg', 'Hero no depende de principal');
$check(count($library['sections'][0]['gallery']) === 3, 'Galeria visible');
$libraryId = $library['sections'][0]['id'];
foreach ($db->media as &$medium) {
    if ($medium['id_sub_menu'] === $libraryId) { $medium['visible'] = 0; break; }
}
unset($medium);
$check(count(menu_test_view($db, 'biblioteca')['sections'][0]['gallery']) === 2, 'Medio oculto no aparece');
foreach ($db->subs as &$sub) {
    if ($sub['id_sub_menu'] === $libraryId) { $sub['estado'] = 0; break; }
}
unset($sub);
$check(count(menu_test_view($db, 'biblioteca')['sections']) === count($library['sections']) - 1, 'Submenu inactivo no aparece');
$html = cms_menu_page_content_html('<p onclick="alert(1)"><a href="java&#x73;cript:alert(1)">Texto</a></p>');
$check(!str_contains($html, 'onclick') && str_contains($html, 'href="#"'), 'Saneado editorial');
$check(cms_menu_page_image_size('/../no-existe') === [] && cms_menu_page_image_size('https://example.org/a.jpg') === [], 'Dimensiones locales seguras');
echo json_encode(['checks' => $checks, 'menus' => $enabled, 'database' => 'simulada, sin conexion ni escrituras'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
