<?php

namespace Models;

use Core\Model;
use Core\Database;

class ConfigMotosAdj extends Model
{
    public const CLAVE_DIAS_MINIMOS_RUTA = 'tracking_ruta_dias_minimos';
    public const DEFAULT_DIAS_MINIMOS_RUTA = 2;
    private const FAD_TABLA_REGLAS = '__SPARTA_SECRET_REDACTED__.fad_motos_config_reglas';
    private const FAD_TABLA_EVENTOS = '__SPARTA_SECRET_REDACTED__.fad_motos_config_eventos';
    private const FAD_SCOPES = ['global', 'user', 'external_id', 'credit', 'operation', 'dictamen'];
    private const FAD_MODES = ['off', 'optional', 'required'];
    private const FAD_ROLLOUT_MODES = ['manual', 'automatic', 'pilot'];

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
            $global = $this->db->queryOne(
                "SELECT
                    id, scope, scope_value, enabled, requires_fad, mode, rollout_mode,
                    notify_pending, show_badge, block_close, effective_from, effective_to,
                    message, metadata_json, priority, is_active
                 FROM " . self::FAD_TABLA_REGLAS . "
                 WHERE scope = 'global'
                   AND scope_value = '*'
                   AND is_active = 1
                 ORDER BY id DESC
                 LIMIT 1"
            );

            $reglas = $this->db->queryAll(
                "SELECT
                    id, scope, scope_value, enabled, requires_fad, mode, rollout_mode,
                    notify_pending, show_badge, block_close, effective_from, effective_to,
                    message, metadata_json, priority, is_active
                 FROM " . self::FAD_TABLA_REGLAS . "
                 WHERE is_active = 1
                   AND (effective_from IS NULL OR effective_from <= CURDATE())
                   AND (effective_to IS NULL OR effective_to >= CURDATE())
                 ORDER BY priority DESC, id DESC"
            );

            return [
                'disponible' => true,
                'global' => $this->normalizarReglaFad($global),
                'reglas' => array_map([$this, 'normalizarReglaFad'], $reglas),
                'scopes' => self::FAD_SCOPES,
                'modes' => self::FAD_MODES,
                'rollout_modes' => self::FAD_ROLLOUT_MODES,
            ];
        } catch (\Throwable $e) {
            return [
                'disponible' => false,
                'mensaje' => 'No se pudo consultar la configuracion FAD.',
                'error' => $e->getMessage(),
                'global' => null,
                'reglas' => [],
                'scopes' => self::FAD_SCOPES,
                'modes' => self::FAD_MODES,
                'rollout_modes' => self::FAD_ROLLOUT_MODES,
            ];
        }
    }

    public function actualizarFadGlobal(string $accion, int $idUsuario = 0): array
    {
        $accion = strtolower(trim($accion));
        if (!in_array($accion, ['off', 'soft_on'], true)) {
            return ['success' => false, 'mensaje' => 'Accion FAD no valida.'];
        }

        $valores = $accion === 'off'
            ? [
                'enabled' => 0,
                'requires_fad' => 0,
                'mode' => 'off',
                'rollout_mode' => 'manual',
                'show_badge' => 0,
                'block_close' => 0,
                'notify_pending' => 0,
                'message' => 'FAD apagado globalmente.',
                'priority' => 100,
            ]
            : [
                'enabled' => 1,
                'requires_fad' => 1,
                'mode' => 'required',
                'rollout_mode' => 'manual',
                'show_badge' => 1,
                'block_close' => 0,
                'notify_pending' => 0,
                'message' => 'FAD pendiente para moto adjudicada',
                'priority' => 100,
            ];

        try {
            $actual = $this->db->queryOne(
                "SELECT id FROM " . self::FAD_TABLA_REGLAS . "
                 WHERE scope = 'global' AND scope_value = '*' AND is_active = 1
                 ORDER BY id DESC
                 LIMIT 1"
            );

            if ($actual && !empty($actual['id'])) {
                $this->db->CRUD(
                    "UPDATE " . self::FAD_TABLA_REGLAS . "
                     SET enabled = :enabled,
                         requires_fad = :requires_fad,
                         mode = :mode,
                         rollout_mode = :rollout_mode,
                         show_badge = :show_badge,
                         block_close = :block_close,
                         notify_pending = :notify_pending,
                         message = :message,
                         priority = :priority
                     WHERE id = :id",
                    $valores + ['id' => (int) $actual['id']]
                );
                $idRegla = (int) $actual['id'];
            } else {
                $this->db->CRUD(
                    "INSERT INTO " . self::FAD_TABLA_REGLAS . "
                        (scope, scope_value, enabled, requires_fad, mode, rollout_mode,
                         notify_pending, show_badge, block_close, effective_from, message,
                         priority, is_active, created_by)
                     VALUES
                        ('global', '*', :enabled, :requires_fad, :mode, :rollout_mode,
                         :notify_pending, :show_badge, :block_close, CURDATE(), :message,
                         :priority, 1, :created_by)",
                    $valores + ['created_by' => $idUsuario > 0 ? (string) $idUsuario : 'sparta']
                );
                $idRegla = $this->db->lastInsertId();
            }

            $this->registrarEventoFad($idRegla, 'global_' . $accion, $idUsuario, $valores);
            return ['success' => true, 'mensaje' => 'Configuracion global FAD actualizada.', 'datos' => $this->obtenerFad()];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo actualizar FAD global.', 'error' => $e->getMessage()];
        }
    }

    public function guardarReglaFad(array $data, int $idUsuario = 0): array
    {
        $scope = strtolower(trim((string) ($data['scope'] ?? '')));
        $scopeValue = trim((string) ($data['scope_value'] ?? ''));
        $mode = strtolower(trim((string) ($data['mode'] ?? 'required')));
        $rolloutMode = strtolower(trim((string) ($data['rollout_mode'] ?? 'manual')));

        if (!in_array($scope, self::FAD_SCOPES, true)) {
            return ['success' => false, 'mensaje' => 'Scope FAD no valido.'];
        }
        if (!in_array($mode, self::FAD_MODES, true)) {
            return ['success' => false, 'mensaje' => 'Modo FAD no valido.'];
        }
        if (!in_array($rolloutMode, self::FAD_ROLLOUT_MODES, true)) {
            return ['success' => false, 'mensaje' => 'Rollout FAD no valido.'];
        }

        if ($scope === 'global') {
            $scopeValue = '*';
        }
        if ($scopeValue === '') {
            return ['success' => false, 'mensaje' => 'Captura el valor del scope.'];
        }

        $enabled = !empty($data['enabled']) ? 1 : 0;
        $requiresFad = !empty($data['requires_fad']) ? 1 : 0;
        $showBadge = !empty($data['show_badge']) ? 1 : 0;
        $blockClose = !empty($data['block_close']) ? 1 : 0;
        $notifyPending = !empty($data['notify_pending']) ? 1 : 0;
        $priority = max(0, min(9999, (int) ($data['priority'] ?? 100)));
        $message = trim((string) ($data['message'] ?? 'FAD pendiente para moto adjudicada'));
        $effectiveFrom = $this->normalizarFechaFad($data['effective_from'] ?? null);
        $effectiveTo = $this->normalizarFechaFad($data['effective_to'] ?? null);

        try {
            $this->db->CRUD(
                "INSERT INTO " . self::FAD_TABLA_REGLAS . "
                    (scope, scope_value, enabled, requires_fad, mode, rollout_mode,
                     notify_pending, show_badge, block_close, effective_from, effective_to,
                     message, priority, is_active, created_by)
                 VALUES
                    (:scope, :scope_value, :enabled, :requires_fad, :mode, :rollout_mode,
                     :notify_pending, :show_badge, :block_close, :effective_from, :effective_to,
                     :message, :priority, 1, :created_by)",
                [
                    'scope' => $scope,
                    'scope_value' => $scopeValue,
                    'enabled' => $enabled,
                    'requires_fad' => $requiresFad,
                    'mode' => $mode,
                    'rollout_mode' => $rolloutMode,
                    'notify_pending' => $notifyPending,
                    'show_badge' => $showBadge,
                    'block_close' => $blockClose,
                    'effective_from' => $effectiveFrom,
                    'effective_to' => $effectiveTo,
                    'message' => $message,
                    'priority' => $priority,
                    'created_by' => $idUsuario > 0 ? (string) $idUsuario : 'sparta',
                ]
            );
            $idRegla = $this->db->lastInsertId();
            $this->registrarEventoFad($idRegla, 'crear_regla', $idUsuario, [
                'scope' => $scope,
                'scope_value' => $scopeValue,
                'mode' => $mode,
                'priority' => $priority,
            ]);

            return ['success' => true, 'mensaje' => 'Regla FAD agregada.', 'datos' => $this->obtenerFad()];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo guardar la regla FAD.', 'error' => $e->getMessage()];
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

    private function normalizarReglaFad(?array $row): ?array
    {
        if (!$row) {
            return null;
        }

        foreach (['enabled', 'requires_fad', 'notify_pending', 'show_badge', 'block_close', 'priority', 'is_active'] as $key) {
            if (array_key_exists($key, $row)) {
                $row[$key] = (int) $row[$key];
            }
        }

        return $row;
    }

    private function normalizarFechaFad($fecha): ?string
    {
        $fecha = trim((string) $fecha);
        if ($fecha === '') {
            return null;
        }
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ? $fecha : null;
    }

    private function registrarEventoFad(int $idRegla, string $evento, int $idUsuario, array $payload = []): void
    {
        if ($idRegla <= 0) {
            return;
        }

        try {
            $this->db->CRUD(
                "INSERT INTO " . self::FAD_TABLA_EVENTOS . "
                    (id_regla, evento, usuario_id, payload_json, fecha_alta)
                 VALUES
                    (:id_regla, :evento, :usuario_id, :payload_json, NOW())",
                [
                    'id_regla' => $idRegla,
                    'evento' => $evento,
                    'usuario_id' => $idUsuario > 0 ? $idUsuario : null,
                    'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]
            );
        } catch (\Throwable $e) {
            // La bitacora FAD es opcional; no debe bloquear la configuracion.
        }
    }
}
