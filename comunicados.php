<?php
require_once __DIR__ . '/includes/cms_helpers.php';

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

function comunicado_public_file(?string $archivo): array
{
    $archivo = trim((string) $archivo);
    if ($archivo === '') {
        return ['', false];
    }

    $archivo = str_replace('\\', '/', $archivo);
    if (str_contains($archivo, '..') || !str_starts_with($archivo, 'uploads/comunicados/')) {
        return ['', false];
    }

    $fullPath = __DIR__ . '/' . $archivo;
    return [$archivo, is_file($fullPath)];
}

$institution = null;
$sectionConfigsMap = [];
$sectionItemsMap = [];
$categoriesById = [];
$arrMenus = [];
$arrSubs = [];
$categories = [];
$comunicados = [];
$totalRows = 0;
$perPage = 8;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$categoryFilter = max(0, (int) ($_GET['categoria'] ?? 0));
$search = trim((string) ($_GET['q'] ?? ''));
$institutionId = 1;

try {
    $db = cms_get_connection();
    $site = cms_get_site_data($db);
    $institution = $site['institution'];
    $sectionConfigsMap = $site['configs'];
    $sectionItemsMap = $site['items'];
    $categoriesById = $site['categories'];
    $arrMenus = $site['menus'];
    $arrSubs = $site['subs'];
    $institutionId = (int) ($institution['id_institucion'] ?? cms_get_institution_id($db));

    $stmtCategories = $db->prepare("
        SELECT id_categoria_comunicado, nombre, color
        FROM comunicado_categoria
        WHERE id_institucion = ?
          AND visible = 1
        ORDER BY orden ASC, nombre ASC
    ");
    if (!$stmtCategories) {
        throw new RuntimeException('No se pudo preparar la consulta de categorias.');
    }
    $stmtCategories->bind_param('i', $institutionId);
    $stmtCategories->execute();
    $resultCategories = $stmtCategories->get_result();
    $categories = $resultCategories ? $resultCategories->fetch_all(MYSQLI_ASSOC) : [];
    $stmtCategories->close();

    $where = [
        "c.estado = 'publicado'",
        'c.visible = 1',
        'cc.visible = 1',
        'c.id_institucion = ?',
    ];
    $types = 'i';
    $values = [$institutionId];

    if ($categoryFilter > 0) {
        $where[] = 'c.id_categoria_comunicado = ?';
        $types .= 'i';
        $values[] = $categoryFilter;
    }

    if ($search !== '') {
        $where[] = '(c.titulo LIKE ? OR c.descripcion_corta LIKE ?)';
        $types .= 'ss';
        $likeSearch = '%' . $search . '%';
        $values[] = $likeSearch;
        $values[] = $likeSearch;
    }

    $whereSql = implode(' AND ', $where);
    $stmtCount = $db->prepare("
        SELECT COUNT(*)
        FROM comunicado c
        INNER JOIN comunicado_categoria cc ON cc.id_categoria_comunicado = c.id_categoria_comunicado
        WHERE $whereSql
    ");
    if (!$stmtCount) {
        throw new RuntimeException('No se pudo preparar la consulta de total de comunicados.');
    }
    $stmtCount->bind_param($types, ...$values);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalRows = $resultCount ? (int) $resultCount->fetch_column() : 0;
    $stmtCount->close();

    $listTypes = $types . 'ii';
    $listValues = array_merge($values, [$perPage, $offset]);
    $stmtComunicados = $db->prepare("
        SELECT
            c.id_comunicado,
            c.titulo,
            c.descripcion_corta,
            c.archivo,
            c.fecha_publicacion,
            c.estado,
            c.visible,
            c.orden,
            cc.id_categoria_comunicado,
            cc.nombre AS categoria_nombre,
            cc.color AS categoria_color
        FROM comunicado c
        INNER JOIN comunicado_categoria cc ON cc.id_categoria_comunicado = c.id_categoria_comunicado
        WHERE $whereSql
        ORDER BY c.fecha_publicacion DESC, c.orden ASC, c.id_comunicado DESC
        LIMIT ? OFFSET ?
    ");
    if (!$stmtComunicados) {
        throw new RuntimeException('No se pudo preparar la consulta de comunicados.');
    }
    $stmtComunicados->bind_param($listTypes, ...$listValues);
    $stmtComunicados->execute();
    $resultComunicados = $stmtComunicados->get_result();
    $comunicados = $resultComunicados ? $resultComunicados->fetch_all(MYSQLI_ASSOC) : [];
    $stmtComunicados->close();
} catch (Throwable $exception) {
    error_log('comunicados.php: ' . $exception->getMessage());
}

$totalPages = max(1, (int) ceil($totalRows / $perPage));
$primary = $institution['color_primario'] ?? '#F0A000';
$secondary = $institution['color_secundario'] ?? '#EF6C00';
$tertiary = $institution['color_terciario'] ?? '#1976D2';
$fontPrincipal = trim((string) ($institution['fuente_principal'] ?? 'Poppins'));
if ($fontPrincipal === '') {
    $fontPrincipal = 'Poppins';
}
$fontPrincipal = preg_replace('/[^A-Za-z0-9 \-]/', '', $fontPrincipal) ?: 'Poppins';
$queryBase = [];
if ($categoryFilter > 0) { $queryBase['categoria'] = $categoryFilter; }
if ($search !== '') { $queryBase['q'] = $search; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunicados | <?= e($institution['nombre'] ?? 'Colegio San Pablo') ?></title>
    <link rel="shortcut icon" href="<?= e($institution['favicon'] ?? 'assets/images/icono_ppt.png') ?>">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/meanmenu.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/colegiosanpablo.css">
    <style>
        :root {
            --com-primary: <?= e($primary) ?>;
            --com-secondary: <?= e($secondary) ?>;
            --com-tertiary: <?= e($tertiary) ?>;
            --com-font: "<?= e($fontPrincipal) ?>", "Poppins", sans-serif;
        }
        .com-hero, .com-section, .com-hero *, .com-section * { font-family: var(--com-font); }
        .com-hero { padding: 86px 0 78px; color: #fff; background: linear-gradient(135deg, rgba(15,23,42,.84), rgba(15,23,42,.42)), linear-gradient(90deg, var(--com-primary), var(--com-secondary), var(--com-tertiary)); }
        .com-hero h1 { color: #fff; font-size: clamp(42px, 6vw, 76px); line-height: 1; margin: 0 0 14px; letter-spacing: 0; }
        .com-hero p { color: rgba(255,255,255,.88); font-size: 1.15rem; margin: 0; }
        .com-section { padding: 52px 0 82px; background: #f7f9fc; }
        .com-toolbar { display: flex; justify-content: space-between; gap: 18px; flex-wrap: wrap; margin-bottom: 26px; }
        .com-cats { display: flex; flex-wrap: wrap; gap: 10px; }
        .com-cat { border: 1px solid #dbe4ef; border-radius: 999px; padding: 9px 15px; background: #fff; color: #26344f; font-weight: 800; font-size: .88rem; text-decoration: none; }
        .com-cat.active { background: var(--com-primary); border-color: var(--com-primary); color: #fff; }
        .com-search { display: flex; gap: 8px; min-width: min(100%, 360px); }
        .com-search input { border: 1px solid #dbe4ef; border-radius: 999px; padding: 10px 16px; min-width: 0; flex: 1; }
        .com-search button { border: 0; border-radius: 999px; padding: 10px 18px; color: #fff; font-weight: 800; background: var(--com-primary); }
        .com-list { display: grid; gap: 16px; }
        .com-card { display: grid; grid-template-columns: 1fr auto; gap: 18px; align-items: center; border: 1px solid #e4ebf5; border-radius: 16px; padding: 22px; background: #fff; box-shadow: 0 12px 34px rgba(15,35,70,.07); }
        .com-card h2 { color: #111c32; font-size: clamp(20px, 2vw, 28px); line-height: 1.18; margin: 10px 0 8px; letter-spacing: 0; }
        .com-meta { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; color: #657389; font-size: .92rem; }
        .com-badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 6px 11px; color: #fff; font-weight: 800; font-size: .78rem; }
        .com-desc { color: #556176; margin: 0; }
        .com-file { display: inline-flex; align-items: center; justify-content: center; min-width: 148px; height: 46px; border-radius: 12px; color: #fff; background: var(--com-primary); font-weight: 800; text-decoration: none; }
        .com-file.is-disabled { background: #d8dee8; color: #64748b; pointer-events: none; }
        .com-empty { border: 1px dashed #ccd7e5; border-radius: 18px; padding: 42px 22px; background: #fff; text-align: center; color: #64748b; }
        .com-pagination { display: flex; justify-content: center; gap: 8px; margin-top: 30px; }
        .com-page { min-width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #dbe4ef; color: #26344f; font-weight: 800; text-decoration: none; }
        .com-page.active { background: var(--com-primary); border-color: var(--com-primary); color: #fff; }
        @media (max-width: 767px) {
            .com-card { grid-template-columns: 1fr; }
            .com-file { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="sp-colorband"></div>
    <?php
    $headerComponent = cms_get_component_path('header_principal');
    if ($headerComponent) { include $headerComponent; }
    ?>

    <section class="com-hero">
        <div class="container">
            <h1>Comunicados</h1>
            <p>Información importante para nuestra comunidad educativa</p>
        </div>
    </section>

    <main class="com-section">
        <div class="container">
            <div class="com-toolbar">
                <div class="com-cats" aria-label="Filtros por categoría">
                    <?php $allQuery = $search !== '' ? '?q=' . rawurlencode($search) : ''; ?>
                    <a class="com-cat <?= $categoryFilter === 0 ? 'active' : '' ?>" href="comunicados.php<?= e($allQuery) ?>">Todas</a>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        if (mb_strtolower((string) $category['nombre']) === 'todas') { continue; }
                        $catQuery = ['categoria' => (int) $category['id_categoria_comunicado']];
                        if ($search !== '') { $catQuery['q'] = $search; }
                        ?>
                        <a class="com-cat <?= $categoryFilter === (int) $category['id_categoria_comunicado'] ? 'active' : '' ?>"
                           href="comunicados.php?<?= e(http_build_query($catQuery)) ?>"
                           style="<?= $categoryFilter === (int) $category['id_categoria_comunicado'] ? '' : '--cat-color:' . e($category['color'] ?? $primary) ?>">
                            <?= e($category['nombre']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <form class="com-search" method="get" action="comunicados.php">
                    <?php if ($categoryFilter > 0): ?><input type="hidden" name="categoria" value="<?= (int) $categoryFilter ?>"><?php endif; ?>
                    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Buscar comunicado">
                    <button type="submit"><i class="fas fa-search me-1"></i>Buscar</button>
                </form>
            </div>

            <?php if ($comunicados): ?>
                <div class="com-list">
                    <?php foreach ($comunicados as $comunicado): ?>
                        <?php [$fileHref, $fileExists] = comunicado_public_file($comunicado['archivo'] ?? ''); ?>
                        <article class="com-card">
                            <div>
                                <div class="com-meta">
                                    <span class="com-badge" style="background: <?= e($comunicado['categoria_color'] ?: $primary) ?>"><?= e($comunicado['categoria_nombre']) ?></span>
                                    <span><i class="fas fa-calendar-alt me-1"></i><?= e(date('d-m-Y', strtotime((string) $comunicado['fecha_publicacion']))) ?></span>
                                </div>
                                <h2><?= e($comunicado['titulo']) ?></h2>
                                <?php if (!empty($comunicado['descripcion_corta'])): ?>
                                    <p class="com-desc"><?= e($comunicado['descripcion_corta']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if ($fileExists): ?>
                                <a class="com-file" href="<?= e($fileHref) ?>" target="_blank" rel="noopener noreferrer"><i class="fas fa-download me-2"></i>Ver archivo</a>
                            <?php else: ?>
                                <span class="com-file is-disabled">Archivo no disponible</span>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php if ($totalPages > 1): ?>
                    <nav class="com-pagination" aria-label="Paginación de comunicados">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php $pageQuery = $queryBase + ['page' => $i]; ?>
                            <a class="com-page <?= $i === $page ? 'active' : '' ?>" href="comunicados.php?<?= e(http_build_query($pageQuery)) ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="com-empty">
                    <h2>No encontramos comunicados</h2>
                    <p>Prueba limpiando los filtros o realizando una nueva búsqueda.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php
    $footerComponent = cms_get_component_path('footer_principal');
    if ($footerComponent) { include $footerComponent; }
    ?>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/meanmenu.js"></script>
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/magnific-popup.min.js"></script>
    <script src="assets/js/nice-select.min.js"></script>
    <script src="assets/js/parallax.js"></script>
    <script src="assets/js/jquery.waypoints.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
