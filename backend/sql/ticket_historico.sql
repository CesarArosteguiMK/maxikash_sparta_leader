-- Tabla para registrar todos los tickets cerrados o eliminados.
-- Cada vez que se cierra o elimina un ticket se inserta aquí una copia (snapshot)
-- y luego se hace soft-delete en ticket. Fechas en zona horaria CDMX.
-- Ejecutar una sola vez.

CREATE TABLE IF NOT EXISTS ticket_historico (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_ticket INT NOT NULL COMMENT 'ID original del ticket',
  id_credito INT NULL,
  folio VARCHAR(50) NULL,
  id_tipo_ticket INT NULL,
  tipo_ticket_nombre VARCHAR(100) NULL,
  id_estado_ticket INT NULL,
  estado_ticket_nombre VARCHAR(100) NULL,
  id_prioridad INT NULL,
  prioridad_nombre VARCHAR(100) NULL,
  descripcion_inicial TEXT NULL,
  fecha_creacion DATETIME NULL,
  fecha_vencimiento DATE NULL,
  id_persona_creador INT NULL,
  creador_nombre VARCHAR(200) NULL,
  asignado_nombre VARCHAR(200) NULL COMMENT 'Último asignado al momento de cerrar/eliminar',
  id_persona_elimino INT NULL,
  quien_elimino_nombre VARCHAR(200) NULL,
  fecha_eliminacion DATETIME NOT NULL,
  tipo_accion ENUM('cerrado','eliminado') NOT NULL DEFAULT 'eliminado',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_fecha_eliminacion (fecha_eliminacion),
  INDEX idx_id_ticket (id_ticket),
  INDEX idx_id_credito (id_credito),
  INDEX idx_tipo_accion (tipo_accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de tickets cerrados o eliminados';
