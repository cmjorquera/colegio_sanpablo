<?php
$modalSectionName = 'modal_informativo';
$modalItems = $sectionItemsMap[$modalSectionName] ?? [];
$modalItem = null;

foreach ($modalItems as $item) {
    if (($item['visible'] ?? 'si') !== 'si') {
        continue;
    }

    $hasContent = trim((string) ($item['titulo'] ?? '')) !== ''
        || trim((string) ($item['descripcion'] ?? '')) !== ''
        || trim((string) ($item['imagen'] ?? '')) !== '';

    if ($hasContent) {
        $modalItem = $item;
        break;
    }
}

if (!$modalItem) {
    return;
}

$modalTitle = trim((string) ($modalItem['titulo'] ?? ''));
$modalDescription = trim((string) ($modalItem['descripcion'] ?? ''));
$modalImage = trim((string) ($modalItem['imagen'] ?? ''));
$modalButtonText = trim((string) ($modalItem['boton_1_texto'] ?? 'Comenzar a navegar'));
$modalButtonUrl = trim((string) ($modalItem['boton_1_url'] ?? '#'));
$modalMode = cfg($sectionConfigsMap, $modalSectionName, 'mostrar', 'una_vez');
$modalDelay = max(0, (int) cfg($sectionConfigsMap, $modalSectionName, 'delay_ms', '650'));
$modalButtonColor = cfg($sectionConfigsMap, $modalSectionName, 'color_boton', '#ef4444');

if (!preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $modalButtonColor)) {
    $modalButtonColor = '#ef4444';
}

$modalHash = substr(hash('sha1', implode('|', [
    $modalTitle,
    $modalDescription,
    $modalImage,
    $modalButtonText,
    $modalButtonUrl,
])), 0, 14);

if (!function_exists('sp_modal_rich_text')) {
    function sp_modal_rich_text(string $value): string
    {
        $escaped = cms_e($value);
        $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
        $paragraphs = preg_split('/\R{2,}/', trim($escaped)) ?: [];
        $html = [];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }
            $html[] = '<p>' . nl2br($paragraph) . '</p>';
        }

        return implode("\n", $html);
    }
}
?>
<link rel="stylesheet" href="/assets/css/pages/modal_informativo.css">
<div
    class="sp-info-modal"
    id="modal-informativo"
    data-sp-info-modal
    data-preview="<?= basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === 'preview_contenedor.php' ? '1' : '0' ?>"
    data-mode="<?= e($modalMode) ?>"
    data-delay="<?= (int) $modalDelay ?>"
    data-hash="<?= e($modalHash) ?>"
    aria-hidden="true"
    hidden
>
    <div class="sp-info-modal__backdrop" data-sp-modal-close></div>
    <section class="sp-info-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="spInfoModalTitle">
        <button type="button" class="sp-info-modal__close" data-sp-modal-close aria-label="Cerrar">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="sp-info-modal__bar" aria-hidden="true"></div>

        <?php if ($modalImage !== ''): ?>
            <figure class="sp-info-modal__image">
                <img src="<?= e($modalImage) ?>" alt="<?= e($modalTitle) ?>" loading="lazy" onerror="this.closest('.sp-info-modal__image').remove();">
            </figure>
        <?php endif; ?>

        <div class="sp-info-modal__body">
            <?php if ($modalTitle !== ''): ?>
                <h2 id="spInfoModalTitle"><?= e($modalTitle) ?></h2>
            <?php endif; ?>

            <?php if ($modalDescription !== ''): ?>
                <div class="sp-info-modal__text">
                    <?= sp_modal_rich_text($modalDescription) ?>
                </div>
            <?php endif; ?>

            <?php if ($modalButtonText !== ''): ?>
                <a
                    href="<?= e(cms_public_url($modalButtonUrl !== '' ? $modalButtonUrl : '#')) ?>"
                    class="sp-info-modal__button"
                    data-sp-modal-primary
                    style="--sp-modal-button: <?= e($modalButtonColor) ?>;"
                    <?= ($modalButtonUrl !== '' && $modalButtonUrl !== '#') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                >
                    <?= e($modalButtonText) ?>
                </a>
            <?php endif; ?>
        </div>
    </section>
</div>
<script src="/assets/js/modal_informativo.js" defer></script>
