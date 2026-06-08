<?php
$heroItems = $sectionItemsMap['hero_principal'] ?? [];
$mostrarIndicadores = cfg($sectionConfigsMap, 'hero_principal', 'mostrar_indicadores', 'si') === 'si';
$mostrarFlechas = cfg($sectionConfigsMap, 'hero_principal', 'mostrar_flechas', 'si') === 'si';
$heroHasYoutube = false;

if (!function_exists('hero_youtube_video_id')) {
    function hero_youtube_video_id(string $url): string
    {
        $url = trim($url);
        if ($url === '' || !preg_match('/(?:youtube\.com|youtu\.be)/i', $url)) {
            return '';
        }

        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~i', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('~youtube\.com/(?:embed|shorts)/([A-Za-z0-9_-]{6,})~i', $url, $matches)) {
            return $matches[1];
        }

        $parts = parse_url($url);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
            if (!empty($query['v']) && preg_match('/^[A-Za-z0-9_-]{6,}$/', (string) $query['v'])) {
                return (string) $query['v'];
            }
        }

        return '';
    }
}
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
                <?php
                    $youtubeId = hero_youtube_video_id((string) ($item['url'] ?? ''));
                    $heroHasYoutube = $heroHasYoutube || $youtubeId !== '';
                    $youtubeEmbed = $youtubeId !== ''
                        ? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($youtubeId) . '?autoplay=1&mute=1&controls=0&loop=1&playlist=' . rawurlencode($youtubeId) . '&playsinline=1&rel=0&modestbranding=1&iv_load_policy=3&disablekb=1&enablejsapi=1'
                        : '';
                ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?> <?= $youtubeId !== '' ? 'carousel-item--video' : '' ?>" data-bs-interval="<?= $youtubeId !== '' ? '12000' : '5000' ?>">
                    <?php if ($youtubeEmbed !== ''): ?>
                        <div class="slide-video-bg" style="position:absolute;inset:0;z-index:1;overflow:hidden;background:#111827;">
                            <iframe
                                src="<?= e($youtubeEmbed) ?>"
                                data-src="<?= e($youtubeEmbed) ?>"
                                title="<?= e(trim(($item['titulo_linea_1'] ?? '') . ' ' . ($item['titulo_linea_2'] ?? '') . ' ' . ($item['titulo_linea_3'] ?? '')) ?: 'Video del carrusel') ?>"
                                width="1920"
                                height="1080"
                                style="position:absolute;top:50%;left:50%;width:100vw;min-width:100%;height:56.25vw;min-height:100%;border:0;transform:translate(-50%,-50%);pointer-events:none;"
                                allow="autoplay; encrypted-media; picture-in-picture"
                                loading="eager"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                        </div>
                    <?php else: ?>
                        <div class="slide-bg" style="background-image:url('<?= e($item['imagen'] ?: 'assets/images/portada_1.jpg') ?>')"></div>
                    <?php endif; ?>
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

        <?php if ($heroHasYoutube): ?>
            <button type="button" class="sp-video-sound" data-sp-video-sound aria-label="Activar sonido" aria-pressed="false" hidden>
                <i class="fas fa-volume-mute"></i>
            </button>
        <?php endif; ?>

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
<script>
(function () {
    var carousel = document.getElementById('heroCarousel');
    if (!carousel) {
        return;
    }

    function playVideo(slide) {
        var iframe = slide ? slide.querySelector('.slide-video-bg iframe') : null;
        if (iframe && !iframe.getAttribute('src')) {
            iframe.setAttribute('src', iframe.getAttribute('data-src'));
        }
    }

    function stopInactiveVideos() {
        carousel.querySelectorAll('.carousel-item:not(.active) .slide-video-bg iframe').forEach(function (iframe) {
            iframe.removeAttribute('src');
        });
    }

    function setSoundButtonState(button, soundOn) {
        button.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
        button.setAttribute('aria-label', soundOn ? 'Silenciar video' : 'Activar sonido');
        button.innerHTML = '<i class="fas ' + (soundOn ? 'fa-volume-up' : 'fa-volume-mute') + '"></i>';
    }

    function updateSoundButton(slide) {
        var button = carousel.querySelector('[data-sp-video-sound]');
        var hasVideo = !!(slide && slide.querySelector('.slide-video-bg iframe'));

        if (!button) {
            return;
        }

        button.hidden = !hasVideo;
        setSoundButtonState(button, false);
    }

    playVideo(carousel.querySelector('.carousel-item.active'));
    updateSoundButton(carousel.querySelector('.carousel-item.active'));
    carousel.addEventListener('slide.bs.carousel', function (event) {
        playVideo(event.relatedTarget);
        updateSoundButton(event.relatedTarget);
    });
    carousel.addEventListener('slid.bs.carousel', stopInactiveVideos);

    carousel.querySelectorAll('[data-sp-video-sound]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }

            var slide = carousel.querySelector('.carousel-item.active');
            var iframe = slide ? slide.querySelector('.slide-video-bg iframe') : null;
            var isSoundOn = button.getAttribute('aria-pressed') === 'true';

            if (!iframe) {
                return;
            }

            playVideo(slide);
            iframe.contentWindow.postMessage(JSON.stringify({
                event: 'command',
                func: isSoundOn ? 'mute' : 'unMute',
                args: []
            }), '*');

            setSoundButtonState(button, !isSoundOn);
        });
    });
})();
</script>
<?php endif; ?>
