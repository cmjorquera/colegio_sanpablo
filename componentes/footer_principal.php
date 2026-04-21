<?php
$escape = static function ($value): string {
    if (function_exists('e')) {
        return e((string) $value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$normalizeLabel = static function (?string $value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (is_string($converted) && $converted !== '') {
            $value = $converted;
        }
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
    return trim((string) $value);
};

$normalizeUrl = static function (?string $url): string {
    $url = trim((string) $url);
    if ($url === '' || $url === '#') {
        return '#';
    }

    if (
        preg_match('~^(https?:)?//~i', $url) === 1
        || str_starts_with($url, 'mailto:')
        || str_starts_with($url, 'tel:')
        || str_starts_with($url, '#')
        || str_starts_with($url, '/')
        || preg_match('~^[a-z0-9_-]+\.php(\?.*)?$~i', $url) === 1
    ) {
        return $url;
    }

    return 'https://' . ltrim($url, '/');
};

$buildFooterMenuTree = static function (array $menus, array $subs): array {
    $items = [];

    foreach ($menus as $menu) {
        $menuId = (int) ($menu['id_menu'] ?? 0);
        if ($menuId < 1) {
            continue;
        }

        $children = [];
        foreach (($subs[$menuId] ?? []) as $subMenu) {
            $children[] = [
                'id' => (int) ($subMenu['id_sub_menu'] ?? 0),
                'label' => trim((string) ($subMenu['nombre'] ?? '')),
                'url' => trim((string) ($subMenu['url'] ?? '')) ?: '#',
            ];
        }

        $items[] = [
            'id' => $menuId,
            'label' => trim((string) ($menu['nombre'] ?? '')),
            'url' => trim((string) ($menu['url'] ?? '')) ?: '#',
            'children' => $children,
        ];
    }

    return $items;
};

$mapFooterSiteItem = static function (array $item): array {
    $label = trim((string) ($item['titulo'] ?? ''));
    $label = $label !== '' ? $label : trim((string) ($item['etiqueta'] ?? ''));

    $address = trim((string) ($item['descripcion'] ?? ''));
    if ($address === '') {
        $address = trim((string) ($item['subtitulo'] ?? ''));
    }

    $phone = trim((string) ($item['boton_1_texto'] ?? ''));
    if ($phone === '' && preg_match('/\d/', (string) ($item['subtitulo'] ?? ''))) {
        $phone = trim((string) ($item['subtitulo'] ?? ''));
    }
    if ($phone === '' && preg_match('/\d/', (string) ($item['boton_2_texto'] ?? ''))) {
        $phone = trim((string) ($item['boton_2_texto'] ?? ''));
    }

    $icon = trim((string) ($item['boton_1_url'] ?? ''));
    if ($icon === '' || strpos($icon, 'fa') === false) {
        $icon = 'fas fa-map-marker-alt';
    }

    return [
        'title' => $label,
        'address' => $address,
        'phone' => $phone,
        'icon' => $icon,
    ];
};

$institutionData = is_array($institution ?? null) ? $institution : [];
$footerSectionConfig = is_array($sectionConfigsMap['footer_principal'] ?? null) ? $sectionConfigsMap['footer_principal'] : [];
$footerSectionItems = is_array($sectionItemsMap['footer_principal'] ?? null) ? $sectionItemsMap['footer_principal'] : [];

$footerQuickLinksFallback = [
    ['id' => 0, 'label' => 'Inicio', 'url' => '#', 'children' => []],
    ['id' => 0, 'label' => 'Institucional', 'url' => '#', 'children' => []],
    ['id' => 0, 'label' => 'Noticias', 'url' => '#', 'children' => []],
];

$footerLevelsFallback = [
    ['id' => 0, 'label' => 'Maternal', 'url' => '#', 'children' => []],
    ['id' => 0, 'label' => 'Inicial', 'url' => '#', 'children' => []],
    ['id' => 0, 'label' => 'Primaria', 'url' => '#', 'children' => []],
];

$footerSitesFallback = [
    ['title' => 'Administracion', 'address' => 'Venancio Benavidez 3612', 'phone' => '', 'icon' => 'fas fa-building'],
    ['title' => 'Inicial', 'address' => 'Joaquin Suarez 3596', 'phone' => '2336 6000', 'icon' => 'fas fa-school'],
    ['title' => 'Preuniversitario', 'address' => 'Av. Millan 3375', 'phone' => '2202 0000', 'icon' => 'fas fa-graduation-cap'],
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

$copyrightText = trim((string) ($footerSectionConfig['copyright_text'] ?? ''));
$copyrightText = $copyrightText !== ''
    ? $copyrightText
    : '© 2026 ' . $institutionName . '. Todos los derechos reservados.';

$allFooterMenus = $buildFooterMenuTree(
    is_array($arrMenus ?? null) ? $arrMenus : [],
    is_array($arrSubs ?? null) ? $arrSubs : []
);

$levelNames = [
    'maternal',
    'inicial',
    'primaria',
    '3er ciclo ebi',
    'tercer ciclo ebi',
    'bachillerato',
    'libre asistido',
];

$footerLevels = [];
$footerQuickLinks = [];

foreach ($allFooterMenus as $menuItem) {
    $normalizedName = $normalizeLabel($menuItem['label'] ?? '');
    if ($normalizedName === '') {
        continue;
    }

    if (in_array($normalizedName, $levelNames, true)) {
        $footerLevels[] = $menuItem;
        continue;
    }

    $footerQuickLinks[] = $menuItem;
}

if ($footerQuickLinks === []) {
    $footerQuickLinks = $footerQuickLinksFallback;
}

if ($footerLevels === []) {
    $footerLevels = $footerLevelsFallback;
}

$footerSocial = [];
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

$footerSites = [];
foreach ($footerSectionItems as $item) {
    $mappedItem = $mapFooterSiteItem($item);
    if ($mappedItem['title'] === '' && $mappedItem['address'] === '') {
        continue;
    }
    $footerSites[] = $mappedItem;
}

if ($footerSites === []) {
    $footerSites = $footerSitesFallback;
}

$websiteUrl = trim((string) ($footerSectionConfig['sitio_url'] ?? $institutionData['dominio'] ?? ''));
if ($websiteUrl === '' && !empty($_SERVER['HTTP_HOST'])) {
    $websiteUrl = (string) $_SERVER['HTTP_HOST'];
}
$websiteUrl = $normalizeUrl($websiteUrl);
$websiteLabel = $websiteUrl !== '#'
    ? preg_replace('~^https?://~i', '', $websiteUrl)
    : trim((string) ($footerSectionConfig['sitio_label'] ?? ''));
$websiteLabel = rtrim((string) $websiteLabel, '/');

$generalPhone = trim((string) ($footerSectionConfig['telefono_general'] ?? $institutionData['telefono'] ?? ''));
$generalEmail = trim((string) ($footerSectionConfig['correo_general'] ?? $institutionData['email'] ?? ''));

$footerLegalLinks = [];
if (
    !empty($footerSectionConfig['politica_url'])
    || !empty($footerSectionConfig['terminos_url'])
    || !empty($footerSectionConfig['admisiones_url'])
) {
    $footerLegalLinks = [
        ['label' => 'Politica de privacidad', 'url' => trim((string) ($footerSectionConfig['politica_url'] ?? '#')) ?: '#'],
        ['label' => 'Terminos legales', 'url' => trim((string) ($footerSectionConfig['terminos_url'] ?? '#')) ?: '#'],
        ['label' => 'Admisiones', 'url' => trim((string) ($footerSectionConfig['admisiones_url'] ?? '#')) ?: '#'],
    ];
}

if ($footerLegalLinks === []) {
    $footerLegalLinks = $footerLegalLinksFallback;
}

$renderFooterMenuList = static function (array $items, string $listIdPrefix) use ($escape, $normalizeUrl): void {
    ?>
    <ul class="footer-links footer-menu-list">
        <?php foreach ($items as $index => $item): ?>
            <?php
            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $url = trim((string) ($item['url'] ?? '')) ?: '#';
            $children = is_array($item['children'] ?? null) ? $item['children'] : [];
            $hasChildren = $children !== [];
            $submenuId = $listIdPrefix . '-' . ((int) ($item['id'] ?? 0)) . '-' . $index;
            ?>
            <li class="footer-menu-item<?= $hasChildren ? ' has-children' : '' ?>">
                <?php if ($hasChildren): ?>
                    <button
                        class="footer-menu-toggle"
                        type="button"
                        aria-expanded="false"
                        aria-controls="<?= $escape($submenuId) ?>"
                        data-target="<?= $escape($submenuId) ?>"
                        data-url="<?= $escape($normalizeUrl($url)) ?>"
                    >
                        <span class="footer-menu-toggle__label">
                            <i class="fas fa-chevron-right footer-menu-item__bullet"></i>
                            <span><?= $escape($label) ?></span>
                        </span>
                        <i class="fas fa-chevron-down footer-menu-toggle__arrow" aria-hidden="true"></i>
                    </button>
                    <ul class="footer-submenu" id="<?= $escape($submenuId) ?>" hidden>
                        <?php foreach ($children as $child): ?>
                            <?php if (trim((string) ($child['label'] ?? '')) === '') { continue; } ?>
                            <li>
                                <a href="<?= $escape($normalizeUrl($child['url'] ?? '#')) ?>">
                                    <i class="fas fa-angle-right"></i>
                                    <span><?= $escape($child['label']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <a href="<?= $escape($normalizeUrl($url)) ?>">
                        <i class="fas fa-chevron-right footer-menu-item__bullet"></i>
                        <span><?= $escape($label) ?></span>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
};
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

    .footer-menu-item > a,
    .footer-menu-toggle,
    .footer-submenu a {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        line-height: 1.65;
    }

    .footer-menu-item > a,
    .footer-submenu a {
        color: var(--footer-text);
    }

    .footer-menu-toggle {
        border: 0;
        background: transparent;
        color: var(--footer-text);
        padding: 0;
        text-align: left;
    }

    .footer-menu-toggle:hover {
        color: var(--footer-accent);
    }

    .footer-menu-toggle__label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .footer-menu-item__bullet,
    .footer-submenu i,
    .footer-contacto i {
        color: var(--footer-accent);
        width: 18px;
        text-align: center;
        flex: 0 0 18px;
    }

    .footer-menu-toggle__arrow {
        color: var(--footer-accent);
        transition: transform 0.25s ease;
        flex: 0 0 auto;
    }

    .footer-menu-item.is-open .footer-menu-toggle__arrow {
        transform: rotate(180deg);
    }

    .footer-submenu {
        list-style: none;
        margin: 12px 0 0;
        padding: 0 0 0 28px;
    }

    .footer-submenu li + li {
        margin-top: 10px;
    }

    .footer-submenu a {
        justify-content: flex-start;
        gap: 10px;
        color: var(--footer-muted);
        font-size: 0.93rem;
    }

    .footer-submenu a:hover {
        color: var(--footer-accent);
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
                                <a href="<?= $escape($normalizeUrl($socialItem['url'] ?? '#')) ?>" target="_blank" rel="noopener" aria-label="<?= $escape($socialItem['name'] ?? 'Red social') ?>">
                                    <i class="<?= $escape($socialItem['icon'] ?? 'fas fa-share-alt') ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-xl-2 col-md-6">
                <h5 class="footer-title">Menu Rapido</h5>
                <?php $renderFooterMenuList($footerQuickLinks, 'footer-quick'); ?>
            </div>

            <div class="col-xl-2 col-md-6">
                <h5 class="footer-title">Niveles</h5>
                <?php $renderFooterMenuList($footerLevels, 'footer-levels'); ?>
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

<script src="assets/js/footer_principal.js"></script>
