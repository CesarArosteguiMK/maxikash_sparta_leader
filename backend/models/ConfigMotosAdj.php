<?php

namespace Models;

use Core\Model;
use Core\Database;

class ConfigMotosAdj extends Model
{
    public const CLAVE_DIAS_MINIMOS_RUTA = 'tracking_ruta_dias_minimos';
    public const DEFAULT_DIAS_MINIMOS_RUTA = 2;
    private const FAD_TABLA_GLOBAL_HISTORIAL = '__SPARTA_SECRET_REDACTED__.fad_motos_global_estado_historial';
    private const FAD_TABLA_CREDITO_EXCEPCIONES = '__SPARTA_SECRET_REDACTED__.fad_motos_credito_excepciones';
    private const FAD_TABLA_DECISIONES = '__SPARTA_SECRET_REDACTED__.fad_motos_decisiones';

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
            'fad' => $this->obtenerFad(),
        ];
    }

    public function obtenerFad(): array
    {
        try {
            $apagadoActual = $this->db->queryOne(
                "SELECT *
                 FROM " . self::FAD_TABLA_GLOBAL_HISTORIAL . "
                 WHERE estado = 'apagado'
                   AND started_at <= NOW()
                   AND (ended_at IS NULL OR ended_at >= NOW())
                 ORDER BY started_at DESC, id DESC
                 LIMIT 1"
            );

            return [
                'disponible' => true,
                'global_apagado' => (bool) $apagadoActual,
                'global_estado' => $apagadoActual ? 'apagado' : 'activo',
                'apagado_actual' => $apagadoActual,
                'historial_apagados' => $this->obtenerHistorialApagadosFad(),
                'excepciones' => $this->obtenerExcepcionesFad(),
                'decisiones' => $this->obtenerDecisionesFad(),
            ];
        } catch (\Throwable $e) {
            return [
                'disponible' => false,
                'mensaje' => 'No se pudo consultar la configuracion FAD.',
                'error' => $e->getMessage(),
                'global_apagado' => false,
                'global_estado' => 'desconocido',
                'apagado_actual' => null,
                'historial_apagados' => [],
                'excepciones' => [],
                'decisiones' => [],
            ];
        }
    }

    public function actualizarFadGlobal(string $accion, int $idUsuario = 0): array
    {
        $accion = strtolower(trim($accion));
        if (!in_array($accion, ['off', 'soft_on', 'on'], true)) {
            return ['success' => false, 'mensaje' => 'Accion FAD no valida.'];
        }

        return $accion === 'off'
            ? $this->apagarFadGlobal('FAD desactivado temporalmente', $idUsuario)
            : $this->encenderFadGlobal($idUsuario);
    }

    public function apagarFadGlobal(string $motivo, int $idUsuario = 0): array
    {
        $motivo = trim($motivo);
        if ($motivo === '') {
            return ['success' => false, 'mensaje' => 'Captura el motivo para apagar FAD.'];
        }

        try {
            $this->db->CRUD(
                "INSERT INTO " . self::FAD_TABLA_GLOBAL_HISTORIAL . "
                    (estado, started_at, ended_at, motivo, created_by)
                 VALUES
                    ('apagado', NOW(), NULL, :motivo, :created_by)",
                [
                    'motivo' => $motivo,
                    'created_by' => $this->actorFad($idUsuario),
                ]
            );

            return ['success' => true, 'mensaje' => 'FAD apagado correctamente.', 'datos' => $this->obtenerFad()];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo apagar FAD.', 'error' => $e->getMessage()];
        }
    }

    public function encenderFadGlobal(int $idUsuario = 0): array
    {
        try {
            $this->db->CRUD(
                "UPDATE " . self::FAD_TABLA_GLOBAL_HISTORIAL . "
                 SET ended_at = NOW()
                 WHERE estado = 'apagado'
                   AND ended_at IS NULL"
            );

            return ['success' => true, 'mensaje' => 'FAD encendido correctamente.', 'datos' => $this->obtenerFad()];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo encender FAD.', 'error' => $e->getMessage()];
        }
    }

    public function guardarReglaFad(array $data, int $idUsuario = 0): array
    {
        return $this->agregarExcepcionCredito($data, $idUsuario);
    }

    public function agregarExcepcionCredito(array $data, int $idUsuario = 0): array
    {
        $idCredito = trim((string) ($data['id_credito'] ?? ''));
        $motivo = trim((string) ($data['motivo'] ?? $data['reason'] ?? $data['message'] ?? ''));

        if ($idCredito === '') {
            return ['success' => false, 'mensaje' => 'Captura el credito.'];
        }
        if ($motivo === '') {
            return ['success' => false, 'mensaje' => 'Captura el motivo de la excepcion.'];
        }

        try {
            $this->db->CRUD(
                "INSERT INTO " . self::FAD_TABLA_CREDITO_EXCEPCIONES . "
                    (id_credito, action, reason, active, created_by)
                 VALUES
                    (:id_credito, 'no_pedir_fad', :reason, 1, :created_by)",
                [
                    'id_credito' => $idCredito,
                    'reason' => $motivo,
                    'created_by' => $this->actorFad($idUsuario),
                ]
            );

            return ['success' => true, 'mensaje' => 'Excepcion agregada.', 'datos' => $this->obtenerFad()];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo agregar la excepcion.', 'error' => $e->getMessage()];
        }
    }

    public function desactivarExcepcionCredito(int $id, int $idUsuario = 0): array
    {
        if ($id <= 0) {
            return ['success' => false, 'mensaje' => 'Excepcion no valida.'];
        }

        try {
            $this->db->CRUD(
                "UPDATE " . self::FAD_TABLA_CREDITO_EXCEPCIONES . "
                 SET active = 0,
                     deleted_at = NOW(),
                     deleted_by = :deleted_by
                 WHERE id = :id",
                [
                    'id' => $id,
                    'deleted_by' => $this->actorFad($idUsuario),
                ]
            );

            return ['success' => true, 'mensaje' => 'Excepcion desactivada.', 'datos' => $this->obtenerFad()];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo desactivar la excepcion.', 'error' => $e->getMessage()];
        }
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

    private function obtenerHistorialApagadosFad(): array
    {
        return $this->db->queryAll(
            "SELECT
                id,
                estado,
                started_at,
                ended_at,
                motivo,
                created_by,
                created_at
             FROM " . self::FAD_TABLA_GLOBAL_HISTORIAL . "
             ORDER BY started_at DESC, id DESC"
        );
    }

    private function obtenerExcepcionesFad(): array
    {
        return $this->db->queryAll(
            "SELECT
                id,
                id_credito,
                action,
                reason,
                active,
                created_by,
                created_at,
                deleted_at,
                deleted_by
             FROM " . self::FAD_TABLA_CREDITO_EXCEPCIONES . "
             WHERE active = 1
               AND deleted_at IS NULL
             ORDER BY created_at DESC"
        );
    }

    private function obtenerDecisionesFad(): array
    {
        return $this->db->queryAll(
            "SELECT
                id,
                id_operacion,
                id_credito,
                user_id_legacy,
                external_id,
                dictamen_at,
                fad_required,
                fad_decision_reason,
                fad_decision_at,
                matched_rule_json,
                created_at,
                updated_at
             FROM " . self::FAD_TABLA_DECISIONES . "
             ORDER BY fad_decision_at DESC"
        );
    }

    private function actorFad(int $idUsuario = 0): string
    {
        foreach (['usuario_nombre', 'nombre_usuario', 'username', 'email', 'correo'] as $key) {
            if (!empty($_SESSION[$key])) {
                return (string) $_SESSION[$key];
            }
        }

        if ($idUsuario > 0) {
            return 'usuario_' . $idUsuario;
        }

        return 'sparta';
    }
}
