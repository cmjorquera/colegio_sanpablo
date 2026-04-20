<?php
$logoHeader = $institution['logo_header'] ?? 'assets/images/logo/logo.svg';
$textoBoton = $institution['texto_boton_principal'] ?? 'Matrícula';
$urlBoton = $institution['url_boton_principal'] ?? '#';
?>
<header class="sp-header">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between">
            <div class="sp-logo py-2">
                <a href="#">
                    <img src="<?= e($logoHeader) ?>" alt="<?= e($institution['nombre'] ?? 'Colegio San Pablo') ?>" onerror="this.src='assets/images/logo/logo.svg'">
                </a>
            </div>
            <nav class="sp-nav">
                <ul>
                    <?php foreach ($arrMenus as $i => $menu):
                        $idMenu = (int) $menu['id_menu'];
                        $hasSubs = !empty($arrSubs[$idMenu]);
                        $active = $i === 0 ? ' class="active"' : '';
                    ?>
                        <li<?= $active ?>>
                            <a href="<?= e($menu['url'] ?: '#') ?>">
                                <?= e($menu['nombre']) ?><?= $hasSubs ? ' <span aria-hidden="true">&#9662;</span>' : '' ?>
                            </a>
                            <?php if ($hasSubs): ?>
                                <ul class="dropdown">
                                    <?php foreach ($arrSubs[$idMenu] as $sub): ?>
                                        <li><a href="<?= e($sub['url'] ?: '#') ?>"><?= e($sub['nombre']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    <li><a href="<?= e($urlBoton) ?>" class="sp-btn-matricula"><?= e($textoBoton) ?></a></li>
                </ul>
            </nav>
            <button class="btn d-lg-none" style="font-size:22px;color:var(--sp-azul)">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>
