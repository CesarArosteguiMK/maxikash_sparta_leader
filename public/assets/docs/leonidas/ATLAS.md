# Atlas: conocimiento operativo de Leonidas

Dominio: `atlas`.

## Propósito y conceptos

Atlas administra sucursales, rutas, gestores, presupuestos, distribuidores, riesgos operativos, catálogos, notificaciones y accesos relacionados con la operación territorial.

## Reglas de negocio

- Una consulta debe indicar operación, zona, ruta, sucursal, gestor, cartera o periodo.
- Ruta, sucursal y gestor son entidades diferentes; una coincidencia por nombre no basta para reasignar.
- Los presupuestos deben presentarse con periodo, unidad y fuente.
- Un ranking requiere declarar la métrica y el rango de comparación.
- Los riesgos operativos se muestran como registros de Atlas; Leonidas no inventa una clasificación de riesgo.
- Los accesos de Atlas conservan el control de permisos del módulo.
- Las escrituras permanecen en las pantallas de Atlas mientras no exista un ejecutor auditado conectado.

## Fuentes autorizadas

- `sparta_principal`: rutas, sucursales, presupuestos, distribuidores, riesgos, catálogos y accesos.
- `geografia`: ubicación territorial.
- `legacy`: referencias de gestor o cartera cuando el proceso las utilice.

## Permisos

La sesión requiere `atlas`. Una consulta cruzada de personas, cartera o Legacy necesita también el permiso correspondiente.

## Preguntas reales que debe responder

- ¿Qué rutas tiene asignadas este gestor?
- ¿Cuál es el presupuesto de la sucursal durante julio?
- Compara el avance de estas rutas.
- ¿Qué riesgos operativos siguen abiertos?
- ¿Qué distribuidores están asociados a esta zona?

## Ejecutores disponibles

Leonidas no tiene ejecutores de escritura propios para Atlas. Puede explicar, consultar mediante fuentes aprobadas y abrir el módulo autorizado. Guardar presupuestos, asignar rutas, importar distribuidores o cambiar accesos se realiza en Atlas.
