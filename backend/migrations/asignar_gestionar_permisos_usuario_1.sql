-- ============================================================
-- Asignar "Gestionar permisos" (módulo 43) al usuario id 1
-- para que al menos un admin pueda ver el botón Permisos.
-- ============================================================

INSERT IGNORE INTO asigna_modulo_web (usuario_id, modulo_web_id)
VALUES (1, 43);
