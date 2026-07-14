<?php

namespace Services;

use Core\Database;

/**
 * Mensajeria interna de Leonidas. El modelo de IA no escribe en estas tablas:
 * todas las operaciones pasan por metodos validados y auditables del servidor.
 */
class LeonidasMessagingService
{
    private const MAX_MESSAGE_LENGTH = 1000;
    private const MAX_REPLY_LENGTH = 1000;

    private Database $db;
    private static bool $schemaReady = false;

    public function __construct()
    {
        $this->db = new Database();
        $this->ensureSchema();
    }

    public static function actorSesion(): array
    {
        $actorId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($actorId <= 0 || empty($_SESSION['login'])) {
            throw new \RuntimeException('Tu sesion no esta disponible. Inicia sesion nuevamente.');
        }

        return [
            'actor_id' => $actorId,
            'nombre' => trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Usuario')),
        ];
    }

    public function buscarDestinatarios(string $criterio, int $limite = 6): array
    {
        $terminos = preg_split('/\s+/', $this->normalizar($criterio)) ?: [];
        $terminos = array_values(array_filter($terminos, static fn (string $termino): bool => mb_strlen($termino, 'UTF-8') >= 2));
        if (!$terminos) {
            return [];
        }

        $condiciones = [];
        $params = [];
        foreach (array_slice($terminos, 0, 5) as $indice => $termino) {
            $clave = 'termino' . $indice;
            $condiciones[] = "LOWER(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom, p.user_name, p.numero_empleado)) LIKE :{$clave}";
            $params[$clave] = '%' . $termino . '%';
        }

        $sql = "SELECT p.id,
                    p.numero_empleado,
                    p.user_name AS usuario,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre,
                    COALESCE(NULLIF(TRIM(p.estatus), ''), 'Activo') AS estatus
                FROM persona p
                WHERE " . implode(' AND ', $condiciones) . "
                  AND UPPER(COALESCE(NULLIF(TRIM(p.estatus), ''), 'ACTIVO')) = 'ACTIVO'
                ORDER BY
                    CASE WHEN UPPER(TRIM(COALESCE(p.estatus, ''))) = 'ACTIVO' THEN 0 ELSE 1 END,
                    nombre ASC
                LIMIT " . max(1, min(10, $limite));

        return array_map(static function (array $persona): array {
            return [
                'id' => (int) ($persona['id'] ?? 0),
                'nombre' => (string) ($persona['nombre'] ?? ''),
                'numero_empleado' => (string) ($persona['numero_empleado'] ?? ''),
                'usuario' => (string) ($persona['usuario'] ?? ''),
                'estatus' => (string) ($persona['estatus'] ?? ''),
            ];
        }, $this->db->queryAll($sql, $params));
    }

    public function enviar(int $remitenteId, int $destinatarioId, string $mensaje): array
    {
        $mensaje = trim($mensaje);
        if ($remitenteId <= 0 || $destinatarioId <= 0) {
            throw new \InvalidArgumentException('No se pudo identificar al remitente o al destinatario.');
        }
        if ($remitenteId === $destinatarioId) {
            throw new \InvalidArgumentException('El destinatario debe ser otra persona.');
        }
        if ($mensaje === '' || mb_strlen($mensaje, 'UTF-8') > self::MAX_MESSAGE_LENGTH) {
            throw new \InvalidArgumentException('El mensaje debe contener entre 1 y ' . self::MAX_MESSAGE_LENGTH . ' caracteres.');
        }

        $remitente = $this->personaPorId($remitenteId);
        $destinatario = $this->personaPorId($destinatarioId);
        if (!$remitente || !$destinatario) {
            throw new \RuntimeException('La persona seleccionada ya no esta disponible.');
        }
        if (mb_strtoupper(trim((string) ($destinatario['estatus'] ?? '')), 'UTF-8') !== 'ACTIVO') {
            throw new \RuntimeException('El destinatario no tiene estatus Activo. Revisa la persona antes de enviar.');
        }

        $this->db->CRUD(
            "INSERT INTO leonidas_mensajes
                (remitente_persona_id, destinatario_persona_id, mensaje, estado, creado_en)
             VALUES (:remitente, :destinatario, :mensaje, 'pendiente', NOW())",
            [
                'remitente' => $remitenteId,
                'destinatario' => $destinatarioId,
                'mensaje' => $mensaje,
            ]
        );
        $id = $this->db->lastInsertId();
        $this->auditar($id, $remitenteId, 'enviado', ['destinatario_id' => $destinatarioId]);

        return [
            'id' => $id,
            'destinatario' => $this->nombrePersona($destinatario),
            'mensaje' => $mensaje,
            'estado' => 'pendiente',
        ];
    }

    public function obtenerEntrega(int $destinatarioId): ?array
    {
        $fila = $this->db->queryOne(
            "SELECT m.id, m.mensaje, m.creado_en, m.estado,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS remitente
               FROM leonidas_mensajes m
               INNER JOIN persona p ON p.id = m.remitente_persona_id
              WHERE m.destinatario_persona_id = :destinatario
                AND m.estado IN ('pendiente', 'entregado')
              ORDER BY m.id ASC
              LIMIT 1",
            ['destinatario' => $destinatarioId]
        );
        if (!$fila) {
            return null;
        }

        if ((string) ($fila['estado'] ?? '') === 'pendiente') {
            $this->db->CRUD(
                "UPDATE leonidas_mensajes
                    SET estado = 'entregado', entregado_en = COALESCE(entregado_en, NOW())
                  WHERE id = :id AND destinatario_persona_id = :destinatario AND estado = 'pendiente'",
                ['id' => (int) $fila['id'], 'destinatario' => $destinatarioId]
            );
            $this->auditar((int) $fila['id'], $destinatarioId, 'mostrado', []);
        }

        return [
            'id' => (int) $fila['id'],
            'remitente' => (string) $fila['remitente'],
            'mensaje' => (string) $fila['mensaje'],
            'creado_en' => (string) $fila['creado_en'],
        ];
    }

    public function responder(int $destinatarioId, int $mensajeId, string $tipo, string $contenido = ''): array
    {
        $tipos = ['respuesta', 'reaccion', 'descartado'];
        if (!in_array($tipo, $tipos, true)) {
            throw new \InvalidArgumentException('El tipo de respuesta no es valido.');
        }

        $contenido = trim($contenido);
        if ($tipo === 'respuesta' && $contenido === '') {
            throw new \InvalidArgumentException('Escribe la respuesta antes de enviarla.');
        }
        if ($tipo === 'reaccion' && !in_array($contenido, ['like', 'love', 'laugh', 'ok'], true)) {
            throw new \InvalidArgumentException('Selecciona una reaccion valida.');
        }
        if (mb_strlen($contenido, 'UTF-8') > self::MAX_REPLY_LENGTH) {
            throw new \InvalidArgumentException('La respuesta no puede superar ' . self::MAX_REPLY_LENGTH . ' caracteres.');
        }

        $mensaje = $this->db->queryOne(
            "SELECT id, remitente_persona_id
               FROM leonidas_mensajes
              WHERE id = :id
                AND destinatario_persona_id = :destinatario
                AND estado IN ('pendiente', 'entregado')
              LIMIT 1",
            ['id' => $mensajeId, 'destinatario' => $destinatarioId]
        );
        if (!$mensaje) {
            throw new \RuntimeException('Este mensaje ya fue atendido o ya no esta disponible.');
        }

        $estado = $tipo === 'respuesta' ? 'respondido' : ($tipo === 'reaccion' ? 'reaccionado' : 'descartado');
        $this->db->CRUD(
            "UPDATE leonidas_mensajes
                SET estado = :estado,
                    respuesta_tipo = :tipo,
                    respuesta_texto = :contenido,
                    respondido_en = NOW(),
                    remitente_visto = 0
              WHERE id = :id AND destinatario_persona_id = :destinatario",
            [
                'estado' => $estado,
                'tipo' => $tipo,
                'contenido' => $contenido,
                'id' => $mensajeId,
                'destinatario' => $destinatarioId,
            ]
        );
        $this->auditar($mensajeId, $destinatarioId, $estado, ['tipo' => $tipo]);

        return [
            'id' => $mensajeId,
            'estado' => $estado,
            'mensaje' => $tipo === 'descartado'
                ? 'Entendido. Le informare al remitente que viste el mensaje y no enviaste respuesta.'
                : 'Listo. Llevare tu ' . ($tipo === 'reaccion' ? 'reaccion' : 'respuesta') . ' al remitente.',
        ];
    }

    public function obtenerNovedadesRemitente(int $remitenteId): array
    {
        $filas = $this->db->queryAll(
            "SELECT m.id, m.estado, m.respuesta_tipo, m.respuesta_texto, m.respondido_en,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS destinatario
               FROM leonidas_mensajes m
               INNER JOIN persona p ON p.id = m.destinatario_persona_id
              WHERE m.remitente_persona_id = :remitente
                AND m.remitente_visto = 0
                AND m.estado IN ('respondido', 'reaccionado', 'descartado')
              ORDER BY m.id ASC
              LIMIT 10",
            ['remitente' => $remitenteId]
        );
        if (!$filas) {
            return [];
        }

        $ids = array_map(static fn (array $fila): int => (int) $fila['id'], $filas);
        $params = [];
        foreach ($ids as $indice => $id) {
            $params['id' . $indice] = $id;
        }
        $condiciones = implode(' OR ', array_map(static fn (string $clave): string => 'id = :' . $clave, array_keys($params)));
        $this->db->CRUD("UPDATE leonidas_mensajes SET remitente_visto = 1 WHERE {$condiciones}", $params);

        return array_map(static function (array $fila): array {
            return [
                'id' => (int) $fila['id'],
                'destinatario' => (string) $fila['destinatario'],
                'estado' => (string) $fila['estado'],
                'tipo' => (string) ($fila['respuesta_tipo'] ?? ''),
                'contenido' => (string) ($fila['respuesta_texto'] ?? ''),
                'respondido_en' => (string) ($fila['respondido_en'] ?? ''),
            ];
        }, $filas);
    }

    private function ensureSchema(): void
    {
        if (self::$schemaReady) {
            return;
        }

        $this->db->CRUD("CREATE TABLE IF NOT EXISTS leonidas_mensajes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            remitente_persona_id INT NOT NULL,
            destinatario_persona_id INT NOT NULL,
            mensaje VARCHAR(1000) NOT NULL,
            estado VARCHAR(24) NOT NULL DEFAULT 'pendiente',
            respuesta_tipo VARCHAR(24) NULL,
            respuesta_texto VARCHAR(1000) NULL,
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            entregado_en DATETIME NULL,
            respondido_en DATETIME NULL,
            remitente_visto TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY idx_leonidas_destino_estado (destinatario_persona_id, estado, id),
            KEY idx_leonidas_remitente_visto (remitente_persona_id, remitente_visto, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->CRUD("CREATE TABLE IF NOT EXISTS leonidas_mensajes_auditoria (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            mensaje_id BIGINT UNSIGNED NOT NULL,
            actor_persona_id INT NOT NULL,
            evento VARCHAR(40) NOT NULL,
            detalle_json TEXT NULL,
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_leonidas_auditoria_mensaje (mensaje_id, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        self::$schemaReady = true;
    }

    private function personaPorId(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT id, nombres, segundo_nombre, apellidop, apellidom,
                    COALESCE(NULLIF(estatus, ''), 'Activo') AS estatus
               FROM persona WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    private function nombrePersona(array $persona): string
    {
        return trim(implode(' ', array_filter([
            $persona['nombres'] ?? '',
            $persona['segundo_nombre'] ?? '',
            $persona['apellidop'] ?? '',
            $persona['apellidom'] ?? '',
        ], static fn ($valor): bool => trim((string) $valor) !== '')));
    }

    private function auditar(int $mensajeId, int $actorId, string $evento, array $detalle): void
    {
        $this->db->CRUD(
            "INSERT INTO leonidas_mensajes_auditoria
                (mensaje_id, actor_persona_id, evento, detalle_json, creado_en)
             VALUES (:mensaje, :actor, :evento, :detalle, NOW())",
            [
                'mensaje' => $mensajeId,
                'actor' => $actorId,
                'evento' => $evento,
                'detalle' => json_encode($detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            ]
        );
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        return $ascii === false ? $texto : $ascii;
    }
}
