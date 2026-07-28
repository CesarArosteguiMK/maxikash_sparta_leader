# Capital Humano: conocimiento operativo de Leonidas

Dominio: `capital_humano`.

## Propósito y conceptos

Comprende colaboradores, candidatos, expediente laboral, vacaciones, bajas, reingresos, estructura, accesos, salarios y auditoría. La jerarquía oficial es Empresa > Dirección > Área > Departamento > Puesto.

`numero_empleado` y `codigo_contpac` son identificadores distintos: no deben copiarse, sustituirse ni deducirse entre sí.

## Reglas de negocio

- Una búsqueda de persona debe resolver una coincidencia inequívoca antes de mostrar o modificar datos.
- La ficha 360 combina datos laborales, vacaciones y estado documental, respetando permisos.
- Bajas y reingresos son eventos diferentes; un reingreso no borra el historial de la baja.
- Una solicitud de vacaciones y su resolución administrativa son acciones distintas.
- Los documentos pueden reevaluarse, validarse o clasificarse solo mediante su ejecutor y con trazabilidad.
- Los salarios necesitan permiso especial y segundo factor vigente cuando el servidor lo exija.
- Los cambios de permisos nunca se infieren por el puesto ni por una petición informal.
- Las importaciones de estructura o personal deben producir vista previa, errores por fila y confirmación antes de aplicar.

## Fuentes autorizadas

- `sparta_principal`: personas, candidatos, estructura, expedientes, accesos y auditoría.
- `legacy`: equivalencias y referencias operativas que deban reconciliarse.
- `geografia`: países, estados, municipios, colonias y códigos postales.

## Permisos

Las capacidades se separan en `rrhh_lectura`, `auditoria_rrhh`, `rrhh_registrar`, `rrhh_editar`, `estructura`, `bajas`, `reingresos`, `vacaciones`, `vacaciones_admin`, `candidatos`, `documentos`, `permisos` y `salarios`.

## Preguntas reales que debe responder

- Dame la ficha 360 de la persona 878.
- ¿Qué candidatos siguen en revisión?
- ¿Qué documentos le faltan al candidato 42?
- ¿Quiénes no tienen jefe asignado?
- ¿Cuántas vacaciones tiene disponibles esta persona?
- Audita duplicados de número de empleado o código CONTPAQi.

## Ejecutores disponibles

`rrhh_registrar`, `rrhh_actualizar`, `persona_baja`, `persona_reingreso`, `vacaciones_solicitar`, `vacaciones_resolver`, `estructura_cambiar`, `estructura_importar`, `candidato_registrar`, `candidato_actualizar`, `candidato_etapa`, `documento_reevaluar`, `documento_validar`, `documento_clasificar`, `permiso_actualizar` y `salario_actualizar`.

Todos requieren autorización del servidor; las escrituras requieren vista previa, confirmación y auditoría.
