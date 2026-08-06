-- Módulo de Inducción: tablas e índices. Ejecutar primero.
-- Las llaves foráneas se agregan después con migration_induccion_llaves_foraneas.sql.

-- Catálogo de cursos de inducción.
CREATE TABLE IF NOT EXISTS induccion_curso (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    version VARCHAR(30) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_induccion_curso_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Avance principal de una persona dentro de un curso.
CREATE TABLE IF NOT EXISTS induccion_persona (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_curso INT NOT NULL,
    id_persona INT NOT NULL,
    id_asigna_puesto INT NULL,
    porcentaje_avance DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    estatus CHAR(1) NOT NULL DEFAULT 'S' COMMENT 'S=sin iniciar, E=en proceso, C=completado, I=incompleto',
    celebracion_mostrada TINYINT(1) NOT NULL DEFAULT 0,
    fecha_inicio DATETIME NULL,
    fecha_ultimo_avance DATETIME NULL,
    fecha_termino DATETIME NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_persona_curso (id_induccion_curso, id_persona),
    INDEX idx_induccion_persona_persona (id_persona),
    INDEX idx_induccion_persona_asigna_puesto (id_asigna_puesto),
    INDEX idx_induccion_persona_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catálogo de videos. El código estable identifica funcionalmente cada video.
CREATE TABLE IF NOT EXISTS induccion_video (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_curso INT NOT NULL,
    codigo VARCHAR(60) NOT NULL COMMENT 'Ejemplo: bienvenida',
    titulo VARCHAR(180) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    orden SMALLINT NOT NULL,
    duracion_segundos INT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_video_codigo (id_induccion_curso, codigo),
    UNIQUE KEY uq_induccion_video_orden (id_induccion_curso, orden),
    INDEX idx_induccion_video_activo (id_induccion_curso, activo, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registro de visualización de cada video por persona.
CREATE TABLE IF NOT EXISTS induccion_persona_video (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_persona INT NOT NULL,
    id_induccion_video INT NOT NULL,
    visto TINYINT(1) NOT NULL DEFAULT 0,
    fecha_inicio DATETIME NULL,
    fecha_visto DATETIME NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_persona_video (id_induccion_persona, id_induccion_video),
    INDEX idx_induccion_persona_video_visto (id_induccion_persona, visto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catálogo de evaluaciones corporativas y especializadas.
CREATE TABLE IF NOT EXISTS induccion_evaluacion (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_curso INT NOT NULL,
    codigo VARCHAR(60) NOT NULL,
    nombre VARCHAR(180) NOT NULL,
    tipo VARCHAR(20) NOT NULL COMMENT 'corporativa o puesto',
    calificacion_minima DECIMAL(5,2) NOT NULL DEFAULT 8.00,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_evaluacion_codigo (id_induccion_curso, codigo),
    INDEX idx_induccion_evaluacion_tipo (id_induccion_curso, tipo, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asigna la evaluación especializada que corresponde a cada puesto.
CREATE TABLE IF NOT EXISTS induccion_evaluacion_puesto (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_evaluacion INT NOT NULL,
    id_puesto INT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_evaluacion_puesto (id_induccion_evaluacion, id_puesto),
    INDEX idx_induccion_evaluacion_puesto_puesto (id_puesto, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Banco de preguntas de una evaluación.
CREATE TABLE IF NOT EXISTS induccion_pregunta (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_evaluacion INT NOT NULL,
    texto TEXT NOT NULL,
    orden SMALLINT NOT NULL,
    puntaje DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_pregunta_orden (id_induccion_evaluacion, orden),
    INDEX idx_induccion_pregunta_activa (id_induccion_evaluacion, activa, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Opciones de respuesta y marca de opción correcta.
CREATE TABLE IF NOT EXISTS induccion_pregunta_respuesta (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_pregunta INT NOT NULL,
    texto TEXT NOT NULL,
    es_correcta TINYINT(1) NOT NULL DEFAULT 0,
    orden SMALLINT NOT NULL,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_respuesta_orden (id_induccion_pregunta, orden),
    INDEX idx_induccion_respuesta_correcta (id_induccion_pregunta, es_correcta, activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Intentos de evaluación de una persona.
CREATE TABLE IF NOT EXISTS induccion_persona_evaluacion (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_persona INT NOT NULL,
    id_induccion_evaluacion INT NOT NULL,
    numero_intento SMALLINT NOT NULL DEFAULT 1,
    estatus CHAR(1) NOT NULL DEFAULT 'E' COMMENT 'E=en proceso, A=aprobada, R=reprobada, I=incompleta',
    calificacion DECIMAL(5,2) NULL,
    aprobada TINYINT(1) NOT NULL DEFAULT 0,
    fecha_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_envio DATETIME NULL,
    fecha_evaluacion DATETIME NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_persona_evaluacion_intento (id_induccion_persona, id_induccion_evaluacion, numero_intento),
    INDEX idx_induccion_persona_evaluacion_avance (id_induccion_persona, id_induccion_evaluacion, estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Respuesta elegida por persona en cada intento de evaluación.
CREATE TABLE IF NOT EXISTS induccion_persona_respuesta (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_persona_evaluacion INT NOT NULL,
    id_induccion_pregunta INT NOT NULL,
    id_induccion_pregunta_respuesta INT NOT NULL,
    es_correcta TINYINT(1) NOT NULL DEFAULT 0,
    fecha_respuesta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_persona_respuesta (id_induccion_persona_evaluacion, id_induccion_pregunta),
    INDEX idx_induccion_persona_respuesta_opcion (id_induccion_pregunta_respuesta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Constancias generadas al aprobar las evaluaciones.
CREATE TABLE IF NOT EXISTS induccion_constancia (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_persona INT NOT NULL,
    id_induccion_persona_evaluacion INT NOT NULL,
    tipo VARCHAR(20) NOT NULL COMMENT 'corporativa o puesto',
    folio VARCHAR(80) NOT NULL,
    estatus CHAR(1) NOT NULL DEFAULT 'G' COMMENT 'G=generada, D=descargada',
    fecha_generacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_descarga DATETIME NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_constancia_folio (folio),
    UNIQUE KEY uq_induccion_constancia_evaluacion (id_induccion_persona_evaluacion),
    INDEX idx_induccion_constancia_persona (id_induccion_persona, tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Retroalimentación enviada por la persona al terminar el portal.
CREATE TABLE IF NOT EXISTS induccion_retroalimentacion (
    id INT NOT NULL AUTO_INCREMENT,
    id_induccion_persona INT NOT NULL,
    calificacion TINYINT UNSIGNED NOT NULL,
    comentario TEXT NULL,
    fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_retroalimentacion_persona (id_induccion_persona),
    INDEX idx_induccion_retroalimentacion_calificacion (calificacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catálogo reutilizable de responsabilidades laborales.
CREATE TABLE IF NOT EXISTS induccion_responsabilidad (
    id INT NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_induccion_responsabilidad_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Responsabilidades activas, ordenadas y reutilizables por puesto.
CREATE TABLE IF NOT EXISTS induccion_puesto_responsabilidad (
    id INT NOT NULL AUTO_INCREMENT,
    id_puesto INT NOT NULL,
    id_induccion_responsabilidad INT NOT NULL,
    orden SMALLINT NOT NULL,
    obligatoria TINYINT(1) NOT NULL DEFAULT 1,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_induccion_puesto_responsabilidad (id_puesto, id_induccion_responsabilidad),
    UNIQUE KEY uq_induccion_puesto_responsabilidad_orden (id_puesto, orden),
    INDEX idx_induccion_puesto_responsabilidad_activa (id_puesto, activa, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
