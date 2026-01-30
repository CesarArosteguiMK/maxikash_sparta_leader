# Ticket por WhatsApp – Configuración

## Paso 1: Ejecutar el SQL (una sola vez)

Ejecuta el archivo `ticket_whatsapp_setup.sql` contra la base de datos **__SPARTA_SECRET_REDACTED__** (la misma que usa la aplicación):

- Crea el origen de ticket **"WhatsApp"** (si no existe).
- Crea el usuario **"Bot WhatsApp"** en `persona` con `user_name = 'bot_whatsapp'` (solo para asignar como "quién levantó" el ticket; no se usa para login en web).

Ejemplo desde MySQL:

```bash
mysql -u usuario -p __SPARTA_SECRET_REDACTED__ < backend/sql/ticket_whatsapp_setup.sql
```

O desde el cliente MySQL:

```sql
USE __SPARTA_SECRET_REDACTED__;
SOURCE ruta/completa/backend/sql/ticket_whatsapp_setup.sql;
```

## Paso 2: API key

La API key está definida en `backend/config/config.php` como `TICKET_WHATSAPP_API_KEY`.  
**Recomendación:** cámbiala por una clave secreta que solo conozca tu servidor del bot (no la subas al repositorio si es sensible).

## Paso 3: Endpoint

- **URL:** `POST /sabueso/crearTicketWhatsApp`
- **Autenticación:** cabecera `X-API-Key: <tu TICKET_WHATSAPP_API_KEY>` o en el body JSON: `"api_key": "<tu TICKET_WHATSAPP_API_KEY>"`.
- **Body (JSON):** igual que "Levantar ticket" por web, pero **sin** enviar `id_origen_ticket` (se asigna automáticamente "WhatsApp"):

  - `id_tipo_ticket` (número)
  - `id_prioridad` (número)
  - `id_credito` (número)
  - `descripcion_inicial` (texto)
  - `fecha_vencimiento` (texto YYYY-MM-DD)

Ejemplo:

```json
{
  "api_key": "tu_clave_de_config",
  "id_tipo_ticket": 1,
  "id_prioridad": 1,
  "id_credito": 12345,
  "descripcion_inicial": "Solicitud por WhatsApp",
  "fecha_vencimiento": "2025-02-15"
}
```

O con cabecera en lugar de `api_key` en el body:

```json
{
  "id_tipo_ticket": 1,
  "id_prioridad": 1,
  "id_credito": 12345,
  "descripcion_inicial": "Solicitud por WhatsApp",
  "fecha_vencimiento": "2025-02-15"
}
```
y cabecera: `X-API-Key: tu_clave_de_config`

## Respuesta

- **Éxito:** `{ "success": true, "mensaje": "Ticket creado correctamente.", "datos": { "folio": "TCK-0001", "id_ticket": 1 } }`
- **Error:** `{ "success": false, "mensaje": "..." }`

En Panel Admin, los tickets creados por WhatsApp se verán con origen **WhatsApp** y "Quién levantó" = **Bot WhatsApp**.
