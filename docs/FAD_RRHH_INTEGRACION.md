# Integración FAD para Capital Humano

## Separación obligatoria

La integración de Capital Humano es independiente de FAD Motos Adjudicadas.
No debe compartir credenciales, variables, rutas, tablas, bitácoras ni reglas de
negocio con `FAD_MOTOS_*`.

Prefijo reservado para esta integración: `FAD_RRHH_*`.

## Punto actual del flujo

El flujo de Selección de Personal ya registra al candidato como `Ingreso
programado`. Después, la interfaz pregunta manualmente si el contrato fue
firmado y ejecuta `/caphum/pasarCandidatoAGestion`.

La integración debe reemplazar esa confirmación manual por evidencia verificable
de FAD:

1. Capital Humano genera o selecciona el contrato PDF.
2. Sparta crea una solicitud de firma en la cuenta FAD RR. HH.
3. Se conserva el identificador externo, vínculo, estado y fechas de la solicitud.
4. El candidato firma en FAD.
5. Sparta recibe un webhook o consulta el estado de la solicitud.
6. Solo un estado final firmado habilita el paso del candidato a Gestión.
7. El PDF firmado y el archivo/evidencia FAD se incorporan al expediente de
   Capital Humano.

## Hallazgos verificados en el portal de Capital Humano

Inspección de solo lectura realizada el 4 de agosto de 2026 sobre
`https://clientes.firmaautografa.com`. No se enviaron, cancelaron ni modificaron
documentos reales.

- Portal: `https://clientes.firmaautografa.com`.
- API utilizada por el portal: `https://api.firmaautografa.com`.
- Versión observada del portal: `4.2.4` del 31 de julio de 2026.
- El acceso usa un token Bearer obtenido en
  `POST /authorization-server/oauth/token`.
- El portal contempla estados `PENDING`, `SIGNED`, `CANCELLED`, `EXPIRED` y
  `REJECTED`. En la interfaz se muestran como en proceso, firmados,
  cancelados/eliminados, expirados, con error o rechazados.
- El PDF final se consulta en
  `GET /cloud/getBackUp/{requisitionId}.pdf`.

### Flujo confirmado del portal

1. Registrar o seleccionar firmantes.
2. Cargar o seleccionar el documento PDF.
3. Capturar nombre, tipo, vigencia, referencia y leyenda de aceptación.
4. Definir el orden de los firmantes.
5. Colocar las áreas de firma en el PDF.
6. Colocar el área del certificado de conservación.
7. Configurar notificaciones y enviar la solicitud.

### Endpoints observados

| Operación | Método y ruta |
| --- | --- |
| Cargar PDF | `POST /clients/users/{userId}/documents` |
| Listar documentos | `GET /clients/users/{userId}/documents` |
| Registrar firmante | `POST /clients/users/{userId}/signers` |
| Listar firmantes | `GET /clients/users/{userId}/signers` |
| Crear solicitud | `POST /clients/users/{userId}/requisitions` |
| Listar solicitudes | `GET /clients/users/{userId}/requisitions` |
| Consultar solicitud | `GET /clients/requisitions/{requisitionId}/info` |
| Consultar firmantes | `GET /clients/requisitions/{requisitionId}/signers` |
| Descargar PDF final | `GET /cloud/getBackUp/{requisitionId}.pdf` |
| Descargar paquete de firmados | `POST /cloud/getSignedRequisitionsZip` |

La carga de documentos es `multipart/form-data`; el campo observado se llama
`files` y admite uno o varios PDF.

### Datos observados para registrar un firmante

```json
{
  "name": "Nombre",
  "lastName": "Apellido paterno",
  "secondLastName": "Apellido materno opcional",
  "email": "correo@ejemplo.com",
  "companyEmail": "correo@ejemplo.com",
  "phone": "5512345678",
  "securityCode": "0000",
  "countryId": "identificador-del-pais",
  "countryCode": "+52"
}
```

### Estructura observada para crear una solicitud

La solicitud referencia un PDF ya cargado y firmantes ya registrados. Las
coordenadas son decimales normalizados respecto a la página.

```json
{
  "name": "Contrato laboral - CANDIDATO",
  "requisitionTypeId": "catalogo-fad",
  "reference": "SPARTA-CANDIDATO-ID",
  "signTimeId": "catalogo-fad",
  "acceptanceLegend": "Leyenda autorizada por Capital Humano",
  "documentId": "uuid-documento",
  "cardId": null,
  "certificate": {
    "page": "1",
    "positionX1": "0.1000",
    "positionX2": "0.9000",
    "positionY1": "0.8500",
    "positionY2": "0.9500"
  },
  "localizationNotRequired": false,
  "acceptanceVideoNotRequired": false,
  "isSingleDeviceSignAvailable": false,
  "signOnWeb": true,
  "signers": [
    {
      "signerId": "uuid-firmante",
      "order": 1,
      "countryCode": "+52",
      "phone": "5512345678",
      "notification": true,
      "sendSMS": true,
      "signDevicePhone": "+525512345678",
      "status": "ACTIVE",
      "signatures": [
        {
          "signerType": "Firmante",
          "page": "1",
          "positionX1": "0.1000",
          "positionX2": "0.4000",
          "positionY1": "0.7000",
          "positionY2": "0.8000",
          "centerX": "0.2500",
          "centerY": "0.7500",
          "optional": false
        }
      ]
    }
  ]
}
```

Los identificadores de tipo de solicitud, vigencia y país deben obtenerse de
los catálogos de la cuenta; no deben codificarse como valores fijos.

## Lo que todavía debe entregar o confirmar FAD

- Credenciales API oficiales y exclusivas para Capital Humano (`client_id` y
  `client_secret`), separadas del acceso humano al portal.
- Ambiente de pruebas o autorización expresa para ejecutar una solicitud de
  prueba en producción.
- Contrato oficial de autenticación. El portal usa OAuth, pero Sparta no debe
  copiar secretos internos contenidos en el frontend de FAD.
- Especificación del webhook, su firma, reintentos y lista contractual de
  estados.
- Cuerpo exacto para descargar el paquete que contiene PDF y evidencia `.FAD`.
- Límites, idempotencia, retención y tratamiento de datos personales.

Las credenciales recibidas no deben copiarse en este documento. Deben residir en
`C:\xampp\secure\sparta_ledger.env` o en el gestor de secretos del servidor.

La cuenta y contraseña recibidas permiten operar el portal. No prueban por sí
solas que el proveedor autorice su uso como credenciales de una integración
automatizada.

## Primer alcance recomendado

Integrar primero la firma del contrato laboral de un candidato en Selección de
Personal. Una vez validado el ciclo completo, reutilizar el cliente de RR. HH.
para otros documentos laborales sin mezclarlo con Motos Adjudicadas.

## Diseño propuesto dentro de Sparta

El primer cambio debe conservar el flujo actual y agregar una compuerta FAD:

1. Crear el contrato y la solicitud desde Selección de Personal.
2. Guardar `candidato_id`, `requisition_id`, `document_id`, estado, referencia,
   fechas, intentos y último error en una tabla exclusiva de RR. HH.
3. Consultar el estado de manera periódica; agregar webhook cuando FAD entregue
   su contrato oficial.
4. Al recibir `SIGNED`, descargar el PDF, validar que sea un PDF real, calcular
   SHA-256 y guardarlo como `Contrato firmado` (tipo 28).
5. Descargar y guardar la evidencia `.FAD` como tipo 29 cuando el proveedor
   confirme el endpoint y formato.
6. Habilitar `pasarCandidatoAGestion` solo si la solicitud asociada está firmada
   y el PDF final fue incorporado correctamente al expediente.

El cliente oficial de producción sigue pendiente del contrato técnico del
proveedor. Mientras tanto existe un adaptador provisional del portal, protegido
por `FAD_RRHH_ENABLED=0` y sujeto a una prueba controlada antes de activarse.

## Preparación implementada en Sparta

La primera etapa ya está disponible detrás de configuración segura:

- Tabla local `candidato_fad_rrhh_solicitud`, creada al preparar el primer
  candidato, con referencia idempotente, identificadores externos, estado,
  auditoría, rutas de evidencias y SHA-256 del PDF final.
- Servicio separado `FadRrhhService`, sin dependencias de Motos Adjudicadas.
- Acción **Preparar FAD** para candidatos con ingreso programado.
- Endpoints autenticados para preparar, consultar y vincular una solicitud
  creada manualmente en el portal.
- Compuerta de seguridad en el alta a Gestión. Permanece inactiva con
  `FAD_RRHH_ENFORCE_SIGNED=0`; al activarla exige `SIGNED`, PDF final resguardado
  y hash SHA-256 válido.
- No existe un endpoint de usuario para marcar manualmente una solicitud como
  firmada. Ese estado deberá venir del adaptador API o del webhook verificado.

Con `FAD_RRHH_ENABLED=0` no se consumen firmas ni se envían PDFs. Al habilitarlo
y completar los catálogos y coordenadas, el envío se ejecuta únicamente al
pulsar **Enviar contrato a FAD** y seleccionar conscientemente un PDF.

### Adaptador provisional del portal

Se agregó un adaptador provisional solicitado para avanzar sin el manual del
proveedor. Este adaptador:

1. Descarga el `index.html` y el bundle JavaScript vigente del portal oficial.
2. Extrae únicamente en memoria la configuración pública usada por la página.
3. Cifra el inicio de sesión con AES-128-CBC igual que el portal.
4. Obtiene un token Bearer sin guardar el token en disco ni registrarlo en logs.
5. Puede consultar usuario, tipos de solicitud, vigencias y país sugerido.
6. Puede cargar un contrato PDF, registrar al candidato como firmante y crear
   la solicitud.
7. Puede sincronizar el estado y, cuando sea `SIGNED`, descargar, validar,
   calcular SHA-256 y guardar el PDF como `Contrato firmado`.

El botón **Enviar contrato a FAD** aparece junto a **Preparar FAD** cuando el
candidato está en `Ingreso programado`. Antes de usarlo se debe completar
`C:\xampp\secure\sparta_ledger.env` mediante
`scripts/configurar_fad_rrhh_seguro.ps1`.

El archivo `.FAD` continúa pendiente porque el portal no expone todavía un
contrato inequívoco para descargar individualmente esa evidencia. Esto no
impide validar y resguardar el PDF firmado.

## Validación operativa local

Revisión ejecutada el 4 de agosto de 2026 sin crear solicitudes ni consumir
firmas:

- Autenticación de Capital Humano y consulta de catálogos: correctas.
- País México: `countryId=1` y código `+52`.
- Tipo contractual: `requisitionTypeId=2` (`Contrato`).
- Vigencia inicial configurada: `signTimeId=15` (`10 días`).
- Tabla `candidato_fad_rrhh_solicitud`: creada y con estructura verificada.
- Archivo de secretos: acceso restringido al usuario que ejecuta Apache,
  Administradores y `SYSTEM`.
- `FAD_RRHH_ENFORCE_SIGNED=0`: se mantiene desactivado.
- Envío real: deliberadamente inhabilitado hasta definir las cajas de firma y
  certificado sobre la plantilla contractual oficial.

## Criterios mínimos de seguridad

- Nunca registrar contraseña, token, PDF completo ni datos personales sensibles.
- Usar HTTPS y validar certificados.
- Registrar auditoría por candidato y por solicitud externa.
- Aplicar idempotencia para evitar solicitudes de firma duplicadas.
- Validar criptográficamente el webhook antes de modificar un estado.
- No dar de alta al candidato en Gestión mientras FAD no confirme la firma.
