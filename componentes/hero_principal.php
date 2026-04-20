<?php
$heroItems = $sectionItemsMap['hero_principal'] ?? [];
$mostrarIndicadores = cfg($sectionConfigsMap, 'hero_principal', 'mostrar_indicadores', 'si') === 'si';
$mostrarFlechas = cfg($sectionConfigsMap, 'hero_principal', 'mostrar_flechas', 'si') === 'si';
?>
<?php if ($heroItems): ?>
<section class="sp-carousel-hero" id="hero-principal">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <?php if ($mostrarIndicadores): ?>
            <div class="carousel-indicators">
                <?php foreach ($heroItems as $index => $item): ?>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" <?= $index === 0 ? 'aria-current="true"' : '' ?>></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="carousel-inner">
            <?php foreach ($heroItems as $index => $item): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <div class="slide-bg" style="background-image:url('<?= e($item['imagen'] ?: 'assets/images/portada_1.jpg') ?>')"></div>
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="slide-texto">
                            <span class="slide-label"><i class="fas fa-star"></i> <?= e($item['etiqueta'] ?: ($institution['nombre'] ?? 'Colegio San Pablo')) ?></span>
                            <h2>
                                <?= e($item['titulo_linea_1'] ?: '') ?><br>
                                <strong><?= e($item['titulo_linea_2'] ?: '') ?></strong><br>
                                <?= e($item['titulo_linea_3'] ?: '') ?>
                            </h2>
                            <?php if (!empty($item['descripcion'])): ?>
                                <p class="text-white mb-4"><?= e($item['descripcion']) ?></p>
                            <?php endif; ?>
                            <div class="slide-botones">
                                <?php if (!empty($item['boton_1_texto'])): ?><a href="<?= e($item['boton_1_url'] ?: '#') ?>" class="slide-btn-primary"><?= e($item['boton_1_texto']) ?></a><?php endif; ?>
                                <?php if (!empty($item['boton_2_texto'])): ?><a href="<?= e($item['boton_2_url'] ?: '#') ?>" class="slide-btn-outline"><?= e($item['boton_2_texto']) ?></a><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($mostrarFlechas): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-ctrl-icon"><i class="fas fa-chevron-left"></i></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-ctrl-icon"><i class="fas fa-chevron-right"></i></span>
            </button>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
