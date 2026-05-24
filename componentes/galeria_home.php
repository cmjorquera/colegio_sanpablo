<?php
$sectionName = $section['nombre_interno'] ?? 'galeria_home';
$galleryItems = $sectionItemsMap[$sectionName] ?? [];

$galleryLabel = cfg($sectionConfigsMap, $sectionName, 'etiqueta', 'Vida Escolar');
$galleryTitle = cfg($sectionConfigsMap, $sectionName, 'titulo_bloque', 'Galeria');
$galleryTitleAccent = cfg($sectionConfigsMap, $sectionName, 'titulo_resaltado', 'Fotografica');
$galleryDescription = cfg(
    $sectionConfigsMap,
    $sectionName,
    'descripcion_bloque',
    'Momentos iconicos de nuestra comunidad educativa a lo largo del ano.'
);

$galleryFallback = [
    [
        'titulo' => 'Vida escolar Colegio San Pablo',
        'descripcion' => 'Momentos de la comunidad educativa.',
        'imagen' => 'assets/images/20251107131854222.png',
        'url' => '#0',
    ],
    [
        'titulo' => 'Actividades institucionales',
        'descripcion' => 'Participacion de estudiantes en actividades del colegio.',
        'imagen' => 'assets/images/IMG_6827.jpg',
        'url' => '#0',
    ],
    [
        'titulo' => 'Encuentros de aprendizaje',
        'descripcion' => 'Espacios de aprendizaje y convivencia.',
        'imagen' => 'assets/images/20251107131854.png',
        'url' => '#0',
    ],
    [
        'titulo' => 'Comunidad San Pablo',
        'descripcion' => 'Experiencias compartidas por la comunidad educativa.',
        'imagen' => 'assets/images/20251107131913.png',
        'url' => '#0',
    ],
    [
        'titulo' => 'Jornadas escolares',
        'descripcion' => 'Registros fotograficos de la vida escolar.',
        'imagen' => 'assets/images/20251107131958.png',
        'url' => '#0',
    ],
    [
        'titulo' => 'Colegio San Pablo',
        'descripcion' => 'Galeria institucional.',
        'imagen' => 'assets/images/IMG_6845.jpg',
        'url' => '#0',
    ],
];

$visibleGalleryItems = array_values(array_filter($galleryItems, static function (array $item): bool {
    return ($item['visible'] ?? 'si') === 'si' && trim((string) ($item['imagen'] ?? '')) !== '';
}));

$galleryToRender = $visibleGalleryItems ?: $galleryFallback;
$galleryToRender = array_slice($galleryToRender, 0, 6);

$resolveGalleryImage = static function (array $item): string {
    $image = trim((string) ($item['imagen'] ?? ''));
    if ($image === '') {
        return 'assets/images/frontis_01.jpg';
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    $relativePath = ltrim($image, '/');
    $absolutePath = __DIR__ . '/../' . $relativePath;

    return is_file($absolutePath) ? $relativePath : 'assets/images/frontis_01.jpg';
};
?>

<section class="sp-galeria" id="galeria">
    <div class="container">
        <div class="text-center mb-5">
            <?php if ($galleryLabel !== ''): ?>
                <span class="section-label"><?= e($galleryLabel) ?></span>
            <?php endif; ?>
            <h2 class="section-title">
                <?= e($galleryTitle) ?>
                <?php if ($galleryTitleAccent !== ''): ?>
                    <span><?= e($galleryTitleAccent) ?></span>
                <?php endif; ?>
            </h2>
            <div class="divider-line mx-auto"></div>
            <?php if ($galleryDescription !== ''): ?>
                <p class="section-desc mx-auto"><?= e($galleryDescription) ?></p>
            <?php endif; ?>
        </div>

        <div class="gallery-grid">
            <?php foreach ($galleryToRender as $index => $item): ?>
                <?php
                $image = $resolveGalleryImage($item);
                $title = trim((string) ($item['titulo'] ?? 'Galeria Colegio San Pablo'));
                $description = trim((string) ($item['descripcion'] ?? ''));
                $url = trim((string) ($item['url'] ?? '#0'));
                $href = $url !== '' && $url !== '#0' ? $url : $image;
                $isExternal = preg_match('/^https?:\/\//i', $href) === 1;
                $opensImage = $href === $image;
                ?>
                <a
                    class="g-item<?= $index === 0 ? ' large' : '' ?><?= $opensImage ? ' image-popup' : '' ?>"
                    href="<?= e($href) ?>"
                    aria-label="<?= e($title) ?>"
                    <?= $isExternal ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                >
                    <img src="<?= e($image) ?>" alt="<?= e($title) ?>">
                    <div class="overlay">
                        <i class="fas fa-search-plus"></i>
                        <?php if ($description !== ''): ?>
                            <span class="visually-hidden"><?= e($description) ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
