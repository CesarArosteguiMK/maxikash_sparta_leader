# Machotes FAD para Capital Humano

Estos archivos son copias de trabajo de los machotes entregados por Capital Humano. Los originales recibidos no se modifican.

## Selección

- `amigo_general_nuevo.docx`: nuevo ingreso de MaxiKash / Amigo Efectivo.
- `pensionamax_nuevo.docx`: nuevo ingreso de Furia Moto / Pensionamax.
- `amigo_gestor_cobranza.docx`: nuevo ingreso de cobranza para Amigo Efectivo; pendiente de incorporar al generador automático.
- `amigo_actualizacion.docx`: actualización y reconocimiento de antigüedad para colaboradores existentes. No debe aparecer en el flujo de candidatos.

Los nombres vistos en capturas de FAD son ejemplos. El trabajador siempre se obtiene de los datos del candidato. El representante legal sí es fijo por razón social.

## Controles

- El generador rechaza un machote cuyo SHA-256 haya cambiado sin revisión.
- Elimina revisiones aceptadas, texto tachado, resaltados amarillos y marcadores de ejemplo.
- Valida campos contractuales obligatorios antes de generar.
- Las demostraciones deben convertirse a PDF y marcarse en todas sus páginas con `scripts/marcar_pdf_demostracion.py`.
- Ninguna plantilla se envía a FAD mientras su variable `*_APPROVED` permanezca apagada.

