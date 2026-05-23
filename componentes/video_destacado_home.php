<?php
$videoSectionName = 'video_destacado_home';
$videoImagen = cfg($sectionConfigsMap, $videoSectionName, 'imagen_fondo', 'assets/images/video/video-eight-image.jpg');
$videoUrl = cfg($sectionConfigsMap, $videoSectionName, 'video_url', 'https://www.youtube.com/watch?v=V-siUdyJ3Ac');
?>
<div class="banner-video-four-area bg-image" id="video-destacado-home" data-background="<?= e($videoImagen) ?>" style="background-image: url('<?= e($videoImagen) ?>');">
    <div class="banner-video__video-btn">
        <div class="video-btn video-pulse">
            <a class="video-popup" href="<?= e($videoUrl) ?>" aria-label="Reproducir video institucional">
                <i class="fa-solid fa-play"></i>
            </a>
        </div>
    </div>
</div>
<script>
    (function () {
        var initVideoPopup = function () {
            if (window.jQuery && jQuery.fn.magnificPopup) {
                jQuery('#video-destacado-home .video-popup').magnificPopup({
                    type: 'iframe'
                });
            }
        };

        document.addEventListener('DOMContentLoaded', initVideoPopup);
        window.addEventListener('load', initVideoPopup);
        setTimeout(initVideoPopup, 800);
    })();
</script>
