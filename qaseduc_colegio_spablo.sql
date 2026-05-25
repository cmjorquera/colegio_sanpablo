-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 24-05-2026 a las 15:17:57
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
-- Estructura de tabla para la tabla `auditoria_log`
--

CREATE TABLE `auditoria_log` (
  `id_log` bigint(20) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_institucion` int(11) DEFAULT NULL,
  `modulo` varchar(80) NOT NULL,
  `tabla_afectada` varchar(80) NOT NULL,
  `id_registro` varchar(80) DEFAULT NULL,
  `accion` enum('crear','editar','eliminar','activar','desactivar','login','logout','importar') NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `datos_antes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_antes`)),
  `datos_despues` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_despues`)),
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `auditoria_log`
--

INSERT INTO `auditoria_log` (`id_log`, `id_usuario`, `id_institucion`, `modulo`, `tabla_afectada`, `id_registro`, `accion`, `descripcion`, `datos_antes`, `datos_despues`, `ip_usuario`, `user_agent`, `fecha_hora`) VALUES
(1, 1, 1, 'Contenedores del sitio', 'seccion', '1', '', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":1,\"id_institucion\":1,\"nombre_interno\":\"topbar\",\"titulo_admin\":\"Topbar superior\",\"observacion\":\"Franja superior con direccion, telefono, correo y redes institucionales.\",\"tipo_seccion\":\"topbar\",\"variante\":\"clasico\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":1,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_seccion\":1,\"id_institucion\":1,\"nombre_interno\":\"topbar\",\"titulo_admin\":\"Topbar superior\",\"observacion\":\"Franja superior con direccion, telefono, correo y redes institucionales.\",\"tipo_seccion\":\"topbar\",\"variante\":\"clasico\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":1,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:52:45'),
(2, 1, 1, 'Contenedores del sitio', 'seccion', '11', '', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":11,\"id_institucion\":1,\"nombre_interno\":\"header_principal\",\"titulo_admin\":\"Header principal\",\"observacion\":\"Bloque visual completo del encabezado. Incluye logo, identidad institucional, navegacion horizontal basada en menus y sub_menus, y boton principal.\",\"tipo_seccion\":\"header\",\"variante\":\"branding\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":2,\"fecha_creacion\":\"2026-04-19 21:46:37\"}', '{\"id_seccion\":11,\"id_institucion\":1,\"nombre_interno\":\"header_principal\",\"titulo_admin\":\"Header principal\",\"observacion\":\"Bloque visual completo del encabezado. Incluye logo, identidad institucional, navegacion horizontal basada en menus y sub_menus, y boton principal.\",\"tipo_seccion\":\"header\",\"variante\":\"branding\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":2,\"fecha_creacion\":\"2026-04-19 21:46:37\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:52:46'),
(3, 1, 1, 'Contenedores del sitio', 'seccion', '3', '', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":3,\"id_institucion\":1,\"nombre_interno\":\"hero_principal\",\"titulo_admin\":\"Carrusel principal\",\"observacion\":\"Carrusel destacado del home con slides, imagenes y botones principales.\",\"tipo_seccion\":\"carousel\",\"variante\":\"texto_izquierda\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":3,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_seccion\":3,\"id_institucion\":1,\"nombre_interno\":\"hero_principal\",\"titulo_admin\":\"Carrusel principal\",\"observacion\":\"Carrusel destacado del home con slides, imagenes y botones principales.\",\"tipo_seccion\":\"carousel\",\"variante\":\"texto_izquierda\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":3,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:52:46'),
(4, 1, 1, 'Contenedores del sitio', 'seccion', '20', '', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":20,\"id_institucion\":1,\"nombre_interno\":\"calendario_eventos_home\",\"titulo_admin\":\"Calendario de eventos\",\"observacion\":\"Contenedor del home que muestra calendario institucional y próximos eventos.\",\"tipo_seccion\":\"events\",\"variante\":\"calendario_lista\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":5,\"fecha_creacion\":\"2026-05-22 12:44:54\"}', '{\"id_seccion\":20,\"id_institucion\":1,\"nombre_interno\":\"calendario_eventos_home\",\"titulo_admin\":\"Calendario de eventos\",\"observacion\":\"Contenedor del home que muestra calendario institucional y próximos eventos.\",\"tipo_seccion\":\"events\",\"variante\":\"calendario_lista\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":5,\"fecha_creacion\":\"2026-05-22 12:44:54\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:52:47'),
(5, 1, 1, 'Contenedores del sitio', 'seccion', '4', '', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":4,\"id_institucion\":1,\"nombre_interno\":\"noticias_home\",\"titulo_admin\":\"Noticias home\",\"observacion\":\"Bloque de noticias destacadas del home con categoria, imagen y fecha.\",\"tipo_seccion\":\"news\",\"variante\":\"cards_4\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":4,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_seccion\":4,\"id_institucion\":1,\"nombre_interno\":\"noticias_home\",\"titulo_admin\":\"Noticias home\",\"observacion\":\"Bloque de noticias destacadas del home con categoria, imagen y fecha.\",\"tipo_seccion\":\"news\",\"variante\":\"cards_4\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":4,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:52:48'),
(6, 1, 1, 'Contenedores del sitio', 'seccion', '5', '', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":5,\"id_institucion\":1,\"nombre_interno\":\"faq_home\",\"titulo_admin\":\"Preguntas frecuentes\",\"observacion\":\"Contenedor de preguntas frecuentes con acordeon e imagen lateral.\",\"tipo_seccion\":\"faq\",\"variante\":\"imagen_lateral\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":8,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_seccion\":5,\"id_institucion\":1,\"nombre_interno\":\"faq_home\",\"titulo_admin\":\"Preguntas frecuentes\",\"observacion\":\"Contenedor de preguntas frecuentes con acordeon e imagen lateral.\",\"tipo_seccion\":\"faq\",\"variante\":\"imagen_lateral\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":8,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:52:49'),
(7, 1, 1, 'Contenedores del sitio', 'seccion', '22', '', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":22,\"id_institucion\":1,\"nombre_interno\":\"galeria_home\",\"titulo_admin\":\"Galería home\",\"observacion\":\"Contenedor del home con galería visual tipo carrusel basado en template_07.\",\"tipo_seccion\":\"gallery\",\"variante\":\"slider_seven\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":7,\"fecha_creacion\":\"2026-05-22 13:29:19\"}', '{\"id_seccion\":22,\"id_institucion\":1,\"nombre_interno\":\"galeria_home\",\"titulo_admin\":\"Galería home\",\"observacion\":\"Contenedor del home con galería visual tipo carrusel basado en template_07.\",\"tipo_seccion\":\"gallery\",\"variante\":\"slider_seven\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":7,\"fecha_creacion\":\"2026-05-22 13:29:19\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:52:49'),
(8, 1, 1, 'Contenedores del sitio', 'seccion', '21', '', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":21,\"id_institucion\":1,\"nombre_interno\":\"video_destacado_home\",\"titulo_admin\":\"Video destacado\",\"observacion\":\"Contenedor del home con banner de video destacado basado en template_07.\",\"tipo_seccion\":\"video\",\"variante\":\"banner_video\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":6,\"fecha_creacion\":\"2026-05-22 13:29:19\"}', '{\"id_seccion\":21,\"id_institucion\":1,\"nombre_interno\":\"video_destacado_home\",\"titulo_admin\":\"Video destacado\",\"observacion\":\"Contenedor del home con banner de video destacado basado en template_07.\",\"tipo_seccion\":\"video\",\"variante\":\"banner_video\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":6,\"fecha_creacion\":\"2026-05-22 13:29:19\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:52:50'),
(9, 1, 1, 'Contenedores del sitio', 'seccion', '7', '', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":7,\"id_institucion\":1,\"nombre_interno\":\"footer_principal\",\"titulo_admin\":\"Footer principal\",\"observacion\":\"Este es el contenedor del footer. Aqui se muestran logo, descripcion institucional, enlaces rapidos, contacto, redes sociales y datos principales del sitio.\",\"tipo_seccion\":\"footer\",\"variante\":\"institucional\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":10,\"fecha_creacion\":\"2026-04-19 20:56:57\"}', '{\"id_seccion\":7,\"id_institucion\":1,\"nombre_interno\":\"footer_principal\",\"titulo_admin\":\"Footer principal\",\"observacion\":\"Este es el contenedor del footer. Aqui se muestran logo, descripcion institucional, enlaces rapidos, contacto, redes sociales y datos principales del sitio.\",\"tipo_seccion\":\"footer\",\"variante\":\"institucional\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":10,\"fecha_creacion\":\"2026-04-19 20:56:57\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:52:53'),
(10, 1, 1, 'Contenedores del sitio', 'seccion', '6', '', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":6,\"id_institucion\":1,\"nombre_interno\":\"about_home\",\"titulo_admin\":\"Sobre nosotros\",\"observacion\":\"Bloque institucional de presentacion con imagen principal, video y descripcion.\",\"tipo_seccion\":\"content\",\"variante\":\"imagen_texto\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":9,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_seccion\":6,\"id_institucion\":1,\"nombre_interno\":\"about_home\",\"titulo_admin\":\"Sobre nosotros\",\"observacion\":\"Bloque institucional de presentacion con imagen principal, video y descripcion.\",\"tipo_seccion\":\"content\",\"variante\":\"imagen_texto\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":9,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:52:54'),
(11, 1, 1, 'Contenedores del sitio', 'seccion', '1', 'activar', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":1,\"id_institucion\":1,\"nombre_interno\":\"topbar\",\"titulo_admin\":\"Topbar superior\",\"observacion\":\"Franja superior con direccion, telefono, correo y redes institucionales.\",\"tipo_seccion\":\"topbar\",\"variante\":\"clasico\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":1,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_seccion\":1,\"id_institucion\":1,\"nombre_interno\":\"topbar\",\"titulo_admin\":\"Topbar superior\",\"observacion\":\"Franja superior con direccion, telefono, correo y redes institucionales.\",\"tipo_seccion\":\"topbar\",\"variante\":\"clasico\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":1,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:53:02'),
(12, 1, 1, 'Contenedores del sitio', 'seccion', '11', 'activar', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":11,\"id_institucion\":1,\"nombre_interno\":\"header_principal\",\"titulo_admin\":\"Header principal\",\"observacion\":\"Bloque visual completo del encabezado. Incluye logo, identidad institucional, navegacion horizontal basada en menus y sub_menus, y boton principal.\",\"tipo_seccion\":\"header\",\"variante\":\"branding\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":2,\"fecha_creacion\":\"2026-04-19 21:46:37\"}', '{\"id_seccion\":11,\"id_institucion\":1,\"nombre_interno\":\"header_principal\",\"titulo_admin\":\"Header principal\",\"observacion\":\"Bloque visual completo del encabezado. Incluye logo, identidad institucional, navegacion horizontal basada en menus y sub_menus, y boton principal.\",\"tipo_seccion\":\"header\",\"variante\":\"branding\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":2,\"fecha_creacion\":\"2026-04-19 21:46:37\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:53:02'),
(13, 1, 1, 'Contenedores del sitio', 'seccion', '3', 'activar', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":3,\"id_institucion\":1,\"nombre_interno\":\"hero_principal\",\"titulo_admin\":\"Carrusel principal\",\"observacion\":\"Carrusel destacado del home con slides, imagenes y botones principales.\",\"tipo_seccion\":\"carousel\",\"variante\":\"texto_izquierda\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":3,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_seccion\":3,\"id_institucion\":1,\"nombre_interno\":\"hero_principal\",\"titulo_admin\":\"Carrusel principal\",\"observacion\":\"Carrusel destacado del home con slides, imagenes y botones principales.\",\"tipo_seccion\":\"carousel\",\"variante\":\"texto_izquierda\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":3,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:53:03'),
(14, 1, 1, 'Contenedores del sitio', 'seccion', '4', 'activar', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":4,\"id_institucion\":1,\"nombre_interno\":\"noticias_home\",\"titulo_admin\":\"Noticias home\",\"observacion\":\"Bloque de noticias destacadas del home con categoria, imagen y fecha.\",\"tipo_seccion\":\"news\",\"variante\":\"cards_4\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":4,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_seccion\":4,\"id_institucion\":1,\"nombre_interno\":\"noticias_home\",\"titulo_admin\":\"Noticias home\",\"observacion\":\"Bloque de noticias destacadas del home con categoria, imagen y fecha.\",\"tipo_seccion\":\"news\",\"variante\":\"cards_4\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":4,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:53:03'),
(15, 1, 1, 'Contenedores del sitio', 'seccion', '21', 'activar', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":21,\"id_institucion\":1,\"nombre_interno\":\"video_destacado_home\",\"titulo_admin\":\"Video destacado\",\"observacion\":\"Contenedor del home con banner de video destacado basado en template_07.\",\"tipo_seccion\":\"video\",\"variante\":\"banner_video\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":6,\"fecha_creacion\":\"2026-05-22 13:29:19\"}', '{\"id_seccion\":21,\"id_institucion\":1,\"nombre_interno\":\"video_destacado_home\",\"titulo_admin\":\"Video destacado\",\"observacion\":\"Contenedor del home con banner de video destacado basado en template_07.\",\"tipo_seccion\":\"video\",\"variante\":\"banner_video\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":6,\"fecha_creacion\":\"2026-05-22 13:29:19\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:53:06'),
(16, 1, 1, 'Contenedores del sitio', 'seccion', '20', 'activar', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":20,\"id_institucion\":1,\"nombre_interno\":\"calendario_eventos_home\",\"titulo_admin\":\"Calendario de eventos\",\"observacion\":\"Contenedor del home que muestra calendario institucional y próximos eventos.\",\"tipo_seccion\":\"events\",\"variante\":\"calendario_lista\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":5,\"fecha_creacion\":\"2026-05-22 12:44:54\"}', '{\"id_seccion\":20,\"id_institucion\":1,\"nombre_interno\":\"calendario_eventos_home\",\"titulo_admin\":\"Calendario de eventos\",\"observacion\":\"Contenedor del home que muestra calendario institucional y próximos eventos.\",\"tipo_seccion\":\"events\",\"variante\":\"calendario_lista\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":5,\"fecha_creacion\":\"2026-05-22 12:44:54\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:53:07'),
(17, 1, 1, 'Contenedores del sitio', 'seccion', '22', 'activar', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":22,\"id_institucion\":1,\"nombre_interno\":\"galeria_home\",\"titulo_admin\":\"Galería home\",\"observacion\":\"Contenedor del home con galería visual tipo carrusel basado en template_07.\",\"tipo_seccion\":\"gallery\",\"variante\":\"slider_seven\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":7,\"fecha_creacion\":\"2026-05-22 13:29:19\"}', '{\"id_seccion\":22,\"id_institucion\":1,\"nombre_interno\":\"galeria_home\",\"titulo_admin\":\"Galería home\",\"observacion\":\"Contenedor del home con galería visual tipo carrusel basado en template_07.\",\"tipo_seccion\":\"gallery\",\"variante\":\"slider_seven\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":7,\"fecha_creacion\":\"2026-05-22 13:29:19\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:53:08'),
(18, 1, 1, 'Contenedores del sitio', 'seccion', '5', 'activar', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":5,\"id_institucion\":1,\"nombre_interno\":\"faq_home\",\"titulo_admin\":\"Preguntas frecuentes\",\"observacion\":\"Contenedor de preguntas frecuentes con acordeon e imagen lateral.\",\"tipo_seccion\":\"faq\",\"variante\":\"imagen_lateral\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":8,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_seccion\":5,\"id_institucion\":1,\"nombre_interno\":\"faq_home\",\"titulo_admin\":\"Preguntas frecuentes\",\"observacion\":\"Contenedor de preguntas frecuentes con acordeon e imagen lateral.\",\"tipo_seccion\":\"faq\",\"variante\":\"imagen_lateral\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":8,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:53:09'),
(19, 1, 1, 'Contenedores del sitio', 'seccion', '6', 'activar', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":6,\"id_institucion\":1,\"nombre_interno\":\"about_home\",\"titulo_admin\":\"Sobre nosotros\",\"observacion\":\"Bloque institucional de presentacion con imagen principal, video y descripcion.\",\"tipo_seccion\":\"content\",\"variante\":\"imagen_texto\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":9,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_seccion\":6,\"id_institucion\":1,\"nombre_interno\":\"about_home\",\"titulo_admin\":\"Sobre nosotros\",\"observacion\":\"Bloque institucional de presentacion con imagen principal, video y descripcion.\",\"tipo_seccion\":\"content\",\"variante\":\"imagen_texto\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":9,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:53:09'),
(20, 1, 1, 'Contenedores del sitio', 'seccion', '7', 'activar', 'Se cambió la visibilidad de un contenedor', '{\"id_seccion\":7,\"id_institucion\":1,\"nombre_interno\":\"footer_principal\",\"titulo_admin\":\"Footer principal\",\"observacion\":\"Este es el contenedor del footer. Aqui se muestran logo, descripcion institucional, enlaces rapidos, contacto, redes sociales y datos principales del sitio.\",\"tipo_seccion\":\"footer\",\"variante\":\"institucional\",\"visible\":\"no\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":10,\"fecha_creacion\":\"2026-04-19 20:56:57\"}', '{\"id_seccion\":7,\"id_institucion\":1,\"nombre_interno\":\"footer_principal\",\"titulo_admin\":\"Footer principal\",\"observacion\":\"Este es el contenedor del footer. Aqui se muestran logo, descripcion institucional, enlaces rapidos, contacto, redes sociales y datos principales del sitio.\",\"tipo_seccion\":\"footer\",\"variante\":\"institucional\",\"visible\":\"si\",\"estado\":\"activo\",\"editable\":\"si\",\"usa_config\":\"si\",\"usa_items\":\"no\",\"archivo_componente\":null,\"icono_admin\":null,\"clase_css\":null,\"orden\":10,\"fecha_creacion\":\"2026-04-19 20:56:57\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 11:53:10'),
(21, 1, 1, 'Eventos del calendario', 'eventos', NULL, '', 'Se descargó la plantilla de carga masiva de eventos', NULL, '{\"archivo\":\"plantilla_eventos_calendario.xlsx\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:07:19'),
(22, 1, 1, 'Eventos del calendario', 'eventos', NULL, '', 'Se descargó la plantilla de carga masiva de eventos', NULL, '{\"archivo\":\"plantilla_eventos_calendario.xlsx\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:09:44'),
(23, 1, 1, 'Eventos del calendario', 'eventos', '1', 'editar', 'Se modificó un evento', '{\"id_evento\":1,\"titulo\":\"EVENTO DE PRUEBA  JORQUERA\",\"slug\":null,\"descripcion_corta\":\"pronado la cracion de un event\",\"descripcion\":\"pronado la creación de un evento  en el calendario\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Deportivo\",\"color\":\"#c61010\",\"imagen\":\"uploads/eventos/mermaid-diagram-20260522143849-0b9a89.png\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/frontis-09-20260522144511-90ef7d.jpg\",\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"cancelado\",\"creado_en\":\"2026-05-22 14:38:49\",\"actualizado_en\":\"2026-05-24 11:26:54\"}', '{\"id_evento\":1,\"titulo\":\"EVENTO DE PRUEBA  JORQUERA\",\"slug\":null,\"descripcion_corta\":\"pronado la cracion de un event\",\"descripcion\":\"pronado la creación de un evento  en el calendario\",\"fecha_inicio\":\"2026-05-23\",\"fecha_termino\":\"2026-05-23\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Deportivo\",\"color\":\"#c61010\",\"imagen\":\"uploads/eventos/mermaid-diagram-20260522143849-0b9a89.png\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/frontis-09-20260522144511-90ef7d.jpg\",\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"cancelado\",\"creado_en\":\"2026-05-22 14:38:49\",\"actualizado_en\":\"2026-05-24 12:13:36\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:13:36'),
(24, 1, 1, 'Eventos del calendario', 'eventos', '1', '', 'Se cambió la visibilidad de un evento', '{\"id_evento\":1,\"titulo\":\"EVENTO DE PRUEBA  JORQUERA\",\"slug\":null,\"descripcion_corta\":\"pronado la cracion de un event\",\"descripcion\":\"pronado la creación de un evento  en el calendario\",\"fecha_inicio\":\"2026-05-23\",\"fecha_termino\":\"2026-05-23\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Deportivo\",\"color\":\"#c61010\",\"imagen\":\"uploads/eventos/mermaid-diagram-20260522143849-0b9a89.png\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/frontis-09-20260522144511-90ef7d.jpg\",\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"cancelado\",\"creado_en\":\"2026-05-22 14:38:49\",\"actualizado_en\":\"2026-05-24 12:13:36\"}', '{\"id_evento\":1,\"titulo\":\"EVENTO DE PRUEBA  JORQUERA\",\"slug\":null,\"descripcion_corta\":\"pronado la cracion de un event\",\"descripcion\":\"pronado la creación de un evento  en el calendario\",\"fecha_inicio\":\"2026-05-23\",\"fecha_termino\":\"2026-05-23\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Deportivo\",\"color\":\"#c61010\",\"imagen\":\"uploads/eventos/mermaid-diagram-20260522143849-0b9a89.png\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/frontis-09-20260522144511-90ef7d.jpg\",\"destacado\":1,\"visible\":0,\"orden\":0,\"estado\":\"cancelado\",\"creado_en\":\"2026-05-22 14:38:49\",\"actualizado_en\":\"2026-05-24 12:13:41\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:13:41'),
(25, 1, 1, 'Eventos del calendario', 'eventos', '2', 'crear', 'Se creó un evento', NULL, '{\"id_evento\":2,\"titulo\":\"EVENTO DE PRUEBA\",\"slug\":null,\"descripcion_corta\":\"EVENTO DE PRUEBA\",\"descripcion\":\"\",\"fecha_inicio\":\"2026-05-25\",\"fecha_termino\":\"2026-05-25\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Pastoral\",\"color\":\"#fd7e14\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:14:09\",\"actualizado_en\":\"2026-05-24 12:14:09\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:14:09'),
(26, 1, 1, 'Eventos del calendario', 'eventos', '1', 'editar', 'Se modificó un evento', '{\"id_evento\":1,\"titulo\":\"EVENTO DE PRUEBA  JORQUERA\",\"slug\":null,\"descripcion_corta\":\"pronado la cracion de un event\",\"descripcion\":\"pronado la creación de un evento  en el calendario\",\"fecha_inicio\":\"2026-05-23\",\"fecha_termino\":\"2026-05-23\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Deportivo\",\"color\":\"#c61010\",\"imagen\":\"uploads/eventos/mermaid-diagram-20260522143849-0b9a89.png\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/frontis-09-20260522144511-90ef7d.jpg\",\"destacado\":1,\"visible\":0,\"orden\":0,\"estado\":\"cancelado\",\"creado_en\":\"2026-05-22 14:38:49\",\"actualizado_en\":\"2026-05-24 12:13:41\"}', '{\"id_evento\":1,\"titulo\":\"EVENTO DE PRUEBA  JORQUERA\",\"slug\":null,\"descripcion_corta\":\"pronado la cracion de un event\",\"descripcion\":\"pronado la creación de un evento  en el calendario\",\"fecha_inicio\":\"2026-05-23\",\"fecha_termino\":\"2026-05-23\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Deportivo\",\"color\":\"#c61010\",\"imagen\":\"uploads/eventos/mermaid-diagram-20260522143849-0b9a89.png\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/frontis-09-20260522144511-90ef7d.jpg\",\"destacado\":1,\"visible\":0,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-22 14:38:49\",\"actualizado_en\":\"2026-05-24 12:14:18\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:14:18'),
(27, 1, 1, 'Eventos del calendario', 'eventos', '2', 'editar', 'Se modificó un evento', '{\"id_evento\":2,\"titulo\":\"EVENTO DE PRUEBA\",\"slug\":null,\"descripcion_corta\":\"EVENTO DE PRUEBA\",\"descripcion\":\"\",\"fecha_inicio\":\"2026-05-25\",\"fecha_termino\":\"2026-05-25\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Pastoral\",\"color\":\"#fd7e14\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:14:09\",\"actualizado_en\":\"2026-05-24 12:14:09\"}', '{\"id_evento\":2,\"titulo\":\"EVENTO DE PRUEBA\",\"slug\":null,\"descripcion_corta\":\"EVENTO DE PRUEBA\",\"descripcion\":\"\",\"fecha_inicio\":\"2026-05-25\",\"fecha_termino\":\"2026-05-25\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Pastoral\",\"color\":\"#deabab\",\"imagen\":\"uploads/eventos/frontis-02-20260524121516-247829.jpg\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/20251107131913-20260524121516-78c8f9.png\",\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:14:09\",\"actualizado_en\":\"2026-05-24 12:15:16\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:15:16'),
(28, 1, 1, 'Eventos del calendario', 'eventos', '1', 'editar', 'Se modificó un evento', '{\"id_evento\":1,\"titulo\":\"EVENTO DE PRUEBA  JORQUERA\",\"slug\":null,\"descripcion_corta\":\"pronado la cracion de un event\",\"descripcion\":\"pronado la creación de un evento  en el calendario\",\"fecha_inicio\":\"2026-05-23\",\"fecha_termino\":\"2026-05-23\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Deportivo\",\"color\":\"#c61010\",\"imagen\":\"uploads/eventos/mermaid-diagram-20260522143849-0b9a89.png\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/frontis-09-20260522144511-90ef7d.jpg\",\"destacado\":1,\"visible\":0,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-22 14:38:49\",\"actualizado_en\":\"2026-05-24 12:14:18\"}', '{\"id_evento\":1,\"titulo\":\"EVENTO DE PRUEBA  JORQUERA\",\"slug\":null,\"descripcion_corta\":\"pronado la cracion de un event\",\"descripcion\":\"pronado la creación de un evento  en el calendario\",\"fecha_inicio\":\"2026-05-23\",\"fecha_termino\":\"2026-05-23\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Deportivo\",\"color\":\"#c61010\",\"imagen\":\"uploads/eventos/mermaid-diagram-20260522143849-0b9a89.png\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/frontis-09-20260522144511-90ef7d.jpg\",\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-22 14:38:49\",\"actualizado_en\":\"2026-05-24 12:15:25\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:15:25'),
(29, 1, 1, 'Eventos del calendario', 'eventos', '1', 'editar', 'Se modificó un evento', '{\"id_evento\":1,\"titulo\":\"EVENTO DE PRUEBA  JORQUERA\",\"slug\":null,\"descripcion_corta\":\"pronado la cracion de un event\",\"descripcion\":\"pronado la creación de un evento  en el calendario\",\"fecha_inicio\":\"2026-05-23\",\"fecha_termino\":\"2026-05-23\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Deportivo\",\"color\":\"#c61010\",\"imagen\":\"uploads/eventos/mermaid-diagram-20260522143849-0b9a89.png\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/frontis-09-20260522144511-90ef7d.jpg\",\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-22 14:38:49\",\"actualizado_en\":\"2026-05-24 12:15:25\"}', '{\"id_evento\":1,\"titulo\":\"EVENTO DE PRUEBA  JORQUERA CAICEDOS\",\"slug\":null,\"descripcion_corta\":\"pronado la cracion de un event\",\"descripcion\":\"pronado la creación de un evento  en el calendario\",\"fecha_inicio\":\"2026-05-23\",\"fecha_termino\":\"2026-05-23\",\"hora_inicio\":null,\"hora_termino\":null,\"ubicacion\":\"\",\"categoria\":\"Deportivo\",\"color\":\"#c61010\",\"imagen\":\"uploads/eventos/mermaid-diagram-20260522143849-0b9a89.png\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/frontis-09-20260522144511-90ef7d.jpg\",\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-22 14:38:49\",\"actualizado_en\":\"2026-05-24 12:16:47\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:16:47'),
(30, 1, 1, 'Eventos del calendario', 'eventos', '3', 'crear', 'Se creó un evento', NULL, '{\"id_evento\":3,\"titulo\":\"ejemplo de evento\",\"slug\":null,\"descripcion_corta\":\"ejemplo de evento deportivo\",\"descripcion\":\"de describe detalladamente\",\"fecha_inicio\":\"2026-05-24\",\"fecha_termino\":\"2026-05-24\",\"hora_inicio\":\"15:19:00\",\"hora_termino\":\"18:19:00\",\"ubicacion\":\"Venancio Benavídez 3612, 11700 Montevideo,\",\"categoria\":\"Deportivo\",\"color\":\"#fd7e14\",\"imagen\":\"uploads/eventos/img-6939-20260524122111-35c903.jpg\",\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:21:11\",\"actualizado_en\":\"2026-05-24 12:21:11\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:21:11'),
(31, 1, 1, 'Eventos del calendario', 'eventos', '3', 'editar', 'Se modificó un evento', '{\"id_evento\":3,\"titulo\":\"ejemplo de evento\",\"slug\":null,\"descripcion_corta\":\"ejemplo de evento deportivo\",\"descripcion\":\"de describe detalladamente\",\"fecha_inicio\":\"2026-05-24\",\"fecha_termino\":\"2026-05-24\",\"hora_inicio\":\"15:19:00\",\"hora_termino\":\"18:19:00\",\"ubicacion\":\"Venancio Benavídez 3612, 11700 Montevideo,\",\"categoria\":\"Deportivo\",\"color\":\"#fd7e14\",\"imagen\":\"uploads/eventos/img-6939-20260524122111-35c903.jpg\",\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:21:11\",\"actualizado_en\":\"2026-05-24 12:21:11\"}', '{\"id_evento\":3,\"titulo\":\"ejemplo de evento\",\"slug\":null,\"descripcion_corta\":\"ejemplo de evento deportivo\",\"descripcion\":\"de describe detalladamente\",\"fecha_inicio\":\"2026-05-24\",\"fecha_termino\":\"2026-05-24\",\"hora_inicio\":\"15:19:00\",\"hora_termino\":\"18:19:00\",\"ubicacion\":\"Venancio Benavídez 3612, 11700 Montevideo,\",\"categoria\":\"Deportivo\",\"color\":\"#fd7e14\",\"imagen\":\"uploads/eventos/img-6939-20260524122111-35c903.jpg\",\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:21:11\",\"actualizado_en\":\"2026-05-24 12:21:11\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:41:37'),
(32, 1, 1, 'Eventos del calendario', 'eventos', NULL, '', 'Se descargó la plantilla de carga masiva de eventos', NULL, '{\"archivo\":\"plantilla_eventos_calendario.xlsx\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:45:54'),
(33, 1, 1, 'Login administrador', 'usuario', '1', '', 'Intento de login fallido: clave incorrecta', '{\"id_usuario\":1,\"id_institucion\":1,\"nombre\":\"Cristian\",\"apellido\":\"Jorquera\",\"email\":\"cm.jorquerag@gmail.com\",\"usuario\":\"cm.jorquerag@gmail.com\",\"clave\":\"[OCULTO]\",\"rol\":\"\",\"estado\":\"activo\"}', '{\"usuario_input\":\"cm.jorquerag@gmail.com\",\"motivo\":\"clave_incorrecta\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:49:39'),
(34, 1, 1, 'Login administrador', 'usuario', '1', 'login', 'Login correcto en panel CMS', NULL, '{\"id_usuario\":1,\"id_institucion\":1,\"nombre\":\"Cristian\",\"apellido\":\"Jorquera\",\"email\":\"cm.jorquerag@gmail.com\",\"usuario\":\"cm.jorquerag@gmail.com\",\"clave\":\"[OCULTO]\",\"rol\":\"\",\"estado\":\"activo\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:49:44'),
(35, 1, 1, 'Eventos del calendario', 'eventos', NULL, '', 'Se descargó la plantilla de carga masiva de eventos', NULL, '{\"archivo\":\"plantilla_eventos_calendario.xlsx\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:50:17'),
(36, 1, 1, 'Eventos del calendario', 'eventos', NULL, '', 'Se descargó la plantilla de carga masiva de eventos', NULL, '{\"archivo\":\"plantilla_eventos_calendario.xlsx\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:51:08'),
(37, 1, 1, 'Eventos del calendario', 'eventos', '4', 'crear', 'Se creó un evento', NULL, '{\"id_evento\":4,\"titulo\":\"EVENTO DE PRUEBA\",\"slug\":null,\"descripcion_corta\":\"ejemplo de evento\",\"descripcion\":\"descripción larga del evento ejemplo de evento\",\"fecha_inicio\":\"2026-05-24\",\"fecha_termino\":\"2026-05-24\",\"hora_inicio\":\"14:56:00\",\"hora_termino\":\"14:56:00\",\"ubicacion\":\"Venancio Benavídez 3612, 11700 Montevideo,\",\"categoria\":\"Deportivo\",\"color\":\"#fd7e14\",\"imagen\":\"uploads/eventos/img-6939-20260524125729-5be3c3.jpg\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/img-6845-20260524125729-47624b.jpg\",\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:57:29\",\"actualizado_en\":\"2026-05-24 12:57:29\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:57:29'),
(38, 1, 1, 'Eventos del calendario', 'eventos', '4', 'editar', 'Se modificó un evento', '{\"id_evento\":4,\"titulo\":\"EVENTO DE PRUEBA\",\"slug\":null,\"descripcion_corta\":\"ejemplo de evento\",\"descripcion\":\"descripción larga del evento ejemplo de evento\",\"fecha_inicio\":\"2026-05-24\",\"fecha_termino\":\"2026-05-24\",\"hora_inicio\":\"14:56:00\",\"hora_termino\":\"14:56:00\",\"ubicacion\":\"Venancio Benavídez 3612, 11700 Montevideo,\",\"categoria\":\"Deportivo\",\"color\":\"#fd7e14\",\"imagen\":\"uploads/eventos/img-6939-20260524125729-5be3c3.jpg\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/img-6845-20260524125729-47624b.jpg\",\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:57:29\",\"actualizado_en\":\"2026-05-24 12:57:29\"}', '{\"id_evento\":4,\"titulo\":\"EVENTO DE PRUEBA\",\"slug\":null,\"descripcion_corta\":\"ejemplo de evento\",\"descripcion\":\"descripción larga del evento ejemplo de evento\",\"fecha_inicio\":\"2026-05-24\",\"fecha_termino\":\"2026-05-24\",\"hora_inicio\":\"14:56:00\",\"hora_termino\":\"14:56:00\",\"ubicacion\":\"Venancio Benavídez 3612, 11700 Montevideo,\",\"categoria\":\"Deportivo\",\"color\":\"#fd7e14\",\"imagen\":\"uploads/eventos/img-6939-20260524125729-5be3c3.jpg\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/img-6845-20260524125729-47624b.jpg\",\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:57:29\",\"actualizado_en\":\"2026-05-24 12:57:29\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 12:59:28'),
(39, 1, 1, 'Eventos del calendario', 'eventos', '4', 'editar', 'Se modificó un evento', '{\"id_evento\":4,\"titulo\":\"EVENTO DE PRUEBA\",\"slug\":null,\"descripcion_corta\":\"ejemplo de evento\",\"descripcion\":\"descripción larga del evento ejemplo de evento\",\"fecha_inicio\":\"2026-05-24\",\"fecha_termino\":\"2026-05-24\",\"hora_inicio\":\"14:56:00\",\"hora_termino\":\"14:56:00\",\"ubicacion\":\"Venancio Benavídez 3612, 11700 Montevideo,\",\"categoria\":\"Deportivo\",\"color\":\"#fd7e14\",\"imagen\":\"uploads/eventos/img-6939-20260524125729-5be3c3.jpg\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/img-6845-20260524125729-47624b.jpg\",\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:57:29\",\"actualizado_en\":\"2026-05-24 12:57:29\"}', '{\"id_evento\":4,\"titulo\":\"EVENTO DE PRUEBA\",\"slug\":null,\"descripcion_corta\":\"ejemplo de evento\",\"descripcion\":\"descripción larga del evento ejemplo de evento\",\"fecha_inicio\":\"2026-05-24\",\"fecha_termino\":\"2026-05-24\",\"hora_inicio\":\"14:56:00\",\"hora_termino\":\"14:56:00\",\"ubicacion\":\"Venancio Benavídez 3612, 11700 Montevideo,\",\"categoria\":\"Deportivo\",\"color\":\"#fd7e14\",\"imagen\":\"uploads/eventos/img-6939-20260524125729-5be3c3.jpg\",\"archivo_adjunto\":\"uploads/eventos/adjuntos/img-6845-20260524125729-47624b.jpg\",\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 12:57:29\",\"actualizado_en\":\"2026-05-24 12:57:29\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:00:24'),
(40, 1, 1, 'Eventos del calendario', 'eventos', NULL, '', 'Se descargó la plantilla de carga masiva de eventos', NULL, '{\"archivo\":\"plantilla_eventos_calendario.xlsx\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:05:13'),
(41, 1, 1, 'Login administrador', 'usuario', '1', 'login', 'Login correcto en panel CMS', NULL, '{\"id_usuario\":1,\"id_institucion\":1,\"nombre\":\"Cristian\",\"apellido\":\"Jorquera\",\"email\":\"cm.jorquerag@gmail.com\",\"usuario\":\"cm.jorquerag@gmail.com\",\"clave\":\"[OCULTO]\",\"rol\":\"\",\"estado\":\"activo\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:10:59'),
(42, 1, 1, 'Items de contenedor', 'seccion_item', '24', 'editar', 'Se modificó un item de contenedor', '{\"id_item\":24,\"id_seccion\":21,\"id_categoria\":null,\"etiqueta\":\"\",\"icono\":null,\"titulo\":\"\",\"titulo_linea_1\":\"\",\"titulo_linea_2\":\"\",\"titulo_linea_3\":\"\",\"subtitulo\":\"\",\"descripcion\":\"\",\"imagen\":\"uploads/secciones/video_destacado_home/alumno-1-20260523133509-da6832.jpg\",\"imagen_mobile\":null,\"boton_1_texto\":\"\",\"boton_1_url\":\"\",\"boton_2_texto\":\"\",\"boton_2_url\":\"\",\"url\":\"\",\"fecha_publicacion\":null,\"visible\":\"si\",\"orden\":1,\"fecha_creacion\":\"2026-05-23 13:34:41\"}', '{\"id_item\":24,\"id_seccion\":21,\"id_categoria\":null,\"etiqueta\":\"\",\"icono\":null,\"titulo\":\"\",\"titulo_linea_1\":\"\",\"titulo_linea_2\":\"\",\"titulo_linea_3\":\"\",\"subtitulo\":\"\",\"descripcion\":\"\",\"imagen\":\"uploads/secciones/video_destacado_home/img-6939-20260524133007-264b78.jpg\",\"imagen_mobile\":null,\"boton_1_texto\":\"\",\"boton_1_url\":\"\",\"boton_2_texto\":\"\",\"boton_2_url\":\"\",\"url\":\"\",\"fecha_publicacion\":null,\"visible\":\"si\",\"orden\":1,\"fecha_creacion\":\"2026-05-23 13:34:41\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:30:07'),
(43, 1, 1, 'Items de contenedor', 'seccion_item', '9', 'editar', 'Se modificó un item de contenedor', '{\"id_item\":9,\"id_seccion\":3,\"id_categoria\":null,\"etiqueta\":\"\",\"icono\":null,\"titulo\":\"\",\"titulo_linea_1\":\"Guadalupe\",\"titulo_linea_2\":\"jorquera\",\"titulo_linea_3\":\"titulo 3\",\"subtitulo\":\"\",\"descripcion\":\"contenedor de prueba\",\"imagen\":\"uploads/secciones/hero_principal/img-6939-20260523133210-99d308.jpg\",\"imagen_mobile\":\"uploads/secciones/hero_principal/imagen-fondo-20260514191258-82a3e9.jpg\",\"boton_1_texto\":\"\",\"boton_1_url\":\"\",\"boton_2_texto\":\"\",\"boton_2_url\":\"\",\"url\":\"\",\"fecha_publicacion\":null,\"visible\":\"si\",\"orden\":1,\"fecha_creacion\":\"2026-04-19 19:44:28\"}', '{\"id_item\":9,\"id_seccion\":3,\"id_categoria\":null,\"etiqueta\":\"\",\"icono\":null,\"titulo\":\"\",\"titulo_linea_1\":\"Guadalupe\",\"titulo_linea_2\":\"jorquera\",\"titulo_linea_3\":\"titulo 3\",\"subtitulo\":\"\",\"descripcion\":\"contenedor de prueba\",\"imagen\":\"uploads/secciones/hero_principal/img-6939-20260524133308-4d0fad.jpg\",\"imagen_mobile\":\"uploads/secciones/hero_principal/imagen-fondo-20260514191258-82a3e9.jpg\",\"boton_1_texto\":\"\",\"boton_1_url\":\"\",\"boton_2_texto\":\"\",\"boton_2_url\":\"\",\"url\":\"\",\"fecha_publicacion\":null,\"visible\":\"si\",\"orden\":1,\"fecha_creacion\":\"2026-04-19 19:44:28\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:33:08');
INSERT INTO `auditoria_log` (`id_log`, `id_usuario`, `id_institucion`, `modulo`, `tabla_afectada`, `id_registro`, `accion`, `descripcion`, `datos_antes`, `datos_despues`, `ip_usuario`, `user_agent`, `fecha_hora`) VALUES
(44, 1, 1, 'Items de contenedor', 'seccion_item', '20', 'editar', 'Se modificó un item de contenedor', '{\"id_item\":20,\"id_seccion\":3,\"id_categoria\":null,\"etiqueta\":\"eticketa ej\",\"icono\":null,\"titulo\":\"\",\"titulo_linea_1\":\"titulo 1 ejemplo\",\"titulo_linea_2\":\"titulo 2 ejemplo\",\"titulo_linea_3\":\"titulo 3 ejemplo\",\"subtitulo\":\"\",\"descripcion\":\"descripcion ejemplo\",\"imagen\":\"uploads/secciones/hero_principal/alumno-2-20260523133240-318dd4.jpg\",\"imagen_mobile\":\"uploads/secciones/hero_principal/lupe-04-20260420113602-ee9305.jpg\",\"boton_1_texto\":\"botón 1\",\"boton_1_url\":\"botón url\",\"boton_2_texto\":\"botón  2\",\"boton_2_url\":\"botón url\",\"url\":\"\",\"fecha_publicacion\":null,\"visible\":\"si\",\"orden\":1,\"fecha_creacion\":\"2026-04-20 11:30:02\"}', '{\"id_item\":20,\"id_seccion\":3,\"id_categoria\":null,\"etiqueta\":\"eticketa ej\",\"icono\":null,\"titulo\":\"\",\"titulo_linea_1\":\"titulo 1 ejemplo\",\"titulo_linea_2\":\"titulo 2 ejemplo\",\"titulo_linea_3\":\"titulo 3 ejemplo\",\"subtitulo\":\"\",\"descripcion\":\"descripcion ejemplo\",\"imagen\":\"uploads/secciones/hero_principal/member-3-20260524133327-11a954.jpg\",\"imagen_mobile\":\"uploads/secciones/hero_principal/lupe-04-20260420113602-ee9305.jpg\",\"boton_1_texto\":\"botón 1\",\"boton_1_url\":\"botón url\",\"boton_2_texto\":\"botón  2\",\"boton_2_url\":\"botón url\",\"url\":\"\",\"fecha_publicacion\":null,\"visible\":\"si\",\"orden\":1,\"fecha_creacion\":\"2026-04-20 11:30:02\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:33:27'),
(45, 1, 1, 'Eventos del calendario', 'eventos', '5', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":5,\"titulo\":\"Misa Institucional\",\"slug\":null,\"descripcion_corta\":\"Eucaristia en comunidad.\",\"descripcion\":\"Descripcion completa del evento.\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"10:00:00\",\"ubicacion\":\"Capilla del Colegio\",\"categoria\":\"Pastoral\",\"color\":\"#8e44ad\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":1,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(46, 1, 1, 'Eventos del calendario', 'eventos', '6', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":6,\"titulo\":\"Feria Científica 2026\",\"slug\":null,\"descripcion_corta\":\"Exposición de proyectos científicos\",\"descripcion\":\"Jornada donde estudiantes presentan experimentos y proyectos innovadores desarrollados durante el semestre.\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"13:30:00\",\"ubicacion\":\"Gimnasio Colegio San Pablo\",\"categoria\":\"Académico\",\"color\":\"#2563eb\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(47, 1, 1, 'Eventos del calendario', 'eventos', '7', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":7,\"titulo\":\"Día del Deporte\",\"slug\":null,\"descripcion_corta\":\"Actividades deportivas institucionales\",\"descripcion\":\"Competencias recreativas y deportivas para fomentar el trabajo en equipo y vida saludable.\",\"fecha_inicio\":\"2026-05-24\",\"fecha_termino\":\"2026-05-24\",\"hora_inicio\":\"08:30:00\",\"hora_termino\":\"14:00:00\",\"ubicacion\":\"Cancha principal\",\"categoria\":\"Deportivo\",\"color\":\"#16a34a\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(48, 1, 1, 'Eventos del calendario', 'eventos', '8', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":8,\"titulo\":\"Misa Institucional\",\"slug\":null,\"descripcion_corta\":\"Eucaristía comunitaria\",\"descripcion\":\"Celebración religiosa junto a estudiantes, docentes y apoderados.\",\"fecha_inicio\":\"2026-05-25\",\"fecha_termino\":\"2026-05-25\",\"hora_inicio\":\"10:00:00\",\"hora_termino\":\"11:30:00\",\"ubicacion\":\"Capilla del Colegio\",\"categoria\":\"Pastoral\",\"color\":\"#9333ea\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(49, 1, 1, 'Eventos del calendario', 'eventos', '9', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":9,\"titulo\":\"Semana de la Lectura\",\"slug\":null,\"descripcion_corta\":\"Fomento lector\",\"descripcion\":\"Actividades orientadas a incentivar la lectura y comprensión lectora.\",\"fecha_inicio\":\"2026-06-02\",\"fecha_termino\":\"2026-06-06\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"12:00:00\",\"ubicacion\":\"Biblioteca Central\",\"categoria\":\"Académico\",\"color\":\"#0ea5e9\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(50, 1, 1, 'Eventos del calendario', 'eventos', '10', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":10,\"titulo\":\"Campeonato de Futbol\",\"slug\":null,\"descripcion_corta\":\"Encuentro deportivo interescolar\",\"descripcion\":\"Torneo amistoso entre distintos niveles educativos del colegio.\",\"fecha_inicio\":\"2026-06-08\",\"fecha_termino\":\"2026-06-08\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"16:00:00\",\"ubicacion\":\"Cancha sintética\",\"categoria\":\"Deportivo\",\"color\":\"#22c55e\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(51, 1, 1, 'Eventos del calendario', 'eventos', '11', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":11,\"titulo\":\"Taller de Robótica\",\"slug\":null,\"descripcion_corta\":\"Innovación tecnológica\",\"descripcion\":\"Estudiantes aprenden programación y automatización con robots educativos.\",\"fecha_inicio\":\"2026-06-12\",\"fecha_termino\":\"2026-06-12\",\"hora_inicio\":\"14:00:00\",\"hora_termino\":\"17:00:00\",\"ubicacion\":\"Laboratorio de Computación\",\"categoria\":\"Académico\",\"color\":\"#3b82f6\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(52, 1, 1, 'Eventos del calendario', 'eventos', '12', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":12,\"titulo\":\"Festival de Talentos\",\"slug\":null,\"descripcion_corta\":\"Presentaciones artísticas\",\"descripcion\":\"Muestra musical, danza y teatro preparada por los estudiantes.\",\"fecha_inicio\":\"2026-06-18\",\"fecha_termino\":\"2026-06-18\",\"hora_inicio\":\"18:00:00\",\"hora_termino\":\"21:00:00\",\"ubicacion\":\"Auditorio Principal\",\"categoria\":\"Cultural\",\"color\":\"#ec4899\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(53, 1, 1, 'Eventos del calendario', 'eventos', '13', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":13,\"titulo\":\"Jornada de Convivencia\",\"slug\":null,\"descripcion_corta\":\"Integración estudiantil\",\"descripcion\":\"Actividades grupales para fortalecer el compañerismo y la convivencia escolar.\",\"fecha_inicio\":\"2026-06-21\",\"fecha_termino\":\"2026-06-21\",\"hora_inicio\":\"09:30:00\",\"hora_termino\":\"13:00:00\",\"ubicacion\":\"Patio Central\",\"categoria\":\"Institucional\",\"color\":\"#f59e0b\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(54, 1, 1, 'Eventos del calendario', 'eventos', '14', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":14,\"titulo\":\"Charla Universitaria\",\"slug\":null,\"descripcion_corta\":\"Orientación vocacional\",\"descripcion\":\"Universidades invitadas presentan carreras y beneficios estudiantiles.\",\"fecha_inicio\":\"2026-06-24\",\"fecha_termino\":\"2026-06-24\",\"hora_inicio\":\"11:00:00\",\"hora_termino\":\"13:00:00\",\"ubicacion\":\"Salón Multimedia\",\"categoria\":\"Académico\",\"color\":\"#2563eb\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(55, 1, 1, 'Eventos del calendario', 'eventos', '15', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":15,\"titulo\":\"Celebración Día del Profesor\",\"slug\":null,\"descripcion_corta\":\"Reconocimiento docente\",\"descripcion\":\"Actividad especial organizada por estudiantes y directivos.\",\"fecha_inicio\":\"2026-07-01\",\"fecha_termino\":\"2026-07-01\",\"hora_inicio\":\"12:00:00\",\"hora_termino\":\"14:00:00\",\"ubicacion\":\"Casino del Colegio\",\"categoria\":\"Institucional\",\"color\":\"#f97316\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(56, 1, 1, 'Eventos del calendario', 'eventos', '16', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":16,\"titulo\":\"Encuentro de Debate\",\"slug\":null,\"descripcion_corta\":\"Competencia académica\",\"descripcion\":\"Debates sobre actualidad y pensamiento crítico entre estudiantes.\",\"fecha_inicio\":\"2026-07-05\",\"fecha_termino\":\"2026-07-05\",\"hora_inicio\":\"10:00:00\",\"hora_termino\":\"15:00:00\",\"ubicacion\":\"Sala de Conferencias\",\"categoria\":\"Académico\",\"color\":\"#4f46e5\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(57, 1, 1, 'Eventos del calendario', 'eventos', '17', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":17,\"titulo\":\"Salida Pedagógica\",\"slug\":null,\"descripcion_corta\":\"Museo de Ciencias\",\"descripcion\":\"Visita educativa guiada para reforzar contenidos de ciencias naturales.\",\"fecha_inicio\":\"2026-07-10\",\"fecha_termino\":\"2026-07-10\",\"hora_inicio\":\"08:00:00\",\"hora_termino\":\"17:00:00\",\"ubicacion\":\"Museo Nacional de Ciencias\",\"categoria\":\"Académico\",\"color\":\"#0f766e\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(58, 1, 1, 'Eventos del calendario', 'eventos', '18', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":18,\"titulo\":\"Encuentro Pastoral\",\"slug\":null,\"descripcion_corta\":\"Jornada espiritual\",\"descripcion\":\"Reflexión y actividades pastorales para toda la comunidad educativa.\",\"fecha_inicio\":\"2026-07-15\",\"fecha_termino\":\"2026-07-15\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"12:30:00\",\"ubicacion\":\"Centro Pastoral\",\"categoria\":\"Pastoral\",\"color\":\"#7c3aed\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(59, 1, 1, 'Eventos del calendario', 'eventos', '19', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":19,\"titulo\":\"Expo Arte Estudiantil\",\"slug\":null,\"descripcion_corta\":\"Muestra artística\",\"descripcion\":\"Exposición de pinturas, esculturas y trabajos creativos de estudiantes.\",\"fecha_inicio\":\"2026-07-18\",\"fecha_termino\":\"2026-07-18\",\"hora_inicio\":\"10:00:00\",\"hora_termino\":\"18:00:00\",\"ubicacion\":\"Galería Escolar\",\"categoria\":\"Cultural\",\"color\":\"#db2777\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(60, 1, 1, 'Eventos del calendario', 'eventos', '20', 'importar', 'Se importó un evento desde carga masiva confirmada', NULL, '{\"id_evento\":20,\"titulo\":\"Ceremonia de Premiación\",\"slug\":null,\"descripcion_corta\":\"Reconocimiento académico\",\"descripcion\":\"Premiación a estudiantes destacados del semestre.\",\"fecha_inicio\":\"2026-07-25\",\"fecha_termino\":\"2026-07-25\",\"hora_inicio\":\"17:00:00\",\"hora_termino\":\"19:30:00\",\"ubicacion\":\"Auditorio Principal\",\"categoria\":\"Institucional\",\"color\":\"#dc2626\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(61, 1, 1, 'Eventos del calendario', 'eventos', NULL, 'importar', 'Importación masiva confirmada', NULL, '{\"creados\":16,\"duplicados\":0,\"errores\":0,\"seleccionados\":16}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:40:51'),
(62, 1, 1, 'Eventos del calendario', 'eventos', '6', 'editar', 'Se modificó un evento', '{\"id_evento\":6,\"titulo\":\"Feria Científica 2026\",\"slug\":null,\"descripcion_corta\":\"Exposición de proyectos científicos\",\"descripcion\":\"Jornada donde estudiantes presentan experimentos y proyectos innovadores desarrollados durante el semestre.\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"13:30:00\",\"ubicacion\":\"Gimnasio Colegio San Pablo\",\"categoria\":\"Académico\",\"color\":\"#2563eb\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":1,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:40:51\"}', '{\"id_evento\":6,\"titulo\":\"Feria Científicaaaaa 2026\",\"slug\":null,\"descripcion_corta\":\"Exposición de proyectos científicos\",\"descripcion\":\"Jornada donde estudiantes presentan experimentos y proyectos innovadores desarrollados durante el semestre.\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"13:30:00\",\"ubicacion\":\"Gimnasio Colegio San Pablo\",\"categoria\":\"Académico\",\"color\":\"#2563eb\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:41:18\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:41:18'),
(63, 1, 1, 'Eventos del calendario', 'eventos', '6', 'editar', 'Se modificó un evento', '{\"id_evento\":6,\"titulo\":\"Feria Científicaaaaa 2026\",\"slug\":null,\"descripcion_corta\":\"Exposición de proyectos científicos\",\"descripcion\":\"Jornada donde estudiantes presentan experimentos y proyectos innovadores desarrollados durante el semestre.\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"13:30:00\",\"ubicacion\":\"Gimnasio Colegio San Pablo\",\"categoria\":\"Académico\",\"color\":\"#2563eb\",\"imagen\":null,\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:41:18\"}', '{\"id_evento\":6,\"titulo\":\"Feria Científicaaaaa 2026\",\"slug\":null,\"descripcion_corta\":\"Exposición de proyectos científicos\",\"descripcion\":\"Jornada donde estudiantes presentan experimentos y proyectos innovadores desarrollados durante el semestre.\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"13:30:00\",\"ubicacion\":\"Gimnasio Colegio San Pablo\",\"categoria\":\"Académico\",\"color\":\"#2563eb\",\"imagen\":\"uploads/eventos/20251107131854-20260524134314-253f1f.png\",\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:43:14\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:43:14'),
(64, 1, 1, 'Eventos del calendario', 'eventos', '21', 'crear', 'Se creó un evento', NULL, '{\"id_evento\":21,\"titulo\":\"EVENTO DE PRUEBA\",\"slug\":null,\"descripcion_corta\":\"evento prueba descripción corta\",\"descripcion\":\"evento prueba descripción completa\",\"fecha_inicio\":\"2026-05-23\",\"fecha_termino\":\"2026-05-23\",\"hora_inicio\":\"16:46:00\",\"hora_termino\":\"19:47:00\",\"ubicacion\":\"Venancio Benavídez 3612, 11700 Montevideo,\",\"categoria\":\"Deportivo\",\"color\":\"#32fd17\",\"imagen\":\"uploads/eventos/portada-2-20260524134811-655672.jpg\",\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:48:11\",\"actualizado_en\":\"2026-05-24 13:48:11\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 13:48:12'),
(65, 1, 1, 'Eventos del calendario', 'eventos', '6', '', 'Se cambió la visibilidad de un evento', '{\"id_evento\":6,\"titulo\":\"Feria Científicaaaaa 2026\",\"slug\":null,\"descripcion_corta\":\"Exposición de proyectos científicos\",\"descripcion\":\"Jornada donde estudiantes presentan experimentos y proyectos innovadores desarrollados durante el semestre.\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"13:30:00\",\"ubicacion\":\"Gimnasio Colegio San Pablo\",\"categoria\":\"Académico\",\"color\":\"#2563eb\",\"imagen\":\"uploads/eventos/20251107131854-20260524134314-253f1f.png\",\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 13:43:14\"}', '{\"id_evento\":6,\"titulo\":\"Feria Científicaaaaa 2026\",\"slug\":null,\"descripcion_corta\":\"Exposición de proyectos científicos\",\"descripcion\":\"Jornada donde estudiantes presentan experimentos y proyectos innovadores desarrollados durante el semestre.\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"13:30:00\",\"ubicacion\":\"Gimnasio Colegio San Pablo\",\"categoria\":\"Académico\",\"color\":\"#2563eb\",\"imagen\":\"uploads/eventos/20251107131854-20260524134314-253f1f.png\",\"archivo_adjunto\":null,\"destacado\":0,\"visible\":0,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 14:33:58\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 14:33:58'),
(66, 1, 1, 'Eventos del calendario', 'eventos', '6', '', 'Se cambió la visibilidad de un evento', '{\"id_evento\":6,\"titulo\":\"Feria Científicaaaaa 2026\",\"slug\":null,\"descripcion_corta\":\"Exposición de proyectos científicos\",\"descripcion\":\"Jornada donde estudiantes presentan experimentos y proyectos innovadores desarrollados durante el semestre.\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"13:30:00\",\"ubicacion\":\"Gimnasio Colegio San Pablo\",\"categoria\":\"Académico\",\"color\":\"#2563eb\",\"imagen\":\"uploads/eventos/20251107131854-20260524134314-253f1f.png\",\"archivo_adjunto\":null,\"destacado\":0,\"visible\":0,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 14:33:58\"}', '{\"id_evento\":6,\"titulo\":\"Feria Científicaaaaa 2026\",\"slug\":null,\"descripcion_corta\":\"Exposición de proyectos científicos\",\"descripcion\":\"Jornada donde estudiantes presentan experimentos y proyectos innovadores desarrollados durante el semestre.\",\"fecha_inicio\":\"2026-05-22\",\"fecha_termino\":\"2026-05-22\",\"hora_inicio\":\"09:00:00\",\"hora_termino\":\"13:30:00\",\"ubicacion\":\"Gimnasio Colegio San Pablo\",\"categoria\":\"Académico\",\"color\":\"#2563eb\",\"imagen\":\"uploads/eventos/20251107131854-20260524134314-253f1f.png\",\"archivo_adjunto\":null,\"destacado\":0,\"visible\":1,\"orden\":0,\"estado\":\"publicado\",\"creado_en\":\"2026-05-24 13:40:51\",\"actualizado_en\":\"2026-05-24 14:34:03\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 14:34:03'),
(67, 1, 1, 'Login administrador', 'usuario', '1', 'login', 'Login correcto en panel CMS', NULL, '{\"id_usuario\":1,\"id_institucion\":1,\"nombre\":\"Cristian\",\"apellido\":\"Jorquera\",\"email\":\"cm.jorquerag@gmail.com\",\"usuario\":\"cm.jorquerag@gmail.com\",\"clave\":\"[OCULTO]\",\"rol\":\"\",\"estado\":\"activo\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 14:39:28'),
(68, 1, 1, 'Items de contenedor', 'seccion_item', '3', 'editar', 'Se modificó un item de contenedor', '{\"id_item\":3,\"id_seccion\":4,\"id_categoria\":null,\"etiqueta\":\"\",\"icono\":null,\"titulo\":\"Rugby del Prado\",\"titulo_linea_1\":\"\",\"titulo_linea_2\":\"\",\"titulo_linea_3\":\"\",\"subtitulo\":\"\",\"descripcion\":\"Nuestros alumnos participaron en el torneo intercolegial de rugby con excelentes resultados.\",\"imagen\":\"uploads/noticias/alumno-1-20260523133321-91f3e6.jpg\",\"imagen_mobile\":null,\"boton_1_texto\":\"Leer más\",\"boton_1_url\":\"#\",\"boton_2_texto\":\"\",\"boton_2_url\":\"\",\"url\":\"\",\"fecha_publicacion\":\"2025-11-01\",\"visible\":\"si\",\"orden\":1,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '{\"id_item\":3,\"id_seccion\":4,\"id_categoria\":null,\"etiqueta\":\"\",\"icono\":null,\"titulo\":\"Rugby del Prado\",\"titulo_linea_1\":\"\",\"titulo_linea_2\":\"\",\"titulo_linea_3\":\"\",\"subtitulo\":\"\",\"descripcion\":\"Nuestros alumnos participaron en el torneo intercolegial de rugby con excelentes resultados.\",\"imagen\":\"uploads/noticias/portada-1-20260524151506-5de9b2.jpg\",\"imagen_mobile\":null,\"boton_1_texto\":\"Leer más\",\"boton_1_url\":\"#\",\"boton_2_texto\":\"\",\"boton_2_url\":\"\",\"url\":\"\",\"fecha_publicacion\":\"2025-11-01\",\"visible\":\"si\",\"orden\":1,\"fecha_creacion\":\"2026-04-19 19:39:21\"}', '191.113.135.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-24 15:15:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calendario`
--

CREATE TABLE `calendario` (
  `id_calendario` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `ano` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `dia` int(11) NOT NULL,
  `numero_dia_semana` tinyint(4) NOT NULL,
  `nombre_dia_semana` varchar(20) NOT NULL,
  `es_fin_semana` tinyint(1) NOT NULL DEFAULT 0,
  `es_feriado` tinyint(1) NOT NULL DEFAULT 0,
  `es_dia_habil` tinyint(1) NOT NULL DEFAULT 1,
  `nombre_feriado` varchar(180) DEFAULT NULL,
  `descripcion_feriado` varchar(300) DEFAULT NULL,
  `tipo` enum('normal','feriado','institucional','academico','vacaciones','suspension') NOT NULL DEFAULT 'normal',
  `color` varchar(20) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `calendario`
--

INSERT INTO `calendario` (`id_calendario`, `fecha`, `ano`, `mes`, `dia`, `numero_dia_semana`, `nombre_dia_semana`, `es_fin_semana`, `es_feriado`, `es_dia_habil`, `nombre_feriado`, `descripcion_feriado`, `tipo`, `color`, `visible`, `creado_en`, `actualizado_en`) VALUES
(1, '2026-01-01', 2026, 1, 1, 4, 'Jueves', 0, 1, 0, 'Año Nuevo', 'Feriado nacional en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(2, '2026-01-02', 2026, 1, 2, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(3, '2026-01-03', 2026, 1, 3, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(4, '2026-01-04', 2026, 1, 4, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(5, '2026-01-05', 2026, 1, 5, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(6, '2026-01-06', 2026, 1, 6, 2, 'Martes', 0, 1, 1, 'Día de Reyes', 'Celebración tradicional en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(7, '2026-01-07', 2026, 1, 7, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(8, '2026-01-08', 2026, 1, 8, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(9, '2026-01-09', 2026, 1, 9, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(10, '2026-01-10', 2026, 1, 10, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(11, '2026-01-11', 2026, 1, 11, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(12, '2026-01-12', 2026, 1, 12, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(13, '2026-01-13', 2026, 1, 13, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(14, '2026-01-14', 2026, 1, 14, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(15, '2026-01-15', 2026, 1, 15, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(16, '2026-01-16', 2026, 1, 16, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(17, '2026-01-17', 2026, 1, 17, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(18, '2026-01-18', 2026, 1, 18, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(19, '2026-01-19', 2026, 1, 19, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(20, '2026-01-20', 2026, 1, 20, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(21, '2026-01-21', 2026, 1, 21, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(22, '2026-01-22', 2026, 1, 22, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(23, '2026-01-23', 2026, 1, 23, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(24, '2026-01-24', 2026, 1, 24, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(25, '2026-01-25', 2026, 1, 25, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(26, '2026-01-26', 2026, 1, 26, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(27, '2026-01-27', 2026, 1, 27, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(28, '2026-01-28', 2026, 1, 28, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(29, '2026-01-29', 2026, 1, 29, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(30, '2026-01-30', 2026, 1, 30, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(31, '2026-01-31', 2026, 1, 31, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(32, '2026-02-01', 2026, 2, 1, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(33, '2026-02-02', 2026, 2, 2, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(34, '2026-02-03', 2026, 2, 3, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(35, '2026-02-04', 2026, 2, 4, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(36, '2026-02-05', 2026, 2, 5, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(37, '2026-02-06', 2026, 2, 6, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(38, '2026-02-07', 2026, 2, 7, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(39, '2026-02-08', 2026, 2, 8, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(40, '2026-02-09', 2026, 2, 9, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(41, '2026-02-10', 2026, 2, 10, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(42, '2026-02-11', 2026, 2, 11, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(43, '2026-02-12', 2026, 2, 12, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(44, '2026-02-13', 2026, 2, 13, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(45, '2026-02-14', 2026, 2, 14, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(46, '2026-02-15', 2026, 2, 15, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(47, '2026-02-16', 2026, 2, 16, 1, 'Lunes', 0, 1, 0, 'Carnaval', 'Feriado de Carnaval en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(48, '2026-02-17', 2026, 2, 17, 2, 'Martes', 0, 1, 0, 'Carnaval', 'Feriado de Carnaval en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(49, '2026-02-18', 2026, 2, 18, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(50, '2026-02-19', 2026, 2, 19, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(51, '2026-02-20', 2026, 2, 20, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(52, '2026-02-21', 2026, 2, 21, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(53, '2026-02-22', 2026, 2, 22, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(54, '2026-02-23', 2026, 2, 23, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(55, '2026-02-24', 2026, 2, 24, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(56, '2026-02-25', 2026, 2, 25, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(57, '2026-02-26', 2026, 2, 26, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(58, '2026-02-27', 2026, 2, 27, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(59, '2026-02-28', 2026, 2, 28, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(60, '2026-03-01', 2026, 3, 1, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(61, '2026-03-02', 2026, 3, 2, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(62, '2026-03-03', 2026, 3, 3, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(63, '2026-03-04', 2026, 3, 4, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(64, '2026-03-05', 2026, 3, 5, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(65, '2026-03-06', 2026, 3, 6, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(66, '2026-03-07', 2026, 3, 7, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(67, '2026-03-08', 2026, 3, 8, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(68, '2026-03-09', 2026, 3, 9, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(69, '2026-03-10', 2026, 3, 10, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(70, '2026-03-11', 2026, 3, 11, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(71, '2026-03-12', 2026, 3, 12, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(72, '2026-03-13', 2026, 3, 13, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(73, '2026-03-14', 2026, 3, 14, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(74, '2026-03-15', 2026, 3, 15, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(75, '2026-03-16', 2026, 3, 16, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(76, '2026-03-17', 2026, 3, 17, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(77, '2026-03-18', 2026, 3, 18, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(78, '2026-03-19', 2026, 3, 19, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(79, '2026-03-20', 2026, 3, 20, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(80, '2026-03-21', 2026, 3, 21, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(81, '2026-03-22', 2026, 3, 22, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(82, '2026-03-23', 2026, 3, 23, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(83, '2026-03-24', 2026, 3, 24, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(84, '2026-03-25', 2026, 3, 25, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(85, '2026-03-26', 2026, 3, 26, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(86, '2026-03-27', 2026, 3, 27, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(87, '2026-03-28', 2026, 3, 28, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(88, '2026-03-29', 2026, 3, 29, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(89, '2026-03-30', 2026, 3, 30, 1, 'Lunes', 0, 1, 0, 'Semana de Turismo', 'Semana de Turismo / Semana Santa en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(90, '2026-03-31', 2026, 3, 31, 2, 'Martes', 0, 1, 0, 'Semana de Turismo', 'Semana de Turismo / Semana Santa en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(91, '2026-04-01', 2026, 4, 1, 3, 'Miércoles', 0, 1, 0, 'Semana de Turismo', 'Semana de Turismo / Semana Santa en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(92, '2026-04-02', 2026, 4, 2, 4, 'Jueves', 0, 1, 0, 'Semana de Turismo', 'Semana de Turismo / Semana Santa en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(93, '2026-04-03', 2026, 4, 3, 5, 'Viernes', 0, 1, 0, 'Semana de Turismo', 'Semana de Turismo / Semana Santa en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(94, '2026-04-04', 2026, 4, 4, 6, 'Sábado', 1, 1, 0, 'Semana de Turismo', 'Semana de Turismo / Semana Santa en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(95, '2026-04-05', 2026, 4, 5, 7, 'Domingo', 1, 1, 0, 'Semana de Turismo', 'Semana de Turismo / Semana Santa en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(96, '2026-04-06', 2026, 4, 6, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(97, '2026-04-07', 2026, 4, 7, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(98, '2026-04-08', 2026, 4, 8, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(99, '2026-04-09', 2026, 4, 9, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(100, '2026-04-10', 2026, 4, 10, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(101, '2026-04-11', 2026, 4, 11, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(102, '2026-04-12', 2026, 4, 12, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(103, '2026-04-13', 2026, 4, 13, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(104, '2026-04-14', 2026, 4, 14, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(105, '2026-04-15', 2026, 4, 15, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(106, '2026-04-16', 2026, 4, 16, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(107, '2026-04-17', 2026, 4, 17, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(108, '2026-04-18', 2026, 4, 18, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(109, '2026-04-19', 2026, 4, 19, 7, 'Domingo', 1, 1, 0, 'Desembarco de los Treinta y Tres Orientales', 'Conmemoración histórica nacional.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(110, '2026-04-20', 2026, 4, 20, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(111, '2026-04-21', 2026, 4, 21, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(112, '2026-04-22', 2026, 4, 22, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(113, '2026-04-23', 2026, 4, 23, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(114, '2026-04-24', 2026, 4, 24, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(115, '2026-04-25', 2026, 4, 25, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(116, '2026-04-26', 2026, 4, 26, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(117, '2026-04-27', 2026, 4, 27, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(118, '2026-04-28', 2026, 4, 28, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(119, '2026-04-29', 2026, 4, 29, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(120, '2026-04-30', 2026, 4, 30, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(121, '2026-05-01', 2026, 5, 1, 5, 'Viernes', 0, 1, 0, 'Día de los Trabajadores', 'Feriado nacional pago.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(122, '2026-05-02', 2026, 5, 2, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(123, '2026-05-03', 2026, 5, 3, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(124, '2026-05-04', 2026, 5, 4, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(125, '2026-05-05', 2026, 5, 5, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(126, '2026-05-06', 2026, 5, 6, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(127, '2026-05-07', 2026, 5, 7, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(128, '2026-05-08', 2026, 5, 8, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(129, '2026-05-09', 2026, 5, 9, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(130, '2026-05-10', 2026, 5, 10, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(131, '2026-05-11', 2026, 5, 11, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(132, '2026-05-12', 2026, 5, 12, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(133, '2026-05-13', 2026, 5, 13, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(134, '2026-05-14', 2026, 5, 14, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(135, '2026-05-15', 2026, 5, 15, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(136, '2026-05-16', 2026, 5, 16, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(137, '2026-05-17', 2026, 5, 17, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(138, '2026-05-18', 2026, 5, 18, 1, 'Lunes', 0, 1, 1, 'Batalla de Las Piedras', 'Conmemoración histórica nacional.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(139, '2026-05-19', 2026, 5, 19, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(140, '2026-05-20', 2026, 5, 20, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(141, '2026-05-21', 2026, 5, 21, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(142, '2026-05-22', 2026, 5, 22, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(143, '2026-05-23', 2026, 5, 23, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(144, '2026-05-24', 2026, 5, 24, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(145, '2026-05-25', 2026, 5, 25, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(146, '2026-05-26', 2026, 5, 26, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(147, '2026-05-27', 2026, 5, 27, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(148, '2026-05-28', 2026, 5, 28, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(149, '2026-05-29', 2026, 5, 29, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(150, '2026-05-30', 2026, 5, 30, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(151, '2026-05-31', 2026, 5, 31, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(152, '2026-06-01', 2026, 6, 1, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(153, '2026-06-02', 2026, 6, 2, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(154, '2026-06-03', 2026, 6, 3, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(155, '2026-06-04', 2026, 6, 4, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(156, '2026-06-05', 2026, 6, 5, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(157, '2026-06-06', 2026, 6, 6, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(158, '2026-06-07', 2026, 6, 7, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(159, '2026-06-08', 2026, 6, 8, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(160, '2026-06-09', 2026, 6, 9, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(161, '2026-06-10', 2026, 6, 10, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(162, '2026-06-11', 2026, 6, 11, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(163, '2026-06-12', 2026, 6, 12, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(164, '2026-06-13', 2026, 6, 13, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(165, '2026-06-14', 2026, 6, 14, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(166, '2026-06-15', 2026, 6, 15, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(167, '2026-06-16', 2026, 6, 16, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(168, '2026-06-17', 2026, 6, 17, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(169, '2026-06-18', 2026, 6, 18, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(170, '2026-06-19', 2026, 6, 19, 5, 'Viernes', 0, 1, 1, 'Natalicio de Artigas', 'Conmemoración del nacimiento de José Gervasio Artigas.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(171, '2026-06-20', 2026, 6, 20, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(172, '2026-06-21', 2026, 6, 21, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(173, '2026-06-22', 2026, 6, 22, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(174, '2026-06-23', 2026, 6, 23, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(175, '2026-06-24', 2026, 6, 24, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(176, '2026-06-25', 2026, 6, 25, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(177, '2026-06-26', 2026, 6, 26, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(178, '2026-06-27', 2026, 6, 27, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(179, '2026-06-28', 2026, 6, 28, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(180, '2026-06-29', 2026, 6, 29, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(181, '2026-06-30', 2026, 6, 30, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(182, '2026-07-01', 2026, 7, 1, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(183, '2026-07-02', 2026, 7, 2, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(184, '2026-07-03', 2026, 7, 3, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(185, '2026-07-04', 2026, 7, 4, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(186, '2026-07-05', 2026, 7, 5, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(187, '2026-07-06', 2026, 7, 6, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(188, '2026-07-07', 2026, 7, 7, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(189, '2026-07-08', 2026, 7, 8, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(190, '2026-07-09', 2026, 7, 9, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(191, '2026-07-10', 2026, 7, 10, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(192, '2026-07-11', 2026, 7, 11, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(193, '2026-07-12', 2026, 7, 12, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(194, '2026-07-13', 2026, 7, 13, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(195, '2026-07-14', 2026, 7, 14, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(196, '2026-07-15', 2026, 7, 15, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(197, '2026-07-16', 2026, 7, 16, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(198, '2026-07-17', 2026, 7, 17, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(199, '2026-07-18', 2026, 7, 18, 6, 'Sábado', 1, 1, 0, 'Jura de la Constitución', 'Feriado nacional pago.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(200, '2026-07-19', 2026, 7, 19, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(201, '2026-07-20', 2026, 7, 20, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(202, '2026-07-21', 2026, 7, 21, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(203, '2026-07-22', 2026, 7, 22, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(204, '2026-07-23', 2026, 7, 23, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(205, '2026-07-24', 2026, 7, 24, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(206, '2026-07-25', 2026, 7, 25, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(207, '2026-07-26', 2026, 7, 26, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(208, '2026-07-27', 2026, 7, 27, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(209, '2026-07-28', 2026, 7, 28, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(210, '2026-07-29', 2026, 7, 29, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(211, '2026-07-30', 2026, 7, 30, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(212, '2026-07-31', 2026, 7, 31, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(213, '2026-08-01', 2026, 8, 1, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(214, '2026-08-02', 2026, 8, 2, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(215, '2026-08-03', 2026, 8, 3, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(216, '2026-08-04', 2026, 8, 4, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(217, '2026-08-05', 2026, 8, 5, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(218, '2026-08-06', 2026, 8, 6, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(219, '2026-08-07', 2026, 8, 7, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(220, '2026-08-08', 2026, 8, 8, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(221, '2026-08-09', 2026, 8, 9, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(222, '2026-08-10', 2026, 8, 10, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(223, '2026-08-11', 2026, 8, 11, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(224, '2026-08-12', 2026, 8, 12, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(225, '2026-08-13', 2026, 8, 13, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(226, '2026-08-14', 2026, 8, 14, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(227, '2026-08-15', 2026, 8, 15, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(228, '2026-08-16', 2026, 8, 16, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(229, '2026-08-17', 2026, 8, 17, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(230, '2026-08-18', 2026, 8, 18, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(231, '2026-08-19', 2026, 8, 19, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(232, '2026-08-20', 2026, 8, 20, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(233, '2026-08-21', 2026, 8, 21, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(234, '2026-08-22', 2026, 8, 22, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(235, '2026-08-23', 2026, 8, 23, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(236, '2026-08-24', 2026, 8, 24, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(237, '2026-08-25', 2026, 8, 25, 2, 'Martes', 0, 1, 0, 'Declaratoria de la Independencia', 'Feriado nacional pago.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(238, '2026-08-26', 2026, 8, 26, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(239, '2026-08-27', 2026, 8, 27, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(240, '2026-08-28', 2026, 8, 28, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(241, '2026-08-29', 2026, 8, 29, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(242, '2026-08-30', 2026, 8, 30, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(243, '2026-08-31', 2026, 8, 31, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(244, '2026-09-01', 2026, 9, 1, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(245, '2026-09-02', 2026, 9, 2, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(246, '2026-09-03', 2026, 9, 3, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(247, '2026-09-04', 2026, 9, 4, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(248, '2026-09-05', 2026, 9, 5, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(249, '2026-09-06', 2026, 9, 6, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(250, '2026-09-07', 2026, 9, 7, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(251, '2026-09-08', 2026, 9, 8, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(252, '2026-09-09', 2026, 9, 9, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(253, '2026-09-10', 2026, 9, 10, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(254, '2026-09-11', 2026, 9, 11, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(255, '2026-09-12', 2026, 9, 12, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(256, '2026-09-13', 2026, 9, 13, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(257, '2026-09-14', 2026, 9, 14, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(258, '2026-09-15', 2026, 9, 15, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(259, '2026-09-16', 2026, 9, 16, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(260, '2026-09-17', 2026, 9, 17, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(261, '2026-09-18', 2026, 9, 18, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(262, '2026-09-19', 2026, 9, 19, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(263, '2026-09-20', 2026, 9, 20, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(264, '2026-09-21', 2026, 9, 21, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(265, '2026-09-22', 2026, 9, 22, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(266, '2026-09-23', 2026, 9, 23, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(267, '2026-09-24', 2026, 9, 24, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(268, '2026-09-25', 2026, 9, 25, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(269, '2026-09-26', 2026, 9, 26, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(270, '2026-09-27', 2026, 9, 27, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(271, '2026-09-28', 2026, 9, 28, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(272, '2026-09-29', 2026, 9, 29, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(273, '2026-09-30', 2026, 9, 30, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(274, '2026-10-01', 2026, 10, 1, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(275, '2026-10-02', 2026, 10, 2, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(276, '2026-10-03', 2026, 10, 3, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(277, '2026-10-04', 2026, 10, 4, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(278, '2026-10-05', 2026, 10, 5, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(279, '2026-10-06', 2026, 10, 6, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(280, '2026-10-07', 2026, 10, 7, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(281, '2026-10-08', 2026, 10, 8, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(282, '2026-10-09', 2026, 10, 9, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(283, '2026-10-10', 2026, 10, 10, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(284, '2026-10-11', 2026, 10, 11, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(285, '2026-10-12', 2026, 10, 12, 1, 'Lunes', 0, 1, 1, 'Día de la Raza', 'Conmemoración nacional.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(286, '2026-10-13', 2026, 10, 13, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(287, '2026-10-14', 2026, 10, 14, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(288, '2026-10-15', 2026, 10, 15, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(289, '2026-10-16', 2026, 10, 16, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(290, '2026-10-17', 2026, 10, 17, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(291, '2026-10-18', 2026, 10, 18, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(292, '2026-10-19', 2026, 10, 19, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(293, '2026-10-20', 2026, 10, 20, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(294, '2026-10-21', 2026, 10, 21, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(295, '2026-10-22', 2026, 10, 22, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(296, '2026-10-23', 2026, 10, 23, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(297, '2026-10-24', 2026, 10, 24, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(298, '2026-10-25', 2026, 10, 25, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(299, '2026-10-26', 2026, 10, 26, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(300, '2026-10-27', 2026, 10, 27, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(301, '2026-10-28', 2026, 10, 28, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(302, '2026-10-29', 2026, 10, 29, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(303, '2026-10-30', 2026, 10, 30, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(304, '2026-10-31', 2026, 10, 31, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(305, '2026-11-01', 2026, 11, 1, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(306, '2026-11-02', 2026, 11, 2, 1, 'Lunes', 0, 1, 1, 'Día de los Difuntos', 'Feriado tradicional en Uruguay.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:53', '2026-05-22 16:41:10'),
(307, '2026-11-03', 2026, 11, 3, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(308, '2026-11-04', 2026, 11, 4, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(309, '2026-11-05', 2026, 11, 5, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:53', '2026-05-22 16:40:53'),
(310, '2026-11-06', 2026, 11, 6, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(311, '2026-11-07', 2026, 11, 7, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(312, '2026-11-08', 2026, 11, 8, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(313, '2026-11-09', 2026, 11, 9, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(314, '2026-11-10', 2026, 11, 10, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(315, '2026-11-11', 2026, 11, 11, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(316, '2026-11-12', 2026, 11, 12, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(317, '2026-11-13', 2026, 11, 13, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(318, '2026-11-14', 2026, 11, 14, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(319, '2026-11-15', 2026, 11, 15, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(320, '2026-11-16', 2026, 11, 16, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(321, '2026-11-17', 2026, 11, 17, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(322, '2026-11-18', 2026, 11, 18, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(323, '2026-11-19', 2026, 11, 19, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(324, '2026-11-20', 2026, 11, 20, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(325, '2026-11-21', 2026, 11, 21, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(326, '2026-11-22', 2026, 11, 22, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(327, '2026-11-23', 2026, 11, 23, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(328, '2026-11-24', 2026, 11, 24, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(329, '2026-11-25', 2026, 11, 25, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(330, '2026-11-26', 2026, 11, 26, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(331, '2026-11-27', 2026, 11, 27, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(332, '2026-11-28', 2026, 11, 28, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(333, '2026-11-29', 2026, 11, 29, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(334, '2026-11-30', 2026, 11, 30, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(335, '2026-12-01', 2026, 12, 1, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(336, '2026-12-02', 2026, 12, 2, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(337, '2026-12-03', 2026, 12, 3, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(338, '2026-12-04', 2026, 12, 4, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(339, '2026-12-05', 2026, 12, 5, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(340, '2026-12-06', 2026, 12, 6, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(341, '2026-12-07', 2026, 12, 7, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(342, '2026-12-08', 2026, 12, 8, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(343, '2026-12-09', 2026, 12, 9, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(344, '2026-12-10', 2026, 12, 10, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(345, '2026-12-11', 2026, 12, 11, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(346, '2026-12-12', 2026, 12, 12, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(347, '2026-12-13', 2026, 12, 13, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(348, '2026-12-14', 2026, 12, 14, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(349, '2026-12-15', 2026, 12, 15, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(350, '2026-12-16', 2026, 12, 16, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(351, '2026-12-17', 2026, 12, 17, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(352, '2026-12-18', 2026, 12, 18, 5, 'Viernes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(353, '2026-12-19', 2026, 12, 19, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(354, '2026-12-20', 2026, 12, 20, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(355, '2026-12-21', 2026, 12, 21, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(356, '2026-12-22', 2026, 12, 22, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(357, '2026-12-23', 2026, 12, 23, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(358, '2026-12-24', 2026, 12, 24, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54');
INSERT INTO `calendario` (`id_calendario`, `fecha`, `ano`, `mes`, `dia`, `numero_dia_semana`, `nombre_dia_semana`, `es_fin_semana`, `es_feriado`, `es_dia_habil`, `nombre_feriado`, `descripcion_feriado`, `tipo`, `color`, `visible`, `creado_en`, `actualizado_en`) VALUES
(359, '2026-12-25', 2026, 12, 25, 5, 'Viernes', 0, 1, 0, 'Navidad / Día de la Familia', 'Feriado nacional pago.', 'feriado', '#dc3545', 1, '2026-05-22 16:40:54', '2026-05-22 16:41:10'),
(360, '2026-12-26', 2026, 12, 26, 6, 'Sábado', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(361, '2026-12-27', 2026, 12, 27, 7, 'Domingo', 1, 0, 0, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(362, '2026-12-28', 2026, 12, 28, 1, 'Lunes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(363, '2026-12-29', 2026, 12, 29, 2, 'Martes', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(364, '2026-12-30', 2026, 12, 30, 3, 'Miércoles', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54'),
(365, '2026-12-31', 2026, 12, 31, 4, 'Jueves', 0, 0, 1, NULL, NULL, 'normal', '#0d6efd', 1, '2026-05-22 16:40:54', '2026-05-22 16:40:54');

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
-- Estructura de tabla para la tabla `dia_semana`
--

CREATE TABLE `dia_semana` (
  `id_dia_semana` tinyint(4) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `nombre_corto` varchar(10) NOT NULL,
  `orden` tinyint(4) NOT NULL,
  `es_fin_semana` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `dia_semana`
--

INSERT INTO `dia_semana` (`id_dia_semana`, `nombre`, `nombre_corto`, `orden`, `es_fin_semana`) VALUES
(1, 'Lunes', 'Lun', 1, 0),
(2, 'Martes', 'Mar', 2, 0),
(3, 'Miércoles', 'Mié', 3, 0),
(4, 'Jueves', 'Jue', 4, 0),
(5, 'Viernes', 'Vie', 5, 0),
(6, 'Sábado', 'Sáb', 6, 1),
(7, 'Domingo', 'Dom', 7, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL,
  `titulo` varchar(180) NOT NULL,
  `slug` varchar(220) DEFAULT NULL,
  `descripcion_corta` varchar(300) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_termino` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_termino` time DEFAULT NULL,
  `ubicacion` varchar(180) DEFAULT NULL,
  `categoria` varchar(80) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `estado` enum('borrador','publicado','oculto','cancelado') NOT NULL DEFAULT 'publicado',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id_evento`, `titulo`, `slug`, `descripcion_corta`, `descripcion`, `fecha_inicio`, `fecha_termino`, `hora_inicio`, `hora_termino`, `ubicacion`, `categoria`, `color`, `imagen`, `archivo_adjunto`, `destacado`, `visible`, `orden`, `estado`, `creado_en`, `actualizado_en`) VALUES
(5, 'Misa Institucional', NULL, 'Eucaristia en comunidad.', 'Descripcion completa del evento.', '2026-05-22', '2026-05-22', '09:00:00', '10:00:00', 'Capilla del Colegio', 'Pastoral', '#8e44ad', NULL, NULL, 1, 1, 1, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(6, 'Feria Científicaaaaa 2026', NULL, 'Exposición de proyectos científicos', 'Jornada donde estudiantes presentan experimentos y proyectos innovadores desarrollados durante el semestre.', '2026-05-22', '2026-05-22', '09:00:00', '13:30:00', 'Gimnasio Colegio San Pablo', 'Académico', '#2563eb', 'uploads/eventos/20251107131854-20260524134314-253f1f.png', NULL, 0, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 18:34:03'),
(7, 'Día del Deporte', NULL, 'Actividades deportivas institucionales', 'Competencias recreativas y deportivas para fomentar el trabajo en equipo y vida saludable.', '2026-05-24', '2026-05-24', '08:30:00', '14:00:00', 'Cancha principal', 'Deportivo', '#16a34a', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(8, 'Misa Institucional', NULL, 'Eucaristía comunitaria', 'Celebración religiosa junto a estudiantes, docentes y apoderados.', '2026-05-25', '2026-05-25', '10:00:00', '11:30:00', 'Capilla del Colegio', 'Pastoral', '#9333ea', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(9, 'Semana de la Lectura', NULL, 'Fomento lector', 'Actividades orientadas a incentivar la lectura y comprensión lectora.', '2026-06-02', '2026-06-06', '09:00:00', '12:00:00', 'Biblioteca Central', 'Académico', '#0ea5e9', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(10, 'Campeonato de Futbol', NULL, 'Encuentro deportivo interescolar', 'Torneo amistoso entre distintos niveles educativos del colegio.', '2026-06-08', '2026-06-08', '09:00:00', '16:00:00', 'Cancha sintética', 'Deportivo', '#22c55e', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(11, 'Taller de Robótica', NULL, 'Innovación tecnológica', 'Estudiantes aprenden programación y automatización con robots educativos.', '2026-06-12', '2026-06-12', '14:00:00', '17:00:00', 'Laboratorio de Computación', 'Académico', '#3b82f6', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(12, 'Festival de Talentos', NULL, 'Presentaciones artísticas', 'Muestra musical, danza y teatro preparada por los estudiantes.', '2026-06-18', '2026-06-18', '18:00:00', '21:00:00', 'Auditorio Principal', 'Cultural', '#ec4899', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(13, 'Jornada de Convivencia', NULL, 'Integración estudiantil', 'Actividades grupales para fortalecer el compañerismo y la convivencia escolar.', '2026-06-21', '2026-06-21', '09:30:00', '13:00:00', 'Patio Central', 'Institucional', '#f59e0b', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(14, 'Charla Universitaria', NULL, 'Orientación vocacional', 'Universidades invitadas presentan carreras y beneficios estudiantiles.', '2026-06-24', '2026-06-24', '11:00:00', '13:00:00', 'Salón Multimedia', 'Académico', '#2563eb', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(15, 'Celebración Día del Profesor', NULL, 'Reconocimiento docente', 'Actividad especial organizada por estudiantes y directivos.', '2026-07-01', '2026-07-01', '12:00:00', '14:00:00', 'Casino del Colegio', 'Institucional', '#f97316', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(16, 'Encuentro de Debate', NULL, 'Competencia académica', 'Debates sobre actualidad y pensamiento crítico entre estudiantes.', '2026-07-05', '2026-07-05', '10:00:00', '15:00:00', 'Sala de Conferencias', 'Académico', '#4f46e5', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(17, 'Salida Pedagógica', NULL, 'Museo de Ciencias', 'Visita educativa guiada para reforzar contenidos de ciencias naturales.', '2026-07-10', '2026-07-10', '08:00:00', '17:00:00', 'Museo Nacional de Ciencias', 'Académico', '#0f766e', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(18, 'Encuentro Pastoral', NULL, 'Jornada espiritual', 'Reflexión y actividades pastorales para toda la comunidad educativa.', '2026-07-15', '2026-07-15', '09:00:00', '12:30:00', 'Centro Pastoral', 'Pastoral', '#7c3aed', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(19, 'Expo Arte Estudiantil', NULL, 'Muestra artística', 'Exposición de pinturas, esculturas y trabajos creativos de estudiantes.', '2026-07-18', '2026-07-18', '10:00:00', '18:00:00', 'Galería Escolar', 'Cultural', '#db2777', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(20, 'Ceremonia de Premiación', NULL, 'Reconocimiento académico', 'Premiación a estudiantes destacados del semestre.', '2026-07-25', '2026-07-25', '17:00:00', '19:30:00', 'Auditorio Principal', 'Institucional', '#dc2626', NULL, NULL, 1, 1, 0, 'publicado', '2026-05-24 17:40:51', '2026-05-24 17:40:51'),
(21, 'EVENTO DE PRUEBA', NULL, 'evento prueba descripción corta', 'evento prueba descripción completa', '2026-05-23', '2026-05-23', '16:46:00', '19:47:00', 'Venancio Benavídez 3612, 11700 Montevideo,', 'Deportivo', '#32fd17', 'uploads/eventos/portada-2-20260524134811-655672.jpg', NULL, 0, 1, 0, 'publicado', '2026-05-24 17:48:11', '2026-05-24 17:48:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evento_media`
--

CREATE TABLE `evento_media` (
  `id_media` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `tipo` enum('imagen','video','youtube') NOT NULL DEFAULT 'imagen',
  `archivo` varchar(255) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `titulo` varchar(180) DEFAULT NULL,
  `descripcion` varchar(300) DEFAULT NULL,
  `portada` tinyint(1) NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `evento_media`
--

INSERT INTO `evento_media` (`id_media`, `id_evento`, `tipo`, `archivo`, `url`, `titulo`, `descripcion`, `portada`, `visible`, `orden`, `creado_en`) VALUES
(13, 6, 'youtube', NULL, 'https://www.youtube.com/watch?v=PHcmZKZ2xVI&list=RDMMPHcmZKZ2xVI&start_radio=1', '', NULL, 0, 1, 44594, '2026-05-24 17:43:14'),
(12, 6, 'imagen', 'uploads/eventos/media/6/20251107131854222-20260524134314-265896.png', NULL, '20251107131854222', NULL, 0, 1, 44594, '2026-05-24 17:43:14'),
(11, 6, 'imagen', 'uploads/eventos/media/6/20251107131958-20260524134314-54a6b3.png', NULL, '20251107131958', NULL, 0, 1, 44594, '2026-05-24 17:43:14'),
(10, 6, 'imagen', 'uploads/eventos/media/6/20251107131936-20260524134314-ea198c.png', NULL, '20251107131936', NULL, 0, 1, 44594, '2026-05-24 17:43:14'),
(9, 6, 'imagen', 'uploads/eventos/media/6/20251107131913-20260524134314-1ba93a.png', NULL, '20251107131913', NULL, 0, 1, 44594, '2026-05-24 17:43:14'),
(8, 6, 'imagen', 'uploads/eventos/media/6/20251107131854-20260524134314-6ed5ef.png', NULL, '20251107131854', NULL, 0, 1, 44594, '2026-05-24 17:43:14'),
(14, 21, 'imagen', 'uploads/eventos/media/21/alumno-1-20260524134811-291c94.jpg', NULL, 'alumno_1', NULL, 0, 1, 44892, '2026-05-24 17:48:12'),
(15, 21, 'imagen', 'uploads/eventos/media/21/alumno-2-20260524134812-277029.jpg', NULL, 'alumno_2', NULL, 0, 1, 44892, '2026-05-24 17:48:12'),
(16, 21, 'imagen', 'uploads/eventos/media/21/alumno-3-20260524134812-4bb041.jpg', NULL, 'alumno_3', NULL, 0, 1, 44892, '2026-05-24 17:48:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institucion`
--

CREATE TABLE `institucion` (
  `id_institucion` int(11) NOT NULL,
  `id_tipo_institucion` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `nombre_corto` varchar(100) DEFAULT NULL,
  `eslogan` varchar(255) DEFAULT NULL,
  `descripcion_corta` text DEFAULT NULL,
  `descripcion_larga` longtext DEFAULT NULL,
  `dominio` varchar(150) DEFAULT NULL,
  `logo_header` varchar(255) DEFAULT NULL,
  `logo_footer` varchar(255) DEFAULT NULL,
  `logo_blanco` varchar(255) DEFAULT NULL,
  `logo_mobile` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `imagen_login` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `latitud` varchar(50) DEFAULT NULL,
  `longitud` varchar(50) DEFAULT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `horario_atencion` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `email_soporte` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `color_primario` varchar(20) DEFAULT '#2563EB',
  `color_secundario` varchar(20) DEFAULT '#E9A629',
  `color_terciario` varchar(20) DEFAULT '#222222',
  `color_cuaternario` varchar(20) DEFAULT '#F8F8F8',
  `fuente_principal` varchar(100) DEFAULT NULL,
  `usar_gradientes` tinyint(1) DEFAULT 1,
  `usar_modo_oscuro` tinyint(1) DEFAULT 0,
  `texto_boton_principal` varchar(100) DEFAULT NULL,
  `url_boton_principal` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `texto_footer` text DEFAULT NULL,
  `copyright` text DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `institucion`
--

INSERT INTO `institucion` (`id_institucion`, `id_tipo_institucion`, `nombre`, `nombre_corto`, `eslogan`, `descripcion_corta`, `descripcion_larga`, `dominio`, `logo_header`, `logo_footer`, `logo_blanco`, `logo_mobile`, `favicon`, `imagen_portada`, `imagen_login`, `direccion`, `ciudad`, `region`, `pais`, `latitud`, `longitud`, `telefono`, `whatsapp`, `horario_atencion`, `email`, `email_soporte`, `facebook`, `instagram`, `youtube`, `linkedin`, `color_primario`, `color_secundario`, `color_terciario`, `color_cuaternario`, `fuente_principal`, `usar_gradientes`, `usar_modo_oscuro`, `texto_boton_principal`, `url_boton_principal`, `meta_title`, `meta_description`, `meta_keywords`, `og_image`, `texto_footer`, `copyright`, `estado`, `fecha_creacion`) VALUES
(1, 1, 'Colegio San Pablo', 'San Pablo', NULL, NULL, NULL, 'https://sanpablo.edu.uy/', 'uploads/institucion/icono-ppt-20260524151354-9654be.png', 'uploads/institucion/logo_footer.png', NULL, NULL, 'uploads/institucion/logo-horiz-20260524151354-05c771.png', NULL, NULL, 'Venancio Benavidez 3612, 11700 Montevideo', 'Montevideo', 'Departamento de Montevideo', 'Uruguay', NULL, NULL, '+598 2337 3737', NULL, 'Abre a las 7 a. m. de lunes a viernes', 'info@sanpablo.edu.uy', NULL, '#', 'https://www.instagram.com/colegioyliceosanpablo/', NULL, NULL, '#F0A000', '#EF6C00', '#1976D2', '#E53935', 'Poppins', 1, 0, 'Matrícula', '#', 'Colegio San Pablo', 'Sitio oficial del Colegio San Pablo. Información institucional, noticias, calendario de eventos y comunidad educativa.', 'Colegio San Pablo, Montevideo, educación, liceo, colegio, Uruguay', NULL, 'Colegio San Pablo acompaña a su comunidad educativa con una propuesta integral, cercana y orientada a la formación académica y humana.', '© 2026 Colegio San Pablo. Todos los derechos reservados.', 'activo', '2026-04-19 19:39:20');

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
(3, 1, 'hero_principal', 'Carrusel principal', 'Carrusel destacado del home con slides, imagenes y botones principales.', 'carousel', 'texto_izquierda', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 3, '2026-04-19 19:39:21'),
(4, 1, 'noticias_home', 'Noticias home', 'Bloque de noticias destacadas del home con categoria, imagen y fecha.', 'news', 'cards_4', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 4, '2026-04-19 19:39:21'),
(5, 1, 'faq_home', 'Preguntas frecuentes', 'Contenedor de preguntas frecuentes con acordeon e imagen lateral.', 'faq', 'imagen_lateral', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 8, '2026-04-19 19:39:21'),
(6, 1, 'about_home', 'Sobre nosotros', 'Bloque institucional de presentacion con imagen principal, video y descripcion.', 'content', 'imagen_texto', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 9, '2026-04-19 19:39:21'),
(7, 1, 'footer_principal', 'Footer principal', 'Este es el contenedor del footer. Aqui se muestran logo, descripcion institucional, enlaces rapidos, contacto, redes sociales y datos principales del sitio.', 'footer', 'institucional', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 10, '2026-04-19 20:56:57'),
(11, 1, 'header_principal', 'Header principal', 'Bloque visual completo del encabezado. Incluye logo, identidad institucional, navegacion horizontal basada en menus y sub_menus, y boton principal.', 'header', 'branding', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 2, '2026-04-19 21:46:37'),
(19, 1, 'menu_principal', 'Menú principal', 'Bloque de compatibilidad. La navegacion ya esta absorbida visualmente dentro de header_principal, pero sus enlaces siguen saliendo de menus y sub_menus.', 'menu', 'navegacion_principal', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 3, '2026-04-20 15:59:24'),
(20, 1, 'calendario_eventos_home', 'Calendario de eventos', 'Contenedor del home que muestra calendario institucional y próximos eventos.', 'events', 'calendario_lista', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 5, '2026-05-22 12:44:54'),
(21, 1, 'video_destacado_home', 'Video destacado', 'Contenedor del home con banner de video destacado basado en template_07.', 'video', 'banner_video', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 6, '2026-05-22 13:29:19'),
(22, 1, 'galeria_home', 'Galería home', 'Contenedor del home con galería visual tipo carrusel basado en template_07.', 'gallery', 'slider_seven', 'si', 'activo', 'si', 'si', 'no', NULL, NULL, NULL, 7, '2026-05-22 13:29:19');

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
(116, 5, 'imagen_lateral', 'assets/images/20251107131936.png'),
(117, 5, 'subtitulo_bloque', 'PREGUNTAS FRECUENTES'),
(118, 5, 'titulo_bloque', 'Siempre nos aseguramos de que el mejor curso esté listo para aprender.en colegio san pablo'),
(136, 6, 'descripcion_bloque', 'duuc antoni varas'),
(137, 6, 'imagen_principal', 'assets/images/about/about-two-image1.png'),
(138, 6, 'subtitulo_bloque', 'SOBRE NOSOTROS'),
(139, 6, 'titulo_bloque', 'Aprende Nuevas Habilidades para Crecer'),
(140, 6, 'video_url', 'https://www.youtube.com/watch?v=oKu4GAeGjp8'),
(171, 1, 'texto_boton_ingresar', 'Ingresar'),
(172, 1, 'mostrar_direccion', 'si'),
(173, 1, 'mostrar_telefono', 'si'),
(174, 1, 'mostrar_email', 'si'),
(175, 1, 'mostrar_redes', 'si'),
(176, 1, 'mostrar_boton_ingresar', 'si');

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
(3, 4, NULL, '', NULL, 'Rugby del Prado', '', '', '', '', 'Nuestros alumnos participaron en el torneo intercolegial de rugby con excelentes resultados.', 'uploads/noticias/portada-1-20260524151506-5de9b2.jpg', NULL, 'Leer más', '#', '', '', '', '2025-11-01', 'si', 1, '2026-04-19 19:39:21'),
(4, 4, 2, NULL, NULL, 'Certificaciones Inglés y Portugués', NULL, NULL, NULL, NULL, 'Alumnos de bachillerato rindieron y aprobaron certificaciones internacionales de idiomas.', 'assets/images/frontis_02.jpg', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-01', 'si', 2, '2026-04-19 19:39:21'),
(6, 4, 2, NULL, NULL, 'Certificaciones Inglés y Portugués', NULL, NULL, NULL, NULL, 'Alumnos de bachillerato rindieron y aprobaron certificaciones internacionales de idiomas.', 'assets/images/frontis_02.jpg', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-01', 'si', 2, '2026-04-19 19:39:21'),
(7, 5, NULL, NULL, NULL, '¿Cuánto dura el proceso de admisión?', NULL, NULL, NULL, NULL, 'Nuestro equipo acompaña a las familias con información clara, apoyo cercano y un proceso educativo pensado para el desarrollo integral de cada estudiante.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'si', 1, '2026-04-19 19:39:21'),
(8, 5, NULL, NULL, NULL, '¿Qué incluye la propuesta educativa?', NULL, NULL, NULL, NULL, 'Incluye acompañamiento cercano, formación en valores y una propuesta académica integral para cada etapa.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'si', 2, '2026-04-19 19:39:21'),
(9, 3, NULL, '', NULL, '', 'Guadalupe', 'jorquera', 'titulo 3', '', 'contenedor de prueba', 'uploads/secciones/hero_principal/img-6939-20260524133308-4d0fad.jpg', 'uploads/secciones/hero_principal/imagen-fondo-20260514191258-82a3e9.jpg', '', '', '', '', '', NULL, 'si', 1, '2026-04-19 19:44:28'),
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
(20, 3, NULL, 'eticketa ej', NULL, '', 'titulo 1 ejemplo', 'titulo 2 ejemplo', 'titulo 3 ejemplo', '', 'descripcion ejemplo', 'uploads/secciones/hero_principal/member-3-20260524133327-11a954.jpg', 'uploads/secciones/hero_principal/lupe-04-20260420113602-ee9305.jpg', 'botón 1', 'botón url', 'botón  2', 'botón url', '', NULL, 'si', 1, '2026-04-20 11:30:02'),
(21, 3, NULL, '', NULL, '', '', '', '', '', '', 'uploads/secciones/hero_principal/portada-2-20260420160745-fb6e8c.jpg', NULL, '', '', '', '', NULL, NULL, 'si', 5, '2026-04-20 11:52:20'),
(22, 5, NULL, '', NULL, 'cuanto pares son 3 moscas', '', '', '', 'subtitublo de la pregunta', 'yo creo qeu bla bla bla bla', 'uploads/secciones/faq_home/20251107131854-20260420164615-60b934.png', NULL, '', '', '', '', NULL, NULL, 'no', 1, '2026-04-20 16:46:15'),
(23, 20, NULL, '', NULL, '', '', '', '', '', 'ejemplo de hoy', NULL, NULL, 'Ver detalle', '', '', '', '', '2026-05-22', 'si', 1, '2026-05-22 13:38:12'),
(24, 21, NULL, '', NULL, '', '', '', '', '', '', 'uploads/secciones/video_destacado_home/img-6939-20260524133007-264b78.jpg', NULL, '', '', '', '', '', NULL, 'si', 1, '2026-05-23 13:34:41');

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
  `usuario` varchar(150) DEFAULT NULL,
  `clave` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `rol` enum('super_admin','admin_institucion','editor') NOT NULL DEFAULT 'admin_institucion',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `intento_fallido` int(11) DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_institucion`, `nombre`, `apellido`, `email`, `usuario`, `clave`, `foto`, `rol`, `estado`, `intento_fallido`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 'Cristian', 'Jorquera', 'cm.jorquerag@gmail.com', 'cm.jorquerag@gmail.com', 'Ingeniero186#', NULL, '', 'activo', 0, '2026-04-21 10:56:01', '2026-04-21 10:56:01'),
(2, 1, 'Marcos', '', 'marcos@admin.cl', 'marcos@admin.cl', 'admin123!', NULL, '', 'activo', 0, '2026-04-21 10:56:01', '2026-04-21 10:56:01'),
(3, 1, 'Jonathan', 'Vergara', 'jonatan@gmail.com', 'jonatan@gmail.com', 'admin123!', NULL, '', 'activo', 0, '2026-04-21 10:56:01', '2026-04-21 10:56:01');

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
-- Indices de la tabla `auditoria_log`
--
ALTER TABLE `auditoria_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_tabla_registro` (`tabla_afectada`,`id_registro`),
  ADD KEY `idx_fecha` (`fecha_hora`);

--
-- Indices de la tabla `calendario`
--
ALTER TABLE `calendario`
  ADD PRIMARY KEY (`id_calendario`),
  ADD UNIQUE KEY `fecha` (`fecha`);

--
-- Indices de la tabla `categoria_noticia`
--
ALTER TABLE `categoria_noticia`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `dia_semana`
--
ALTER TABLE `dia_semana`
  ADD PRIMARY KEY (`id_dia_semana`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id_evento`);

--
-- Indices de la tabla `evento_media`
--
ALTER TABLE `evento_media`
  ADD PRIMARY KEY (`id_media`),
  ADD KEY `idx_evento` (`id_evento`);

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
  ADD UNIQUE KEY `uq_seccion_institucion_nombre` (`id_institucion`,`nombre_interno`);

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
-- Indices de la tabla `usuario_perfil`
--
ALTER TABLE `usuario_perfil`
  ADD PRIMARY KEY (`id_usuario`,`id_perfil`),
  ADD KEY `fk_usuario_perfil_perfil` (`id_perfil`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria_log`
--
ALTER TABLE `auditoria_log`
  MODIFY `id_log` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de la tabla `calendario`
--
ALTER TABLE `calendario`
  MODIFY `id_calendario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=366;

--
-- AUTO_INCREMENT de la tabla `categoria_noticia`
--
ALTER TABLE `categoria_noticia`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `evento_media`
--
ALTER TABLE `evento_media`
  MODIFY `id_media` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
  MODIFY `id_seccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `seccion_config`
--
ALTER TABLE `seccion_config`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT de la tabla `seccion_item`
--
ALTER TABLE `seccion_item`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  ADD CONSTRAINT `fk_usuario_perfil_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
