<?php
$aboutSubtitle = cfg($sectionConfigsMap, 'about_home', 'subtitulo_bloque', 'Sobre nosotros');
$aboutTitle = cfg($sectionConfigsMap, 'about_home', 'titulo_bloque', 'Aprende Nuevas Habilidades para Crecer');
$aboutDescription = cfg($sectionConfigsMap, 'about_home', 'descripcion_bloque', 'Brindamos una educación integral con acompañamiento cercano, formación en valores y experiencias de aprendizaje.');
$aboutImage = cfg($sectionConfigsMap, 'about_home', 'imagen_principal', 'assets/images/about/about-two-image1.png');
$aboutVideo = cfg($sectionConfigsMap, 'about_home', 'video_url', '');
?>
<section class="py-5" id="about">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="position-relative">
                    <img src="<?= e($aboutImage) ?>" class="img-fluid rounded-4 shadow-sm" alt="Sobre nosotros">
                    <?php if ($aboutVideo !== ''): ?>
                        <a href="<?= e($aboutVideo) ?>" class="video-btn video-pulse position-absolute top-50 start-50 translate-middle video-popup">
                            <i class="fa-solid fa-play"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <span class="section-label"><?= e($aboutSubtitle) ?></span>
                <h2 class="section-title mt-2 mb-4"><?= e($aboutTitle) ?></h2>
                <p class="section-desc"><?= e($aboutDescription) ?></p>
                <div class="row g-3 mt-3">
                    <div class="col-md-6"><div class="p-3 bg-white rounded-4 shadow-sm h-100"><strong>Acompañamiento cercano</strong><div class="text-muted small mt-2">Seguimiento constante para cada etapa educativa.</div></div></div>
                    <div class="col-md-6"><div class="p-3 bg-white rounded-4 shadow-sm h-100"><strong>Formación en valores</strong><div class="text-muted small mt-2">Proyecto institucional con identidad y comunidad.</div></div></div>
                </div>
            </div>
        </div>
    </div>
</section>
