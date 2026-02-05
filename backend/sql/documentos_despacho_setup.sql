-- ============================================================================
-- TABLAS PARA GESTIÓN DE DOCUMENTOS DE DESPACHO
-- ============================================================================
-- Fecha de creación: 2026-02-05
-- Descripción: Sistema de carga y gestión de documentos para despachos
-- ============================================================================

-- Tabla de catálogo de tipos de documentos
CREATE TABLE IF NOT EXISTS `catalogo_documentos_despacho` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_documento` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar documentos comunes de despachos (si no existen)
INSERT INTO `catalogo_documentos_despacho` (`nombre_documento`, `descripcion`) VALUES
('Acta Constitutiva', 'Documento legal que registra la constitución de la empresa o despacho'),
('RFC', 'Registro Federal de Contribuyentes'),
('Comprobante de Domicilio', 'Comprobante domiciliario vigente (luz, agua, teléfono, etc.)'),
('Identificación Oficial', 'INE, Pasaporte o Cédula Profesional'),
('Poder Notarial', 'Documento que acredita la representación legal'),
('Contrato de Prestación de Servicios', 'Contrato firmado entre la empresa y el despacho'),
('Comprobante de Situación Fiscal', 'Constancia de situación fiscal del SAT'),
('Carta Compromiso', 'Carta compromiso firmada de cumplimiento de obligaciones')
ON DUPLICATE KEY UPDATE id=id;

-- Tabla de documentos subidos por despacho
CREATE TABLE IF NOT EXISTS `documentos_despacho` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_despacho` int(11) NOT NULL COMMENT 'FK a despachos.id',
  `id_catalogo_documento` int(11) NOT NULL COMMENT 'FK a catalogo_documentos_despacho.id',
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `fecha_carga` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estatus` enum('Vigente','Vencido','Rechazado') NOT NULL DEFAULT 'Vigente',
  `id_persona_carga` int(11) DEFAULT NULL COMMENT 'FK a persona.id - Usuario que cargó el documento',
  PRIMARY KEY (`id`),
  KEY `idx_id_despacho` (`id_despacho`),
  KEY `idx_id_catalogo` (`id_catalogo_documento`),
  KEY `idx_estatus` (`estatus`),
  CONSTRAINT `fk_documentos_despacho_despacho` FOREIGN KEY (`id_despacho`) REFERENCES `despachos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_documentos_despacho_catalogo` FOREIGN KEY (`id_catalogo_documento`) REFERENCES `catalogo_documentos_despacho` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_documentos_despacho_persona` FOREIGN KEY (`id_persona_carga`) REFERENCES `persona` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INDICES ADICIONALES PARA OPTIMIZACIÓN
-- ============================================================================

-- Índice compuesto para búsquedas comunes
CREATE INDEX idx_despacho_catalogo ON documentos_despacho(id_despacho, id_catalogo_documento);

-- ============================================================================
-- COMENTARIOS Y NOTAS
-- ============================================================================
-- 
-- NOTAS DE USO:
-- 1. La tabla catalogo_documentos_despacho contiene los tipos de documentos requeridos
-- 2. La tabla documentos_despacho almacena los documentos cargados por cada despacho
-- 3. El campo estatus permite marcar documentos como Vigente, Vencido o Rechazado
-- 4. Los archivos físicos se almacenan en: /uploads/documentos_despacho/
-- 5. Al eliminar un despacho, sus documentos se eliminan en cascada
-- 
-- ============================================================================
