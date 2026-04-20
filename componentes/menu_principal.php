<?php
$textoBoton = $institution['texto_boton_principal'] ?? 'Matrícula';
$urlBoton = $institution['url_boton_principal'] ?? '#';
?>
<section id="menu-principal" class="bg-white shadow-sm">
    <div class="container-fluid">
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
    </div>
</section>
