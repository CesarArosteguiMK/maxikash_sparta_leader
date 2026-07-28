<?php

namespace Services;

use Core\Database;

/**
 * Registro durable de ejecuciones y autorizaciones sensibles de Leonidas.
 *
 * Nunca persiste el texto completo de la conversación. Solo conserva hashes,
 * payloads operativos normalizados y comprobantes sanitizados.
 */
final class LeonidasOperationStore
{
    private Database $db;
    private static bool $schemaReady = false;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? new Database();
        $this->ensureSchema();
    }

    public function buscarEjecucion(string $idempotencyKey): ?array
    {
        $row = $this->db->queryOne(
            "SELECT id, accion, actor_id, estado, comprobante_json, creado_en, terminado_en
             FROM leonidas_operaciones
             WHERE idempotency_key = :clave
             LIMIT 1",
            ['clave' => $idempotencyKey]
        );
        if (!$row) {
            return null;
        }
        $row['comprobante'] = $this->decode((string) ($row['comprobante_json'] ?? ''));
        unset($row['comprobante_json']);
        return $row;
    }

    public function iniciar(string $idempotencyKey, string $accion, int $actorId, string $payloadHash): array
    {
        $existente = $this->buscarEjecucion($idempotencyKey);
        if ($existente) {
            if ((string) ($existente['estado'] ?? '') === 'fallida') {
                $this->db->CRUD(
                    "UPDATE leonidas_operaciones
                     SET estado = 'ejecutando', actor_id = :actor, payload_hash = :hash,
                         error_resumen = NULL, comprobante_json = NULL,
                         creado_en = NOW(), terminado_en = NULL
                     WHERE idempotency_key = :clave AND estado = 'fallida'",
                    [
                        'actor' => $actorId,
                        'hash' => $payloadHash,
                        'clave' => $idempotencyKey,
                    ]
                );
                return [
                    'nueva' => true,
                    'reintento' => true,
                    'ejecucion' => [
                        'id' => (int) ($existente['id'] ?? 0),
                        'accion' => $accion,
                        'actor_id' => $actorId,
                        'estado' => 'ejecutando',
                    ],
                ];
            }
            return ['nueva' => false, 'ejecucion' => $existente];
        }

        try {
            $this->db->CRUD(
                "INSERT INTO leonidas_operaciones
                    (idempotency_key, accion, actor_id, payload_hash, estado, creado_en)
                 VALUES (:clave, :accion, :actor, :hash, 'ejecutando', NOW())",
                [
                    'clave' => $idempotencyKey,
                    'accion' => $accion,
                    'actor' => $actorId,
                    'hash' => $payloadHash,
                ]
            );
        } catch (\Throwable $e) {
            $existente = $this->buscarEjecucion($idempotencyKey);
            if ($existente) {
                return ['nueva' => false, 'ejecucion' => $existente];
            }
            throw $e;
        }

        return [
            'nueva' => true,
            'ejecucion' => [
                'id' => $this->db->lastInsertId(),
                'accion' => $accion,
                'actor_id' => $actorId,
                'estado' => 'ejecutando',
            ],
        ];
    }

    public function completar(string $idempotencyKey, array $comprobante): void
    {
        $this->db->CRUD(
            "UPDATE leonidas_operaciones
             SET estado = 'verificada', comprobante_json = :comprobante, terminado_en = NOW()
             WHERE idempotency_key = :clave",
            [
                'clave' => $idempotencyKey,
                'comprobante' => $this->encode($comprobante),
            ]
        );
    }

    public function fallar(string $idempotencyKey, string $mensaje): void
    {
        $this->db->CRUD(
            "UPDATE leonidas_operaciones
             SET estado = 'fallida', error_resumen = :error, terminado_en = NOW()
             WHERE idempotency_key = :clave",
            [
                'clave' => $idempotencyKey,
                'error' => mb_substr(trim($mensaje), 0, 500),
            ]
        );
    }

    public function crearAutorizacion(string $accion, array $payload, int $actorId, string $resumen): array
    {
        $hash = hash('sha256', $accion . '|' . $this->canonicalJson($payload));
        $vigente = $this->db->queryOne(
            "SELECT id, codigo, accion, primer_actor_id, estado, expira_en
             FROM leonidas_autorizaciones
             WHERE payload_hash = :hash
               AND primer_actor_id = :actor
               AND estado = 'pendiente'
               AND expira_en >= NOW()
             ORDER BY id DESC
             LIMIT 1",
            ['hash' => $hash, 'actor' => $actorId]
        );
        if ($vigente) {
            return $vigente;
        }

        $codigo = 'LEO-' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 10));
        $this->db->CRUD(
            "INSERT INTO leonidas_autorizaciones
                (codigo, accion, payload_json, payload_hash, resumen, primer_actor_id, estado, creado_en, expira_en)
             VALUES (:codigo, :accion, :payload, :hash, :resumen, :actor, 'pendiente', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))",
            [
                'codigo' => $codigo,
                'accion' => $accion,
                'payload' => $this->encode($payload),
                'hash' => $hash,
                'resumen' => mb_substr(trim($resumen), 0, 500),
                'actor' => $actorId,
            ]
        );

        return [
            'id' => $this->db->lastInsertId(),
            'codigo' => $codigo,
            'accion' => $accion,
            'primer_actor_id' => $actorId,
            'estado' => 'pendiente',
        ];
    }

    public function obtenerAutorizacion(string $codigo): ?array
    {
        $row = $this->db->queryOne(
            "SELECT id, codigo, accion, payload_json, resumen, primer_actor_id,
                    segundo_actor_id, estado, creado_en, expira_en
             FROM leonidas_autorizaciones
             WHERE codigo = :codigo
             LIMIT 1",
            ['codigo' => strtoupper(trim($codigo))]
        );
        if (!$row) {
            return null;
        }
        $row['payload'] = $this->decode((string) ($row['payload_json'] ?? ''));
        unset($row['payload_json']);
        return $row;
    }

    public function reclamarAutorizacion(string $codigo, int $segundoActorId): array
    {
        $autorizacion = $this->obtenerAutorizacion($codigo);
        if (!$autorizacion) {
            throw new \RuntimeException('La autorización reforzada no existe.');
        }
        if ((string) ($autorizacion['estado'] ?? '') !== 'pendiente') {
            throw new \RuntimeException('La autorización ya fue atendida o ya no está disponible.');
        }
        if ((string) ($autorizacion['expira_en'] ?? '') < date('Y-m-d H:i:s')) {
            $this->marcarAutorizacion($codigo, 'expirada', $segundoActorId);
            throw new \RuntimeException('La autorización reforzada expiró.');
        }
        if ((int) ($autorizacion['primer_actor_id'] ?? 0) === $segundoActorId) {
            throw new \DomainException('La segunda autorización debe realizarla otra persona con permiso.');
        }

        $afectadas = $this->db->CRUD(
            "UPDATE leonidas_autorizaciones
             SET estado = 'ejecutando', segundo_actor_id = :actor, autorizado_en = NOW()
             WHERE codigo = :codigo AND estado = 'pendiente' AND primer_actor_id <> :actor",
            ['codigo' => strtoupper(trim($codigo)), 'actor' => $segundoActorId]
        );
        if ((int) $afectadas < 1) {
            throw new \RuntimeException('Otra sesión atendió la autorización antes que tú.');
        }

        $autorizacion['segundo_actor_id'] = $segundoActorId;
        $autorizacion['estado'] = 'ejecutando';
        return $autorizacion;
    }

    public function marcarAutorizacion(string $codigo, string $estado, ?int $actorId = null, array $comprobante = []): void
    {
        $permitidos = ['ejecutada', 'rechazada', 'fallida', 'expirada', 'cancelada'];
        if (!in_array($estado, $permitidos, true)) {
            throw new \InvalidArgumentException('Estado de autorización no permitido.');
        }
        $this->db->CRUD(
            "UPDATE leonidas_autorizaciones
             SET estado = :estado,
                 segundo_actor_id = COALESCE(:actor, segundo_actor_id),
                 comprobante_json = :comprobante,
                 terminado_en = NOW()
             WHERE codigo = :codigo",
            [
                'estado' => $estado,
                'actor' => $actorId,
                'comprobante' => $this->encode($comprobante),
                'codigo' => strtoupper(trim($codigo)),
            ]
        );
    }

    private function ensureSchema(): void
    {
        if (self::$schemaReady) {
            return;
        }
        $this->db->CRUD(
            "CREATE TABLE IF NOT EXISTS leonidas_operaciones (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                idempotency_key CHAR(64) NOT NULL,
                accion VARCHAR(120) NOT NULL,
                actor_id INT NOT NULL,
                payload_hash CHAR(64) NOT NULL,
                estado VARCHAR(30) NOT NULL,
                comprobante_json LONGTEXT NULL,
                error_resumen VARCHAR(500) NULL,
                creado_en DATETIME NOT NULL,
                terminado_en DATETIME NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_leonidas_operacion_idempotencia (idempotency_key),
                KEY idx_leonidas_operacion_actor (actor_id, creado_en),
                KEY idx_leonidas_operacion_accion (accion, creado_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $this->db->CRUD(
            "CREATE TABLE IF NOT EXISTS leonidas_autorizaciones (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                codigo VARCHAR(20) NOT NULL,
                accion VARCHAR(120) NOT NULL,
                payload_json LONGTEXT NOT NULL,
                payload_hash CHAR(64) NOT NULL,
                resumen VARCHAR(500) NOT NULL,
                primer_actor_id INT NOT NULL,
                segundo_actor_id INT NULL,
                estado VARCHAR(30) NOT NULL,
                comprobante_json LONGTEXT NULL,
                creado_en DATETIME NOT NULL,
                expira_en DATETIME NOT NULL,
                autorizado_en DATETIME NULL,
                terminado_en DATETIME NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_leonidas_autorizacion_codigo (codigo),
                KEY idx_leonidas_autorizacion_pendiente (estado, expira_en),
                KEY idx_leonidas_autorizacion_hash (payload_hash, primer_actor_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $this->asegurarPermisoEspecial(
            'Autorizar viáticos con Leonidas',
            'Permite otorgar una de las dos autorizaciones requeridas para viáticos.'
        );
        $this->asegurarPermisoEspecial(
            'Registrar pagos de viáticos con Leonidas',
            'Permite registrar la referencia de pago de un viático con doble autorización.'
        );
        $this->asegurarPermisoEspecial(
            'Autorizar condonaciones con Leonidas',
            'Permite otorgar una de las dos autorizaciones requeridas para aplicar condonaciones.'
        );
        self::$schemaReady = true;
    }

    private function asegurarPermisoEspecial(string $nombre, string $descripcion): void
    {
        $existente = $this->db->queryOne(
            "SELECT id FROM modulos_web
             WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:nombre))
             LIMIT 1",
            ['nombre' => $nombre]
        );
        if ($existente) {
            $this->db->CRUD(
                "UPDATE modulos_web
                 SET pestana = 'Permisos especiales', descripcion = :descripcion, activo = 1
                 WHERE id = :id",
                ['descripcion' => $descripcion, 'id' => (int) $existente['id']]
            );
            return;
        }
        $this->db->CRUD(
            "INSERT INTO modulos_web (nombre, pestana, descripcion, activo)
             VALUES (:nombre, 'Permisos especiales', :descripcion, 1)",
            ['nombre' => $nombre, 'descripcion' => $descripcion]
        );
    }

    private function canonicalJson(array $value): string
    {
        ksort($value);
        foreach ($value as &$item) {
            if (is_array($item)) {
                $item = json_decode($this->canonicalJson($item), true);
            }
        }
        unset($item);
        return $this->encode($value);
    }

    private function encode(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function decode(string $value): array
    {
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
}
