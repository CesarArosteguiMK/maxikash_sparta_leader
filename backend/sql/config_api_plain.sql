-- API keys en texto plano (lectura directa desde BD).
-- Ejecutar en la BD __SPARTA_SECRET_REDACTED__.
-- Las claves se insertan con: php backend/config/set_api_key.php GEMINI_API_KEY "AIzaSy..."
-- En pantalla no se muestran completas (solo últimos caracteres) para no exponerlas.

-- Si ya existe config_api con valor_cifrado, añadir columna valor:
-- ALTER TABLE config_api ADD COLUMN valor TEXT NULL;

CREATE TABLE IF NOT EXISTS config_api (
  clave VARCHAR(64) NOT NULL PRIMARY KEY,
  valor TEXT NOT NULL DEFAULT '',
  actualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
