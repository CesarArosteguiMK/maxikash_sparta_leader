# API keys en BD (tabla config_api)

Las claves de API (Gemini, OpenAI, Google Maps, etc.) se guardan en la tabla `config_api` de la base de datos en **texto plano**. La aplicación las lee directamente para llamar a las APIs. **No se muestran completas en pantalla**: al listar la config use `config_api_for_display()` para ver solo los últimos caracteres (ej. `***Sy12`).

## 1. Crear la tabla

Ejecutar una vez en la BD `__SPARTA_SECRET_REDACTED__`:

```sql
-- Crear tabla config_api (ejecutar una vez en __SPARTA_SECRET_REDACTED__)
CREATE TABLE IF NOT EXISTS config_api (
  clave VARCHAR(64) NOT NULL PRIMARY KEY,
  valor TEXT NOT NULL DEFAULT '',
  actualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Si ya tiene la tabla con columna `valor_cifrado` (versión anterior):

```sql
ALTER TABLE config_api ADD COLUMN valor TEXT NULL;
```

## 2. Insertar o actualizar API keys

Desde la raíz del proyecto:

```bash
php backend/config/set_api_key.php GEMINI_API_KEY "AIzaSy..."
php backend/config/set_api_key.php OPENAI_API_KEY "sk-proj-..."
php backend/config/set_api_key.php GOOGLE_MAPS_API_KEY "AIzaSy..."
php backend/config/set_api_key.php OPENAI_SSL_VERIFY "1"
```

Claves admitidas: `OPENAI_API_KEY`, `GEMINI_API_KEY`, `GOOGLE_MAPS_API_KEY`, `OPENAI_SSL_VERIFY`.

## 3. Mostrar en pantalla (sin exponer la key completa)

En vistas o APIs donde se listen las claves configuradas, use **solo** la función que enmascara el valor:

```php
require_once __DIR__ . '/config/ConfigApi.php';
$configDisplay = config_api_for_display(4); // últimos 4 caracteres visibles
// Ejemplo: [ 'GEMINI_API_KEY' => '***Sy12' ]
```

No use `config_api_load_from_db()` para mostrar en pantalla; esa función es solo para uso interno (llamadas a APIs).

## 4. Cambiar una API key

Vuelva a ejecutar el script con la misma clave y el nuevo valor:

```bash
php backend/config/set_api_key.php GEMINI_API_KEY "nueva_clave_aqui"
```

## Seguridad

- Las keys se guardan en texto plano en la BD. Restrinja acceso a la base de datos y al panel de administración.
- **Nunca** muestre el valor completo en pantalla; use `config_api_for_display()` al listar.
