-- Migracion: paginas dinamicas para submenus
-- Fecha: 2026-06-07
-- Objetivo:
-- 1. Crear tablas de contenido administrable para cada sub_menu.
-- 2. Crear tabla de multimedia asociada a paginas de submenus.
-- 3. Apuntar submenus sin URL a pagina_submenu.php?id=ID.

CREATE TABLE IF NOT EXISTS `sub_menu_paginas` (
  `id_pagina` int(11) NOT NULL AUTO_INCREMENT,
  `id_sub_menu` int(11) NOT NULL,
  `titulo` varchar(180) DEFAULT NULL,
  `bajada` varchar(300) DEFAULT NULL,
  `contenido` mediumtext,
  `imagen_hero` varchar(255) DEFAULT NULL,
  `hero_video_url` varchar(500) DEFAULT NULL,
  `hero_video_archivo` varchar(255) DEFAULT NULL,
  `imagen_secundaria` varchar(255) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `video_archivo` varchar(255) DEFAULT NULL,
  `boton_texto` varchar(150) DEFAULT NULL,
  `boton_url` varchar(255) DEFAULT NULL,
  `meta_title` varchar(180) DEFAULT NULL,
  `meta_description` varchar(300) DEFAULT NULL,
  `actualizado_en` datetime DEFAULT NULL,
  `actualizado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pagina`),
  UNIQUE KEY `uq_sub_menu_paginas_submenu` (`id_sub_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `sub_menu_paginas`
  ADD COLUMN IF NOT EXISTS `hero_video_url` varchar(500) DEFAULT NULL AFTER `imagen_hero`,
  ADD COLUMN IF NOT EXISTS `hero_video_archivo` varchar(255) DEFAULT NULL AFTER `hero_video_url`;

CREATE TABLE IF NOT EXISTS `sub_menu_pagina_media` (
  `id_media` int(11) NOT NULL AUTO_INCREMENT,
  `id_sub_menu` int(11) NOT NULL,
  `tipo` enum('imagen','video','youtube') NOT NULL DEFAULT 'imagen',
  `archivo` varchar(255) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `titulo` varchar(180) DEFAULT NULL,
  `descripcion` varchar(300) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `orden` int(11) NOT NULL DEFAULT '0',
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_media`),
  KEY `idx_sub_menu_pagina_media_submenu` (`id_sub_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE `sub_menus`
SET `url` = CONCAT('pagina_submenu.php?id=', `id_sub_menu`),
    `actualizado_en` = NOW()
WHERE `url` IS NULL
   OR TRIM(`url`) = ''
   OR TRIM(`url`) = '#';
