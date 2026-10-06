<?php
// HTTP regression fixture. Run through tests/menu_header_upload.cjs, never production.
if (PHP_SAPI !== 'cli-server' || extension_loaded('mysqli') || getenv('SP_MENU_UPLOAD_TEST') !== '1') {
    http_response_code(404); exit;
}
class mysqli_sql_exception extends RuntimeException {}
class mysqli
{
    public array $rows, $snapshot = [];
    public int $insert_id = 0;
    public function __construct() { $this->rows = json_decode(file_get_contents(getenv('SP_MENU_UPLOAD_STATE')), true); }
    public function prepare(string $sql): MenuUploadStatement { return new MenuUploadStatement($this, $sql); }
    public function query(string $sql) { throw new RuntimeException('Consulta no prevista en el fixture.'); }
    public function begin_transaction(): bool { $this->snapshot = $this->rows; return true; }
    public function rollback(): bool { $this->rows = $this->snapshot; return true; }
    public function commit(): bool { file_put_contents(getenv('SP_MENU_UPLOAD_STATE'), json_encode($this->rows)); return true; }
}
class MenuUploadResult
{
    public function __construct(private array $rows) {}
    public function fetch_assoc() { return array_shift($this->rows); }
    public function fetch_row() { $row = array_shift($this->rows); return $row ? array_values($row) : null; }
    public function free(): void {}
}
class MenuUploadStatement
{
    private array $values = [], $rows = [];
    private mixed $count;
    public function __construct(private mysqli $db, private string $sql) {}
    public function bind_param($types, &...$values): void { $this->values = $values; }
    public function bind_result(&$count): void { $this->count = &$count; }
    public function fetch(): bool { $this->count = 1; return true; }
    public function execute(): bool
    {
        $id = (int) end($this->values);
        if (str_contains($this->sql, 'information_schema')) { return true; }
        if (str_starts_with($this->sql, 'SELECT hero_tipo')) {
            $this->rows = isset($this->db->rows[$id]) ? [$this->db->rows[$id]] : []; return true;
        }
        if (str_starts_with($this->sql, 'UPDATE menus SET nombre')) {
            if (!isset($this->db->rows[$id])) { throw new RuntimeException('Menu ajeno al fixture.'); }
            return true;
        }
        if (str_starts_with($this->sql, 'UPDATE menus SET hero_tipo')) {
            if (!empty($_GET['fail'])) { throw new mysqli_sql_exception('Mensaje privado simulado', 99); }
            foreach (['hero_tipo','imagen_hero','hero_video_url','hero_video_archivo'] as $index => $key) {
                $this->db->rows[$id][$key] = $this->values[$index];
            }
            return true;
        }
        if (str_starts_with($this->sql, 'SELECT 1 FROM seccion_config')) { return true; }
        if (str_starts_with($this->sql, 'SELECT 1 FROM `')) {
            if (str_contains($this->sql, 'FROM `menus`')) {
                foreach ($this->db->rows as $row) {
                    if (in_array($this->values[0], [$row['imagen_hero'], $row['hero_video_archivo']], true)) { $this->rows = [[1]]; }
                }
            }
            return true;
        }
        throw new RuntimeException('Operacion no prevista en el fixture.');
    }
    public function get_result(): MenuUploadResult { return new MenuUploadResult($this->rows); }
    public function close(): void {}
}
require_once __DIR__ . '/../includes/cms_helpers.php';
header('Content-Type: application/json; charset=UTF-8');
$db = new mysqli();
try {
    cms_upload_check_request_size();
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') { cms_save_menu($db, $_POST); }
    $views = array_map('cms_menu_header_media', $db->rows);
    echo json_encode(['ok' => true, 'rows' => $db->rows, 'heroes' => $views]);
} catch (Throwable $error) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => $error->getMessage(), 'rows' => $db->rows,
        'php_error' => $_FILES['menu_imagen_hero']['error'] ?? null]);
}
