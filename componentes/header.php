<!-- esta es la la session del logo y lo menus  -->


<?php
$logoHeader = $institution['logo_header'] ?? 'assets/images/logo/logo.svg';
$nombreInstitucion = $institution['nombre'] ?? 'Sin colegio ';
$dominio = $institution['dominio'] ?? '';
$textoBoton = $institution['texto_boton_principal'] ?? 'Matrícula';
$urlBoton = cms_public_url($institution['url_boton_principal'] ?? '#');
?>
<header class="sp-header" id="header-principal">
    <div class="container-fluid">
        <div class="sp-header-row">
            <div class="d-flex align-items-center gap-3">
                <div class="sp-logo py-2">
                    <a href="/">
                        <img src="<?= e($logoHeader) ?>" alt="<?= e($nombreInstitucion) ?>" onerror="this.src='assets/images/logo/logo.svg'">
                    </a>
                </div>
                <!-- <div class="d-none d-lg-block">
                    <div class="fw-semibold" style="color:var(--sp-azul)"><?= e($nombreInstitucion) ?></div>
                    <?php if ($dominio !== ''): ?><small class="text-muted"><?= e($dominio) ?></small><?php endif; ?>
                </div> -->
            </div>

            <div class="sp-header-actions">
                <nav class="sp-nav">
                    <ul>
                        <?php foreach ($arrMenus as $i => $menu):
                            $idMenu = (int) $menu['id_menu'];
                            $active = $i === 0 ? ' class="active"' : '';
                        ?>
                            <!-- Sin desplegable de submenús en escritorio: el menú enlaza directo a su página -->
                            <li<?= $active ?>>
                                <a href="<?= e(cms_public_menu_url($menu, $arrSubs[$idMenu] ?? [], $arrMenus)) ?>">
                                    <?= e($menu['nombre']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <a href="<?= e($urlBoton) ?>" class="sp-btn-matricula sp-header-desktop-cta"><?= e($textoBoton) ?></a>

                <a href="#" class="sp-btn-matricula sp-header-toggle" data-bs-toggle="offcanvas" data-bs-target="#spHeaderMobileNav" aria-controls="spHeaderMobileNav" aria-label="Abrir menú principal">
                    <i class="fas fa-bars"></i>
                </a>
            </div>
        </div>
    </div>
</header>

<div class="offcanvas offcanvas-end" tabindex="-1" id="spHeaderMobileNav" aria-labelledby="spHeaderMobileNavLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="spHeaderMobileNavLabel"><?= e($nombreInstitucion) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex flex-column gap-2">
            <?php foreach ($arrMenus as $menu):
                $idMenu = (int) $menu['id_menu'];
                $hasSubs = !empty($arrSubs[$idMenu]);
                $headerMenuPageUrl = cms_menu_page_url($menu, $arrSubs[$idMenu] ?? [], $arrMenus);
            ?>
                <div class="border rounded-4 p-3">
                    <a href="<?= e(cms_public_menu_url($menu, $arrSubs[$idMenu] ?? [], $arrMenus)) ?>" class="d-flex align-items-center justify-content-between text-decoration-none text-dark fw-semibold">
                        <span><?= e($menu['nombre']) ?></span>
                        <?php if ($hasSubs): ?><i class="fas fa-chevron-right small text-muted"></i><?php endif; ?>
                    </a>
                    <?php if ($hasSubs): ?>
                        <div class="mt-2 d-flex flex-column gap-2">
                            <?php foreach ($arrSubs[$idMenu] as $sub): ?>
                                <a href="<?= e(cms_public_header_submenu_url($sub, $menu, $headerMenuPageUrl)) ?>" class="text-decoration-none text-muted small"><?= e($sub['nombre']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <a href="<?= e($urlBoton) ?>" class="sp-btn-matricula justify-content-center mt-2"><?= e($textoBoton) ?></a>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/public_floating_actions.php'; ?>
