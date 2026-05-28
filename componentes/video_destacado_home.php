<?php
$videoSectionName = 'video_destacado_home';
$videoItems = $sectionItemsMap[$videoSectionName] ?? [];
$videoItem = $videoItems[0] ?? [];
$videoImagen = cfg($sectionConfigsMap, $videoSectionName, 'imagen_fondo', 'assets/images/video/video-eight-image.jpg');
$videoUrl = trim((string) ($videoItem['url'] ?? ''));
$videoUrl = $videoUrl !== '' ? $videoUrl : cfg($sectionConfigsMap, $videoSectionName, 'video_url', 'https://www.youtube.com/watch?v=V-siUdyJ3Ac');
$isYoutubeVideo = (bool) preg_match('/(youtube\.com|youtu\.be)/i', $videoUrl);
$videoHref = $isYoutubeVideo ? $videoUrl : '#videoDestacadoPlayer';
?>
<div class="banner-video-four-area bg-image" id="video-destacado-home" data-background="<?= e($videoImagen) ?>" style="background-image: url('<?= e($videoImagen) ?>');">
    <div class="banner-video__video-btn">
        <div class="video-btn video-pulse">
            <a class="<?= $isYoutubeVideo ? 'video-popup' : 'local-video-popup' ?>" href="<?= e($videoHref) ?>" aria-label="Reproducir video institucional">
                <i class="fa-solid fa-play"></i>
            </a>
        </div>
    </div>
</div>
<?php if (!$isYoutubeVideo): ?>
    <div id="videoDestacadoPlayer" class="mfp-hide video-destacado-player">
        <video controls playsinline preload="metadata" src="<?= e($videoUrl) ?>"></video>
    </div>
<?php endif; ?>
<script>
    (function () {
        var initVideoPopup = function () {
            if (window.jQuery && jQuery.fn.magnificPopup) {
                jQuery('#video-destacado-home .video-popup').magnificPopup({
                    type: 'iframe'
                });
                jQuery('#video-destacado-home .local-video-popup').magnificPopup({
                    type: 'inline',
                    midClick: true,
                    callbacks: {
                        close: function () {
                            var video = document.querySelector('#videoDestacadoPlayer video');
                            if (video) {
                                video.pause();
                            }
                        }
                    }
                });
            }
        };

        document.addEventListener('DOMContentLoaded', initVideoPopup);
        window.addEventListener('load', initVideoPopup);
        setTimeout(initVideoPopup, 800);
    })();
</script>
<style>
    .video-destacado-player {
        width: min(960px, calc(100vw - 32px));
        margin: 0 auto;
        background: #000;
        border-radius: 12px;
        overflow: hidden;
    }
    .video-destacado-player video {
        display: block;
        width: 100%;
        max-height: 80vh;
        background: #000;
    }
</style>
