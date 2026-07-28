# Plataforma, sesión y experiencia del usuario

Esta ficha documenta componentes transversales de Sparta que no pertenecen a un único dominio de negocio.

## Inicio

`Inicio` presenta el tablero inicial, frase del día, diagnósticos de conexiones y Segundómetro, además del estado y control autorizado de servicios locales.

Reglas:

- Un diagnóstico es lectura; iniciar o detener un servicio es una acción.
- Los logs se muestran sin secretos.
- Los controles locales conservan la lista permitida del servidor.

## Login y sesión

`Login` valida usuario, crea la sesión y permite cerrarla. El puesto se obtiene de la asignación organizacional; los permisos se obtienen de módulos y controles especiales.

Reglas:

- Puesto y permiso no son equivalentes.
- Una sesión vencida no puede reutilizar acciones pendientes.
- Cerrar sesión invalida el contexto operativo de Leonidas.
- Leonidas nunca solicita ni repite contraseñas.

## Perfil

`Perfil` permite consultar y guardar información personal autorizada y eliminar la foto.

Reglas:

- El usuario solo modifica los campos habilitados por el servidor.
- Eliminar una foto no elimina el expediente de la persona.
- Datos sensibles de RR. HH. siguen perteneciendo a Capital Humano.

## Notificaciones

`Notificaciones` lista avisos, marca uno o todos como leídos y contiene diagnóstico de sincronización.

Reglas:

- Marcar como leído cambia únicamente el estado de lectura.
- Una notificación no prueba por sí misma que el proceso de origen terminó.
- El diagnóstico de sincronización se reserva a perfiles autorizados.

## Onboarding

`Onboarding` expone un índice y contenido de video para incorporación o capacitación.

Reglas:

- Ver contenido no equivale a completar una validación laboral salvo que el módulo registre expresamente el avance.
- Leonidas puede localizar y explicar el contenido, pero no debe certificar su cumplimiento sin un dato del servidor.

## Clima

`Clima` contiene una vista para CDMX. Es una utilidad interna específica; no convierte a Leonidas en un servicio meteorológico mundial. Si la consulta pide clima fuera del alcance configurado, debe explicarlo.

## API y controladores técnicos

`Api` ofrece puntos de analítica. `DynamoValidations` contiene vista previa de oferta y coordenadas. Estos componentes son integraciones técnicas: su existencia no implica acceso directo desde el chat.

## Preguntas reales

- ¿Dónde actualizo mi perfil?
- ¿Por qué una notificación sigue pendiente?
- ¿Qué servicios aparecen caídos en Inicio?
- ¿Dónde está el video de onboarding?
- ¿Cómo determina Sparta mi puesto?

## Fuentes, permisos y ejecutores

Fuente principal: `sparta_principal`, además de los servicios diagnosticados. Las consultas conservan la sesión. Leonidas puede navegar y, para servicios permitidos, usar `servicio_local_control`; no tiene ejecutores para alterar sesión, perfil, onboarding o notificaciones.
