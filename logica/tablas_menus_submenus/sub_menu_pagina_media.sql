CREATE TABLE `sub_menu_pagina_media` (
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
  PRIMARY KEY (`id_media`), KEY `idx_sub_menu_pagina_media_submenu` (`id_sub_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
