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
<div
    class="sp-info-modal"
    id="modal-informativo"
    data-sp-info-modal
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
                    href="<?= e($modalButtonUrl !== '' ? $modalButtonUrl : '#') ?>"
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

<style>
    .sp-info-modal[hidden] {
        display: none !important;
    }

    .sp-info-modal {
        position: fixed;
        inset: 0;
        z-index: 99990;
        display: grid;
        place-items: center;
        padding: 24px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 180ms ease, visibility 180ms ease;
    }

    .sp-info-modal.is-open {
        opacity: 1;
        visibility: visible;
    }

    .sp-info-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(3, 8, 18, 0.68);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .sp-info-modal__dialog {
        position: relative;
        z-index: 1;
        width: min(520px, 100%);
        max-height: calc(100vh - 48px);
        overflow: auto;
        border-radius: 2px;
        background: #fff;
        color: #252525;
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.36);
        transform: translateY(14px) scale(0.985);
        transition: transform 220ms ease;
    }

    .sp-info-modal.is-open .sp-info-modal__dialog {
        transform: translateY(0) scale(1);
    }

    .sp-info-modal__close {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.08);
        color: #0f172a;
        cursor: pointer;
        z-index: 2;
    }

    .sp-info-modal__close:hover {
        background: rgba(15, 23, 42, 0.14);
    }

    .sp-info-modal__bar {
        height: 4px;
        background: linear-gradient(90deg, #f5a400 0 25%, #ef4444 25% 50%, #1f8fd1 50% 75%, #ef4444 75% 100%);
    }

    .sp-info-modal__image {
        margin: 0;
        max-height: 220px;
        overflow: hidden;
    }

    .sp-info-modal__image img {
        display: block;
        width: 100%;
        height: 220px;
        object-fit: cover;
    }

    .sp-info-modal__body {
        padding: 22px 34px 28px;
        text-align: center;
    }

    .sp-info-modal__body h2 {
        margin: 0 0 14px;
        color: #111827;
        font-size: clamp(22px, 3vw, 28px);
        line-height: 1.18;
        font-weight: 800;
        letter-spacing: 0;
    }

    .sp-info-modal__text {
        color: #4b5563;
        font-size: 15px;
        line-height: 1.75;
    }

    .sp-info-modal__text p {
        margin: 0 0 16px;
    }

    .sp-info-modal__text p:last-child {
        margin-bottom: 0;
    }

    .sp-info-modal__text strong {
        color: #2f2f2f;
        font-weight: 800;
    }

    .sp-info-modal__button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        margin-top: 22px;
        padding: 12px 34px;
        border-radius: 999px;
        background: var(--sp-modal-button, #ef4444);
        color: #fff !important;
        font-size: 16px;
        font-weight: 800;
        line-height: 1;
        text-transform: uppercase;
        text-decoration: none;
        box-shadow: 0 10px 24px rgba(239, 68, 68, 0.35);
    }

    .sp-info-modal__button:hover {
        filter: brightness(1.04);
        color: #fff !important;
        transform: translateY(-1px);
    }

    @media (max-width: 575.98px) {
        .sp-info-modal {
            padding: 16px;
        }

        .sp-info-modal__body {
            padding: 22px 22px 26px;
        }

        .sp-info-modal__text {
            font-size: 14px;
        }

        .sp-info-modal__button {
            width: 100%;
            padding-inline: 18px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.querySelector('[data-sp-info-modal]');
        if (!modal) {
            return;
        }

        var mode = modal.getAttribute('data-mode') || 'una_vez';
        var delay = parseInt(modal.getAttribute('data-delay') || '0', 10);
        var hash = modal.getAttribute('data-hash') || 'default';
        var storageKey = 'sp_info_modal_seen_' + hash;

        function storageGet(key) {
            try {
                return window.localStorage.getItem(key);
            } catch (error) {
                return null;
            }
        }

        function storageSet(key, value) {
            try {
                window.localStorage.setItem(key, value);
            } catch (error) {}
        }

        var shouldShow = mode === 'siempre' || storageGet(storageKey) !== '1';

        if (!shouldShow) {
            modal.remove();
            return;
        }

        function markSeen() {
            if (mode !== 'siempre') {
                storageSet(storageKey, '1');
            }
        }

        function openModal() {
            modal.hidden = false;
            modal.offsetHeight;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            markSeen();
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            setTimeout(function () {
                modal.remove();
            }, 190);
        }

        modal.querySelectorAll('[data-sp-modal-close], [data-sp-modal-primary]').forEach(function (element) {
            element.addEventListener('click', function (event) {
                if (element.getAttribute('href') === '#') {
                    event.preventDefault();
                }
                closeModal();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        window.setTimeout(openModal, Math.max(0, delay));
    });
</script>
