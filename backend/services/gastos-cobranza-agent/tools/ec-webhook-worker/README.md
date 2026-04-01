# Worker: estado de cuenta (S2) + avisos Google Chat

Herramienta **independiente** del flujo web de Sparta Ledger. Recorre una lista de `id_credito`, llama a la API S2 **uno por uno** (mismo contrato que el sistema: `idCredito` + `fechaCorte`), y envía un mensaje al **webhook entrante** de Google Chat por cada resultado y un resumen al final.

## Requisitos

- PHP 8+ con extensión `curl` (XAMPP incluye).
- URL del webhook de Chat y token S2 (los mismos que usa el proyecto o variables de entorno).

## Configuración

1. Copiar `config.example.env` → `config.local.env`.
2. Rellenar:
   - `TOKEN` — header `Token` para `servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta`
   - `GOOGLE_CHAT_WEBHOOK_URL` — URL completa del espacio (Integraciones → Webhooks)
   - Opcional: `DELAY_MS_BETWEEN_CREDITS` (por defecto 500 ms entre créditos)
3. Lista de créditos: copiar `creditos.example.txt` → `creditos.txt` y poner **un ID por línea** (solo dígitos; `#` comentarios).

`config.local.env` y `creditos.txt` están en `.gitignore`: no se suben al repositorio.

## Uso

Desde esta carpeta:

```bat
c:\xampp\php\php.exe worker.php
```

Opciones:

| Opción | Descripción |
|--------|-------------|
| `--file=ruta\archivo.txt` | Archivo de IDs (por defecto `creditos.txt` en esta carpeta) |
| `--dry-run` | No llama a S2 ni a Chat; solo lista lo que haría |
| `--no-chat` | Llama a S2 pero no envía mensajes a Chat (pruebas de API) |
| `--delay=500` | Milisegundos entre créditos (sobreescribe env) |

Código de salida: `0` si todo OK, `2` si hubo al menos un error en S2.

## Programar en Windows (Tareas programadas)

Crear tarea que ejecute:

`c:\xampp\php\php.exe c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\services\gastos-cobranza-agent\tools\ec-webhook-worker\worker.php`

con “Iniciar en” = carpeta del worker, o usar ruta absoluta a `creditos.txt` con `--file=...`.

## Seguridad

- No commitear `config.local.env` ni pegar la URL del webhook en sitios públicos.
- Quien tenga la URL del webhook puede publicar en el espacio de Chat.
