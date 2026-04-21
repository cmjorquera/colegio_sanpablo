<?php
/**
 * Sidebar para menu movil y accesos rapidos.
 */
?>
<div class="sidebar-area offcanvas offcanvas-end" id="menubar">
    <div class="offcanvas-header">
        <a href="index.php" class="logo">
            <img src="assets/images/logo/logo-light.svg" alt="logo">
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar menú">
            <i class="fa-regular fa-xmark"></i>
        </button>
    </div>
    <div class="offcanvas-body sidebar__body">
        <div class="mobile-menu overflow-hidden"></div>
        <div class="d-none d-lg-block">
            <h5 class="text-white mb-20">Sobre nosotros</h5>
            <p class="paragraph-light fs-16">Impulsamos una experiencia educativa creativa, cercana y moderna para que cada estudiante desarrolle su talento.</p>
        </div>
        <div class="sidebar__search d-block d-lg-none">
            <input type="text" placeholder="Buscar aquí...">
            <button aria-label="Buscar"><i class="fa-regular fa-magnifying-glass"></i></button>
        </div>
        <div class="sidebar__contact-info mt-30">
            <h5 class="text-white mb-20">Información de contacto</h5>
            <ul>
                <li><i class="fa-solid fa-location-dot"></i> <a href="#0">Av. Principal 6391, Celina</a></li>
                <li class="py-2"><i class="fa-solid fa-phone-volume"></i> <a href="tel:+56926593020">+56 9 2659 3020</a></li>
                <li><i class="fa-solid fa-paper-plane"></i> <a href="mailto:info@colegiospablo.cl">info@colegiospablo.cl</a></li>
            </ul>
        </div>
        <div class="sidebar__btns my-4">
            <a href="sign-up.html">Crear cuenta</a>
            <a class="sign-in" href="sign-in.html">Iniciar sesión</a>
        </div>
        <div class="sidebar__socials">
            <ul>
                <li><a href="#0"><i class="fa-brands text-white fa-facebook-f"></i></a></li>
                <li><a href="#0"><i class="fa-brands text-white fa-twitter"></i></a></li>
                <li><a href="#0"><i class="fa-brands text-white fa-linkedin-in"></i></a></li>
                <li><a href="#0"><i class="fa-brands text-white fa-youtube"></i></a></li>
            </ul>
        </div>
    </div>
</div>
