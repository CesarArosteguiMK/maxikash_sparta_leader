-- Ejecuta este script UNA VEZ en DBeaver si ya creaste el trigger
-- y te sale el error al actualizar persona con legión.
-- Elimina el trigger que provoca el conflicto.

DROP TRIGGER IF EXISTS __SPARTA_SECRET_REDACTED__.trg_una_legion_activa;
