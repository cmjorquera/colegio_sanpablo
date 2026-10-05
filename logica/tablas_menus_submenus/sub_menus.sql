CREATE TABLE `sub_menus` (
  `id_sub_menu` int(11) NOT NULL AUTO_INCREMENT,
  `id_menu` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `orden` int(11) DEFAULT '0',
  `estado` tinyint(1) DEFAULT '1',
  `fecha_creacion` date DEFAULT NULL,
  `hora_creacion` time DEFAULT NULL,
  `ip_creacion` varchar(45) DEFAULT NULL,
  `actualizado_en` datetime DEFAULT NULL,
  `actualizado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_sub_menu`),
  UNIQUE KEY `uq_menu_submenu` (`id_menu`,`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
