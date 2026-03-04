# Microservicio Documentación Candidato (Node.js)

Endpoint rápido para el botón **Documentación** del menú Candidatos. Evita el arranque de PHP y devuelve el mismo JSON que `CapHum::getDocumentosCandidatoList()`.

## Por qué existe

La demora al abrir Documentación no viene de las 2 consultas a MySQL, sino del **arranque de PHP** (bootstrap, sesión, autoload). Este servicio en Node.js mantiene el proceso en memoria y responde en pocos milisegundos.

## Instalación

```bash
cd backend/services/documentacion-candidato
npm install
cp .env.example .env
# Editar .env con DB_HOST, DB_NAME, DB_USER, DB_PASSWORD (mismos que el proyecto PHP)
```

## Ejecución

```bash
npm start
```

Escucha por defecto en `http://localhost:3001`. Endpoint:

- **GET** `/documentacion-candidato?id_candidato=123`

Respuesta: mismo formato que PHP (`{ success, mensaje, datos: { documentos, verificacion_expediente?, metricas } }`).

## Integración en el frontend

En la vista de Candidatos, el JavaScript debe usar este servicio en lugar de PHP cuando esté disponible. En `candidatos.php` se define una variable global para la base URL del microservicio; si está definida, se usa este endpoint; si no, se sigue usando PHP.

Ejemplo de configuración (en el layout o en candidatos.php antes del script):

```html
<script>
  window.DOCUMENTACION_CANDIDATO_API = 'http://localhost:3001';
</script>
```

En producción se puede poner la misma URL (si Node corre en el mismo servidor) o un proxy en Apache/Nginx, por ejemplo:

```apache
ProxyPass /documentacion-api http://127.0.0.1:3001
ProxyPassReverse /documentacion-api http://127.0.0.1:3001
```

y entonces `window.DOCUMENTACION_CANDIDATO_API = ''` con la petición a `/documentacion-api/documentacion-candidato?id_candidato=...`.

## Variables de entorno

| Variable       | Descripción                          | Por defecto      |
|----------------|--------------------------------------|------------------|
| DOC_PORT       | Puerto del servidor Node              | 3001             |
| DB_HOST        | Host MySQL (igual que PHP)           | localhost        |
| DB_PUERTO      | Puerto MySQL                         | 3306             |
| DB_NAME        | Base de datos                        | __SPARTA_SECRET_REDACTED__    |
| DB_USER        | Usuario MySQL                        | __SPARTA_SECRET_REDACTED__           |
| DB_PASSWORD    | Contraseña MySQL                     | (vacío)          |

## Seguridad

- El servicio no comprueba sesión PHP; está pensado para uso **interno** (mismo servidor o detrás de proxy).
- En producción conviene que solo sea accesible desde el mismo origen (proxy) o que se valide un token/header si se expone a otra red.
