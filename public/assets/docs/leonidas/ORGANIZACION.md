# Organización: conocimiento operativo de Leonidas

Dominio: `organizacion`.

## Propósito y conceptos

Mantiene empresas, países, direcciones organizacionales, áreas, departamentos, puestos, equivalencias y asignaciones de personas. La estructura oficial es Empresa > Dirección > Área > Departamento > Puesto.

Un puesto representa la posición organizacional. Los módulos y permisos especiales representan acceso; no deben deducirse exclusivamente del nombre del puesto.

## Reglas de negocio

- Cada cambio debe identificar persona o nodo, valor actual y valor propuesto.
- Cambiar jefe, puesto o departamento conserva el historial que mantenga el módulo; no debe simularse borrando y recreando sin necesidad.
- Una persona sin jefe se reporta como inconsistencia o excepción, no se asigna automáticamente.
- Las equivalencias con Legacy deben presentarse como correspondencias verificadas.
- Una importación debe validar filas duplicadas, referencias inexistentes y ambigüedades antes de aplicar.
- `numero_empleado` y `codigo_contpac` nunca se mezclan durante una carga.
- Los cambios masivos requieren vista previa, resumen de errores y confirmación.

## Fuentes autorizadas

- `sparta_principal`: estructura, puestos, asignaciones, empresas y auditoría.
- `legacy`: puestos o equivalencias operativas.
- `geografia`: países y ubicaciones relacionadas.

## Permisos

La consulta requiere `organizacion` o `estructura`. Las escrituras utilizan el permiso específico de estructura y los controles de Capital Humano.

## Preguntas reales que debe responder

- Muéstrame la estructura de esta empresa.
- ¿Qué personas no tienen jefe?
- ¿En qué puesto y departamento está esta persona?
- ¿Qué equivalencias con Legacy faltan?
- Prepara el cambio de jefe de esta persona.
- Revisa este Excel de estructura antes de importarlo.

## Ejecutores disponibles

- `estructura_cambiar`
- `estructura_importar`
- `excel_aplicar`

La propuesta se vuelve a validar al confirmar para impedir que una vista previa obsoleta modifique la estructura.
