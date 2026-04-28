-- Atención a clientes — pestaña «Aprobados»: solo filas enviadas con el botón «Enviar evidencias validadas».
-- Sin esto, cualquier operación en Procesando IA (kanban, arrastre, histórico) aparecía mal en esa pestaña.

ALTER TABLE adj_operacion
    ADD COLUMN atencion_envio_validado TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '1 = pasó por Enviar evidencias validadas en Atención (no solo estatus Procesando IA)';
