<?php
if (!empty($spFloatingActionsRendered)) {
    return;
}
$spFloatingActionsRendered = true;
$floatingRegistrationUrl = cms_public_url($institution['url_boton_principal'] ?? '#');
$floatingRegistrationText = $institution['texto_boton_principal'] ?? 'Matrícula';
$floatingColor = trim((string) ($institution['color_primario'] ?? ''));
if (!preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $floatingColor)) {
    $floatingColor = '#F0A000';
}
?>
<div class="sp-floating-actions" style="--sp-floating-color: <?= cms_e($floatingColor) ?>;">
    <a class="sp-floating-comunicados sp-floating-inscripciones" href="<?= cms_e($floatingRegistrationUrl) ?>" aria-label="<?= cms_e($floatingRegistrationText) ?>">
        <span class="sp-floating-comunicados__icon" aria-hidden="true"><i class="fas fa-file-signature"></i></span>
        <span class="sp-floating-comunicados__label"><?= cms_e($floatingRegistrationText) ?></span>
    </a>
    <a class="sp-floating-comunicados sp-floating-cantina" href="https://mi.sanpablo.edu.uy/cantina" target="_blank" rel="noopener" aria-label="Ver menu de cantina">
        <span class="sp-floating-comunicados__icon" aria-hidden="true"><i class="fas fa-utensils"></i></span>
        <span class="sp-floating-comunicados__label">Menú</span>
    </a>
    <a class="sp-floating-comunicados" href="/comunicados" aria-label="Ver comunicados">
        <span class="sp-floating-comunicados__icon" aria-hidden="true"><i class="fas fa-bullhorn"></i></span>
        <span class="sp-floating-comunicados__label">Comunicados</span>
    </a>
</div>
