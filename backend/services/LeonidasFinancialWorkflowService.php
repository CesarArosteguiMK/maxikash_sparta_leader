<?php

namespace Services;

use Core\Database;

/**
 * Estados de trabajo que los módulos históricos aún no modelan por sí solos.
 * Las fuentes finales siguen siendo Ticket (categoría viáticos),
 * Condonaciones y CierreCredito.
 */
final class LeonidasFinancialWorkflowService
{
    private Database $db;
    private LeonidasAttachmentService $attachments;
    private static bool $schemaReady = false;

    public function __construct(?Database $db = null, ?LeonidasAttachmentService $attachments = null)
    {
        $this->db = $db ?? new Database();
        $this->attachments = $attachments ?? new LeonidasAttachmentService();
        $this->ensureSchema();
    }

    public function crearViatico(array $payload, array $contexto): array
    {
        $tipo = trim((string) ($payload['tipo_viatico'] ?? ''));
        $descripcion = trim((string) ($payload['descripcion'] ?? ''));
        $monto = round((float) ($payload['monto'] ?? 0), 2);
        if ($tipo === '' || $descripcion === '' || $monto <= 0) {
            return ['success' => false, 'message' => 'Tipo, descripción y monto son obligatorios.'];
        }
        $resultado = \Models\Ticket::crearTicketSimple([
            'categoria_gestion' => 'viaticos',
            'tipo_categoria' => $tipo,
            'asunto' => 'Solicitud de viáticos',
            'descripcion_inicial' => $descripcion,
            'prefijo_folio' => 'VIA',
        ], (int) ($contexto['actor_id'] ?? 0));
        if (empty($resultado['success'])) {
            return $this->normalizar($resultado);
        }
        $idViatico = (int) ($resultado['datos']['id_ticket'] ?? 0);
        if ($idViatico <= 0) {
            return ['success' => false, 'message' => 'La solicitud se guardó, pero no se obtuvo su identificador.'];
        }
        $this->db->CRUD(
            "INSERT INTO leonidas_viaticos_flujo
                (id_viatico, solicitante_id, monto, moneda, estatus, creado_en, actualizado_en)
             VALUES (:id, :actor, :monto, :moneda, 'borrador', NOW(), NOW())",
            [
                'id' => $idViatico,
                'actor' => (int) $contexto['actor_id'],
                'monto' => $monto,
                'moneda' => strtoupper(trim((string) ($payload['moneda'] ?? 'MXN'))) ?: 'MXN',
            ]
        );
        return [
            'success' => true,
            'message' => 'Solicitud de viáticos creada en borrador.',
            'id_viatico' => $idViatico,
            'estatus' => 'borrador',
            'monto' => $monto,
        ];
    }

    public function adjuntarComprobante(array $payload, array $contexto): array
    {
        $id = (int) ($payload['id_viatico'] ?? 0);
        $token = trim((string) ($payload['archivo_token'] ?? ''));
        $actual = $this->consultarViatico($id);
        if (!$actual) {
            return ['success' => false, 'message' => 'La solicitud de viáticos no existe.'];
        }
        if (in_array((string) ($actual['estatus'] ?? ''), ['pagado', 'rechazado'], true)) {
            return ['success' => false, 'message' => 'La solicitud ya no admite comprobantes.'];
        }
        $archivo = $this->attachments->materializar($token, (int) $contexto['actor_id'], 'viaticos');
        $evidencia = \Models\Ticket::guardarEvidencia(
            $id,
            (int) $contexto['actor_id'],
            (string) $archivo['ruta_publica'],
            (string) $archivo['nombre_original']
        );
        if (empty($evidencia['success'])) {
            return $this->normalizar($evidencia);
        }
        $this->db->CRUD(
            "UPDATE leonidas_viaticos_flujo
             SET comprobante_hash = :hash, actualizado_en = NOW()
             WHERE id_viatico = :id",
            ['hash' => $archivo['hash'], 'id' => $id]
        );
        return [
            'success' => true,
            'message' => 'Comprobante adjuntado y verificado.',
            'id_viatico' => $id,
            'archivo' => $archivo['nombre_original'],
            'hash' => $archivo['hash'],
        ];
    }

    public function enviarViatico(int $id, int $actorId): array
    {
        $actual = $this->consultarViatico($id);
        if (!$actual) {
            return ['success' => false, 'message' => 'La solicitud de viáticos no existe.'];
        }
        if ((int) ($actual['solicitante_id'] ?? 0) !== $actorId) {
            return ['success' => false, 'message' => 'Solo la persona solicitante puede enviarla a autorización.'];
        }
        if ((string) ($actual['estatus'] ?? '') !== 'borrador') {
            return ['success' => false, 'message' => 'La solicitud ya fue enviada o atendida.'];
        }
        $this->db->CRUD(
            "UPDATE leonidas_viaticos_flujo
             SET estatus = 'pendiente_autorizacion', enviado_en = NOW(), actualizado_en = NOW()
             WHERE id_viatico = :id AND estatus = 'borrador'",
            ['id' => $id]
        );
        return ['success' => true, 'message' => 'Solicitud enviada a autorización.', 'id_viatico' => $id, 'estatus' => 'pendiente_autorizacion'];
    }

    public function aprobarViatico(int $id, int $actorId): array
    {
        return $this->cambiarViatico(
            $id,
            ['pendiente_autorizacion'],
            'aprobado',
            ['autorizado_por' => $actorId, 'autorizado_en' => date('Y-m-d H:i:s')],
            'Solicitud de viáticos aprobada.'
        );
    }

    public function rechazarViatico(int $id, int $actorId, string $motivo): array
    {
        if (trim($motivo) === '') {
            return ['success' => false, 'message' => 'El motivo de rechazo es obligatorio.'];
        }
        return $this->cambiarViatico(
            $id,
            ['pendiente_autorizacion', 'aprobado'],
            'rechazado',
            [
                'autorizado_por' => $actorId,
                'autorizado_en' => date('Y-m-d H:i:s'),
                'motivo_rechazo' => mb_substr(trim($motivo), 0, 500),
            ],
            'Solicitud de viáticos rechazada.'
        );
    }

    public function registrarPagoViatico(int $id, int $actorId, string $referencia): array
    {
        if (trim($referencia) === '') {
            return ['success' => false, 'message' => 'La referencia de pago es obligatoria.'];
        }
        return $this->cambiarViatico(
            $id,
            ['aprobado'],
            'pagado',
            [
                'pagado_por' => $actorId,
                'pagado_en' => date('Y-m-d H:i:s'),
                'referencia_pago' => mb_substr(trim($referencia), 0, 160),
            ],
            'Pago de viáticos registrado.'
        );
    }

    public function consultarViatico(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $row = $this->db->queryOne(
            "SELECT v.id_ticket AS id, v.id_persona_creador,
                    v.tipo_categoria AS tipo_viatico,
                    v.descripcion_inicial AS descripcion,
                    v.folio, v.fecha_creacion,
                    f.solicitante_id, f.monto, f.moneda, f.estatus,
                    f.autorizado_por, f.autorizado_en, f.motivo_rechazo,
                    f.pagado_por, f.pagado_en, f.referencia_pago, f.actualizado_en
             FROM ticket v
             LEFT JOIN leonidas_viaticos_flujo f ON f.id_viatico = v.id_ticket
             WHERE v.id_ticket = :id
               AND v.categoria_gestion = 'viaticos'
             LIMIT 1",
            ['id' => $id]
        );
        if ($row) {
            $evidencias = \Models\Ticket::getEvidenciasPorTicket($id);
            $row['evidencias'] = !empty($evidencias['success']) ? (array) ($evidencias['datos'] ?? []) : [];
        }
        return $row ?: null;
    }

    public function prepararCondonacion(array $payload, int $actorId): array
    {
        $idCredito = (int) ($payload['id_credito'] ?? 0);
        $monto = round((float) ($payload['total_condonado'] ?? 0), 2);
        $comentario = trim((string) ($payload['comentario'] ?? ''));
        if ($idCredito <= 0 || $monto <= 0 || $comentario === '') {
            return ['success' => false, 'message' => 'Crédito, total condonado y comentario son obligatorios.'];
        }
        $codigo = 'CON-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
        $datos = [
            'id_credito' => $idCredito,
            'id_motivo_condonacion' => (int) ($payload['id_motivo_condonacion'] ?? 1),
            'comentario' => $comentario,
            'total_condonado' => $monto,
            'detalles' => is_array($payload['detalles'] ?? null) ? $payload['detalles'] : [],
        ];
        $this->db->CRUD(
            "INSERT INTO leonidas_condonaciones_flujo
                (codigo, payload_json, id_credito, monto, creador_id, estatus, creado_en, actualizado_en)
             VALUES (:codigo, :payload, :credito, :monto, :actor, 'borrador', NOW(), NOW())",
            [
                'codigo' => $codigo,
                'payload' => json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'credito' => $idCredito,
                'monto' => $monto,
                'actor' => $actorId,
            ]
        );
        return ['success' => true, 'message' => 'Solicitud de condonación preparada.', 'codigo' => $codigo, 'estatus' => 'borrador'];
    }

    public function enviarCondonacion(string $codigo, int $actorId): array
    {
        $row = $this->consultarCondonacion($codigo);
        if (!$row) {
            return ['success' => false, 'message' => 'La solicitud de condonación no existe.'];
        }
        if ((int) ($row['creador_id'] ?? 0) !== $actorId || (string) ($row['estatus'] ?? '') !== 'borrador') {
            return ['success' => false, 'message' => 'La solicitud no puede enviarse desde su estado actual.'];
        }
        $this->db->CRUD(
            "UPDATE leonidas_condonaciones_flujo
             SET estatus = 'pendiente_autorizacion', enviado_en = NOW(), actualizado_en = NOW()
             WHERE codigo = :codigo AND estatus = 'borrador'",
            ['codigo' => strtoupper($codigo)]
        );
        return ['success' => true, 'message' => 'Condonación enviada a doble autorización.', 'codigo' => strtoupper($codigo), 'estatus' => 'pendiente_autorizacion'];
    }

    public function aplicarCondonacion(string $codigo, array $contexto): array
    {
        $row = $this->consultarCondonacion($codigo);
        if (!$row || (string) ($row['estatus'] ?? '') !== 'pendiente_autorizacion') {
            return ['success' => false, 'message' => 'La condonación no está pendiente de autorización.'];
        }
        $payload = json_decode((string) ($row['payload_json'] ?? ''), true);
        if (!is_array($payload)) {
            return ['success' => false, 'message' => 'El borrador de condonación está dañado.'];
        }
        $resultado = \models\Condonaciones::crearOperativa(
            $payload,
            (int) ($contexto['actor_id'] ?? 0),
            (string) ($contexto['nombre'] ?? 'Leonidas')
        );
        if (empty($resultado['success'])) {
            return $this->normalizar($resultado);
        }
        $this->db->CRUD(
            "UPDATE leonidas_condonaciones_flujo
             SET estatus = 'aplicada', aprobador_id = :actor, id_condonacion = :id_condonacion,
                 aprobado_en = NOW(), actualizado_en = NOW()
             WHERE codigo = :codigo AND estatus = 'pendiente_autorizacion'",
            [
                'actor' => (int) $contexto['actor_id'],
                'id_condonacion' => (int) ($resultado['id_condonacion'] ?? 0),
                'codigo' => strtoupper($codigo),
            ]
        );
        return $this->normalizar($resultado) + ['codigo' => strtoupper($codigo), 'estatus' => 'aplicada'];
    }

    public function rechazarCondonacion(string $codigo, int $actorId, string $motivo): array
    {
        if (trim($motivo) === '') {
            return ['success' => false, 'message' => 'El motivo de rechazo es obligatorio.'];
        }
        $afectadas = $this->db->CRUD(
            "UPDATE leonidas_condonaciones_flujo
             SET estatus = 'rechazada', aprobador_id = :actor, motivo_rechazo = :motivo,
                 aprobado_en = NOW(), actualizado_en = NOW()
             WHERE codigo = :codigo AND estatus = 'pendiente_autorizacion'",
            [
                'actor' => $actorId,
                'motivo' => mb_substr(trim($motivo), 0, 500),
                'codigo' => strtoupper($codigo),
            ]
        );
        return (int) $afectadas > 0
            ? ['success' => true, 'message' => 'Condonación rechazada.', 'codigo' => strtoupper($codigo), 'estatus' => 'rechazada']
            : ['success' => false, 'message' => 'La condonación no está pendiente de autorización.'];
    }

    public function consultarCondonacion(string $codigo): ?array
    {
        $row = $this->db->queryOne(
            "SELECT * FROM leonidas_condonaciones_flujo WHERE codigo = :codigo LIMIT 1",
            ['codigo' => strtoupper(trim($codigo))]
        );
        return $row ?: null;
    }

    public function verificarCondonacion(string $codigo): array
    {
        $flujo = $this->consultarCondonacion($codigo);
        if (!$flujo) {
            return ['success' => false, 'message' => 'Solicitud de condonación no encontrada.'];
        }
        $id = (int) ($flujo['id_condonacion'] ?? 0);
        $fuente = $id > 0 ? \models\Condonaciones::obtenerOperativa($id) : null;
        $coincide = $fuente
            && (int) ($fuente['id_credito'] ?? 0) === (int) ($flujo['id_credito'] ?? 0)
            && abs((float) ($fuente['total_condonado'] ?? 0) - (float) ($flujo['monto'] ?? 0)) <= 0.01;
        return [
            'success' => $coincide,
            'message' => $coincide
                ? 'Resultado financiero verificado en la fuente de condonaciones.'
                : 'La solicitud aún no está aplicada o no coincide con la fuente financiera.',
            'codigo' => strtoupper($codigo),
            'flujo' => $flujo,
            'fuente' => $fuente,
        ];
    }

    private function cambiarViatico(
        int $id,
        array $origenes,
        string $destino,
        array $campos,
        string $mensaje
    ): array {
        $actual = $this->consultarViatico($id);
        if (!$actual || !in_array((string) ($actual['estatus'] ?? ''), $origenes, true)) {
            return ['success' => false, 'message' => 'La solicitud no está en un estado válido para esta acción.'];
        }
        $sets = ['estatus = :estatus', 'actualizado_en = NOW()'];
        $params = ['estatus' => $destino, 'id' => $id];
        $permitidos = ['autorizado_por', 'autorizado_en', 'motivo_rechazo', 'pagado_por', 'pagado_en', 'referencia_pago'];
        foreach ($campos as $campo => $valor) {
            if (in_array($campo, $permitidos, true)) {
                $sets[] = "{$campo} = :{$campo}";
                $params[$campo] = $valor;
            }
        }
        $this->db->CRUD(
            "UPDATE leonidas_viaticos_flujo SET " . implode(', ', $sets) . "
             WHERE id_viatico = :id",
            $params
        );
        return ['success' => true, 'message' => $mensaje, 'id_viatico' => $id, 'estatus' => $destino];
    }

    private function normalizar(array $resultado): array
    {
        $resultado['message'] = (string) ($resultado['message'] ?? $resultado['mensaje'] ?? '');
        return $resultado;
    }

    private function ensureSchema(): void
    {
        if (self::$schemaReady) return;
        $this->db->CRUD(
            "CREATE TABLE IF NOT EXISTS leonidas_viaticos_flujo (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_viatico INT NOT NULL,
                solicitante_id INT NOT NULL,
                monto DECIMAL(14,2) NOT NULL,
                moneda CHAR(3) NOT NULL DEFAULT 'MXN',
                estatus VARCHAR(40) NOT NULL,
                comprobante_hash CHAR(64) NULL,
                autorizado_por INT NULL,
                autorizado_en DATETIME NULL,
                motivo_rechazo VARCHAR(500) NULL,
                pagado_por INT NULL,
                pagado_en DATETIME NULL,
                referencia_pago VARCHAR(160) NULL,
                enviado_en DATETIME NULL,
                creado_en DATETIME NOT NULL,
                actualizado_en DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_leonidas_viatico (id_viatico),
                KEY idx_leonidas_viatico_estado (estatus, actualizado_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $this->db->CRUD(
            "CREATE TABLE IF NOT EXISTS leonidas_condonaciones_flujo (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                codigo VARCHAR(20) NOT NULL,
                payload_json LONGTEXT NOT NULL,
                id_credito INT NOT NULL,
                monto DECIMAL(14,2) NOT NULL,
                creador_id INT NOT NULL,
                aprobador_id INT NULL,
                id_condonacion INT NULL,
                estatus VARCHAR(40) NOT NULL,
                motivo_rechazo VARCHAR(500) NULL,
                creado_en DATETIME NOT NULL,
                enviado_en DATETIME NULL,
                aprobado_en DATETIME NULL,
                actualizado_en DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_leonidas_condonacion_codigo (codigo),
                KEY idx_leonidas_condonacion_estado (estatus, actualizado_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        self::$schemaReady = true;
    }
}
