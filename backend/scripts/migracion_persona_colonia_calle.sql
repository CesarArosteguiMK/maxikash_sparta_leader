-- =============================================================================
-- Domicilio extendido: colonia y calle vía divisiones_administrativas
-- (nivel 3 = colonia, id_padre = municipio; nivel 4 = calle, id_padre = colonia).
-- codigo_interno en nivel 3 = código postal sugerido (SEPOMEX / listados públicos).
-- Ejecutar una vez contra __SPARTA_SECRET_REDACTED__ (ajustar esquema si difiere).
-- =============================================================================

ALTER TABLE __SPARTA_SECRET_REDACTED__.persona
  ADD COLUMN id_div_nivel3 INT NULL DEFAULT NULL COMMENT 'Colonia (divisiones_administrativas.nivel=3)' AFTER id_div_nivel2,
  ADD COLUMN id_div_nivel4 INT NULL DEFAULT NULL COMMENT 'Calle (divisiones_administrativas.nivel=4)' AFTER id_div_nivel3,
  ADD COLUMN domicilio_num_exterior VARCHAR(32) NULL DEFAULT NULL AFTER id_div_nivel4,
  ADD COLUMN domicilio_num_interior VARCHAR(32) NULL DEFAULT NULL AFTER domicilio_num_exterior,
  ADD COLUMN codigo_postal VARCHAR(10) NULL DEFAULT NULL AFTER domicilio_num_interior;

-- Si las columnas ya existen, MySQL devolverá error en ALTER: omitir solo esa sentencia.

-- -----------------------------------------------------------------------------
-- Colonias a nivel nacional (recomendado): catálogo SEPOMEX oficial
--   1) Descargar cpdescarga.txt desde Correos de México (datos abiertos).
--   2) Ejecutar en CLI (con PHP del servidor / XAMPP):
--        php backend/scripts/import_sepomex_colonias.php "C:\ruta\cpdescarga.txt"
--      Opciones: --dry-run  --pais=1  --limite=5000
--   Requiere que estados (nivel 1) y municipios (nivel 2) tengan codigo_interno
--   alineado con INEGI (c_estado, c_mnpio del TXT) o nombres compatibles con SEPOMEX.
--
-- Solo Gustavo A. Madero (ejemplo / prueba): seed_gam_divisiones_completo.sql
-- Regenerar ese seed: python backend/scripts/build_gam_seed_sql.py
-- -----------------------------------------------------------------------------
