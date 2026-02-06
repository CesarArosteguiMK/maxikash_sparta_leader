-- Tabla para API keys cifradas (Opción B: cifrado reversible con clave maestra).
-- Ejecutar una vez en la BD __SPARTA_SECRET_REDACTED__.
-- Las claves se insertan/actualizan con: php backend/config/set_api_key.php CLAVE "valor"

CREATE TABLE IF NOT EXISTS config_api (
  clave VARCHAR(64) NOT NULL PRIMARY KEY,
  valor_cifrado TEXT NOT NULL,
  actualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
