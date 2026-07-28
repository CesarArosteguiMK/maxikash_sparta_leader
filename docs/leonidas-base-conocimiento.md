# Mantenimiento de la base de conocimiento de Leonidas

La documentación que Leonidas consume vive en `public/assets/docs/leonidas/`. El registro funcional que define los dominios y ejecutores reales vive en `backend/services/LeonidasCapabilityRegistry.php`.

Además de las fichas:

- `LeonidasCodeKnowledgeService` crea un inventario seguro de controladores, modelos, servicios y métodos públicos;
- `LeonidasKnowledgeGapService` registra patrones anonimizados de preguntas que no obtuvieron una respuesta completa;
- `LeonidasKnowledgeAuditService` mide cobertura, vigencia y validaciones pendientes.

## Regla de sincronización

Todo dominio del registro debe tener exactamente un documento principal. La prueba `tests/LeonidasKnowledgeDocumentationTest.php` falla cuando:

- aparece un dominio sin documento;
- desaparece un dominio documentado;
- falta una sección obligatoria;
- un ejecutor conectado no está mencionado;
- el buscador no recupera el documento para una consulta representativa.

## Secciones obligatorias

```md
# Nombre: conocimiento operativo de Leonidas

Dominio: `identificador`.

## Propósito y conceptos
## Reglas de negocio
## Fuentes autorizadas
## Permisos
## Preguntas reales que debe responder
## Ejecutores disponibles
```

## Cómo documentar una regla

Una regla debe:

1. estar respaldada por un servicio, modelo, controlador, catálogo o decisión operativa vigente;
2. indicar cuándo aplica y qué identificador necesita;
3. distinguir una consulta de una modificación;
4. mencionar permisos o segundo factor cuando maneje información sensible;
5. evitar credenciales, endpoints internos, SQL y secretos;
6. describir honestamente los límites cuando no exista un ejecutor.

## Proceso para agregar o cambiar un dominio

1. Actualizar `LeonidasCapabilityRegistry`.
2. Confirmar las claves correspondientes en `LeonidasDomainAccessService`.
3. Crear o actualizar el documento del dominio.
4. Agregar preguntas reales basadas en solicitudes de usuarios.
5. Documentar solo ejecutores presentes en `acciones_ejecutables`.
6. Ejecutar:

```powershell
C:\xampp\php\php.exe tests\LeonidasKnowledgeDocumentationTest.php
C:\xampp\php\php.exe tests\LeonidasDomainCoverageTest.php
```

## Revisión periódica recomendada

Cada cambio relevante en un módulo debe revisar su documento. Mensualmente conviene analizar preguntas que terminaron en `dominio_ayuda`, `dominio_requiere_criterio` o `dominio_fuente_error` para convertir fallos reales en nuevas reglas, sin almacenar datos personales del usuario.

Para consultar el resumen anonimizado:

```powershell
C:\xampp\php\php.exe scripts\reporte_brechas_leonidas.php
```

Para auditar cobertura, controladores documentados, vigencia y revisiones de negocio:

```powershell
C:\xampp\php\php.exe scripts\auditar_conocimiento_leonidas.php
```

El archivo `public/assets/docs/leonidas/REVISIONES.json` registra el área responsable y el estado de validación. `validacion_codigo = verificada` significa que la ficha coincide con la implementación revisada; no debe cambiarse `validacion_negocio` a `validada` hasta que un responsable funcional confirme reglas y excepciones.
