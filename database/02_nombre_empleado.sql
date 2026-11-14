-- Ejecutar SOLO una vez, DESPUÉS de hacer copia de seguridad.
-- Si ya agregaste nombre_completo en HeidiSQL, NO ejecutes este ALTER otra vez.
USE ardila_onmarket;
ALTER TABLE usuario ADD COLUMN nombre_completo VARCHAR(120) NULL AFTER nombre;
UPDATE usuario SET nombre_completo = 'Administrador Ardila Property' WHERE nombre = 'admin' AND nombre_completo IS NULL;
