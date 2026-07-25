# Leónidas por WhatsApp

## Regla principal

El colaborador siempre inicia la conversación. Sparta no incluye ningún proceso
para que Leónidas escriba primero ni usa plantillas proactivas de Meta.

## Flujo

1. El colaborador escribe al número empresarial de WhatsApp.
2. Meta entrega el mensaje firmado al webhook de Sparta.
3. Sparta valida la firma y busca el número en los teléfonos activos de `persona`.
4. La identidad debe corresponder a una sola persona activa.
5. Se valida el permiso especial `Asistente de Sparta` (módulo 194).
6. Leónidas procesa la instrucción con una sesión aislada para ese colaborador.
7. Si existe una acción sensible, la respuesta incluye una vista previa.
8. El colaborador responde `CONFIRMAR` o `CANCELAR`.

Un número desconocido, duplicado o sin permiso no puede consultar información.

## Configuración en Meta

1. Crear o seleccionar una app de negocio en Meta for Developers.
2. Agregar el producto WhatsApp y vincular el número empresarial.
3. Configurar como callback:
   `https://TU_DOMINIO/LeonidasWhatsApp/webhook`
4. Suscribir el campo `messages`.
5. Usar en Meta el mismo valor configurado en `WHATSAPP_VERIFY_TOKEN`.
6. Copiar el App Secret, Phone Number ID y un token permanente de System User al
   archivo seguro indicado por `SPARTA_ENV_FILE`.
7. Activar `WHATSAPP_CLOUD_ENABLED=1`.

No se deben guardar secretos en Git ni en `.env.example`.

## Identidad y seguridad

- El número se compara en formato internacional y nacional mexicano.
- Si el teléfono está repetido, el acceso se bloquea.
- Cada persona tiene una sesión técnica distinta; no comparte borradores,
  confirmaciones ni memoria con otros números.
- Los reintentos de Meta se deduplican por `message_id`.
- Las reglas de permisos, vista previa, confirmación y auditoría son las mismas
  que utiliza Leónidas dentro del portal.

## Prueba inicial

1. Mantener `WHATSAPP_CLOUD_ENABLED=0` mientras se verifica el webhook.
2. Registrar un teléfono de prueba en una sola persona activa con permiso 194.
3. Activar el canal.
4. Escribir desde ese teléfono: `Hola`.
5. Pedir una consulta de lectura.
6. Pedir una acción sensible y verificar que no se ejecute antes de responder
   `CONFIRMAR`.
7. Repetir el mismo evento de Meta y comprobar que no se procese dos veces.
