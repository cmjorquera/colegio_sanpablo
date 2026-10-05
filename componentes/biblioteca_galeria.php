<?php
// Biblioteca only: the caller supplies images, a unique ID and an accessible label.
$galleryCount = count($libraryGalleryImages);
if ($galleryCount === 0) {
    return;
}
$galleryCarousel = $galleryCount > 1;
?>
<div id="<?= e($libraryGalleryId) ?>" class="sp-library-image-gallery<?= $galleryCarousel ? ' carousel slide sp-library-carousel' : '' ?>"<?= $galleryCarousel ? ' data-library-carousel data-bs-interval="false" tabindex="0" aria-roledescription="carrusel"' : '' ?> aria-label="<?= e($libraryGalleryLabel) ?>">
    <div class="sp-library-gallery-frame<?= $galleryCarousel ? ' carousel-inner' : '' ?>">
        <?php foreach ($libraryGalleryImages as $imageIndex => $image): ?>
            <figure class="sp-library-gallery-slide<?= $galleryCarousel ? ' carousel-item' . ($imageIndex === 0 ? ' active' : '') : '' ?>"<?= $galleryCarousel ? ' role="group" aria-roledescription="diapositiva" aria-label="' . ($imageIndex + 1) . ' de ' . $galleryCount . '"' : '' ?>>
                <a href="<?= e($image['src']) ?>" target="_blank" rel="noopener"<?= $galleryCarousel && $imageIndex !== 0 ? ' tabindex="-1"' : '' ?>><img src="<?= e($image['src']) ?>" alt="<?= e($image['alt']) ?>" loading="lazy"></a>
                <?php if ($image['title'] !== '' || $image['description'] !== ''): ?>
                    <figcaption><?php if ($image['title'] !== ''): ?><span><?= e($image['title']) ?></span><?php endif; ?><?php if ($image['description'] !== ''): ?><span><?= e($image['description']) ?></span><?php endif; ?></figcaption>
                <?php endif; ?>
            </figure>
        <?php endforeach; ?>
    </div>
    <?php if ($galleryCarousel): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#<?= e($libraryGalleryId) ?>" data-bs-slide="prev"><span aria-hidden="true">‹</span><span class="visually-hidden">Imagen anterior</span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#<?= e($libraryGalleryId) ?>" data-bs-slide="next"><span aria-hidden="true">›</span><span class="visually-hidden">Imagen siguiente</span></button>
        <div class="carousel-indicators">
            <?php foreach ($libraryGalleryImages as $imageIndex => $image): ?>
                <button type="button" data-bs-target="#<?= e($libraryGalleryId) ?>" data-bs-slide-to="<?= $imageIndex ?>"<?= $imageIndex === 0 ? ' class="active" aria-current="true"' : '' ?> aria-label="Ver imagen <?= $imageIndex + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <span class="visually-hidden" data-library-gallery-status aria-live="polite" aria-atomic="true">Imagen 1 de <?= $galleryCount ?></span>
    <?php endif; ?>
</div>
