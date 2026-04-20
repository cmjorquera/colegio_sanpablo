<?php
$email = trim((string) ($institution['email'] ?? ''));
$telefono = trim((string) ($institution['telefono'] ?? ''));
$direccion = trim((string) ($institution['direccion'] ?? ''));
$mostrarDireccion = cfg($sectionConfigsMap, 'topbar', 'mostrar_direccion', 'si') === 'si';
$mostrarTelefono = cfg($sectionConfigsMap, 'topbar', 'mostrar_telefono', 'si') === 'si';
$mostrarEmail = cfg($sectionConfigsMap, 'topbar', 'mostrar_email', 'si') === 'si';
$mostrarRedes = cfg($sectionConfigsMap, 'topbar', 'mostrar_redes', 'si') === 'si';
$mostrarBotonIngresar = cfg($sectionConfigsMap, 'topbar', 'mostrar_boton_ingresar', 'si') === 'si';
$textoBotonIngresar = trim(cfg($sectionConfigsMap, 'topbar', 'texto_boton_ingresar', 'Ingresar'));
$textoBotonIngresar = $textoBotonIngresar !== '' ? $textoBotonIngresar : 'Ingresar';
$colorPrimario = trim((string) ($institution['color_primario'] ?? '')) ?: '#2563EB';
$colorSecundario = trim((string) ($institution['color_secundario'] ?? '')) ?: '#E9A629';
$colorTerciario = trim((string) ($institution['color_terciario'] ?? '')) ?: '#222222';
$topbarGradient = 'linear-gradient(90deg, ' . $colorPrimario . ', ' . $colorSecundario . ', ' . $colorTerciario . ')';
$redesTopbar = array_values(array_filter(
    $sectionItemsMap['topbar'] ?? [],
    static fn(array $item): bool => ($item['etiqueta'] ?? '') === 'red_social'
));
$redesTopbar = array_slice($redesTopbar, 0, 4);

$contactos = [];
if ($mostrarDireccion && $direccion !== '') {
    $contactos[] = [
        'icono' => 'fas fa-map-marker-alt',
        'contenido' => e($direccion),
    ];
}
if ($mostrarTelefono && $telefono !== '') {
    $contactos[] = [
        'icono' => 'fas fa-phone',
        'contenido' => e($telefono),
    ];
}
if ($mostrarEmail && $email !== '') {
    $contactos[] = [
        'icono' => 'fas fa-envelope',
        'contenido' => '<a href="mailto:' . e($email) . '">' . e($email) . '</a>',
    ];
}
?>
<div class="sp-topbar d-none d-md-block" id="topbar" style=" background: var(--sp-negro);color: #e0e0e0;padding: 8px 0;   font-size: 13px;">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center flex-wrap">
                <?php foreach ($contactos as $index => $contacto): ?>
                    <?php if ($index > 0): ?><span class="sep">|</span><?php endif; ?>
                    <span class="d-inline-flex align-items-center">
                        <i class="<?= e($contacto['icono']) ?> me-2" style="color:<?= e($colorSecundario) ?>"></i><?= $contacto['contenido'] ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <?php if ($mostrarBotonIngresar): ?>
                    <a href="#" class="sp-login-btn" data-bs-toggle="modal" data-bs-target="#modalLogin" title="Ingresar al sistema">
                        <i class="fas fa-sign-in-alt"></i>
                        <span><?= e($textoBotonIngresar) ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($mostrarRedes && $redesTopbar): ?>
                    <?php if ($mostrarBotonIngresar): ?><span class="sep">|</span><?php endif; ?>
                    <div class="d-flex align-items-center gap-2">
                        <?php foreach ($redesTopbar as $red): ?>
                            <?php $urlRed = trim((string) ($red['descripcion'] ?? '')); ?>
                            <?php if ($urlRed === '') { continue; } ?>
                            <a href="<?= e($urlRed) ?>" target="_blank" rel="noopener" title="<?= e($red['titulo'] ?? 'Red social') ?>" aria-label="<?= e($red['titulo'] ?? 'Red social') ?>" 
                            class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:32px;height:32px;background:rgba(255,255,255,.14); color:#e0e0e0;;">
                                <i class="<?= e($red['icono'] ?: 'fas fa-link') ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sp-modal-login">
            <div class="sp-modal-colorband"></div>
            <div class="modal-body p-0">
                <div class="sp-modal-header">
                    <button type="button" class="sp-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="sp-modal-logo">
                        <i class="fas fa-school"></i>
                    </div>
                    <h5 id="modalLoginLabel">Mi Colegio San Pablo</h5>
                    <p>Elige el área a la que deseas ingresar:</p>
                </div>

                <div class="sp-modal-areas">
                    <a href="admin.php" class="sp-area-btn sp-area-admin">
                        <i class="fas fa-lock"></i>
                        <span>Administrador</span>
                        <i class="fas fa-chevron-right sp-area-arrow"></i>
                    </a>

                    <a href="#" class="sp-area-btn" data-bs-dismiss="modal">
                        <i class="fas fa-user-graduate"></i>
                        <span>Área Alumnos</span>
                        <i class="fas fa-chevron-right sp-area-arrow"></i>
                    </a>

                    <a href="#" class="sp-area-btn" data-bs-dismiss="modal">
                        <i class="fas fa-users"></i>
                        <span>Área Padres</span>
                        <i class="fas fa-chevron-right sp-area-arrow"></i>
                    </a>

                    <a href="#" class="sp-area-btn" data-bs-dismiss="modal">
                        <i class="fas fa-briefcase"></i>
                        <span>Área Funcionario</span>
                        <i class="fas fa-chevron-right sp-area-arrow"></i>
                    </a>

                    <a href="#" class="sp-area-btn" data-bs-dismiss="modal">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Área Docentes</span>
                        <i class="fas fa-chevron-right sp-area-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
