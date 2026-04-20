<?php
$faqItems = $sectionItemsMap['faq_home'] ?? [];
$faqSubtitle = cfg($sectionConfigsMap, 'faq_home', 'subtitulo_bloque', 'Preguntas frecuentes');
$faqTitle = cfg($sectionConfigsMap, 'faq_home', 'titulo_bloque', 'Preguntas frecuentes');
$faqImage = cfg($sectionConfigsMap, 'faq_home', 'imagen_lateral', 'assets/images/faq/faq-image1.png');
?>
<section class="py-5 bg-white" id="faq">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="section-label"><?= e($faqSubtitle) ?></span>
                <h2 class="section-title mt-2 mb-4"><?= e($faqTitle) ?></h2>
                <div class="accordion" id="faqAccordionHome">
                    <?php foreach ($faqItems as $index => $item): ?>
                        <div class="accordion-item mb-3 border-0 shadow-sm" style="border-radius:18px; overflow:hidden;">
                            <h2 class="accordion-header" id="faqHeading<?= $index ?>">
                                <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?= $index ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="faqCollapse<?= $index ?>">
                                    <?= e($item['titulo']) ?>
                                </button>
                            </h2>
                            <div id="faqCollapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordionHome">
                                <div class="accordion-body"><?= e($item['descripcion']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= e($faqImage) ?>" class="img-fluid rounded-4 shadow-sm" alt="Preguntas frecuentes">
            </div>
        </div>
    </div>
</section>
