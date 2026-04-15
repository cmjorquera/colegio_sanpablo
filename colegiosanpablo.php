<?php
/**
 * Colegio San Pablo — Página principal con menú dinámico desde BD
 */
require_once __DIR__ . '/class/conexion.php';

$arrMenus = [];
$arrSubs  = [];

try {
    $db = (new Conexion())->getConexion();

    // ── Menús principales activos ordenados por campo "orden" ──
    $resMenus = $db->query(
        "SELECT id_menu, nombre, url, icono, orden
           FROM menus
          WHERE estado = 1
          ORDER BY orden ASC"
    );
    if ($resMenus) {
        $arrMenus = $resMenus->fetch_all(MYSQLI_ASSOC);
        $resMenus->free();
    }

    // ── Sub-menús activos agrupados por id_menu ──
    $resSubs = $db->query(
        "SELECT id_sub_menu, id_menu, nombre, url, icono, orden
           FROM sub_menus
          WHERE estado = 1
          ORDER BY id_menu ASC, orden ASC"
    );
    if ($resSubs) {
        while ($row = $resSubs->fetch_assoc()) {
            $arrSubs[(int)$row['id_menu']][] = $row;
        }
        $resSubs->free();
    }

} catch (RuntimeException $e) {
    // Si falla la BD se muestra la página sin menú dinámico
    error_log('Menú dinámico: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colegio San Pablo</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/icono_ppt.png">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Mean Menu CSS -->
    <link rel="stylesheet" href="assets/css/meanmenu.css">
    <!-- All Min CSS (Font Awesome) -->
    <link rel="stylesheet" href="assets/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="assets/css/animate.css">
    <!-- Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* =============================================
           COLEGIO SAN PABLO - ESTILOS PERSONALIZADOS
        ============================================= */

        :root {
            /* Colores institucionales (paleta real del colegio) */
            --sp-azul:        #2060B0;   /* azul */
            --sp-azul-claro:  #ddeaf8;   /* azul muy claro para fondos */
            --sp-amber:       #E8A030;   /* ámbar/dorado — botón principal */
            --sp-naranja:     #E07830;   /* naranja */
            --sp-rojo:        #D94535;   /* rojo/coral */
            --sp-crema:       #F5ECD5;   /* fondo crema institucional */
            /* Neutros */
            --sp-blanco:      #ffffff;
            --sp-gris-claro:  #f5f7fa;
            --sp-gris-texto:  #5a5a5a;
            --sp-negro:       #2D2D2D;
            /* Alias para no romper referencias anteriores */
            --sp-amarillo:    #E8A030;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--sp-negro);
        }

        /* ---- BANDA DE COLORES INSTITUCIONALES ---- */
        .sp-colorband {
            height: 6px;
            background: linear-gradient(to right,
                var(--sp-amber)   0%,
                var(--sp-amber)   25%,
                var(--sp-naranja) 25%,
                var(--sp-naranja) 50%,
                var(--sp-azul)    50%,
                var(--sp-azul)    75%,
                var(--sp-rojo)    75%,
                var(--sp-rojo)    100%
            );
        }

        /* ---- TOP BAR ---- */
        .sp-topbar {
            background: var(--sp-negro);
            color: #e0e0e0;
            padding: 8px 0;
            font-size: 13px;
        }
        .sp-topbar a { color: #e0e0e0; text-decoration: none; }
        .sp-topbar a:hover { color: var(--sp-amber); }
        .sp-topbar .sep { margin: 0 12px; opacity: .4; }

        /* ---- HEADER ---- */
        .sp-header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .sp-header .container-fluid { padding: 0 40px; }
        .sp-logo img { height: 70px; }
        .sp-nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 4px; }
        .sp-nav ul li a {
            display: block;
            padding: 28px 12px;
            font-size: 12px;
            font-weight: 600;
            color: var(--sp-negro);
            text-decoration: none;
            transition: color .2s;
            white-space: nowrap;
        }
        .sp-nav ul li a:hover,
        .sp-nav ul li.active a { color: var(--sp-azul); }
        .sp-nav ul li { position: relative; }
        .sp-nav ul li .dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #fff;
            min-width: 200px;
            box-shadow: 0 8px 25px rgba(0,0,0,.12);
            border-top: 3px solid var(--sp-azul);
            z-index: 1000;
        }
        .sp-nav ul li:hover .dropdown { display: block; }
        .sp-nav ul li .dropdown li a {
            padding: 10px 18px;
            font-size: 13px;
            border-bottom: 1px solid #f0f0f0;
        }
        .sp-btn-matricula {
            background: var(--sp-amber);
            color: #fff !important;
            padding: 10px 20px !important;
            border-radius: 50px;
            margin-left: 8px;
        }
        .sp-btn-matricula:hover { background: var(--sp-naranja) !important; color: #fff !important; }

        /* ---- HERO / SLIDER ---- */
        .sp-hero {
            background: var(--sp-crema);
            min-height: 560px;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .sp-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('assets/images/banner/banner-eight-hero.jpg') center/cover no-repeat;
            opacity: .06;
        }
        /* Franja decorativa inferior del hero con los 4 colores */
        .sp-hero::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(to right,
                var(--sp-amber)   25%, var(--sp-naranja) 25%,
                var(--sp-naranja) 50%, var(--sp-azul)    50%,
                var(--sp-azul)    75%, var(--sp-rojo)    75%);
        }
        .sp-hero .hero-content { position: relative; z-index: 2; }
        .sp-hero .badge-lema {
            display: inline-block;
            background: rgba(232,160,48,.15);
            color: var(--sp-naranja);
            border: 1px solid rgba(232,160,48,.4);
            border-radius: 50px;
            padding: 6px 18px;
            font-size: 13px;
            margin-bottom: 18px;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .sp-hero h1 {
            font-size: 52px;
            font-weight: 800;
            color: var(--sp-negro);
            line-height: 1.15;
            margin-bottom: 18px;
        }
        .sp-hero h1 span { color: var(--sp-azul); }
        .sp-hero p {
            font-size: 17px;
            color: var(--sp-gris-texto);
            max-width: 520px;
            margin-bottom: 32px;
        }
        .sp-hero .btn-primary-sp {
            background: var(--sp-amber);
            color: #fff;
            border: none;
            padding: 14px 32px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            margin-right: 12px;
            transition: background .2s, transform .2s;
            display: inline-block;
        }
        .sp-hero .btn-primary-sp:hover { background: var(--sp-naranja); transform: translateY(-2px); }
        .sp-hero .btn-outline-sp {
            background: transparent;
            color: var(--sp-azul);
            border: 2px solid var(--sp-azul);
            padding: 13px 30px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: border-color .2s, background .2s, color .2s;
            display: inline-block;
        }
        .sp-hero .btn-outline-sp:hover { background: var(--sp-azul); color: #fff; }
        .sp-hero .hero-img {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        .sp-hero .hero-img img {
            max-height: 440px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,.15);
        }
        .sp-hero .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 14px 20px;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0,0,0,.08);
            display: inline-block;
            margin: 8px;
        }
        .sp-hero .stat-card strong { display: block; font-size: 28px; color: var(--sp-azul); font-weight: 800; }
        .sp-hero .stat-card span { font-size: 12px; color: var(--sp-gris-texto); }

        /* ---- SECTION TITLES ---- */
        .section-label {
            display: inline-block;
            color: var(--sp-naranja);
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .section-title {
            font-size: 36px;
            font-weight: 800;
            color: var(--sp-negro);
            margin-bottom: 14px;
        }
        .section-title span { color: var(--sp-azul); }
        .section-desc {
            color: var(--sp-gris-texto);
            font-size: 16px;
            max-width: 600px;
        }
        .divider-line {
            width: 50px;
            height: 4px;
            background: var(--sp-amber);
            border-radius: 4px;
            margin: 14px 0 24px;
        }

        /* ---- NIVELES EDUCATIVOS ---- */
        .sp-niveles { background: var(--sp-crema); padding: 80px 0; }
        .nivel-card {
            background: #fff;
            border-radius: 14px;
            padding: 32px 24px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,.06);
            border-top: 4px solid var(--sp-azul);
            transition: transform .2s, box-shadow .2s;
            height: 100%;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        /* Cada nivel con un color institucional distinto */
        .nivel-card:nth-child(1) { border-top-color: var(--sp-amarillo); }
        .nivel-card:nth-child(2) { border-top-color: var(--sp-naranja); }
        .nivel-card:nth-child(3) { border-top-color: var(--sp-azul); }
        .nivel-card:nth-child(4) { border-top-color: var(--sp-rojo); }
        .nivel-card:nth-child(5) { border-top-color: var(--sp-amarillo); }
        .nivel-card:nth-child(6) { border-top-color: var(--sp-naranja); }
        .nivel-card:hover { transform: translateY(-6px); box-shadow: 0 12px 35px rgba(0,0,0,.12); color: inherit; }
        .nivel-card .icon {
            width: 64px;
            height: 64px;
            background: var(--sp-azul-claro);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 26px;
            color: var(--sp-azul);
        }
        .nivel-card:nth-child(1) .icon,
        .nivel-card:nth-child(5) .icon { background: #fff8e8; color: var(--sp-amarillo); }
        .nivel-card:nth-child(2) .icon,
        .nivel-card:nth-child(6) .icon { background: #fef0eb; color: var(--sp-naranja); }
        .nivel-card:nth-child(4) .icon { background: #fdeaed; color: var(--sp-rojo); }
        .nivel-card h5 { font-size: 17px; font-weight: 700; margin-bottom: 8px; color: var(--sp-negro); }
        .nivel-card p { font-size: 13px; color: var(--sp-gris-texto); margin: 0; }

        /* ---- ACCESO PORTAL ---- */
        .sp-portal { background: var(--sp-negro); padding: 60px 0; }
        .sp-portal h2 { color: #fff; font-size: 30px; font-weight: 800; }
        .sp-portal p { color: rgba(255,255,255,.75); font-size: 15px; }
        .portal-card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 16px;
            padding: 28px 20px;
            text-align: center;
            transition: background .2s, border-color .2s;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }
        .portal-card:hover { background: rgba(232,160,48,.15); border-color: var(--sp-amber); }
        .portal-card .icon {
            font-size: 36px;
            color: var(--sp-amber);
            margin-bottom: 12px;
        }
        .portal-card h6 { color: #fff; font-size: 15px; font-weight: 700; margin-bottom: 4px; }
        .portal-card small { color: rgba(255,255,255,.6); font-size: 12px; }

        /* ---- NOTICIAS ---- */
        .sp-noticias { padding: 80px 0; background: #fff; }
        .noticia-card {
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.07);
            transition: transform .2s, box-shadow .2s;
            height: 100%;
            background: #fff;
        }
        .noticia-card:hover { transform: translateY(-5px); box-shadow: 0 12px 35px rgba(0,0,0,.13); }
        .noticia-card .img-wrap { overflow: hidden; height: 200px; }
        .noticia-card .img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
        .noticia-card:hover .img-wrap img { transform: scale(1.06); }
        .noticia-card .card-body { padding: 20px; }
        .noticia-card .tag {
            display: inline-block;
            background: var(--sp-azul-claro);
            color: var(--sp-azul);
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 50px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .noticia-card:nth-child(2) .tag { background: #fff8e8; color: var(--sp-amarillo); }
        .noticia-card:nth-child(3) .tag { background: #fef0eb; color: var(--sp-naranja); }
        .noticia-card:nth-child(4) .tag { background: #fdeaed; color: var(--sp-rojo); }
        .noticia-card h5 { font-size: 16px; font-weight: 700; margin-bottom: 10px; line-height: 1.4; }
        .noticia-card p { font-size: 13px; color: var(--sp-gris-texto); margin-bottom: 14px; }
        .noticia-card .meta { font-size: 12px; color: #aaa; }
        .noticia-card .meta i { margin-right: 4px; }
        .btn-ver-mas {
            background: var(--sp-amber);
            color: #fff;
            border: none;
            padding: 10px 26px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background .2s;
        }
        .btn-ver-mas:hover { background: var(--sp-naranja); color: #fff; }

        /* ---- COMUNICADOS ---- */
        .sp-comunicados { background: var(--sp-crema); padding: 80px 0; }
        .comunicado-item {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 2px 12px rgba(0,0,0,.05);
            transition: box-shadow .2s, border-left-color .2s;
            text-decoration: none;
            border-left: 4px solid var(--sp-azul);
            color: inherit;
        }
        .comunicado-item:nth-child(2) { border-left-color: var(--sp-amarillo); }
        .comunicado-item:nth-child(3) { border-left-color: var(--sp-naranja); }
        .comunicado-item:nth-child(4) { border-left-color: var(--sp-rojo); }
        .comunicado-item:hover { box-shadow: 0 6px 24px rgba(0,0,0,.1); }
        .comunicado-item .fecha-box {
            min-width: 56px;
            background: var(--sp-azul);
            color: #fff;
            border-radius: 8px;
            text-align: center;
            padding: 10px 6px;
        }
        .comunicado-item:nth-child(2) .fecha-box { background: var(--sp-amarillo); }
        .comunicado-item:nth-child(3) .fecha-box { background: var(--sp-naranja); }
        .comunicado-item:nth-child(4) .fecha-box { background: var(--sp-rojo); }
        .comunicado-item .fecha-box strong { display: block; font-size: 22px; font-weight: 800; line-height: 1; }
        .comunicado-item .fecha-box span { font-size: 11px; text-transform: uppercase; opacity: .9; }
        .comunicado-item .info h6 { font-size: 15px; font-weight: 700; color: var(--sp-negro); margin-bottom: 4px; }
        .comunicado-item .info p { font-size: 13px; color: var(--sp-gris-texto); margin: 0; }
        .comunicado-item .arrow { margin-left: auto; color: var(--sp-azul); font-size: 18px; }

        /* ---- VIDEO ---- */
        .sp-video { padding: 80px 0; background: #fff; }
        .video-wrapper {
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 16px 48px rgba(0,0,0,.15);
            position: relative;
        }
        .video-wrapper iframe { width: 100%; height: 420px; border: none; display: block; }

        /* ---- GALERÍA ---- */
        .sp-galeria { background: var(--sp-crema); padding: 80px 0; }
        .gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .gallery-grid .g-item {
            border-radius: 12px;
            overflow: hidden;
            height: 200px;
            position: relative;
        }
        .gallery-grid .g-item.large { grid-column: span 2; height: 414px; }
        .gallery-grid .g-item img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
        .gallery-grid .g-item:hover img { transform: scale(1.05); }
        .gallery-grid .g-item .overlay {
            position: absolute;
            inset: 0;
            background: rgba(27,94,166,.5);
            opacity: 0;
            transition: opacity .3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gallery-grid .g-item:hover .overlay { opacity: 1; }
        .gallery-grid .g-item .overlay i { font-size: 36px; color: #fff; }

        /* ---- DOCENTES ---- */
        .sp-docentes { padding: 80px 0; background: #fff; }
        .docente-card {
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.07);
            text-align: center;
            transition: transform .2s;
            background: #fff;
        }
        .docente-card:hover { transform: translateY(-5px); }
        .docente-card .img-wrap { overflow: hidden; height: 220px; }
        .docente-card .img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
        .docente-card:hover .img-wrap img { transform: scale(1.06); }
        .docente-card .info { padding: 18px; }
        .docente-card h5 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .docente-card span { font-size: 13px; color: var(--sp-azul); font-weight: 600; }
        .docente-card .social { margin-top: 10px; }
        .docente-card .social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--sp-azul-claro);
            color: var(--sp-azul);
            font-size: 13px;
            margin: 0 3px;
            text-decoration: none;
            transition: background .2s, color .2s;
        }
        .docente-card .social a:hover { background: var(--sp-azul); color: #fff; }

        /* ---- STATS BANNER ---- */
        .sp-stats {
            background: var(--sp-negro);
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }
        .sp-stats::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(to right,
                var(--sp-amber)   25%, var(--sp-naranja) 25%,
                var(--sp-naranja) 50%, var(--sp-azul)    50%,
                var(--sp-azul)    75%, var(--sp-rojo)    75%);
        }
        .stat-item { text-align: center; }
        .stat-item strong { display: block; font-size: 48px; font-weight: 800; color: var(--sp-amber); line-height: 1; }
        .stat-item span { font-size: 15px; color: rgba(255,255,255,.75); margin-top: 6px; display: block; }

        /* ---- FOOTER ---- */
        .sp-footer { background: var(--sp-negro); color: #bbb; padding: 70px 0 0; }
        .sp-footer h5 { color: #fff; font-size: 16px; font-weight: 700; margin-bottom: 20px; position: relative; padding-bottom: 10px; }
        .sp-footer h5::after { content: ''; position: absolute; bottom: 0; left: 0; width: 30px; height: 3px; background: var(--sp-amber); border-radius: 4px; }
        .sp-footer .logo-footer img { height: 60px; filter: brightness(0) invert(1); margin-bottom: 16px; }
        .sp-footer p { font-size: 14px; line-height: 1.7; }
        .sp-footer ul { list-style: none; padding: 0; margin: 0; }
        .sp-footer ul li { margin-bottom: 10px; font-size: 14px; }
        .sp-footer ul li a { color: #bbb; text-decoration: none; transition: color .2s; }
        .sp-footer ul li a:hover { color: var(--sp-amber); }
        .sp-footer ul li i { color: var(--sp-amber); margin-right: 8px; width: 16px; }
        .sp-footer .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,.2);
            color: #bbb;
            font-size: 15px;
            margin-right: 8px;
            margin-top: 8px;
            text-decoration: none;
            transition: background .2s, color .2s, border-color .2s;
        }
        .sp-footer .social-links a:hover { background: var(--sp-amber); color: #fff; border-color: var(--sp-amber); }
        .sp-footer-bottom {
            border-top: 1px solid rgba(255,255,255,.08);
            padding: 20px 0;
            margin-top: 50px;
            text-align: center;
            font-size: 13px;
            color: #777;
        }
        /* Banda de 4 colores encima del footer bottom */
        .sp-footer-colorband {
            height: 4px;
            background: linear-gradient(to right,
                var(--sp-amber)   25%, var(--sp-naranja) 25%,
                var(--sp-naranja) 50%, var(--sp-azul)    50%,
                var(--sp-azul)    75%, var(--sp-rojo)    75%);
        }
        .sp-footer-bottom a { color: #999; text-decoration: none; }
        .sp-footer-bottom a:hover { color: var(--sp-amber); }

        /* =============================================
           BOTÓN LOGIN (topbar)
        ============================================= */
        .sp-login-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #e0e0e0;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 4px 12px;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 50px;
            transition: background .2s, border-color .2s, color .2s;
        }
        .sp-login-btn i { font-size: 14px; color: var(--sp-amber); }
        .sp-login-btn:hover {
            background: rgba(232,160,48,.15);
            border-color: var(--sp-amber);
            color: var(--sp-amber);
        }

        /* =============================================
           MODAL LOGIN
        ============================================= */
        .sp-modal-login {
            background: #1e2132;
            border: none;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(0,0,0,.55);
        }

        /* Banda de 4 colores en la parte superior del modal */
        .sp-modal-colorband {
            height: 5px;
            background: linear-gradient(to right,
                var(--sp-amber)   25%, var(--sp-naranja) 25%,
                var(--sp-naranja) 50%, var(--sp-azul)    50%,
                var(--sp-azul)    75%, var(--sp-rojo)    75%);
        }

        /* Cabecera del modal */
        .sp-modal-header {
            text-align: center;
            padding: 28px 28px 16px;
            position: relative;
        }
        .sp-modal-close {
            position: absolute;
            top: 14px;
            right: 14px;
            background: rgba(255,255,255,.08);
            border: none;
            color: #aaa;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: 13px;
            cursor: pointer;
            transition: background .2s, color .2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sp-modal-close:hover { background: var(--sp-rojo); color: #fff; }

        .sp-modal-logo {
            width: 56px;
            height: 56px;
            background: rgba(232,160,48,.15);
            border: 2px solid rgba(232,160,48,.35);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 24px;
            color: var(--sp-amber);
        }
        .sp-modal-header h5 {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .sp-modal-header p {
            color: #888;
            font-size: 13px;
            margin: 0;
        }

        /* Área de botones */
        .sp-modal-areas {
            padding: 12px 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sp-area-btn {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #272b3f;
            color: #d0d0d0;
            text-decoration: none;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            border: 1px solid transparent;
            transition: background .2s, border-color .2s, color .2s, transform .15s;
        }
        .sp-area-btn > i:first-child {
            font-size: 18px;
            color: var(--sp-amber);
            width: 22px;
            text-align: center;
            flex-shrink: 0;
        }
        .sp-area-btn span { flex: 1; }
        .sp-area-arrow { font-size: 12px; color: #555; transition: color .2s, transform .2s; }

        .sp-area-btn:hover {
            background: #2e3450;
            border-color: var(--sp-amber);
            color: #fff;
            transform: translateX(3px);
        }
        .sp-area-btn:hover .sp-area-arrow {
            color: var(--sp-amber);
            transform: translateX(3px);
        }

        /* Separador "Administración" */
        .sp-modal-sep {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 4px 0 0;
        }
        .sp-modal-sep::before,
        .sp-modal-sep::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,.1);
        }
        .sp-modal-sep span {
            font-size: 11px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        /* Botón admin — acento rojo/ámbar */
        .sp-area-admin > i:first-child { color: var(--sp-rojo); }
        .sp-area-admin:hover { border-color: var(--sp-rojo); }
        .sp-area-admin:hover .sp-area-arrow { color: var(--sp-rojo); }

        /* =============================================
           MODAL ÁREA — footer compartido
        ============================================= */
        .sp-area-footer {
            text-align: center;
            padding: 16px 24px 22px;
            border-top: 1px solid rgba(255,255,255,.07);
            margin-top: 4px;
        }
        .sp-area-footer p {
            color: rgba(255,255,255,.55);
            font-size: 13px;
            margin: 3px 0;
        }
        .sp-area-footer strong { color: rgba(255,255,255,.8); }
        .sp-area-lema  { font-style:italic; font-size:12px !important; color:rgba(255,255,255,.38) !important; }
        .sp-area-copy  { font-size:11px    !important; color:rgba(255,255,255,.2)  !important; margin-top:6px !important; }

        /* =============================================
           MODAL ADMIN LOGIN — FORMULARIO
        ============================================= */
        .sp-login-form {
            padding: 8px 22px 26px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* Alerta */
        .sp-login-alert {
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sp-login-alert.error  { background: rgba(217,69,53,.15); color: #ff7b6b; border: 1px solid rgba(217,69,53,.3); }
        .sp-login-alert.success{ background: rgba(46,185,126,.15); color: #5de8a8; border: 1px solid rgba(46,185,126,.3); }

        /* Campos */
        .sp-field { display: flex; flex-direction: column; gap: 6px; }
        .sp-field label {
            font-size: 12px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .sp-field label i { color: var(--sp-amber); margin-right: 5px; }
        .sp-field input {
            background: #272b3f;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 8px;
            color: #e0e0e0;
            padding: 11px 14px;
            font-size: 14px;
            outline: none;
            transition: border-color .2s;
            width: 100%;
        }
        .sp-field input::placeholder { color: #555; }
        .sp-field input:focus { border-color: var(--sp-amber); }
        .sp-field input.is-invalid { border-color: var(--sp-rojo); }

        /* Wrapper contraseña + ojo */
        .sp-pass-wrap { position: relative; }
        .sp-pass-wrap input { padding-right: 42px; }
        .sp-toggle-pass {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #555; cursor: pointer;
            font-size: 14px;
            padding: 0;
            transition: color .2s;
        }
        .sp-toggle-pass:hover { color: var(--sp-amber); }

        /* Botón ingresar */
        .sp-btn-login {
            background: var(--sp-rojo);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 13px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: background .2s, transform .15s;
            margin-top: 4px;
        }
        .sp-btn-login:hover  { background: #b8293c; transform: translateY(-1px); }
        .sp-btn-login:active { transform: translateY(0); }
        .sp-btn-login:disabled { opacity: .65; cursor: not-allowed; transform: none; }

        /* Link volver */
        .sp-volver-link {
            font-size: 13px;
            color: #666;
            text-decoration: none;
            transition: color .2s;
        }
        .sp-volver-link:hover { color: var(--sp-amber); }

        /* =============================================
           CARRUSEL HERO
        ============================================= */
        .sp-carousel-hero { position: relative; }

        /* Imagen de fondo del slide — cubre todo el item */
        .carousel-item {
            height: 580px;
            position: relative;
        }
        .slide-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            transition: transform 8s ease;
        }
        /* Efecto Ken Burns sutil en el slide activo */
        .carousel-item.active .slide-bg {
            transform: scale(1.05);
        }

        /* Overlay oscuro degradado desde la izquierda */
        .slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to right,
                rgba(20, 20, 30, 0.78) 0%,
                rgba(20, 20, 30, 0.45) 55%,
                rgba(20, 20, 30, 0.10) 100%
            );
        }

        /* Contenedor del texto, centrado verticalmente */
        .slide-content {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            padding: 0 60px;
        }

        .slide-texto {
            max-width: 600px;
            color: #fff;
        }

        /* Etiqueta pequeña sobre el título */
        .slide-label {
            display: inline-block;
            background: rgba(232, 160, 48, 0.20);
            color: var(--sp-amber);
            border: 1px solid rgba(232, 160, 48, 0.45);
            border-radius: 50px;
            padding: 5px 16px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 18px;
        }
        .slide-label i { margin-right: 6px; }

        /* Título principal */
        .slide-texto h2 {
            font-size: 54px;
            font-weight: 700;
            line-height: 1.12;
            color: #fff;
            margin-bottom: 28px;
            text-shadow: 0 2px 12px rgba(0,0,0,.35);
        }
        .slide-texto h2 strong {
            color: var(--sp-amber);
            font-weight: 900;
        }

        /* Botones */
        .slide-botones { display: flex; flex-wrap: wrap; gap: 12px; }

        .slide-btn-primary {
            background: var(--sp-amber);
            color: #fff;
            padding: 13px 30px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: background .2s, transform .2s;
            display: inline-block;
        }
        .slide-btn-primary:hover {
            background: var(--sp-naranja);
            color: #fff;
            transform: translateY(-2px);
        }

        .slide-btn-outline {
            background: transparent;
            color: #fff;
            border: 2px solid rgba(255,255,255,.65);
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s, border-color .2s;
            display: inline-block;
        }
        .slide-btn-outline:hover {
            background: rgba(255,255,255,.12);
            border-color: #fff;
            color: #fff;
        }

        /* Indicadores personalizados */
        #heroCarousel .carousel-indicators {
            bottom: 22px;
            gap: 8px;
        }
        #heroCarousel .carousel-indicators button {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,.6);
            background: transparent;
            opacity: 1;
            transition: background .3s, border-color .3s, width .3s;
            padding: 0;
        }
        #heroCarousel .carousel-indicators button.active {
            background: var(--sp-amber);
            border-color: var(--sp-amber);
            width: 28px;
            border-radius: 50px;
        }

        /* Botones prev / next */
        .carousel-ctrl-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,.15);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,.3);
            color: #fff;
            font-size: 16px;
            transition: background .2s;
        }
        .carousel-control-prev:hover .carousel-ctrl-icon,
        .carousel-control-next:hover .carousel-ctrl-icon {
            background: var(--sp-amber);
            border-color: var(--sp-amber);
        }
        .carousel-control-prev,
        .carousel-control-next {
            width: auto;
            padding: 0 20px;
            opacity: 1;
        }

        /* Franja de colores debajo del carrusel */
        .sp-carousel-hero::after {
            content: '';
            display: block;
            height: 5px;
            background: linear-gradient(to right,
                var(--sp-amber)   25%, var(--sp-naranja) 25%,
                var(--sp-naranja) 50%, var(--sp-azul)    50%,
                var(--sp-azul)    75%, var(--sp-rojo)    75%);
        }

        /* ---- MOBILE ---- */
        @media (max-width: 991px) {
            .sp-nav { display: none; }
            .carousel-item { height: 420px; }
            .slide-content { padding: 0 30px; }
            .slide-texto h2 { font-size: 36px; }
            .gallery-grid { grid-template-columns: repeat(2, 1fr); }
            .gallery-grid .g-item.large { grid-column: span 2; height: 250px; }
        }
        @media (max-width: 576px) {
            .carousel-item { height: 340px; }
            .slide-content { padding: 0 20px; }
            .slide-texto h2 { font-size: 28px; }
            .slide-botones { flex-direction: column; }
            .gallery-grid { grid-template-columns: 1fr; }
            .gallery-grid .g-item.large { height: 220px; }
        }
    </style>
</head>

<body>

    <!-- Banda de 4 colores institucionales -->
    <div class="sp-colorband"></div>

    <!-- ===================== TOP BAR ===================== -->
    <div class="sp-topbar d-none d-md-block">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-map-marker-alt me-2" style="color:var(--sp-amarillo)"></i>Venancio Benavídez 3612
                    <span class="sep">|</span>
                    <i class="fas fa-phone me-2" style="color:var(--sp-amarillo)"></i>+598 2337 3737
                    <span class="sep">|</span>
                    <i class="fas fa-envelope me-2" style="color:var(--sp-amarillo)"></i>
                    <a href="mailto:info@sanpablo.edu.uy">info@sanpablo.edu.uy</a>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <!-- Botón Login → abre modal -->
                    <a href="#" class="sp-login-btn" data-bs-toggle="modal" data-bs-target="#modalLogin"
                       title="Ingresar al sistema">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                    <span class="sep">|</span>
                    <a href="#" class="me-1"><i class="fab fa-instagram me-1"></i>Instagram</a>
                    <a href="#"><i class="fab fa-facebook me-1"></i>Facebook</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== HEADER ===================== -->
    <header class="sp-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between">
                <!-- Logo -->
                <div class="sp-logo py-2">
                    <a href="#">
                        <img src="https://colegiosanpablo.webnia.cl/wp-content/uploads/2025/11/logo-sin-fondo-1.png"
                             alt="Colegio San Pablo"
                             onerror="this.src='assets/images/logo/logo.svg'">
                    </a>
                </div>
                <!-- Nav dinámica desde BD -->
                <nav class="sp-nav">
                    <ul>
                        <?php foreach ($arrMenus as $i => $menu):
                            $idMenu  = (int)$menu['id_menu'];
                            $nombre  = htmlspecialchars($menu['nombre'], ENT_QUOTES);
                            $url     = htmlspecialchars($menu['url'] ?: '#', ENT_QUOTES);
                            $hasSubs = !empty($arrSubs[$idMenu]);
                            $active  = ($i === 0) ? ' class="active"' : '';
                        ?>
                        <li<?= $active ?>>
                            <a href="<?= $url ?>">
                                <?= $nombre ?><?= $hasSubs ? ' ▾' : '' ?>
                            </a>
                            <?php if ($hasSubs): ?>
                            <ul class="dropdown">
                                <?php foreach ($arrSubs[$idMenu] as $sub):
                                    $subNombre = htmlspecialchars($sub['nombre'], ENT_QUOTES);
                                    $subUrl    = htmlspecialchars($sub['url'] ?: '#', ENT_QUOTES);
                                ?>
                                <li>
                                    <a href="<?= $subUrl ?>"><?= $subNombre ?></a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                        <li><a href="#" class="sp-btn-matricula">Matrícula</a></li>
                    </ul>
                </nav>
                <!-- Hamburger (mobile) -->
                <button class="btn d-lg-none" style="font-size:22px;color:var(--sp-azul)">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- ===================== CARRUSEL HERO ===================== -->
    <section class="sp-carousel-hero">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">

            <!-- Indicadores -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
            </div>

            <div class="carousel-inner">

                <!-- Slide 1 — portada_2 -->
                <div class="carousel-item active">
                    <div class="slide-bg" style="background-image:url('assets/images/portada_2.jpg')"></div>
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="slide-texto">
                            <span class="slide-label"><i class="fas fa-star"></i> Colegio San Pablo</span>
                            <h2>Quien Educa<br><strong>Con Amor</strong><br>Educa Para Siempre</h2>
                            <div class="slide-botones">
                                <a href="#niveles" class="slide-btn-primary">Conoce nuestros niveles</a>
                                <a href="#portal"  class="slide-btn-outline">Acceso Mi San Pablo</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 — portada_1 -->
                <div class="carousel-item">
                    <div class="slide-bg" style="background-image:url('assets/images/portada_1.jpg')"></div>
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="slide-texto">
                            <span class="slide-label"><i class="fas fa-heart"></i> Nuestra Misión</span>
                            <h2>Caminamos<br><strong>Juntos</strong><br>Hacia el Futuro</h2>
                            <div class="slide-botones">
                                <a href="#noticias" class="slide-btn-primary">Ver novedades</a>
                                <a href="#portal"   class="slide-btn-outline">Acceso Mi San Pablo</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 — portada_3 -->
                <div class="carousel-item">
                    <div class="slide-bg" style="background-image:url('assets/images/portada_3.jpg')"></div>
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="slide-texto">
                            <span class="slide-label"><i class="fas fa-users"></i> Comunidad Educativa</span>
                            <h2>Disfrutamos<br><strong>Creciendo</strong><br>Contigo</h2>
                            <div class="slide-botones">
                                <a href="#galeria"  class="slide-btn-primary">Ver galería</a>
                                <a href="#docentes" class="slide-btn-outline">Nuestro equipo</a>
                            </div>
                        </div>
                    </div>
                </div>

                      <div class="carousel-item">
                    <div class="slide-bg" style="background-image:url('assets/images/IMG_6939.JPG')"></div>
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="slide-texto">
                            <span class="slide-label"><i class="fas fa-users"></i> Comunidad Educativa</span>
                            <h2>Disfrutamos<br><strong>Creciendo</strong><br>Contigo</h2>
                            <div class="slide-botones">
                                <a href="#galeria"  class="slide-btn-primary">Ver galería</a>
                                <a href="#docentes" class="slide-btn-outline">Nuestro equipo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /carousel-inner -->

            <!-- Controles prev / next -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-ctrl-icon"><i class="fas fa-chevron-left"></i></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-ctrl-icon"><i class="fas fa-chevron-right"></i></span>
            </button>

        </div>
    </section>

    <!-- ===================== NIVELES EDUCATIVOS ===================== -->
    <section class="sp-niveles" id="niveles">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label">Oferta Educativa</span>
                <h2 class="section-title">Nuestros <span>Niveles</span></h2>
                <div class="divider-line mx-auto"></div>
                <p class="section-desc mx-auto">Acompañamos cada etapa del crecimiento de tus hijos con propuestas pedagógicas de calidad.</p>
            </div>
            <div class="row g-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="nivel-card">
                        <div class="icon"><i class="fas fa-baby"></i></div>
                        <h5>Maternal</h5>
                        <p>0 a 3 años</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="nivel-card">
                        <div class="icon"><i class="fas fa-child"></i></div>
                        <h5>Inicial</h5>
                        <p>3 a 6 años</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="nivel-card">
                        <div class="icon"><i class="fas fa-pencil-alt"></i></div>
                        <h5>Primaria</h5>
                        <p>6 a 11 años</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="nivel-card">
                        <div class="icon"><i class="fas fa-book"></i></div>
                        <h5>3er Ciclo EBI</h5>
                        <p>12 a 15 años</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="nivel-card">
                        <div class="icon"><i class="fas fa-graduation-cap"></i></div>
                        <h5>Bachillerato</h5>
                        <p>15 a 18 años</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="nivel-card">
                        <div class="icon"><i class="fas fa-user-graduate"></i></div>
                        <h5>Libre Asistido</h5>
                        <p>Educación flexible</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== PORTAL MI SAN PABLO ===================== -->
    <section class="sp-portal" id="portal">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <span class="section-label" style="color:#cde">Portal Digital</span>
                    <h2>Acceso<br>Mi San Pablo</h2>
                    <p>Plataforma de gestión escolar para toda la comunidad educativa. Ingresa con tus credenciales.</p>
                    <a href="#" class="btn-primary-sp mt-2" style="background:var(--sp-amber);border-radius:50px">Recuperar contraseña</a>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <a href="#" class="portal-card">
                                <div class="icon"><i class="fas fa-user-graduate"></i></div>
                                <h6>Alumnos</h6>
                                <small>Notas, horarios y tareas</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="#" class="portal-card">
                                <div class="icon"><i class="fas fa-users"></i></div>
                                <h6>Padres</h6>
                                <small>Seguimiento y comunicados</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="#" class="portal-card">
                                <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                <h6>Docentes</h6>
                                <small>Planificación y registros</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="#" class="portal-card">
                                <div class="icon"><i class="fas fa-briefcase"></i></div>
                                <h6>Funcionarios</h6>
                                <small>Administración interna</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== NOTICIAS ===================== -->
    <section class="sp-noticias" id="noticias">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
                <div>
                    <span class="section-label">Novedades</span>
                    <h2 class="section-title">Últimas <span>Noticias</span></h2>
                    <div class="divider-line"></div>
                </div>
                <a href="#" class="btn-ver-mas">Ver todas las noticias</a>
            </div>
            <div class="row g-4">
                <!-- Noticia 1 -->
                <div class="col-md-6 col-lg-3">
                    <div class="noticia-card">
                        <div class="img-wrap">
                            <img src="assets/images/frontis_01.jpg" alt="Rugby del Prado">
                        </div>
                        <div class="card-body">
                            <span class="tag">Deporte</span>
                            <h5>Rugby del Prado</h5>
                            <p>Nuestros alumnos participaron en el torneo intercolegial de rugby con excelentes resultados.</p>
                            <div class="meta">
                                <i class="fas fa-calendar-alt"></i> Noviembre 2025
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Noticia 2 -->
                <div class="col-md-6 col-lg-3">
                    <div class="noticia-card">
                        <div class="img-wrap">
                            <img src="assets/images/frontis_02.jpg" alt="Certificaciones Inglés">
                        </div>
                        <div class="card-body">
                            <span class="tag">Idiomas</span>
                            <h5>Certificaciones Inglés y Portugués</h5>
                            <p>Alumnos de bachillerato rindieron y aprobaron certificaciones internacionales de idiomas.</p>
                            <div class="meta">
                                <i class="fas fa-calendar-alt"></i> Noviembre 2025
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Noticia 3 -->
                <div class="col-md-6 col-lg-3">
                    <div class="noticia-card">
                        <div class="img-wrap">
                            <img src="assets/images/frontis_03.jpg" alt="Jornada Orientate">
                        </div>
                        <div class="card-body">
                            <span class="tag">Orientación</span>
                            <h5>Jornada Orientate</h5>
                            <p>Jornada de orientación vocacional para estudiantes de 3er ciclo y bachillerato.</p>
                            <div class="meta">
                                <i class="fas fa-calendar-alt"></i> Octubre 2025
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Noticia 4 -->
                <div class="col-md-6 col-lg-3">
                    <div class="noticia-card">
                        <div class="img-wrap">
                            <img src="assets/images/frontis_04.jpg" alt="Reciclar Actitudes">
                        </div>
                        <div class="card-body">
                            <span class="tag">Medio Ambiente</span>
                            <h5>Proyecto Reciclar Actitudes</h5>
                            <p>Proyecto de concientización ambiental que involucra a toda la comunidad escolar.</p>
                            <div class="meta">
                                <i class="fas fa-calendar-alt"></i> Octubre 2025
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== COMUNICADOS ===================== -->
    <section class="sp-comunicados" id="comunicados">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-5">
                    <span class="section-label">Informaciones oficiales</span>
                    <h2 class="section-title">Comunicados <span>Institucionales</span></h2>
                    <div class="divider-line"></div>
                    <p class="section-desc">Mantenemos informada a toda la comunidad educativa mediante comunicados oficiales y anuncios importantes.</p>
                    <a href="#" class="btn-ver-mas mt-3">Ver todos los comunicados</a>
                </div>
                <div class="col-lg-7">
                    <a href="#" class="comunicado-item">
                        <div class="fecha-box">
                            <strong>15</strong>
                            <span>Nov</span>
                        </div>
                        <div class="info">
                            <h6>Calendario de actividades fin de año</h6>
                            <p>Fechas de actos, exámenes y entrega de libretas del segundo semestre.</p>
                        </div>
                        <div class="arrow"><i class="fas fa-chevron-right"></i></div>
                    </a>
                    <a href="#" class="comunicado-item">
                        <div class="fecha-box">
                            <strong>10</strong>
                            <span>Nov</span>
                        </div>
                        <div class="info">
                            <h6>Proceso de matrícula 2026</h6>
                            <p>Información sobre plazos y documentación requerida para la reinscripción.</p>
                        </div>
                        <div class="arrow"><i class="fas fa-chevron-right"></i></div>
                    </a>
                    <a href="#" class="comunicado-item">
                        <div class="fecha-box">
                            <strong>05</strong>
                            <span>Nov</span>
                        </div>
                        <div class="info">
                            <h6>Nueva organización del menú por niveles educativos</h6>
                            <p>Renovación del sitio web con mejor navegación por secciones.</p>
                        </div>
                        <div class="arrow"><i class="fas fa-chevron-right"></i></div>
                    </a>
                    <a href="#" class="comunicado-item">
                        <div class="fecha-box">
                            <strong>01</strong>
                            <span>Nov</span>
                        </div>
                        <div class="info">
                            <h6>Acto de clausura de primaria</h6>
                            <p>Invitación a la ceremonia de finalización del año lectivo de 6° año.</p>
                        </div>
                        <div class="arrow"><i class="fas fa-chevron-right"></i></div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== VIDEO INSTITUCIONAL ===================== -->
    <section class="sp-video">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label">Conócenos</span>
                <h2 class="section-title">Video <span>Institucional</span></h2>
                <div class="divider-line mx-auto"></div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="video-wrapper">
                        <iframe
                            src="https://www.youtube.com/embed/df-gbYjvElg"
                            title="Colegio San Pablo - Video Institucional"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== GALERÍA ===================== -->
    <section class="sp-galeria" id="galeria">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label">Vida Escolar</span>
                <h2 class="section-title">Galería <span>Fotográfica</span></h2>
                <div class="divider-line mx-auto"></div>
                <p class="section-desc mx-auto">Momentos únicos de nuestra comunidad educativa a lo largo del año.</p>
            </div>
            <div class="gallery-grid">
                <div class="g-item large">
                    <img src="assets/images/20251107131854222.png" alt="Actividad escolar">
                    <div class="overlay"><i class="fas fa-search-plus"></i></div>
                </div>
                <div class="g-item">
                    <img src="assets/images/IMG_6827.jpg" alt="Actividad escolar">
                    <div class="overlay"><i class="fas fa-search-plus"></i></div>
                </div>
                <div class="g-item">
                    <img src="assets/images/20251107131854.png" alt="Actividad escolar">
                    <div class="overlay"><i class="fas fa-search-plus"></i></div>
                </div>
                <div class="g-item">
                    <img src="assets/images/20251107131913.png" alt="Actividad escolar">
                    <div class="overlay"><i class="fas fa-search-plus"></i></div>
                </div>
                <div class="g-item">
                    <img src="assets/images/20251107131958.png" alt="Actividad escolar">
                    <div class="overlay"><i class="fas fa-search-plus"></i></div>
                </div>
                <div class="g-item">
                    <img src="assets/images/IMG_6831.jpg" alt="Actividad escolar">
                    <div class="overlay"><i class="fas fa-search-plus"></i></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== ESTADÍSTICAS ===================== -->
    <section class="sp-stats">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <strong>+50</strong>
                        <span>Años de historia</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <strong>800+</strong>
                        <span>Alumnos matriculados</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <strong>60+</strong>
                        <span>Docentes especializados</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <strong>3</strong>
                        <span>Sedes educativas</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== DOCENTES ===================== -->
    <section class="sp-docentes" id="docentes">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label">Nuestro equipo</span>
                <h2 class="section-title">Equipo <span>Docente</span></h2>
                <div class="divider-line mx-auto"></div>
                <p class="section-desc mx-auto">Profesionales comprometidos con la educación y el desarrollo integral de cada alumno.</p>
            </div>
            <div class="row g-4">
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="docente-card">
                        <div class="img-wrap">
                            <img src="assets/images/docentes/member-1.jpg" alt="Docente">
                        </div>
                        <div class="info">
                            <h5>María González</h5>
                            <span>Maestra de Primaria</span>
                            <div class="social">
                                <a href="#"><i class="fas fa-envelope"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="docente-card">
                        <div class="img-wrap">
                            <img src="assets/images/docentes/member-2.jpg" alt="Docente">
                        </div>
                        <div class="info">
                            <h5>Carlos Ramírez</h5>
                            <span>Prof. de Matemáticas</span>
                            <div class="social">
                                <a href="#"><i class="fas fa-envelope"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="docente-card">
                        <div class="img-wrap">
                            <img src="assets/images/docentes/member-3.jpg" alt="Docente">
                        </div>
                        <div class="info">
                            <h5>Ana Fernández</h5>
                            <span>Prof. de Inglés</span>
                            <div class="social">
                                <a href="#"><i class="fas fa-envelope"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="docente-card">
                        <div class="img-wrap">
                            <img src="assets/images/docentes/member-8.jpg" alt="Docente">
                        </div>
                        <div class="info">
                            <h5>Roberto Silva</h5>
                            <span>Prof. de Educación Física</span>
                            <div class="social">
                                <a href="#"><i class="fas fa-envelope"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="docente-card">
                        <div class="img-wrap">
                            <img src="assets/images/docentes/member-1.jpg" alt="Docente">
                        </div>
                        <div class="info">
                            <h5>Laura Martínez</h5>
                            <span>Maestra de Inicial</span>
                            <div class="social">
                                <a href="#"><i class="fas fa-envelope"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="docente-card">
                        <div class="img-wrap">
                            <img src="assets/images/docentes/member-6.jpg" alt="Docente">
                        </div>
                        <div class="info">
                            <h5>Diego Herrera</h5>
                            <span>Prof. de Ciencias</span>
                            <div class="social">
                                <a href="#"><i class="fas fa-envelope"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="docente-card">
                        <div class="img-wrap">
                            <img src="assets/images/docentes/member-9.jpg" alt="Docente">
                        </div>
                        <div class="info">
                            <h5>Sofía Álvarez</h5>
                            <span>Maestra de Maternal</span>
                            <div class="social">
                                <a href="#"><i class="fas fa-envelope"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="docente-card">
                        <div class="img-wrap">
                            <img src="assets/images/docentes/member-4.jpg" alt="Docente">
                        </div>
                        <div class="info">
                            <h5>Pablo Torres</h5>
                            <span>Prof. de Historia</span>
                            <div class="social">
                                <a href="#"><i class="fas fa-envelope"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
    <footer class="sp-footer">
        <div class="container">
            <div class="row g-5">
                <!-- Col 1: Logo + Descripción -->
                <div class="col-lg-4">
                    <div class="logo-footer">
                        <img src="https://colegiosanpablo.webnia.cl/wp-content/uploads/2025/11/logo-sin-fondo-1.png"
                             alt="Colegio San Pablo"
                             onerror="this.src='assets/images/logo/logo-light.svg'">
                    </div>
                    <p>"Quien enseña con amor educa para siempre." Formamos ciudadanos íntegros, críticos y comprometidos con su entorno desde hace más de 50 años.</p>
                    <div class="social-links mt-3">
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <!-- Col 2: Menú rápido -->
                <div class="col-lg-2 col-md-4">
                    <h5>Menú Rápido</h5>
                    <ul>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Inicio</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Institucional</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Noticias</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Comunicados</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Biblioteca</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Confesionalidad</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Matrícula</a></li>
                    </ul>
                </div>
                <!-- Col 3: Niveles educativos -->
                <div class="col-lg-2 col-md-4">
                    <h5>Niveles</h5>
                    <ul>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Maternal</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Inicial</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Primaria</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>3er Ciclo EBI</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Bachillerato</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i>Libre Asistido</a></li>
                    </ul>
                </div>
                <!-- Col 4: Contacto y sedes -->
                <div class="col-lg-4 col-md-4">
                    <h5>Contacto y Sedes</h5>
                    <ul>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <strong style="color:#fff">Administración:</strong><br>
                            <span style="padding-left:24px">Venancio Benavídez 3612</span>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <strong style="color:#fff">Inicial:</strong><br>
                            <span style="padding-left:24px">Joaquín Suárez 3596 | Tel 2336 6000</span>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <strong style="color:#fff">Preuniversitario:</strong><br>
                            <span style="padding-left:24px">Av. Millán 3375 | Tel 2202 0000</span>
                        </li>
                        <li><i class="fas fa-phone"></i> +598 2337 3737</li>
                        <li><i class="fas fa-envelope"></i> <a href="mailto:info@sanpablo.edu.uy">info@sanpablo.edu.uy</a></li>
                        <li><i class="fas fa-globe"></i> <a href="https://www.sanpablo.edu.uy" target="_blank">www.sanpablo.edu.uy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Banda de colores antes del bottom -->
        <div class="sp-footer-colorband"></div>
        <!-- Footer Bottom -->
        <div class="sp-footer-bottom">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>&copy; 2026 Colegio San Pablo. Todos los derechos reservados.</span>
                    <span>
                        <a href="#">Política de privacidad</a> &nbsp;|&nbsp;
                        <a href="#">Términos legales</a> &nbsp;|&nbsp;
                        <a href="#">Admisiones</a>
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- ===================== MODAL LOGIN ===================== -->
    <div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sp-modal-login">

                <!-- Banda de 4 colores institucionales -->
                <div class="sp-modal-colorband"></div>

                <div class="modal-body p-0">

                    <!-- Cabecera -->
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

                    <!-- Opciones de área -->
                    <div class="sp-modal-areas">

                        <!-- Admin → abre modal de login -->
                        <a href="#" class="sp-area-btn sp-area-admin" id="btnAbrirLogin"
                           data-bs-dismiss="modal"
                           data-bs-toggle="modal"
                           data-bs-target="#modalAdminLogin">
                            <i class="fas fa-lock"></i>
                            <span>Admin</span>
                            <i class="fas fa-chevron-right sp-area-arrow"></i>
                        </a>

                        <a href="#" class="sp-area-btn btn-abrir-area"
                           data-area="ALUMNOS" data-icono="fa-user-graduate"
                           data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalArea">
                            <i class="fas fa-user-graduate"></i>
                            <span>Área Alumnos</span>
                            <i class="fas fa-chevron-right sp-area-arrow"></i>
                        </a>

                        <a href="#" class="sp-area-btn btn-abrir-area"
                           data-area="PADRES" data-icono="fa-users"
                           data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalArea">
                            <i class="fas fa-users"></i>
                            <span>Área Padres</span>
                            <i class="fas fa-chevron-right sp-area-arrow"></i>
                        </a>

                        <a href="#" class="sp-area-btn btn-abrir-area"
                           data-area="FUNCIONARIO" data-icono="fa-briefcase"
                           data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalArea">
                            <i class="fas fa-briefcase"></i>
                            <span>Área Funcionario</span>
                            <i class="fas fa-chevron-right sp-area-arrow"></i>
                        </a>

                        <a href="#" class="sp-area-btn btn-abrir-area"
                           data-area="DOCENTES" data-icono="fa-chalkboard-teacher"
                           data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalArea">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Área Docentes</span>
                            <i class="fas fa-chevron-right sp-area-arrow"></i>
                        </a>

                    </div>

                </div>

            </div>
        </div>
    </div>
    <!-- /MODAL LOGIN -->

    <!-- ===================== MODAL ADMIN LOGIN ===================== -->
    <div class="modal fade" id="modalAdminLogin" tabindex="-1"
         aria-labelledby="modalAdminLoginLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
            <div class="modal-content sp-modal-login">

                <div class="sp-modal-colorband"></div>

                <div class="modal-body p-0">

                    <!-- Cabecera -->
                    <div class="sp-modal-header">
                        <button type="button" class="sp-modal-close" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="sp-modal-logo" style="background:rgba(206,53,73,.12);border-color:rgba(206,53,73,.35)">
                            <i class="fas fa-shield-alt" style="color:var(--sp-rojo)"></i>
                        </div>
                        <h5 id="modalAdminLoginLabel">Acceso Administrador</h5>
                        <p>Ingresa tus credenciales para continuar</p>
                    </div>

                    <!-- Formulario -->
                    <form id="formAdminLogin" novalidate autocomplete="off">
                        <div class="sp-login-form">

                            <!-- Alerta de error / éxito -->
                            <div id="loginAlert" class="sp-login-alert" style="display:none"></div>

                            <!-- Campo usuario -->
                            <div class="sp-field">
                                <label for="loginUsuario">
                                    <i class="fas fa-user"></i> Usuario
                                </label>
                                <input type="text" id="loginUsuario" name="usuario"
                                       placeholder="nombre@dominio.cl"
                                       autocomplete="username" required>
                            </div>

                            <!-- Campo clave -->
                            <div class="sp-field">
                                <label for="loginClave">
                                    <i class="fas fa-lock"></i> Clave
                                </label>
                                <div class="sp-pass-wrap">
                                    <input type="password" id="loginClave" name="clave"
                                           placeholder="••••••••"
                                           autocomplete="current-password" required>
                                    <button type="button" class="sp-toggle-pass" id="adminTogglePass" tabindex="-1"
                                            title="Mostrar/ocultar clave">
                                        <i class="fas fa-eye" id="iconEye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Botón ingresar -->
                            <button type="submit" id="btnLogin" class="sp-btn-login">
                                <span id="btnLoginTxt">
                                    <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                                </span>
                                <span id="btnLoginSpinner" style="display:none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Verificando...
                                </span>
                            </button>

                            <!-- Volver -->
                            <div class="text-center mt-3">
                                <a href="#" class="sp-volver-link"
                                   data-bs-dismiss="modal"
                                   data-bs-toggle="modal"
                                   data-bs-target="#modalLogin">
                                    <i class="fas fa-arrow-left me-1"></i>Volver
                                </a>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <!-- /MODAL ADMIN LOGIN -->

    <!-- ===================== MODAL ÁREA (Alumnos / Padres / Funcionario / Docentes) ===================== -->
    <div class="modal fade" id="modalArea" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
            <div class="modal-content sp-modal-login">

                <div class="sp-modal-colorband"></div>

                <div class="modal-body p-0">

                    <!-- Cabecera igual que admin -->
                    <div class="sp-modal-header">
                        <button type="button" class="sp-modal-close" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>
                        </button>
                        <!-- Ícono dinámico del área -->
                        <div class="sp-modal-logo" id="areaIconoWrap">
                            <i class="fas fa-user-graduate" id="areaIcono"></i>
                        </div>
                        <h5 id="areaTitulo">Área ALUMNOS</h5>
                        <p>Ingresa tus credenciales para continuar</p>
                    </div>

                    <!-- Formulario igual que admin -->
                    <form id="formArea" novalidate autocomplete="off">
                        <div class="sp-login-form">
                            <input type="hidden" id="areaActual" value="">

                            <!-- Alerta -->
                            <div id="areaAlert" class="sp-login-alert" style="display:none"></div>

                            <!-- Usuario -->
                            <div class="sp-field">
                                <label for="areaUsuario">
                                    <i class="fas fa-user"></i> Usuario
                                </label>
                                <input type="text" id="areaUsuario" name="usuario"
                                       placeholder="nombre@dominio.cl"
                                       autocomplete="username" required>
                            </div>

                            <!-- Contraseña -->
                            <div class="sp-field">
                                <label for="areaPass">
                                    <i class="fas fa-lock"></i> Contraseña
                                </label>
                                <div class="sp-pass-wrap">
                                    <input type="password" id="areaPass" name="pass"
                                           placeholder="••••••••"
                                           autocomplete="current-password" required>
                                    <button type="button" class="sp-toggle-pass" id="areaTogglePass" tabindex="-1">
                                        <i class="fas fa-eye" id="areaEyeIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Olvidé -->
                            <div class="text-end" style="margin-top:-4px">
                                <a href="#" class="sp-volver-link" style="font-size:12px;color:rgba(255,255,255,.45)">
                                    *Olvidé mi contraseña
                                </a>
                            </div>

                            <!-- Botón ingresar -->
                            <button type="submit" class="sp-btn-login" id="btnAreaIngresar">
                                <span id="btnAreaTxt">
                                    <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                                </span>
                                <span id="btnAreaSpinner" style="display:none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Verificando...
                                </span>
                            </button>

                            <!-- Volver -->
                            <div class="text-center mt-2">
                                <a href="#" class="sp-volver-link"
                                   data-bs-dismiss="modal"
                                   data-bs-toggle="modal"
                                   data-bs-target="#modalLogin">
                                    <i class="fas fa-arrow-left me-1"></i>Volver
                                </a>
                            </div>

                        </div>
                    </form>

                    <!-- Footer igual que screenshot -->
                    <div class="sp-area-footer">
                        <p>info@sanpablo.edu.uy</p>
                        <p>www.sanpablo.edu.uy</p>
                        <p><strong>Colegio San Pablo</strong></p>
                        <p class="sp-area-lema">"Quien enseña con amor educa para siempre"</p>
                        <p class="sp-area-copy">Mi Colegio San Pablo v2.0</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- /MODAL ÁREA -->

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <script>
        // ── Modal Área (Alumnos / Padres / Funcionario / Docentes) ──
        document.addEventListener('DOMContentLoaded', function () {

            var modalAreaEl = document.getElementById('modalArea');

            // Cuando se abre, leer el data-area del botón que lo disparó
            modalAreaEl.addEventListener('show.bs.modal', function (e) {
                var trigger = e.relatedTarget;
                var area    = trigger ? (trigger.dataset.area || 'ALUMNOS') : 'ALUMNOS';

                document.getElementById('areaTitulo').textContent = 'Área ' + area;
                document.getElementById('areaActual').value       = area;

                // Limpiar formulario y alertas
                document.getElementById('formArea').reset();
                document.getElementById('areaAlert').style.display = 'none';
                document.getElementById('areaUsuario').classList.remove('is-invalid');
                document.getElementById('areaPass').classList.remove('is-invalid');
            });

            // Submit — por ahora muestra un mensaje (conectar al sistema correspondiente)
            document.getElementById('formArea').addEventListener('submit', function (e) {
                e.preventDefault();

                var usuario = document.getElementById('areaUsuario').value.trim();
                var pass    = document.getElementById('areaPass').value.trim();
                var area    = document.getElementById('areaActual').value;
                var alertEl = document.getElementById('areaAlert');

                if (!usuario || !pass) {
                    alertEl.textContent     = 'Completa usuario y contraseña';
                    alertEl.style.display   = 'block';
                    if (!usuario) document.getElementById('areaUsuario').classList.add('is-invalid');
                    if (!pass)    document.getElementById('areaPass').classList.add('is-invalid');
                    return;
                }

                var btn     = document.getElementById('btnAreaIngresar');
                var btnTxt  = document.getElementById('btnAreaTxt');
                var spinner = document.getElementById('btnAreaSpinner');
                btn.disabled = true;
                btnTxt.style.display  = 'none';
                spinner.style.display = 'inline';
                alertEl.style.display = 'none';

                // TODO: conectar al endpoint de login de cada área
                // fetch('login_area.php', { method:'POST', ... })
                // Por ahora simulamos la respuesta:
                setTimeout(function () {
                    btn.disabled = false;
                    btnTxt.style.display  = 'inline';
                    spinner.style.display = 'none';
                    alertEl.textContent   = 'Acceso al área ' + area + ' próximamente disponible.';
                    alertEl.style.display = 'block';
                }, 800);
            });

            // Limpiar is-invalid al escribir
            ['areaUsuario', 'areaPass'].forEach(function (id) {
                document.getElementById(id).addEventListener('input', function () {
                    this.classList.remove('is-invalid');
                    document.getElementById('areaAlert').style.display = 'none';
                });
            });
        });

        // ── Login Admin — AJAX ──
        document.addEventListener('DOMContentLoaded', function () {

            const form        = document.getElementById('formAdminLogin');
            const loginAlert  = document.getElementById('loginAlert');
            const btnTxt      = document.getElementById('btnLoginTxt');
            const btnSpinner  = document.getElementById('btnLoginSpinner');
            const btnLogin    = document.getElementById('btnLogin');
            const eyeBtn      = document.getElementById('adminTogglePass');
            const claveInput  = document.getElementById('loginClave');
            const usuarioInput= document.getElementById('loginUsuario');

            // Limpiar formulario cada vez que se abre el modal
            document.getElementById('modalAdminLogin').addEventListener('show.bs.modal', function () {
                loginAlert.style.display = 'none';
                loginAlert.className = 'sp-login-alert';
                form.reset();
                usuarioInput.classList.remove('is-invalid');
                claveInput.classList.remove('is-invalid');
            });

            // Mostrar / ocultar clave — modal admin
            if (eyeBtn) {
                eyeBtn.addEventListener('click', function () {
                    var icon = document.getElementById('iconEye');
                    if (claveInput.type === 'password') {
                        claveInput.type = 'text';
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        claveInput.type = 'password';
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                });
            }

            // Mostrar / ocultar clave — modal área
            var areaEyeBtn = document.getElementById('areaTogglePass');
            if (areaEyeBtn) {
                areaEyeBtn.addEventListener('click', function () {
                    var areaPass = document.getElementById('areaPass');
                    var areaIcon = document.getElementById('areaEyeIcon');
                    if (areaPass.type === 'password') {
                        areaPass.type = 'text';
                        areaIcon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        areaPass.type = 'password';
                        areaIcon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                });
            }

            function mostrarAlerta(msg, tipo) {
                loginAlert.innerHTML = '<i class="fas ' + (tipo === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle') + '"></i> ' + msg;
                loginAlert.className = 'sp-login-alert ' + tipo;
                loginAlert.style.display = 'flex';
            }

            function setLoading(loading) {
                btnLogin.disabled        = loading;
                btnTxt.style.display     = loading ? 'none'   : 'inline';
                btnSpinner.style.display = loading ? 'inline' : 'none';
            }

            // Submit del formulario
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var usuario = usuarioInput.value.trim();
                var clave   = claveInput.value.trim();

                if (!usuario || !clave) {
                    mostrarAlerta('Completa usuario y clave', 'error');
                    return;
                }

                setLoading(true);
                loginAlert.style.display = 'none';

                fetch('login_check.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'usuario=' + encodeURIComponent(usuario) + '&clave=' + encodeURIComponent(clave)
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    setLoading(false);
                    if (data.ok) {
                        mostrarAlerta('Acceso correcto. Redirigiendo...', 'success');
                        setTimeout(function() {
                            window.location.href = data.redirect || 'admin.php';
                        }, 900);
                    } else {
                        mostrarAlerta(data.msg || 'Usuario o clave incorrectos', 'error');
                        usuarioInput.classList.add('is-invalid');
                        claveInput.classList.add('is-invalid');
                    }
                })
                .catch(function() {
                    setLoading(false);
                    mostrarAlerta('Error de conexión. Intenta nuevamente.', 'error');
                });
            });

            // Quitar is-invalid al escribir
            [usuarioInput, claveInput].forEach(function(el) {
                el.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    loginAlert.style.display = 'none';
                });
            });
        });

        // ── Inicializar carrusel explícitamente ──
        document.addEventListener('DOMContentLoaded', function () {
            var carouselEl = document.getElementById('heroCarousel');
            if (carouselEl) {
                var carousel = new bootstrap.Carousel(carouselEl, {
                    interval: 5000,
                    ride: 'carousel',
                    pause: 'hover',
                    wrap: true
                });
            }
        });

        // ── Smooth scroll (excluye data-bs-toggle para no interferir con modal) ──
        document.querySelectorAll('a[href^="#"]:not([data-bs-toggle])').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // ── Sombra del header al hacer scroll ──
        window.addEventListener('scroll', function() {
            var header = document.querySelector('.sp-header');
            if (window.scrollY > 60) {
                header.style.boxShadow = '0 4px 20px rgba(0,0,0,.15)';
            } else {
                header.style.boxShadow = '0 2px 10px rgba(0,0,0,.08)';
            }
        });
    </script>

</body>
</html>
