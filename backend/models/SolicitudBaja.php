<?php

namespace Models;

use Core\Database;
use Core\Model;

/**
 * Solicitudes de baja desde Levantar ticket > Solicitud de baja.
 */
class SolicitudBaja extends Model
{
    /**
     * Guarda una solicitud de baja.
     *
     * @param array $datos motivo_baja, detalle_motivo, descripcion (opcional), nombre_colaborador,
     *                      nombre_archivo_original (opcional), ruta_adjunto (opcional)
     * @param int  $idPersonaCreador Quien levanta la solicitud (sesión)
     * @return array { success, mensaje, datos: { id } }
     */
    public static function guardar(array $datos, $idPersonaCreador)
    {
        $idPersonaCreador = (int) $idPersonaCreador;
        if ($idPersonaCreador < 1) {
            return self::resultado(false, 'Sesión inválida. Debe iniciar sesión para enviar la solicitud.', null);
        }

        $motivo = isset($datos['motivo_baja']) ? trim((string) $datos['motivo_baja']) : '';
        $detalle = isset($datos['detalle_motivo']) ? trim((string) $datos['detalle_motivo']) : '';
        $descripcion = isset($datos['descripcion']) ? trim((string) $datos['descripcion']) : null;
        $nombreColaborador = isset($datos['nombre_colaborador']) ? trim((string) $datos['nombre_colaborador']) : '';
        $nombreArchivo = isset($datos['nombre_archivo_original']) ? trim((string) $datos['nombre_archivo_original']) : null;
        $rutaAdjunto = isset($datos['ruta_adjunto']) ? trim((string) $datos['ruta_adjunto']) : null;

        if ($motivo === '') {
            return self::resultado(false, 'El motivo de la solicitud es obligatorio.', null);
        }
        if (strlen($motivo) > 100) {
            return self::resultado(false, 'El motivo no debe exceder 100 caracteres.', null);
        }
        if ($detalle === '') {
            return self::resultado(false, 'El detalle del motivo es obligatorio.', null);
        }
        if ($nombreColaborador === '') {
            return self::resultado(false, 'El nombre del colaborador a dar de baja es obligatorio.', null);
        }
        if (strlen($nombreColaborador) > 255) {
            return self::resultado(false, 'El nombre del colaborador no debe exceder 255 caracteres.', null);
        }
        if ($nombreArchivo !== null && strlen($nombreArchivo) > 255) {
            $nombreArchivo = substr($nombreArchivo, 0, 255);
        }
        if ($rutaAdjunto !== null && strlen($rutaAdjunto) > 500) {
            return self::resultado(false, 'Ruta del adjunto demasiado larga.', null);
        }

        try {
            $db = new Database();
            $db->CRUD(
                "INSERT INTO solicitud_baja (id_persona_creador, motivo_baja, detalle_motivo, descripcion, nombre_colaborador, nombre_archivo_original, ruta_adjunto, fecha_creacion) " .
                "VALUES (:id_persona_creador, :motivo_baja, :detalle_motivo, :descripcion, :nombre_colaborador, :nombre_archivo_original, :ruta_adjunto, NOW())",
                [
                    'id_persona_creador'       => $idPersonaCreador,
                    'motivo_baja'              => $motivo,
                    'detalle_motivo'           => $detalle,
                    'descripcion'              => $descripcion === '' ? null : $descripcion,
                    'nombre_colaborador'       => $nombreColaborador,
                    'nombre_archivo_original'   => $nombreArchivo,
                    'ruta_adjunto'              => $rutaAdjunto,
                ]
            );
            $row = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id = $row ? (int) $row['id'] : 0;
            return self::resultado(true, 'Solicitud de baja registrada correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar la solicitud.', null, $e->getMessage());
        }
    }

    /**
     * Inserta un adjunto adicional para una solicitud de baja (tabla solicitud_baja_adjunto).
     * Usar después de guardar() cuando hay múltiples archivos.
     */
    public static function guardarAdjunto($idSolicitudBaja, $nombreOriginal, $rutaArchivo, $orden = 0)
    {
        $idSolicitudBaja = (int) $idSolicitudBaja;
        if ($idSolicitudBaja < 1) {
            return false;
        }
        $nombreOriginal = trim((string) $nombreOriginal);
        $rutaArchivo = trim((string) $rutaArchivo);
        if ($rutaArchivo === '') {
            return false;
        }
        if (strlen($nombreOriginal) > 255) {
            $nombreOriginal = substr($nombreOriginal, 0, 255);
        }
        if (strlen($rutaArchivo) > 500) {
            return false;
        }
        try {
            $db = new Database();
            $db->CRUD(
                "INSERT INTO solicitud_baja_adjunto (id_solicitud_baja, nombre_original, ruta_archivo, orden) " .
                "VALUES (:id_solicitud_baja, :nombre_original, :ruta_archivo, :orden)",
                [
                    'id_solicitud_baja' => $idSolicitudBaja,
                    'nombre_original'   => $nombreOriginal,
                    'ruta_archivo'       => $rutaArchivo,
                    'orden'              => (int) $orden,
                ]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Lista de solicitudes de baja para Panel Admin (con nombre del creador).
     *
     * @return array { success, mensaje, datos: array }
     */
    public static function getLista()
    {
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT sb.id, sb.motivo_baja, sb.detalle_motivo, sb.descripcion, sb.nombre_colaborador, " .
                "sb.nombre_archivo_original, sb.ruta_adjunto, sb.fecha_creacion, " .
                "CONCAT(TRIM(IFNULL(p.nombres, '')), ' ', TRIM(IFNULL(p.apellidop, ''))) AS creador_nombre, " .
                "(SELECT COUNT(*) FROM solicitud_baja_adjunto a WHERE a.id_solicitud_baja = sb.id) AS num_adjuntos_extra " .
                "FROM solicitud_baja sb " .
                "LEFT JOIN persona p ON sb.id_persona_creador = p.id " .
                "ORDER BY sb.fecha_creacion DESC"
            );
            return self::resultado(true, 'OK', is_array($rows) ? $rows : []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al listar solicitudes.', null, $e->getMessage());
        }
    }

    /**
     * Adjuntos adicionales de una solicitud (tabla solicitud_baja_adjunto), ordenados por orden.
     *
     * @param int $idSolicitudBaja
     * @return array Lista de { id, nombre_original, ruta_archivo, orden }
     */
    public static function getAdjuntosAdicionales($idSolicitudBaja)
    {
        $idSolicitudBaja = (int) $idSolicitudBaja;
        if ($idSolicitudBaja < 1) {
            return [];
        }
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT id, nombre_original, ruta_archivo, orden FROM solicitud_baja_adjunto " .
                "WHERE id_solicitud_baja = :id ORDER BY orden ASC",
                ['id' => $idSolicitudBaja]
            );
            return is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Una solicitud de baja por ID (para modal Ver en Panel Admin).
     *
     * @param int $id
     * @return array|null Fila con creador_nombre o null si no existe.
     */
    public static function getPorId($id)
    {
        $id = (int) $id;
        if ($id < 1) {
            return null;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT sb.id, sb.motivo_baja, sb.detalle_motivo, sb.descripcion, sb.nombre_colaborador, " .
                "sb.nombre_archivo_original, sb.ruta_adjunto, sb.fecha_creacion, " .
                "CONCAT(TRIM(IFNULL(p.nombres, '')), ' ', TRIM(IFNULL(p.apellidop, ''))) AS creador_nombre " .
                "FROM solicitud_baja sb " .
                "LEFT JOIN persona p ON sb.id_persona_creador = p.id " .
                "WHERE sb.id = :id",
                ['id' => $id]
            );
            return $row ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
