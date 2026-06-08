<?php
$newsItems    = array_values(array_filter($sectionItemsMap['noticias_home'] ?? [], static fn($i) => ($i['visible'] ?? 'si') === 'si'));
$tituloBloque = cfg($sectionConfigsMap, 'noticias_home', 'titulo_bloque', 'Últimas Noticias');
$subtitulo    = cfg($sectionConfigsMap, 'noticias_home', 'subtitulo_bloque', 'Novedades');
$textoBoton   = cfg($sectionConfigsMap, 'noticias_home', 'texto_boton', 'Ver todas las noticias');
$urlBoton     = cfg($sectionConfigsMap, 'noticias_home', 'url_boton', 'todas_noticias.php');

// Color institucional para los botones de navegación
$noticiasPrimary = '#2060B0';
if (!empty($institution['color_primario']) && preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $institution['color_primario'])) {
    $noticiasPrimary = $institution['color_primario'];
}
$noticiasAccent = '#E07830';
if (!empty($institution['color_secundario']) && preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $institution['color_secundario'])) {
    $noticiasAccent = $institution['color_secundario'];
}
$noticiasButton = '#1976D2';
if (!empty($institution['color_terciario']) && preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $institution['color_terciario'])) {
    $noticiasButton = $institution['color_terciario'];
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
                        $cardUrl = 'noticia_detalle.php?id=' . (int) ($item['id_item'] ?? 0);
                        $buttonText = trim((string) ($item['boton_1_texto'] ?? ''));
                        if ($buttonText === '') {
                            $buttonText = 'Leer más';
                        }
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
                                    <span class="noticia-card-cta"><?= e($buttonText) ?><i class="fas fa-arrow-right"></i></span>
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
.noticias-swiper .swiper-wrapper {
    align-items: stretch;
}
.noticias-swiper .swiper-slide {
    display: flex;
    height: auto;
}
.noticia-card--link {
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
    height: 100%;
    min-height: 560px;
    width: 100%;
}
.noticia-card--link:hover { color: inherit; text-decoration: none; }
.noticias-swiper .noticia-card { height: auto; }
.noticias-swiper .noticia-card .img-wrap {
    flex: 0 0 190px;
    height: 190px;
}
.noticias-swiper .noticia-card .card-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    padding: 24px 22px 22px;
}
.noticias-swiper .noticia-card h5 {
    display: -webkit-box;
    min-height: 46px;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
.noticias-swiper .noticia-card p {
    display: -webkit-box;
    min-height: 132px;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 7;
}
.noticias-swiper .noticia-card .meta {
    margin-top: 2px;
}
.noticia-card-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 48px;
    margin: auto auto 0;
    padding: 12px 28px;
    border-radius: 10px;
    background: <?= e($noticiasButton) ?>;
    color: #fff;
    font-weight: 700;
    font-size: 15px;
    line-height: 1.15;
    text-align: center;
    box-shadow: 0 10px 22px rgba(32, 96, 176, .18);
    transition: background .2s ease, box-shadow .2s ease, transform .2s ease;
}
.noticia-card-cta i {
    font-size: 12px;
    transition: transform .2s ease;
}
.noticia-card--link:hover .noticia-card-cta {
    background: <?= e($noticiasAccent) ?>;
    color: #fff;
    box-shadow: 0 12px 26px rgba(224, 120, 48, .2);
    transform: translateY(-1px);
}
.noticia-card--link:hover .noticia-card-cta i {
    transform: translateX(3px);
}
@media (max-width: 575px) {
    .noticias-slider-wrap { padding: 0 40px; }
    .noticias-nav-btn { width: 36px; height: 36px; font-size: 13px; }
    .noticia-card--link { min-height: 520px; }
    .noticias-swiper .noticia-card .img-wrap {
        flex-basis: 170px;
        height: 170px;
    }
    .noticias-swiper .noticia-card p {
        min-height: 112px;
        -webkit-line-clamp: 6;
    }
    .noticia-card-cta {
        width: 100%;
        padding-inline: 18px;
    }
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
            speed: 700,
            slidesPerGroup: 1,
            autoplay: totalSlides > 4 ? {
                delay: 3000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            } : false,
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

