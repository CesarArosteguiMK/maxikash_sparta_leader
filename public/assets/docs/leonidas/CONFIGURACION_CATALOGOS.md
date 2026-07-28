# Configuración, catálogos y administración

## Países, empresas y departamentos

`Paises` consulta, crea y activa o desactiva países. `Empresas` consulta existentes y sus departamentos. `Departamentos` administra direcciones organizacionales, departamentos, puestos y orden.

Reglas:

- Desactivar un catálogo no equivale a borrar su historial.
- Antes de eliminar o desactivar se revisan dependencias.
- Dirección organizacional y domicilio geográfico son conceptos distintos.
- El orden de puestos es una configuración explícita.

## Equivalencias

`Equivalencias` relaciona puestos de Legacy con puestos de Sparta.

Reglas:

- Una equivalencia debe usar identificadores de ambos catálogos.
- Coincidencia de nombre no implica equivalencia automática.
- Cambiar una equivalencia puede afectar sincronizaciones futuras y requiere auditoría.

## Usuarios y plantilla

`Usuarios` consulta existentes, detalles y empresas. `Plantilla` expone panel administrativo.

Reglas:

- Usuario, persona y colaborador son entidades relacionadas pero no intercambiables.
- Estado laboral y estado de acceso deben presentarse por separado.
- Los permisos provienen de módulos y reglas especiales, no solo de la empresa o el puesto.

## Configuración de Tickets

`ConfigTicketPuesto` configura puestos, estadísticas y paneles por usuario.

Reglas:

- Configurar visibilidad no reasigna tickets existentes salvo que el flujo lo indique.
- Los cambios por puesto y los cambios por usuario son niveles diferentes.
- Una configuración debe registrar actor y valores anterior/nuevo.

## Validaciones y formularios

`Validaciones` contiene paneles de gestor y territorial, reasignación, formularios y preguntas configurables.

Reglas:

- Formulario, pregunta y respuesta son entidades distintas.
- Desactivar conserva historial; eliminar requiere revisar respuestas asociadas.
- Reasignar un ticket territorial es una escritura con responsable anterior y nuevo.

## Configuración de Motos

`ConfigMotosAdj` mantiene rutas, FAD, reglas, excepciones y recordatorios. Pertenece funcionalmente a Motos Adjudicadas y conserva sus permisos reforzados.

## Controladores de panel especializados

`Aclaracioncredito`, `Aplicacionespago`, `Atencioncliente`, `Creditoproblematico`, `Plantilla` y `Viaticos` exponen paneles administrativos. Aunque algunos tengan una única entrada pública, su conocimiento se complementa con el módulo de negocio y con el inventario seguro de modelos.

## Preguntas reales

- ¿Qué países están activos?
- ¿Qué puestos de Legacy no tienen equivalencia?
- ¿Qué panel ve este puesto?
- ¿Qué formularios territoriales están activos?
- ¿Qué dependencias impiden eliminar este departamento?

## Fuentes, permisos y ejecutores

Fuentes: `sparta_principal`, `legacy` y `geografia`. Cada catálogo conserva el permiso de su módulo. Leonidas puede consultar y explicar; solo estructura y permisos disponen de ejecutores limitados (`estructura_cambiar`, `estructura_importar`, `excel_aplicar`, `permiso_actualizar`).
