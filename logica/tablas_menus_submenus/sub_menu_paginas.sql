CREATE TABLE `sub_menu_paginas` (
  `id_pagina` int(11) NOT NULL AUTO_INCREMENT, `id_sub_menu` int(11) NOT NULL,
  `titulo` varchar(180) DEFAULT NULL, `bajada` varchar(300) DEFAULT NULL, `contenido` mediumtext,
  `imagen_hero` varchar(255) DEFAULT NULL, `hero_video_url` varchar(500) DEFAULT NULL,
  `hero_video_archivo` varchar(255) DEFAULT NULL, `imagen_secundaria` varchar(255) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL, `video_archivo` varchar(255) DEFAULT NULL,
  `boton_texto` varchar(150) DEFAULT NULL, `boton_url` varchar(255) DEFAULT NULL,
  `meta_title` varchar(180) DEFAULT NULL, `meta_description` varchar(300) DEFAULT NULL,
  `actualizado_en` datetime DEFAULT NULL, `actualizado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pagina`), UNIQUE KEY `uq_sub_menu_paginas_submenu` (`id_sub_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
