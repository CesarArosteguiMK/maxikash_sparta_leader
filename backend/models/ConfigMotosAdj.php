<?php

namespace Models;

use Core\Model;
use Core\Database;

class ConfigMotosAdj extends Model
{
    public const CLAVE_DIAS_MINIMOS_RUTA = 'tracking_ruta_dias_minimos';
    public const DEFAULT_DIAS_MINIMOS_RUTA = 2;

    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
        $this->asegurarTabla();
        $this->asegurarDefaults();
    }

    private function asegurarTabla(): void
    {
        try {
            $this->db->CRUD(
                "CREATE TABLE IF NOT EXISTS `config_motos_adj` (
                    `id_config` INT NOT NULL AUTO_INCREMENT,
                    `clave` VARCHAR(120) NOT NULL,
                    `valor` TEXT NULL,
                    `tipo_dato` ENUM('int','string','bool','json') NOT NULL DEFAULT 'string',
                    `descripcion` VARCHAR(255) NULL,
                    `activo` TINYINT(1) NOT NULL DEFAULT 1,
                    `actualizado_por` INT NULL,
                    `fecha_alta` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id_config`),
                    UNIQUE KEY `ux_config_motos_adj_clave` (`clave`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
        } catch (\Throwable $e) {
            // No bloquear la vista si el usuario de BD no tiene DDL.
        }
    }

    private function asegurarDefaults(): void
    {
        try {
            $row = $this->db->queryOne(
                "SELECT clave FROM config_motos_adj WHERE clave = :clave LIMIT 1",
                ['clave' => self::CLAVE_DIAS_MINIMOS_RUTA]
            );
            if (!$row) {
                $this->db->CRUD(
                    "INSERT INTO config_motos_adj (clave, valor, tipo_dato, descripcion, activo)
                     VALUES (:clave, :valor, 'int', :descripcion, 1)",
                    [
                        'clave' => self::CLAVE_DIAS_MINIMOS_RUTA,
                        'valor' => (string) self::DEFAULT_DIAS_MINIMOS_RUTA,
                        'descripcion' => 'Dias minimos de anticipacion para programar rutas de recoleccion.',
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Default en codigo si la tabla no esta disponible.
        }
    }

    public static function obtenerDiasMinimosRuta(): int
    {
        try {
            $dao = new self();
            $row = $dao->db->queryOne(
                "SELECT valor FROM config_motos_adj WHERE clave = :clave AND activo = 1 LIMIT 1",
                ['clave' => self::CLAVE_DIAS_MINIMOS_RUTA]
            );
            $dias = isset($row['valor']) ? (int) $row['valor'] : self::DEFAULT_DIAS_MINIMOS_RUTA;
            return max(0, min(365, $dias));
        } catch (\Throwable $e) {
            return self::DEFAULT_DIAS_MINIMOS_RUTA;
        }
    }

    public function obtener(): array
    {
        $dias = self::obtenerDiasMinimosRuta();
        return [
            'tracking' => [
                'ruta_dias_minimos' => $dias,
            ],
        ];
    }

    public function guardar(array $data, int $idUsuario = 0): array
    {
        $dias = (int) ($data['ruta_dias_minimos'] ?? self::DEFAULT_DIAS_MINIMOS_RUTA);
        $dias = max(0, min(365, $dias));

        try {
            $this->db->CRUD(
                "INSERT INTO config_motos_adj (clave, valor, tipo_dato, descripcion, activo, actualizado_por)
                 VALUES (:clave, :valor, 'int', :descripcion, 1, :usuario)
                 ON DUPLICATE KEY UPDATE
                    valor = VALUES(valor),
                    tipo_dato = VALUES(tipo_dato),
                    descripcion = VALUES(descripcion),
                    activo = 1,
                    actualizado_por = VALUES(actualizado_por),
                    fecha_actualizacion = NOW()",
                [
                    'clave' => self::CLAVE_DIAS_MINIMOS_RUTA,
                    'valor' => (string) $dias,
                    'descripcion' => 'Dias minimos de anticipacion para programar rutas de recoleccion.',
                    'usuario' => $idUsuario > 0 ? $idUsuario : null,
                ]
            );
            return ['success' => true, 'mensaje' => 'Configuracion guardada.', 'datos' => $this->obtener()];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo guardar la configuracion.', 'error' => $e->getMessage()];
        }
    }
}
