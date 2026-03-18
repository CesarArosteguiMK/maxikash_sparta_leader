<?php

namespace Models;

use Core\Database;
use Core\Model;

/**
 * Tickets tipo Plantilla (solicitud de plantilla o documento).
 */
class TicketPlantilla extends Model
{
    public static function guardar(array $datos, $idPersonaCreador)
    {
        $idPersonaCreador = (int) $idPersonaCreador;
        if ($idPersonaCreador < 1) {
            return self::resultado(false, 'Sesión inválida.', null);
        }
        $tipo = isset($datos['tipo_plantilla']) ? trim((string) $datos['tipo_plantilla']) : '';
        $descripcion = isset($datos['descripcion']) ? trim((string) $datos['descripcion']) : '';
        $nombreArchivo = isset($datos['nombre_archivo_original']) ? trim((string) $datos['nombre_archivo_original']) : null;
        $rutaAdjunto = isset($datos['ruta_adjunto']) ? trim((string) $datos['ruta_adjunto']) : null;

        if ($tipo === '') {
            return self::resultado(false, 'El tipo de plantilla es obligatorio.', null);
        }
        if (strlen($tipo) > 100) {
            return self::resultado(false, 'El tipo no debe exceder 100 caracteres.', null);
        }
        if ($descripcion === '') {
            return self::resultado(false, 'La descripción es obligatoria.', null);
        }

        try {
            $db = new Database();
            $db->CRUD(
                "INSERT INTO ticket_plantilla (id_persona_creador, tipo_plantilla, descripcion, nombre_archivo_original, ruta_adjunto, fecha_creacion) " .
                "VALUES (:id_persona_creador, :tipo_plantilla, :descripcion, :nombre_archivo_original, :ruta_adjunto, NOW())",
                [
                    'id_persona_creador'       => $idPersonaCreador,
                    'tipo_plantilla'           => $tipo,
                    'descripcion'              => $descripcion,
                    'nombre_archivo_original'  => $nombreArchivo,
                    'ruta_adjunto'             => $rutaAdjunto,
                ]
            );
            $row = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id = $row ? (int) $row['id'] : 0;
            return self::resultado(true, 'Solicitud de plantilla registrada correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar.', null, $e->getMessage());
        }
    }
}
