# RFC del cliente y cómo se identifica el puesto (Call Center, RRHH, etc.)

## 1. Dónde obtener el RFC del cliente

### Fuentes en el código

- **Vista Estado de Cuenta (resultado)**  
  En `backend/views/__SPARTA_SECRET_REDACTED___request.php` el RFC se muestra así:
  ```php
  $rfcCliente = $datosRef["rfc"] ?? '—';
  ```
  `$datosRef` viene del primer elemento de **referencias**:
  ```php
  $datosRef = $referencias['datos'][0] ?? [];
  ```
  Esas referencias las llena el controlador con:
  ```php
  $referencias = EmpresasDAO::getConsultaReferenciasEstadoCuenta($idCredito);
  ```
  En **`backend/models/Empresa.php`**, el método `getConsultaReferenciasEstadoCuenta($id_credito)` hace un `SELECT` contra la base **__SPARTA_SECRET_REDACTED__** (tablas `oferta`, `persona`, `persona_adicionales`) y **hoy no incluye ningún campo RFC**. Por eso, si no se añade ese campo, en la vista el RFC siempre saldrá como "—".

### Cómo tener RFC de verdad

Tienes dos opciones:

1. **Si el RFC está en la base __SPARTA_SECRET_REDACTED__**  
   Añadir el campo al `SELECT` de `getConsultaReferenciasEstadoCuenta`, por ejemplo:
   - Si está en `persona`: `p.rfc AS rfc`
   - Si está en `persona_adicionales`: `COALESCE(p2.rfc, '') AS rfc`  
   (o el nombre real de la columna en tu esquema).  
   Así `$referencias['datos'][0]['rfc']` tendrá valor y en la vista seguirá funcionando `$datosRef["rfc"]`.

2. **Si el RFC viene de la API de estado de cuenta**  
   La respuesta del servicio se usa así en el controlador:
   ```php
   $cliente = $resultado['data']['datosCliente'];
   ```
   y se pasa a la vista como `dataCliente`. Si la API devuelve RFC dentro de `datosCliente` (por ejemplo `rfc` o `rfcCliente`), en la vista puedes usar:
   ```php
   $rfcCliente = $dataCliente["rfc"] ?? $dataCliente["rfcCliente"] ?? $datosRef["rfc"] ?? '—';
   ```
   y, si quieres, dejar de depender de `$datosRef["rfc"]` cuando la API ya lo trae.

**Resumen:** Hoy el RFC en estado de cuenta solo puede salir de: (a) añadiendo la columna RFC al query de referencias en `Empresa::getConsultaReferenciasEstadoCuenta`, o (b) usando el RFC que venga en la respuesta de la API dentro de `datosCliente`.

---

## 2. Cómo se identifica Call Center, RRHH, etc. (puesto del usuario)

No hay detección por lógica ni por módulos: el sistema usa el **puesto asignado al usuario en la base de datos**.

### En el login

- **`backend/models/Login.php`** → `validaUsuario()`  
  Hace un `SELECT` que incluye:
  - `pp.id AS id_puesto`
  - `pp.nombre AS nombre_puesto`  
  desde las tablas `persona`, `asigna_puesto` y **`puesto`**.

- El usuario se relaciona con un puesto mediante **`asigna_puesto`** (persona ↔ id_puesto).  
  El **nombre** del puesto sale del catálogo **`puesto`** (por ejemplo "Call Center", "Capital Humano", "Administrador de Proyectos", "Desarrollador Junior", "Desarrollador Senior").

- En **`backend/controllers/Login.php`** esos datos se guardan en sesión:
  ```php
  $_SESSION['nombre_puesto'] = $datos['nombre_puesto'];
  $_SESSION['id_puesto']     = $datos['id_puesto'];
  $_SESSION['nivel_puesto']  = $datos['id_puesto'];
  ```

### En la aplicación

- El mensaje del hero en Inicio, los accesos rápidos y el menú usan **`$_SESSION['nombre_puesto']`**.
- Si en la tabla `puesto` el registro asignado al usuario tiene `nombre = 'Call Center'`, para el sistema ese usuario es "Call Center". Lo mismo para "Capital Humano", "Administrador de Proyectos", etc.: es el **nombre del puesto** en el catálogo, no un rol calculado.

**Resumen:** Call Center, RRHH, etc. se “identifican” porque el usuario tiene en la BD un puesto cuyo **nombre** es exactamente ese (p. ej. "Call Center", "Capital Humano"). Se gestiona en Capital Humano / asignación de puestos (tabla `puesto` y `asigna_puesto`).
