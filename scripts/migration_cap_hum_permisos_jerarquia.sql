-- Permisos jerárquicos de Gestión de Personal
-- Jerarquía soportada: pais -> area -> departamento -> puesto

CREATE TABLE IF NOT EXISTS `privilegios_jerarquia` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_persona` INT NOT NULL,
  `nivel` ENUM('pais','area','departamento','puesto') NOT NULL,
  `id_nodo` INT NOT NULL,
  `fecha_alta` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_persona_nivel_nodo` (`id_persona`, `nivel`, `id_nodo`),
  KEY `idx_nivel_nodo` (`nivel`, `id_nodo`),
  KEY `idx_persona` (`id_persona`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
