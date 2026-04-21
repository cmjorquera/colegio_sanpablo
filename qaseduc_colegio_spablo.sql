-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 20-04-2026 a las 15:34:54
-- Versión del servidor: 10.6.19-MariaDB
-- Versión de PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `qaseduc_colegio_spablo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_noticia`
--

CREATE TABLE `categoria_noticia` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `color` varchar(20) DEFAULT '#D9EAFE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria_noticia`
--

INSERT INTO `categoria_noticia` (`id_categoria`, `nombre`, `color`) VALUES
(1, 'DEPORTE', '#D9EAFE'),
(2, 'IDIOMAS', '#D9EAFE'),
(3, 'ORIENTACIÓN', '#D9EAFE'),
(4, 'MEDIO AMBIENTE', '#D9EAFE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institucion`
--

CREATE TABLE `institucion` (
  `id_institucion` int(11) NOT NULL,
  `id_tipo_institucion` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `nombre_corto` varchar(100) DEFAULT NULL,
  `dominio` varchar(150) DEFAULT NULL,
  `logo_header` varchar(255) DEFAULT NULL,
  `logo_footer` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `color_primario` varchar(20) DEFAULT '#2563EB',
  `color_secundario` varchar(20) DEFAULT '#E9A629',
  `color_terciario` varchar(20) DEFAULT '#222222',
  `color_cuaternario` varchar(20) DEFAULT '#F8F8F8',
  `texto_boton_principal` varchar(100) DEFAULT NULL,
  `url_boton_principal` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `institucion`
--

INSERT INTO `institucion` (`id_institucion`, `id_tipo_institucion`, `nombre`, `nombre_corto`, `dominio`, `logo_header`, `logo_footer`, `favicon`, `direccion`, `telefono`, `email`, `facebook`, `instagram`, `youtube`, `linkedin`, `color_primario`, `color_secundario`, `color_terciario`, `color_cuaternario`, `texto_boton_principal`, `url_boton_principal`, `estado`, `fecha_creacion`) VALUES
(1, 1, 'Colegio San Pabloxxxxx', 'San Pablo', 'www.sanpablo.edu.uy', 'uploads/institucion/logo-sin-fondo-20260419221245-d5a03a.png', 'uploads/institucion/logo_footer.png', 'uploads/institucion/favicon.ico', 'Venancio Benavidez 3612', '+598 2337 3737', 'info@sanpablo.edu.uy', '#', '#', NULL, NULL, '#2563EB', '#E9A629', '#222222', '#F8F8F8', 'Matrícula', '#', 'activo', '2026-04-19 19:39:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menus`
--

CREATE TABLE `menus` (
  `id_menu` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `id_padre` int(11) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` date DEFAULT NULL,
  `hora_creacion` time DEFAULT NULL,
  `ip_creacion` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `menus`
--

INSERT INTO `menus` (`id_menu`, `nombre`, `url`, `icono`, `id_padre`, `orden`, `estado`, `fecha_creacion`, `hora_creacion`, `ip_creacion`) VALUES
(1, 'Inicio', '', '', NULL, 1, 1, '2026-04-14', '18:43:29', '127.0.0.1'),
(2, 'Institucional', '', '', NULL, 2, 1, '2026-04-14', '18:43:29', '127.0.0.1'),
(3, 'Maternal', '', '', NULL, 3, 1, '2026-04-14', '18:43:29', '127.0.0.1'),
(4, 'Inicial', '', '', NULL, 4, 1, '2026-04-14', '18:43:29', '127.0.0.1'),
(5, 'Primaria', '', '', NULL, 5, 1, '2026-04-14', '18:43:29', '127.0.0.1'),
(6, '3er Ciclo EBI', '', '', NULL, 6, 1, '2026-04-14', '18:43:29', '127.0.0.1'),
(7, 'Bachillerato', '', '', NULL, 7, 1, '2026-04-14', '18:43:29', '127.0.0.1'),
(8, 'Libre  Asistido', '', '', NULL, 8, 1, '2026-04-14', '18:43:29', '127.0.0.1'),
(9, 'Confesionalidad', '', '', NULL, 9, 1, '2026-04-14', '18:43:29', '127.0.0.1'),
(10, 'Biblioteca', '', '', NULL, 10, 1, '2026-04-14', '18:43:29', '127.0.0.1'),
(11, 'Mi San Pablo', '', '', NULL, 11, 1, '2026-04-14', '18:43:29', '127.0.0.1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles`
--

CREATE TABLE `perfiles` (
  `id_perfil` int(11) NOT NULL,
  `nombre_perfil` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seccion`
--

CREATE TABLE `seccion` (
  `id_seccion` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `nombre_interno` varchar(100) NOT NULL,
  `titulo_admin` varchar(150) NOT NULL,
  `observacion` text DEFAULT NULL,
  `tipo_seccion` varchar(50) NOT NULL,
  `variante` varchar(100) DEFAULT NULL,
  `visible` enum('si','no') NOT NULL DEFAULT 'si',
  `estado` enum('activo','inactivo','borrador') DEFAULT 'activo',
  `editable` enum('si','no') DEFAULT 'si',
  `usa_config` enum('si','no') DEFAULT 'si',
  `usa_items` enum('si','no') DEFAULT 'no',
  `archivo_componente` varchar(150) DEFAULT NULL,
  `icono_admin` varchar(100) DEFAULT NULL,
  `clase_css` varchar(255) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seccion`
--

INSERT INTO `seccion` (`id_seccion`, `id_institucion`, `nombre_interno`, `titulo_admin`, `observacion`, `tipo_seccion`, `variante`, `visible`, `estado`, `editable`, `usa_config`, `usa_items`, `archivo_componente`, `icono_admin`, `clase_css`, `orden`, `fecha_creacion`) VALUES
(1, 1, 'topbar', 'Topbar superior', 'Franja superior con direccion, telefono, correo y redes institucionales.', 'topbar', 'clasico', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 1, '2026-04-19 19:39:21'),
(3, 1, 'hero_principal', 'Carrusel principal', 'Carrusel destacado del home con slides, imagenes y botones principales.', 'carousel', 'texto_izquierda', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 4, '2026-04-19 19:39:21'),
(4, 1, 'noticias_home', 'Noticias home', 'Bloque de noticias destacadas del home con categoria, imagen y fecha.', 'news', 'cards_4', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 5, '2026-04-19 19:39:21'),
(5, 1, 'faq_home', 'Preguntas frecuentes', 'Contenedor de preguntas frecuentes con acordeon e imagen lateral.', 'faq', 'imagen_lateral', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 6, '2026-04-19 19:39:21'),
(6, 1, 'about_home', 'Sobre nosotros', 'Bloque institucional de presentacion con imagen principal, video y descripcion.', 'content', 'imagen_texto', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 7, '2026-04-19 19:39:21'),
(7, 1, 'footer_principal', 'Footer principal', 'Este es el contenedor del footer. Aqui se muestran logo, descripcion institucional, enlaces rapidos, contacto, redes sociales y datos principales del sitio.', 'footer', 'institucional', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 8, '2026-04-19 20:56:57'),
(11, 1, 'header_principal', 'Header principal', 'Bloque visual completo del encabezado. Incluye logo, identidad institucional, navegacion horizontal basada en menus y sub_menus, y boton principal.', 'header', 'branding', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 2, '2026-04-19 21:46:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seccion_config`
--

CREATE TABLE `seccion_config` (
  `id_config` int(11) NOT NULL,
  `id_seccion` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seccion_config`
--

INSERT INTO `seccion_config` (`id_config`, `id_seccion`, `clave`, `valor`) VALUES
(5, 4, 'titulo_bloque', 'Últimas Noticias'),
(6, 4, 'texto_boton', 'Ver todas las noticias'),
(7, 4, 'url_boton', '#'),
(8, 4, 'cantidad_items', '4'),
(12, 6, 'subtitulo_bloque', 'SOBRE NOSOTROS'),
(13, 6, 'titulo_bloque', 'Aprende Nuevas Habilidades para Crecer'),
(14, 6, 'descripcion_bloque', 'Brindamos una educación integral con acompañamiento cercano, formación en valores y experiencias de aprendizaje.'),
(15, 6, 'imagen_principal', 'assets/images/about/about-two-image1.png'),
(16, 6, 'video_url', 'https://www.youtube.com/watch?v=oKu4GAeGjp8'),
(20, 5, 'imagen_lateral', 'assets/images/faq/faq-image1.png'),
(21, 5, 'subtitulo_bloque', 'PREGUNTAS FRECUENTES'),
(22, 5, 'titulo_bloque', 'Siempre nos aseguramos de que el mejor curso esté listo para aprender.'),
(23, 3, 'alineacion_texto', 'izquierda'),
(24, 3, 'mostrar_flechas', 'si'),
(25, 3, 'mostrar_indicadores', 'si'),
(26, 3, 'overlay', 'oscuro'),
(63, 7, 'descripcion_footer', 'Colegio San Pablo acompaña a su comunidad con una propuesta educativa integral, cercana e inspirada en una formación académica, humana y valórica.'),
(64, 7, 'mostrar_contacto', 'si'),
(65, 7, 'mostrar_menu_rapido', 'si'),
(66, 7, 'mostrar_niveles', 'si'),
(67, 7, 'mostrar_redes', 'si'),
(68, 7, 'titulo_contacto', 'Contacto y Sedes'),
(69, 7, 'titulo_footer', 'Colegio San Pablo'),
(70, 7, 'titulo_menu_rapido', 'Menú Rápido'),
(71, 7, 'titulo_niveles', 'Niveles'),
(78, 1, 'usar_gradiente_colores', 'si'),
(79, 1, 'max_redes', '4'),
(104, 1, 'texto_boton_ingresar', 'Ingresar'),
(105, 1, 'mostrar_direccion', 'si'),
(106, 1, 'mostrar_telefono', 'si'),
(107, 1, 'mostrar_email', 'si'),
(108, 1, 'mostrar_redes', 'si'),
(109, 1, 'mostrar_boton_ingresar', 'si');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seccion_item`
--

CREATE TABLE `seccion_item` (
  `id_item` int(11) NOT NULL,
  `id_seccion` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `etiqueta` varchar(150) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `titulo_linea_1` varchar(255) DEFAULT NULL,
  `titulo_linea_2` varchar(255) DEFAULT NULL,
  `titulo_linea_3` varchar(255) DEFAULT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `imagen_mobile` varchar(255) DEFAULT NULL,
  `boton_1_texto` varchar(150) DEFAULT NULL,
  `boton_1_url` varchar(255) DEFAULT NULL,
  `boton_2_texto` varchar(150) DEFAULT NULL,
  `boton_2_url` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `fecha_publicacion` date DEFAULT NULL,
  `visible` enum('si','no') NOT NULL DEFAULT 'si',
  `orden` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seccion_item`
--

INSERT INTO `seccion_item` (`id_item`, `id_seccion`, `id_categoria`, `etiqueta`, `icono`, `titulo`, `titulo_linea_1`, `titulo_linea_2`, `titulo_linea_3`, `subtitulo`, `descripcion`, `imagen`, `imagen_mobile`, `boton_1_texto`, `boton_1_url`, `boton_2_texto`, `boton_2_url`, `url`, `fecha_publicacion`, `visible`, `orden`, `fecha_creacion`) VALUES
(1, 3, NULL, 'Comunidad Educativa', NULL, NULL, 'Disfrutamos', 'Creciendo', 'Contigo', NULL, NULL, 'assets/images/portada_3.jpg', NULL, 'Ver galería', '#galeria', 'Nuestro equipo', '#equipo', NULL, NULL, 'si', 1, '2026-04-19 19:39:21'),
(2, 3, NULL, 'Nuestra Misión', NULL, '', 'Caminamos', 'Juntos', 'Hacia el Futuro', '', '', 'assets/images/portada_1.jpg', 'uploads/secciones/hero_principal/lupe-y-papa-20260420084202-2b38c7.jpg', 'Ver novedades', '#noticias', 'Acceso Mi San Pablo', '#portal', NULL, NULL, 'si', 2, '2026-04-19 19:39:21'),
(3, 4, NULL, '', NULL, 'Rugby del Prado', '', '', '', '', 'Nuestros alumnos participaron en el torneo intercolegial de rugby con excelentes resultados.', 'assets/images/frontis_01.jpg', NULL, 'Leer más', '#', '', '', NULL, '2025-11-01', 'si', 1, '2026-04-19 19:39:21'),
(4, 4, 2, NULL, NULL, 'Certificaciones Inglés y Portugués', NULL, NULL, NULL, NULL, 'Alumnos de bachillerato rindieron y aprobaron certificaciones internacionales de idiomas.', 'assets/images/frontis_02.jpg', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-01', 'si', 2, '2026-04-19 19:39:21'),
(6, 4, 2, NULL, NULL, 'Certificaciones Inglés y Portugués', NULL, NULL, NULL, NULL, 'Alumnos de bachillerato rindieron y aprobaron certificaciones internacionales de idiomas.', 'assets/images/frontis_02.jpg', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-01', 'si', 2, '2026-04-19 19:39:21'),
(7, 5, NULL, NULL, NULL, '¿Cuánto dura el proceso de admisión?', NULL, NULL, NULL, NULL, 'Nuestro equipo acompaña a las familias con información clara, apoyo cercano y un proceso educativo pensado para el desarrollo integral de cada estudiante.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'si', 1, '2026-04-19 19:39:21'),
(8, 5, NULL, NULL, NULL, '¿Qué incluye la propuesta educativa?', NULL, NULL, NULL, NULL, 'Incluye acompañamiento cercano, formación en valores y una propuesta académica integral para cada etapa.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'si', 2, '2026-04-19 19:39:21'),
(9, 3, NULL, '', NULL, '', 'Guadalupe', 'jorquera', 'qweqwe', '', 'contenedor de prueba', 'uploads/secciones/hero_principal/img-6939-20260420150930-ee68cc.jpg', 'uploads/secciones/hero_principal/imagen-caballo-1-20260420085409-b549ea.png', '', '', '', '', NULL, NULL, 'si', 3, '2026-04-19 19:44:28'),
(10, 4, NULL, '', NULL, 'Ejemplo', '', '', '', '', 'contenedor de ejemplo', 'uploads/noticias/portada-3-20260419221051-6bab8d.jpg', NULL, 'Leer más', '#', '', '', NULL, '2026-04-27', 'si', 4, '2026-04-19 22:10:51'),
(11, 4, NULL, '', NULL, 'otro ejemplo', '', '', '', '', 'wqeqweqwe', NULL, NULL, 'Leer más', '#', '', '', NULL, '2026-04-24', 'si', 5, '2026-04-19 22:11:28'),
(12, 7, NULL, '', 'bi bi-building', 'eeee', '', '', '', 'probando', 'eeee', NULL, NULL, '', '', '', '', NULL, NULL, 'si', 1, '2026-04-20 08:47:28'),
(13, 7, NULL, '', 'bi bi-building', 'Administracion', '', '', '', 'hola', 'Venancio Benavidez 3612  xxxx', NULL, NULL, '', '', '', '', NULL, NULL, 'si', 1, '2026-04-20 08:59:35'),
(14, 7, NULL, 'inicial', 'bi bi-house-door-fill', 'Inicial', NULL, NULL, NULL, 'Tel. 2336 6000', 'Joaquin Suarez 3596', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'si', 2, '2026-04-20 08:59:35'),
(15, 7, NULL, 'preuniversitario', 'bi bi-mortarboard-fill', 'Preuniversitario', NULL, NULL, NULL, 'Tel. 2202 0000', 'Av. Millan 3375', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'si', 3, '2026-04-20 08:59:35'),
(16, 1, NULL, 'red_social', 'fab fa-instagram', 'Instagram', NULL, NULL, NULL, NULL, 'https://instagram.com/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'si', 1, '2026-04-20 10:04:26'),
(17, 1, NULL, 'red_social', 'fab fa-facebook me-1', 'Facebook', NULL, NULL, NULL, NULL, 'https://facebook.com/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'si', 2, '2026-04-20 10:04:26'),
(18, 1, NULL, 'red_social', 'fab fa-youtube', 'YouTube', NULL, NULL, NULL, NULL, 'https://youtube.com/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'si', 3, '2026-04-20 10:04:26'),
(19, 1, NULL, 'red_social', 'fa fa-linkedin', 'LinkedIn', NULL, NULL, NULL, NULL, 'https://linkedin.com/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'si', 4, '2026-04-20 10:04:26'),
(20, 3, NULL, 'eticketa ej', NULL, '', 'titulo 1 ejemplo', 'titulo 2 ejemplo', 'titulo 3 ejemplo', '', 'descripcion ejemplo', 'uploads/secciones/hero_principal/portada-2-20260420150941-20969a.jpg', 'uploads/secciones/hero_principal/lupe-04-20260420113602-ee9305.jpg', 'botón 1', 'botón url', 'botón  2', 'botón url', NULL, NULL, 'si', 4, '2026-04-20 11:30:02'),
(21, 3, NULL, '', NULL, '', '', '', '', '', '', 'uploads/secciones/hero_principal/img-6939-20260420115220-a8df9b.jpg', NULL, '', '', '', '', NULL, NULL, 'si', 5, '2026-04-20 11:52:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sub_menus`
--

CREATE TABLE `sub_menus` (
  `id_sub_menu` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` date DEFAULT NULL,
  `hora_creacion` time DEFAULT NULL,
  `ip_creacion` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sub_menus`
--

INSERT INTO `sub_menus` (`id_sub_menu`, `id_menu`, `nombre`, `url`, `icono`, `orden`, `estado`, `fecha_creacion`, `hora_creacion`, `ip_creacion`) VALUES
(1, 2, 'PRESENTACIÓN', NULL, NULL, 1, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(2, 2, 'HISTORIA', NULL, NULL, 2, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(3, 2, 'LOGOTIPO Y LEMA', NULL, NULL, 3, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(4, 2, 'VISIÓN Y MISIÓN', NULL, NULL, 4, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(5, 2, 'PRINCIPIOS DE IDENTIDAD', NULL, NULL, 5, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(6, 2, 'PROPUESTA PEDAGÓGICA', NULL, NULL, 6, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(7, 2, 'PERFIL DEL ALUMNO', NULL, NULL, 7, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(8, 2, 'ESTRUCTURA FÍSICA', NULL, NULL, 8, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(9, 2, 'ADMINISTRACIÓN', NULL, NULL, 9, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(10, 3, 'PRESENTACIÓN', NULL, NULL, 1, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(11, 3, 'GRUPOS Y HORARIOS', NULL, NULL, 2, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(12, 3, 'PROPUESTA BILINGÜE', NULL, NULL, 3, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(13, 3, 'ACTIVIDADES', NULL, NULL, 4, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(14, 4, 'PRESENTACIÓN', NULL, NULL, 1, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(15, 4, 'PROPUESTA CURRICULAR', NULL, NULL, 2, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(16, 4, 'PROPUESTA BILINGÜE', NULL, NULL, 3, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(17, 4, 'ACTIVIDADES', NULL, NULL, 4, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(18, 5, 'PRESENTACIÓN', NULL, NULL, 1, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(19, 5, 'PROPUESTA CURRICULAR', NULL, NULL, 2, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(20, 5, 'PROPUESTA BILINGÜE', NULL, NULL, 3, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(21, 5, 'ACTIVIDADES EXTRACURRICULARES', NULL, NULL, 4, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(22, 5, 'BIBLIOTECA', NULL, NULL, 5, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(23, 6, 'PRESENTACIÓN', NULL, NULL, 1, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(24, 6, 'PROPUESTA CURRICULAR', NULL, NULL, 2, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(25, 6, 'PROPUESTA BILINGÜE', NULL, NULL, 3, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(26, 6, 'ACTIVIDADES EXTRACURRICULARES', NULL, NULL, 4, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(27, 6, 'SERVICIOS EDUCATIVOS', NULL, NULL, 5, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(28, 7, 'PRESENTACIÓN', NULL, NULL, 1, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(29, 7, 'PROPUESTA CURRICULAR', NULL, NULL, 2, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(30, 7, 'PROPUESTA BILINGÜE', NULL, NULL, 3, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(31, 7, 'ACTIVIDADES EXTRACURRICULARES', NULL, NULL, 4, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(32, 7, 'SERVICIOS EDUCATIVOS', NULL, NULL, 5, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(33, 8, 'PRESENTACIÓN', NULL, NULL, 1, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(34, 8, 'INSCRIPCIONES', NULL, NULL, 2, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(35, 9, 'IDENTIDAD', NULL, NULL, 1, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(36, 9, 'VISIÓN', NULL, NULL, 2, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(37, 9, 'LA CONFESIONALIDAD EN LA PRÁCTICA', NULL, NULL, 3, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(38, 9, 'CAPELLANÍA', NULL, NULL, 4, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(39, 9, 'EDUCACIÓN CRISTIANA', NULL, NULL, 5, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(40, 9, 'IGLESIA LUTERANA', NULL, NULL, 6, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(41, 10, 'PRESENTACIÓN', NULL, NULL, 1, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(42, 10, 'LA FUNCIÓN DE LA BIBLIOTECA', NULL, NULL, 2, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(43, 10, 'OBJETIVOS', NULL, NULL, 3, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(44, 11, 'ÁREA ALUMNOS', NULL, NULL, 1, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(45, 11, 'ÁREA PADRES', NULL, NULL, 2, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(46, 11, 'ÁREA FUNCIONARIOS', NULL, NULL, 3, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(47, 11, 'ÁREA DOCENTES', NULL, NULL, 4, 1, '2026-04-14', '18:50:51', '127.0.0.1'),
(48, 11, 'WEBMAIL', NULL, NULL, 5, 1, '2026-04-14', '18:50:51', '127.0.0.1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_institucion`
--

CREATE TABLE `tipo_institucion` (
  `id_tipo_institucion` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_institucion`
--

INSERT INTO `tipo_institucion` (`id_tipo_institucion`, `nombre`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(1, 'Colegio', 'Institución educacional', 'activo', '2026-04-19 19:39:20'),
(2, 'Hospital', 'Institución de salud', 'activo', '2026-04-19 19:39:20'),
(3, 'Municipalidad', 'Institución pública', 'activo', '2026-04-19 19:39:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `rol` enum('super_admin','admin_institucion','editor') NOT NULL DEFAULT 'admin_institucion',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `apellido` varchar(120) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `usuario` varchar(80) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  `intento_fallido` int(11) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `usuario`, `clave`, `foto`, `estado`, `intento_fallido`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'marcos', NULL, 'marcos@admin.cl', 'marcos@admin.cl', 'admin123!', NULL, 'activo', 0, '2026-04-15 00:26:09', '2026-04-15 00:31:48'),
(2, 'Cristin ', 'Jorquera', 'cm.jorquerag@gmail.com', 'cm.jorquerag@gmail.com', 'Ingeniero186#', NULL, 'activo', 0, '2026-04-15 00:27:15', '2026-04-15 00:27:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_perfil`
--

CREATE TABLE `usuario_perfil` (
  `id_usuario` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria_noticia`
--
ALTER TABLE `categoria_noticia`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `institucion`
--
ALTER TABLE `institucion`
  ADD PRIMARY KEY (`id_institucion`),
  ADD KEY `fk_institucion_tipo` (`id_tipo_institucion`);

--
-- Indices de la tabla `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indices de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  ADD PRIMARY KEY (`id_perfil`),
  ADD UNIQUE KEY `nombre_perfil` (`nombre_perfil`);

--
-- Indices de la tabla `seccion`
--
ALTER TABLE `seccion`
  ADD PRIMARY KEY (`id_seccion`),
  ADD UNIQUE KEY `uq_seccion_institucion_nombre` (`id_institucion`,`nombre_interno`),
  ADD KEY `fk_seccion_institucion` (`id_institucion`);

--
-- Indices de la tabla `seccion_config`
--
ALTER TABLE `seccion_config`
  ADD PRIMARY KEY (`id_config`),
  ADD KEY `fk_seccion_config_seccion` (`id_seccion`);

--
-- Indices de la tabla `seccion_item`
--
ALTER TABLE `seccion_item`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `fk_seccion_item_seccion` (`id_seccion`),
  ADD KEY `fk_seccion_item_categoria` (`id_categoria`);

--
-- Indices de la tabla `sub_menus`
--
ALTER TABLE `sub_menus`
  ADD PRIMARY KEY (`id_sub_menu`),
  ADD UNIQUE KEY `uq_menu_submenu` (`id_menu`,`nombre`);

--
-- Indices de la tabla `tipo_institucion`
--
ALTER TABLE `tipo_institucion`
  ADD PRIMARY KEY (`id_tipo_institucion`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_usuario_institucion` (`id_institucion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `usuario_perfil`
--
ALTER TABLE `usuario_perfil`
  ADD PRIMARY KEY (`id_usuario`,`id_perfil`),
  ADD KEY `fk_usuario_perfil_perfil` (`id_perfil`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria_noticia`
--
ALTER TABLE `categoria_noticia`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `institucion`
--
ALTER TABLE `institucion`
  MODIFY `id_institucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `menus`
--
ALTER TABLE `menus`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  MODIFY `id_perfil` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `seccion`
--
ALTER TABLE `seccion`
  MODIFY `id_seccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `seccion_config`
--
ALTER TABLE `seccion_config`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT de la tabla `seccion_item`
--
ALTER TABLE `seccion_item`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `sub_menus`
--
ALTER TABLE `sub_menus`
  MODIFY `id_sub_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `tipo_institucion`
--
ALTER TABLE `tipo_institucion`
  MODIFY `id_tipo_institucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `institucion`
--
ALTER TABLE `institucion`
  ADD CONSTRAINT `fk_institucion_tipo` FOREIGN KEY (`id_tipo_institucion`) REFERENCES `tipo_institucion` (`id_tipo_institucion`);

--
-- Filtros para la tabla `seccion`
--
ALTER TABLE `seccion`
  ADD CONSTRAINT `fk_seccion_institucion` FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`);

--
-- Filtros para la tabla `seccion_config`
--
ALTER TABLE `seccion_config`
  ADD CONSTRAINT `fk_seccion_config_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `seccion` (`id_seccion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seccion_item`
--
ALTER TABLE `seccion_item`
  ADD CONSTRAINT `fk_seccion_item_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_noticia` (`id_categoria`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_seccion_item_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `seccion` (`id_seccion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sub_menus`
--
ALTER TABLE `sub_menus`
  ADD CONSTRAINT `fk_submenu_menu` FOREIGN KEY (`id_menu`) REFERENCES `menus` (`id_menu`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_institucion` FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`);

--
-- Filtros para la tabla `usuario_perfil`
--
ALTER TABLE `usuario_perfil`
  ADD CONSTRAINT `fk_usuario_perfil_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_perfil_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
