-- Módulo de Inducción: llaves foráneas.
-- Ejecutar únicamente después de migration_induccion_tablas.sql.
-- Verificar antes que persona.id, puesto.id y asigna_puesto.id sean INT del mismo tipo.

ALTER TABLE induccion_persona
    ADD CONSTRAINT fk_induccion_persona_curso
        FOREIGN KEY (id_induccion_curso) REFERENCES induccion_curso (id),
    ADD CONSTRAINT fk_induccion_persona_persona
        FOREIGN KEY (id_persona) REFERENCES persona (id),
    ADD CONSTRAINT fk_induccion_persona_asigna_puesto
        FOREIGN KEY (id_asigna_puesto) REFERENCES asigna_puesto (id);

ALTER TABLE induccion_video
    ADD CONSTRAINT fk_induccion_video_curso
        FOREIGN KEY (id_induccion_curso) REFERENCES induccion_curso (id);

ALTER TABLE induccion_persona_video
    ADD CONSTRAINT fk_induccion_persona_video_persona
        FOREIGN KEY (id_induccion_persona) REFERENCES induccion_persona (id),
    ADD CONSTRAINT fk_induccion_persona_video_video
        FOREIGN KEY (id_induccion_video) REFERENCES induccion_video (id);

ALTER TABLE induccion_evaluacion
    ADD CONSTRAINT fk_induccion_evaluacion_curso
        FOREIGN KEY (id_induccion_curso) REFERENCES induccion_curso (id);

ALTER TABLE induccion_evaluacion_puesto
    ADD CONSTRAINT fk_induccion_evaluacion_puesto_evaluacion
        FOREIGN KEY (id_induccion_evaluacion) REFERENCES induccion_evaluacion (id),
    ADD CONSTRAINT fk_induccion_evaluacion_puesto_puesto
        FOREIGN KEY (id_puesto) REFERENCES puesto (id);

ALTER TABLE induccion_pregunta
    ADD CONSTRAINT fk_induccion_pregunta_evaluacion
        FOREIGN KEY (id_induccion_evaluacion) REFERENCES induccion_evaluacion (id);

ALTER TABLE induccion_pregunta_respuesta
    ADD CONSTRAINT fk_induccion_respuesta_pregunta
        FOREIGN KEY (id_induccion_pregunta) REFERENCES induccion_pregunta (id);

ALTER TABLE induccion_persona_evaluacion
    ADD CONSTRAINT fk_induccion_persona_evaluacion_persona
        FOREIGN KEY (id_induccion_persona) REFERENCES induccion_persona (id),
    ADD CONSTRAINT fk_induccion_persona_evaluacion_evaluacion
        FOREIGN KEY (id_induccion_evaluacion) REFERENCES induccion_evaluacion (id);

ALTER TABLE induccion_persona_respuesta
    ADD CONSTRAINT fk_induccion_persona_respuesta_evaluacion
        FOREIGN KEY (id_induccion_persona_evaluacion) REFERENCES induccion_persona_evaluacion (id),
    ADD CONSTRAINT fk_induccion_persona_respuesta_pregunta
        FOREIGN KEY (id_induccion_pregunta) REFERENCES induccion_pregunta (id),
    ADD CONSTRAINT fk_induccion_persona_respuesta_opcion
        FOREIGN KEY (id_induccion_pregunta_respuesta) REFERENCES induccion_pregunta_respuesta (id);

ALTER TABLE induccion_constancia
    ADD CONSTRAINT fk_induccion_constancia_persona
        FOREIGN KEY (id_induccion_persona) REFERENCES induccion_persona (id),
    ADD CONSTRAINT fk_induccion_constancia_evaluacion
        FOREIGN KEY (id_induccion_persona_evaluacion) REFERENCES induccion_persona_evaluacion (id);

ALTER TABLE induccion_retroalimentacion
    ADD CONSTRAINT fk_induccion_retroalimentacion_persona
        FOREIGN KEY (id_induccion_persona) REFERENCES induccion_persona (id);

ALTER TABLE induccion_puesto_responsabilidad
    ADD CONSTRAINT fk_induccion_puesto_responsabilidad_puesto
        FOREIGN KEY (id_puesto) REFERENCES puesto (id),
    ADD CONSTRAINT fk_induccion_puesto_responsabilidad_responsabilidad
        FOREIGN KEY (id_induccion_responsabilidad) REFERENCES induccion_responsabilidad (id);
