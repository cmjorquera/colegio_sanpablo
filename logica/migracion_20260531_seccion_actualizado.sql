-- Migracion segura para registrar ultima modificacion de contenedores CMS.
-- No altera datos existentes ni fuerza FK para mantener compatibilidad con instalaciones actuales.

ALTER TABLE `seccion`
  ADD COLUMN IF NOT EXISTS `actualizado_en` DATETIME NULL AFTER `fecha_creacion`,
  ADD COLUMN IF NOT EXISTS `actualizado_por` INT NULL AFTER `actualizado_en`;
