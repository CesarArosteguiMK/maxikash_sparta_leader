-- Cargas iniciales del módulo de Inducción.
-- Incluye curso, videos, evaluaciones y banco corporativo.

-- Curso principal.
INSERT INTO induccion_curso (nombre, descripcion, version, activo)
SELECT
    'Curso de Inducción',
    'Curso de inducción corporativa y por puesto para colaboradores de Maxikash.',
    '2026',
    1
WHERE NOT EXISTS (
    SELECT 1
    FROM induccion_curso
    WHERE nombre = 'Curso de Inducción'
      AND version = '2026'
);

SET @id_curso := (
    SELECT id
    FROM induccion_curso
    WHERE nombre = 'Curso de Inducción'
      AND version = '2026'
    LIMIT 1
);

-- Video institucional y módulos del curso.
INSERT INTO induccion_video
    (id_induccion_curso, codigo, titulo, ruta_archivo, orden, activo)
VALUES
    (@id_curso, 'bienvenida',      'Video institucional de bienvenida', '/onboarding/video?modulo=bienvenida',      1, 1),
    (@id_curso, 'legacyapp',       'LegacyApp',                          '/onboarding/video?modulo=legacyapp',       2, 1),
    (@id_curso, 'asistencia',      'Asistencia',                         '/onboarding/video?modulo=asistencia',      3, 1),
    (@id_curso, 'nomina',          'Pago de Nómina',                     '/onboarding/video?modulo=nomina',          4, 1),
    (@id_curso, 'bonos',           'Bonos e Incentivos',                 '/onboarding/video?modulo=bonos',           5, 1),
    (@id_curso, 'recibos_nomina',  'Recibos de Nómina',                  '/onboarding/video?modulo=recibos_nomina',  6, 1),
    (@id_curso, 'cambio_cuenta',   'Cambio de Cuenta',                   '/onboarding/video?modulo=cambio_cuenta',   7, 1),
    (@id_curso, 'incapacidades',   'Incidencias Médicas',                '/onboarding/video?modulo=incapacidades',   8, 1),
    (@id_curso, 'valores',         'Nuestros Valores',                   '/onboarding/video?modulo=valores',         9, 1),
    (@id_curso, 'cultura',         'Nuestra Cultura',                    '/onboarding/video?modulo=cultura',        10, 1)
ON DUPLICATE KEY UPDATE
    titulo = VALUES(titulo),
    ruta_archivo = VALUES(ruta_archivo),
    activo = VALUES(activo);

-- Evaluaciones corporativa y especializada.
INSERT INTO induccion_evaluacion
    (id_induccion_curso, codigo, nombre, tipo, calificacion_minima, activo)
VALUES
    (@id_curso, 'quiz_corporativo', 'Quiz de Inducción Corporativo', 'corporativa', 8.00, 1),
    (@id_curso, 'quiz_puesto', 'Quiz por Puestos (Específico)', 'puesto', 8.00, 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    calificacion_minima = VALUES(calificacion_minima),
    activo = VALUES(activo);

SET @id_evaluacion_corporativa := (
    SELECT id
    FROM induccion_evaluacion
    WHERE id_induccion_curso = @id_curso
      AND codigo = 'quiz_corporativo'
    LIMIT 1
);

-- Preguntas del Quiz de Inducción Corporativo.
INSERT INTO induccion_pregunta
    (id_induccion_evaluacion, texto, orden, puntaje, activa)
VALUES
    (@id_evaluacion_corporativa, '¿Qué comprobante médico es legalmente válido y aceptado para justificar una incapacidad en Maxikash?', 1, 2.00, 1),
    (@id_evaluacion_corporativa, '¿A qué área se debe dirigir la solicitud formal en caso de requerir un Cambio de Cuenta de Nómina?', 2, 2.00, 1),
    (@id_evaluacion_corporativa, '¿Cómo ejerce un colaborador de Maxikash sus Derechos ARCO para la protección de sus Datos Personales?', 3, 2.00, 1),
    (@id_evaluacion_corporativa, '¿Cuáles son los tres valores fundamentales que guían a Maxikash?', 4, 2.00, 1),
    (@id_evaluacion_corporativa, '¿Cómo se distribuyen tus recibos de nómina digitales?', 5, 2.00, 1)
ON DUPLICATE KEY UPDATE
    texto = VALUES(texto),
    puntaje = VALUES(puntaje),
    activa = VALUES(activa);

SET @pregunta_1 := (SELECT id FROM induccion_pregunta WHERE id_induccion_evaluacion = @id_evaluacion_corporativa AND orden = 1);
SET @pregunta_2 := (SELECT id FROM induccion_pregunta WHERE id_induccion_evaluacion = @id_evaluacion_corporativa AND orden = 2);
SET @pregunta_3 := (SELECT id FROM induccion_pregunta WHERE id_induccion_evaluacion = @id_evaluacion_corporativa AND orden = 3);
SET @pregunta_4 := (SELECT id FROM induccion_pregunta WHERE id_induccion_evaluacion = @id_evaluacion_corporativa AND orden = 4);
SET @pregunta_5 := (SELECT id FROM induccion_pregunta WHERE id_induccion_evaluacion = @id_evaluacion_corporativa AND orden = 5);

-- Respuestas y opción correcta del Quiz Corporativo.
INSERT INTO induccion_pregunta_respuesta
    (id_induccion_pregunta, texto, es_correcta, orden, activa)
VALUES
    (@pregunta_1, 'Receta médica de una farmacia privada de conveniencia', 0, 1, 1),
    (@pregunta_1, 'Incapacidad oficial emitida directamente por el IMSS', 1, 2, 1),
    (@pregunta_2, 'Área de Nóminas, adjuntando el formato firmado y estado de cuenta', 1, 1, 1),
    (@pregunta_2, 'Únicamente al departamento de Soporte Técnico', 0, 2, 1),
    (@pregunta_3, 'Enviando una solicitud formal al correo del departamento Jurídico', 1, 1, 1),
    (@pregunta_3, 'Por medio de una llamada telefónica informal al área de Nóminas', 0, 2, 1),
    (@pregunta_4, 'Responsabilidad, Calidad y Liderazgo', 0, 1, 1),
    (@pregunta_4, 'Innovación, Cercanía y Disciplina', 1, 2, 1),
    (@pregunta_5, 'Se envían automáticamente al correo personal registrado del colaborador', 1, 1, 1),
    (@pregunta_5, 'Se entregan impresos únicamente los días 15 de cada mes', 0, 2, 1)
ON DUPLICATE KEY UPDATE
    texto = VALUES(texto),
    es_correcta = VALUES(es_correcta),
    activa = VALUES(activa);
