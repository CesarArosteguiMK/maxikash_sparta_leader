-- Banco especializado vigente: tres bancos específicos y un banco genérico reutilizable.
-- Requiere haber ejecutado migration_induccion_cargas_iniciales.sql.

SET @id_curso := (
    SELECT id FROM induccion_curso
    WHERE nombre = 'Curso de Inducción' AND version = '2026'
    LIMIT 1
);

-- Bancos específicos existentes en cuestionario puesto.html.
INSERT INTO induccion_evaluacion
    (id_induccion_curso, codigo, nombre, tipo, calificacion_minima, activo)
VALUES
    (@id_curso, 'quiz_gestor_domiciliaria', 'Quiz de Gestor de Cobranza Domiciliaria', 'puesto', 8.00, 1),
    (@id_curso, 'quiz_gestor_telefonica', 'Quiz de Gestor de Cobranza Telefónica', 'puesto', 8.00, 1),
    (@id_curso, 'quiz_asesor_credito', 'Quiz de Asesor de Crédito Individual', 'puesto', 8.00, 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    calificacion_minima = VALUES(calificacion_minima),
    activo = VALUES(activo);

-- Estructura temporal con preguntas, tres opciones y respuesta correcta.
CREATE TEMPORARY TABLE tmp_induccion_banco_actual (
    codigo_evaluacion VARCHAR(60) COLLATE utf8mb4_unicode_ci NOT NULL,
    orden SMALLINT NOT NULL,
    pregunta TEXT NOT NULL,
    opcion_1 TEXT NOT NULL,
    correcta_1 TINYINT(1) NOT NULL,
    opcion_2 TEXT NOT NULL,
    correcta_2 TINYINT(1) NOT NULL,
    opcion_3 TEXT NOT NULL,
    correcta_3 TINYINT(1) NOT NULL,
    PRIMARY KEY (codigo_evaluacion, orden)
);

INSERT INTO tmp_induccion_banco_actual VALUES
('quiz_gestor_domiciliaria', 1, '¿Cuál es el primer paso obligatorio al presentarse en el domicilio del cliente?',
 'Ingresar de forma autónoma a la propiedad privada sin aguardar respuesta.', 0,
 'Exigir el pago total inmediato con voz fuerte para ejercer presión.', 0,
 'Identificarse con credencial corporativa vigente y explicar el motivo de la visita de forma educada.', 1),
('quiz_gestor_domiciliaria', 2, 'Si el deudor legítimo no se encuentra, pero un familiar directo atiende la puerta, ¿cómo procede?',
 'Dejar un citatorio confidencial cerrado dirigido al titular, sin revelar montos ni datos sensibles a terceros.', 1,
 'Detallar el estado de cuenta y las consecuencias legales al familiar para que lo presione.', 0,
 'Retirarse del lugar de inmediato sin dejar ningún tipo de notificación física.', 0),
('quiz_gestor_domiciliaria', 3, '¿Qué acción debe tomar si el cliente realiza un abono en efectivo en campo?',
 'Emitir un recibo foliado oficial al instante y subir la evidencia a la plataforma de inmediato.', 1,
 'Aceptar transferencias bancarias directas a la cuenta de ahorros personal del gestor.', 0,
 'Guardar el dinero en la billetera y reportarlo hasta el cierre de operaciones del fin de semana.', 0),
('quiz_gestor_domiciliaria', 4, 'Ante una actitud hostil o agresión verbal física inminente en el domicilio, ¿cuál es el protocolo?',
 'Discutir firmemente con el cliente hasta lograr que firme una promesa de pago.', 0,
 'Solicitar el auxilio inmediato de otros gestores de campo de zonas aledañas para confrontación.', 0,
 'Retirarse inmediatamente resguardando su integridad física y levantar un reporte de incidencia en el sistema.', 1),
('quiz_gestor_domiciliaria', 5, '¿Cuál es la herramienta tecnológica obligatoria para trazar la ruta de visitas diarias?',
 'Elegir las colonias de forma aleatoria basándose en la experiencia personal diaria.', 0,
 'El sistema de geolocalización y mapas integrado en el dispositivo corporativo.', 1,
 'Utilizar mapas impresos obsoletos ajenos al control de la plataforma central.', 0),
('quiz_gestor_domiciliaria', 6, 'Si el domicilio asignado en el sistema resulta ser un terreno baldío o dirección inexistente, usted debe:',
 'Registrar la verificación domiciliaria fallida adjuntando evidencia fotográfica de las nomenclaturas.', 1,
 'Omitir la cuenta y continuar con la siguiente visita sin dejar registro del error.', 0,
 'Inventar datos de contacto de vecinos simulando haber realizado la validación física.', 0),
('quiz_gestor_domiciliaria', 7, '¿Qué norma legal prohíbe revelar los datos de deudas a los vecinos del cliente?',
 'El Código de Comercio en su sección de correspondencias estándar.', 0,
 'La Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP).', 1,
 'La Ley General de Instituciones de Crédito Bancario General.', 0),
('quiz_gestor_domiciliaria', 8, '¿Cuál es el objetivo principal del Gestor Domiciliario en Maxikash?',
 'Embargar bienes de manera autónoma durante la primera visita técnica.', 0,
 'Clausurar locales comerciales de los acreditados con firmas simuladas.', 0,
 'Recuperar la cartera vencida mediante la conciliación, ofreciendo planes aprobados por el sistema.', 1),
('quiz_gestor_domiciliaria', 9, '¿Qué se entiende por un ''Convenio de Pago'' válido en campo?',
 'Un acuerdo verbal informal donde el deudor promete pagar en una fecha imprecisa.', 0,
 'Aquel estructurado por el sistema central, firmado por el cliente y cargado en la aplicación.', 1,
 'Un documento genérico en blanco firmado únicamente por el deudor externo.', 0),
('quiz_gestor_domiciliaria', 10, '¿En qué horarios está autorizado realizar visitas de cobranza según las normativas vigentes?',
 'Únicamente en los horarios permitidos por la regulación legal aplicable (7:00 AM a 10:00 PM).', 1,
 'A cualquier hora de la madrugada siempre y cuando se localice al titular en casa.', 0,
 'Exclusivamente en días festivos y fines de semana después de la medianoche.', 0),

('quiz_gestor_telefonica', 1, '¿Cuál es el saludo inicial obligatorio en cada llamada saliente de cobranza?',
 'Mencionar el nombre del ejecutivo, la institución Maxikash y validar la identidad del titular.', 1,
 'Preguntar inmediatamente por qué no ha realizado su pago correspondiente.', 0,
 'Exigir los datos bancarios secretos sin presentarse de manera formal.', 0),
('quiz_gestor_telefonica', 2, 'Si el cliente alega que ya pagó pero no se refleja en el sistema, ¿qué se solicita?',
 'El comprobante de transferencia o depósito para enviarlo a validación con el área de conciliación.', 1,
 'Que vuelva a pagar de inmediato y que pida la devolución de su primer pago después.', 0,
 'Dar de baja la deuda basándose únicamente en la palabra del cliente sin soporte.', 0),
('quiz_gestor_telefonica', 3, '¿Qué tono de voz e identidad de comunicación se debe mantener ante clientes difíciles?',
 'Tono profesional, firme, empático, asertivo y libre de confrontaciones.', 1,
 'Tono impositivo e irónico para demostrar la autoridad de la institución financiera.', 0,
 'Tono sumiso cediendo a todas las demandas informales fuera de contrato.', 0),
('quiz_gestor_telefonica', 4, '¿Cómo debe documentarse una llamada telefónica en el CRM corporativo?',
 'Registrando un resumen claro del estatus (Promesa de pago, Negativa, Buzón) con fecha y hora.', 1,
 'Colocando solo un punto o dejando el espacio en blanco si el cliente no contestó.', 0,
 'Escribir comentarios subjetivos o insultos sobre el deudor en la bitácora pública.', 0),
('quiz_gestor_telefonica', 5, '¿Qué constituye una violación grave a la confidencialidad de datos por teléfono?',
 'Dar información de saldos y moras a un tercero o familiar que contestó el teléfono.', 1,
 'Solicitar al titular que confirme los últimos dígitos de su año de nacimiento para autenticación.', 0,
 'Mencionar el nombre de la fintech Maxikash exclusivamente al titular de la cuenta.', 0),
('quiz_gestor_telefonica', 6, '¿Qué es una ''Promesa de Pago (PP)'' en la gestión telefónica?',
 'Un compromiso con fecha específica y monto exacto registrado en el sistema.', 1,
 'Una declaración vaga donde el cliente menciona que ''tratará de conseguir dinero''.', 0,
 'La desconexión total de la línea telefónica sin intenciones de abono.', 0),
('quiz_gestor_telefonica', 7, 'Si la llamada es transferida al buzón de voz de manera repetitiva, ¿cuál es la acción correcta?',
 'Tipificar la llamada como Buzón de Voz y programar el reintento automático por sistema.', 1,
 'Llamar compulsivamente cada dos minutos desde su teléfono personal.', 0,
 'Bloquear el expediente del cliente permanentemente asignándolo como incobrable.', 0),
('quiz_gestor_telefonica', 8, 'Al recibir insultos o palabras altisonantes por parte de un deudor, el protocolo indica:',
 'Advertir respetuosamente que de continuar con el lenguaje la llamada será terminada, y colgar si persiste.', 1,
 'Responder con el mismo lenguaje para equilibrar la comunicación y no dejarse intimidar.', 0,
 'Transferir la llamada inmediatamente a la dirección general sin mediar advertencias.', 0),
('quiz_gestor_telefonica', 9, '¿Cuál es el beneficio de ofrecer una reestructura telefónica autorizada por riesgos?',
 'Ayudar al cliente a regularizar su situación reduciendo su pago mensual incrementando el plazo formal.', 1,
 'Condonar el 100% de la deuda original sin autorización formal de la dirección general.', 0,
 'Generar cobros extras de intereses moratorios ocultos sin el consentimiento del deudor.', 0),
('quiz_gestor_telefonica', 10, 'El uso de marcadores predictivos automatizados en el Call Center nos ayuda a:',
 'Maximizar el tiempo efectivo de conversación disminuyendo los tiempos muertos entre folios.', 1,
 'Espiar las conversaciones personales de otros compañeros dentro de la misma sala.', 0,
 'Marcar a números telefónicos inexistentes fuera de la República Mexicana.', 0),

('quiz_asesor_credito', 1, '¿Qué documento es indispensable para iniciar el cotejo de identidad de un solicitante de crédito?',
 'Identificación oficial vigente (INE o Pasaporte) original y legible.', 1,
 'Cualquier credencial con fotografía, incluso si está vencida hace años.', 0,
 'Una fotografía digital borrosa de una credencial escolar antigua.', 0),
('quiz_asesor_credito', 2, '¿Cómo se define la capacidad de pago de un cliente durante la entrevista comercial?',
 'La diferencia entre los ingresos mensuales demostrables y sus gastos fijos o deudas vigentes.', 1,
 'El monto total de dinero que el cliente dice que le sobra cada quincena de manera verbal.', 0,
 'Los ahorros familiares hipotéticos que el solicitante espera recibir en el futuro.', 0),
('quiz_asesor_credito', 3, '¿Qué acción constituye un conflicto de interés grave para un Asesor de Crédito?',
 'Aprobar u operar expedientes financieros de familiares directos o amigos cercanos.', 1,
 'Ofrecer las tasas de interés institucionales vigentes publicadas en el catálogo oficial.', 0,
 'Asistir a las capacitaciones quincenales obligatorias organizadas por capital humano.', 0),
('quiz_asesor_credito', 4, '¿Cuál es la manera correcta de manejar el historial del Buró de Crédito del cliente?',
 'Analizarlo de forma estrictamente confidencial en el sistema para determinar el nivel de riesgo.', 1,
 'Imprimirlo y enseñárselo a otros clientes como ejemplo de mal historial.', 0,
 'Vender la base de datos de los historiales a agencias comerciales externas.', 0),
('quiz_asesor_credito', 5, 'Si detecta alteraciones evidentes o falsificación en un comprobante de ingresos, usted debe:',
 'Rechazar el trámite inmediatamente y dar aviso al área de prevención de fraudes.', 1,
 'Aceptar el documento simulando no darse cuenta para cumplir con la meta de ventas mensual.', 0,
 'Modificar los números manualmente en Photoshop para cuadrar el perfil.', 0),
('quiz_asesor_credito', 6, '¿Qué es el CAT en los productos financieros de Maxikash?',
 'El Costo Anual Total, indicador financiero que incorpora la totalidad de costos y gastos del crédito.', 1,
 'La Comisión por Apertura Temprana cobrada únicamente a cuentas platino.', 0,
 'El Control de Activos Totales del departamento de auditoría interna corporativa.', 0),
('quiz_asesor_credito', 7, '¿Cuál es el canal oficial para que el cliente reciba el depósito de su crédito autorizado?',
 'Transferencia interbancaria SPEI directo a la cuenta CLABE validada a nombre del titular.', 1,
 'Entrega de dinero en efectivo por medio de los asesores en cafeterías públicas.', 0,
 'Depósitos dirigidos a tarjetas de tiendas departamentales de terceros.', 0),
('quiz_asesor_credito', 8, '¿Por qué es fundamental realizar una visita de validación al negocio o empleo del solicitante?',
 'Para constatar la existencia física, arraigo y veracidad de la fuente de ingresos declarada.', 1,
 'Para solicitar muestras gratuitas del producto que comercializa el cliente.', 0,
 'Para auditar las declaraciones fiscales anuales de la empresa directamente en sitio.', 0),
('quiz_asesor_credito', 9, '¿Qué rol juegan las referencias personales en el expediente de crédito?',
 'Servir como canales alternos de localización y validación de la moralidad crediticia.', 1,
 'Actuar automáticamente como avales legales solidarios obligados a liquidar el saldo.', 0,
 'Sustituir la firma digital del titular en caso de fallecimiento imprevisto.', 0),
('quiz_asesor_credito', 10, 'Al promover los productos de Maxikash, el asesor debe destacar siempre:',
 'Los términos, condiciones, comisiones y tasas de interés reales de manera transparente.', 1,
 'Promesas falsas de que el crédito no genera intereses si se paga con retrasos.', 0,
 'Garantías ficticias sobre la eliminación del registro en buró de crédito en 24 horas.', 0),

('quiz_puesto', 1, '[PUESTO] - Criterio Operativo Especializado N.º1: ¿Cuál es el lineamiento regulado para mitigar riesgos en este proceso operativo?',
 'Aplicar los protocolos estandarizados de control, documentación interna y auditoría del puesto institucional.', 1,
 'Proceder de forma aislada sin registrar movimientos ni bitácoras en el software central de Maxikash.', 0,
 'Delegar la responsabilidad total de las operaciones críticas a prestadores de servicio externos sin contrato.', 0),
('quiz_puesto', 2, '[PUESTO] - Criterio Operativo Especializado N.º2: ¿Cuál es el lineamiento regulado para mitigar riesgos en este proceso operativo?',
 'Aplicar los protocolos estandarizados de control, documentación interna y auditoría del puesto institucional.', 1,
 'Proceder de forma aislada sin registrar movimientos ni bitácoras en el software central de Maxikash.', 0,
 'Delegar la responsabilidad total de las operaciones críticas a prestadores de servicio externos sin contrato.', 0),
('quiz_puesto', 3, '[PUESTO] - Criterio Operativo Especializado N.º3: ¿Cuál es el lineamiento regulado para mitigar riesgos en este proceso operativo?',
 'Aplicar los protocolos estandarizados de control, documentación interna y auditoría del puesto institucional.', 1,
 'Proceder de forma aislada sin registrar movimientos ni bitácoras en el software central de Maxikash.', 0,
 'Delegar la responsabilidad total de las operaciones críticas a prestadores de servicio externos sin contrato.', 0),
('quiz_puesto', 4, '[PUESTO] - Criterio Operativo Especializado N.º4: ¿Cuál es el lineamiento regulado para mitigar riesgos en este proceso operativo?',
 'Aplicar los protocolos estandarizados de control, documentación interna y auditoría del puesto institucional.', 1,
 'Proceder de forma aislada sin registrar movimientos ni bitácoras en el software central de Maxikash.', 0,
 'Delegar la responsabilidad total de las operaciones críticas a prestadores de servicio externos sin contrato.', 0),
('quiz_puesto', 5, '[PUESTO] - Criterio Operativo Especializado N.º5: ¿Cuál es el lineamiento regulado para mitigar riesgos en este proceso operativo?',
 'Aplicar los protocolos estandarizados de control, documentación interna y auditoría del puesto institucional.', 1,
 'Proceder de forma aislada sin registrar movimientos ni bitácoras en el software central de Maxikash.', 0,
 'Delegar la responsabilidad total de las operaciones críticas a prestadores de servicio externos sin contrato.', 0),
('quiz_puesto', 6, '[PUESTO] - Criterio Operativo Especializado N.º6: ¿Cuál es el lineamiento regulado para mitigar riesgos en este proceso operativo?',
 'Aplicar los protocolos estandarizados de control, documentación interna y auditoría del puesto institucional.', 1,
 'Proceder de forma aislada sin registrar movimientos ni bitácoras en el software central de Maxikash.', 0,
 'Delegar la responsabilidad total de las operaciones críticas a prestadores de servicio externos sin contrato.', 0),
('quiz_puesto', 7, '[PUESTO] - Criterio Operativo Especializado N.º7: ¿Cuál es el lineamiento regulado para mitigar riesgos en este proceso operativo?',
 'Aplicar los protocolos estandarizados de control, documentación interna y auditoría del puesto institucional.', 1,
 'Proceder de forma aislada sin registrar movimientos ni bitácoras en el software central de Maxikash.', 0,
 'Delegar la responsabilidad total de las operaciones críticas a prestadores de servicio externos sin contrato.', 0),
('quiz_puesto', 8, '[PUESTO] - Criterio Operativo Especializado N.º8: ¿Cuál es el lineamiento regulado para mitigar riesgos en este proceso operativo?',
 'Aplicar los protocolos estandarizados de control, documentación interna y auditoría del puesto institucional.', 1,
 'Proceder de forma aislada sin registrar movimientos ni bitácoras en el software central de Maxikash.', 0,
 'Delegar la responsabilidad total de las operaciones críticas a prestadores de servicio externos sin contrato.', 0),
('quiz_puesto', 9, '[PUESTO] - Criterio Operativo Especializado N.º9: ¿Cuál es el lineamiento regulado para mitigar riesgos en este proceso operativo?',
 'Aplicar los protocolos estandarizados de control, documentación interna y auditoría del puesto institucional.', 1,
 'Proceder de forma aislada sin registrar movimientos ni bitácoras en el software central de Maxikash.', 0,
 'Delegar la responsabilidad total de las operaciones críticas a prestadores de servicio externos sin contrato.', 0),
('quiz_puesto', 10, '[PUESTO] - Criterio Operativo Especializado N.º10: ¿Cuál es el lineamiento regulado para mitigar riesgos en este proceso operativo?',
 'Aplicar los protocolos estandarizados de control, documentación interna y auditoría del puesto institucional.', 1,
 'Proceder de forma aislada sin registrar movimientos ni bitácoras en el software central de Maxikash.', 0,
 'Delegar la responsabilidad total de las operaciones críticas a prestadores de servicio externos sin contrato.', 0);

-- Inserta o actualiza las preguntas usando el código del banco.
INSERT INTO induccion_pregunta
    (id_induccion_evaluacion, texto, orden, puntaje, activa)
SELECT e.id, t.pregunta, t.orden, 1.00, 1
FROM tmp_induccion_banco_actual t
INNER JOIN induccion_evaluacion e
    ON e.id_induccion_curso = @id_curso
   AND e.codigo = t.codigo_evaluacion
ON DUPLICATE KEY UPDATE
    texto = VALUES(texto),
    puntaje = VALUES(puntaje),
    activa = VALUES(activa);

-- Inserta o actualiza las tres opciones de cada pregunta.
INSERT INTO induccion_pregunta_respuesta
    (id_induccion_pregunta, texto, es_correcta, orden, activa)
SELECT q.id, t.opcion_1, t.correcta_1, 1, 1
FROM tmp_induccion_banco_actual t
INNER JOIN induccion_evaluacion e ON e.id_induccion_curso = @id_curso AND e.codigo = t.codigo_evaluacion
INNER JOIN induccion_pregunta q ON q.id_induccion_evaluacion = e.id AND q.orden = t.orden
UNION ALL
SELECT q.id, t.opcion_2, t.correcta_2, 2, 1
FROM tmp_induccion_banco_actual t
INNER JOIN induccion_evaluacion e ON e.id_induccion_curso = @id_curso AND e.codigo = t.codigo_evaluacion
INNER JOIN induccion_pregunta q ON q.id_induccion_evaluacion = e.id AND q.orden = t.orden
UNION ALL
SELECT q.id, t.opcion_3, t.correcta_3, 3, 1
FROM tmp_induccion_banco_actual t
INNER JOIN induccion_evaluacion e ON e.id_induccion_curso = @id_curso AND e.codigo = t.codigo_evaluacion
INNER JOIN induccion_pregunta q ON q.id_induccion_evaluacion = e.id AND q.orden = t.orden
ON DUPLICATE KEY UPDATE
    texto = VALUES(texto),
    es_correcta = VALUES(es_correcta),
    activa = VALUES(activa);

DROP TEMPORARY TABLE IF EXISTS tmp_induccion_banco_actual;
