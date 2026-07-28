# Direcciones: conocimiento operativo de Leonidas

Dominio: `direcciones`.

## Propósito y conceptos

El dominio relaciona domicilios con créditos o personas e incluye tipo, prioridad, origen, estado, municipio, colonia, código postal, geolocalización y sincronización operativa.

## Reglas de negocio

- Una consulta debe identificar el crédito, persona, ruta o dirección concreta.
- La prioridad de una dirección es un dato operativo; no se deduce por el orden en que aparezcan resultados sin validar el campo correspondiente.
- Estado, municipio, colonia y código postal deben resolverse mediante catálogos geográficos autorizados.
- Una dirección proveniente de Segundómetro conserva su origen; sincronizar no debe ocultar que existe información distinta en Sparta.
- Antes de guardar o reordenar se debe mostrar qué registro cambia y cuál será la nueva prioridad.
- Leonidas no debe mostrar teléfonos, coordenadas o domicilio completo a perfiles sin acceso al dominio.

## Fuentes autorizadas

- `sparta_principal`: direcciones relacionadas, prioridad y vínculos operativos.
- `geografia`: catálogos de ubicación.
- `segundometro`: direcciones operativas susceptibles de reconciliación.

## Permisos

La consulta requiere `direcciones`. También se respetan los permisos del crédito o persona a la que pertenece el domicilio.

## Preguntas reales que debe responder

- ¿Qué direcciones tiene el crédito 12345 y cuál es la prioritaria?
- ¿De dónde proviene esta dirección?
- ¿Qué colonia corresponde al código postal indicado?
- ¿Hay diferencias entre Sparta y Segundómetro?
- Abre el módulo de Direcciones para este crédito.

## Ejecutores disponibles

- `direccion_registrar`
- `direccion_prioridad`
- `direccion_sincronizar`
- `direccion_corregir`

El reordenamiento exige la lista completa de IDs de las direcciones activas del crédito. La corrección comprueba que la dirección pertenezca al crédito y vuelve a leerla después del cambio.
