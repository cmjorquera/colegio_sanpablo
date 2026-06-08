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

$visibleGalleryItems = array_values(array_filter($galleryItems, static function (array $item): bool {
    return ($item['visible'] ?? 'si') === 'si' && trim((string) ($item['imagen'] ?? '')) !== '';
}));

$galleryToRender = array_slice($visibleGalleryItems, 0, 6);
$hasGalleryItems = count($galleryToRender) > 0;

$resolveGalleryImage = static function (array $item): string {
    $image = trim((string) ($item['imagen'] ?? ''));
    if ($image === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    $relativePath = ltrim($image, '/');
    $absolutePath = __DIR__ . '/../' . $relativePath;

    return is_file($absolutePath) ? $relativePath : '';
};

$galleryLightboxItems = [];
foreach ($galleryToRender as $item) {
    $image = $resolveGalleryImage($item);
    if ($image === '') {
        continue;
    }

    $galleryLightboxItems[] = [
        'src' => $image,
        'title' => trim((string) ($item['titulo'] ?? 'Galeria Colegio San Pablo')),
        'description' => trim((string) ($item['descripcion'] ?? '')),
    ];
}

$hasGalleryItems = count($galleryLightboxItems) > 0;
?>

<?php if ($hasGalleryItems): ?>
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
                <?php foreach ($galleryLightboxItems as $index => $item): ?>
                    <a
                        class="g-item<?= $index === 0 ? ' large' : '' ?>"
                        href="#"
                        data-sp-gallery-open="<?= (int) $index ?>"
                        aria-label="<?= e($item['title']) ?>"
                    >
                        <img src="<?= e($item['src']) ?>" alt="<?= e($item['title']) ?>">
                        <div class="overlay">
                            <i class="fas fa-search-plus"></i>
                            <?php if ($item['description'] !== ''): ?>
                                <span class="visually-hidden"><?= e($item['description']) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="sp-gallery-lightbox" id="sp-gallery-lightbox" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-label="Galeria fotografica">
        <button type="button" class="sp-gallery-close" data-sp-gallery-close aria-label="Cerrar galeria">
            <i class="fas fa-times"></i>
        </button>
        <button type="button" class="sp-gallery-nav sp-gallery-prev" data-sp-gallery-prev aria-label="Imagen anterior">
            <i class="fas fa-chevron-left"></i>
        </button>
        <figure class="sp-gallery-frame">
            <img src="" alt="" id="sp-gallery-lightbox-image">
            <figcaption>
                <strong id="sp-gallery-lightbox-title"></strong>
                <span id="sp-gallery-lightbox-description"></span>
            </figcaption>
        </figure>
        <button type="button" class="sp-gallery-nav sp-gallery-next" data-sp-gallery-next aria-label="Imagen siguiente">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <style>
    .sp-gallery-lightbox {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 34px 86px;
        background: rgba(5, 12, 24, .88);
    }
    .sp-gallery-lightbox[hidden] {
        display: none;
    }
    .sp-gallery-frame {
        width: min(1080px, 100%);
        margin: 0;
        color: #fff;
    }
    .sp-gallery-frame img {
        display: block;
        width: 100%;
        max-height: calc(100vh - 180px);
        object-fit: contain;
        border-radius: 18px;
        box-shadow: 0 28px 80px rgba(0, 0, 0, .35);
        background: #0b1220;
    }
    .sp-gallery-frame figcaption {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-top: 16px;
        text-align: center;
    }
    .sp-gallery-frame strong {
        font-size: 20px;
    }
    .sp-gallery-frame span {
        color: rgba(255, 255, 255, .76);
    }
    .sp-gallery-close,
    .sp-gallery-nav {
        border: 0;
        border-radius: 999px;
        background: #fff;
        color: #14213d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 18px 42px rgba(0, 0, 0, .28);
        transition: transform .2s ease, background .2s ease, color .2s ease;
    }
    .sp-gallery-close:hover,
    .sp-gallery-nav:hover {
        transform: scale(1.06);
        background: #F0A000;
        color: #fff;
    }
    .sp-gallery-close {
        position: absolute;
        top: 24px;
        right: 28px;
        width: 48px;
        height: 48px;
        font-size: 20px;
    }
    .sp-gallery-nav {
        position: absolute;
        top: 50%;
        width: 58px;
        height: 58px;
        transform: translateY(-50%);
        font-size: 18px;
    }
    .sp-gallery-nav:hover {
        transform: translateY(-50%) scale(1.06);
    }
    .sp-gallery-prev {
        left: 28px;
    }
    .sp-gallery-next {
        right: 28px;
    }
    @media (max-width: 767px) {
        .sp-gallery-lightbox {
            padding: 76px 18px 34px;
        }
        .sp-gallery-close {
            top: 16px;
            right: 16px;
            width: 42px;
            height: 42px;
        }
        .sp-gallery-nav {
            width: 44px;
            height: 44px;
            top: auto;
            bottom: 18px;
            transform: none;
        }
        .sp-gallery-nav:hover {
            transform: scale(1.06);
        }
        .sp-gallery-prev {
            left: 18px;
        }
        .sp-gallery-next {
            right: 18px;
        }
        .sp-gallery-frame img {
            max-height: calc(100vh - 220px);
        }
    }
    </style>

    <script>
    (function () {
        var items = <?= json_encode($galleryLightboxItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        var lightbox = document.getElementById('sp-gallery-lightbox');
        if (!lightbox || !items.length) {
            return;
        }

        var image = document.getElementById('sp-gallery-lightbox-image');
        var title = document.getElementById('sp-gallery-lightbox-title');
        var description = document.getElementById('sp-gallery-lightbox-description');
        var current = 0;

        function render(index) {
            current = (index + items.length) % items.length;
            var item = items[current];
            image.src = item.src;
            image.alt = item.title || 'Imagen de galeria';
            title.textContent = item.title || '';
            description.textContent = item.description || '';
            description.hidden = !item.description;
        }

        function open(index) {
            render(index);
            lightbox.hidden = false;
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function close() {
            lightbox.hidden = true;
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            image.removeAttribute('src');
        }

        document.querySelectorAll('[data-sp-gallery-open]').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                open(parseInt(trigger.getAttribute('data-sp-gallery-open') || '0', 10));
            });
        });

        lightbox.querySelector('[data-sp-gallery-close]').addEventListener('click', close);
        lightbox.querySelector('[data-sp-gallery-prev]').addEventListener('click', function () { render(current - 1); });
        lightbox.querySelector('[data-sp-gallery-next]').addEventListener('click', function () { render(current + 1); });
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                close();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (lightbox.hidden) {
                return;
            }
            if (event.key === 'Escape') {
                close();
            }
            if (event.key === 'ArrowLeft') {
                render(current - 1);
            }
            if (event.key === 'ArrowRight') {
                render(current + 1);
            }
        });
    })();
    </script>
<?php endif; ?>
