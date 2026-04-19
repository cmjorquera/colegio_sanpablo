-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 19-04-2026 a las 14:01:32
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
(3, 'Maternal', '', '', NULL, 3, 0, '2026-04-14', '18:43:29', '127.0.0.1'),
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
-- Estructura de tabla para la tabla `menus_`
--

CREATE TABLE `menus_` (
  `id_menu` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `id_padre` int(11) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `menus_`
--

INSERT INTO `menus_` (`id_menu`, `nombre`, `url`, `icono`, `id_padre`, `orden`, `activo`, `visible`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Inicio', 'index.php', 'bi bi-house-door', NULL, 1, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(2, 'Institucional', NULL, 'bi bi-building', NULL, 2, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(3, 'Material', NULL, 'bi bi-journal-bookmark', NULL, 3, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(4, 'Inicial', NULL, 'bi bi-balloon-heart', NULL, 4, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(5, 'Primaria', NULL, 'bi bi-book', NULL, 5, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(6, '3er Ciclo EBI', NULL, 'bi bi-mortarboard', NULL, 6, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(7, 'Bachillerato', NULL, 'bi bi-mortarboard-fill', NULL, 7, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(8, 'Libre Asistido', NULL, 'bi bi-person-workspace', NULL, 8, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(9, 'Confesionalidad', NULL, 'bi bi-stars', NULL, 9, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(10, 'Biblioteca', NULL, 'bi bi-bookshelf', NULL, 10, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(11, 'Mi San Pablo', NULL, 'bi bi-person-circle', NULL, 11, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(12, 'Presentación', 'institucional/presentacion.php', 'bi bi-chevron-right', 2, 1, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(13, 'Historia', 'institucional/historia.php', 'bi bi-chevron-right', 2, 2, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(14, 'Logotipo y Lema', 'institucional/logotipo-y-lema.php', 'bi bi-chevron-right', 2, 3, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(15, 'Visión y Misión', 'institucional/vision-y-mision.php', 'bi bi-chevron-right', 2, 4, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(16, 'Principios de Identidad', 'institucional/principios-de-identidad.php', 'bi bi-chevron-right', 2, 5, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(17, 'Propuesta Pedagógica', 'institucional/propuesta-pedagogica.php', 'bi bi-chevron-right', 2, 6, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(18, 'Perfil del Alumno', 'institucional/perfil-del-alumno.php', 'bi bi-chevron-right', 2, 7, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(19, 'Estructura Física', 'institucional/estructura-fisica.php', 'bi bi-chevron-right', 2, 8, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(20, 'Administración', 'institucional/administracion.php', 'bi bi-chevron-right', 2, 9, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(21, 'Presentación', 'material/presentacion.php', 'bi bi-chevron-right', 3, 1, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(22, 'Grupos y Horarios', 'material/grupos-y-horarios.php', 'bi bi-chevron-right', 3, 2, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(23, 'Propuesta Bilingüe', 'material/propuesta-bilingue.php', 'bi bi-chevron-right', 3, 3, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(24, 'Actividades', 'material/actividades.php', 'bi bi-chevron-right', 3, 4, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(25, 'Presentación', 'inicial/presentacion.php', 'bi bi-chevron-right', 4, 1, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(26, 'Propuesta Curricular', 'inicial/propuesta-curricular.php', 'bi bi-chevron-right', 4, 2, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(27, 'Propuesta Bilingüe', 'inicial/propuesta-bilingue.php', 'bi bi-chevron-right', 4, 3, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(28, 'Actividades', 'inicial/actividades.php', 'bi bi-chevron-right', 4, 4, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(29, 'Presentación', 'primaria/presentacion.php', 'bi bi-chevron-right', 5, 1, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(30, 'Propuesta Curricular', 'primaria/propuesta-curricular.php', 'bi bi-chevron-right', 5, 2, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(31, 'Propuesta Bilingüe', 'primaria/propuesta-bilingue.php', 'bi bi-chevron-right', 5, 3, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(32, 'Actividades Extracurriculares', 'primaria/actividades-extracurriculares.php', 'bi bi-chevron-right', 5, 4, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(33, 'Biblioteca', 'primaria/biblioteca.php', 'bi bi-chevron-right', 5, 5, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(34, 'Presentación', 'tercer-ciclo-ebi/presentacion.php', 'bi bi-chevron-right', 6, 1, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(35, 'Propuesta Curricular', 'tercer-ciclo-ebi/propuesta-curricular.php', 'bi bi-chevron-right', 6, 2, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(36, 'Propuesta Bilingüe', 'tercer-ciclo-ebi/propuesta-bilingue.php', 'bi bi-chevron-right', 6, 3, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(37, 'Actividades Extracurriculares', 'tercer-ciclo-ebi/actividades-extracurriculares.php', 'bi bi-chevron-right', 6, 4, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(38, 'Servicios Educativos', 'tercer-ciclo-ebi/servicios-educativos.php', 'bi bi-chevron-right', 6, 5, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(39, 'Presentación', 'libre-asistido/presentacion.php', 'bi bi-chevron-right', 8, 1, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(40, 'Inscripciones', 'libre-asistido/inscripciones.php', 'bi bi-chevron-right', 8, 2, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(41, 'Identidad', 'confesionalidad/identidad.php', 'bi bi-chevron-right', 9, 1, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(42, 'Visión', 'confesionalidad/vision.php', 'bi bi-chevron-right', 9, 2, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(43, 'La Confesionalidad en la Práctica', 'confesionalidad/la-confesionalidad-en-la-practica.php', 'bi bi-chevron-right', 9, 3, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(44, 'Capellanía', 'confesionalidad/capellania.php', 'bi bi-chevron-right', 9, 4, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(45, 'Educación Cristiana', 'confesionalidad/educacion-cristiana.php', 'bi bi-chevron-right', 9, 5, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(46, 'Iglesia Luterana', 'confesionalidad/iglesia-luterana.php', 'bi bi-chevron-right', 9, 6, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(47, 'Presentación', 'biblioteca/presentacion.php', 'bi bi-chevron-right', 10, 1, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(48, 'La Función de la Biblioteca', 'biblioteca/la-funcion-de-la-biblioteca.php', 'bi bi-chevron-right', 10, 2, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(49, 'Objetivos', 'biblioteca/objetivos.php', 'bi bi-chevron-right', 10, 3, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(50, 'Área Alumnos', 'mi-san-pablo/area-alumnos.php', 'bi bi-chevron-right', 11, 1, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(51, 'Área Padres', 'mi-san-pablo/area-padres.php', 'bi bi-chevron-right', 11, 2, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(52, 'Área Funcionarios', 'mi-san-pablo/area-funcionarios.php', 'bi bi-chevron-right', 11, 3, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(53, 'Área Docentes', 'mi-san-pablo/area-docentes.php', 'bi bi-chevron-right', 11, 4, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42'),
(54, 'Webmail', 'mi-san-pablo/webmail.php', 'bi bi-chevron-right', 11, 5, 1, 1, '2026-03-27 03:19:42', '2026-03-27 03:19:42');

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
-- Indices de la tabla `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indices de la tabla `menus_`
--
ALTER TABLE `menus_`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `fk_menu_padre` (`id_padre`);

--
-- Indices de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  ADD PRIMARY KEY (`id_perfil`),
  ADD UNIQUE KEY `nombre_perfil` (`nombre_perfil`);

--
-- Indices de la tabla `sub_menus`
--
ALTER TABLE `sub_menus`
  ADD PRIMARY KEY (`id_sub_menu`),
  ADD UNIQUE KEY `uq_menu_submenu` (`id_menu`,`nombre`);

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
-- AUTO_INCREMENT de la tabla `menus`
--
ALTER TABLE `menus`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `menus_`
--
ALTER TABLE `menus_`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  MODIFY `id_perfil` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sub_menus`
--
ALTER TABLE `sub_menus`
  MODIFY `id_sub_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `menus_`
--
ALTER TABLE `menus_`
  ADD CONSTRAINT `fk_menu_padre` FOREIGN KEY (`id_padre`) REFERENCES `menus_` (`id_menu`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sub_menus`
--
ALTER TABLE `sub_menus`
  ADD CONSTRAINT `fk_submenu_menu` FOREIGN KEY (`id_menu`) REFERENCES `menus` (`id_menu`) ON DELETE CASCADE ON UPDATE CASCADE;

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
