<?php
$email = $institution['email'] ?? '';
$telefono = $institution['telefono'] ?? '';
$direccion = $institution['direccion'] ?? '';
?>
<div class="sp-topbar d-none d-md-block" id="topbar">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <?php if ($direccion !== ''): ?>
                    <i class="fas fa-map-marker-alt me-2" style="color:var(--sp-amarillo)"></i><?= e($direccion) ?>
                <?php endif; ?>
                <?php if ($telefono !== ''): ?>
                    <span class="sep">|</span>
                    <i class="fas fa-phone me-2" style="color:var(--sp-amarillo)"></i><?= e($telefono) ?>
                <?php endif; ?>
                <?php if ($email !== ''): ?>
                    <span class="sep">|</span>
                    <i class="fas fa-envelope me-2" style="color:var(--sp-amarillo)"></i>
                    <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="#configuracion" class="sp-login-btn" title="Configuración institucional">
                    <i class="fas fa-cog"></i>
                    <span>Institución</span>
                </a>
                <?php if (!empty($institution['instagram'])): ?>
                    <span class="sep">|</span>
                    <a href="<?= e($institution['instagram']) ?>" target="_blank" rel="noopener" class="me-1"><i class="fab fa-instagram me-1"></i>Instagram</a>
                <?php endif; ?>
                <?php if (!empty($institution['facebook'])): ?>
                    <a href="<?= e($institution['facebook']) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook me-1"></i>Facebook</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
