<?php
$newsItems    = array_values(array_filter($sectionItemsMap['noticias_home'] ?? [], static fn($i) => ($i['visible'] ?? 'si') === 'si'));
$cantidad     = (int) cfg($sectionConfigsMap, 'noticias_home', 'cantidad_items', '12');
$newsItems    = array_slice($newsItems, 0, max(1, $cantidad));
$tituloBloque = cfg($sectionConfigsMap, 'noticias_home', 'titulo_bloque', 'Últimas Noticias');
$subtitulo    = cfg($sectionConfigsMap, 'noticias_home', 'subtitulo_bloque', 'Novedades');
$textoBoton   = cfg($sectionConfigsMap, 'noticias_home', 'texto_boton', 'Ver todas las noticias');
$urlBoton     = cfg($sectionConfigsMap, 'noticias_home', 'url_boton', 'todas_noticias.php');

// Color institucional para los botones de navegación
$noticiasPrimary = '#2060B0';
if (!empty($institution['color_primario']) && preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $institution['color_primario'])) {
    $noticiasPrimary = $institution['color_primario'];
}
?>
<section class="sp-noticias" id="noticias">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <span class="section-label"><?= e($subtitulo) ?></span>
                <h2 class="section-title"><?= e($tituloBloque) ?></h2>
                <div class="divider-line"></div>
            </div>
            <a href="<?= e($urlBoton) ?>" class="btn-ver-mas"><?= e($textoBoton) ?></a>
        </div>

        <div class="noticias-slider-wrap">
            <!-- Flecha anterior -->
            <button class="noticias-nav-btn noticias-prev" id="noticias-prev" aria-label="Noticias anteriores">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="swiper noticias-swiper" id="noticias-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($newsItems as $item): ?>
                        <?php
                        $categoria = $item['etiqueta'] ?? '';
                        if (!empty($item['id_categoria']) && isset($categoriesById[(int) $item['id_categoria']])) {
                            $categoria = $categoriesById[(int) $item['id_categoria']]['nombre'];
                        }
                        $cardUrl = !empty($item['boton_1_url']) ? $item['boton_1_url'] : $urlBoton;
                        ?>
                        <div class="swiper-slide">
                            <a class="noticia-card noticia-card--link" href="<?= e($cardUrl) ?>">
                                <div class="img-wrap">
                                    <img src="<?= e($item['imagen'] ?: 'assets/images/frontis_01.jpg') ?>"
                                         alt="<?= e($item['titulo']) ?>"
                                         loading="lazy">
                                </div>
                                <div class="card-body">
                                    <?php if ($categoria !== ''): ?>
                                        <span class="tag"><?= e($categoria) ?></span>
                                    <?php endif; ?>
                                    <h5><?= e($item['titulo']) ?></h5>
                                    <p><?= e($item['descripcion']) ?></p>
                                    <?php if (!empty($item['fecha_publicacion'])): ?>
                                        <div class="meta">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?= e($item['fecha_publicacion']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Flecha siguiente -->
            <button class="noticias-nav-btn noticias-next" id="noticias-next" aria-label="Noticias siguientes">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

<style>
.noticias-slider-wrap {
    position: relative;
    padding: 0 54px;
}
.noticias-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid <?= e($noticiasPrimary) ?>;
    color: <?= e($noticiasPrimary) ?>;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    box-shadow: 0 4px 16px rgba(0,0,0,.10);
    transition: background .2s, color .2s, box-shadow .2s, transform .2s;
    outline: none;
}
.noticias-nav-btn:hover {
    background: <?= e($noticiasPrimary) ?>;
    color: #fff;
    box-shadow: 0 6px 20px rgba(0,0,0,.16);
    transform: translateY(-50%) scale(1.08);
}
.noticias-prev { left: 0; }
.noticias-next { right: 0; }
.noticias-nav-btn.swiper-button-disabled {
    opacity: .3;
    pointer-events: none;
}
.noticias-swiper { overflow: hidden; }
.noticia-card--link {
    display: block;
    text-decoration: none;
    color: inherit;
    height: 100%;
}
.noticia-card--link:hover { color: inherit; text-decoration: none; }
.swiper-slide { height: auto; }
.noticias-swiper .noticia-card { height: 100%; }
@media (max-width: 575px) {
    .noticias-slider-wrap { padding: 0 40px; }
    .noticias-nav-btn { width: 36px; height: 36px; font-size: 13px; }
}
</style>

<script>
(function () {
    var totalSlides = <?= count($newsItems) ?>;

    function initNoticiasSwiper() {
        if (!window.Swiper) {
            setTimeout(initNoticiasSwiper, 80);
            return;
        }
        new Swiper('#noticias-swiper', {
            slidesPerView: 1.15,
            spaceBetween: 16,
            loop: totalSlides > 4,
            navigation: {
                nextEl: '#noticias-next',
                prevEl: '#noticias-prev',
            },
            breakpoints: {
                576:  { slidesPerView: 1.6,  spaceBetween: 18 },
                768:  { slidesPerView: 2.2,  spaceBetween: 20 },
                992:  { slidesPerView: 3,    spaceBetween: 22 },
                1200: { slidesPerView: 4,    spaceBetween: 24 },
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNoticiasSwiper);
    } else {
        initNoticiasSwiper();
    }
})();
</script>
