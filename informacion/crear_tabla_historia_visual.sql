SET NAMES utf8mb4;
START TRANSACTION;
CREATE TABLE IF NOT EXISTS `sub_menu_historia_item` (
  `id_historia_item` int(11) NOT NULL AUTO_INCREMENT,
  `id_sub_menu` int(11) NOT NULL,
  `titulo` varchar(180) NOT NULL,
  `contenido` mediumtext DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `imagen_alt` varchar(180) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `orden` int(11) NOT NULL DEFAULT '0',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `creado_por` int(11) DEFAULT NULL,
  `actualizado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_historia_item`),
  KEY `idx_historia_submenu_orden` (`id_sub_menu`,`visible`,`orden`),
  CONSTRAINT `fk_historia_item_submenu` FOREIGN KEY (`id_sub_menu`) REFERENCES `sub_menus` (`id_sub_menu`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;
SHOW CREATE TABLE sub_menu_historia_item;
SELECT * FROM sub_menu_historia_item WHERE id_sub_menu=2 ORDER BY orden,id_historia_item;
