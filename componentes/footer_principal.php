<?php
$escape = static function ($value): string {
    if (function_exists('e')) {
        return e((string) $value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$institutionData = is_array($institution ?? null) ? $institution : [];
$footerSectionConfig = is_array($sectionConfigsMap['footer_principal'] ?? null) ? $sectionConfigsMap['footer_principal'] : [];

/*
|--------------------------------------------------------------------------
| Fallbacks temporales para futura migracion a base de datos
|--------------------------------------------------------------------------
*/
$footerQuickLinksFallback = [
    ['label' => 'Inicio', 'url' => '#'],
    ['label' => 'Institucional', 'url' => '#'],
    ['label' => 'Noticias', 'url' => '#'],
    ['label' => 'Comunicados', 'url' => '#'],
    ['label' => 'Biblioteca', 'url' => '#'],
    ['label' => 'Confesionalidad', 'url' => '#'],
    ['label' => 'Matricula', 'url' => '#'],
];

$footerLevelsFallback = [
    ['label' => 'Maternal', 'url' => '#'],
    ['label' => 'Inicial', 'url' => '#'],
    ['label' => 'Primaria', 'url' => '#'],
    ['label' => '3er Ciclo EBI', 'url' => '#'],
    ['label' => 'Bachillerato', 'url' => '#'],
    ['label' => 'Libre Asistido', 'url' => '#'],
];

$footerSitesFallback = [
    [
        'title' => 'Administracion',
        'address' => 'Venancio Benavidez 3612',
        'phone' => '',
        'icon' => 'fas fa-building',
    ],
    [
        'title' => 'Inicial',
        'address' => 'Joaquin Suarez 3596',
        'phone' => '2336 6000',
        'icon' => 'fas fa-school',
    ],
    [
        'title' => 'Preuniversitario',
        'address' => 'Av. Millan 3375',
        'phone' => '2202 0000',
        'icon' => 'fas fa-graduation-cap',
    ],
];

$footerLegalLinksFallback = [
    ['label' => 'Politica de privacidad', 'url' => '#'],
    ['label' => 'Terminos legales', 'url' => '#'],
    ['label' => 'Admisiones', 'url' => '#'],
];

$logoPath = trim((string) ($institutionData['logo_footer'] ?? $institutionData['logo_header'] ?? 'assets/images/logo-sin-fondo-1.png'));
$institutionName = trim((string) ($institutionData['nombre'] ?? 'Colegio San Pablo'));
$footerDescription = trim((string) ($footerSectionConfig['descripcion_institucional'] ?? $footerSectionConfig['descripcion'] ?? ''));
$footerDescription = $footerDescription !== ''
    ? $footerDescription
    : $institutionName . ' acompana a su comunidad con una propuesta educativa integral, cercana e inspirada en una formacion academica, humana y valorial.';

$currentYear = '2026';
$copyrightText = trim((string) ($footerSectionConfig['copyright_text'] ?? ''));
$copyrightText = $copyrightText !== ''
    ? $copyrightText
    : '© ' . $currentYear . ' ' . $institutionName . '. Todos los derechos reservados.';

$normalizeUrl = static function (?string $url): string {
    $url = trim((string) $url);
    if ($url === '' || $url === '#') {
        return '#';
    }

    if (preg_match('~^(https?:)?//~i', $url) === 1 || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:') || str_starts_with($url, '#')) {
        return $url;
    }

    return 'https://' . ltrim($url, '/');
};

$db = null;
if (function_exists('cms_get_connection')) {
    try {
        $db = cms_get_connection();
    } catch (Throwable $exception) {
        $db = null;
    }
}

$institutionId = (int) ($institutionData['id_institucion'] ?? 0);

$loadFooterRows = static function (?mysqli $dbConnection, string $table, array $requiredColumns, string $orderSql = '') use ($institutionId): array {
    if (!$dbConnection || !function_exists('cms_table_exists') || !function_exists('cms_column_exists')) {
        return [];
    }

    if (!cms_table_exists($dbConnection, $table)) {
        return [];
    }

    foreach ($requiredColumns as $column) {
        if (!cms_column_exists($dbConnection, $table, $column)) {
            return [];
        }
    }

    $columnsSql = implode(', ', array_map(static fn($column) => '`' . $column . '`', $requiredColumns));
    $whereParts = [];

    if (cms_column_exists($dbConnection, $table, 'activo')) {
        $whereParts[] = '`activo` = 1';
    }

    if ($institutionId > 0 && cms_column_exists($dbConnection, $table, 'id_institucion')) {
        $whereParts[] = '`id_institucion` = ' . $institutionId;
    }

    $sql = 'SELECT ' . $columnsSql . ' FROM `' . $table . '`';
    if ($whereParts !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $whereParts);
    }
    if ($orderSql !== '') {
        $sql .= ' ORDER BY ' . $orderSql;
    }

    $result = $dbConnection->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
};

$footerConfigRows = $loadFooterRows(
    $db,
    'footer_configuracion',
    ['descripcion_institucional', 'copyright_text', 'telefono_general', 'correo_general', 'sitio_url', 'sitio_label', 'politica_url', 'terminos_url', 'admisiones_url'],
    ''
);
$footerConfigDb = $footerConfigRows[0] ?? [];

if (!empty($footerConfigDb['descripcion_institucional'])) {
    $footerDescription = trim((string) $footerConfigDb['descripcion_institucional']);
}

if (!empty($footerConfigDb['copyright_text'])) {
    $copyrightText = trim((string) $footerConfigDb['copyright_text']);
}

$footerQuickLinks = $loadFooterRows($db, 'footer_enlaces_rapidos', ['label', 'url', 'orden'], '`orden` ASC');
$footerQuickLinks = array_map(static function (array $row): array {
    return [
        'label' => trim((string) ($row['label'] ?? '')),
        'url' => trim((string) ($row['url'] ?? '#')) ?: '#',
    ];
}, $footerQuickLinks);

if ($footerQuickLinks === []) {
    if (!empty($arrMenus) && is_array($arrMenus)) {
        foreach (array_slice($arrMenus, 0, 7) as $menuItem) {
            $footerQuickLinks[] = [
                'label' => trim((string) ($menuItem['nombre'] ?? '')),
                'url' => trim((string) ($menuItem['url'] ?? '#')) ?: '#',
            ];
        }
    }
}

if ($footerQuickLinks === []) {
    $footerQuickLinks = $footerQuickLinksFallback;
}

$footerLevels = $loadFooterRows($db, 'footer_niveles', ['nombre', 'url', 'orden'], '`orden` ASC');
$footerLevels = array_map(static function (array $row): array {
    return [
        'label' => trim((string) ($row['nombre'] ?? '')),
        'url' => trim((string) ($row['url'] ?? '#')) ?: '#',
    ];
}, $footerLevels);

if ($footerLevels === []) {
    $footerLevels = $footerLevelsFallback;
}

$footerSocial = $loadFooterRows($db, 'footer_redes_sociales', ['nombre', 'url', 'icono', 'orden'], '`orden` ASC');
$footerSocial = array_map(static function (array $row): array {
    return [
        'name' => trim((string) ($row['nombre'] ?? '')),
        'url' => trim((string) ($row['url'] ?? '')),
        'icon' => trim((string) ($row['icono'] ?? '')),
    ];
}, $footerSocial);

if ($footerSocial === []) {
    $socialMap = [
        'instagram' => ['label' => 'Instagram', 'url' => $institutionData['instagram'] ?? '', 'icon' => 'fab fa-instagram'],
        'facebook' => ['label' => 'Facebook', 'url' => $institutionData['facebook'] ?? '', 'icon' => 'fab fa-facebook-f'],
        'youtube' => ['label' => 'YouTube', 'url' => $institutionData['youtube'] ?? ($footerSectionConfig['youtube'] ?? ''), 'icon' => 'fab fa-youtube'],
        'twitter' => ['label' => 'Twitter', 'url' => $institutionData['twitter'] ?? $institutionData['x'] ?? ($footerSectionConfig['twitter'] ?? $footerSectionConfig['x'] ?? ''), 'icon' => 'fab fa-twitter'],
    ];

    foreach ($socialMap as $socialItem) {
        if (trim((string) $socialItem['url']) === '') {
            continue;
        }

        $footerSocial[] = [
            'name' => $socialItem['label'],
            'url' => trim((string) $socialItem['url']),
            'icon' => $socialItem['icon'],
        ];
    }
}

$footerSites = $loadFooterRows($db, 'footer_sedes_contacto', ['titulo', 'direccion', 'telefono', 'icono', 'tipo', 'orden'], '`orden` ASC');
$footerSites = array_map(static function (array $row): array {
    return [
        'title' => trim((string) ($row['titulo'] ?? '')),
        'address' => trim((string) ($row['direccion'] ?? '')),
        'phone' => trim((string) ($row['telefono'] ?? '')),
        'icon' => trim((string) ($row['icono'] ?? '')) ?: 'fas fa-map-marker-alt',
        'type' => trim((string) ($row['tipo'] ?? 'sede')),
    ];
}, $footerSites);

if ($footerSites === []) {
    $footerSites = $footerSitesFallback;
}

$websiteUrl = trim((string) ($footerConfigDb['sitio_url'] ?? $footerSectionConfig['sitio_url'] ?? $institutionData['dominio'] ?? ''));
if ($websiteUrl === '' && !empty($_SERVER['HTTP_HOST'])) {
    $websiteUrl = (string) $_SERVER['HTTP_HOST'];
}
$websiteUrl = $normalizeUrl($websiteUrl);
$websiteLabel = $websiteUrl !== '#'
    ? preg_replace('~^https?://~i', '', $websiteUrl)
    : trim((string) ($footerConfigDb['sitio_label'] ?? $footerSectionConfig['sitio_label'] ?? ''));
$websiteLabel = rtrim((string) $websiteLabel, '/');

$generalPhone = trim((string) ($footerConfigDb['telefono_general'] ?? $footerSectionConfig['telefono_general'] ?? $institutionData['telefono'] ?? ''));
$generalEmail = trim((string) ($footerConfigDb['correo_general'] ?? $footerSectionConfig['correo_general'] ?? $institutionData['email'] ?? ''));

$footerLegalLinks = [];
if ($footerConfigDb !== []) {
    $footerLegalLinks = [
        ['label' => 'Politica de privacidad', 'url' => trim((string) ($footerConfigDb['politica_url'] ?? '#')) ?: '#'],
        ['label' => 'Terminos legales', 'url' => trim((string) ($footerConfigDb['terminos_url'] ?? '#')) ?: '#'],
        ['label' => 'Admisiones', 'url' => trim((string) ($footerConfigDb['admisiones_url'] ?? '#')) ?: '#'],
    ];
}

if ($footerLegalLinks === []) {
    $footerLegalLinks = $footerLegalLinksFallback;
}
?>
<style>
    .footer-principal {
        --footer-bg: linear-gradient(180deg, #313131 0%, #262626 100%);
        --footer-surface: rgba(255, 255, 255, 0.04);
        --footer-border: rgba(255, 255, 255, 0.1);
        --footer-text: #d4d4d4;
        --footer-muted: #a9a9a9;
        --footer-title: #ffffff;
        --footer-accent: var(--sp-amber, #e8a030);
        --footer-accent-strong: var(--sp-naranja, #e07830);
        position: relative;
        background: var(--footer-bg);
        color: var(--footer-text);
        margin-top: 0;
        overflow: hidden;
    }

    .footer-principal::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top left, rgba(232, 160, 48, 0.08), transparent 26%),
            radial-gradient(circle at bottom right, rgba(32, 96, 176, 0.12), transparent 24%);
        pointer-events: none;
    }

    .footer-principal .container {
        position: relative;
        z-index: 1;
    }

    .footer-principal__main {
        padding: 72px 0 32px;
    }

    .footer-principal__grid > [class*="col-"] {
        margin-bottom: 24px;
    }

    .footer-brand {
        max-width: 380px;
    }

    .footer-brand__logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 84px;
        margin-bottom: 20px;
    }

    .footer-brand__logo img {
        max-width: 100%;
        max-height: 74px;
        width: auto;
        object-fit: contain;
        filter: drop-shadow(0 12px 30px rgba(0, 0, 0, 0.22));
    }

    .footer-brand__name {
        color: var(--footer-title);
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .footer-brand__text {
        margin: 0;
        color: var(--footer-text);
        line-height: 1.85;
        font-size: 0.96rem;
    }

    .footer-title {
        position: relative;
        display: inline-block;
        margin-bottom: 26px;
        padding-bottom: 14px;
        color: var(--footer-title);
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .footer-title::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 44px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--footer-accent), var(--footer-accent-strong));
        box-shadow: 0 0 16px rgba(232, 160, 48, 0.3);
    }

    .footer-social {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .footer-social a {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.16);
        color: var(--footer-accent);
        background: rgba(255, 255, 255, 0.03);
        text-decoration: none;
        transition: transform 0.25s ease, background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease;
    }

    .footer-social a:hover {
        transform: translateY(-2px);
        background: rgba(232, 160, 48, 0.14);
        border-color: rgba(232, 160, 48, 0.5);
        color: #fff4dc;
    }

    .footer-links,
    .footer-contacto {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .footer-links li + li,
    .footer-contacto li + li {
        margin-top: 12px;
    }

    .footer-links a,
    .footer-bottom a,
    .footer-contacto a {
        color: var(--footer-text);
        text-decoration: none;
        transition: color 0.25s ease, opacity 0.25s ease;
    }

    .footer-links a:hover,
    .footer-bottom a:hover,
    .footer-contacto a:hover {
        color: var(--footer-accent);
    }

    .footer-links a {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        line-height: 1.65;
    }

    .footer-links i,
    .footer-contacto i {
        color: var(--footer-accent);
        width: 18px;
        text-align: center;
        flex: 0 0 18px;
    }

    .footer-contacto li {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        color: var(--footer-text);
        line-height: 1.7;
    }

    .footer-contacto__body {
        flex: 1 1 auto;
        min-width: 0;
    }

    .footer-contacto__label {
        display: block;
        margin-bottom: 2px;
        color: var(--footer-title);
        font-weight: 600;
    }

    .footer-contacto__meta {
        color: var(--footer-muted);
        font-size: 0.93rem;
    }

    .footer-contacto__stack {
        display: grid;
        gap: 16px;
    }

    .footer-contacto__card {
        padding: 14px 16px;
        border-radius: 16px;
        background: var(--footer-surface);
        border: 1px solid var(--footer-border);
        backdrop-filter: blur(3px);
    }

    .footer-contacto__card .footer-contacto__label {
        margin-bottom: 4px;
    }

    .footer-contacto__aux {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(0, 0, 0, 0.15);
    }

    .footer-bottom__wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 0 20px;
    }

    .footer-bottom__copy {
        color: var(--footer-muted);
        font-size: 0.93rem;
        line-height: 1.6;
    }

    .footer-bottom__links {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px 18px;
        color: var(--footer-muted);
        font-size: 0.93rem;
    }

    .footer-bottom__links a {
        color: var(--footer-muted);
    }

    .footer-color-bar {
        height: 6px;
        background: linear-gradient(to right,
            var(--sp-amber, #e8a030) 0%,
            var(--sp-amber, #e8a030) 25%,
            var(--sp-naranja, #e07830) 25%,
            var(--sp-naranja, #e07830) 50%,
            var(--sp-azul, #2060b0) 50%,
            var(--sp-azul, #2060b0) 75%,
            var(--sp-rojo, #d94535) 75%,
            var(--sp-rojo, #d94535) 100%);
    }

    @media (max-width: 1199.98px) {
        .footer-principal__main {
            padding-top: 64px;
        }
    }

    @media (max-width: 991.98px) {
        .footer-principal__main {
            padding-top: 56px;
        }

        .footer-brand {
            max-width: none;
        }

        .footer-bottom__wrap {
            flex-direction: column;
            align-items: flex-start;
        }

        .footer-bottom__links {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .footer-principal__main {
            padding-top: 48px;
            padding-bottom: 24px;
        }

        .footer-title {
            margin-bottom: 22px;
        }

        .footer-contacto__card {
            padding: 14px;
        }

        .footer-social {
            gap: 10px;
        }

        .footer-bottom__wrap {
            gap: 12px;
        }
    }
</style>

<footer class="footer-principal" id="footer-principal">
    <div class="container footer-principal__main">
        <div class="row footer-principal__grid">
            <div class="col-xl-4 col-md-6">
                <div class="footer-brand">
                    <div class="footer-brand__logo">
                        <img src="<?= $escape($logoPath) ?>" alt="<?= $escape($institutionName) ?>" onerror="this.src='assets/images/logo-sin-fondo-1.png'">
                    </div>
                    <div class="footer-brand__name"><?= $escape($institutionName) ?></div>
                    <p class="footer-brand__text"><?= $escape($footerDescription) ?></p>

                    <?php if ($footerSocial !== []): ?>
                        <div class="footer-social" aria-label="Redes sociales institucionales">
                            <?php foreach ($footerSocial as $socialItem): ?>
                                <?php
                                $socialUrl = trim((string) ($socialItem['url'] ?? ''));
                                if ($socialUrl === '') {
                                    continue;
                                }
                                ?>
                                <a href="<?= $escape($normalizeUrl($socialUrl)) ?>" target="_blank" rel="noopener" aria-label="<?= $escape($socialItem['name'] ?? 'Red social') ?>">
                                    <i class="<?= $escape($socialItem['icon'] ?? 'fas fa-share-alt') ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-xl-2 col-md-6">
                <h5 class="footer-title">Menu Rapido</h5>
                <ul class="footer-links">
                    <?php foreach ($footerQuickLinks as $linkItem): ?>
                        <?php if (trim((string) ($linkItem['label'] ?? '')) === '') { continue; } ?>
                        <li>
                            <a href="<?= $escape($normalizeUrl($linkItem['url'] ?? '#')) ?>">
                                <i class="fas fa-chevron-right"></i>
                                <span><?= $escape($linkItem['label']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-xl-2 col-md-6">
                <h5 class="footer-title">Niveles</h5>
                <ul class="footer-links">
                    <?php foreach ($footerLevels as $levelItem): ?>
                        <?php if (trim((string) ($levelItem['label'] ?? '')) === '') { continue; } ?>
                        <li>
                            <a href="<?= $escape($normalizeUrl($levelItem['url'] ?? '#')) ?>">
                                <i class="fas fa-chevron-right"></i>
                                <span><?= $escape($levelItem['label']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-xl-4 col-md-6">
                <h5 class="footer-title">Contacto y Sedes</h5>

                <div class="footer-contacto__stack">
                    <?php foreach ($footerSites as $siteItem): ?>
                        <?php if (trim((string) ($siteItem['title'] ?? '')) === '' && trim((string) ($siteItem['address'] ?? '')) === '') { continue; } ?>
                        <div class="footer-contacto__card">
                            <ul class="footer-contacto">
                                <li>
                                    <i class="<?= $escape($siteItem['icon'] ?? 'fas fa-map-marker-alt') ?>"></i>
                                    <div class="footer-contacto__body">
                                        <?php if (!empty($siteItem['title'])): ?>
                                            <span class="footer-contacto__label"><?= $escape($siteItem['title']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($siteItem['address'])): ?>
                                            <div><?= $escape($siteItem['address']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($siteItem['phone'])): ?>
                                            <div class="footer-contacto__meta">Tel. <?= $escape($siteItem['phone']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>

                <ul class="footer-contacto footer-contacto__aux">
                    <?php if ($generalPhone !== ''): ?>
                        <li>
                            <i class="fas fa-phone-alt"></i>
                            <div class="footer-contacto__body">
                                <span class="footer-contacto__label">Telefono general</span>
                                <a href="<?= $escape('tel:' . preg_replace('/\s+/', '', $generalPhone)) ?>"><?= $escape($generalPhone) ?></a>
                            </div>
                        </li>
                    <?php endif; ?>

                    <?php if ($generalEmail !== ''): ?>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <div class="footer-contacto__body">
                                <span class="footer-contacto__label">Correo</span>
                                <a href="<?= $escape('mailto:' . $generalEmail) ?>"><?= $escape($generalEmail) ?></a>
                            </div>
                        </li>
                    <?php endif; ?>

                    <?php if ($websiteUrl !== '#' && $websiteLabel !== ''): ?>
                        <li>
                            <i class="fas fa-globe"></i>
                            <div class="footer-contacto__body">
                                <span class="footer-contacto__label">Sitio web</span>
                                <a href="<?= $escape($websiteUrl) ?>" target="_blank" rel="noopener"><?= $escape($websiteLabel) ?></a>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="footer-color-bar"></div>

    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom__wrap">
                <div class="footer-bottom__copy"><?= $escape($copyrightText) ?></div>

                <div class="footer-bottom__links">
                    <?php foreach ($footerLegalLinks as $legalItem): ?>
                        <?php if (trim((string) ($legalItem['label'] ?? '')) === '') { continue; } ?>
                        <a href="<?= $escape($normalizeUrl($legalItem['url'] ?? '#')) ?>"><?= $escape($legalItem['label']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</footer>
