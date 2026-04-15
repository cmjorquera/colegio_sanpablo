<?php
/**
 * Seccion de cursos destacados.
 *
 * Ejemplo futuro de uso con base de datos:
 * require_once __DIR__ . '/../class/conexion.php';
 * $conexion = (new Conexion())->getConexion();
 * $resultado = $conexion->query("SELECT titulo, precio FROM cursos ORDER BY id DESC LIMIT 3");
 */
?>
<section class="courses-six-area pt-120 pb-120">
    <div class="container">
        <div class="section-header text-center mb-60">
            <h5 class="wow fadeInUp" data-wow-delay="00ms" data-wow-duration="1500ms">Cursos destacados</h5>
            <h2 class="wow fadeInUp" data-wow-delay="200ms" data-wow-duration="1500ms">Elige <span>nuestros mejores <img src="assets/images/shape/header-shape.png" alt="shape"></span> cursos</h2>
        </div>
        <div class="row g-4">
            <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay="00ms" data-wow-duration="1500ms">
                <div class="courses-six__item bor">
                    <div class="courses-six__image image">
                        <img src="assets/images/courses/courses-six-image1.png" alt="imagen">
                        <div class="courses-price"><h5 class="fs-18">$49</h5></div>
                    </div>
                    <div class="courses-six__content">
                        <div class="tag mb-20">Inicial</div>
                        <h3><a href="course-details.html" class="primary-hover">Curso integral de dibujo para principiantes</a></h3>
                        <ul class="d-flex align-items-center gap-4 my-3">
                            <li><a class="primary-hover fs-14" href="#0">180 estudiantes</a></li>
                            <li><a class="primary-hover fs-14" href="#0">12 lecciones</a></li>
                        </ul>
                        <div class="bor-top pt-3 d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex gap-2 align-items-center">
                                <img src="assets/images/courses/courses-user1.jpg" alt="docente">
                                <a href="#0" class="primary-hover">Rahat Hasan</a>
                            </div>
                            <div class="star"><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star disabled"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay="200ms" data-wow-duration="1500ms">
                <div class="courses-six__item bor">
                    <div class="courses-six__image image">
                        <img src="assets/images/courses/courses-six-image2.png" alt="imagen">
                        <div class="courses-price"><h5 class="fs-18">Gratis</h5></div>
                    </div>
                    <div class="courses-six__content">
                        <div class="tag mb-20">Avanzado</div>
                        <h3><a href="course-details.html" class="primary-hover">Arte y ciencia del dibujo de figura humana</a></h3>
                        <ul class="d-flex align-items-center gap-4 my-3">
                            <li><a class="primary-hover fs-14" href="#0">160 estudiantes</a></li>
                            <li><a class="primary-hover fs-14" href="#0">15 lecciones</a></li>
                        </ul>
                        <div class="bor-top pt-3 d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex gap-2 align-items-center">
                                <img src="assets/images/courses/courses-user2.jpg" alt="docente">
                                <a href="#0" class="primary-hover">Shanta Roy</a>
                            </div>
                            <div class="star"><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star disabled"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 wow fadeInUp" data-wow-delay="400ms" data-wow-duration="1500ms">
                <div class="courses-six__item bor">
                    <div class="courses-six__image image">
                        <img src="assets/images/courses/courses-six-image3.png" alt="imagen">
                        <div class="courses-price"><h5 class="fs-18">$29</h5></div>
                    </div>
                    <div class="courses-six__content">
                        <div class="tag mb-20">Arte online</div>
                        <h3><a href="course-details.html" class="primary-hover">Arte y diseño moderno y contemporáneo</a></h3>
                        <ul class="d-flex align-items-center gap-4 my-3">
                            <li><a class="primary-hover fs-14" href="#0">180 estudiantes</a></li>
                            <li><a class="primary-hover fs-14" href="#0">12 lecciones</a></li>
                        </ul>
                        <div class="bor-top pt-3 d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex gap-2 align-items-center">
                                <img src="assets/images/courses/courses-user3.jpg" alt="docente">
                                <a href="#0" class="primary-hover">Ayon Sheek</a>
                            </div>
                            <div class="star"><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star"></i><i class="fa-sharp fa-solid fa-star disabled"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
