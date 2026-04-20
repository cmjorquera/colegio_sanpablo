<footer class="sp-footer" id="footer-principal">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <div class="logo-footer mb-3">
                    <img src="<?= e($institution['logo_footer'] ?? $institution['logo_header'] ?? 'assets/images/logo-sin-fondo-1.png') ?>" alt="<?= e($institution['nombre'] ?? 'Colegio San Pablo')?>" onerror="this.src='assets/images/logo-sin-fondo-1.png'">
                </div>
                <p><?= e($institution['nombre'] ?? 'Colegio San Pablo') ?> acompaña a su comunidad con una propuesta educativa integral y cercana.</p>
                <div class="social-links mt-3">
                    <?php if (!empty($institution['instagram'])): ?><a href="<?= e($institution['instagram']) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    <?php if (!empty($institution['facebook'])): ?><a href="<?= e($institution['facebook']) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                    <?php if (!empty($institution['youtube'])): ?><a href="<?= e($institution['youtube']) ?>" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a><?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <h5>Menú rápido</h5>
                <ul class="list-unstyled mt-3">
                    <?php foreach ($arrMenus as $menu): ?>
                        <li class="mb-2"><a href="<?= e($menu['url'] ?: '#') ?>"><i class="fas fa-chevron-right me-2"></i><?= e($menu['nombre']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5>Contacto</h5>
                <ul class="list-unstyled mt-3">
                    <?php if (!empty($institution['direccion'])): ?><li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><?= e($institution['direccion']) ?></li><?php endif; ?>
                    <?php if (!empty($institution['telefono'])): ?><li class="mb-2"><i class="fas fa-phone me-2"></i><?= e($institution['telefono']) ?></li><?php endif; ?>
                    <?php if (!empty($institution['email'])): ?><li class="mb-2"><i class="fas fa-envelope me-2"></i><a href="mailto:<?= e($institution['email']) ?>"><?= e($institution['email']) ?></a></li><?php endif; ?>
                    <?php if (!empty($institution['dominio'])): ?><li class="mb-2"><i class="fas fa-globe me-2"></i><?= e($institution['dominio']) ?></li><?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="sp-footer-colorband"></div>
</footer>
