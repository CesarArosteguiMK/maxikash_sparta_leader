<?php

namespace Models;

use Core\Database;
use Core\Model;
use Services\SolicitudAdjudicacionValidator;

class SolicitudAdjudicacion extends Model
{
    public const ESTATUS_RECIBIDA = 'recibida';
    private const ESTATUS_TERMINALES = ['cancelada', 'rechazada', 'completada', 'blacklist'];

    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function tablasDisponibles(): bool
    {
        try {
            $this->db->queryOne('SELECT id FROM adj_solicitud LIMIT 1');
            $this->db->queryOne('SELECT id FROM adj_solicitud_historial LIMIT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function crear(array $input, int $actorId, string $actorNombre): array
    {
        if ($actorId <= 0) {
            return ['success' => false, 'message' => 'La sesion no tiene un usuario valido.'];
        }
        if (!$this->tablasDisponibles()) {
            return [
                'success' => false,
                'migration_required' => true,
                'message' => 'Falta aplicar la migracion de solicitudes de adjudicacion.',
            ];
        }

        $validacion = SolicitudAdjudicacionValidator::validarCreacion(
            $input,
            (string) ($input['canal'] ?? SolicitudAdjudicacionValidator::CANAL_ATC)
        );
        if (!$validacion['valid']) {
            return [
                'success' => false,
                'message' => 'Revisa los campos obligatorios.',
                'errors' => $validacion['errors'],
            ];
        }

        $data = $validacion['data'];
        $idCredito = (int) $data['id_credito'];
        $actorNombre = $this->texto($actorNombre !== '' ? $actorNombre : 'Usuario Sparta', 180);
        $nombreCliente = $this->texto($input['nombre_cliente'] ?? '', 180);
        $datosCredito = $this->snapshotCredito(
            is_array($input['datos_credito'] ?? null) ? $input['datos_credito'] : []
        );
        $idempotencyKey = $this->texto($input['idempotency_key'] ?? '', 100);
        $ahora = $this->fechaHoraCdmx();

        try {
            $this->db->beginTransaction();

            $terminales = [];
            $params = ['id_credito' => $idCredito];
            foreach (self::ESTATUS_TERMINALES as $index => $estatusTerminal) {
                $key = 'terminal_' . $index;
                $terminales[] = ':' . $key;
                $params[$key] = $estatusTerminal;
            }
            if ($idempotencyKey !== '') {
                $repetida = $this->db->queryOne(
                    'SELECT id, id_usuario_solicitante
                       FROM adj_solicitud
                      WHERE idempotency_key = :idempotency_key
                      LIMIT 1
                      FOR UPDATE',
                    ['idempotency_key' => $idempotencyKey]
                );
                if ($repetida) {
                    $this->db->rollback();
                    if ((int) $repetida['id_usuario_solicitante'] !== $actorId) {
                        return [
                            'success' => false,
                            'message' => 'La clave de operación ya fue utilizada.',
                        ];
                    }
                    return [
                        'success' => true,
                        'idempotent_replay' => true,
                        'message' => 'La solicitud ya habia sido registrada.',
                        'solicitud' => $this->obtenerPorId((int) $repetida['id'], $actorId),
                    ];
                }
            }
            $activa = $this->db->queryOne(
                "SELECT id, folio, estatus
                   FROM adj_solicitud
                  WHERE id_credito = :id_credito
                    AND deleted_at IS NULL
                    AND estatus NOT IN (" . implode(',', $terminales) . ")
                  ORDER BY id DESC
                  LIMIT 1
                  FOR UPDATE",
                $params
            );
            if ($activa) {
                $this->db->rollback();
                return [
                    'success' => false,
                    'duplicate' => true,
                    'message' => 'El credito ya tiene una solicitud activa.',
                    'solicitud' => $activa,
                ];
            }

            $uuid = $this->uuidV4();
            $folio = $this->generarFolio();
            $payloadOriginal = $input;
            unset($payloadOriginal['datos_credito']);

            $this->db->CRUD(
                'INSERT INTO adj_solicitud (
                    uuid, folio, id_credito, canal, estatus, nombre_cliente,
                    entregara_titular, nombre_entregante, kilometraje, telefono_actual,
                    direccion_resguardo, motivo, vin, tipo_asignacion,
                    id_persona_gestor, nombre_gestor, id_usuario_solicitante,
                    nombre_usuario_solicitante, idempotency_key, datos_credito_json,
                    payload_original, version, fecha_alta, fecha_actualizacion
                 ) VALUES (
                    :uuid, :folio, :id_credito, :canal, :estatus, :nombre_cliente,
                    :entregara_titular, :nombre_entregante, :kilometraje, :telefono_actual,
                    :direccion_resguardo, :motivo, :vin, :tipo_asignacion,
                    :id_persona_gestor, :nombre_gestor, :actor_id, :actor_nombre,
                    :idempotency_key, :datos_credito_json, :payload_original,
                    1, :fecha_alta, :fecha_actualizacion
                 )',
                [
                    'uuid' => $uuid,
                    'folio' => $folio,
                    'id_credito' => $idCredito,
                    'canal' => $data['canal'],
                    'estatus' => self::ESTATUS_RECIBIDA,
                    'nombre_cliente' => $nombreCliente !== '' ? $nombreCliente : null,
                    'entregara_titular' => $data['entregara_titular'] ? 1 : 0,
                    'nombre_entregante' => $data['nombre_entregante'],
                    'kilometraje' => $data['kilometraje'],
                    'telefono_actual' => $data['telefono_actual'],
                    'direccion_resguardo' => $data['direccion_resguardo'],
                    'motivo' => $data['motivo'],
                    'vin' => $data['vin'],
                    'tipo_asignacion' => $data['tipo_asignacion'],
                    'id_persona_gestor' => $data['id_persona_gestor'],
                    'nombre_gestor' => $data['nombre_gestor'],
                    'actor_id' => $actorId,
                    'actor_nombre' => $actorNombre,
                    'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : null,
                    'datos_credito_json' => $this->json($datosCredito),
                    'payload_original' => $this->json($payloadOriginal),
                    'fecha_alta' => $ahora,
                    'fecha_actualizacion' => $ahora,
                ]
            );
            $id = (int) $this->db->lastInsertId();

            $this->db->CRUD(
                'INSERT INTO adj_solicitud_historial (
                    id_solicitud, evento, estatus_anterior, estatus_nuevo, comentario,
                    actor_id, actor_nombre, actor_canal, metadata_json, fecha
                 ) VALUES (
                    :id_solicitud, :evento, NULL, :estatus_nuevo, :comentario,
                    :actor_id, :actor_nombre, :actor_canal, :metadata_json, :fecha
                 )',
                [
                    'id_solicitud' => $id,
                    'evento' => 'solicitud_creada',
                    'estatus_nuevo' => self::ESTATUS_RECIBIDA,
                    'comentario' => 'Solicitud registrada desde ' . $data['canal'] . '.',
                    'actor_id' => $actorId,
                    'actor_nombre' => $actorNombre,
                    'actor_canal' => $data['canal'],
                    'metadata_json' => $this->json(['id_credito' => $idCredito]),
                    'fecha' => $ahora,
                ]
            );

            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Solicitud registrada correctamente.',
                'solicitud' => $this->obtenerPorId($id, $actorId),
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            error_log('[SolicitudAdjudicacion] ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo registrar la solicitud.'];
        }
    }

    public function listarPorSolicitante(int $actorId, array $filtros = [], string $canal = 'ATC'): array
    {
        if (!$this->tablasDisponibles()) {
            return ['success' => false, 'migration_required' => true, 'rows' => [], 'message' => 'Falta aplicar la migracion.'];
        }

        $canal = strtoupper(trim($canal));
        $where = ['s.deleted_at IS NULL', 's.id_usuario_solicitante = :actor_id', 's.canal = :canal'];
        $params = ['actor_id' => $actorId, 'canal' => $canal];
        $q = trim((string) ($filtros['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(CAST(s.id_credito AS CHAR) LIKE :q OR s.folio LIKE :q OR s.nombre_cliente LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }

        $rows = $this->db->queryAll(
            'SELECT s.id, s.uuid, s.folio, s.id_credito, s.canal, s.estatus,
                    s.nombre_cliente, s.entregara_titular, s.nombre_entregante,
                    s.kilometraje, s.telefono_actual, s.direccion_resguardo, s.motivo,
                    s.vin, s.tipo_asignacion, s.id_persona_gestor, s.nombre_gestor,
                    s.id_persona_asignada, s.nombre_persona_asignada,
                    s.fecha_asignacion, s.comentario_asignacion,
                    s.nombre_usuario_solicitante,
                    DATE_FORMAT(s.fecha_alta, "%d/%m/%Y %H:%i") AS fecha_alta_fmt,
                    DATE_FORMAT(s.fecha_actualizacion, "%d/%m/%Y %H:%i") AS fecha_actualizacion_fmt
               FROM adj_solicitud s
              WHERE ' . implode(' AND ', $where) . '
              ORDER BY s.fecha_alta DESC, s.id DESC
              LIMIT 200',
            $params
        ) ?: [];

        return ['success' => true, 'rows' => $rows];
    }

    public function listarBandeja(array $filtros = []): array
    {
        if (!$this->tablasDisponibles()) {
            return [
                'success' => false,
                'migration_required' => true,
                'rows' => [],
                'stats' => [],
                'message' => 'Falta aplicar la migracion de solicitudes.',
            ];
        }

        $where = ['s.deleted_at IS NULL'];
        $params = [];
        $q = trim((string) ($filtros['q'] ?? ''));
        $canal = strtoupper(trim((string) ($filtros['canal'] ?? '')));
        $estatus = strtolower(trim((string) ($filtros['estatus'] ?? '')));

        if ($q !== '') {
            $where[] = '(CAST(s.id_credito AS CHAR) LIKE :q
                         OR s.folio LIKE :q
                         OR s.nombre_cliente LIKE :q
                         OR s.nombre_entregante LIKE :q
                         OR s.nombre_persona_asignada LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        if (in_array($canal, ['ATC', 'CALLCENTER', 'DESPACHOS', 'CAMPO'], true)) {
            $where[] = 's.canal = :canal';
            $params['canal'] = $canal;
        }
        if (in_array($estatus, ['recibida', 'asignada', 'rechazada', 'cancelada', 'completada'], true)) {
            $where[] = 's.estatus = :estatus';
            $params['estatus'] = $estatus;
        }

        $rows = $this->db->queryAll(
            'SELECT s.id, s.uuid, s.folio, s.id_credito, s.canal, s.estatus,
                    s.nombre_cliente, s.entregara_titular, s.nombre_entregante,
                    s.kilometraje, s.telefono_actual, s.direccion_resguardo, s.motivo,
                    s.vin, s.tipo_asignacion, s.id_persona_gestor, s.nombre_gestor,
                    s.id_persona_asignada, s.nombre_persona_asignada,
                    s.asignada_por_nombre, s.fecha_asignacion, s.comentario_asignacion,
                    s.id_usuario_solicitante, s.nombre_usuario_solicitante, s.id_operacion,
                    DATE_FORMAT(s.fecha_alta, "%d/%m/%Y %H:%i") AS fecha_alta_fmt,
                    DATE_FORMAT(s.fecha_actualizacion, "%d/%m/%Y %H:%i") AS fecha_actualizacion_fmt,
                    DATE_FORMAT(s.fecha_asignacion, "%d/%m/%Y %H:%i") AS fecha_asignacion_fmt
               FROM adj_solicitud s
              WHERE ' . implode(' AND ', $where) . '
              ORDER BY CASE WHEN s.estatus = "recibida" THEN 0 ELSE 1 END,
                       s.fecha_alta ASC, s.id ASC
              LIMIT 500',
            $params
        ) ?: [];

        $stats = $this->db->queryOne(
            'SELECT COUNT(*) AS total,
                    SUM(CASE WHEN estatus = "recibida" THEN 1 ELSE 0 END) AS recibidas,
                    SUM(CASE WHEN estatus = "asignada" THEN 1 ELSE 0 END) AS asignadas,
                    SUM(CASE WHEN canal = "ATC" THEN 1 ELSE 0 END) AS atc,
                    SUM(CASE WHEN canal = "CALLCENTER" THEN 1 ELSE 0 END) AS callcenter,
                    SUM(CASE WHEN canal = "DESPACHOS" THEN 1 ELSE 0 END) AS despachos,
                    SUM(CASE WHEN canal = "CAMPO" THEN 1 ELSE 0 END) AS campo
               FROM adj_solicitud
              WHERE deleted_at IS NULL'
        ) ?: [];

        return ['success' => true, 'rows' => $rows, 'stats' => $stats];
    }

    public function obtenerPorIdBandeja(int $id): ?array
    {
        if ($id <= 0 || !$this->tablasDisponibles()) {
            return null;
        }
        $row = $this->db->queryOne(
            'SELECT s.*,
                    DATE_FORMAT(s.fecha_alta, "%d/%m/%Y %H:%i") AS fecha_alta_fmt,
                    DATE_FORMAT(s.fecha_actualizacion, "%d/%m/%Y %H:%i") AS fecha_actualizacion_fmt,
                    DATE_FORMAT(s.fecha_asignacion, "%d/%m/%Y %H:%i") AS fecha_asignacion_fmt
               FROM adj_solicitud s
              WHERE s.id = :id
                AND s.deleted_at IS NULL
              LIMIT 1',
            ['id' => $id]
        );
        if (!$row) {
            return null;
        }
        $row['historial'] = $this->historial((int) $row['id']);
        unset($row['payload_original']);
        return $row;
    }

    public function registrarAsignacionBandeja(
        int $idSolicitud,
        int $idPersona,
        string $nombrePersona,
        int $actorId,
        string $actorNombre,
        string $comentario = ''
    ): array {
        if ($idSolicitud <= 0 || $idPersona <= 0 || $actorId <= 0) {
            return ['success' => false, 'message' => 'La solicitud, el responsable o el usuario no son validos.'];
        }

        $nombrePersona = $this->texto($nombrePersona, 180);
        $actorNombre = $this->texto($actorNombre !== '' ? $actorNombre : 'Usuario Sparta', 180);
        $comentario = $this->texto($comentario, 1000);
        $ahora = $this->fechaHoraCdmx();

        try {
            $this->db->beginTransaction();
            $solicitud = $this->db->queryOne(
                'SELECT id, id_credito, canal, estatus, id_persona_asignada
                   FROM adj_solicitud
                  WHERE id = :id AND deleted_at IS NULL
                  LIMIT 1
                  FOR UPDATE',
                ['id' => $idSolicitud]
            );
            if (!$solicitud) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'La solicitud ya no esta disponible.'];
            }
            if (in_array((string) $solicitud['estatus'], self::ESTATUS_TERMINALES, true)) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'La solicitud se encuentra en un estatus terminal.'];
            }

            $operacion = $this->db->queryOne(
                'SELECT id
                   FROM adj_operacion
                  WHERE id_credito = :id_credito
                  ORDER BY id DESC
                  LIMIT 1',
                ['id_credito' => (int) $solicitud['id_credito']]
            );
            $estatusAnterior = (string) $solicitud['estatus'];
            $idOperacion = (int) ($operacion['id'] ?? 0);

            $this->db->CRUD(
                'UPDATE adj_solicitud
                    SET estatus = :estatus,
                        id_persona_asignada = :id_persona,
                        nombre_persona_asignada = :nombre_persona,
                        asignada_por = :actor_id,
                        asignada_por_nombre = :actor_nombre,
                        fecha_asignacion = :fecha_asignacion,
                        comentario_asignacion = :comentario,
                        id_operacion = :id_operacion,
                        version = version + 1,
                        fecha_actualizacion = :fecha_actualizacion
                  WHERE id = :id',
                [
                    'estatus' => 'asignada',
                    'id_persona' => $idPersona,
                    'nombre_persona' => $nombrePersona,
                    'actor_id' => $actorId,
                    'actor_nombre' => $actorNombre,
                    'fecha_asignacion' => $ahora,
                    'comentario' => $comentario !== '' ? $comentario : null,
                    'id_operacion' => $idOperacion > 0 ? $idOperacion : null,
                    'fecha_actualizacion' => $ahora,
                    'id' => $idSolicitud,
                ]
            );

            $this->db->CRUD(
                'INSERT INTO adj_solicitud_historial (
                    id_solicitud, evento, estatus_anterior, estatus_nuevo, comentario,
                    actor_id, actor_nombre, actor_canal, metadata_json, fecha
                 ) VALUES (
                    :id_solicitud, :evento, :estatus_anterior, :estatus_nuevo, :comentario,
                    :actor_id, :actor_nombre, :actor_canal, :metadata_json, :fecha
                 )',
                [
                    'id_solicitud' => $idSolicitud,
                    'evento' => $estatusAnterior === 'asignada' ? 'solicitud_reasignada' : 'solicitud_asignada',
                    'estatus_anterior' => $estatusAnterior,
                    'estatus_nuevo' => 'asignada',
                    'comentario' => $comentario !== ''
                        ? 'Asignada a ' . $nombrePersona . '. ' . $comentario
                        : 'Asignada a ' . $nombrePersona . '.',
                    'actor_id' => $actorId,
                    'actor_nombre' => $actorNombre,
                    'actor_canal' => 'MOTOS_ADJUDICADAS',
                    'metadata_json' => $this->json([
                        'id_persona' => $idPersona,
                        'id_credito' => (int) $solicitud['id_credito'],
                        'id_operacion' => $idOperacion > 0 ? $idOperacion : null,
                    ]),
                    'fecha' => $ahora,
                ]
            );

            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Solicitud asignada y enviada al flujo operativo.',
                'solicitud' => $this->obtenerPorIdBandeja($idSolicitud),
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            error_log('[SolicitudAdjudicacion::registrarAsignacionBandeja] ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo actualizar la solicitud despues de asignar el credito.'];
        }
    }

    public function obtenerPorId(int $id, int $actorId): ?array
    {
        if ($id <= 0 || !$this->tablasDisponibles()) {
            return null;
        }
        $row = $this->db->queryOne(
            'SELECT s.*,
                    DATE_FORMAT(s.fecha_alta, "%d/%m/%Y %H:%i") AS fecha_alta_fmt,
                    DATE_FORMAT(s.fecha_actualizacion, "%d/%m/%Y %H:%i") AS fecha_actualizacion_fmt
               FROM adj_solicitud s
              WHERE s.id = :id
                AND s.id_usuario_solicitante = :actor_id
                AND s.deleted_at IS NULL
              LIMIT 1',
            ['id' => $id, 'actor_id' => $actorId]
        );
        if (!$row) {
            return null;
        }
        $row['historial'] = $this->historial((int) $row['id']);
        unset($row['payload_original']);
        return $row;
    }

    private function historial(int $idSolicitud): array
    {
        return $this->db->queryAll(
            'SELECT id, evento, estatus_anterior, estatus_nuevo, comentario,
                    actor_nombre, actor_canal,
                    DATE_FORMAT(fecha, "%d/%m/%Y %H:%i") AS fecha_fmt
               FROM adj_solicitud_historial
              WHERE id_solicitud = :id
              ORDER BY fecha DESC, id DESC',
            ['id' => $idSolicitud]
        ) ?: [];
    }

    private function generarFolio(): string
    {
        return 'SA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function fechaHoraCdmx(): string
    {
        return (new \DateTime('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function texto($value, int $max): string
    {
        $value = trim((string) $value);
        return function_exists('mb_substr') ? mb_substr($value, 0, $max, 'UTF-8') : substr($value, 0, $max);
    }

    private function snapshotCredito(array $credito): array
    {
        $permitidos = [
            'id_credito', 'nombre_cliente', 'telefono', 'direccion', 'sucursal',
            'fecha_desembolso', 'saldo_actual', 'dias_mora', 'status_credito',
            'producto', 'periodicidad', 'bucket',
        ];
        return array_intersect_key($credito, array_fill_keys($permitidos, true));
    }
}
