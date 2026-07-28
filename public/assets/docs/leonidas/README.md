# Base de conocimiento operativo de Leonidas

Esta biblioteca describe lo que Leonidas conoce y puede hacer dentro de Sparta. Cada documento corresponde a un dominio operativo y separa expresamente:

- conceptos y reglas de negocio;
- fuentes autorizadas;
- permisos de consulta y de ejecución;
- preguntas que debe poder responder;
- ejecutores realmente conectados;
- límites que no debe sobrepasar.

## Reglas transversales

1. Una consulta de lectura solo usa fuentes y módulos permitidos para la sesión.
2. Un dato operativo debe indicar su fuente y, cuando corresponda, fecha o corte.
3. Leonidas no inventa resultados, SQL, permisos ni comprobantes de ejecución.
4. Una escritura o comunicación requiere un ejecutor aprobado, vista previa, confirmación explícita y auditoría.
5. Que una operación exista en una pantalla de Sparta no significa que Leonidas pueda ejecutarla.
6. Los datos personales, salarios, documentos y accesos se entregan únicamente con permisos especiales.
7. Si falta un identificador, periodo o criterio inequívoco, Leonidas debe pedir el dato mínimo necesario.
8. Si una fuente falla, debe informar la fuente prevista, el motivo y confirmar que no realizó cambios.
9. Las operaciones financieras sensibles requieren dos personas autorizadas distintas.
10. Leonidas no declara éxito hasta volver a consultar la fuente correspondiente.

## Dominios documentados

- Créditos
- Capital Humano
- Convenios
- Motos Adjudicadas
- Direcciones
- Legacy
- Atlas
- Tickets
- Analítica
- Gastos de Cobranza
- Organización
- Servicios

## Cobertura complementaria de plataforma

- `CREDITOS_SUBMODULOS.md`: Estado de Cuenta, aclaraciones, aplicaciones de pago, cierre, condonaciones y crédito problemático.
- `MOTOS_CADENA_OPERATIVA.md`: solicitudes, Atención a Clientes, configuración, almacén, revisión, piso de venta, traspasos, tracking y API.
- `COBRANZA_CAMPO_DESPACHOS.md`: Despachos, Gestión de Campo, Indicadores y Viáticos.
- `PLATAFORMA_USUARIO.md`: Inicio, sesión, Perfil, Notificaciones, Onboarding, Clima e integraciones técnicas.
- `REPORTERIA_PROCESOS.md`: Reportería, Primeros Pagos, Segundómetro, indicadores y reportes de Sabueso.
- `CONFIGURACION_CATALOGOS.md`: países, empresas, departamentos, equivalencias, usuarios, tickets por puesto y formularios.
- `OPERACIONES_AGENTICAS.md`: contrato de ejecución, doble autorización, adjuntos, idempotencia, comprobantes y reversión.

Esta documentación no contiene credenciales, direcciones internas, secretos ni consultas SQL.

## Componentes técnicos del propio asistente

- `Leonidas` es el controlador web de conversación, archivos, confirmaciones, bandeja, voz y medios.
- `LeonidasWhatsApp` recibe el webhook firmado del canal de WhatsApp; la identidad y los permisos se resuelven en el servidor.
- `CapHum` es el controlador principal de los flujos documentados en Capital Humano.
- `Gastoscobranza` implementa paneles, reportes, archivos y procesos del dominio Gastos de Cobranza.
- `SabuesoPaneladminScriptChunk` es un componente auxiliar de la interfaz administrativa de Sabueso y no constituye un módulo de negocio independiente.

El inventario seguro del código reconoce automáticamente todos los controladores, modelos y servicios con contenido aunque todavía no tengan una ficha especializada.
