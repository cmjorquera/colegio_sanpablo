<?php
$videoSectionName = 'video_destacado_home';
$videoItems = $sectionItemsMap[$videoSectionName] ?? [];
$videoItem  = $videoItems[0] ?? [];
$videoImagen = cfg($sectionConfigsMap, $videoSectionName, 'imagen_fondo', '');
$videoUrl    = trim((string) ($videoItem['url'] ?? ''));
$videoUrl    = $videoUrl !== '' ? $videoUrl : cfg($sectionConfigsMap, $videoSectionName, 'video_url', '');
$isYoutubeVideo = (bool) preg_match('/(youtube\.com|youtu\.be)/i', $videoUrl);

// Auto-thumbnail de YouTube cuando no hay imagen_fondo configurada
if ($videoImagen === '' && $isYoutubeVideo) {
    preg_match('/(?:v=|youtu\.be\/|\/embed\/)([a-zA-Z0-9_-]{11})/', $videoUrl, $ytMatches);
    $ytVideoId = $ytMatches[1] ?? '';
    if ($ytVideoId !== '') {
        $videoImagen = 'https://img.youtube.com/vi/' . $ytVideoId . '/maxresdefault.jpg';
    }
}
if ($videoImagen === '') {
    $videoImagen = 'assets/images/video/video-eight-image.jpg';
}
?>

<div class="banner-video-four-area bg-image vd-hero" id="video-destacado-home"
     data-background="<?= e($videoImagen) ?>"
     style="background-image:url('<?= e($videoImagen) ?>');">
    <div class="vd-hero__overlay"></div>
    <div class="banner-video__video-btn">
        <div class="video-btn video-pulse">
            <button class="vd-play-btn" aria-label="Reproducir video" data-url="<?= e($videoUrl) ?>">
                <i class="fa-solid fa-play"></i>
            </button>
        </div>
    </div>
</div>

<?php if (!$isYoutubeVideo && $videoUrl !== ''): ?>
<div id="vd-overlay" class="vd-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Reproductor de video">
    <div class="vd-overlay__backdrop"></div>
    <div class="vd-modal">
        <button class="vd-modal__close" id="vd-close" aria-label="Cerrar video">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="vd-player">
            <video id="vd-local" controls playsinline preload="metadata" src="<?= e($videoUrl) ?>"></video>
        </div>
    </div>
</div>
<?php else: ?>
<div id="vd-overlay" class="vd-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Reproductor de video">
    <div class="vd-overlay__backdrop"></div>
    <div class="vd-modal">
        <button class="vd-modal__close" id="vd-close" aria-label="Cerrar video">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="vd-player">
            <iframe id="vd-iframe" src="" allow="autoplay; encrypted-media; fullscreen; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.vd-hero {
    position: relative;
    overflow: hidden;
}
.vd-hero__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,.28) 0%, rgba(0,0,0,.18) 100%);
    pointer-events: none;
}
.vd-play-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: rgba(255,255,255,.92);
    border: none;
    cursor: pointer;
    color: #111;
    font-size: 22px;
    box-shadow: 0 8px 32px rgba(0,0,0,.28), 0 0 0 0 rgba(255,255,255,.5);
    transition: transform .2s ease, background .2s ease, box-shadow .2s ease;
    outline: none;
    position: relative;
    z-index: 2;
}
.vd-play-btn:hover {
    transform: scale(1.1);
    background: #fff;
    box-shadow: 0 12px 40px rgba(0,0,0,.35), 0 0 0 14px rgba(255,255,255,.18);
}
.vd-play-btn:active {
    transform: scale(0.97);
}
/* Overlay / modal */
.vd-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    visibility: hidden;
    transition: opacity .28s ease, visibility .28s ease;
}
.vd-overlay.is-open {
    opacity: 1;
    visibility: visible;
}
.vd-overlay__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(4, 6, 12, .88);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    cursor: pointer;
}
.vd-modal {
    position: relative;
    width: min(940px, 100%);
    z-index: 1;
    transform: scale(.94);
    transition: transform .28s ease;
}
.vd-overlay.is-open .vd-modal {
    transform: scale(1);
}
.vd-modal__close {
    position: absolute;
    top: -46px;
    right: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,.12);
    border: 1.5px solid rgba(255,255,255,.22);
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background .18s;
    outline: none;
}
.vd-modal__close:hover {
    background: rgba(255,255,255,.24);
}
.vd-player {
    position: relative;
    width: 100%;
    padding-top: 56.25%;
    background: #000;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 32px 80px rgba(0,0,0,.55);
}
.vd-player iframe,
.vd-player video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: none;
    display: block;
}
@media (max-width: 600px) {
    .vd-modal__close { top: -42px; right: 0; }
    .vd-player { border-radius: 10px; }
}
</style>

<script>
(function () {
    var overlay  = document.getElementById('vd-overlay');
    var closeBtn = document.getElementById('vd-close');
    var iframe   = document.getElementById('vd-iframe');
    var localVid = document.getElementById('vd-local');

    if (!overlay) return;

    function ytEmbedUrl(rawUrl) {
        var match = rawUrl.match(/(?:v=|youtu\.be\/|\/embed\/)([a-zA-Z0-9_-]{11})/);
        if (!match) return null;
        return 'https://www.youtube.com/embed/' + match[1] + '?autoplay=1&rel=0&modestbranding=1';
    }

    function openOverlay(url) {
        if (iframe) {
            var embedUrl = ytEmbedUrl(url);
            if (!embedUrl) {
                window.open(url, '_blank', 'noopener noreferrer');
                return;
            }
            iframe.src = embedUrl;
        }
        if (localVid) {
            localVid.play().catch(function () {});
        }
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (closeBtn) { closeBtn.focus(); }
    }

    function closeOverlay() {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (iframe)   { iframe.src = ''; }
        if (localVid) { localVid.pause(); }
    }

    // Botón play del hero
    var playBtn = document.querySelector('#video-destacado-home .vd-play-btn');
    if (playBtn) {
        playBtn.addEventListener('click', function () {
            openOverlay(this.getAttribute('data-url'));
        });
    }

    // Cierre
    if (closeBtn) { closeBtn.addEventListener('click', closeOverlay); }

    overlay.querySelector('.vd-overlay__backdrop').addEventListener('click', closeOverlay);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
            closeOverlay();
        }
    });
})();
</script>
