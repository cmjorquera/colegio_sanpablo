<?php
$logoHeader = $institution['logo_header'] ?? 'assets/images/logo/logo.svg';
$nombreInstitucion = $institution['nombre'] ?? 'Colegio San Pablo';
$dominio = $institution['dominio'] ?? '';
?>
<header class="sp-header" id="header-principal">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between">
            <div class="sp-logo py-2">
                <a href="#">
                    <img src="<?= e($logoHeader) ?>" alt="<?= e($nombreInstitucion) ?>" onerror="this.src='assets/images/logo/logo.svg'">
                </a>
            </div>
            <div class="text-end d-none d-lg-block">
                <div class="fw-semibold" style="color:var(--sp-azul)"><?= e($nombreInstitucion) ?></div>
                <?php if ($dominio !== ''): ?><small class="text-muted"><?= e($dominio) ?></small><?php endif; ?>
            </div>
        </div>
    </div>
</header>
