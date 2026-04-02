# Worker: estado de cuenta (S2) + avisos Google Chat

Herramienta **independiente** del flujo web de Sparta Ledger. Recorre una lista de `id_credito`, llama a la API S2 **uno por uno** (mismo contrato que el sistema: `idCredito` + `fechaCorte`), y envía un mensaje al **webhook entrante** de Google Chat por cada resultado y un resumen al final.

## Requisitos

- PHP 8+ con extensión `curl` (XAMPP incluye).
- URL del webhook de Chat y token S2 (los mismos que usa el proyecto o variables de entorno).

## Configuración

1. El repositorio incluye `config.local.env` (TOKEN S2, webhook de Chat, etc.) para que tras `git pull` el agente encuentre la config sin crear el archivo a mano en el servidor.
2. Para un entorno distinto o credenciales propias: copiar `config.example.env` → `config.local.env` y ajustar valores (o usar variables de entorno del sistema).
3. Lista de créditos: copiar `creditos.example.txt` → `creditos.txt` y poner **un ID por línea** (solo dígitos; `#` comentarios).

`creditos.txt` sigue en `.gitignore` (datos operativos, no se versiona).

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

- `config.local.env` contiene secretos (token API y URL de webhook). Mantener el repositorio **privado** o sustituir por variables de entorno en el servidor si el código es público.
- Quien tenga la URL del webhook puede publicar en el espacio de Chat; rote el webhook si hubo fuga.
