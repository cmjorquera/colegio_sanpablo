<?php
$email = $institution['email'] ?? '';
$telefono = $institution['telefono'] ?? '';
$direccion = $institution['direccion'] ?? '';
?>
<div class="sp-topbar d-none d-md-block" id="topbar">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <?php if ($direccion !== ''): ?>
                    <i class="fas fa-map-marker-alt me-2" style="color:var(--sp-amarillo)"></i><?= e($direccion) ?>
                <?php endif; ?>
                <?php if ($telefono !== ''): ?>
                    <span class="sep">|</span>
                    <i class="fas fa-phone me-2" style="color:var(--sp-amarillo)"></i><?= e($telefono) ?>
                <?php endif; ?>
                <?php if ($email !== ''): ?>
                    <span class="sep">|</span>
                    <i class="fas fa-envelope me-2" style="color:var(--sp-amarillo)"></i>
                    <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="#" class="sp-login-btn" data-bs-toggle="modal" data-bs-target="#modalLogin" title="Ingresar al sistema">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Ingresar</span>
                </a>
                <?php if (!empty($institution['instagram'])): ?>
                    <span class="sep">|</span>
                    <a href="<?= e($institution['instagram']) ?>" target="_blank" rel="noopener" class="me-1"><i class="fab fa-instagram me-1"></i>Instagram</a>
                <?php endif; ?>
                <?php if (!empty($institution['facebook'])): ?>
                    <a href="<?= e($institution['facebook']) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook me-1"></i>Facebook</a>
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
