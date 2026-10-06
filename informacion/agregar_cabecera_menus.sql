-- PROPUESTA PENDIENTE DE EJECUCION MANUAL. Codex no ejecuta este archivo.
-- Una cabecera propia por menu, independiente de sub_menu_paginas y su galeria.
-- Columnas nuevas: no existen en el volcado de referencia revisado.
-- No migra ni copia la imagen de Presentacion a Biblioteca.

ALTER TABLE `menus`
    ADD COLUMN `hero_tipo` ENUM('imagen', 'video') NOT NULL DEFAULT 'imagen',
    ADD COLUMN `imagen_hero` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `hero_video_url` VARCHAR(500) DEFAULT NULL,
    ADD COLUMN `hero_video_archivo` VARCHAR(255) DEFAULT NULL;
