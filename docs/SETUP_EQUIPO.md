# Configuración para el equipo y despliegue

## Archivos que NO se suben a Git

- **Credenciales:** `.env`, `backend/config/config.ini`, `backend/config/secret.php`, `backend/config/master_key.php`
- **Llaves SSH:** `backend/config/ssh/*.key`, `*.pem`, `*.ppk`, etc. (ver `.gitignore`)
- **Certificados SSL:** `backend/BD/*.pem`
- **Logs:** `backend/storage/logs/*.log`
- **Test:** `public/test_geo_ssl.php`

## Después de clonar (desarrollo local)

1. **Crear `backend/config/config.ini`** (obligatorio)  
   Copiar desde `backend/config/config.ini.example` y rellenar con los datos de tu entorno (BD, webhook, pdf_media, ssh si usas Segundómetro).

2. **Crear `backend/config/master_key.php`**  
   Si usas API keys cifradas en BD: copiar el que use el equipo por canal seguro, o definir en el servidor la variable de entorno `CONFIG_MASTER_KEY`.

3. **Opcional – `backend/config/secret.php`**  
   Solo si usas "Analizar con IA" y no cargas la API key desde la BD: copiar `backend/config/secret.php.example` como `secret.php` y poner la clave.

4. **Opcional – Llaves SSH**  
   Si usas Shell Segundómetro: colocar las llaves en `backend/config/ssh/` según indique el equipo.

## Copiar al servidor (producción)

- `backend/config/config.ini` (creado desde `config.ini.example`, con datos de producción).
- `backend/config/master_key.php` o variable de entorno `CONFIG_MASTER_KEY`.
- `backend/config/secret.php` solo si se usa.
- Llaves SSH en `backend/config/ssh/` si aplica.
- Certificados SSL en `backend/BD/` si la BD exige SSL.

Recomendación: en producción usar variables de entorno para secretos (DB_*, TOKEN, CONFIG_MASTER_KEY, etc.) y no depender de archivos con contraseñas en el repo.
