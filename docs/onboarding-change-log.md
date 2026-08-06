# Registro de cambios — Onboarding

Este archivo conserva cambios estructurales aplicados al portal de Onboarding y la información necesaria para revertirlos de forma puntual.

## Onboarding > Bienvenida

### 2026-08-04 — Reubicación directa del reproductor institucional

**Antes**

- El reproductor institucional estaba definido en un contenedor oculto (`#onboarding-video-card`, con `d-none`) dentro de la misma vista.
- Al cargar la página, un script copiaba su contenido al destino visible `#onboardingVideoMount` dentro de la sección de bienvenida.

**Cambio realizado**

- El marcado completo del reproductor se movió directamente al bloque visible de bienvenida, dentro de `.onboarding-hero-video`.
- Se eliminó el contenedor oculto y el script responsable de moverlo.
- No se modificaron textos, estilos, controles ni la fuente del video.

**Archivos afectados**

- `backend/views/onboarding_contenido.php`

## Onboarding > Evaluaciones y diplomas

### 2026-08-04 — Modales de quiz y diploma descargable

**Cambio realizado**

- Se eliminó la etiqueta visible de cantidad de reactivos del quiz especializado.
- Los dos modales de evaluación usan un encabezado oscuro uniforme, con icono y jerarquía visual inspirados en el encabezado de referencia.
- Al aprobar una evaluación, el botón de diploma descarga un PDF horizontal con el nombre completo recuperado desde la sesión/persona.
- La ruta del diploma verifica primero el avance persistido del usuario, por lo que no se puede descargar antes de aprobar.

**Archivos afectados**

- `backend/controllers/Onboarding.php`
- `backend/views/onboarding_contenido.php`

**Reversa**

- Retirar el método `Onboarding::diploma()` y los enlaces `/onboarding/diploma?tipo=...`.
- Restaurar los botones de diploma inactivos y el estilo previo de los encabezados de modal.

## Onboarding > Soporte, Evaluación y Retroalimentación

### 2026-08-04 — Simulación de evaluación y secciones pendientes

**Cambio realizado**

- El indicador Identidad adopta el formato de etiqueta Roadmap con acento amarillo.
- El Directorio de Atención y Soporte se compactó para priorizar una presentación horizontal; Jurídico incorpora acción de correo a `juridico@maxikash.mx`.
- Se agregó el Buzón de Retroalimentación visual, sin envío real mientras no se confirme un destinatario.
- Se agregaron Quiz de Inducción Corporativo y Quiz por Puestos. El segundo abre un modal vacío preparado para su integración posterior.
- El Quiz corporativo exige las cinco respuestas y el nombre; ubica al usuario en la primera respuesta faltante. Al completarlo, oculta el formulario y muestra "Evaluando tus respuestas". La simulación se conserva por identificador de sesión en `localStorage`.
- No se implementó envío de respuestas o feedback por correo.

**Pendiente**

- Confirmar destinatario(s), asunto y formato del correo para quiz y buzón antes de habilitar envíos reales.

### 2026-08-04 — Ajustes de consulta y contenido validado

**Cambio realizado**

- Las preguntas frecuentes se sustituyeron por las cuatro preguntas y respuestas verificadas visualmente en el PDF proporcionado.
- El acordeón conserva el comportamiento de una sola respuesta abierta a la vez mediante `data-bs-parent`.
- La búsqueda del glosario se integró en la cabecera, ofrece sugerencias nativas y desplaza/resalta el término seleccionado.
- Los términos del glosario ahora presentan hover con sombra leve.
- Nuestros Valores se alineó a la jerarquía de Misión y Visión; su etiqueta Identidad adopta el estilo de acción del sistema.
- Se revisaron las rutas de archivos del proyecto y de `public/uploads`: no hay documentos existentes para Manual de Registro en App, Calendario de Pagos 2026 ni Formato Cambio de Cuenta. Por ello los modales continúan preparados y vacíos.

**Reversa**

- Restablecer el contenedor oculto `#onboarding-video-card` y el destino `#onboardingVideoMount`.
- Restaurar el script que mueve `.card-body` desde ese contenedor hasta el destino visible.

## Onboarding > Ruta de Integración y Cultura

### 2026-08-04 — Jerarquía visual homogénea

**Cambio realizado**

- La Ruta de Integración adopta el mismo patrón visual de Instrucciones: borde superior, fondo sutil, icono de cabecera y etapas delimitadas.
- Las tres etapas ganan una jerarquía visual de recorrido mediante acentos laterales y conectores en escritorio.
- Misión y Visión se ajustaron con el mismo nivel de presencia visual, manteniendo su estructura y todos sus textos.

**Archivos afectados**

- `backend/views/onboarding_contenido.php`

## Onboarding > Reproductor y catálogo de módulos

### 2026-08-04 — Sincronización de altura entre reproductor y catálogo

**Antes**

- El listado lateral tenía una altura máxima fija de 382 px.
- Al reproducir un video, el reproductor aumentaba de altura según su proporción, pero el catálogo mantenía ese límite y dejaba un espacio vacío debajo.

**Cambio realizado**

- La pantalla inicial del reproductor adopta la misma proporción 16:9 del video.
- La altura del catálogo lateral se sincroniza con la altura actual del reproductor al cargar, cambiar de tamaño de ventana o seleccionar un módulo.
- Los módulos que no caben se muestran mediante desplazamiento vertical, sin ampliar la sección ni dejar espacios vacíos.
- No se modificaron textos, tamaños de tipografía, iconos ni contenido de los módulos.

**Archivos afectados**

- `backend/views/onboarding_contenido.php`

## Pendientes para decisión con usuario

- Incorporar `cierre_induccion.mp4` en `public/uploads/onboarding/`.
- Definir e implementar el bloqueo obligatorio de módulos en orden. La alternativa recomendada para exigirlo entre navegadores y dispositivos es persistencia de servidor mediante JSON con control de concurrencia; `localStorage` solo permite una guía visual en el navegador actual.

## Onboarding > Quiz especializado y uniformidad visual

### 2026-08-04 — Quiz por puestos dentro del portal

**Cambio realizado**

- El modal de Quiz por Puestos adopta tamaño `modal-xl`, desplazamiento interno y ajustes para pantallas móviles.
- Se incorporaron los diez reactivos y los puestos disponibles de la referencia `quiz-puestos.html`, con el encabezado adaptado al sistema visual local.
- Las respuestas, nombre y puesto se guardan por sesión de navegador mediante `sessionStorage`. Al finalizar el cuestionario, al volver a abrir el modal se muestra el agradecimiento y se evita una nueva captura durante esa sesión.
- Nuestros Valores deja de elevarse como bloque completo; el hover se aplica exclusivamente a cada valor interno. Misión y Visión conservan su hover suave.
- Los nombres de área del directorio ahora usan un solo color azul fuerte, los teléfonos se muestran como texto y se conserva únicamente la acción de correo para Jurídico.
- Las FAQ inician completamente cerradas y Bootstrap mantiene como máximo una respuesta abierta.
- El contador dinámico de módulos usa verde oscuro. Las instrucciones se estructuran como etapas de la Ruta de Integración.
- Se integró un pie de página local, adaptado al estilo del portal, con la información indicada en la referencia.

**Reversa**

- Sustituir el modal `#onboardingSpecializedQuizModal` por su estructura vacía y retirar el bloque de script `specializedModal`.
- Eliminar las clases `onboarding-specialized-*`, `onboarding-footer`, `onboarding-module-count`, `onboarding-contact-area` y `onboarding-contact-phone` de la vista.

## Onboarding > Avance persistente

### 2026-08-04 — Progreso por usuario en JSON

**Cambio realizado**

- Se agregó `backend/storage/onboarding/progress.json` como almacenamiento central del avance. Cada registro se indexa por `id_usuario`, obtenido exclusivamente de la sesión autenticada.
- El controlador `Onboarding::progress()` consulta y actualiza el archivo con bloqueo exclusivo (`flock`) para evitar pérdida de avances simultáneos.
- Se registran los videos vistos, las evaluaciones corporativa y especializada finalizadas, y el envío simulado del buzón. Las evaluaciones que no se envían no incrementan el avance.
- Al abrir el portal se reconstruyen los videos completados, las evaluaciones ya respondidas y el porcentaje, por lo que no se permite volver a responderlas desde la interfaz.
- Se añadió un indicador circular flotante y permanente que muestra el porcentaje global.

**Reversa**

- Retirar la ruta `/onboarding/progress`, el archivo de almacenamiento y el bloque `window.onboardingProgress` de la vista.
- Restaurar los estados locales de módulos y evaluaciones si se decide volver a una simulación solo por navegador.

## Onboarding > Cultura, Glosario, Soporte, Documentación y FAQ

### 2026-08-04 — Secciones informativas y de consulta

**Cambio realizado**

- Misión, Visión y Nuestros Valores incorporan elevación y sombra suaves al pasar el cursor.
- Se agregaron Glosario de Términos Maxikash, Directorio de Atención y Soporte, Documentación de Soporte y Preguntas Frecuentes con el patrón visual de tarjetas e instrucciones del portal.
- El buscador del glosario filtra sus términos en la propia página.
- Los botones de documentos abren un modal preparado, con el cuerpo vacío para integrar el documento posteriormente.
- Las Preguntas Frecuentes usan acordeones Bootstrap funcionales.

**Archivos afectados**

- `backend/views/onboarding_contenido.php`

**Reversa**

- Eliminar la proporción aplicada a `#onboardingModuleEmpty` y la función `syncModuleListHeight()`.
- Restaurar `.onboarding-module-player { min-height: 330px; }` y el límite fijo del catálogo.

### Pendiente de contenido

- El módulo **Cierre de inducción** sigue configurado en el catálogo, pero requiere el archivo `cierre_induccion.mp4` dentro de `public/uploads/onboarding/` para reproducirse.

### 2026-08-04 — Iconos, contador y estado de videos

**Cambio realizado**

- Los números y la etiqueta visual "Ver" del catálogo se sustituyeron por iconos Bootstrap específicos de cada módulo y un indicador de estado.
- El indicador muestra un círculo para video pendiente y una marca de verificación al finalizarlo.
- El estado se conserva por usuario y navegador mediante `localStorage`; no se usan base de datos ni archivos JSON compartidos.
- El contador de módulos se calcula desde los módulos existentes en la vista y usa el mismo estilo de etiqueta de Roadmap.

**Alcance del estado**

- El avance se conserva al cerrar y abrir sesión en el mismo navegador y equipo.
- No se comparte entre navegadores, equipos o dispositivos. Para ello sería necesario persistirlo en servidor (base de datos o un archivo JSON con bloqueo y control de concurrencia).

**Archivos afectados**

- `backend/views/onboarding_contenido.php`
