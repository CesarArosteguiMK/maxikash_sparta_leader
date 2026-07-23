<?php

namespace Models;

use Core\Database;
use Core\Model;
use Services\GestionCampoEvidenciaPolicy;

class GestionCampo extends Model
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function tablasDisponibles(): bool
    {
        try {
            $this->db->queryOne('SELECT id FROM adj_gestion_campo LIMIT 1');
            $this->db->queryOne('SELECT id FROM adj_gestion_campo_evento LIMIT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function iniciarDesdeLuzVerde(int $idSolicitud, int $actorId, string $actorNombre): array
    {
        if ($idSolicitud <= 0 || $actorId <= 0) {
            return ['success' => false, 'message' => 'Solicitud o usuario inválido.'];
        }
        if (!$this->tablasDisponibles()) {
            return ['success' => false, 'migration_required' => true, 'message' => 'Falta aplicar la migración de Etapa 2.'];
        }

        $fecha = $this->fechaHoraCdmx();
        try {
            $this->db->beginTransaction();
            $solicitud = $this->db->queryOne(
                'SELECT id, id_operacion, id_credito, estatus, evidencia_perfil
                   FROM adj_solicitud WHERE id = :id AND deleted_at IS NULL LIMIT 1 FOR UPDATE',
                ['id' => $idSolicitud]
            );
            if (!$solicitud) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Solicitud no encontrada.'];
            }
            if ((string) $solicitud['estatus'] !== 'luz_verde') {
                $this->db->rollback();
                return ['success' => false, 'message' => 'La gestión de campo solo inicia después de Luz Verde.'];
            }
            if ((int) ($solicitud['id_operacion'] ?? 0) <= 0) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'La solicitud todavía no está vinculada a una operación.'];
            }

            $existente = $this->db->queryOne(
                'SELECT * FROM adj_gestion_campo WHERE id_solicitud = :id LIMIT 1 FOR UPDATE',
                ['id' => $idSolicitud]
            );
            if ($existente) {
                $this->db->rollback();
                return ['success' => true, 'idempotent_replay' => true, 'gestion' => $existente];
            }

            $perfil = GestionCampoEvidenciaPolicy::normalizarPerfil((string) ($solicitud['evidencia_perfil'] ?? ''));
            $this->db->CRUD(
                'INSERT INTO adj_gestion_campo
                    (id_solicitud, id_operacion, id_credito, estatus, evidencia_perfil,
                     creado_por, creado_por_nombre, fecha_alta, fecha_actualizacion)
                 VALUES
                    (:id_solicitud, :id_operacion, :id_credito, :estatus, :perfil,
                     :actor_id, :actor_nombre, :fecha, :fecha)',
                [
                    'id_solicitud' => $idSolicitud,
                    'id_operacion' => (int) $solicitud['id_operacion'],
                    'id_credito' => (int) $solicitud['id_credito'],
                    'estatus' => 'pendiente_captura',
                    'perfil' => $perfil,
                    'actor_id' => $actorId,
                    'actor_nombre' => $this->texto($actorNombre, 180),
                    'fecha' => $fecha,
                ]
            );
            $id = (int) $this->db->lastInsertId();
            $this->db->CRUD(
                'INSERT INTO adj_gestion_campo_evento
                    (id_gestion_campo, evento, estatus_anterior, estatus_nuevo, actor_id, actor_nombre, metadata_json, fecha)
                 VALUES (:id, :evento, NULL, :estatus, :actor_id, :actor_nombre, :metadata, :fecha)',
                [
                    'id' => $id,
                    'evento' => 'gestion_campo_iniciada',
                    'estatus' => 'pendiente_captura',
                    'actor_id' => $actorId,
                    'actor_nombre' => $this->texto($actorNombre, 180),
                    'metadata' => json_encode(['id_solicitud' => $idSolicitud], JSON_UNESCAPED_UNICODE),
                    'fecha' => $fecha,
                ]
            );
            $this->db->commit();
            return ['success' => true, 'message' => 'Gestión de campo preparada.', 'gestion' => $this->obtenerPorId($id)];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            error_log('[GestionCampo] ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo iniciar la gestión de campo.'];
        }
    }

    public function evaluarEvidencias(int $idGestion): array
    {
        $gestion = $this->obtenerPorId($idGestion);
        if (!$gestion) {
            return ['success' => false, 'message' => 'Gestión de campo no encontrada.'];
        }
        $rows = $this->db->queryAll(
            "SELECT DISTINCT slot FROM adj_evidencia
              WHERE id_operacion = :id_operacion
                AND NULLIF(TRIM(COALESCE(url, '')), '') IS NOT NULL",
            ['id_operacion' => (int) $gestion['id_operacion']]
        ) ?: [];
        $evaluacion = GestionCampoEvidenciaPolicy::evaluar(
            array_column($rows, 'slot'),
            (string) $gestion['evidencia_perfil']
        );
        return ['success' => true, 'gestion' => $gestion, 'evidencias' => $evaluacion];
    }

    public function registrarResultadoNotificacion(int $idGestion, array $resultado, int $actorId, string $actorNombre): void
    {
        if ($idGestion <= 0) return;
        $fecha = $this->fechaHoraCdmx();
        $estado = !empty($resultado['success']) ? 'enviada' : 'fallida';
        try {
            $this->db->CRUD(
                'UPDATE adj_gestion_campo
                    SET notificacion_luz_verde_at = :fecha,
                        notificacion_luz_verde_resultado = :resultado,
                        fecha_actualizacion = :fecha,
                        version = version + 1
                  WHERE id = :id',
                ['fecha' => $fecha, 'resultado' => $estado, 'id' => $idGestion]
            );
            $this->db->CRUD(
                'INSERT INTO adj_gestion_campo_evento
                    (id_gestion_campo, evento, estatus_anterior, estatus_nuevo, actor_id, actor_nombre, metadata_json, fecha)
                 VALUES (:id, :evento, NULL, :estatus, :actor_id, :actor_nombre, :metadata, :fecha)',
                [
                    'id' => $idGestion,
                    'evento' => 'notificacion_luz_verde_' . $estado,
                    'estatus' => 'pendiente_captura',
                    'actor_id' => $actorId,
                    'actor_nombre' => $this->texto($actorNombre, 180),
                    'metadata' => json_encode(['success' => !empty($resultado['success']), 'message' => $resultado['message'] ?? ''], JSON_UNESCAPED_UNICODE),
                    'fecha' => $fecha,
                ]
            );
        } catch (\Throwable $e) {
            error_log('[GestionCampo] No se pudo auditar la notificación: ' . $e->getMessage());
        }
    }

    public function listarPendientes(): array
    {
        if (!$this->tablasDisponibles()) {
            return [];
        }
        return $this->db->queryAll(
            "SELECT gc.*, s.folio, s.nombre_cliente, s.canal
               FROM adj_gestion_campo gc
               INNER JOIN adj_solicitud s ON s.id = gc.id_solicitud
              WHERE gc.estatus NOT IN ('completada', 'cancelada')
              ORDER BY gc.fecha_actualizacion DESC, gc.id DESC
              LIMIT 300"
        ) ?: [];
    }

    private function obtenerPorId(int $id): ?array
    {
        return $this->db->queryOne('SELECT * FROM adj_gestion_campo WHERE id = :id LIMIT 1', ['id' => $id]) ?: null;
    }

    private function fechaHoraCdmx(): string
    {
        return (new \DateTime('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');
    }

    private function texto(string $value, int $max): string
    {
        $value = trim($value) ?: 'Usuario Sparta';
        return function_exists('mb_substr') ? mb_substr($value, 0, $max, 'UTF-8') : substr($value, 0, $max);
    }
}
